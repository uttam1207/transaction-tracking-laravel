<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function index()
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return redirect()->route('admin.dashboard')->with('warning', 'Employee profile not found.');
        }

        $data = $this->dashboardService->getEmployeeDashboard($employee->id);
        $recentTasks = $employee->tasks()->with('project')->latest()->limit(5)->get();
        $recentAttendance = $employee->attendance()->latest('date')->limit(7)->get();

        return view('employee.dashboard', compact('data', 'recentTasks', 'recentAttendance', 'employee'));
    }
}
