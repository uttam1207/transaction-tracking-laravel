<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService)
    {
    }

    public function index(Request $request)
    {
        $date = $request->date ? \Carbon\Carbon::parse($request->date) : today();

        $query = Attendance::with('employee.user', 'employee.department')
            ->whereDate('date', $date);

        if ($request->department_id) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $attendance = $query->paginate(15)->withQueryString();

        $stats = [
            'present' => Attendance::whereDate('date', $date)->whereIn('status', ['present', 'late'])->count(),
            'absent' => Employee::active()->count() - Attendance::whereDate('date', $date)->count(),
            'late' => Attendance::whereDate('date', $date)->where('status', 'late')->count(),
            'on_leave' => Attendance::whereDate('date', $date)->where('status', 'on_leave')->count(),
        ];

        $departments = Department::orderBy('name')->get();
        return view('admin.attendance.index', compact('attendance', 'stats', 'date', 'departments'));
    }

    public function report(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $employees = Employee::with('user', 'department')->active()->get();

        $reportData = $employees->map(function ($employee) use ($month, $year) {
            return [
                'employee' => $employee,
                'report' => $this->attendanceService->getMonthlyReport($employee, $month, $year),
            ];
        });

        return view('admin.attendance.report', compact('reportData', 'month', 'year'));
    }

    public function leaves(Request $request)
    {
        $query = Leave::with('employee.user', 'approver');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $leaves = $query->latest()->paginate(15)->withQueryString();

        return view('admin.attendance.leaves', compact('leaves'));
    }

    public function approveLeave(Request $request, Leave $leave)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);

        $status = $request->action === 'approve' ? 'approved' : 'rejected';
        $leave->update([
            'status' => $status,
            'approved_by' => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
            'actioned_at' => now(),
        ]);

        if ($status === 'approved') {
            // Mark attendance as on_leave for those days
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
