<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockApiController extends Controller
{
    use ApiResponse;

    /** GET /stock/items — list all inventory items with current stock level */
    public function items(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $items = $query->orderBy('name')->get()->map(function ($item) {
            return [
                'id'                 => $item->id,
                'name'               => $item->name,
                'category'           => $item->category,
                'item_type'          => $item->item_type,
                'unit'               => $item->unit,
                'available_quantity' => $item->available_quantity,
                'min_stock'          => $item->min_stock,
                'stock_status'       => $item->stock_status,
                'expiry_date'        => $item->expiry_date?->toDateString(),
                'is_active'          => $item->is_active,
            ];
        });

        return $this->success($items, 'Stock items retrieved successfully.');
    }

    /** GET /stock/summary */
    public function summary(Request $request)
    {
        $user = $request->user();
        $allItems = InventoryItem::with('stockMovements')->get();

        $data = [
            'total_items'         => $allItems->count(),
            'low_stock'           => $allItems->filter(fn ($i) => $i->stock_status === 'Low Stock')->count(),
            'out_of_stock'        => $allItems->filter(fn ($i) => $i->stock_status === 'Out of Stock')->count(),
            'optimal'             => $allItems->filter(fn ($i) => $i->stock_status === 'Optimal')->count(),
            'received_this_month' => StockMovement::where('type', 'in')
                ->when($user->isFarmer(), fn ($q) => $q->where('recorded_by', $user->id))
                ->whereMonth('date', now()->month)->sum('quantity'),
            'issued_this_month'   => StockMovement::where('type', 'out')
                ->when($user->isFarmer(), fn ($q) => $q->where('recorded_by', $user->id))
                ->whereMonth('date', now()->month)->sum('quantity'),
        ];

        return $this->success($data, 'Stock summary retrieved successfully.');
    }

    /** GET /stock/movements — movement audit log */
    public function movements(Request $request)
    {
        $user  = $request->user();
        $query = StockMovement::with('inventoryItem');

        if ($user->isFarmer()) {
            $query->where('recorded_by', $user->id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('item_id')) {
            $query->where('inventory_item_id', $request->item_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $movements = $query->orderByDesc('date')->orderByDesc('id')
            ->paginate($request->per_page ?? 20);

        return $this->paginated($movements, 'Stock movements retrieved successfully.');
    }

    /** POST /stock/in — record stock received */
    public function storeIn(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id'   => 'required|exists:inventory_items,id',
            'quantity'            => 'required|numeric|min:0.01',
            'date'                => 'required|date',
            'source_purpose'      => 'nullable|string|max:200',
            'issued_to_or_vendor' => 'nullable|string|max:200',
            'remarks'             => 'nullable|string|max:1000',
        ]);

        $validated['type']        = 'in';
        $validated['recorded_by'] = $request->user()->id;

        $movement = StockMovement::create($validated);
        $movement->load('inventoryItem');

        return $this->success($movement, 'Stock in recorded successfully.', 201);
    }

    /** POST /stock/out — record stock issued */
    public function storeOut(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id'   => 'required|exists:inventory_items,id',
            'quantity'            => 'required|numeric|min:0.01',
            'date'                => 'required|date',
            'source_purpose'      => 'required|string|max:200',
            'issued_to_or_vendor' => 'nullable|string|max:200',
            'remarks'             => 'nullable|string|max:1000',
        ]);

        $item = InventoryItem::findOrFail($validated['inventory_item_id']);
        if ((float)$validated['quantity'] > $item->available_quantity) {
            return $this->error(
                "Insufficient stock. Available: {$item->available_quantity} {$item->unit}.",
                422
            );
        }

        $validated['type']        = 'out';
        $validated['recorded_by'] = $request->user()->id;

        $movement = StockMovement::create($validated);
        $movement->load('inventoryItem');

        return $this->success($movement, 'Stock out recorded successfully.', 201);
    }
}