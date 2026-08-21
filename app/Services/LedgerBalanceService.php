<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\FinancialPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LedgerBalance;

class LedgerBalanceService
{
    /**
     * Recalculate ledger balances for all accounts that have posted entries in a period.
     */
    public function recalculateForPeriod(FinancialPeriod $period): void
    {
        $accountIds = JournalEntryLine::whereHas('journalEntry', function ($q) use ($period) {
            $q->where('status', 'posted')->where('period_id', $period->id);
        })->distinct()->pluck('account_id');

        foreach ($accountIds as $accountId) {
            $this->recalculateAccountPeriod($accountId, $period);
        }
    }

    /**
     * Recalculate ledger balance for one account in one period.
     */
    public function recalculateAccountPeriod(int $accountId, FinancialPeriod $period): LedgerBalance
    {
        $totals = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($period) {
                $q->where('status', 'posted')->where('period_id', $period->id);
            })
            ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->first();

        $totalDebit  = (float) $totals->total_debit;
        $totalCredit = (float) $totals->total_credit;
        $opening     = $this->getOpeningBalance($accountId, $period);
        $account     = ChartOfAccount::find($accountId);
        $closing     = $this->calculateClosing($account->type, $opening, $totalDebit, $totalCredit);

        return LedgerBalance::updateOrCreate(
            ['account_id' => $accountId, 'period_id' => $period->id],
            [
                'opening_balance' => $opening,
                'total_debit'     => $totalDebit,
                'total_credit'    => $totalCredit,
                'closing_balance' => $closing,
            ]
        );
    }

    /**
     * Update ledger balances after a journal entry is posted.
     */
    public function updateAfterPost(JournalEntry $entry): void
    {
        if (!$entry->period_id) return;

        $period = FinancialPeriod::find($entry->period_id);
        if (!$period) return;

        $entry->load('lines');
        $accountIds = $entry->lines->pluck('account_id')->unique();

        foreach ($accountIds as $accountId) {
            $this->recalculateAccountPeriod($accountId, $period);
        }
    }

    /**
     * Get the opening balance from the closest prior period's closing balance.
     */
    public function getOpeningBalance(int $accountId, FinancialPeriod $period): float
    {
        $prev = LedgerBalance::where('account_id', $accountId)
            ->whereHas('period', fn($q) => $q->where('end_date', '<', $period->start_date))
            ->orderByDesc('period_id')
            ->first();

        return $prev ? (float) $prev->closing_balance : 0.00;
    }

    /**
     * Normal balance rules:
     *   Asset / Expense   → debit increases balance  → closing = opening + debit - credit
     *   Liability / Equity / Revenue → credit increases → closing = opening + credit - debit
     */
    public function calculateClosing(string $type, float $opening, float $debit, float $credit): float
    {
        return in_array($type, ['asset', 'expense'])
            ? $opening + $debit - $credit
            : $opening + $credit - $debit;
    }

    /**
     * Generate trial balance data: one row per account with cumulative posted totals.
     * Optionally filtered to a specific period.
     */
    public function trialBalance(?int $periodId = null): \Illuminate\Support\Collection
    {
        $query = JournalEntryLine::with('account')
            ->whereHas('journalEntry', function ($q) use ($periodId) {
                $q->where('status', 'posted');
                if ($periodId) {
                    $q->where('period_id', $periodId);
                }
            })
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->groupBy('account_id');

        return $query->get()->map(function ($row) {
            $account = $row->account;
            return (object) [
                'account'      => $account,
                'total_debit'  => (float) $row->total_debit,
                'total_credit' => (float) $row->total_credit,
            ];
        })->sortBy('account.code')->values();
    }

    /**
     * General ledger lines for a specific account, optionally within a period/date range.
     */
    public function generalLedger(int $accountId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $account = ChartOfAccount::findOrFail($accountId);

        $query = JournalEntryLine::with('journalEntry.period', 'journalEntry.createdBy')
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'posted');
                if ($dateFrom) $q->whereDate('entry_date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('entry_date', '<=', $dateTo);
            })
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->select('journal_entry_lines.*');

        $lines   = $query->get();
        $balance = 0.0;
        $rows    = [];

        foreach ($lines as $line) {
            $debit  = (float) $line->debit;
            $credit = (float) $line->credit;

            if (in_array($account->type, ['asset', 'expense'])) {
                $balance += $debit - $credit;
            } else {
                $balance += $credit - $debit;
            }

            $rows[] = [
                'date'         => $line->journalEntry->entry_date,
                'entry_number' => $line->journalEntry->entry_number,
                'description'  => $line->description ?: $line->journalEntry->description,
                'debit'        => $debit,
                'credit'       => $credit,
                'balance'      => $balance,
                'entry'        => $line->journalEntry,
            ];
        }

        return ['account' => $account, 'rows' => $rows, 'closing_balance' => $balance];
    }

    /**
     * Profit & Loss: Revenue - Expenses for posted entries in period (or all time).
     */
    public function profitAndLoss(?int $periodId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $revenue  = $this->sumByType('revenue',  $periodId, $dateFrom, $dateTo);
        $expenses = $this->sumByType('expense',  $periodId, $dateFrom, $dateTo);

        $netRevenue   = $revenue->sum('net');
        $netExpenses  = $expenses->sum('net');
        $netIncome    = $netRevenue - $netExpenses;

        return compact('revenue', 'expenses', 'netRevenue', 'netExpenses', 'netIncome');
    }

    /**
     * Balance Sheet: Assets = Liabilities + Equity at end of period.
     */
    public function balanceSheet(?int $periodId = null): array
    {
        $assets      = $this->sumByType('asset',     $periodId);
        $liabilities = $this->sumByType('liability', $periodId);
        $equity      = $this->sumByType('equity',    $periodId);

        $totalAssets      = $assets->sum('net');
        $totalLiabilities = $liabilities->sum('net');
        $totalEquity      = $equity->sum('net');

        return compact('assets', 'liabilities', 'equity', 'totalAssets', 'totalLiabilities', 'totalEquity');
    }

    /**
     * Sum journal entry lines by account type, returning account-level net amounts.
     */
    private function sumByType(string $type, ?int $periodId = null, ?string $dateFrom = null, ?string $dateTo = null): \Illuminate\Support\Collection
    {
        $rows = JournalEntryLine::with('account')
            ->whereHas('account', fn($q) => $q->where('type', $type))
            ->whereHas('journalEntry', function ($q) use ($periodId, $dateFrom, $dateTo) {
                $q->where('status', 'posted');
                if ($periodId) $q->where('period_id', $periodId);
                if ($dateFrom) $q->whereDate('entry_date', '>=', $dateFrom);
                if ($dateTo)   $q->whereDate('entry_date', '<=', $dateTo);
            })
            ->selectRaw('account_id, COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(credit), 0) as total_credit')
            ->groupBy('account_id')
            ->get();

        return $rows->map(function ($row) use ($type) {
            $debit  = (float) $row->total_debit;
            $credit = (float) $row->total_credit;
            // Net = normal balance side minus contra side
            $net = in_array($type, ['asset', 'expense'])
                ? $debit - $credit
                : $credit - $debit;

            return (object) [
                'account' => $row->account,
                'debit'   => $debit,
                'credit'  => $credit,
                'net'     => $net,
            ];
        })->sortBy('account.code')->values();
    }
}
