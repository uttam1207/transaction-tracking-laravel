<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::withCount([
            'expenses as expense_count',
            'expenses as total_amount' => fn ($q) => $q->select(\Illuminate\Support\Facades\DB::raw('SUM(amount)')),
        ])->orderBy('name')->get();

        return view('admin.expense-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100|unique:expense_categories,name',
            'icon'  => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = true;

        // Default icon and color if not provided
        $validated['icon']  = $validated['icon']  ?: 'bi-tag';
        $validated['color'] = $validated['color'] ?: '#6366f1';

        ExpenseCategory::create($validated);

        return back()->with('success', 'Category "' . $validated['name'] . '" added successfully.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:100|unique:expense_categories,name,' . $expenseCategory->id,
            'icon'  => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $expenseCategory->update([
            'name'  => $validated['name'],
            'slug'  => Str::slug($validated['name']),
            'icon'  => $validated['icon']  ?: $expenseCategory->icon,
            'color' => $validated['color'] ?: $expenseCategory->color,
        ]);

        return back()->with('success', '"' . $expenseCategory->name . '" updated.');
    }

    public function toggleStatus(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->update(['is_active' => !$expenseCategory->is_active]);
        $status = $expenseCategory->is_active ? 'activated' : 'deactivated';
        return back()->with('success', '"' . $expenseCategory->name . '" ' . $status . '.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if (Expense::where('expense_category_id', $expenseCategory->id)->exists()) {
            return back()->with('error', 'Cannot delete "' . $expenseCategory->name . '" — it has expense records. Deactivate it instead.');
        }
        $name = $expenseCategory->name;
        $expenseCategory->delete();
        return back()->with('success', '"' . $name . '" deleted.');
    }
}
