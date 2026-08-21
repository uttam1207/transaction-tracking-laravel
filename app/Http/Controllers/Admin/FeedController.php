<?php

namespace App\Http\Controllers\Admin;

use App\Models\Animal;
use App\Models\AnimalGroup;
use App\Models\FeedPlan;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\FeedCalculationService;
use App\Services\FeedDeductionService;

class FeedController extends Controller
{
    public function calculator(FeedCalculationService $feedService, FeedDeductionService $deductService)
    {
        /** @var \App\Models\User $user */
        $user     = auth()->user();
        $isFarmer = $user->isFarmer();

        if ($isFarmer) {
            // Farmer sees feed only for their own animals' groups
            $farmerAnimalGroupIds = Animal::where('created_by', $user->getAuthIdentifier())
                ->whereNotNull('group_id')
                ->distinct()
                ->pluck('group_id');

            // Count how many of the farmer's animals are in each group
            $farmerGroupCounts = Animal::where('created_by', $user->getAuthIdentifier())
                ->whereNotNull('group_id')
                ->selectRaw('group_id, COUNT(*) as count')
                ->groupBy('group_id')
                ->pluck('count', 'group_id');

            $animalGroups = AnimalGroup::with('feedPlans')
                ->whereIn('id', $farmerAnimalGroupIds)
                ->orderBy('name')
                ->get();

            // Build farmer-specific feed calculation data
            $feedTotals        = [];
            $groupCalculations = [];

            foreach ($animalGroups as $group) {
                $farmerCount = $farmerGroupCounts[$group->id] ?? 0;
                $groupData   = [
                    'name'         => $group->name,
                    'count'        => $farmerCount,
                    'requirements' => [],
                ];

                foreach ($group->feedPlans as $plan) {
                    $dailyNeed = $farmerCount * $plan->quantity_per_animal_kg;
                    $groupData['requirements'][$plan->feed_item_name] = [
                        'per_animal'  => $plan->quantity_per_animal_kg,
                        'total_daily' => $dailyNeed,
                    ];
                    $feedTotals[$plan->feed_item_name] = ($feedTotals[$plan->feed_item_name] ?? 0) + $dailyNeed;
                }

                $groupCalculations[] = $groupData;
            }

            // Stock comparison for farmer's feed items only
            $stockComparison = [];
            $alerts          = [];
            foreach ($feedTotals as $feedName => $dailyNeed) {
                $inventoryItem  = InventoryItem::where('name', $feedName)->first()
                    ?? InventoryItem::where('name', 'like', "%{$feedName}%")->first();
                $availableStock = $inventoryItem ? $inventoryItem->available_quantity : 0;
                $unit           = $inventoryItem ? $inventoryItem->unit : 'kg';
                $daysLeft       = $dailyNeed > 0 ? (int) floor($availableStock / $dailyNeed) : 999;

                $stockComparison[$feedName] = [
                    'available'    => $availableStock,
                    'daily_need'   => $dailyNeed,
                    'weekly_need'  => $dailyNeed * 7,
                    'monthly_need' => $dailyNeed * 30,
                    'days_left'    => $daysLeft,
                    'unit'         => $unit,
                    'min_stock'    => $inventoryItem?->min_stock ?? 0,
                ];

                if ($dailyNeed > 0 && $daysLeft <= 7) {
                    $alerts[] = [
                        'type'    => $daysLeft <= 3 ? 'danger' : 'warning',
                        'icon'    => $daysLeft <= 3 ? 'bi-exclamation-triangle-fill' : 'bi-exclamation-circle-fill',
                        'message' => "{$feedName}: {$daysLeft} day(s) of stock left for your animals ({$availableStock} {$unit} available, {$dailyNeed} {$unit}/day needed).",
                    ];
                }
            }

            $data = [
                'groups'           => $groupCalculations,
                'totals'           => $feedTotals,
                'stock_comparison' => $stockComparison,
                'alerts'           => $alerts,
                'total_animals'    => $farmerGroupCounts->sum(),
                'milking_animals'  => 0,
                'pregnant_animals' => 0,
                'dry_animals'      => 0,
                'calves'           => 0,
                'danger_days'      => 3,
                'warning_days'     => 7,
            ];

            $todayDeductionStatus = $deductService->getTodayDeductionStatus();
            $feedItems = InventoryItem::where('is_active', true)->orderBy('category')->orderBy('name')->get();

            return view('admin.feed.calculator', compact('data', 'animalGroups', 'todayDeductionStatus', 'feedItems', 'isFarmer'));
        }

        // Admin / manager — full farm view
        $data                 = $feedService->getFeedCalculationSummary();
        $animalGroups         = AnimalGroup::with('feedPlans')->orderBy('name')->get();
        $todayDeductionStatus = $deductService->getTodayDeductionStatus();
        $feedItems            = InventoryItem::where('is_active', true)->orderBy('category')->orderBy('name')->get();

        return view('admin.feed.calculator', compact('data', 'animalGroups', 'todayDeductionStatus', 'feedItems', 'isFarmer'));
    }

