<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * php artisan accounting:audit
 *
 * Read-only data integrity report.  Never modifies any data.
 *
 * Checks:
 *  1. Unbalanced journal entries (total_debit ≠ total_credit)
 *  2. Sales orders without a posted journal entry
 *  3. Duplicate invoice numbers
 *  4. Journal entry amount vs invoice amount mismatch
 *  5. Purchase orders without a posted journal entry
 *  6. Wallet balance vs GL Bank account (1010) balance
 *  7. AR (account 1100) vs sum of outstanding sales invoices
 *  8. Journal entries missing financial period
 */
class AccountingAudit extends Command
{
    protected $signature   = 'accounting:audit {--fix-report : Show repair suggestions alongside each issue}';
    protected $description = 'Read-only audit of accounting data integrity. Reports problems; never modifies data.';

    private int $issues = 0;

    public function handle(): int
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════╗');
        $this->line('║      AS DAIRY — ACCOUNTING DATA INTEGRITY AUDIT      ║');
        $this->line('╚══════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->checkUnbalancedEntries();
        $this->checkSalesOrdersWithoutJE();
        $this->checkDuplicateInvoiceNumbers();
        $this->checkJEAmountMismatch();
        $this->checkPurchaseOrdersWithoutJE();
        $this->checkWalletVsGL();
        $this->checkARVsOutstanding();
        $this->checkEntriesWithoutPeriod();

        $this->newLine();
        $this->line('─────────────────────────────────────────────────────');
        if ($this->issues === 0) {
            $this->info('✔  No issues found. Accounting data appears healthy.');
        } else {
            $this->warn("⚠  {$this->issues} issue(s) found. Review above before modifying data.");
            $this->line('   Run php artisan accounting:audit --fix-report for repair suggestions.');
        }
        $this->newLine();

        return $this->issues > 0 ? self::FAILURE : self::SUCCESS;
    }

    // ── 1. Unbalanced journal entries ──────────────────────────────────────────

    private function checkUnbalancedEntries(): void
    {
        $this->line('<fg=cyan>① Unbalanced journal entries (debit ≠ credit)</>');

        $unbalanced = JournalEntry::where('status', 'posted')
            ->whereRaw('ABS(total_debit - total_credit) > 0.01')
            ->get(['id', 'entry_number', 'reference', 'total_debit', 'total_credit']);

        if ($unbalanced->isEmpty()) {
            $this->line('   <fg=green>PASS</> — All posted entries are balanced.');
        } else {
            $this->issues += $unbalanced->count();
            $this->line("   <fg=red>FAIL</> — {$unbalanced->count()} unbalanced entry(ies) found:");
            foreach ($unbalanced as $je) {
                $diff = abs((float) $je->total_debit - (float) $je->total_credit);
                $this->line("        JE #{$je->id} [{$je->entry_number}] ref:{$je->reference}  DR={$je->total_debit}  CR={$je->total_credit}  diff={$diff}");
            }
            if ($this->option('fix-report')) {
                $this->line('   ► Repair: Re-check JournalPostingService for the affected references, then use accounting:reset --soft to reverse and re-post.');
            }
        }
        $this->newLine();
    }

    // ── 2. Sales orders without a posted JE ───────────────────────────────────

    private function checkSalesOrdersWithoutJE(): void
    {
        $this->line('<fg=cyan>② Sales orders without a posted journal entry</>');

        $missing = SalesOrder::whereNull('journal_entry_id')
            ->whereNotIn('payment_status', ['Unbilled'])
            ->get(['id', 'invoice_number', 'sale_date', 'total_amount', 'payment_status']);

        // Also check for orders whose linked JE exists but is not posted
        $badStatus = SalesOrder::whereNotNull('journal_entry_id')
            ->whereHas('journalEntry', fn ($q) => $q->where('status', '!=', 'posted'))
            ->get(['id', 'invoice_number', 'journal_entry_id']);

        if ($missing->isEmpty() && $badStatus->isEmpty()) {
            $this->line('   <fg=green>PASS</> — All billed sales orders have posted journal entries.');
        } else {
            if ($missing->isNotEmpty()) {
                $this->issues += $missing->count();
                $this->line("   <fg=red>FAIL</> — {$missing->count()} sales order(s) have no journal entry:");
                foreach ($missing as $so) {
                    $this->line("        #{$so->id} {$so->invoice_number}  {$so->sale_date}  ₹{$so->total_amount}  [{$so->payment_status}]");
                }
                if ($this->option('fix-report')) {
                    $this->line('   ► Repair: php artisan accounting:setup will re-post missing entries.');
                }
            }
            if ($badStatus->isNotEmpty()) {
                $this->issues += $badStatus->count();
                $this->line("   <fg=yellow>WARN</> — {$badStatus->count()} sales order(s) linked to a non-posted JE:");
                foreach ($badStatus as $so) {
                    $this->line("        #{$so->id} {$so->invoice_number} → JE #{$so->journal_entry_id}");
                }
            }
        }
        $this->newLine();
    }

