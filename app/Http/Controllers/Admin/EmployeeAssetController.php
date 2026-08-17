<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAsset;
use App\Traits\ScopesByDepartment;
use Illuminate\Http\Request;

class EmployeeAssetController extends Controller
{
    use ScopesByDepartment;

    public function index(Request $request)
    {
        $query = EmployeeAsset::with('employee.user');
        $this->applyRelatedDeptScope($query);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->employee_id) {
            $empIds = $this->managedEmployeeIds();
            if ($empIds === null || in_array((int) $request->employee_id, $empIds)) {
                $query->where('employee_id', $request->employee_id);
            }
        }
        if ($request->search) {
            $query->where(fn($q) => $q
                ->where('asset_name', 'like', '%' . $request->search . '%')
                ->orWhere('asset_code', 'like', '%' . $request->search . '%')
                ->orWhere('serial_number', 'like', '%' . $request->search . '%')
            );
        }

        $assets    = $query->latest()->paginate(20)->withQueryString();
        $employees = $this->deptEmployeeQuery()->get();

        $stats = [
            'issued'   => EmployeeAsset::where('status', 'issued')->count(),
            'returned' => EmployeeAsset::where('status', 'returned')->count(),
            'damaged'  => EmployeeAsset::where('status', 'damaged')->count(),
            'lost'     => EmployeeAsset::where('status', 'lost')->count(),
        ];

        return view('admin.employee-assets.index', compact('assets', 'employees', 'stats'));
    }

    public function store(Request $request)
    {
        $employee = Employee::findOrFail($request->employee_id);
        $this->authorizeEmployee($employee);

        $request->validate([
            'employee_id'       => 'required|exists:employees,id',
            'asset_name'        => 'required|string|max:150',
            'asset_code'        => 'required|string|max:50|unique:employee_assets,asset_code',
            'category'          => 'required|in:laptop,phone,tablet,vehicle,furniture,equipment,access_card,other',
            'serial_number'     => 'nullable|string|max:100',
            'description'       => 'nullable|string',
            'issued_date'       => 'required|date',
            'condition_on_issue'=> 'nullable|string|max:50',
            'notes'             => 'nullable|string',
        ]);

        $asset = EmployeeAsset::create(array_merge(
            $request->only(['employee_id', 'asset_name', 'asset_code', 'category', 'serial_number',
                            'description', 'issued_date', 'condition_on_issue', 'notes']),
            ['status' => 'issued', 'issued_by' => auth()->id()]
        ));

        return response()->json(['success' => true, 'message' => 'Asset issued.', 'asset' => $asset->load('employee.user')]);
    }

    public function update(Request $request, EmployeeAsset $employeeAsset)
    {
        $employee = Employee::findOrFail($employeeAsset->employee_id);
        $this->authorizeEmployee($employee);

        $request->validate([
            'status'              => 'required|in:issued,returned,damaged,lost',
            'return_date'         => 'nullable|date',
            'condition_on_return' => 'nullable|string|max:50',
            'notes'               => 'nullable|string',
        ]);

        $employeeAsset->update($request->only(['status', 'return_date', 'condition_on_return', 'notes']));

        return response()->json(['success' => true, 'message' => 'Asset updated.']);
    }

    public function destroy(EmployeeAsset $employeeAsset)
    {
        $employee = Employee::findOrFail($employeeAsset->employee_id);
        $this->authorizeEmployee($employee);

        $employeeAsset->delete();
        return response()->json(['success' => true, 'message' => 'Asset record deleted.']);
    }
}
