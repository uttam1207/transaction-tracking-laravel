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
 *   – Paid / UnbilledPaid (full amount received): credit company wallet.
 *   – Partial / UnbilledPartial (partial received): credit wallet for amount_paid only.
 *   – Pending / Unbilled (nothing received yet): no wallet credit.
 *   – When status later changes to Paid: credit wallet for full amount.
 *   – Wallet credits from Transaction-linked sales are already handled by
 *     TransactionController (no double credit needed here).
 */
class SalesOrderObserver
{
    public function created(SalesOrder $salesOrder): void
    {
        if ($salesOrder->transaction_id) return; // Transaction-linked — wallet handled by TransactionController

        $amount = match ($salesOrder->payment_status) {
            'Paid', 'UnbilledPaid'         => (float) $salesOrder->total_amount,
            'Partial', 'UnbilledPartial'   => (float) $salesOrder->amount_paid,
            default                        => 0.0,
        };

        if ($amount > 0) {
            $this->creditWallet($salesOrder, $amount, 'Sale: ');
        }
    }

    public function updated(SalesOrder $salesOrder): void
    {
        if ($salesOrder->transaction_id) return; // Transaction-linked — handled elsewhere

        $statusChanged = $salesOrder->wasChanged('payment_status');
        $oldStatus     = $salesOrder->getOriginal('payment_status');
        $newStatus     = $salesOrder->payment_status;

        // Status just turned fully Paid from any unpaid/partial state
        if ($statusChanged && $newStatus === 'Paid'
            && in_array($oldStatus, ['Pending', 'Partial', 'Unbilled', 'UnbilledPaid', 'UnbilledPartial'])
        ) {
            // Credit the remaining gap (full amount minus what was already credited on create)
            $alreadyCredited = in_array($oldStatus, ['Partial', 'UnbilledPaid', 'UnbilledPartial'])
                ? (float) $salesOrder->getOriginal('amount_paid')
                : 0.0;
            $gap = (float) $salesOrder->total_amount - $alreadyCredited;
            if ($gap > 0) {
                $this->creditWallet($salesOrder, $gap, 'Payment received: ');
            }
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
