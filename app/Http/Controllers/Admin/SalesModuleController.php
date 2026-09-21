<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CrmCustomer;
use App\Models\SaleItemType;
use App\Models\SaleOrderItem;
use App\Models\SalesOrder;
use App\Services\LedgerBalanceService;
use Illuminate\Http\Request;
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

        $sale = DB::transaction(function () use ($request, $first, $totalAmount, $amountPaid, $processedItems) {
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
            ]);

            foreach ($processedItems as $item) {
                $so->items()->create($item);
            }

            return $so;
        });

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

        DB::transaction(function () use ($request, $salesOrder, $first, $totalAmount, $amountPaid, $processedItems) {
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
            ]);

            $salesOrder->items()->delete();
            foreach ($processedItems as $item) {
                $salesOrder->items()->create($item);
            }
        });

        return redirect()->route('admin.sales.show', $salesOrder)->with('success', 'Sales invoice updated.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        // Reverse any posted journal entry so the ledger stays balanced
        if ($salesOrder->journal_entry_id) {
            $this->ledger->reverseEntry(
                $salesOrder->journal_entry_id,
                'Deleted Sale: ' . $salesOrder->invoice_number
            );
        }

        AuditLog::create([
            'user_id'        => auth()->id(),
            'event'          => 'deleted',
            'auditable_type' => SalesOrder::class,
            'auditable_id'   => $salesOrder->id,
            'old_values'     => $salesOrder->toArray(),
            'new_values'     => null,
            'ip_address'     => request()->ip(),
            'module'         => 'sales',
            'description'    => 'Sales invoice ' . $salesOrder->invoice_number . ' permanently deleted.',
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
            'user_id'        => auth()->id(),
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
        $inv = $salesOrder->invoice_number;

        // Reverse any posted journal entry so the ledger stays balanced on permanent delete
        if ($salesOrder->journal_entry_id) {
            $this->ledger->reverseEntry(
                $salesOrder->journal_entry_id,
                'Force Delete Sale: ' . $inv
            );
        }

        $salesOrder->forceDelete();
        return redirect()->route('admin.sales.trash')
            ->with('success', "Sales invoice {$inv} permanently deleted. Ledger reversed.");
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

            if ($isMilk) {
                $fat     = (float) ($item['fat_percentage'] ?? 0);
                $fatRate = (float) ($item['fat_rate'] ?? 0);
                $rate    = $fat * $fatRate;         // effective rate per litre
                $amount  = $qty * $fat * $fatRate;
                $fatPct  = $fat;
            } else {
                $rate    = (float) ($item['rate'] ?? 0);
                $amount  = $qty * $rate;
                $fat     = null;
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