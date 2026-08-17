<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private NotificationService $notificationService,
    ) {}


    public function index(Request $request)
    {
        $employee = auth()->user()->employee;
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        $monthlyReport = $this->attendanceService->getMonthlyReport($employee, $month, $year);
        $todayAttendance = Attendance::where('employee_id', $employee->id)->whereDate('date', today())->first();

        return view('employee.attendance.index', compact('attendance', 'monthlyReport', 'todayAttendance', 'month', 'year'));
    }

    public function checkIn(Request $request)
    {
        $employee = auth()->user()->employee;

        try {
            $attendance = $this->attendanceService->checkIn($employee);
            return response()->json([
                'success' => true,
                'message' => 'Checked in successfully!',
                'check_in' => $attendance->check_in->format('H:i'),
                'status' => $attendance->status,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function checkOut(Request $request)
    {
        $employee = auth()->user()->employee;

        try {
            $attendance = $this->attendanceService->checkOut($employee);
            return response()->json([
                'success' => true,
                'message' => 'Checked out successfully!',
                'check_out' => $attendance->check_out->format('H:i'),
                'work_hours' => $attendance->work_hours,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function leaveIndex()
    {
        $employee = auth()->user()->employee;
        $leaves = Leave::where('employee_id', $employee->id)->latest()->paginate(10);
        return view('employee.attendance.leaves', compact('leaves', 'employee'));
    }

    public function requestLeave(Request $request)
    {
        $request->validate([
            'type' => 'required|in:annual,sick,casual,maternity,paternity,unpaid,other',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string|min:10',
        ]);

        $employee = auth()->user()->employee;

        $days = \Carbon\Carbon::parse($request->from_date)
                              ->diffInWeekdays(\Carbon\Carbon::parse($request->to_date)) + 1;

        // Prevent duplicate: same employee, overlapping dates, still pending/approved
        $overlapping = Leave::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('from_date', [$request->from_date, $request->to_date])
                  ->orWhereBetween('to_date',   [$request->from_date, $request->to_date])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('from_date', '<=', $request->from_date)
                         ->where('to_date',   '>=', $request->to_date);
                  });
            })
            ->exists();

        if ($overlapping) {
            return response()->json(['success' => false, 'message' => 'You already have a pending or approved leave request covering those dates.'], 422);
        }

        // Check leave balance
        if ($request->type === 'annual' && $employee->annual_leave_balance < $days) {
            return response()->json(['success' => false, 'message' => 'Insufficient annual leave balance.'], 422);
        }

        Leave::create([
            'employee_id' => $employee->id,
            'type' => $request->type,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'days' => $days,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Notify all admin / super_admin users about the new leave request
        $employeeName = $employee->user->name ?? 'An employee';
        $adminUsers   = User::whereIn('role', ['super_admin', 'admin'])->get();
        foreach ($adminUsers as $admin) {
            $this->notificationService->send(
                $admin,
                'New Leave Request',
                "{$employeeName} has submitted a " . ucfirst($request->type) . " leave request for {$days} day(s) starting " . \Carbon\Carbon::parse($request->from_date)->format('d M Y') . '.',
                'info',
                [],
                route('admin.leave-requests.index'),
            );
        }

        return response()->json(['success' => true, 'message' => 'Leave request submitted.']);
    }
}
