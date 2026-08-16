<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    public function index(Request $request)
    {
        $year        = (int) ($request->year ?? now()->year);
        $leaveTypes  = LeaveType::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();

        $query = LeaveBalance::with(['employee.user', 'employee.department', 'leaveType'])
            ->where('year', $year);

        if ($request->leave_type_id) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        if ($request->department_id) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->search) {
            $query->whereHas('employee.user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $balances = $query->orderBy('employee_id')->paginate(20)->withQueryString();

        return view('admin.leave-balances.index', compact('balances', 'leaveTypes', 'departments', 'year'));
    }

    /**
     * Bulk-allocate — create LeaveBalance records for all active employees
     * for a given year + leave_type_id using the type's max_days_per_year.
     */
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'year'          => 'required|integer|min:2020|max:2099',
        ]);

        $leaveType = LeaveType::findOrFail($request->leave_type_id);
        $year      = (int) $request->year;
        $created   = 0;

        Employee::active()->pluck('id')->each(function ($empId) use ($leaveType, $year, &$created) {
            $existed = LeaveBalance::firstOrCreate(
                ['employee_id' => $empId, 'leave_type_id' => $leaveType->id, 'year' => $year],
                ['allocated_days' => $leaveType->max_days_per_year]
            );
            if ($existed->wasRecentlyCreated) {
                $created++;
            }
        });

        return back()->with('success', "Allocated {$leaveType->name} for {$year} — {$created} new records created.");
    }

    /**
     * Adjust a single employee's allocated leave days.
     */
    public function adjust(Request $request, LeaveBalance $leaveBalance)
    {
        $request->validate([
            'allocated_days'       => 'required|numeric|min:0',
            'carried_forward_days' => 'nullable|numeric|min:0',
        ]);

        $leaveBalance->update([
            'allocated_days'       => $request->allocated_days,
            'carried_forward_days' => $request->carried_forward_days ?? $leaveBalance->carried_forward_days,
        ]);

        return response()->json(['success' => true, 'message' => 'Balance adjusted.']);
    }
}
