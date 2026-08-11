<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactCategory;
use Illuminate\Http\Request;

class ContactCategoryController extends Controller
{
    public function index()
    {
        $categories = ContactCategory::withCount('contacts')->orderBy('name')->get();
        return view('admin.contact-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:contact_categories,name',
            'icon'        => 'nullable|string|max:60',
            'color'       => 'nullable|string|max:30',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['icon']  = $validated['icon']  ?? 'bi-people';
        $validated['color'] = $validated['color'] ?? '#6366f1';

        ContactCategory::create($validated);

        return back()->with('success', 'Category "' . $validated['name'] . '" added successfully.');
    }

    public function update(Request $request, ContactCategory $contactCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:contact_categories,name,' . $contactCategory->id,
            'icon'        => 'nullable|string|max:60',
            'color'       => 'nullable|string|max:30',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['icon']  = $validated['icon']  ?? 'bi-people';
        $validated['color'] = $validated['color'] ?? '#6366f1';

        $contactCategory->update($validated);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(ContactCategory $contactCategory)
    {
        if ($contactCategory->contacts()->count() > 0) {
            return back()->with('error', 'Cannot delete "' . $contactCategory->name . '" — it has ' . $contactCategory->contacts()->count() . ' contact(s). Reassign them first.');
        }

        $contactCategory->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}