<?php

namespace App\Observers;

use App\Models\SalesOrder;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Zoho-style rule: Sales Invoices are billing documents only.
 *
 * Journal Entries are NEVER auto-created from a SalesOrder.
 * They come exclusively from Transactions (TransactionObserver) or are
 * created manually in the Finance → Journal Entries module.
 *
 * This observer handles only the wallet-balance side-effect:
 *   – Manual cash sale (Paid, no linked Transaction): credit company wallet.
 *   – Manual Pending/Partial → Paid conversion: credit company wallet.
 *   – Wallet credits from Transaction-linked sales are already handled by
 *     TransactionController (no double credit needed here).
 */
class SalesOrderObserver
{
    public function created(SalesOrder $salesOrder): void
    {
        // Only sync wallet for fully-paid manual cash sales (no Transaction link)
        if (
            $salesOrder->payment_status === 'Paid'
            && ! $salesOrder->transaction_id
            && (float) $salesOrder->total_amount > 0
        ) {
            $this->creditWallet($salesOrder, (float) $salesOrder->total_amount, 'Sale: ');
        }
    }

    public function updated(SalesOrder $salesOrder): void
    {
        // Status just turned Paid (was Pending/Partial/Unbilled), no linked transaction
        $becamePaid = $salesOrder->wasChanged('payment_status')
            && $salesOrder->payment_status === 'Paid'
            && in_array($salesOrder->getOriginal('payment_status'), ['Pending', 'Partial', 'Unbilled'])
            && ! $salesOrder->transaction_id;

        if ($becamePaid && (float) $salesOrder->total_amount > 0) {
            $this->creditWallet($salesOrder, (float) $salesOrder->total_amount, 'Payment received: ');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function creditWallet(SalesOrder $salesOrder, float $amount, string $prefix): void
    {
        try {
            $userId = auth()->id() ?? User::where('role', 'super_admin')->value('id') ?? 1;
            Wallet::company()->credit(
                $amount,
                $prefix . $salesOrder->invoice_number,
                $userId,
                $salesOrder->invoice_number
            );
        } catch (\Throwable $e) {
            Log::warning('SalesOrderObserver: wallet credit failed — ' . $e->getMessage(), [
                'invoice' => $salesOrder->invoice_number,
            ]);
        }
    }
}
