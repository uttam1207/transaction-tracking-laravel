<?php

namespace App\Console\Commands;

use App\Models\BreedingRecord;
use App\Models\InventoryItem;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendDairyAlerts extends Command
{
    protected $signature   = 'dairy:send-alerts {--dry-run : Preview alerts without sending}';
    protected $description = 'Send dairy farm alerts: calving due, low stock, medicine expiry';

    public function handle(NotificationService $notifier): int
    {
        $dryRun = $this->option('dry-run');
        $total  = 0;

        // ── 1. Calving Due Alerts (expected_calving_date within 7 days) ──
        $calvingAlerts = BreedingRecord::with('animal')
            ->whereNull('actual_calving_date')
            ->whereNotNull('expected_calving_date')
            ->whereBetween('expected_calving_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->get();

        foreach ($calvingAlerts as $record) {
            $tag     = $record->animal?->tag_number ?? 'Unknown';
            $name    = $record->animal?->name ? " ({$record->animal->name})" : '';
            $daysAway = now()->diffInDays($record->expected_calving_date, false);
            $daysLabel = $daysAway === 0 ? 'today' : "in {$daysAway} day(s)";

            $this->line("  [Calving] {$tag}{$name} — expected {$daysLabel}");

            if (!$dryRun) {
                $notifier->sendToRole('admin',
                    'Calving Alert',
                    "Animal {$tag}{$name} is expected to calve {$daysLabel} ({$record->expected_calving_date->format('d M Y')}).",
                    'warning',
                    ['breeding_record_id' => $record->id]
                );
                $notifier->sendToRole('super_admin',
                    'Calving Alert',
                    "Animal {$tag}{$name} is expected to calve {$daysLabel} ({$record->expected_calving_date->format('d M Y')}).",
                    'warning',
                    ['breeding_record_id' => $record->id]
                );
            }
            $total++;
        }

        // ── 2. Low Stock / Out of Stock Alerts ──
        $lowStockItems = InventoryItem::all()->filter(
            fn($item) => in_array($item->stock_status, ['Low Stock', 'Out of Stock'])
        );

        foreach ($lowStockItems as $item) {
            $status = $item->stock_status;
            $this->line("  [Stock] {$item->name} — {$status} ({$item->available_quantity} {$item->unit})");

            if (!$dryRun) {
                $alertType = $status === 'Out of Stock' ? 'danger' : 'warning';
                $notifier->sendToRole('admin',
                    "{$status}: {$item->name}",
                    "{$item->name} is {$status}. Current quantity: {$item->available_quantity} {$item->unit}. Please restock immediately.",
                    $alertType,
                    ['inventory_item_id' => $item->id]
                );
                $notifier->sendToRole('super_admin',
                    "{$status}: {$item->name}",
                    "{$item->name} is {$status}. Current quantity: {$item->available_quantity} {$item->unit}. Please restock immediately.",
                    $alertType,
                    ['inventory_item_id' => $item->id]
                );
            }
            $total++;
        }

        // ── 3. Medicine / Item Expiry Alerts (within 30 days) ──
        $expiryAlerts = InventoryItem::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now()->toDateString())
            ->get();

        foreach ($expiryAlerts as $item) {
            $daysLeft = now()->diffInDays($item->expiry_date);
            $this->line("  [Expiry] {$item->name} — expires in {$daysLeft} day(s) ({$item->expiry_date->format('d M Y')})");

            if (!$dryRun) {
                $notifier->sendToRole('admin',
                    "Expiry Alert: {$item->name}",
                    "{$item->name} will expire in {$daysLeft} day(s) on {$item->expiry_date->format('d M Y')}. Current stock: {$item->available_quantity} {$item->unit}.",
                    'warning',
                    ['inventory_item_id' => $item->id]
                );
                $notifier->sendToRole('super_admin',
                    "Expiry Alert: {$item->name}",
                    "{$item->name} will expire in {$daysLeft} day(s) on {$item->expiry_date->format('d M Y')}. Current stock: {$item->available_quantity} {$item->unit}.",
                    'warning',
                    ['inventory_item_id' => $item->id]
                );
            }
            $total++;
        }

        $mode = $dryRun ? '[DRY RUN] ' : '';
        $this->info("{$mode}Dairy alerts processed: {$total} alert(s) sent.");

        return self::SUCCESS;
    }
}
