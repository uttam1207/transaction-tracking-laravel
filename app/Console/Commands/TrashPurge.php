<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Console\Command;

class TrashPurge extends Command
{
    protected $signature = 'trash:purge
                            {--days=   : Only purge records deleted more than N days ago (default: all)}
                            {--dry-run : Preview what would be deleted without actually deleting}
                            {--force   : Skip confirmation prompt}';

    protected $description = 'Permanently delete all soft-deleted (trashed) transactions, purchase orders, and sales invoices';

    public function handle(): int
    {
        $days   = $this->option('days');
        $dryRun = $this->option('dry-run');

        $txQuery  = Transaction::onlyTrashed();
        $poQuery  = PurchaseOrder::onlyTrashed();
        $soQuery  = SalesOrder::onlyTrashed();

        if ($days !== null) {
            $cutoff   = now()->subDays((int) $days);
            $txQuery  = $txQuery->where('deleted_at', '<=', $cutoff);
            $poQuery  = $poQuery->where('deleted_at', '<=', $cutoff);
            $soQuery  = $soQuery->where('deleted_at', '<=', $cutoff);
        }

        $txCount = $txQuery->count();
        $poCount = $poQuery->count();
        $soCount = $soQuery->count();
        $total   = $txCount + $poCount + $soCount;

        if ($total === 0) {
            $this->info('Trash is empty — nothing to purge.');
            return self::SUCCESS;
        }

        $this->warn("Records to permanently delete:");
        $this->line("  Transactions    : {$txCount}");
        $this->line("  Purchase Orders : {$poCount}");
        $this->line("  Sales Invoices  : {$soCount}");
        $this->line("  TOTAL           : {$total}");

        if ($days !== null) {
            $this->line("  (older than {$days} day(s))");
        }

        if ($dryRun) {
            $this->warn('[DRY RUN] No records deleted. Re-run without --dry-run to apply.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Permanently delete all {$total} record(s)? THIS CANNOT BE UNDONE.")) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        // Re-fetch queries after confirmation (fresh instances)
        $txQuery->forceDelete();
        $poQuery->forceDelete();
        $soQuery->forceDelete();

        $this->info("Purged: {$txCount} transaction(s), {$poCount} PO(s), {$soCount} sales invoice(s).");
        return self::SUCCESS;
    }
}
