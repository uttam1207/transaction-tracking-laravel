<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use App\Models\FinancialPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\LedgerBalanceService;
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

        // Load all relevant account IDs indexed by code
        $accts = ChartOfAccount::whereIn('code', [
            '1010','2000','2500','3000','3100','3200',
            '4000','4900','5000','5100','5200','5950',
        ])->pluck('id', 'code');

        // Category-aware account mapping (debit_code, credit_code)
        $categoryMap = [
            ['type' => 'credit', 'category' => 'deposit',    'dr' => '1010', 'cr' => '4000'],
            ['type' => 'credit', 'category' => 'investment',  'dr' => '1010', 'cr' => '3000'],
            ['type' => 'credit', 'category' => 'loan',        'dr' => '1010', 'cr' => '2500'],
            ['type' => 'credit', 'category' => 'refund',      'dr' => '1010', 'cr' => '5000'],
            ['type' => 'credit', 'category' => 'transfer',    'dr' => '1010', 'cr' => '4900'],
            ['type' => 'debit',  'category' => 'salary',      'dr' => '5100', 'cr' => '1010'],
            ['type' => 'debit',  'category' => 'purchase',    'dr' => '5000', 'cr' => '1010'],
            ['type' => 'debit',  'category' => 'payment',     'dr' => '2000', 'cr' => '1010'],
            ['type' => 'debit',  'category' => 'withdrawal',  'dr' => '3200', 'cr' => '1010'],
        ];

        $assigned = 0;
        foreach ($categoryMap as $map) {
            $drId = $accts[$map['dr']] ?? null;
            $crId = $accts[$map['cr']] ?? null;
            if (! $drId || ! $crId) continue;

            $txns = Transaction::where('status', 'success')
                ->where('type', $map['type'])
                ->where('category', $map['category'])
                ->whereNull('debit_account_id')
                ->get();

            foreach ($txns as $tx) {
                Transaction::withoutEvents(fn() =>
                    $tx->update(['debit_account_id' => $drId, 'credit_account_id' => $crId])
                );
                $assigned++;
            }
        }

        // Fallback: remaining credit → Bank / Other Income
        $fallbackTxns = Transaction::where('status', 'success')->where('type', 'credit')->whereNull('debit_account_id')->get();
        foreach ($fallbackTxns as $tx) {
            Transaction::withoutEvents(fn() =>
                $tx->update(['debit_account_id' => $accts['1010'] ?? null, 'credit_account_id' => $accts['4900'] ?? null])
            );
            $assigned++;
        }

        // Fallback: remaining debit → Misc Expense / Bank
        $fallbackDebit = Transaction::where('status', 'success')->where('type', 'debit')->whereNull('debit_account_id')->get();
        foreach ($fallbackDebit as $tx) {
            Transaction::withoutEvents(fn() =>
                $tx->update(['debit_account_id' => $accts['5950'] ?? null, 'credit_account_id' => $accts['1010'] ?? null])
            );
            $assigned++;
        }

        $this->info("  Assigned accounts to {$assigned} transactions (category-aware)");

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

        // ── 4. Post existing Sales Orders to ledger ──────────────────────
        $this->info('');
        $this->info('=== Backfilling Sales Orders → Ledger ===');

        $accts = ChartOfAccount::whereIn('code', ['1010','1100','2000','4000','4010','4020','4100','4900','5000'])
            ->pluck('id', 'code');

        $pendingSales = SalesOrder::whereNull('journal_entry_id')->get();
        $this->info("  Sales orders to post: {$pendingSales->count()}");
        $salesPosted = 0;
        $salesFailed = 0;

        foreach ($pendingSales as $sale) {
            try {
                $amount = (float) $sale->total_amount;
                if ($amount <= 0) { $salesFailed++; continue; }

                $revenueCode = match (true) {
                    stripos($sale->item_type, 'milk')      !== false => '4000',
                    stripos($sale->item_type, 'animal')    !== false => '4010',
                    stripos($sale->item_type, 'feed')      !== false => '4020',
                    stripos($sale->item_type, 'franchise') !== false => '4100',
                    default                                          => '4900',
                };
                $debitCode   = $sale->payment_status === 'Paid' ? '1010' : '1100';
                $debitId     = $accts[$debitCode] ?? null;
                $creditId    = $accts[$revenueCode] ?? null;
                if (! $debitId || ! $creditId) { $salesFailed++; continue; }

                $entryDate = $sale->sale_date->toDateString();
                $periodId  = FinancialPeriod::where('start_date', '<=', $entryDate)
                    ->where('end_date', '>=', $entryDate)
                    ->whereIn('status', ['open', 'closed'])
                    ->orderBy('start_date', 'desc')
                    ->value('id');

                if (! $periodId) { $salesFailed++; continue; }

                DB::transaction(function () use ($sale, $entryDate, $periodId, $amount, $debitId, $creditId, $adminId, $ledger) {
                    $entry = JournalEntry::create([
                        'entry_number' => JournalEntry::generateNumber(),
                        'period_id'    => $periodId,
                        'entry_date'   => $entryDate,
                        'reference'    => $sale->invoice_number,
                        'type'         => $sale->payment_status === 'Paid' ? 'receipt' : 'general',
                        'description'  => 'Sale Invoice: ' . $sale->invoice_number . ' — ' . $sale->item_type,
                        'total_debit'  => $amount,
                        'total_credit' => $amount,
                        'status'       => 'posted',
                        'created_by'   => $adminId,
                        'posted_by'    => $adminId,
                        'posted_at'    => now(),
                    ]);
                    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $debitId,  'debit' => $amount, 'credit' => 0, 'description' => $sale->invoice_number]);
                    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $creditId, 'debit' => 0, 'credit' => $amount, 'description' => $sale->invoice_number]);
                    SalesOrder::withoutEvents(fn() => $sale->update(['journal_entry_id' => $entry->id]));
                    $ledger->updateAfterPost($entry->load('lines'));
                });

                $this->line("  <info>✓</info> {$sale->invoice_number}  →  JE posted  [{$entryDate}]");
                $salesPosted++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$sale->invoice_number}: " . $e->getMessage());
                $salesFailed++;
            }
        }
        $this->info("  Posted: {$salesPosted}  |  Failed: {$salesFailed}");

        // ── 5. Post existing Purchase Orders to ledger ───────────────────
        $this->info('');
        $this->info('=== Backfilling Purchase Orders → Ledger ===');

        $pendingPos = PurchaseOrder::whereNull('journal_entry_id')
            ->whereIn('status', ['Received', 'Paid'])
            ->get();
        $this->info("  Purchase orders to post: {$pendingPos->count()}");
        $poPosted = 0;
        $poFailed = 0;

        foreach ($pendingPos as $po) {
            try {
                $amount = (float) $po->total_amount;
                if ($amount <= 0) { $poFailed++; continue; }

                $entryDate = $po->order_date->toDateString();
                $periodId  = FinancialPeriod::where('start_date', '<=', $entryDate)
                    ->where('end_date', '>=', $entryDate)
                    ->whereIn('status', ['open', 'closed'])
                    ->orderBy('start_date', 'desc')
                    ->value('id');

                if (! $periodId) { $poFailed++; continue; }

                $cogsId = $accts['5000'] ?? null;
                $apId   = $accts['2000'] ?? null;
                $bankId = $accts['1010'] ?? null;
                if (! $cogsId || ! $apId || ! $bankId) { $poFailed++; continue; }

                $vendor = $po->vendor?->name ?? 'Vendor';

                if ($po->status === 'Received') {
                    // DR Purchases, CR AP
                    DB::transaction(function () use ($po, $entryDate, $periodId, $amount, $cogsId, $apId, $adminId, $ledger, $vendor) {
                        $entry = JournalEntry::create([
                            'entry_number' => JournalEntry::generateNumber(), 'period_id' => $periodId,
                            'entry_date' => $entryDate, 'reference' => $po->po_number, 'type' => 'purchase',
                            'description' => "Goods Received: {$po->po_number} — {$vendor}",
                            'total_debit' => $amount, 'total_credit' => $amount, 'status' => 'posted',
                            'created_by' => $adminId, 'posted_by' => $adminId, 'posted_at' => now(),
                        ]);
                        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $cogsId, 'debit' => $amount, 'credit' => 0, 'description' => $po->po_number]);
                        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $apId,   'debit' => 0, 'credit' => $amount, 'description' => $po->po_number]);
                        PurchaseOrder::withoutEvents(fn() => $po->update(['journal_entry_id' => $entry->id]));
                        $ledger->updateAfterPost($entry->load('lines'));
                    });
                } else {
                    // Paid: DR Purchases, CR Bank (direct purchase)
                    DB::transaction(function () use ($po, $entryDate, $periodId, $amount, $cogsId, $bankId, $adminId, $ledger, $vendor) {
                        $entry = JournalEntry::create([
                            'entry_number' => JournalEntry::generateNumber(), 'period_id' => $periodId,
                            'entry_date' => $entryDate, 'reference' => $po->po_number, 'type' => 'purchase',
                            'description' => "Direct Purchase: {$po->po_number} — {$vendor}",
                            'total_debit' => $amount, 'total_credit' => $amount, 'status' => 'posted',
                            'created_by' => $adminId, 'posted_by' => $adminId, 'posted_at' => now(),
                        ]);
                        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $cogsId, 'debit' => $amount, 'credit' => 0, 'description' => $po->po_number]);
                        JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $bankId, 'debit' => 0, 'credit' => $amount, 'description' => $po->po_number]);
                        PurchaseOrder::withoutEvents(fn() => $po->update(['journal_entry_id' => $entry->id]));
                        $ledger->updateAfterPost($entry->load('lines'));
                    });
                }

                $this->line("  <info>✓</info> {$po->po_number} ({$po->status})  →  JE posted  [{$entryDate}]");
                $poPosted++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$po->po_number}: " . $e->getMessage());
                $poFailed++;
            }
        }
        $this->info("  Posted: {$poPosted}  |  Failed: {$poFailed}");

        // ── 6. Sync Wallet Balance with Sales + POs ──────────────────────
        $this->info('');
        $this->info('=== Syncing Wallet Balance ===');

        $wallet        = Wallet::company();
        $walletSynced  = 0;
        $walletSkipped = 0;

        // Already-reflected references (by reference field in wallet_transactions)
        $reflected = WalletTransaction::whereNotNull('reference')->pluck('reference')->flip();

        // Sales invoices paid in cash — credit wallet
        $paidSales = SalesOrder::where('payment_status', 'Paid')
            ->whereNotNull('journal_entry_id')
            ->get();

        foreach ($paidSales as $sale) {
            $ref = $sale->invoice_number;
            if (isset($reflected[$ref])) { $walletSkipped++; continue; }
            try {
                $before = (float) $wallet->balance;
                $after  = $before + (float) $sale->total_amount;
                $wallet->transactions()->create([
                    'type'           => 'credit',
                    'amount'         => $sale->total_amount,
                    'balance_before' => $before,
                    'balance_after'  => $after,
                    'description'    => 'Sale: ' . $ref,
                    'reference'      => $ref,
                    'performed_by'   => $adminId,
                ]);
                $wallet->update(['balance' => $after]);
                $wallet->balance = $after;
                $reflected->put($ref, true);
                $walletSynced++;
            } catch (\Throwable $e) {
                $this->warn("  Wallet skip {$ref}: " . $e->getMessage());
            }
        }

        // Payment-received invoices (Pending→Paid) — reference PMT-INV-xxx
        // These are only future; existing paid sales covered above.

        // POs paid directly or via vendor payment — debit wallet
        $paidPos = PurchaseOrder::whereIn('status', ['Paid'])->whereNotNull('journal_entry_id')->get();

        foreach ($paidPos as $po) {
            $ref = $po->po_number; // direct purchase reference
            if (isset($reflected[$ref])) { $walletSkipped++; continue; }
            try {
                $before = (float) $wallet->balance;
                $after  = $before - (float) $po->total_amount;
                $wallet->transactions()->create([
                    'type'           => 'debit',
                    'amount'         => $po->total_amount,
                    'balance_before' => $before,
                    'balance_after'  => $after,
                    'description'    => 'Direct Purchase: ' . $po->po_number,
                    'reference'      => $ref,
                    'performed_by'   => $adminId,
                ]);
                $wallet->update(['balance' => $after]);
                $wallet->balance = $after;
                $reflected->put($ref, true);
                $walletSynced++;
            } catch (\Throwable $e) {
                $this->warn("  Wallet skip {$po->po_number}: " . $e->getMessage());
            }
        }

        // POs that were Received then had separate payment JE
        $paymentPos = PurchaseOrder::where('status', 'Paid')
            ->whereNotNull('payment_journal_entry_id')
            ->get();

        foreach ($paymentPos as $po) {
            $ref = 'PAY-' . $po->po_number;
            if (isset($reflected[$ref])) { $walletSkipped++; continue; }
            try {
                $before = (float) $wallet->balance;
                $after  = $before - (float) $po->total_amount;
                $wallet->transactions()->create([
                    'type'           => 'debit',
                    'amount'         => $po->total_amount,
                    'balance_before' => $before,
                    'balance_after'  => $after,
                    'description'    => 'Vendor Payment: ' . $po->po_number,
                    'reference'      => $ref,
                    'performed_by'   => $adminId,
                ]);
                $wallet->update(['balance' => $after]);
                $wallet->balance = $after;
                $reflected->put($ref, true);
                $walletSynced++;
            } catch (\Throwable $e) {
                $this->warn("  Wallet skip PAY-{$po->po_number}: " . $e->getMessage());
            }
        }

        $wallet->refresh();
        $this->info("  Synced: {$walletSynced}  |  Already reflected: {$walletSkipped}");
        $this->info("  Wallet Balance after sync: ₹" . number_format($wallet->balance, 2));

        // ── Summary ──────────────────────────────────────────────────────
        $this->info('');
        $this->info('=== Summary ===');
        $this->info('  Chart of Accounts : ' . ChartOfAccount::count() . ' accounts');
        $this->info('  Financial Periods : ' . FinancialPeriod::count() . ' periods');
        $this->info('  Journal Entries   : ' . JournalEntry::count() . ' entries');
        $this->info('  Journal Lines     : ' . JournalEntryLine::count() . ' lines');
        $this->info('  Wallet Balance    : ₹' . number_format(Wallet::company()->balance, 2));
        $this->info('');
        $this->info('Balance Sheet, P&L, Ledger, and Wallet are now in sync.');

        return self::SUCCESS;
    }
}
