<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItemType;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemTypeController extends Controller
{
    public function index()
    {
        $types = InventoryItemType::orderBy('name')->get();
        return view('admin.stock.types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:inventory_item_types,name',
        ]);

        InventoryItemType::create(['name' => $request->name, 'is_active' => true]);

        return back()->with('success', 'Item type "' . $request->name . '" added.');
    }

    public function update(Request $request, InventoryItemType $stockType)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:inventory_item_types,name,' . $stockType->id,
        ]);

        $stockType->update(['name' => $request->name]);

        return back()->with('success', 'Type renamed to "' . $request->name . '".');
    }

    public function destroy(InventoryItemType $stockType)
    {
        // Check if any items use this type
        if (InventoryItem::where('item_type', $stockType->name)->exists()) {
            return back()->with('error', 'Cannot delete "' . $stockType->name . '" — items are assigned to this type.');
        }
        $name = $stockType->name;
        $stockType->delete();
        return back()->with('success', '"' . $name . '" deleted.');
    }
}