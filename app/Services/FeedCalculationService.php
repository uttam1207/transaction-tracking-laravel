<?php

namespace App\Services;

use App\Models\AnimalGroup;
use App\Models\InventoryItem;
use App\Models\Setting;

class FeedCalculationService
{
    public function getFeedCalculationSummary(): array
    {
        $groups = AnimalGroup::with('feedPlans')->get();

        // Configurable alert thresholds (read from settings, fallback to defaults)
        $dangerDays  = (int) (Setting::where('key', 'feed_alert_danger_days')->value('value')  ?? 3);
        $warningDays = (int) (Setting::where('key', 'feed_alert_warning_days')->value('value') ?? 7);

        $feedTotals        = [];
        $groupCalculations = [];

        foreach ($groups as $group) {
            $groupData = [
                'name'         => $group->name,
                'count'        => $group->head_count,
                'requirements' => [],
            ];

            foreach ($group->feedPlans as $plan) {
                $dailyNeed = $group->head_count * $plan->quantity_per_animal_kg;
                $groupData['requirements'][$plan->feed_item_name] = [
                    'per_animal'  => $plan->quantity_per_animal_kg,
                    'total_daily' => $dailyNeed,
                ];

                if (!isset($feedTotals[$plan->feed_item_name])) {
                    $feedTotals[$plan->feed_item_name] = 0;
                }
                $feedTotals[$plan->feed_item_name] += $dailyNeed;
            }

            $groupCalculations[] = $groupData;
        }

        // Compare against Stock Management inventory items
        $stockComparison = [];
        $alerts          = [];

        foreach ($feedTotals as $feedName => $dailyNeed) {
            // Improved lookup: exact match first, then partial, then first word
            $inventoryItem = InventoryItem::where('name', $feedName)->first()
                ?? InventoryItem::where('name', 'like', "%{$feedName}%")->first()
                ?? InventoryItem::where('name', 'like', "%" . strtok($feedName, ' ') . "%")->first();

            $availableStock = $inventoryItem ? $inventoryItem->available_quantity : 0;
            $unit           = $inventoryItem ? $inventoryItem->unit : 'kg';
            $minStock       = $inventoryItem ? $inventoryItem->min_stock : 0;

            $daysLeft = $dailyNeed > 0 ? (int) floor($availableStock / $dailyNeed) : 999;

            $stockComparison[$feedName] = [
                'available'    => $availableStock,
                'daily_need'   => $dailyNeed,
                'weekly_need'  => $dailyNeed * 7,
                'monthly_need' => $dailyNeed * 30,
                'days_left'    => $daysLeft,
                'unit'         => $unit,
                'min_stock'    => $minStock,
            ];

            // Generate Alerts using configurable thresholds
            if ($dailyNeed > 0) {
                if ($daysLeft <= $dangerDays) {
                    $alerts[] = [
                        'type'    => 'danger',
                        'icon'    => 'bi-exclamation-triangle-fill',
                        'message' => "🔴 {$feedName} stock will finish in {$daysLeft} day" . ($daysLeft == 1 ? '' : 's') . "! (Available: {$availableStock} {$unit}, Daily Need: {$dailyNeed} {$unit})",
                    ];
                } elseif ($daysLeft <= $warningDays || $availableStock <= $minStock) {
                    $alerts[] = [
                        'type'    => 'warning',
                        'icon'    => 'bi-exclamation-circle-fill',
                        'message' => "🟡 {$feedName}: {$daysLeft} days of stock remaining ({$availableStock} {$unit} available, need {$dailyNeed} {$unit}/day).",
                    ];
                }
            }
        }

        return [
            'groups'           => $groupCalculations,
            'totals'           => $feedTotals,
            'stock_comparison' => $stockComparison,
            'alerts'           => $alerts,
            'total_animals'    => $groups->sum('head_count'),
            'milking_animals'  => $groups->where('group_key', 'lactating')->sum('head_count'),
            'pregnant_animals' => $groups->where('group_key', 'pregnant')->sum('head_count'),
            'dry_animals'      => $groups->where('group_key', 'dry')->sum('head_count'),
            'calves'           => $groups->where('group_key', 'calves')->sum('head_count'),
            'danger_days'      => $dangerDays,
            'warning_days'     => $warningDays,
        ];
    }
}
