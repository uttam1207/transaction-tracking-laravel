<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StockExport;
use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    // Current Stock Screen (/stock)
    public function index(Request $request)
    {
        $query = InventoryItem::with('stockMovements');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        $items = $query->orderBy('name')->get();

        // Apply Stock Status Filter in Collection
        if ($request->status) {
            $items = $items->filter(fn ($item) => strtolower(str_replace(' ', '_', $item->stock_status)) === strtolower($request->status));
        }

        // Expiry alerts for the page banner
        $expiryAlerts = InventoryItem::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date')
            ->get();

        $summary = [
            'total_items'         => InventoryItem::count(),
            'low_stock'           => InventoryItem::all()->filter(fn ($i) => $i->stock_status === 'Low Stock')->count(),
            'out_of_stock'        => InventoryItem::all()->filter(fn ($i) => $i->stock_status === 'Out of Stock')->count(),
            'received_this_month' => StockMovement::where('type', 'in')->whereMonth('date', now()->month)->sum('quantity'),
            'issued_this_month'   => StockMovement::where('type', 'out')->whereMonth('date', now()->month)->sum('quantity'),
            'expiring_soon'       => $expiryAlerts->count(),
        ];

        $categories = InventoryCategory::active()->orderBy('name')->get();

        return view('admin.stock.index', compact('items', 'summary', 'categories', 'expiryAlerts'));
    }

    // Stock In Form (/stock/in)
    public function stockInForm()
    {
        $items = InventoryItem::where('is_active', true)->orderBy('name')->get();
        return view('admin.stock.in', compact('items'));
    }

    // Record Stock In
    public function storeStockIn(Request $request)
    {
        $validated = $request->validate([
            'date'                 => 'required|date',
            'inventory_item_id'    => 'required|exists:inventory_items,id',
            'quantity'             => 'required|numeric|min:0.01',
            'source_purpose'       => 'nullable|string|max:200',
            'issued_to_or_vendor'  => 'nullable|string|max:200',
            'remarks'              => 'nullable|string|max:1000',
        ]);

        $validated['type']        = 'in';
        $validated['recorded_by'] = auth()->id();

        StockMovement::create($validated);

        return redirect()->route('admin.stock.index')
            ->with('success', 'Stock In recorded successfully.');
    }

    // Stock Out Form (/stock/out)
    public function stockOutForm()
    {
        $items = InventoryItem::where('is_active', true)->orderBy('name')->get();
        return view('admin.stock.out', compact('items'));
    }

    // Record Stock Out
    public function storeStockOut(Request $request)
    {
        $validated = $request->validate([
            'date'                => 'required|date',
            'inventory_item_id'   => 'required|exists:inventory_items,id',
            'quantity'            => 'required|numeric|min:0.01',
            'source_purpose'      => 'required|string|max:200',
            'issued_to_or_vendor' => 'nullable|string|max:200',
            'remarks'             => 'nullable|string|max:1000',
        ]);

        $item = InventoryItem::findOrFail($request->inventory_item_id);
        if ($validated['quantity'] > $item->available_quantity) {
            return back()->withInput()->withErrors([
                'quantity' => "Insufficient stock! Current available stock is {$item->available_quantity} {$item->unit}."
            ]);
        }

        $validated['type']        = 'out';
        $validated['recorded_by'] = auth()->id();

        StockMovement::create($validated);

        return redirect()->route('admin.stock.index')
            ->with('success', 'Stock Out recorded successfully.');
    }

    // Stock Adjustment Form (/stock/adjustment)
    public function adjustmentForm()
    {
        $items = InventoryItem::where('is_active', true)->orderBy('name')->get();
        return view('admin.stock.adjustment', compact('items'));
    }

    // Record Stock Adjustment
    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'date'             => 'required|date',
            'inventory_item_id'=> 'required|exists:inventory_items,id',
            'adjustment_type'  => 'required|in:Increase,Decrease',
            'quantity'         => 'required|numeric|min:0.01',
            'reason'           => 'required|string|max:200',
            'remarks'          => 'nullable|string|max:1000',
        ]);

        $item = InventoryItem::findOrFail($request->inventory_item_id);

        if ($validated['adjustment_type'] === 'Decrease' && $validated['quantity'] > $item->available_quantity) {
            return back()->withInput()->withErrors([
                'quantity' => "Cannot decrease stock below zero! Current available stock is {$item->available_quantity} {$item->unit}."
            ]);
        }

        StockMovement::create([
            'inventory_item_id'   => $validated['inventory_item_id'],
            'type'                => 'adjustment',
            'quantity'            => $validated['quantity'],
            'date'                => $validated['date'],
            'reason'              => $validated['adjustment_type'] . ' - ' . $validated['reason'],
            'remarks'             => $validated['remarks'],
            'recorded_by'         => auth()->id(),
        ]);

        return redirect()->route('admin.stock.index')
            ->with('success', 'Stock adjustment completed.');
    }

    // Stock Movement Audit Log
    public function movements(Request $request)
    {
        $query = StockMovement::with('inventoryItem', 'recorder');

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->item_id) {
            $query->where('inventory_item_id', $request->item_id);
        }

        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $movements = $query->latest('date')->paginate(25)->withQueryString();
        $items     = InventoryItem::orderBy('name')->get();

        return view('admin.stock.movements', compact('movements', 'items'));
    }

    // Export Stock Report as PDF
    public function exportPdf()
    {
        $items = InventoryItem::with('stockMovements')->orderBy('name')->get();
        $pdf   = Pdf::loadView('admin.stock.pdf', compact('items'))->setPaper('a4', 'portrait');
        return $pdf->download('stock_report_' . now()->format('Ymd_His') . '.pdf');
    }

    // Export Stock Report as Excel
    public function exportExcel(Request $request)
    {
        $filters  = $request->only(['category', 'status']);
        $filename = 'stock_report_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new StockExport($filters), $filename);
    }

    // Export Stock Report as CSV
    public function exportCsv(Request $request)
    {
        $filters  = $request->only(['category', 'status']);
        $filename = 'stock_report_' . now()->format('Ymd_His') . '.csv';
        return Excel::download(new StockExport($filters), $filename, \Maatwebsite\Excel\Excel::CSV);
    }
}
