<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryItemType;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }

        $items      = $query->orderBy('name')->get();
        $categories = InventoryCategory::active()->orderBy('name')->get();
        $types      = InventoryItemType::active()->orderBy('name')->get();

        return view('admin.stock.items.index', compact('items', 'categories', 'types'));
    }

    public function create()
    {
        $categories = InventoryCategory::active()->orderBy('name')->get();
        $types      = InventoryItemType::active()->orderBy('name')->get();
        return view('admin.stock.items.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:200|unique:inventory_items,name',
            'category'    => 'required|string|max:100|exists:inventory_categories,name',
            'item_type'   => 'nullable|string|max:100|exists:inventory_item_types,name',
            'unit'        => 'required|string|max:50',
            'min_stock'   => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'description' => 'nullable|string|max:500',
        ]);
        $validated['is_active'] = true;

        InventoryItem::create($validated);

        return redirect()->route('admin.stock-items.index')
            ->with('success', 'Item "' . $validated['name'] . '" added successfully.');
    }

    public function edit(InventoryItem $item)
    {
        $categories = InventoryCategory::active()->orderBy('name')->get();
        $types      = InventoryItemType::active()->orderBy('name')->get();
        return view('admin.stock.items.edit', compact('item', 'categories', 'types'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:200|unique:inventory_items,name,' . $item->id,
            'category'    => 'required|string|max:100|exists:inventory_categories,name',
            'item_type'   => 'nullable|string|max:100|exists:inventory_item_types,name',
            'unit'        => 'required|string|max:50',
            'min_stock'   => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'description' => 'nullable|string|max:500',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $item->update($validated);

        return redirect()->route('admin.stock-items.index')
            ->with('success', '"' . $item->name . '" updated successfully.');
    }

    public function destroy(InventoryItem $item)
    {
        if ($item->stockMovements()->exists()) {
            return back()->with('error', 'Cannot delete "' . $item->name . '" — it has stock movement records. Deactivate it instead.');
        }
        $name = $item->name;
        $item->delete();
        return redirect()->route('admin.stock-items.index')
            ->with('success', '"' . $name . '" deleted.');
    }
}