    public function deductFeedStock(FeedDeductionService $deductService)
    {
        // Only admins/managers run the global deduction
        if (auth()->user()->isFarmer()) {
            return back()->with('error', 'Farmers are not permitted to run the global feed deduction.');
        }

        $result = $deductService->runDeduction(recordedBy: auth()->user()?->id);

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
        /** @var \App\Models\User $user */
        $user     = auth()->user();
        $isFarmer = $user->isFarmer();

        $query = StockMovement::with(['inventoryItem', 'recorder'])
            ->where('source_purpose', 'Feed')
            ->where('reason', 'Daily Feed Deduction')
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        // Farmers see only deductions they personally recorded
        if ($isFarmer) {
            $query->where('recorded_by', $user->getAuthIdentifier());
        }

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

        // Daily summary — farmer-scoped
        $dailySummaryQuery = StockMovement::where('source_purpose', 'Feed')
            ->where('reason', 'Daily Feed Deduction');

        if ($isFarmer) {
            $dailySummaryQuery->where('recorded_by', $user->getAuthIdentifier());
        }

        $dailySummary = $dailySummaryQuery
            ->selectRaw('date, COUNT(*) as item_count, SUM(quantity) as total_qty')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        return view('admin.feed.deduction-history', compact('movements', 'dailySummary', 'isFarmer'));
    }

    // ── Admin-only actions below — farmers are blocked ────────────────────────

    // Update head count for a group
    public function updateGroupCount(Request $request, AnimalGroup $group)
    {
        if (auth()->user()->isFarmer()) {
            return back()->with('error', 'Farmers cannot update group head counts.');
        }

        $request->validate([
            'head_count' => 'required|integer|min:0',
        ]);

        $group->update(['head_count' => $request->head_count]);

        return back()->with('success', "Head count updated for \"{$group->name}\".");
    }

    // Add a new animal group
    public function storeGroup(Request $request)
    {
        if (auth()->user()->isFarmer()) {
            return back()->with('error', 'Farmers cannot add animal groups.');
        }

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
        if (auth()->user()->isFarmer()) {
            return back()->with('error', 'Farmers cannot delete animal groups.');
        }

        $name = $group->name;
        $group->feedPlans()->delete();
        $group->delete();
        return back()->with('success', '"' . $name . '" group deleted.');
    }

    // Batch update feed plan quantities
    public function updateFeedPlan(Request $request)
    {
        if (auth()->user()->isFarmer()) {
            return back()->with('error', 'Farmers cannot edit feed plans.');
        }

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
        if (auth()->user()->isFarmer()) {
            return back()->with('error', 'Farmers cannot add feed plan items.');
        }

        $request->validate([
            'animal_group_id'        => 'required|exists:animal_groups,id',
            'inventory_item_id'      => 'required|exists:inventory_items,id',
            'quantity_per_animal_kg' => 'required|numeric|min:0',
        ]);

        $item = InventoryItem::findOrFail($request->inventory_item_id);

        $exists = FeedPlan::where('animal_group_id', $request->animal_group_id)
            ->where(function ($q) use ($request, $item) {
                $q->where('inventory_item_id', $request->inventory_item_id)
                  ->orWhere('feed_item_name', $item->name);
            })->exists();

        if ($exists) {
            return back()->with('error', '"' . $item->name . '" already exists in this group\'s feed plan.');
        }

        FeedPlan::create([
            'animal_group_id'        => $request->animal_group_id,
            'inventory_item_id'      => $item->id,
            'feed_item_name'         => $item->name,
            'quantity_per_animal_kg' => $request->quantity_per_animal_kg,
        ]);

        return back()->with('success', '"' . $item->name . '" added to feed plan.');
    }

    // Delete a feed plan item
    public function destroyPlan(FeedPlan $plan)
    {
        if (auth()->user()->isFarmer()) {
            return back()->with('error', 'Farmers cannot remove feed plan items.');
        }

        $name = $plan->feed_item_name;
        $plan->delete();
        return back()->with('success', '"' . $name . '" removed from feed plan.');
    }

    // Sync AnimalGroup head counts from actual Animal records — Admin only
    public function syncGroupsFromAnimals()
    {
        if (auth()->user()->isFarmer()) {
            return back()->with('error', 'Farmers cannot sync group counts.');
        }

        $syncMap = [
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
