<?php

namespace App\Observers;

use App\Models\ChartOfAccount;
use App\Models\FinancialPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Transaction;
use App\Services\LedgerBalanceService;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{
    public function __construct(protected LedgerBalanceService $ledger) {}

    public function updated(Transaction $transaction): void
    {
        $statusChanged = $transaction->wasChanged('status');

        // Posting: transaction just became 'success'
        if ($statusChanged
            && $transaction->status === 'success'
            && $transaction->getOriginal('status') !== 'success'
            && ! $transaction->journal_entry_id
        ) {
            $this->postToLedger($transaction);
            return;
        }

        // Reversal: transaction just became 'reversed'
        if ($statusChanged
            && $transaction->status === 'reversed'
            && $transaction->getOriginal('status') === 'success'
            && $transaction->journal_entry_id
        ) {
            $this->reverseInLedger($transaction);
        }
    }

    public function created(Transaction $transaction): void
    {
        if ($transaction->status === 'success') {
            $this->postToLedger($transaction);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function postToLedger(Transaction $transaction): void
    {
        // Resolve debit/credit accounts — use manually set or auto-assign from category
        [$debitId, $creditId] = $this->resolveAccounts($transaction);
        if (! $debitId || ! $creditId) return;

        $entryDate = $transaction->processed_at
            ? $transaction->processed_at->toDateString()
            : $transaction->created_at->toDateString();

        $periodId = $this->resolvePeriodId($entryDate);
        $amount   = (float) $transaction->net_amount;
        $type     = $this->resolveJournalType($transaction->category);

        DB::transaction(function () use ($transaction, $entryDate, $periodId, $amount, $type, $debitId, $creditId) {
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'period_id'    => $periodId,
                'entry_date'   => $entryDate,
                'reference'    => $transaction->transaction_id,
                'type'         => $type,
                'description'  => $transaction->description
                    ?: ucfirst($transaction->category) . ' — ' . $transaction->transaction_id,
                'total_debit'  => $amount,
                'total_credit' => $amount,
                'status'       => 'posted',
                'created_by'   => auth()->id(),
                'posted_by'    => auth()->id(),
                'posted_at'    => now(),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $debitId,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => $transaction->description ?: $transaction->transaction_id,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $creditId,
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => $transaction->description ?: $transaction->transaction_id,
            ]);

            // Link journal entry back to transaction (skip observer loop)
            Transaction::withoutEvents(fn() =>
                $transaction->update(['journal_entry_id' => $entry->id])
            );

            // Update ledger balances
            $this->ledger->updateAfterPost($entry->load('lines'));
        });
    }

    private function reverseInLedger(Transaction $transaction): void
    {
        $original = JournalEntry::with('lines')->find($transaction->journal_entry_id);
        if (! $original || $original->status !== 'posted') {
            return;
        }

        DB::transaction(function () use ($transaction, $original) {
            $reversal = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'period_id'    => $original->period_id,
                'entry_date'   => now()->toDateString(),
                'reference'    => 'REV-' . $transaction->transaction_id,
                'type'         => $original->type,
                'description'  => 'Reversal of ' . $transaction->transaction_id,
                'total_debit'  => $original->total_credit,
                'total_credit' => $original->total_debit,
                'status'       => 'posted',
                'created_by'   => auth()->id(),
                'posted_by'    => auth()->id(),
                'posted_at'    => now(),
                'reversal_of'  => $original->id,
            ]);

            foreach ($original->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversal->id,
                    'account_id'       => $line->account_id,
                    'debit'            => $line->credit,
                    'credit'           => $line->debit,
                    'description'      => 'Reversal: ' . ($line->description ?? $transaction->transaction_id),
                ]);
            }

            $original->update(['status' => 'reversed']);

            $this->ledger->updateAfterPost($reversal->load('lines'));
        });
    }

    /**
     * Resolve debit and credit account IDs.
     * Uses manually set accounts if available, otherwise auto-assigns from category/type.
     */
    private function resolveAccounts(Transaction $transaction): array
    {
        if ($transaction->debit_account_id && $transaction->credit_account_id) {
            return [$transaction->debit_account_id, $transaction->credit_account_id];
        }

        // Auto-assign account codes based on category and transaction type
        $codes = match(true) {
            // Credit transactions (money received)
            $transaction->type === 'credit' && $transaction->category === 'deposit'    => ['1010', '4000'],
            $transaction->type === 'credit' && $transaction->category === 'investment' => ['1010', '3000'],
            $transaction->type === 'credit' && $transaction->category === 'loan'       => ['1010', '2500'],
            $transaction->type === 'credit' && $transaction->category === 'refund'     => ['1010', '5000'],
            $transaction->type === 'credit' && $transaction->category === 'transfer'   => ['1010', '4900'],
            // Debit transactions (money paid out)
            $transaction->type === 'debit'  && $transaction->category === 'salary'     => ['5100', '1010'],
            $transaction->type === 'debit'  && $transaction->category === 'purchase'   => ['5000', '1010'],
            $transaction->type === 'debit'  && $transaction->category === 'payment'    => ['2000', '1010'],
            $transaction->type === 'debit'  && $transaction->category === 'withdrawal' => ['3200', '1010'],
            // Fallbacks
            $transaction->type === 'credit' => ['1010', '4900'],
            $transaction->type === 'debit'  => ['5950', '1010'],
            default                         => [null, null],
        };

        if (! $codes[0] || ! $codes[1]) return [null, null];

        $debitId  = ChartOfAccount::where('code', $codes[0])->value('id');
        $creditId = ChartOfAccount::where('code', $codes[1])->value('id');

        if (! $debitId || ! $creditId) return [null, null];

        // Persist the resolved accounts for future reference
        Transaction::withoutEvents(fn() =>
            $transaction->update(['debit_account_id' => $debitId, 'credit_account_id' => $creditId])
        );

        return [$debitId, $creditId];
    }

    /** Find the financial period that covers the given date. */
    private function resolvePeriodId(string $date): ?int
    {
        $period = FinancialPeriod::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->whereIn('status', ['open', 'closed'])
            ->orderBy('start_date', 'desc')
            ->first();

        return $period?->id;
    }

    /** Map transaction category to journal entry type. */
    private function resolveJournalType(string $category): string
    {
        return match ($category) {
            'salary'     => 'payroll',
            'purchase'   => 'purchase',
            'payment'    => 'payment',
            'deposit'    => 'receipt',
            'withdrawal' => 'payment',
            'refund'     => 'adjustment',
            default      => 'general',
        };
    }
}