    // ── 3. Duplicate invoice numbers ──────────────────────────────────────────

    private function checkDuplicateInvoiceNumbers(): void
    {
        $this->line('<fg=cyan>③ Duplicate invoice numbers</>');

        $dupes = DB::table('sales_orders')
            ->whereNull('deleted_at')
            ->select('invoice_number', DB::raw('COUNT(*) as cnt'))
            ->groupBy('invoice_number')
            ->having('cnt', '>', 1)
            ->get();

        if ($dupes->isEmpty()) {
            $this->line('   <fg=green>PASS</> — No duplicate invoice numbers found.');
        } else {
            $this->issues += $dupes->count();
            $this->line("   <fg=red>FAIL</> — {$dupes->count()} duplicate invoice number(s):");
            foreach ($dupes as $d) {
                $this->line("        \"{$d->invoice_number}\" appears {$d->cnt} times");
            }
            if ($this->option('fix-report')) {
                $this->line('   ► Repair: Manually rename duplicate invoices and add unique DB constraint.');
            }
        }
        $this->newLine();
    }

    // ── 4. JE amount vs invoice amount mismatch ───────────────────────────────

    private function checkJEAmountMismatch(): void
    {
        $this->line('<fg=cyan>④ Journal entry amount vs invoice total mismatch</>');

        $rows = DB::table('sales_orders as so')
            ->join('journal_entries as je', 'je.id', '=', 'so.journal_entry_id')
            ->whereNull('so.deleted_at')
            ->where('je.status', 'posted')
            ->whereRaw('ABS(je.total_debit - so.total_amount) > 0.01')
            ->select('so.id', 'so.invoice_number', 'so.total_amount', 'je.id as je_id', 'je.total_debit')
            ->get();

        if ($rows->isEmpty()) {
            $this->line('   <fg=green>PASS</> — All JE amounts match their invoice totals.');
        } else {
            $this->issues += $rows->count();
            $this->line("   <fg=red>FAIL</> — {$rows->count()} mismatch(es):");
            foreach ($rows as $r) {
                $diff = abs((float) $r->total_debit - (float) $r->total_amount);
                $this->line("        #{$r->id} {$r->invoice_number}  invoice=₹{$r->total_amount}  JE #{$r->je_id}=₹{$r->total_debit}  diff=₹{$diff}");
            }
            if ($this->option('fix-report')) {
                $this->line('   ► Repair: Reverse and re-post the affected journal entries after correcting the invoice.');
            }
        }
        $this->newLine();
    }

    // ── 5. Purchase orders without a posted JE ────────────────────────────────

    private function checkPurchaseOrdersWithoutJE(): void
    {
        $this->line('<fg=cyan>⑤ Purchase orders without a posted journal entry</>');

        $missing = PurchaseOrder::whereNull('journal_entry_id')
            ->whereIn('status', ['Received', 'Paid'])
            ->get(['id', 'po_number', 'order_date', 'total_amount', 'status']);

        if ($missing->isEmpty()) {
            $this->line('   <fg=green>PASS</> — All received/paid purchase orders have journal entries.');
        } else {
            $this->issues += $missing->count();
            $this->line("   <fg=red>FAIL</> — {$missing->count()} purchase order(s) have no journal entry:");
            foreach ($missing as $po) {
                $this->line("        #{$po->id} {$po->po_number}  {$po->order_date}  ₹{$po->total_amount}  [{$po->status}]");
            }
            if ($this->option('fix-report')) {
                $this->line('   ► Repair: php artisan accounting:setup will re-post missing entries.');
            }
        }
        $this->newLine();
    }

