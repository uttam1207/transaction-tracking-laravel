<?php

namespace App\Http\Controllers\Admin;

use App\Models\Animal;
use App\Models\AnimalGroup;
use App\Models\FeedPlan;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\FeedCalculationService;
use App\Services\FeedDeductionService;

class FeedController extends Controller
{
    public function calculator(FeedCalculationService $feedService, FeedDeductionService $deductService)
    {
        $data = $feedService->getFeedCalculationSummary();
        $animalGroups = AnimalGroup::with('feedPlans')->orderBy('name')->get();
        $todayDeductionStatus = $deductService->getTodayDeductionStatus();

        return view('admin.feed.calculator', compact('data', 'animalGroups', 'todayDeductionStatus'));
    }

    public function deductFeedStock(FeedDeductionService $deductService)
    {
        $result = $deductService->runDeduction(recordedBy: auth()->id());

        $deductedCount = count($result['deducted']);
        $skippedCount  = count($result['skipped']);
        $notFoundCount = count($result['not_found']);
        $noStockCount  = count($result['no_stock']);

        if ($deductedCount === 0 && $skippedCount > 0) {
            return back()->with('info', "Today's feed deduction was already run ({$skippedCount} item(s) already deducted).");
        }

        $msg = "Feed deduction complete: {$deductedCount} item(s) deducted.";
        if ($skippedCount)  $msg .= " {$skippedCount} already done.";
        if ($notFoundCount) $msg .= " {$notFoundCount} feed item(s) not found in inventory.";
        if ($noStockCount)  $msg .= " {$noStockCount} item(s) had insufficient stock.";

        $sessionKey = ($notFoundCount > 0 || $noStockCount > 0) ? 'error' : 'success';

        return back()->with($sessionKey, $msg);
    }

    public function deductionHistory(Request $request)
    {
        $query = StockMovement::with(['inventoryItem', 'recorder'])
            ->where('source_purpose', 'Feed')
            ->where('reason', 'Daily Feed Deduction')
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('item')) {
            $query->whereHas('inventoryItem', fn($q) => $q->where('name', 'like', '%' . $request->item . '%'));
        }

        $movements = $query->paginate(30)->withQueryString();

        // Group by date for summary
        $dailySummary = StockMovement::where('source_purpose', 'Feed')
            ->where('reason', 'Daily Feed Deduction')
            ->selectRaw('date, COUNT(*) as item_count, SUM(quantity) as total_qty')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        return view('admin.feed.deduction-history', compact('movements', 'dailySummary'));
    }

    // Update head count for a group
    public function updateGroupCount(Request $request, AnimalGroup $group)
    {
        $request->validate([
            'head_count' => 'required|integer|min:0',
        ]);

        $group->update(['head_count' => $request->head_count]);

        return back()->with('success', "Head count updated for \"{$group->name}\".");
    }

    // Add a new animal group
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'group_key'  => 'required|string|max:50|unique:animal_groups,group_key',
            'head_count' => 'required|integer|min:0',
        ]);

        AnimalGroup::create($validated);

        return back()->with('success', 'Animal group "' . $validated['name'] . '" added.');
    }

    // Delete an animal group (and its feed plans)
    public function destroyGroup(AnimalGroup $group)
    {
        $name = $group->name;
        $group->feedPlans()->delete();
        $group->delete();
        return back()->with('success', '"' . $name . '" group deleted.');
    }

    // Batch update feed plan quantities
    public function updateFeedPlan(Request $request)
    {
        $request->validate([
            'plans'                          => 'required|array',
            'plans.*.id'                     => 'required|exists:feed_plans,id',
            'plans.*.quantity_per_animal_kg' => 'required|numeric|min:0',
        ]);

        foreach ($request->plans as $planData) {
            FeedPlan::where('id', $planData['id'])->update([
                'quantity_per_animal_kg' => $planData['quantity_per_animal_kg'],
            ]);
        }

        return back()->with('success', 'Feed plan quantities saved.');
    }

    // Add a new feed item to a group's plan
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'animal_group_id'        => 'required|exists:animal_groups,id',
            'feed_item_name'         => 'required|string|max:100',
            'quantity_per_animal_kg' => 'required|numeric|min:0',
        ]);

        // Prevent duplicate feed item in same group
        $exists = FeedPlan::where('animal_group_id', $validated['animal_group_id'])
            ->where('feed_item_name', $validated['feed_item_name'])
            ->exists();

        if ($exists) {
            return back()->with('error', '"' . $validated['feed_item_name'] . '" already exists in this group\'s feed plan.');
        }

        FeedPlan::create($validated);

        return back()->with('success', '"' . $validated['feed_item_name'] . '" added to feed plan.');
    }

    // Delete a feed plan item
    public function destroyPlan(FeedPlan $plan)
    {
        $name = $plan->feed_item_name;
        $plan->delete();
        return back()->with('success', '"' . $name . '" removed from feed plan.');
    }

    // Sync AnimalGroup head counts from actual Animal records (pregnancy_status/status fields)
    public function syncGroupsFromAnimals()
    {
        $syncMap = [
            // Milking = Active cows/buffaloes NOT Dry with at least 1 lactation
            'lactating' => fn () => Animal::where('status', 'Active')
                ->whereIn('animal_type', ['Cow', 'Buffalo'])
                ->where('pregnancy_status', '!=', 'Dry')
                ->where('lactation_number', '>', 0)->count(),
            'pregnant'  => fn () => Animal::where('status', 'Active')
                ->where('pregnancy_status', 'Pregnant')->count(),
            'dry'       => fn () => Animal::where('status', 'Active')
                ->where('pregnancy_status', 'Dry')->count(),
            'calves'    => fn () => Animal::where('status', 'Active')
                ->where('animal_type', 'Calf')->count(),
            'heifers'   => fn () => Animal::where('status', 'Active')
                ->where('animal_type', 'Heifer')->count(),
            'bulls'     => fn () => Animal::where('status', 'Active')
                ->where('animal_type', 'Bull')->count(),
        ];

        $updated = 0;
        foreach ($syncMap as $groupKey => $countFn) {
            $group = AnimalGroup::where('group_key', $groupKey)->first();
            if ($group) {
                $newCount = $countFn();
                $group->update(['head_count' => $newCount]);
                $updated++;
            }
        }

        return back()->with('success', "Animal group counts synced from Animal records ({$updated} groups updated).");
    }
}