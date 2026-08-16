<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryComponent;
use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    public function index(Request $request)
    {
        $query = SalaryComponent::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        $components = $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.salary-components.index', compact('components'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'required|string|max:20|unique:salary_components,code',
            'type'             => 'required|in:earning,deduction',
            'calculation_type' => 'required|in:fixed,percentage_of_basic,percentage_of_gross',
            'default_value'    => 'nullable|numeric|min:0',
            'is_mandatory'     => 'boolean',
            'is_taxable'       => 'boolean',
            'sort_order'       => 'nullable|integer|min:0',
            'is_active'        => 'boolean',
            'description'      => 'nullable|string',
        ]);

        $data['is_mandatory'] = $request->boolean('is_mandatory');
        $data['is_taxable']   = $request->boolean('is_taxable');
        $data['is_active']    = $request->boolean('is_active', true);
        $data['sort_order']   = $data['sort_order'] ?? 0;

        SalaryComponent::create($data);

        return back()->with('success', 'Component "' . $data['name'] . '" created.');
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'required|string|max:20|unique:salary_components,code,' . $salaryComponent->id,
            'type'             => 'required|in:earning,deduction',
            'calculation_type' => 'required|in:fixed,percentage_of_basic,percentage_of_gross',
            'default_value'    => 'nullable|numeric|min:0',
            'is_mandatory'     => 'boolean',
            'is_taxable'       => 'boolean',
            'sort_order'       => 'nullable|integer|min:0',
            'is_active'        => 'boolean',
            'description'      => 'nullable|string',
        ]);

        $data['is_mandatory'] = $request->boolean('is_mandatory');
        $data['is_taxable']   = $request->boolean('is_taxable');
        $data['is_active']    = $request->boolean('is_active', true);
        $salaryComponent->update($data);

        return back()->with('success', 'Component updated.');
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        if ($salaryComponent->structureItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete — component is used in salary structures.',
            ], 422);
        }

        $salaryComponent->delete();
        return response()->json(['success' => true]);
    }
}
