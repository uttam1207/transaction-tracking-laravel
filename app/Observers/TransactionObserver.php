<?php

namespace App\Observers;

use App\Models\FinancialPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Transaction;
use App\Services\LedgerBalanceService;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{
    public function __construct(protected LedgerBalanceService $ledger) {}

    /**
     * When a transaction is updated, check if the status changed to/from 'success'
     * and create or reverse the corresponding journal entry.
     */
    public function updated(Transaction $transaction): void
    {
        $statusChanged = $transaction->wasChanged('status');

        // ── Posting: transaction just became 'success' ────────────────────
        if ($statusChanged
            && $transaction->status === 'success'
            && $transaction->getOriginal('status') !== 'success'
            && $transaction->debit_account_id
            && $transaction->credit_account_id
            && ! $transaction->journal_entry_id          // not already posted
        ) {
            $this->postToLedger($transaction);
            return;
        }

        // ── Reversal: transaction just became 'reversed' ──────────────────
        if ($statusChanged
            && $transaction->status === 'reversed'
            && $transaction->getOriginal('status') === 'success'
            && $transaction->journal_entry_id
        ) {
            $this->reverseInLedger($transaction);
        }
    }

    /**
     * When a transaction is created directly with status='success' and accounts set,
     * post it immediately.
     */
    public function created(Transaction $transaction): void
    {
        if ($transaction->status === 'success'
            && $transaction->debit_account_id
            && $transaction->credit_account_id
        ) {
            $this->postToLedger($transaction);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function postToLedger(Transaction $transaction): void
    {
        $entryDate = $transaction->processed_at
            ? $transaction->processed_at->toDateString()
            : $transaction->created_at->toDateString();

        $periodId = $this->resolvePeriodId($entryDate);

        $amount = (float) $transaction->net_amount;
        $type   = $this->resolveJournalType($transaction->category);

        DB::transaction(function () use ($transaction, $entryDate, $periodId, $amount, $type) {
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
                'account_id'       => $transaction->debit_account_id,
                'debit'            => $amount,
                'credit'           => 0,
                'description'      => $transaction->description ?: $transaction->transaction_id,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $transaction->credit_account_id,
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

    /** Find the open financial period that covers the given date, or null. */
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