<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LedgerBalance;
use App\Services\LedgerBalanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccountingReset extends Command
{
    protected $signature = 'accounting:reset
                            {--hard    : HARD RESET — truncate all journal entries and zero ledger balances (cannot be undone)}
                            {--dry-run : Preview what would happen without writing}
                            {--force   : Skip confirmation prompt}';

    protected $description = 'Reset the accounting ledger. Soft mode creates reversals for all posted entries. --hard truncates everything.';

    public function handle(): int
    {
        $hard   = $this->option('hard');
        $dryRun = $this->option('dry-run');

        if ($hard) {
            return $this->hardReset($dryRun);
        }

        return $this->softReset($dryRun);
    }

    // ── Soft Reset: reverse every 'posted' journal entry ─────────────────
    private function softReset(bool $dryRun): int
    {
        $posted = JournalEntry::where('status', 'posted')
            ->whereNull('reversal_of')   // skip entries that are already reversals
            ->with('lines')
            ->get();

        if ($posted->isEmpty()) {
            $this->info('No posted journal entries to reverse — ledger is already clean.');
            return self::SUCCESS;
        }

        $this->warn("Posted journal entries that will be reversed: {$posted->count()}");
        foreach ($posted as $entry) {
            $this->line("  {$entry->entry_number}  [{$entry->entry_date}]  {$entry->reference}  ₹" . number_format($entry->total_debit, 2));
        }

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes written. Re-run without --dry-run to apply.');
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Reverse all {$posted->count()} posted journal entries? This will zero the ledger.")) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        $ledger  = app(LedgerBalanceService::class);
        $success = 0;
        $failed  = 0;
        $adminId = \App\Models\User::where('role', 'super_admin')->value('id') ?? 1;

        foreach ($posted as $entry) {
            try {
                DB::transaction(function () use ($entry, $adminId, $ledger) {
                    $reversal = JournalEntry::create([
                        'entry_number' => 'REV-' . $entry->entry_number,
                        'period_id'    => $entry->period_id,
                        'entry_date'   => now()->toDateString(),
                        'reference'    => 'REV-' . $entry->reference,
                        'type'         => $entry->type,
                        'description'  => 'Accounting Reset — Reversal of ' . $entry->entry_number,
                        'total_debit'  => $entry->total_credit,
                        'total_credit' => $entry->total_debit,
                        'status'       => 'posted',
                        'created_by'   => $adminId,
                        'posted_by'    => $adminId,
                        'posted_at'    => now(),
                        'reversal_of'  => $entry->id,
                    ]);

                    foreach ($entry->lines as $line) {
                        $reversal->lines()->create([
                            'account_id'    => $line->account_id,
                            'debit'         => $line->credit,
                            'credit'        => $line->debit,
                            'description'   => 'Reset Reversal: ' . ($line->description ?? $entry->entry_number),
                            'cost_center_id'=> $line->cost_center_id,
                        ]);
                    }

                    $entry->update(['status' => 'reversed']);
                    $ledger->updateAfterPost($reversal->load('lines'));
                });
                $success++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$entry->entry_number}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Done. Reversed: {$success}  |  Failed: {$failed}");
        $this->showLedgerSummary();
        return self::SUCCESS;
    }

    // ── Hard Reset: truncate all journal data, zero ledger_balances ───────
    private function hardReset(bool $dryRun): int
    {
        $jeCount   = JournalEntry::count();
        $lineCount = JournalEntryLine::count();
        $lbCount   = LedgerBalance::count();

        $this->warn('=== HARD RESET ===');
        $this->warn("This will PERMANENTLY DELETE all accounting data:");
        $this->line("  Journal Entries      : {$jeCount}");
        $this->line("  Journal Entry Lines  : {$lineCount}");
        $this->line("  Ledger Balance rows  : {$lbCount} (will be zeroed)");
        $this->newLine();
        $this->warn('Transactions, Sales, and Purchase Orders will NOT be touched.');
        $this->warn('Run php artisan accounting:setup afterwards to re-post from scratch.');

        if ($dryRun) {
            $this->warn('[DRY RUN] No changes written. Re-run without --dry-run to apply.');
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('PERMANENTLY delete all journal entries and zero ledger balances? THIS CANNOT BE UNDONE.')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            // Null out journal_entry_id references so FK constraints don't block truncate
            DB::table('transactions')->update(['journal_entry_id' => null]);
            DB::table('sales_orders')->update(['journal_entry_id' => null]);
            DB::table('purchase_orders')->update(['journal_entry_id' => null, 'payment_journal_entry_id' => null]);

            JournalEntryLine::query()->delete();
            JournalEntry::query()->delete();

            // Zero all ledger balance rows (keep rows, just reset amounts)
            LedgerBalance::query()->update([
                'total_debit'     => 0,
                'total_credit'    => 0,
                'closing_balance' => 0,
                'opening_balance' => 0,
            ]);
        });

        $this->info('Hard reset complete.');
        $this->line("  Journal Entries remaining : " . JournalEntry::count());
        $this->line("  Ledger Balance rows       : " . LedgerBalance::count() . " (all zeroed)");
        $this->newLine();
        $this->info('Run  php artisan accounting:setup  to re-post all transactions.');
        return self::SUCCESS;
    }

    private function showLedgerSummary(): void
    {
        $balances = LedgerBalance::with('account')->get();
        if ($balances->isEmpty()) {
            return;
        }
        $this->newLine();
        $this->info('Ledger Balances after reset:');
        foreach ($balances as $lb) {
            $code = $lb->account?->code ?? '?';
            $name = $lb->account?->name ?? '?';
            $this->line(sprintf(
                '  %s %-30s  Dr ₹%s  |  Cr ₹%s  |  Closing ₹%s',
                $code, $name,
                number_format($lb->total_debit, 2),
                number_format($lb->total_credit, 2),
                number_format($lb->closing_balance, 2)
            ));
        }
    }
}
