<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Services\AttendanceService;
use App\Traits\ScopesByDepartment;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ScopesByDepartment;

    public function __construct(private AttendanceService $attendanceService)
    {
    }

    public function index(Request $request)
    {
        $date = $request->date ? \Carbon\Carbon::parse($request->date) : today();

        $query = Attendance::with('employee.user', 'employee.department')
            ->whereDate('date', $date);

        // Always restrict managers to their own department
        $this->applyRelatedDeptScope($query);

        // Admins may additionally filter by dept; managers are already scoped
        if ($request->department_id && $this->managedDeptId() === null) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $attendance = $query->paginate(15)->withQueryString();

        // Stats scoped to the manager's dept as well
        $statsBase = Attendance::whereDate('date', $date);
        $this->applyRelatedDeptScope($statsBase);

        $totalActive = $this->applyDeptScope(Employee::active())->count();

        $stats = [
            'present'  => (clone $statsBase)->whereIn('status', ['present', 'late'])->count(),
            'absent'   => $totalActive - (clone $statsBase)->count(),
            'late'     => (clone $statsBase)->where('status', 'late')->count(),
            'on_leave' => (clone $statsBase)->where('status', 'on_leave')->count(),
        ];

        $departments = Department::orderBy('name')->get();

        return view('admin.attendance.index', compact('attendance', 'stats', 'date', 'departments'));
    }

    public function report(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year ?? now()->year;

        // Managers only see their dept employees
        $empQuery = Employee::with('user', 'department')->active();
        $this->applyDeptScope($empQuery);
        $employees = $empQuery->get();

        $reportData = $employees->map(function ($employee) use ($month, $year) {
            return [
                'employee' => $employee,
                'report'   => $this->attendanceService->getMonthlyReport($employee, $month, $year),
            ];
        });

        return view('admin.attendance.report', compact('reportData', 'month', 'year'));
    }

    public function leaves(Request $request)
    {
        $query = Leave::with('employee.user', 'approver');

        // Scope leaves to manager's dept employees
        $this->applyRelatedDeptScope($query);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $leaves = $query->latest()->paginate(15)->withQueryString();

        return view('admin.attendance.leaves', compact('leaves'));
    }

    public function approveLeave(Request $request, Leave $leave)
    {
        // Managers can only approve/reject leaves for their own dept employees
        $employee = Employee::findOrFail($leave->employee_id);
        $this->authorizeEmployee($employee);

        $request->validate([
            'action'           => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);

        $status = $request->action === 'approve' ? 'approved' : 'rejected';
        $leave->update([
            'status'           => $status,
            'approved_by'      => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
            'actioned_at'      => now(),
        ]);

        if ($status === 'approved') {
            $date = $leave->from_date->copy();
            while ($date->lte($leave->to_date)) {
                if (!$date->isWeekend()) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $leave->employee_id, 'date' => $date->format('Y-m-d')],
                        ['status' => 'on_leave']
                    );
                }
                $date->addDay();
            }
        }

        return response()->json(['success' => true, 'message' => "Leave {$status}."]);
    }
}
