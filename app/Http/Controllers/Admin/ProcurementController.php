<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with('vendor')->latest('order_date')->paginate(15);
        $vendors = Vendor::orderBy('name')->get();
        $summary = [
            'total_vendors' => Vendor::count(),
            'total_po_value' => PurchaseOrder::sum('total_amount'),
            'pending_pos' => PurchaseOrder::where('status', 'Sent')->count(),
        ];
        return view('admin.procurement.index', compact('orders', 'vendors', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|unique:purchase_orders,po_number',
            'vendor_id' => 'required|exists:vendors,id',
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:Draft,Sent,Received,Paid',
            'remarks' => 'nullable|string|max:500',
        ]);

        PurchaseOrder::create($validated);
        return redirect()->route('admin.procurement.index')->with('success', 'Purchase order created.');
    }
}
