<?php

namespace App\Observers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\SalesOrder;
use App\Models\Wallet;
use App\Models\User;
use App\Services\JournalPostingService;
use App\Services\LedgerBalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Double-entry rules for Sales Invoices:
 *
 * On CREATE (cash sale — Paid):              DR Bank (1010)            CR Sales Revenue (4xxx)
 * On CREATE (credit sale — Pending/Partial): DR Accounts Receivable    CR Sales Revenue (4xxx)
 * On UPDATE Pending/Partial → Paid:          DR Bank (1010)            CR Accounts Receivable
 * On CORRECTION (amount change):             Reverse old JE            Re-post with corrected amount
 *
 * All operations are atomic (DB::transaction).
 * Idempotency is enforced by checking journal_entry_id before posting.
 */
class SalesOrderObserver
{
    public function __construct(
        protected JournalPostingService $poster,
        protected LedgerBalanceService  $ledger,
    ) {}

    public function created(SalesOrder $salesOrder): void
    {
        $this->postSaleInvoice($salesOrder);
    }

    public function updated(SalesOrder $salesOrder): void
    {
        $amountChanged = $salesOrder->wasChanged([
            'total_amount', 'quantity', 'rate', 'fat_percentage', 'fat_rate',
        ]);

        $statusChangedToPaid = $salesOrder->wasChanged('payment_status')
            && $salesOrder->payment_status === 'Paid'
            && in_array($salesOrder->getOriginal('payment_status'), ['Pending', 'Partial', 'Unbilled']);

        $hasJe = (bool) $salesOrder->journal_entry_id;

        // Guard: nothing to do if no JE was ever posted
        if (! $hasJe) {
            // Edge case: Unbilled → Paid or Unbilled → Pending with no JE
            // Let postSaleInvoice handle it on status change to billable state
            return;
        }

        // ── Case 1: Amount changed (with or without a simultaneous status-to-Paid change) ──
        // correctPostedInvoice() reverses the old JE and re-posts using current state,
        // so it covers both "amount only" and "amount + status" together.
        if ($amountChanged) {
            $this->correctPostedInvoice($salesOrder);
            return;
        }

        // ── Case 2: Only payment status changed to Paid (amount unchanged) ──
        // Post a payment receipt JE: DR Bank, CR Accounts Receivable.
        if ($statusChangedToPaid) {
            $this->postPaymentReceived($salesOrder);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Post the original invoice journal entry.
     * Cash sale (Paid)    → DR Bank (1010)          CR Revenue (4xxx)
     * Credit sale         → DR Accounts Receivable  CR Revenue (4xxx)
     *
     * Idempotency: no-op if journal_entry_id is already set.
     */
    private function postSaleInvoice(SalesOrder $salesOrder): void
    {
        if ($salesOrder->journal_entry_id) return; // already posted

        $amount = (float) $salesOrder->total_amount;
        if ($amount <= 0) return;

        $revenueId = $this->resolveRevenueAccount($salesOrder->item_type);

        // Cash sale → DR Bank; Credit / Partial / Unbilled → DR AR
        $debitCode = $salesOrder->payment_status === 'Paid' ? '1010' : '1100';
        $debitId   = ChartOfAccount::where('code', $debitCode)->value('id');

        if (! $revenueId || ! $debitId) {
            Log::warning('SalesOrderObserver: COA account not found — invoice not posted to ledger.', [
                'invoice'   => $salesOrder->invoice_number,
                'debitCode' => $debitCode,
                'debitId'   => $debitId,
                'revenueId' => $revenueId,
            ]);
            return;
        }

        $type        = $salesOrder->payment_status === 'Paid' ? 'receipt' : 'general';
        $description = 'Sale Invoice: ' . $salesOrder->invoice_number . ' — ' . $salesOrder->item_type;
        $isCashSale  = $salesOrder->payment_status === 'Paid';

        // Atomic: JE + wallet both succeed or both roll back.
        DB::transaction(function () use ($salesOrder, $amount, $debitId, $revenueId, $type, $description, $isCashSale) {
            $entry = $this->poster->postEntry(
                reference:       $salesOrder->invoice_number,
                description:     $description,
                amount:          $amount,
                debitAccountId:  $debitId,
                creditAccountId: $revenueId,
                entryDate:       $salesOrder->sale_date->toDateString(),
                type:            $type
            );

            if ($entry) {
                SalesOrder::withoutEvents(fn () =>
                    $salesOrder->update(['journal_entry_id' => $entry->id])
                );
                // Sync wallet for cash sales (money in hand)
                if ($isCashSale) {
                    $userId = auth()->id() ?? User::where('role', 'super_admin')->value('id') ?? 1;
                    Wallet::company()->credit(
                        $amount,
                        'Sale: ' . $salesOrder->invoice_number,
                        $userId,
                        $salesOrder->invoice_number
                    );
                }
            }
        });
    }

    /**
     * Post a payment receipt JE when a credit invoice is fully paid.
     * DR Bank (1010)  CR Accounts Receivable (1100)
     *
     * Idempotency: checks for an existing PMT-{invoice} reference before posting.
     */
    private function postPaymentReceived(SalesOrder $salesOrder): void
    {
        $ref = 'PMT-' . $salesOrder->invoice_number;
        if (JournalEntry::where('reference', $ref)->where('status', 'posted')->exists()) return;

        $amount = (float) $salesOrder->total_amount;
        if ($amount <= 0) return;

        $arId   = ChartOfAccount::where('code', '1100')->value('id');
        $bankId = ChartOfAccount::where('code', '1010')->value('id');

        if (! $arId || ! $bankId) {
            Log::warning('SalesOrderObserver: AR/Bank COA account not found — payment not posted to ledger.', [
                'invoice' => $salesOrder->invoice_number,
                'arId'    => $arId,
                'bankId'  => $bankId,
            ]);
            return;
        }

        DB::transaction(function () use ($salesOrder, $amount, $bankId, $arId, $ref) {
            $entry = $this->poster->postEntry(
                reference:       $ref,
                description:     'Payment received: ' . $salesOrder->invoice_number,
                amount:          $amount,
                debitAccountId:  $bankId,
                creditAccountId: $arId,
                entryDate:       now()->toDateString(),
                type:            'receipt'
            );

            if ($entry) {
                $userId = auth()->id() ?? User::where('role', 'super_admin')->value('id') ?? 1;
                Wallet::company()->credit(
                    $amount,
                    'Payment received: ' . $salesOrder->invoice_number,
                    $userId,
                    $ref
                );
            }
        });
    }

    /**
     * Correct a posted invoice after an amount-affecting edit.
     *
     * Algorithm (all steps inside one DB::transaction):
     *   1. If a PMT JE (DR Bank, CR AR) exists: reverse it + debit wallet.
     *   2. Reverse the original invoice JE.
     *   3. If original invoice JE was a cash sale (DR Bank) and no PMT JE: debit wallet.
     *   4. Clear journal_entry_id.
     *   5. Re-post a fresh invoice JE via postSaleInvoice() using current amounts/status.
     *
     * This safely handles every combination:
     *   – Pending invoice, amount changes
     *   – Partial invoice, amount changes
     *   – Paid (cash from creation), amount changes
     *   – Paid (was Pending, then paid, then amount changed)
     *   – Pending→Paid transition + amount change simultaneously
     */
    private function correctPostedInvoice(SalesOrder $salesOrder): void
    {
        $bankId = ChartOfAccount::where('code', '1010')->value('id');
        $userId = auth()->id() ?? User::where('role', 'super_admin')->value('id') ?? 1;

        $invoiceJE = JournalEntry::with('lines')->find($salesOrder->journal_entry_id);

        // If the JE isn't posted (already reversed by delete/restore cycle), just re-post.
        if (! $invoiceJE || $invoiceJE->status !== 'posted') {
            Log::warning('SalesOrderObserver::correctPostedInvoice — original JE not found or not posted; skipping correction.', [
                'invoice'    => $salesOrder->invoice_number,
                'je_id'      => $salesOrder->journal_entry_id,
                'je_status'  => $invoiceJE?->status,
            ]);
            return;
        }

        // Determine if the original invoice JE was a cash sale (DR Bank)
        $wasOriginalCashSale = $bankId && $invoiceJE->lines
            ->where('account_id', $bankId)
            ->where('debit', '>', 0)
            ->isNotEmpty();

        $oldInvoiceAmount = (float) $invoiceJE->total_debit;

        // Check for an existing PMT JE (posted when Pending→Paid happened earlier)
        $pmtRef = 'PMT-' . $salesOrder->invoice_number;
        $pmtJE  = JournalEntry::where('reference', $pmtRef)
            ->where('status', 'posted')
            ->latest()
            ->first();

        DB::transaction(function () use (
            $salesOrder, $invoiceJE, $pmtJE,
            $wasOriginalCashSale, $oldInvoiceAmount,
            $bankId, $userId, $pmtRef
        ) {
            // ── Step 1: Reverse PMT JE if it exists ──────────────────────────
            // PMT JE: DR Bank  CR AR  → reversal: DR AR  CR Bank
            // The CR Bank in the reversal reduces the GL Bank account.
            // Mirror this in the wallet so both stay in sync.
            if ($pmtJE) {
                $pmtAmount = (float) $pmtJE->total_debit;
                $this->ledger->reverseEntry(
                    $pmtJE->id,
                    'Correction — reversed PMT: ' . $salesOrder->invoice_number
                );
                if ($bankId) {
                    Wallet::company()->debit(
                        $pmtAmount,
                        'Correction — reversed payment: ' . $salesOrder->invoice_number,
                        $userId,
                        'REV-PMT-' . $salesOrder->invoice_number,
                        true  // force: correction can temporarily allow a negative balance
                    );
                }
            }

            // ── Step 2: Reverse the original invoice JE ───────────────────────
            $this->ledger->reverseEntry(
                $invoiceJE->id,
                'Correction: ' . $salesOrder->invoice_number
            );

            // ── Step 3: Debit wallet if original was a cash sale (DR Bank) ────
            // Only when there was NO PMT JE; if a PMT JE existed, the cash entry
            // was in the PMT JE (already reversed in step 1), not the invoice JE.
            if ($wasOriginalCashSale && ! $pmtJE) {
                Wallet::company()->debit(
                    $oldInvoiceAmount,
                    'Correction — reversed sale: ' . $salesOrder->invoice_number,
                    $userId,
                    'REV-' . $salesOrder->invoice_number,
                    true  // force
                );
            }

            // ── Step 4: Clear journal_entry_id ────────────────────────────────
            SalesOrder::withoutEvents(fn () =>
                $salesOrder->update(['journal_entry_id' => null])
            );

            // ── Step 5: Re-post a corrected invoice JE ────────────────────────
            // postSaleInvoice() uses the current values on $salesOrder
            // (new total_amount, current payment_status) — both already set
            // by the controller before the observer fired.
            $this->postSaleInvoice($salesOrder);
        });
    }

    /**
     * Map sale item type name to the appropriate revenue COA code.
     *
     * 4000 — Milk Sales Revenue
     * 4010 — Animal / Dairy Product Sales
     * 4020 — Feed Sales
     * 4100 — Franchise Royalty / Service Income
     * 4900 — Other Income (dung, misc.)
     */
    private function resolveRevenueAccount(string $itemType): ?int
    {
        $code = match (true) {
            stripos($itemType, 'milk')      !== false => '4000',
            stripos($itemType, 'animal')    !== false => '4010',
            stripos($itemType, 'feed')      !== false => '4020',
            stripos($itemType, 'franchise') !== false => '4100',
            default                                   => '4900',
        };

        return ChartOfAccount::where('code', $code)->value('id');
    }
}