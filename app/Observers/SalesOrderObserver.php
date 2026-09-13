<?php

namespace App\Observers;

use App\Models\ChartOfAccount;
use App\Models\SalesOrder;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Services\JournalPostingService;

/**
 * Double-entry rules for Sales Invoices:
 *
 * On CREATE (cash sale — Paid):         DR Bank (1010)           CR Sales Revenue (4xxx)
 * On CREATE (credit sale — Pending):    DR Accounts Receivable   CR Sales Revenue (4xxx)
 * On UPDATE (payment received — Paid):  DR Bank (1010)           CR Accounts Receivable
 */
class SalesOrderObserver
{
    public function __construct(protected JournalPostingService $poster) {}

    public function created(SalesOrder $salesOrder): void
    {
        $this->postSaleInvoice($salesOrder);
    }

    public function updated(SalesOrder $salesOrder): void
    {
        // Payment received: credit sale becomes fully paid
        if ($salesOrder->wasChanged('payment_status')
            && $salesOrder->payment_status === 'Paid'
            && in_array($salesOrder->getOriginal('payment_status'), ['Pending', 'Partial'])
            && $salesOrder->journal_entry_id
        ) {
            $this->postPaymentReceived($salesOrder);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function postSaleInvoice(SalesOrder $salesOrder): void
    {
        if ($salesOrder->journal_entry_id) return; // already posted

        $amount = (float) $salesOrder->total_amount;
        if ($amount <= 0) return;

        $revenueId = $this->resolveRevenueAccount($salesOrder->item_type);
        if (! $revenueId) return;

        // Cash sale → DR Bank; Credit/partial sale → DR Accounts Receivable
        $debitCode = $salesOrder->payment_status === 'Paid' ? '1010' : '1100';
        $debitId   = ChartOfAccount::where('code', $debitCode)->value('id');
        if (! $debitId) return;

        $type        = $salesOrder->payment_status === 'Paid' ? 'receipt' : 'general';
        $description = 'Sale Invoice: ' . $salesOrder->invoice_number . ' — ' . $salesOrder->item_type;

        $entry = $this->poster->postEntry(
            reference: $salesOrder->invoice_number,
            description: $description,
            amount: $amount,
            debitAccountId: $debitId,
            creditAccountId: $revenueId,
            entryDate: $salesOrder->sale_date->toDateString(),
            type: $type
        );

        if ($entry) {
            SalesOrder::withoutEvents(fn () =>
                $salesOrder->update(['journal_entry_id' => $entry->id])
            );
            // Sync wallet for cash sales (bank account credited)
            if ($salesOrder->payment_status === 'Paid') {
                $this->creditWallet($amount, 'Sale: ' . $salesOrder->invoice_number, $salesOrder->invoice_number);
            }
        }
    }

    private function postPaymentReceived(SalesOrder $salesOrder): void
    {
        $amount = (float) $salesOrder->total_amount;
        if ($amount <= 0) return;

        $arId   = ChartOfAccount::where('code', '1100')->value('id'); // Accounts Receivable
        $bankId = ChartOfAccount::where('code', '1010')->value('id'); // Bank

        if (! $arId || ! $bankId) return;

        $entry = $this->poster->postEntry(
            reference: 'PMT-' . $salesOrder->invoice_number,
            description: 'Payment received: ' . $salesOrder->invoice_number,
            amount: $amount,
            debitAccountId: $bankId,
            creditAccountId: $arId,
            entryDate: now()->toDateString(),
            type: 'receipt'
        );

        if ($entry) {
            $this->creditWallet($amount, 'Payment received: ' . $salesOrder->invoice_number, 'PMT-' . $salesOrder->invoice_number);
        }
    }

    /** Credit company wallet; skip silently if reference already reflected. */
    private function creditWallet(float $amount, string $desc, string $reference): void
    {
        try {
            $alreadyDone = WalletTransaction::where('reference', $reference)->exists();
            if ($alreadyDone) return;

            $userId = auth()->id() ?? User::where('role', 'super_admin')->value('id') ?? 1;
            Wallet::company()->credit($amount, $desc, $userId, $reference);
        } catch (\Throwable) {
            // wallet sync is non-critical — journal entry was already posted
        }
    }

    /**
     * Map sale item type name to revenue COA code.
     *
     * 4000 — Milk Sales Revenue
     * 4010 — Dairy Products Sales  (animal)
     * 4020 — Feed Sales Revenue
     * 4100 — Service Income        (franchise royalty)
     * 4900 — Other Income          (dung, misc)
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