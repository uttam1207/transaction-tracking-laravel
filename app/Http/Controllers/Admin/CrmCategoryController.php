<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmCategory;
use Illuminate\Http\Request;

class CrmCategoryController extends Controller
{
    public function index()
    {
        $categories = CrmCategory::withCount('customers')->orderBy('name')->get();
        return view('admin.crm-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:crm_categories,name',
            'icon'        => 'nullable|string|max:60',
            'color'       => 'nullable|string|max:30',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['icon']  = $validated['icon']  ?? 'bi-people';
        $validated['color'] = $validated['color'] ?? '#6366f1';

        CrmCategory::create($validated);

        return back()->with('success', 'Category "' . $validated['name'] . '" added successfully.');
    }

    public function update(Request $request, CrmCategory $crmCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:crm_categories,name,' . $crmCategory->id,
            'icon'        => 'nullable|string|max:60',
            'color'       => 'nullable|string|max:30',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['icon']  = $validated['icon']  ?? 'bi-people';
        $validated['color'] = $validated['color'] ?? '#6366f1';

        $crmCategory->update($validated);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(CrmCategory $crmCategory)
    {
        $count = $crmCategory->customers()->count();
        if ($count > 0) {
            return back()->with('error', 'Cannot delete "' . $crmCategory->name . '" — it has ' . $count . ' customer(s). Reassign them first.');
        }

        $crmCategory->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}