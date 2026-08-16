<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Branch;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    public function index(Request $request)
    {
        $query = CostCenter::with(['department', 'branch']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $costCenters = $query->orderBy('name')->paginate(20)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();
        $branches    = Branch::active()->orderBy('name')->get();

        return view('admin.cost-centers.index', compact('costCenters', 'departments', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|max:50|unique:cost_centers,code',
            'department_id'  => 'nullable|exists:departments,id',
            'branch_id'      => 'nullable|exists:branches,id',
            'description'    => 'nullable|string',
            'monthly_budget' => 'nullable|numeric|min:0',
            'is_active'      => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        CostCenter::create($data);

        return back()->with('success', 'Cost Center "' . $data['name'] . '" created.');
    }

    public function update(Request $request, CostCenter $costCenter)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|max:50|unique:cost_centers,code,' . $costCenter->id,
            'department_id'  => 'nullable|exists:departments,id',
            'branch_id'      => 'nullable|exists:branches,id',
            'description'    => 'nullable|string',
            'monthly_budget' => 'nullable|numeric|min:0',
            'is_active'      => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $costCenter->update($data);

        return back()->with('success', 'Cost Center updated.');
    }

    public function destroy(CostCenter $costCenter)
    {
        $costCenter->delete();
        return back()->with('success', 'Cost Center deleted.');
    }
}
