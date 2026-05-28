<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Timesheet;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        $query = Timesheet::with(['employee.user', 'task', 'project']);

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->month) {
            $query->whereMonth('date', $request->month);
        }
        if ($request->year) {
            $query->whereYear('date', $request->year);
        }

        $timesheets = $query->latest('date')->paginate(20);

        $totalHours = (clone $query)->sum('hours');

        $employees   = Employee::with('user')->where('status', 'active')->get();
        $projects    = Project::where('status', 'active')->get();
        $departments = Department::orderBy('name')->get();

        return view('admin.timesheets.index', compact(
            'timesheets', 'totalHours', 'employees', 'projects', 'departments'
        ));
    }

    public function approve(Timesheet $timesheet)
    {
        $timesheet->update(['status' => 'approved']);
        return back()->with('success', 'Timesheet entry approved.');
    }

    public function reject(Request $request, Timesheet $timesheet)
    {
        $timesheet->update(['status' => 'rejected']);
        return back()->with('success', 'Timesheet entry rejected.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        Timesheet::whereIn('id', $request->ids)->where('status', 'pending')->update(['status' => 'approved']);
        return back()->with('success', count($request->ids) . ' entries approved.');
    }
}
