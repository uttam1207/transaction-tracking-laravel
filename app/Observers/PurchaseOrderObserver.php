<?php

namespace App\Observers;

use App\Models\ChartOfAccount;
use App\Models\PurchaseOrder;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Services\JournalPostingService;

/**
 * Double-entry rules for Purchase Orders (bahikhata — vendor/payable side):
 *
 * On RECEIVED:                    DR Purchases / COGS (5000)   CR Accounts Payable (2000)
 * On PAID (after RECEIVED):       DR Accounts Payable  (2000)  CR Bank              (1010)
 * On PAID (direct, skip Received):DR Purchases / COGS  (5000)  CR Bank              (1010)
 */
class PurchaseOrderObserver
{
    public function __construct(protected JournalPostingService $poster) {}

    public function created(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->status === 'Received') {
            $this->postGoodsReceived($purchaseOrder);
        } elseif ($purchaseOrder->status === 'Paid') {
            // Created directly as Paid (no separate Received step)
            $this->postDirectPurchase($purchaseOrder);
        }
    }

    public function updated(PurchaseOrder $purchaseOrder): void
    {
        if (! $purchaseOrder->wasChanged('status')) return;

        $oldStatus = $purchaseOrder->getOriginal('status');
        $newStatus = $purchaseOrder->status;

        if ($newStatus === 'Received' && ! in_array($oldStatus, ['Received', 'Paid'])) {
            $this->postGoodsReceived($purchaseOrder);
        }

        if ($newStatus === 'Paid') {
            if ($oldStatus === 'Received' && $purchaseOrder->journal_entry_id) {
                // Goods already received → just post the vendor payment
                $this->postVendorPayment($purchaseOrder);
            } elseif (! $purchaseOrder->journal_entry_id) {
                // Draft/Sent → Paid directly: combine both entries in one
                $this->postDirectPurchase($purchaseOrder);
            }
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /** DR Purchases (5000), CR Accounts Payable (2000) — no cash movement */
    private function postGoodsReceived(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->journal_entry_id) return; // already posted

        $amount  = (float) $purchaseOrder->total_amount;
        $cogsId  = ChartOfAccount::where('code', '5000')->value('id');
        $apId    = ChartOfAccount::where('code', '2000')->value('id');

        if (! $cogsId || ! $apId || $amount <= 0) return;

        $vendor = $purchaseOrder->vendor?->name ?? 'Vendor';

        $entry = $this->poster->postEntry(
            reference: $purchaseOrder->po_number,
            description: "Goods Received: {$purchaseOrder->po_number} — {$vendor}",
            amount: $amount,
            debitAccountId: $cogsId,
            creditAccountId: $apId,
            entryDate: $purchaseOrder->order_date->toDateString(),
            type: 'purchase'
        );

        if ($entry) {
            PurchaseOrder::withoutEvents(fn () =>
                $purchaseOrder->update(['journal_entry_id' => $entry->id])
            );
            // No wallet movement — cash has not left yet (still payable)
        }
    }

    /** DR Accounts Payable (2000), CR Bank (1010) — cash leaves bank */
    private function postVendorPayment(PurchaseOrder $purchaseOrder): void
    {
        $amount = (float) $purchaseOrder->total_amount;
        $apId   = ChartOfAccount::where('code', '2000')->value('id');
        $bankId = ChartOfAccount::where('code', '1010')->value('id');

        if (! $apId || ! $bankId || $amount <= 0) return;

        $vendor = $purchaseOrder->vendor?->name ?? 'Vendor';
        $ref    = 'PAY-' . $purchaseOrder->po_number;

        $entry = $this->poster->postEntry(
            reference: $ref,
            description: "Vendor Payment: {$purchaseOrder->po_number} — {$vendor}",
            amount: $amount,
            debitAccountId: $apId,
            creditAccountId: $bankId,
            entryDate: now()->toDateString(),
            type: 'payment'
        );

        if ($entry) {
            PurchaseOrder::withoutEvents(fn () =>
                $purchaseOrder->update(['payment_journal_entry_id' => $entry->id])
            );
            // Cash left bank — debit wallet
            $this->debitWallet($amount, "Vendor Payment: {$purchaseOrder->po_number} — {$vendor}", $ref);
        }
    }

    /** DR Purchases (5000), CR Bank (1010) — single-step cash purchase */
    private function postDirectPurchase(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->journal_entry_id) return;

        $amount  = (float) $purchaseOrder->total_amount;
        $cogsId  = ChartOfAccount::where('code', '5000')->value('id');
        $bankId  = ChartOfAccount::where('code', '1010')->value('id');

        if (! $cogsId || ! $bankId || $amount <= 0) return;

        $vendor = $purchaseOrder->vendor?->name ?? 'Vendor';

        $entry = $this->poster->postEntry(
            reference: $purchaseOrder->po_number,
            description: "Direct Purchase: {$purchaseOrder->po_number} — {$vendor}",
            amount: $amount,
            debitAccountId: $cogsId,
            creditAccountId: $bankId,
            entryDate: $purchaseOrder->order_date->toDateString(),
            type: 'purchase'
        );

        if ($entry) {
            PurchaseOrder::withoutEvents(fn () =>
                $purchaseOrder->update(['journal_entry_id' => $entry->id])
            );
            // Cash left bank — debit wallet
            $this->debitWallet($amount, "Direct Purchase: {$purchaseOrder->po_number} — {$vendor}", $purchaseOrder->po_number);
        }
    }

    /** Debit company wallet; skip silently if reference already reflected or balance insufficient. */
    private function debitWallet(float $amount, string $desc, string $reference): void
    {
        try {
            $alreadyDone = WalletTransaction::where('reference', $reference)->exists();
            if ($alreadyDone) return;

            $userId = auth()->id() ?? User::where('role', 'super_admin')->value('id') ?? 1;
            Wallet::company()->debit($amount, $desc, $userId, $reference);
        } catch (\Throwable) {
            // wallet sync is non-critical — journal entry was already posted
        }
    }
}