<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ChartOfAccount;
use App\Models\CrmCustomer;
use App\Models\FinancialPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SaleItemType;
use App\Models\SalesOrder;
use App\Services\LedgerBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesModuleController extends Controller
{
    public function __construct(private LedgerBalanceService $ledger) {}

    // ── Sales Orders ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $itemTypes = SaleItemType::activeOrdered();
        $query     = SalesOrder::with('customer');

        if ($request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }
        if ($request->item_type) {
            $query->where('item_type', $request->item_type);
        }
        if ($request->payment_status === 'Overdue') {
            $query->whereNotIn('payment_status', ['Paid'])
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', now()->toDateString());
        } elseif ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $sales     = $query->latest('sale_date')->paginate(15)->withQueryString();
        $customers = CrmCustomer::orderBy('name')->get();

        // KPI summary
        $milkTypeName = SaleItemType::where('is_milk_type', true)->where('is_active', true)->pluck('name');
        $summary = [
            'total_sales'     => SalesOrder::sum('total_amount'),
            'milk_sales'      => SalesOrder::whereIn('item_type', $milkTypeName)->sum('total_amount'),
            'animal_sales'    => SalesOrder::where('item_type', 'Animal Sales')->sum('total_amount'),
            'pending_payment' => SalesOrder::where('payment_status', 'Pending')->sum('total_amount'),
            'overdue_count'   => SalesOrder::whereNotIn('payment_status', ['Paid'])
                                    ->whereNotNull('due_date')
                                    ->where('due_date', '<', now()->toDateString())
                                    ->count(),
            'overdue_balance' => SalesOrder::whereNotIn('payment_status', ['Paid'])
                                    ->whereNotNull('due_date')
                                    ->where('due_date', '<', now()->toDateString())
                                    ->selectRaw('SUM(total_amount - amount_paid) as bal')
                                    ->value('bal') ?? 0,
        ];

        return view('admin.sales.index', compact('sales', 'customers', 'summary', 'itemTypes'));
    }

    public function create()
    {
        $customers = CrmCustomer::orderBy('name')->get();
        $itemTypes = SaleItemType::activeOrdered();
        $suggestedInvoiceNumber = DB::transaction(fn () => SalesOrder::generateNumber());
        return view('admin.sales.create', compact('customers', 'itemTypes', 'suggestedInvoiceNumber'));
    }

    public function nextNumber(): \Illuminate\Http\JsonResponse
    {
        $number = DB::transaction(fn () => SalesOrder::generateNumber());
        return response()->json(['number' => $number]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number'  => 'required|string|unique:sales_orders,invoice_number',
            'crm_customer_id' => 'nullable|exists:crm_customers,id',
            'sale_date'       => 'required|date',
            'due_date'        => 'nullable|date',
            'payment_terms'   => 'nullable|string|max:50',
            'payment_status'  => 'required|in:Paid,Pending,Partial,Unbilled',
            'payment_mode'    => 'nullable|in:Cash,Bank',
            'amount_paid'     => 'nullable|numeric|min:0',
            'items'           => 'required|array|min:1',
            'items.*.sale_item_type_id' => 'required|exists:sale_item_types,id',
            'items.*.quantity'          => 'required|numeric|min:0.01',
        ]);

        [$processedItems, $totalAmount, $first] = $this->processItems($request->input('items', []));

        if (empty($processedItems)) {
            return back()->withInput()->withErrors(['items' => 'At least one valid item line is required.']);
        }

        $amountPaid = match ($request->payment_status) {
            'Paid'    => $totalAmount,
            'Partial' => min((float) ($request->amount_paid ?? 0), $totalAmount),
            default   => 0.0,
        };

        // payment_mode only applies when money is actually received
        $paymentMode = in_array($request->payment_status, ['Paid', 'Partial'])
            ? ($request->payment_mode ?: 'Bank')
            : null;

        $sale = DB::transaction(function () use ($request, $first, $totalAmount, $amountPaid, $processedItems, $paymentMode) {
            $so = SalesOrder::create([
                'invoice_number'    => $request->invoice_number,
                'crm_customer_id'   => $request->crm_customer_id ?: null,
                'item_type'         => $first['item_type'],
                'sale_item_type_id' => $first['sale_item_type_id'],
                'sale_date'         => $request->sale_date,
                'due_date'          => $request->due_date ?: null,
                'payment_terms'     => $request->payment_terms ?: null,
                'quantity'          => $first['quantity'],
                'rate'              => $first['rate'],
                'fat_percentage'    => $first['fat_percentage'],
                'fat_rate'          => $first['fat_rate'],
                'total_amount'      => $totalAmount,
                'amount_paid'       => $amountPaid,
                'payment_status'    => $request->payment_status,
                'payment_mode'      => $paymentMode,
            ]);

            foreach ($processedItems as $item) {
                $so->items()->create($item);
            }

            return $so;
        });

        // Post journal entry based on payment_status + payment_mode
        $this->postSaleJournalEntry($sale->fresh());

        $redirect = redirect()->route('admin.sales.index')
            ->with('success', 'Sales invoice ' . $sale->invoice_number . ' created.');

        if (! $sale->fresh()->journal_entry_id) {
            $redirect = $redirect->with('warning', 'Accounting entry could not be posted — no open financial period covers ' . $request->sale_date . '. Run: php artisan accounting:setup');
        }

        return $redirect;
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'saleItemType', 'items.itemType']);
        return view('admin.sales.show', compact('salesOrder'));
    }

    public function printInvoice(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.itemType']);
        return view('admin.sales.print', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'saleItemType', 'items.itemType']);
        $customers = CrmCustomer::orderBy('name')->get();
        $itemTypes = SaleItemType::activeOrdered();

        // Build existing items for JS — fall back to parent fields for legacy orders
        $existingItems = $salesOrder->items->isNotEmpty()
            ? $salesOrder->items->map(fn ($i) => [
                'sale_item_type_id' => $i->sale_item_type_id,
                'description'       => $i->description,
                'quantity'          => $i->quantity,
                'rate'              => $i->fat_percentage ? null : $i->rate,
                'fat_percentage'    => $i->fat_percentage,
                'fat_rate'          => $i->fat_rate,
                'amount'            => $i->amount,
              ])->values()->all()
            : [[
                'sale_item_type_id' => $salesOrder->sale_item_type_id,
                'description'       => null,
                'quantity'          => $salesOrder->quantity,
                'rate'              => $salesOrder->fat_percentage ? null : $salesOrder->rate,
                'fat_percentage'    => $salesOrder->fat_percentage,
                'fat_rate'          => $salesOrder->fat_rate,
                'amount'            => $salesOrder->total_amount,
              ]];

        return view('admin.sales.edit', compact('salesOrder', 'customers', 'itemTypes', 'existingItems'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $request->validate([
            'invoice_number'  => 'required|string|unique:sales_orders,invoice_number,' . $salesOrder->id,
            'crm_customer_id' => 'nullable|exists:crm_customers,id',
            'sale_date'       => 'required|date',
            'due_date'        => 'nullable|date',
            'payment_terms'   => 'nullable|string|max:50',
            'payment_status'  => 'required|in:Paid,Pending,Partial,Unbilled',
            'payment_mode'    => 'nullable|in:Cash,Bank',
            'amount_paid'     => 'nullable|numeric|min:0',
            'items'           => 'required|array|min:1',
            'items.*.sale_item_type_id' => 'required|exists:sale_item_types,id',
            'items.*.quantity'          => 'required|numeric|min:0.01',
        ]);

        [$processedItems, $totalAmount, $first] = $this->processItems($request->input('items', []));

        if (empty($processedItems)) {
            return back()->withInput()->withErrors(['items' => 'At least one valid item line is required.']);
        }

        $amountPaid = match ($request->payment_status) {
            'Paid'    => $totalAmount,
            'Partial' => min((float) ($request->amount_paid ?? $salesOrder->amount_paid), $totalAmount),
            default   => 0.0,
        };

        $paymentMode = in_array($request->payment_status, ['Paid', 'Partial'])
            ? ($request->payment_mode ?: 'Bank')
            : null;

        // Reverse old JE before updating (only for manually-created invoices)
        if ($salesOrder->journal_entry_id && ! $salesOrder->transaction_id) {
            $this->ledger->reverseEntry(
                $salesOrder->journal_entry_id,
                'Updated Sale: ' . $salesOrder->invoice_number
            );
            SalesOrder::withoutEvents(fn() => $salesOrder->update(['journal_entry_id' => null]));
        }

        DB::transaction(function () use ($request, $salesOrder, $first, $totalAmount, $amountPaid, $processedItems, $paymentMode) {
            $salesOrder->update([
                'invoice_number'    => $request->invoice_number,
                'crm_customer_id'   => $request->crm_customer_id ?: null,
                'item_type'         => $first['item_type'],
                'sale_item_type_id' => $first['sale_item_type_id'],
                'sale_date'         => $request->sale_date,
                'due_date'          => $request->due_date ?: null,
                'payment_terms'     => $request->payment_terms ?: null,
                'quantity'          => $first['quantity'],
                'rate'              => $first['rate'],
                'fat_percentage'    => $first['fat_percentage'],
                'fat_rate'          => $first['fat_rate'],
                'total_amount'      => $totalAmount,
                'amount_paid'       => $amountPaid,
                'payment_status'    => $request->payment_status,
                'payment_mode'      => $paymentMode,
            ]);

            $salesOrder->items()->delete();
            foreach ($processedItems as $item) {
                $salesOrder->items()->create($item);
            }
        });

        // Post a fresh journal entry for the updated sale
        $this->postSaleJournalEntry($salesOrder->fresh());

        return redirect()->route('admin.sales.show', $salesOrder)->with('success', 'Sales invoice updated.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        // Only reverse the JE for manually-created invoices.
        // Transaction-linked invoices share the Transaction's JE — do not touch it.
        if ($salesOrder->journal_entry_id && ! $salesOrder->transaction_id) {
            $this->ledger->reverseEntry(
                $salesOrder->journal_entry_id,
                'Deleted Sale: ' . $salesOrder->invoice_number
            );
        }

        AuditLog::create([
            'user_id'        => Auth::id(),
            'event'          => 'deleted',
            'auditable_type' => SalesOrder::class,
            'auditable_id'   => $salesOrder->id,
            'old_values'     => $salesOrder->toArray(),
            'new_values'     => null,
            'ip_address'     => request()->ip(),
            'module'         => 'sales',
            'description'    => 'Sales invoice ' . $salesOrder->invoice_number . ' moved to trash.',
        ]);

        $salesOrder->delete(); // soft delete
        return redirect()->route('admin.sales.index')
            ->with('success', 'Sales invoice ' . $salesOrder->invoice_number . ' moved to trash. Ledger reversed.');
    }

    public function trash()
    {
        $sales = SalesOrder::onlyTrashed()->with('customer')->latest('deleted_at')->paginate(20);
        return view('admin.sales.trash', compact('sales'));
    }

    public function restore(int $id)
    {
        $salesOrder = SalesOrder::onlyTrashed()->findOrFail($id);

        // Reverse the reversal entry to restore ledger balances
        if ($salesOrder->journal_entry_id) {
            $reversal = \App\Models\JournalEntry::where('reversal_of', $salesOrder->journal_entry_id)
                ->where('status', 'posted')->latest()->first();
            if ($reversal) {
                $this->ledger->reverseEntry($reversal->id, 'Restored Sale: ' . $salesOrder->invoice_number);
            }
        }

        $salesOrder->restore();

        AuditLog::create([
            'user_id'        => Auth::id(),
            'event'          => 'restored',
            'auditable_type' => SalesOrder::class,
            'auditable_id'   => $salesOrder->id,
            'old_values'     => null,
            'new_values'     => $salesOrder->toArray(),
            'ip_address'     => request()->ip(),
            'module'         => 'sales',
            'description'    => 'Sales invoice ' . $salesOrder->invoice_number . ' restored from trash.',
        ]);

        return redirect()->route('admin.sales.trash')
            ->with('success', 'Sales invoice ' . $salesOrder->invoice_number . ' restored.');
    }

    public function forceDelete(int $id)
    {
        $salesOrder = SalesOrder::onlyTrashed()->findOrFail($id);
        $inv        = $salesOrder->invoice_number;

        DB::transaction(function () use ($salesOrder) {
            // For manually-created invoices: permanently wipe the original JE and
            // any reversal JE that was created when the invoice was soft-deleted.
            // This ensures the balance sheet returns to its pre-invoice state.
            //
            // Transaction-linked invoices share the Transaction's JE — never touch it.
            if ($salesOrder->journal_entry_id && ! $salesOrder->transaction_id) {
                $jeIds = JournalEntry::where('id', $salesOrder->journal_entry_id)
                    ->orWhere('reversal_of', $salesOrder->journal_entry_id)
                    ->pluck('id');

                JournalEntryLine::whereIn('journal_entry_id', $jeIds)->delete();
                JournalEntry::whereIn('id', $jeIds)->delete();
            }

            $salesOrder->forceDelete();
        });

        AuditLog::create([
            'user_id'        => Auth::id(),
            'event'          => 'force_deleted',
            'auditable_type' => SalesOrder::class,
            'auditable_id'   => $id,
            'old_values'     => null,
            'new_values'     => null,
            'ip_address'     => request()->ip(),
            'module'         => 'sales',
            'description'    => "Sales invoice {$inv} permanently deleted with all ledger entries.",
        ]);

        return redirect()->route('admin.sales.trash')
            ->with('success', "Sales invoice {$inv} permanently deleted. Balance sheet updated.");
    }

    // ── Accounting helpers ─────────────────────────────────────────────────────

    /**
     * Revenue account code based on item type.
     * Unbilled invoices always use 4050 (Unbilled Revenue) regardless of item.
     */
    private function revenueAccountCode(string $itemType, bool $unbilled = false): string
    {
        if ($unbilled) return '4050';

        return match (true) {
            stripos($itemType, 'milk')      !== false => '4000',
            stripos($itemType, 'dairy')     !== false => '4010',
            stripos($itemType, 'animal')    !== false => '4010',
            stripos($itemType, 'feed')      !== false => '4020',
            stripos($itemType, 'franchise') !== false => '4100',
            default                                   => '4900',
        };
    }

    /**
     * Post the correct double-entry journal for a sales invoice based on
     * payment_status and payment_mode.
     *
     *  Paid (Bank)  → DR 1010 Bank,     CR Revenue (full)
     *  Paid (Cash)  → DR 1000 Cash,     CR Revenue (full)
     *  Pending      → DR 1100 AR,        CR Revenue (full)
     *  Partial      → DR 1010/1000 (paid) + DR 1100 AR (remaining), CR Revenue (full)
     *  Unbilled     → DR 1100 AR,        CR 4050 Unbilled Revenue (full)
     *
     * Skips silently if no financial period covers the sale date, or if the
     * invoice is linked to a Transaction (the Transaction already owns the JE).
     */
    public function postSaleJournalEntry(SalesOrder $sale): void
    {
        // Transaction-linked invoices share the Transaction's JE — do not create a second one
        if ($sale->transaction_id) return;
        // Already has a JE (e.g. called twice by mistake)
        if ($sale->journal_entry_id) return;

        $amount     = (float) $sale->total_amount;
        if ($amount <= 0) return;

        $amountPaid = (float) $sale->amount_paid;
        $remaining  = max(0.0, round($amount - $amountPaid, 2));
        $status     = $sale->payment_status;
        $mode       = $sale->payment_mode ?? 'Bank';
        $isUnbilled = $status === 'Unbilled';
        $bankCode   = ($mode === 'Cash') ? '1000' : '1010';
        $arCode     = '1100';
        $revCode    = $this->revenueAccountCode($sale->item_type, $isUnbilled);

        // Resolve financial period
        $entryDate = $sale->sale_date->toDateString();
        $period    = FinancialPeriod::where('start_date', '<=', $entryDate)
            ->where('end_date', '>=', $entryDate)
            ->whereIn('status', ['open', 'closed'])
            ->orderBy('start_date', 'desc')
            ->first();

        if (! $period) return; // No period — no JE

        // Resolve account IDs
        $needed = array_unique([$bankCode, $arCode, $revCode]);
        $accts  = ChartOfAccount::whereIn('code', $needed)->pluck('id', 'code');

        if (! isset($accts[$revCode])) return; // Revenue account missing

        $adminId = Auth::id() ?? \App\Models\User::where('role', 'super_admin')->value('id') ?? 1;
        $jeType  = ($status === 'Paid') ? 'receipt' : 'sales';

        DB::transaction(function () use (
            $sale, $entryDate, $period, $amount, $amountPaid, $remaining,
            $status, $bankCode, $arCode, $revCode, $accts, $adminId, $jeType
        ) {
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'period_id'    => $period->id,
                'entry_date'   => $entryDate,
                'reference'    => $sale->invoice_number,
                'type'         => $jeType,
                'description'  => 'Sale Invoice: ' . $sale->invoice_number . ' — ' . $sale->item_type,
                'total_debit'  => $amount,
                'total_credit' => $amount,
                'status'       => 'posted',
                'created_by'   => $adminId,
                'posted_by'    => $adminId,
                'posted_at'    => now(),
            ]);

            // CR Revenue (always full amount)
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $accts[$revCode],
                'debit'            => 0,
                'credit'           => $amount,
                'description'      => $sale->invoice_number,
            ]);

            if ($status === 'Paid') {
                // DR Bank or Cash for full amount
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $accts[$bankCode],
                    'debit'            => $amount,
                    'credit'           => 0,
                    'description'      => $sale->invoice_number,
                ]);
            } elseif ($status === 'Partial' && $amountPaid > 0 && $remaining > 0 && isset($accts[$bankCode])) {
                // DR Bank/Cash (paid portion) + DR AR (remaining)
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $accts[$bankCode],
                    'debit'            => $amountPaid,
                    'credit'           => 0,
                    'description'      => $sale->invoice_number . ' (partial payment)',
                ]);
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $accts[$arCode],
                    'debit'            => $remaining,
                    'credit'           => 0,
                    'description'      => $sale->invoice_number . ' (outstanding)',
                ]);
            } else {
                // Pending / Unbilled / Partial with no paid amount → DR AR full
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $accts[$arCode],
                    'debit'            => $amount,
                    'credit'           => 0,
                    'description'      => $sale->invoice_number,
                ]);
            }

            // Link JE to sale (bypass observer to prevent loops)
            SalesOrder::withoutEvents(fn() => $sale->update(['journal_entry_id' => $entry->id]));

            // Update ledger balance cache
            $this->ledger->updateAfterPost($entry->load('lines'));
        });
    }

    // ── Shared helper ─────────────────────────────────────────────────────────

    /**
     * Process the items[] array from the form, compute amounts, and return:
     * [ processedItems[], totalAmount, firstItem ]
     */
    private function processItems(array $rawItems): array
    {
        $typeMap        = SaleItemType::pluck('name', 'id')->all(); // id => name
        $isMilkMap      = SaleItemType::pluck('is_milk_type', 'id')->all(); // id => bool
        $processed      = [];
        $total          = 0.0;

        foreach (array_values($rawItems) as $idx => $item) {
            $typeId = (int) ($item['sale_item_type_id'] ?? 0);
            if (! $typeId || ! isset($typeMap[$typeId])) continue;

            $qty     = max(0.0, (float) ($item['quantity'] ?? 0));
            if ($qty <= 0) continue;

            $isMilk  = (bool) ($isMilkMap[$typeId] ?? false);

            // Amount is always Quantity × Rate for all item types.
            // For milk types, fat_percentage and fat_rate are saved for reference only.
            $rate   = (float) ($item['rate'] ?? 0);
            $amount = $qty * $rate;

            if ($isMilk) {
                $fatVal  = ($item['fat_percentage'] ?? '') !== '' ? (float) $item['fat_percentage'] : null;
                $fatRate = ($item['fat_rate']        ?? '') !== '' ? (float) $item['fat_rate']        : null;
                $fatPct  = $fatVal;
            } else {
                $fatVal  = null;
                $fatRate = null;
                $fatPct  = null;
            }

            $processed[] = [
                'sale_item_type_id' => $typeId,
                'item_type'         => $typeMap[$typeId],
                'description'       => isset($item['description']) ? trim($item['description']) : null,
                'quantity'          => $qty,
                'rate'              => $rate,
                'fat_percentage'    => $fatPct,
                'fat_rate'          => $fatRate ?? null,
                'amount'            => round($amount, 2),
                'sort_order'        => $idx,
            ];

            $total += $amount;
        }

        $first = $processed[0] ?? [
            'item_type' => 'Mixed', 'sale_item_type_id' => null,
            'quantity' => 0, 'rate' => 0, 'fat_percentage' => null, 'fat_rate' => null,
        ];

        return [$processed, round($total, 2), $first];
    }

    // ── Sale Item Types CRUD (AJAX) ────────────────────────────────────────────

    public function itemTypesIndex()
    {
        $types = SaleItemType::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.sales.item-types.index', compact('types'));
    }

    public function itemTypesStore(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100|unique:sale_item_types,name',
            'is_milk_type' => 'boolean',
            'sort_order'   => 'integer|min:0',
            'is_active'    => 'boolean',
        ]);

        $type = SaleItemType::create([
            'name'         => $validated['name'],
            'is_milk_type' => $request->boolean('is_milk_type'),
            'sort_order'   => $validated['sort_order'] ?? 0,
            'is_active'    => true,
        ]);

        return response()->json(['success' => true, 'type' => $type]);
    }

    public function itemTypesUpdate(Request $request, SaleItemType $type)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100|unique:sale_item_types,name,' . $type->id,
            'is_milk_type' => 'boolean',
            'sort_order'   => 'integer|min:0',
            'is_active'    => 'boolean',
        ]);

        $type->update([
            'name'         => $validated['name'],
            'is_milk_type' => $request->boolean('is_milk_type'),
            'sort_order'   => $validated['sort_order'] ?? $type->sort_order,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return response()->json(['success' => true, 'type' => $type->fresh()]);
    }

    public function itemTypesDestroy(SaleItemType $type)
    {
        if ($type->salesOrders()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete — this type is used by existing invoices.']);
        }

        $type->delete();
        return response()->json(['success' => true]);
    }
}