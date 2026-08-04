<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmCustomer;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SalesModuleController extends Controller
{
    public function index()
    {
        $sales = SalesOrder::with('customer')->latest('sale_date')->paginate(15);
        $customers = CrmCustomer::orderBy('name')->get();
        $summary = [
            'total_sales' => SalesOrder::sum('total_amount'),
            'milk_sales' => SalesOrder::where('item_type', 'Milk Sales')->sum('total_amount'),
            'animal_sales' => SalesOrder::where('item_type', 'Animal Sales')->sum('total_amount'),
            'pending_payment' => SalesOrder::where('payment_status', 'Pending')->sum('total_amount'),
        ];
        return view('admin.sales.index', compact('sales', 'customers', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:sales_orders,invoice_number',
            'crm_customer_id' => 'nullable|exists:crm_customers,id',
            'item_type' => 'required|in:Milk Sales,Animal Sales,Feed Sales,Dung Sales,Franchise Royalty',
            'sale_date' => 'required|date',
            'quantity' => 'required|numeric|min:0.01',
            'rate' => 'required|numeric|min:0.01',
            'payment_status' => 'required|in:Paid,Pending,Partial',
        ]);

        $validated['total_amount'] = $validated['quantity'] * $validated['rate'];

        SalesOrder::create($validated);
        return redirect()->route('admin.sales.index')->with('success', 'Sales invoice created.');
    }
}
