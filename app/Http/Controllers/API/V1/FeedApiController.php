<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Animal;
use App\Models\AnimalGroup;
use App\Models\FeedPlan;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class FeedApiController extends Controller
{
    use ApiResponse;

    /** GET /feed/calculator — feed plan summary with stock comparison */
    public function calculator(Request $request)
    {
        $user     = $request->user();
        $isFarmer = $user->isFarmer();

        if ($isFarmer) {
            $farmerAnimalGroupIds = Animal::where('created_by', $user->id)
                ->whereNotNull('group_id')
                ->distinct()
                ->pluck('group_id');

            $farmerGroupCounts = Animal::where('created_by', $user->id)
                ->whereNotNull('group_id')
                ->selectRaw('group_id, COUNT(*) as count')
                ->groupBy('group_id')
                ->pluck('count', 'group_id');

            $animalGroups = AnimalGroup::with('feedPlans.inventoryItem')
                ->whereIn('id', $farmerAnimalGroupIds)
                ->orderBy('name')
                ->get();
        } else {
            $animalGroups = AnimalGroup::with('feedPlans.inventoryItem')->orderBy('name')->get();
            $farmerGroupCounts = null;
        }

        $feedTotals        = [];
        $groupCalculations = [];

        foreach ($animalGroups as $group) {
            $count     = $isFarmer ? ($farmerGroupCounts[$group->id] ?? 0) : $group->head_count;
            $groupData = [
                'id'           => $group->id,
                'name'         => $group->name,
                'head_count'   => $count,
                'requirements' => [],
            ];

            foreach ($group->feedPlans as $plan) {
                $feedName  = $plan->effective_feed_name;
                $dailyNeed = $count * $plan->quantity_per_animal_kg;
                $groupData['requirements'][] = [
                    'feed_name'      => $feedName,
                    'per_animal_kg'  => (float) $plan->quantity_per_animal_kg,
                    'total_daily_kg' => $dailyNeed,
                ];
                $feedTotals[$feedName] = ($feedTotals[$feedName] ?? 0) + $dailyNeed;
            }

            $groupCalculations[] = $groupData;
        }

        // Stock comparison
        $stockComparison = [];
        $alerts          = [];
        foreach ($feedTotals as $feedName => $dailyNeed) {
            $inventoryItem  = InventoryItem::where('name', $feedName)->first()
                ?? InventoryItem::where('name', 'like', "%{$feedName}%")->first();
            $available      = $inventoryItem ? $inventoryItem->available_quantity : 0;
            $unit           = $inventoryItem ? $inventoryItem->unit : 'kg';
            $daysLeft       = $dailyNeed > 0 ? (int) floor($available / $dailyNeed) : 999;

            $stockComparison[] = [
                'feed_name'    => $feedName,
                'available'    => $available,
                'unit'         => $unit,
                'daily_need'   => $dailyNeed,
                'weekly_need'  => $dailyNeed * 7,
                'days_left'    => $daysLeft,
            ];

            if ($dailyNeed > 0 && $daysLeft <= 7) {
                $alerts[] = [
                    'severity' => $daysLeft <= 3 ? 'danger' : 'warning',
                    'message'  => "{$feedName}: {$daysLeft} day(s) of stock left ({$available} {$unit} available, {$dailyNeed} {$unit}/day needed).",
                ];
            }
        }

        $data = [
            'groups'          => $groupCalculations,
            'stock_comparison'=> $stockComparison,
            'alerts'          => $alerts,
            'total_animals'   => $isFarmer ? ($farmerGroupCounts?->sum() ?? 0) : Animal::where('status', 'Active')->count(),
        ];

        return $this->success($data, 'Feed calculator data retrieved.');
    }

    /** GET /feed/deduction-history — feed deduction log */
    public function deductionHistory(Request $request)
    {
        $user  = $request->user();
        $query = StockMovement::with('inventoryItem')
            ->where('source_purpose', 'Feed')
            ->where('reason', 'Daily Feed Deduction');

        if ($user->isFarmer()) {
            $query->where('recorded_by', $user->id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $movements = $query->orderByDesc('date')->orderByDesc('id')
            ->paginate($request->per_page ?? 20);

        return $this->paginated($movements, 'Feed deduction history retrieved.');
    }
}