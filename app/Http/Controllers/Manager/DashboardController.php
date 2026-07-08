<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\WorkReport;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('admin.dashboard')
                ->with('warning', 'Employee profile not found for your account.');
        }

        // ── Personal stats ────────────────────────────────────────────────
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())->first();

        $isCheckedIn  = $todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out;
        $isDayComplete = $todayAttendance && $todayAttendance->check_out;

        $myPendingTasks = $employee->tasks()
            ->whereIn('status', ['pending', 'assigned', 'in_progress'])->count();

        $myCompletedMonth = $employee->tasks()
            ->where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // ── Team stats ────────────────────────────────────────────────────
        $teamEmployees = Employee::where('manager_id', $employee->id)
            ->with('user', 'department', 'todayAttendance')
            ->get();

        $teamIds = $teamEmployees->pluck('id');

        $teamPresentToday = Attendance::whereIn('employee_id', $teamIds)
            ->whereDate('date', today())
            ->whereNotNull('check_in')
            ->count();

        $teamOnLeaveToday = Leave::whereIn('employee_id', $teamIds)
            ->where('status', 'approved')
            ->whereDate('from_date', '<=', today())
            ->whereDate('to_date', '>=', today())
            ->count();

        $pendingLeaves = Leave::whereIn('employee_id', $teamIds)
            ->where('status', 'pending')
            ->with('employee.user')
            ->latest()
            ->get();

        $pendingWorkReports = WorkReport::whereIn('employee_id', $teamIds)
            ->where('status', 'submitted')
            ->with('employee.user')
            ->latest()
            ->limit(10)
            ->get();

        // Team task aggregate
        $teamTaskStats = ['total' => 0, 'completed' => 0, 'in_progress' => 0, 'pending' => 0];
        foreach ($teamEmployees as $member) {
            $t = $member->tasks();
            $teamTaskStats['total']       += (clone $t)->count();
            $teamTaskStats['completed']   += (clone $t)->where('status', 'completed')->count();
            $teamTaskStats['in_progress'] += (clone $t)->where('status', 'in_progress')->count();
            $teamTaskStats['pending']     += (clone $t)->whereIn('status', ['pending', 'assigned'])->count();
        }

        // Today's team attendance keyed by employee_id
        $teamAttendanceToday = Attendance::whereIn('employee_id', $teamIds)
            ->whereDate('date', today())
            ->get()
            ->keyBy('employee_id');

        return view('manager.dashboard', compact(
            'employee', 'todayAttendance', 'isCheckedIn', 'isDayComplete',
            'myPendingTasks', 'myCompletedMonth',
            'teamEmployees', 'teamIds', 'teamPresentToday', 'teamOnLeaveToday',
            'pendingLeaves', 'pendingWorkReports', 'teamTaskStats',
            'teamAttendanceToday'
        ));
    }
}
