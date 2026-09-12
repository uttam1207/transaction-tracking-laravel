<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Models\FinancialPeriod;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupAccounting extends Command
{
    protected $signature   = 'accounting:setup {--force : Re-run even if accounts already exist}';
    protected $description = 'Seed Chart of Accounts, Financial Periods, and post existing transactions to the ledger';

    public function handle(): int
    {
        // ── 1. Chart of Accounts ─────────────────────────────────────────
        $this->info('');
        $this->info('=== Chart of Accounts ===');

        if (ChartOfAccount::count() > 0 && ! $this->option('force')) {
            $this->warn('  Already seeded (' . ChartOfAccount::count() . ' accounts). Use --force to re-run.');
        } else {
            $accounts = [
                // Assets
                ['code' => '1000', 'name' => 'Cash on Hand',            'type' => 'asset',     'sub_type' => 'current_asset'],
                ['code' => '1010', 'name' => 'Bank Account',             'type' => 'asset',     'sub_type' => 'current_asset'],
                ['code' => '1020', 'name' => 'Petty Cash',               'type' => 'asset',     'sub_type' => 'current_asset'],
                ['code' => '1100', 'name' => 'Accounts Receivable',      'type' => 'asset',     'sub_type' => 'current_asset'],
                ['code' => '1110', 'name' => 'Advance to Suppliers',     'type' => 'asset',     'sub_type' => 'current_asset'],
                ['code' => '1200', 'name' => 'Milk Inventory',           'type' => 'asset',     'sub_type' => 'current_asset'],
                ['code' => '1210', 'name' => 'Feed & Fodder Inventory',  'type' => 'asset',     'sub_type' => 'current_asset'],
                ['code' => '1220', 'name' => 'Dairy Products Inventory', 'type' => 'asset',     'sub_type' => 'current_asset'],
                ['code' => '1300', 'name' => 'Prepaid Expenses',         'type' => 'asset',     'sub_type' => 'current_asset'],
                ['code' => '1500', 'name' => 'Equipment & Machinery',    'type' => 'asset',     'sub_type' => 'fixed_asset'],
                ['code' => '1510', 'name' => 'Vehicles',                 'type' => 'asset',     'sub_type' => 'fixed_asset'],
                ['code' => '1520', 'name' => 'Furniture & Fixtures',     'type' => 'asset',     'sub_type' => 'fixed_asset'],
                ['code' => '1600', 'name' => 'Land & Buildings',         'type' => 'asset',     'sub_type' => 'fixed_asset'],
                // Liabilities
                ['code' => '2000', 'name' => 'Accounts Payable',         'type' => 'liability', 'sub_type' => 'current_liability'],
                ['code' => '2010', 'name' => 'Advance from Customers',   'type' => 'liability', 'sub_type' => 'current_liability'],
                ['code' => '2100', 'name' => 'Salaries Payable',         'type' => 'liability', 'sub_type' => 'current_liability'],
                ['code' => '2200', 'name' => 'GST Payable',              'type' => 'liability', 'sub_type' => 'current_liability'],
                ['code' => '2210', 'name' => 'TDS Payable',              'type' => 'liability', 'sub_type' => 'current_liability'],
                ['code' => '2300', 'name' => 'Short-Term Loans',         'type' => 'liability', 'sub_type' => 'current_liability'],
                ['code' => '2500', 'name' => 'Long-Term Loans',          'type' => 'liability', 'sub_type' => 'long_term_liability'],
                ['code' => '2900', 'name' => 'Other Liabilities',        'type' => 'liability', 'sub_type' => 'current_liability'],
                // Equity
                ['code' => '3000', 'name' => "Owner's Capital",          'type' => 'equity',    'sub_type' => 'capital'],
                ['code' => '3100', 'name' => 'Retained Earnings',        'type' => 'equity',    'sub_type' => 'retained_earnings'],
                ['code' => '3200', 'name' => 'Drawings / Withdrawals',   'type' => 'equity',    'sub_type' => 'drawings'],
                // Revenue
                ['code' => '4000', 'name' => 'Milk Sales Revenue',       'type' => 'revenue',   'sub_type' => 'sales'],
                ['code' => '4010', 'name' => 'Dairy Products Sales',     'type' => 'revenue',   'sub_type' => 'sales'],
                ['code' => '4020', 'name' => 'Feed Sales Revenue',       'type' => 'revenue',   'sub_type' => 'sales'],
                ['code' => '4100', 'name' => 'Service Income',           'type' => 'revenue',   'sub_type' => 'other_income'],
                ['code' => '4900', 'name' => 'Other Income',             'type' => 'revenue',   'sub_type' => 'other_income'],
                // Expenses
                ['code' => '5000', 'name' => 'Cost of Goods Sold',       'type' => 'expense',   'sub_type' => 'cost_of_sales'],
                ['code' => '5100', 'name' => 'Salaries & Wages',         'type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5200', 'name' => 'Feed & Fodder Expenses',   'type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5210', 'name' => 'Veterinary Expenses',      'type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5300', 'name' => 'Electricity & Utilities',  'type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5310', 'name' => 'Water Charges',            'type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5400', 'name' => 'Rent & Lease',             'type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5500', 'name' => 'Transportation & Fuel',    'type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5600', 'name' => 'Repair & Maintenance',     'type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5700', 'name' => 'Marketing & Advertisement','type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5800', 'name' => 'Bank Charges & Fees',      'type' => 'expense',   'sub_type' => 'financial_expense'],
                ['code' => '5810', 'name' => 'Loan Interest',            'type' => 'expense',   'sub_type' => 'financial_expense'],
                ['code' => '5900', 'name' => 'Depreciation',             'type' => 'expense',   'sub_type' => 'operating_expense'],
                ['code' => '5950', 'name' => 'Miscellaneous Expenses',   'type' => 'expense',   'sub_type' => 'operating_expense'],
            ];

            $created = 0;
            $skipped = 0;
            foreach ($accounts as $a) {
                $exists = ChartOfAccount::where('code', $a['code'])->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }
                ChartOfAccount::create(array_merge($a, [
                    'is_active'            => true,
                    'allow_direct_posting' => true,
                    'description'          => null,
                    'parent_id'            => null,
                ]));
                $created++;
            }

            $this->info("  Created: {$created}  |  Skipped (already existed): {$skipped}");
            $this->info('  Total accounts now: ' . ChartOfAccount::count());
        }

        // ── 2. Financial Periods ─────────────────────────────────────────
        $this->info('');
        $this->info('=== Financial Periods ===');

        $adminId = \App\Models\User::where('role', 'super_admin')->value('id') ?? 1;

        $periods = [
            ['name' => 'FY 2024-25', 'type' => 'year', 'start_date' => '2024-04-01', 'end_date' => '2025-03-31', 'status' => 'closed'],
            ['name' => 'FY 2025-26', 'type' => 'year', 'start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'closed'],
            ['name' => 'FY 2026-27', 'type' => 'year', 'start_date' => '2026-04-01', 'end_date' => '2027-03-31', 'status' => 'open'],
        ];

        foreach ($periods as $p) {
            $exists = FinancialPeriod::where('name', $p['name'])->exists();
            if ($exists) {
                $this->warn("  Skipped (exists): {$p['name']}");
                continue;
            }
            FinancialPeriod::create(array_merge($p, ['created_by' => $adminId]));
            $this->info("  Created: {$p['name']} ({$p['status']})");
        }

        $this->info('  Total periods now: ' . FinancialPeriod::count());

        // ── 3. Post existing transactions to ledger ──────────────────────
        $this->info('');
        $this->info('=== Posting Existing Transactions to Ledger ===');

        $bankId    = ChartOfAccount::where('code', '1010')->value('id');
        $revenueId = ChartOfAccount::where('code', '4000')->value('id');
        $expenseId = ChartOfAccount::where('code', '5000')->value('id');

        if (! $bankId || ! $revenueId || ! $expenseId) {
            $this->error('  Cannot resolve account IDs — ensure COA was created first.');
            return self::FAILURE;
        }

        $this->line("  Bank Account: #{$bankId}  |  Revenue: #{$revenueId}  |  Expense: #{$expenseId}");

        // Assign accounts to unlinked success transactions
        $creditTxns = Transaction::where('status', 'success')
            ->where('type', 'credit')
            ->whereNull('debit_account_id')
            ->get();

        foreach ($creditTxns as $tx) {
            Transaction::withoutEvents(function () use ($tx, $bankId, $revenueId) {
                $tx->update(['debit_account_id' => $bankId, 'credit_account_id' => $revenueId]);
            });
        }
        $this->info("  Assigned accounts to {$creditTxns->count()} credit (income) transactions");

        $debitTxns = Transaction::where('status', 'success')
            ->where('type', 'debit')
            ->whereNull('debit_account_id')
            ->get();

        foreach ($debitTxns as $tx) {
            Transaction::withoutEvents(function () use ($tx, $expenseId, $bankId) {
                $tx->update(['debit_account_id' => $expenseId, 'credit_account_id' => $bankId]);
            });
        }
        $this->info("  Assigned accounts to {$debitTxns->count()} debit (expense) transactions");

        // Post directly — bypass the observer to avoid auth()->id() being null in CLI context
        $pending = Transaction::where('status', 'success')
            ->whereNotNull('debit_account_id')
            ->whereNotNull('credit_account_id')
            ->whereNull('journal_entry_id')
            ->get();

        $this->info("  Transactions to post: {$pending->count()}");

        $posted = 0;
        $failed = 0;
        $ledger = app(\App\Services\LedgerBalanceService::class);

        foreach ($pending as $tx) {
            try {
                $entryDate = $tx->processed_at
                    ? \Carbon\Carbon::parse($tx->processed_at)->toDateString()
                    : $tx->created_at->toDateString();

                // Find financial period covering this date
                $periodId = FinancialPeriod::where('start_date', '<=', $entryDate)
                    ->where('end_date', '>=', $entryDate)
                    ->whereIn('status', ['open', 'closed'])
                    ->orderBy('start_date', 'desc')
                    ->value('id');

                $amount = (float) $tx->net_amount;

                DB::transaction(function () use ($tx, $entryDate, $periodId, $amount, $adminId, $ledger) {
                    $entry = \App\Models\JournalEntry::create([
                        'entry_number' => \App\Models\JournalEntry::generateNumber(),
                        'period_id'    => $periodId,
                        'entry_date'   => $entryDate,
                        'reference'    => $tx->transaction_id,
                        'type'         => 'general',
                        'description'  => ucfirst($tx->category) . ' — ' . $tx->transaction_id,
                        'total_debit'  => $amount,
                        'total_credit' => $amount,
                        'status'       => 'posted',
                        'created_by'   => $adminId,
                        'posted_by'    => $adminId,
                        'posted_at'    => now(),
                    ]);

                    \App\Models\JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id'       => $tx->debit_account_id,
                        'debit'            => $amount,
                        'credit'           => 0,
                        'description'      => $tx->transaction_id,
                    ]);

                    \App\Models\JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id'       => $tx->credit_account_id,
                        'debit'            => 0,
                        'credit'           => $amount,
                        'description'      => $tx->transaction_id,
                    ]);

                    // Link back to transaction without firing events
                    Transaction::withoutEvents(fn() =>
                        $tx->update(['journal_entry_id' => $entry->id])
                    );

                    // Update ledger balance cache
                    $ledger->updateAfterPost($entry->load('lines'));
                });

                $tx->refresh();
                $this->line("  <info>✓</info> {$tx->transaction_id}  →  JE #{$tx->journal_entry_id}  [{$entryDate}]");
                $posted++;

            } catch (\Throwable $e) {
                $this->error("  ✗ {$tx->transaction_id}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info('');
        $this->info("  Posted: {$posted}  |  Failed: {$failed}");

        // ── Summary ──────────────────────────────────────────────────────
        $this->info('');
        $this->info('=== Summary ===');
        $this->info('  Chart of Accounts : ' . ChartOfAccount::count() . ' accounts');
        $this->info('  Financial Periods : ' . FinancialPeriod::count() . ' periods');
        $this->info('  Journal Entries   : ' . \App\Models\JournalEntry::count() . ' entries');
        $this->info('  Journal Lines     : ' . \App\Models\JournalEntryLine::count() . ' lines');
        $this->info('');
        $this->info('Balance Sheet and P&L reports are now ready.');

        return self::SUCCESS;
    }
}
