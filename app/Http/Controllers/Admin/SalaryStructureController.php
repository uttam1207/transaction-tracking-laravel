<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\SalaryStructure;
use App\Models\SalaryStructureItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryStructureController extends Controller
{
    public function index(Request $request)
    {
        $query = SalaryStructure::with(['employee.user', 'department', 'items.component']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $structures  = $query->orderBy('name')->paginate(15)->withQueryString();
        $components  = SalaryComponent::active()->orderBy('sort_order')->orderBy('name')->get();
        $employees   = Employee::with('user')->active()->orderBy('id')->get();
        $departments = Department::active()->orderBy('name')->get();

        return view('admin.salary-structures.index', compact('structures', 'components', 'employees', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'required|string|max:20|unique:salary_structures,code',
            'employee_id'   => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_default'    => 'boolean',
            'effective_from'=> 'required|date',
            'is_active'     => 'boolean',
            'components'    => 'nullable|array',
            'components.*.salary_component_id' => 'required|exists:salary_components,id',
            'components.*.value' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $structure = SalaryStructure::create([
                'name'           => $request->name,
                'code'           => $request->code,
                'employee_id'    => $request->employee_id,
                'department_id'  => $request->department_id,
                'is_default'     => $request->boolean('is_default'),
                'effective_from' => $request->effective_from,
                'is_active'      => $request->boolean('is_active', true),
            ]);

            foreach ($request->components ?? [] as $component) {
                SalaryStructureItem::create([
                    'salary_structure_id'  => $structure->id,
                    'salary_component_id'  => $component['salary_component_id'],
                    'value'                => $component['value'],
                ]);
            }
        });

        return back()->with('success', 'Salary structure "' . $request->name . '" created.');
    }

    public function update(Request $request, SalaryStructure $salaryStructure)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'required|string|max:20|unique:salary_structures,code,' . $salaryStructure->id,
            'employee_id'   => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'is_default'    => 'boolean',
            'effective_from'=> 'required|date',
            'is_active'     => 'boolean',
            'components'    => 'nullable|array',
            'components.*.salary_component_id' => 'required|exists:salary_components,id',
            'components.*.value' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $salaryStructure) {
            $salaryStructure->update([
                'name'           => $request->name,
                'code'           => $request->code,
                'employee_id'    => $request->employee_id,
                'department_id'  => $request->department_id,
                'is_default'     => $request->boolean('is_default'),
                'effective_from' => $request->effective_from,
                'is_active'      => $request->boolean('is_active', true),
            ]);

            // Replace items
            $salaryStructure->items()->delete();
            foreach ($request->components ?? [] as $component) {
                SalaryStructureItem::create([
                    'salary_structure_id'  => $salaryStructure->id,
                    'salary_component_id'  => $component['salary_component_id'],
                    'value'                => $component['value'],
                ]);
            }
        });

        return back()->with('success', 'Salary structure updated.');
    }

    public function destroy(SalaryStructure $salaryStructure)
    {
        $salaryStructure->items()->delete();
        $salaryStructure->delete();
        return response()->json(['success' => true]);
    }
}
