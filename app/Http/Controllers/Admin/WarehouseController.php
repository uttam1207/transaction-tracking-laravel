<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryLocationStock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::with('manager')
            ->withCount('locations')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%"))
            ->when($request->filled('active'), fn($q) => $q->where('is_active', $request->active))
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        $managers = User::active()->orderBy('name')->get();
        return view('admin.warehouses.index', compact('warehouses', 'managers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'       => 'required|string|max:20|unique:warehouses,code',
            'name'       => 'required|string|max:150',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $warehouse = Warehouse::create($request->only('code', 'name', 'address', 'city', 'country', 'manager_id'));
        return response()->json(['success' => true, 'message' => 'Warehouse created.', 'warehouse' => $warehouse]);
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load('manager', 'locations');
        $stock = InventoryLocationStock::with('inventoryItem', 'location')
            ->where('warehouse_id', $warehouse->id)
            ->orderBy('inventory_item_id')
            ->paginate(30);

        $items = InventoryItem::where('is_active', true)->orderBy('name')->get();
        return view('admin.warehouses.show', compact('warehouse', 'stock', 'items'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'code' => "required|string|max:20|unique:warehouses,code,{$warehouse->id}",
            'name' => 'required|string|max:150',
        ]);
        $warehouse->update($request->only('code', 'name', 'address', 'city', 'country', 'manager_id', 'is_active'));
        return response()->json(['success' => true, 'message' => 'Warehouse updated.']);
    }

    public function storeLocation(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'code' => 'required|string|max:30|unique:warehouse_locations,code',
            'name' => 'required|string|max:100',
            'type' => 'required|in:zone,aisle,rack,bin',
        ]);
        $location = $warehouse->locations()->create($request->only('code', 'name', 'type'));
        return response()->json(['success' => true, 'message' => 'Location added.', 'location' => $location]);
    }

    // ── Stock Transfers ───────────────────────────────────────────────────────

    public function transferIndex(Request $request)
    {
        $transfers = StockTransfer::with('fromWarehouse', 'toWarehouse', 'createdBy')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->warehouse_id, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('from_warehouse_id', $request->warehouse_id)
                   ->orWhere('to_warehouse_id', $request->warehouse_id);
            }))
            ->latest('transfer_date')
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('admin.warehouses.transfers', compact('transfers', 'warehouses'));
    }

    public function transferCreate()
    {
        $warehouses = Warehouse::active()->with('locations')->orderBy('name')->get();
        $items      = InventoryItem::where('is_active', true)->orderBy('name')->get();
        return view('admin.warehouses.transfer-create', compact('warehouses', 'items'));
    }

    public function transferStore(Request $request)
    {
        $request->validate([
            'from_warehouse_id'   => 'required|exists:warehouses,id',
            'to_warehouse_id'     => 'required|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date'       => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity'          => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($request) {
            $transfer = StockTransfer::create([
                'transfer_number'    => StockTransfer::generateNumber(),
                'from_warehouse_id'  => $request->from_warehouse_id,
                'to_warehouse_id'    => $request->to_warehouse_id,
                'from_location_id'   => $request->from_location_id ?: null,
                'to_location_id'     => $request->to_location_id ?: null,
                'transfer_date'      => $request->transfer_date,
                'notes'              => $request->notes,
                'status'             => 'draft',
                'created_by'         => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                StockTransferItem::create([
                    'stock_transfer_id'  => $transfer->id,
                    'inventory_item_id'  => $item['inventory_item_id'],
                    'quantity'           => $item['quantity'],
                    'received_qty'       => 0,
                    'remarks'            => $item['remarks'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.warehouses.transfers')->with('success', 'Transfer created.');
    }

    public function transferComplete(Request $request, StockTransfer $transfer)
    {
        if ($transfer->status !== 'draft' && $transfer->status !== 'in_transit') {
            return response()->json(['success' => false, 'message' => 'Cannot complete this transfer.'], 422);
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Deduct from source
                InventoryLocationStock::where('inventory_item_id', $item->inventory_item_id)
                    ->where('warehouse_id', $transfer->from_warehouse_id)
                    ->decrement('quantity', $item->quantity);

                // Add to destination
                InventoryLocationStock::updateOrCreate(
                    [
                        'inventory_item_id' => $item->inventory_item_id,
                        'warehouse_id'      => $transfer->to_warehouse_id,
                        'location_id'       => $transfer->to_location_id,
                    ],
                    ['quantity' => DB::raw("quantity + {$item->quantity}")]
                );

                $item->update(['received_qty' => $item->quantity]);
            }
            $transfer->update(['status' => 'completed']);
        });

        return response()->json(['success' => true, 'message' => 'Transfer completed.']);
    }
}
