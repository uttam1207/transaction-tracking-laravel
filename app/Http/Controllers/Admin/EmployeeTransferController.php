<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeLifecycleEvent;
use App\Models\EmployeeTransfer;
use App\Traits\ScopesByDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeTransferController extends Controller
{
    use ScopesByDepartment;

    public function index(Request $request)
    {
        $query = EmployeeTransfer::with('employee.user', 'fromDepartment', 'toDepartment', 'fromBranch', 'toBranch', 'approvedBy');
        $this->applyRelatedDeptScope($query);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $transfers   = $query->latest()->paginate(20)->withQueryString();
        $employees   = $this->deptEmployeeQuery()->get();
        $departments = Department::active()->orderBy('name')->get();
        $branches    = Branch::active()->orderBy('name')->get();

        return view('admin.transfers.index', compact('transfers', 'employees', 'departments', 'branches'));
    }

    public function store(Request $request)
    {
        $employee = Employee::findOrFail($request->employee_id);
        $this->authorizeEmployee($employee);

        $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'to_department_id' => 'nullable|exists:departments,id',
            'to_branch_id'     => 'nullable|exists:branches,id',
            'effective_date'   => 'required|date',
            'reason'           => 'nullable|string',
        ]);

        // Prevent duplicate pending transfer for the same employee
        $pendingExists = EmployeeTransfer::where('employee_id', $request->employee_id)
            ->where('status', 'pending')
            ->exists();
        if ($pendingExists) {
            return response()->json([
                'success' => false,
                'message' => 'This employee already has a pending transfer request. Approve or reject it first.',
            ], 422);
        }

        $transfer = EmployeeTransfer::create([
            'employee_id'       => $request->employee_id,
            'from_department_id'=> $employee->department_id,
            'to_department_id'  => $request->to_department_id,
            'from_branch_id'    => $employee->branch_id,
            'to_branch_id'      => $request->to_branch_id,
            'effective_date'    => $request->effective_date,
            'reason'            => $request->reason,
            'status'            => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Transfer request created.', 'transfer' => $transfer]);
    }

    public function approve(Request $request, EmployeeTransfer $employeeTransfer)
    {
        $employee = Employee::findOrFail($employeeTransfer->employee_id);
        $this->authorizeEmployee($employee);

        if ($employeeTransfer->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending transfers can be approved.'], 422);
        }

        DB::transaction(function () use ($employeeTransfer, $employee) {
            $employeeTransfer->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Apply the transfer — update employee's primary dept/branch
            $updates = [];
            if ($employeeTransfer->to_department_id) {
                $updates['department_id'] = $employeeTransfer->to_department_id;

                // Close old primary dept record and create new one
                EmployeeDepartment::where('employee_id', $employee->id)
                    ->where('is_primary', true)
                    ->whereNull('ended_at')
                    ->update(['ended_at' => $employeeTransfer->effective_date]);

                EmployeeDepartment::create([
                    'employee_id'   => $employee->id,
                    'department_id' => $employeeTransfer->to_department_id,
                    'is_primary'    => true,
                    'started_at'    => $employeeTransfer->effective_date,
                ]);
            }
            if ($employeeTransfer->to_branch_id) {
                $updates['branch_id'] = $employeeTransfer->to_branch_id;
            }
            if (!empty($updates)) {
                $employee->update($updates);
            }

            // Log lifecycle event
            EmployeeLifecycleEvent::create([
                'employee_id'  => $employee->id,
                'event_type'   => 'transferred',
                'event_date'   => $employeeTransfer->effective_date,
                'description'  => 'Department/Branch transfer approved.',
                'triggered_by' => auth()->id(),
                'metadata'     => [
                    'from_department_id' => $employeeTransfer->from_department_id,
                    'to_department_id'   => $employeeTransfer->to_department_id,
                    'from_branch_id'     => $employeeTransfer->from_branch_id,
                    'to_branch_id'       => $employeeTransfer->to_branch_id,
                ],
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Transfer approved and applied.']);
    }

    public function reject(Request $request, EmployeeTransfer $employeeTransfer)
    {
        $employee = Employee::findOrFail($employeeTransfer->employee_id);
        $this->authorizeEmployee($employee);

        $request->validate(['rejection_reason' => 'required|string']);

        $employeeTransfer->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json(['success' => true, 'message' => 'Transfer rejected.']);
    }
}
