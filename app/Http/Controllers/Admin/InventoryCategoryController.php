<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryCategoryController extends Controller
{
    public function index()
    {
        $categories = InventoryCategory::orderBy('name')->get();
        return view('admin.stock.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:inventory_categories,name',
        ]);

        InventoryCategory::create(['name' => $request->name, 'is_active' => true]);

        return back()->with('success', 'Category "' . $request->name . '" added.');
    }

    public function update(Request $request, InventoryCategory $stockCategory)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:inventory_categories,name,' . $stockCategory->id,
        ]);

        $stockCategory->update(['name' => $request->name]);

        return back()->with('success', 'Category renamed to "' . $request->name . '".');
    }

    public function destroy(InventoryCategory $stockCategory)
    {
        // Check if any items use this category
        if (InventoryItem::where('category', $stockCategory->name)->exists()) {
            return back()->with('error', 'Cannot delete "' . $stockCategory->name . '" — items are assigned to this category.');
        }
        $name = $stockCategory->name;
        $stockCategory->delete();
        return back()->with('success', '"' . $name . '" deleted.');
    }
}