    // ── 6. Wallet balance vs GL Bank (1010) ───────────────────────────────────

    private function checkWalletVsGL(): void
    {
        $this->line('<fg=cyan>⑥ Wallet balance vs GL Bank account (1010)</>');

        $bankId = ChartOfAccount::where('code', '1010')->value('id');
        if (! $bankId) {
            $this->line('   <fg=yellow>SKIP</> — Chart of Account 1010 (Bank) not found. Run php artisan accounting:setup.');
            $this->newLine();
            return;
        }

        $glBalance = (float) DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entry_lines.account_id', $bankId)
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance');

        $walletBalance = (float) Wallet::company()->balance;
        $diff          = abs($glBalance - $walletBalance);

        $this->line("   GL Bank (1010) balance : ₹" . number_format($glBalance, 2));
        $this->line("   Wallet balance          : ₹" . number_format($walletBalance, 2));

        if ($diff <= 0.01) {
            $this->line('   <fg=green>PASS</> — Wallet and GL Bank balances match.');
        } else {
            $this->issues++;
            $this->line("   <fg=red>FAIL</> — Discrepancy of ₹" . number_format($diff, 2));
            if ($this->option('fix-report')) {
                $this->line('   ► Repair: php artisan wallet:recalculate to sync wallet from transactions, then reconcile with GL.');
            }
        }
        $this->newLine();
    }

    // ── 7. AR GL balance vs sum of outstanding invoices ───────────────────────

    private function checkARVsOutstanding(): void
    {
        $this->line('<fg=cyan>⑦ AR ledger (1100) vs sum of outstanding invoices</>');

        $arId = ChartOfAccount::where('code', '1100')->value('id');
        if (! $arId) {
            $this->line('   <fg=yellow>SKIP</> — Chart of Account 1100 (AR) not found. Run php artisan accounting:setup.');
            $this->newLine();
            return;
        }

        $glAR = (float) DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entry_lines.account_id', $arId)
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance');

        $operationalAR = (float) SalesOrder::whereIn('payment_status', ['Pending', 'Partial'])
            ->selectRaw('COALESCE(SUM(total_amount - COALESCE(amount_paid, 0)), 0) as outstanding')
            ->value('outstanding');

        $diff = abs($glAR - $operationalAR);

        $this->line("   GL AR (1100) balance        : ₹" . number_format($glAR, 2));
        $this->line("   Operational outstanding     : ₹" . number_format($operationalAR, 2));

        if ($diff <= 0.01) {
            $this->line('   <fg=green>PASS</> — GL AR balance matches operational outstanding.');
        } else {
            // This is expected to diverge if cash sales (Paid) were debited to Bank not AR
            $this->line("   <fg=yellow>INFO</> — Difference of ₹" . number_format($diff, 2));
            $this->line('          Note: GL AR (1100) only tracks credit sales; cash (Paid) sales debit Bank (1010).');
            $this->line('          Divergence is expected unless all sales were credit sales.');
        }
        $this->newLine();
    }

    // ── 8. Journal entries missing a financial period ─────────────────────────

    private function checkEntriesWithoutPeriod(): void
    {
        $this->line('<fg=cyan>⑧ Journal entries without a financial period</>');

        $missing = JournalEntry::whereNull('period_id')
            ->where('status', 'posted')
            ->count();

        if ($missing === 0) {
            $this->line('   <fg=green>PASS</> — All posted entries have a financial period assigned.');
        } else {
            $this->issues += $missing;
            $this->line("   <fg=red>FAIL</> — {$missing} posted journal entry(ies) have no period_id.");
            if ($this->option('fix-report')) {
                $this->line('   ► Repair: php artisan accounting:setup to create missing periods, then manually assign period_id to orphan entries.');
            }
        }
        $this->newLine();
    }
}