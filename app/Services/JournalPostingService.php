<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\EmployeeSalary;
use App\Models\Expense;
use App\Models\FinancialPeriod;
use App\Models\FixedAsset;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class JournalPostingService
{
    public function __construct(protected LedgerBalanceService $ledger) {}

    // ── Expense Auto-posting ──────────────────────────────────────────────────

    public function postExpense(Expense $expense): ?JournalEntry
    {
        // Debit: first postable expense account
        $expenseAccount = ChartOfAccount::postable()->where('type', 'expense')->first();
        // Credit: first postable asset account (cash/bank)
        $cashAccount = ChartOfAccount::postable()->where('type', 'asset')->first();

        if (!$expenseAccount || !$cashAccount) return null;

        $period = $this->periodFor($expense->expense_date);

        return DB::transaction(function () use ($expense, $expenseAccount, $cashAccount, $period) {
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'period_id'    => $period?->id,
                'entry_date'   => $expense->expense_date,
                'reference'    => 'EXP-' . $expense->id,
                'type'         => 'payment',
                'description'  => 'Expense: ' . ($expense->description ?: $expense->category?->name),
                'total_debit'  => $expense->amount,
                'total_credit' => $expense->amount,
                'status'       => 'posted',
                'created_by'   => auth()->id() ?? 1,
                'posted_by'    => auth()->id() ?? 1,
                'posted_at'    => now(),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $expenseAccount->id,
                'debit'            => $expense->amount,
                'credit'           => 0,
                'description'      => $expense->description,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $cashAccount->id,
                'debit'            => 0,
                'credit'           => $expense->amount,
                'description'      => $expense->description,
            ]);

            if ($period) $this->ledger->updateAfterPost($entry->load('lines'));

            return $entry;
        });
    }

    // ── Salary Auto-posting ───────────────────────────────────────────────────

    public function postSalary(EmployeeSalary $salary): ?JournalEntry
    {
        $salaryAccount = ChartOfAccount::postable()
            ->where('type', 'expense')
            ->where('name', 'like', '%salary%')
            ->first()
            ?? ChartOfAccount::postable()->where('type', 'expense')->first();

        $cashAccount = ChartOfAccount::postable()->where('type', 'asset')->first();

        if (!$salaryAccount || !$cashAccount) return null;

        $date   = $salary->payment_date ?? now()->toDateString();
        $period = $this->periodFor($date);
        $name   = $salary->employee?->user?->name ?? 'Employee #' . $salary->employee_id;

        return DB::transaction(function () use ($salary, $salaryAccount, $cashAccount, $period, $date, $name) {
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'period_id'    => $period?->id,
                'entry_date'   => $date,
                'reference'    => 'SAL-' . $salary->id,
                'type'         => 'payroll',
                'description'  => "Salary: {$name} ({$salary->month_label} {$salary->year})",
                'total_debit'  => $salary->net_salary,
                'total_credit' => $salary->net_salary,
                'status'       => 'posted',
                'created_by'   => auth()->id() ?? 1,
                'posted_by'    => auth()->id() ?? 1,
                'posted_at'    => now(),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $salaryAccount->id,
                'debit'            => $salary->net_salary,
                'credit'           => 0,
                'description'      => "Net salary — {$name}",
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $cashAccount->id,
                'debit'            => 0,
                'credit'           => $salary->net_salary,
                'description'      => "Net salary — {$name}",
            ]);

            if ($period) $this->ledger->updateAfterPost($entry->load('lines'));

            return $entry;
        });
    }

    // ── Fixed Asset Depreciation Auto-posting ─────────────────────────────────

    public function postDepreciation(FixedAsset $asset, int $year): ?JournalEntry
    {
        if ($asset->depreciation_method === 'none') return null;
        if ($asset->annual_depreciation <= 0) return null;

        $deprExpenseAccount = ChartOfAccount::postable()
            ->where('type', 'expense')
            ->where('name', 'like', '%depreciation%')
            ->first()
            ?? ChartOfAccount::postable()->where('type', 'expense')->first();

        $accumDeprAccount = ChartOfAccount::postable()
            ->where('type', 'asset')
            ->where('name', 'like', '%accumulated%')
            ->first()
            ?? ChartOfAccount::postable()->where('type', 'asset')->first();

        if (!$deprExpenseAccount || !$accumDeprAccount) return null;

        $entryDate = now()->setYear($year)->endOfYear()->toDateString();
        $period    = $this->periodFor($entryDate);
        $amount    = $asset->annual_depreciation;

        return DB::transaction(function () use ($asset, $deprExpenseAccount, $accumDeprAccount, $period, $entryDate, $year, $amount) {
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'period_id'    => $period?->id,
                'entry_date'   => $entryDate,
                'reference'    => 'DEP-' . $asset->asset_code . '-' . $year,
                'type'         => 'depreciation',
                'description'  => "Depreciation: {$asset->name} (FY {$year})",
                'total_debit'  => $amount,
                'total_credit' => $amount,
                'status'       => 'posted',
                'created_by'   => auth()->id() ?? 1,
                'posted_by'    => auth()->id() ?? 1,
                'posted_at'    => now(),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $deprExpenseAccount->id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => "Annual depreciation — {$asset->name}",
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $accumDeprAccount->id,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => "Accumulated depreciation — {$asset->name}",
            ]);

            if ($period) $this->ledger->updateAfterPost($entry->load('lines'));

            return $entry;
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function periodFor(string|\DateTimeInterface $date): ?FinancialPeriod
    {
        return FinancialPeriod::where('status', 'open')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }
}
