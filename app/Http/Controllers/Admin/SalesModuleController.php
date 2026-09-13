<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CrmCustomer;
use App\Models\SaleItemType;
use App\Models\SalesOrder;
use App\Services\LedgerBalanceService;
use Illuminate\Http\Request;

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
        if ($request->payment_status) {
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
        ];

        return view('admin.sales.index', compact('sales', 'customers', 'summary', 'itemTypes'));
    }

    public function create()
    {
        $customers = CrmCustomer::orderBy('name')->get();
        $itemTypes = SaleItemType::activeOrdered();
        return view('admin.sales.create', compact('customers', 'itemTypes'));
    }

    public function store(Request $request)
    {
        $itemType = SaleItemType::findOrFail($request->sale_item_type_id);

        $rules = [
            'invoice_number'    => 'required|string|unique:sales_orders,invoice_number',
            'crm_customer_id'   => 'nullable|exists:crm_customers,id',
            'sale_item_type_id' => 'required|exists:sale_item_types,id',
            'sale_date'         => 'required|date',
            'quantity'          => 'required|numeric|min:0.01',
            'payment_status'    => 'required|in:Paid,Pending,Partial',
        ];

        if ($itemType->is_milk_type) {
            $rules['fat_percentage'] = 'required|numeric|min:0.01|max:100';
            $rules['fat_rate']       = 'required|numeric|min:0.01';
            $rules['rate']           = 'nullable|numeric|min:0';
        } else {
            $rules['rate']           = 'required|numeric|min:0.01';
            $rules['fat_percentage'] = 'nullable';
            $rules['fat_rate']       = 'nullable';
        }

        $validated = $request->validate($rules);

        $quantity = (float) $validated['quantity'];

        if ($itemType->is_milk_type) {
            $fat        = (float) $validated['fat_percentage'];
            $fatRate    = (float) $validated['fat_rate'];
            $total      = $quantity * $fat * $fatRate;
            $effectRate = $fat * $fatRate; // effective per-unit rate for display
        } else {
            $effectRate = (float) $validated['rate'];
            $total      = $quantity * $effectRate;
            $fat        = null;
            $fatRate    = null;
        }

        SalesOrder::create([
            'invoice_number'    => $validated['invoice_number'],
            'crm_customer_id'   => $validated['crm_customer_id'] ?? null,
            'item_type'         => $itemType->name,
            'sale_item_type_id' => $itemType->id,
            'sale_date'         => $validated['sale_date'],
            'quantity'          => $quantity,
            'rate'              => $effectRate,
            'fat_percentage'    => $fat,
            'fat_rate'          => $fatRate,
            'total_amount'      => $total,
            'payment_status'    => $validated['payment_status'],
        ]);

        return redirect()->route('admin.sales.index')->with('success', 'Sales invoice created.');
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load('customer', 'saleItemType');
        return view('admin.sales.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder)
    {
        $salesOrder->load('customer', 'saleItemType');
        $customers = CrmCustomer::orderBy('name')->get();
        $itemTypes = SaleItemType::activeOrdered();
        return view('admin.sales.edit', compact('salesOrder', 'customers', 'itemTypes'));
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        $itemType = SaleItemType::findOrFail($request->sale_item_type_id);

        $rules = [
            'invoice_number'    => 'required|string|unique:sales_orders,invoice_number,' . $salesOrder->id,
            'crm_customer_id'   => 'nullable|exists:crm_customers,id',
            'sale_item_type_id' => 'required|exists:sale_item_types,id',
            'sale_date'         => 'required|date',
            'quantity'          => 'required|numeric|min:0.01',
            'payment_status'    => 'required|in:Paid,Pending,Partial',
        ];

        if ($itemType->is_milk_type) {
            $rules['fat_percentage'] = 'required|numeric|min:0.01|max:100';
            $rules['fat_rate']       = 'required|numeric|min:0.01';
            $rules['rate']           = 'nullable|numeric|min:0';
        } else {
            $rules['rate']           = 'required|numeric|min:0.01';
            $rules['fat_percentage'] = 'nullable';
            $rules['fat_rate']       = 'nullable';
        }

        $validated = $request->validate($rules);

        $quantity = (float) $validated['quantity'];

        if ($itemType->is_milk_type) {
            $fat        = (float) $validated['fat_percentage'];
            $fatRate    = (float) $validated['fat_rate'];
            $total      = $quantity * $fat * $fatRate;
            $effectRate = $fat * $fatRate;
        } else {
            $effectRate = (float) $validated['rate'];
            $total      = $quantity * $effectRate;
            $fat        = null;
            $fatRate    = null;
        }

        $salesOrder->update([
            'invoice_number'    => $validated['invoice_number'],
            'crm_customer_id'   => $validated['crm_customer_id'] ?? null,
            'item_type'         => $itemType->name,
            'sale_item_type_id' => $itemType->id,
            'sale_date'         => $validated['sale_date'],
            'quantity'          => $quantity,
            'rate'              => $effectRate,
            'fat_percentage'    => $fat,
            'fat_rate'          => $fatRate,
            'total_amount'      => $total,
            'payment_status'    => $validated['payment_status'],
        ]);

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
        $salesOrder->forceDelete();
        return redirect()->route('admin.sales.trash')
            ->with('success', "Sales invoice {$inv} permanently deleted.");
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