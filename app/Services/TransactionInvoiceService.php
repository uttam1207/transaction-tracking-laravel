<?php

namespace App\Services;

use App\Models\CrmCustomer;
use App\Models\SaleItemType;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Converts a successful credit transaction (sales category) into a
 * Sales Invoice (SalesOrder) automatically.
 *
 * Flow:
 *  1. TransactionObserver::postToLedger() creates a JournalEntry for the
 *     transaction and sets transaction.journal_entry_id.
 *  2. TransactionController calls shouldCreateInvoice() + createFromTransaction().
 *  3. A SalesOrder is created with transaction_id linked back to the
 *     transaction, and journal_entry_id shared from the transaction.
 *  4. SalesOrderObserver::created() fires; because journal_entry_id is
 *     already set it skips its own JE — only ONE entry in the ledger.
 *
 * Result: Transaction ←→ SalesOrder both point to the same JournalEntry.
 */
class TransactionInvoiceService
{
    /** Categories that trigger automatic invoice generation. */
    public const SALES_CATEGORIES = [
        'Milk Sales',
        'Dairy Products Sales',
        'Animal Sales',
        'Feed Sales',
        'Dung Sales',
        'Franchise Royalty',
    ];

    /**
     * Returns true when a transaction should trigger invoice creation:
     *  - type = credit   (money received)
     *  - status = success
     *  - category is in the sales list
     *  - no SalesOrder has been linked yet (idempotency)
     */
    public function shouldCreateInvoice(Transaction $txn): bool
    {
        return $txn->type === 'credit'
            && $txn->status === 'success'
            && in_array($txn->category, self::SALES_CATEGORIES, true)
            && ! $txn->salesOrder()->exists();
    }

    /**
     * Create a SalesOrder from a successful credit transaction.
     *
     * The transaction's journal_entry_id (set by TransactionObserver) is
     * shared with the new SalesOrder so SalesOrderObserver's guard prevents
     * a second JE being posted.
     */
    public function createFromTransaction(Transaction $txn): SalesOrder
    {
        return DB::transaction(function () use ($txn) {

            // Re-fetch inside the transaction to get the latest journal_entry_id
            $txn->refresh();

            $invoiceNumber = SalesOrder::generateNumber();

            // Try to match sender to a CRM customer by name (null = Walk-in)
            $customerId = $txn->sender_name
                ? CrmCustomer::where('name', $txn->sender_name)->value('id')
                : null;

            // Find or create a SaleItemType matching the category
            $itemType = SaleItemType::firstOrCreate(
                ['name' => $txn->category],
                ['is_active' => true, 'sort_order' => 99]
            );

            // Create the invoice.
            // journal_entry_id is pre-populated from the transaction so that
            // SalesOrderObserver::postSaleInvoice() skips and no duplicate JE
            // is created.
            $order = SalesOrder::create([
                'invoice_number'    => $invoiceNumber,
                'crm_customer_id'   => $customerId,
                'sale_date'         => $txn->processed_at
                                           ? $txn->processed_at->toDateString()
                                           : now()->toDateString(),
                'payment_status'    => 'Paid',
                'total_amount'      => $txn->net_amount,
                'amount_paid'       => $txn->net_amount,
                'item_type'         => $txn->category,
                'sale_item_type_id' => $itemType->id,
                'quantity'          => 1,
                'rate'              => $txn->net_amount,
                'transaction_id'    => $txn->id,
                'journal_entry_id'  => $txn->journal_entry_id, // shared — prevents double JE
            ]);

            // Create the single line item
            $order->items()->create([
                'sale_item_type_id' => $itemType->id,
                'item_type'         => $txn->category,
                'quantity'          => 1,
                'rate'              => $txn->net_amount,
                'amount'            => $txn->net_amount,
                'description'       => $txn->description ?: 'From TXN: ' . $txn->transaction_id,
                'sort_order'        => 1,
            ]);

            return $order;
        });
    }
}
