<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Services\LedgerBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcurementController extends Controller
{
    public function __construct(private LedgerBalanceService $ledger) {}

    public function index(Request $request)
    {
        $query = PurchaseOrder::with('vendor');

        if ($request->search) {
            $query->where(fn($q) => $q
                ->where('po_number', 'like', '%'.$request->search.'%')
                ->orWhereHas('vendor', fn($v) => $v->where('name', 'like', '%'.$request->search.'%')));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest('order_date')->paginate(15)->withQueryString();
        $vendors = Vendor::orderBy('name')->get();
        $summary = [
            'total_vendors' => Vendor::count(),
            'total_po_value' => PurchaseOrder::sum('total_amount'),
            'pending_pos' => PurchaseOrder::where('status', 'Sent')->count(),
        ];
        return view('admin.procurement.index', compact('orders', 'vendors', 'summary'));
    }

    public function create()
    {
        $vendors = Vendor::orderBy('name')->get();
        return view('admin.procurement.create', compact('vendors'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number'    => 'required|string|unique:purchase_orders,po_number',
            'vendor_id'    => 'required|exists:vendors,id',
            'order_date'   => 'required|date',
            'total_amount' => 'required|numeric|min:0.01',
            'status'       => 'required|in:Draft,Sent,Received,Paid',
            'remarks'      => 'nullable|string|max:500',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('invoice_file')) {
            $validated['invoice_path'] = $request->file('invoice_file')->store('procurement-invoices', 'uploads');
        }
        unset($validated['invoice_file']);

        PurchaseOrder::create($validated);
        return redirect()->route('admin.procurement.index')->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('vendor');
        return view('admin.procurement.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('vendor');
        $vendors = Vendor::orderBy('name')->get();
        return view('admin.procurement.edit', compact('purchaseOrder', 'vendors'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'po_number'    => 'required|string|unique:purchase_orders,po_number,' . $purchaseOrder->id,
            'vendor_id'    => 'required|exists:vendors,id',
            'order_date'   => 'required|date',
            'total_amount' => 'required|numeric|min:0.01',
            'status'       => 'required|in:Draft,Sent,Received,Paid',
            'remarks'      => 'nullable|string|max:500',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('invoice_file')) {
            if ($purchaseOrder->invoice_path) {
                Storage::disk('uploads')->delete($purchaseOrder->invoice_path);
            }
            $validated['invoice_path'] = $request->file('invoice_file')->store('procurement-invoices', 'uploads');
        }
        unset($validated['invoice_file']);

        $purchaseOrder->update($validated);

        return redirect()->route('admin.procurement.show', $purchaseOrder)
            ->with('success', 'Purchase order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Reverse any posted journal entries so the ledger stays balanced
        if ($purchaseOrder->journal_entry_id) {
            $this->ledger->reverseEntry(
                $purchaseOrder->journal_entry_id,
                'Deleted PO: ' . $purchaseOrder->po_number
            );
        }
        if ($purchaseOrder->payment_journal_entry_id) {
            $this->ledger->reverseEntry(
                $purchaseOrder->payment_journal_entry_id,
                'Deleted PO payment: ' . $purchaseOrder->po_number
            );
        }

        AuditLog::create([
            'user_id'        => auth()->id(),
            'event'          => 'deleted',
            'auditable_type' => PurchaseOrder::class,
            'auditable_id'   => $purchaseOrder->id,
            'old_values'     => $purchaseOrder->toArray(),
            'new_values'     => null,
            'ip_address'     => request()->ip(),
            'module'         => 'procurement',
            'description'    => 'Purchase order ' . $purchaseOrder->po_number . ' permanently deleted.',
        ]);

        $purchaseOrder->delete(); // soft delete
        return redirect()->route('admin.procurement.index')
            ->with('success', 'Purchase order ' . $purchaseOrder->po_number . ' moved to trash. Ledger reversed.');
    }

    public function trash()
    {
        $orders = PurchaseOrder::onlyTrashed()->with('vendor')->latest('deleted_at')->paginate(20);
        return view('admin.procurement.trash', compact('orders'));
    }

    public function restore(int $id)
    {
        $purchaseOrder = PurchaseOrder::onlyTrashed()->findOrFail($id);

        // Reverse the reversal entries to restore ledger balances
        foreach (['journal_entry_id', 'payment_journal_entry_id'] as $field) {
            if ($purchaseOrder->$field) {
                $reversal = \App\Models\JournalEntry::where('reversal_of', $purchaseOrder->$field)
                    ->where('status', 'posted')->latest()->first();
                if ($reversal) {
                    $this->ledger->reverseEntry($reversal->id, 'Restored PO: ' . $purchaseOrder->po_number);
                }
            }
        }

        $purchaseOrder->restore();

        AuditLog::create([
            'user_id'        => auth()->id(),
            'event'          => 'restored',
            'auditable_type' => PurchaseOrder::class,
            'auditable_id'   => $purchaseOrder->id,
            'old_values'     => null,
            'new_values'     => $purchaseOrder->toArray(),
            'ip_address'     => request()->ip(),
            'module'         => 'procurement',
            'description'    => 'Purchase order ' . $purchaseOrder->po_number . ' restored from trash.',
        ]);

        return redirect()->route('admin.procurement.trash')
            ->with('success', 'Purchase order ' . $purchaseOrder->po_number . ' restored.');
    }

    public function forceDelete(int $id)
    {
        $purchaseOrder = PurchaseOrder::onlyTrashed()->findOrFail($id);
        $po = $purchaseOrder->po_number;
        $purchaseOrder->forceDelete();
        return redirect()->route('admin.procurement.trash')
            ->with('success', "Purchase order {$po} permanently deleted.");
    }
}
