<?php

namespace App\Services;

use App\Models\AnimalGroup;
use App\Models\FeedPlan;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Carbon\Carbon;

class FeedDeductionService
{
    /**
     * Run today's feed deduction.
     *
     * Returns a summary array:
     *   deducted   - list of items successfully deducted
     *   skipped    - items already deducted today
     *   not_found  - feed names that had no matching inventory item
     *   no_stock   - items found but stock was 0 (partial deduction recorded)
     */
    public function runDeduction(?int $recordedBy = null): array
    {
        $today      = Carbon::today()->toDateString();
        $recordedBy = $recordedBy ?? 1; // fallback to system user

        // --- Aggregate daily feed need from all groups ---
        // Key: inventory_item_id (int) when FK available, else "name:{feedItemName}" (string)
        // Value: ['item' => InventoryItem|null, 'need' => float, 'label' => string]
        $feedTotals = [];

        $groups = AnimalGroup::with(['feedPlans.inventoryItem'])->get();
        foreach ($groups as $group) {
            if ($group->head_count <= 0) {
                continue;
            }
            foreach ($group->feedPlans as $plan) {
                $dailyNeed = (float) $group->head_count * (float) $plan->quantity_per_animal_kg;

                // Prefer inventory_item_id FK; fall back to name-based key
                $item = $plan->resolveInventoryItem();
                $key  = $item ? "id:{$item->id}" : "name:{$plan->feed_item_name}";

                if (!isset($feedTotals[$key])) {
                    $feedTotals[$key] = ['item' => $item, 'need' => 0, 'label' => $plan->effective_feed_name];
                }
                $feedTotals[$key]['need'] += $dailyNeed;
            }
        }

        $deducted  = [];
        $skipped   = [];
        $notFound  = [];
        $noStock   = [];

        foreach ($feedTotals as $entry) {
            $dailyNeed = $entry['need'];
            $feedName  = $entry['label'];
            $item      = $entry['item'];

            if ($dailyNeed <= 0) {
                continue;
            }

            // Item not resolved by FK or name matching
            if (!$item) {
                $notFound[] = [
                    'feed_name'  => $feedName,
                    'daily_need' => $dailyNeed,
                ];
                continue;
            }

            // Skip if already deducted today
            $alreadyDone = StockMovement::where('inventory_item_id', $item->id)
                ->where('date', $today)
                ->where('source_purpose', 'Feed')
                ->where('reason', 'Daily Feed Deduction')
                ->exists();

            if ($alreadyDone) {
                $skipped[] = [
                    'feed_name'  => $feedName,
                    'item_name'  => $item->name,
                    'daily_need' => $dailyNeed,
                    'unit'       => $item->unit,
                ];
                continue;
            }

            $available = $item->available_quantity;

            // Create stock movement (deduct whatever is available if stock is short)
            $deductQty = min($dailyNeed, $available);

            StockMovement::create([
                'inventory_item_id'   => $item->id,
                'type'                => 'out',
                'quantity'            => $deductQty,
                'date'                => $today,
                'source_purpose'      => 'Feed',
                'issued_to_or_vendor' => 'Feed Yard',
                'reason'              => 'Daily Feed Deduction',
                'remarks'             => "Auto-deducted for " . now()->format('d M Y') . ". Required: {$dailyNeed} {$item->unit}, Deducted: {$deductQty} {$item->unit}.",
                'recorded_by'         => $recordedBy,
            ]);

            if ($available < $dailyNeed) {
                $noStock[] = [
                    'feed_name'  => $feedName,
                    'item_name'  => $item->name,
                    'daily_need' => $dailyNeed,
                    'available'  => $available,
                    'deducted'   => $deductQty,
                    'unit'       => $item->unit,
                ];
            }

            $deducted[] = [
                'feed_name'  => $feedName,
                'item_name'  => $item->name,
                'daily_need' => $dailyNeed,
                'deducted'   => $deductQty,
                'unit'       => $item->unit,
            ];
        }

        return [
            'deducted'  => $deducted,
            'skipped'   => $skipped,
            'not_found' => $notFound,
            'no_stock'  => $noStock,
        ];
    }

    /**
     * Check which feed items have already been deducted today.
     */
    public function getTodayDeductionStatus(): array
    {
        $today  = Carbon::today()->toDateString();
        $groups = AnimalGroup::with(['feedPlans.inventoryItem'])->get();

        $feedTotals = [];
        foreach ($groups as $group) {
            foreach ($group->feedPlans as $plan) {
                $item = $plan->resolveInventoryItem();
                $key  = $item ? "id:{$item->id}" : "name:{$plan->feed_item_name}";
                if (!isset($feedTotals[$key])) {
                    $feedTotals[$key] = ['item' => $item, 'need' => 0, 'label' => $plan->effective_feed_name];
                }
                $feedTotals[$key]['need'] += (float) $group->head_count * (float) $plan->quantity_per_animal_kg;
            }
        }

        $status = [];
        foreach ($feedTotals as $entry) {
            $feedName  = $entry['label'];
            $dailyNeed = $entry['need'];
            $item      = $entry['item'];

            $movement = $item
                ? StockMovement::where('inventory_item_id', $item->id)
                    ->where('date', $today)
                    ->where('source_purpose', 'Feed')
                    ->where('reason', 'Daily Feed Deduction')
                    ->first()
                : null;

            $status[$feedName] = [
                'daily_need'   => $dailyNeed,
                'deducted'     => $movement !== null,
                'deducted_qty' => $movement ? (float) $movement->quantity : 0,
                'unit'         => $item ? $item->unit : 'kg',
                'item_found'   => $item !== null,
            ];
        }

        return $status;
    }
}
