<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\WorkReport;
use App\Services\NotificationService;
use App\Traits\ScopesByDepartment;
use Illuminate\Http\Request;

class WorkReportController extends Controller
{
    use ScopesByDepartment;

    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $query = WorkReport::with('employee.user');

        // Always scope managers to their own department
        $this->applyRelatedDeptScope($query);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->employee_id) {
            $empIds = $this->managedEmployeeIds();
            if ($empIds === null || in_array((int) $request->employee_id, $empIds)) {
                $query->where('employee_id', $request->employee_id);
            }
        }
        if ($request->month) {
            $query->whereMonth('report_date', $request->month);
        }
        if ($request->year) {
            $query->whereYear('report_date', $request->year);
        }
        // Admins may additionally filter by dept; managers are already scoped
        if ($request->department_id && $this->managedDeptId() === null) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }

        $reports     = $query->latest('report_date')->paginate(20)->withQueryString();
        $employees   = $this->deptEmployeeQuery()->get();
        $departments = Department::orderBy('name')->get();

        // Stats also scoped to manager's dept
        $statsBase = WorkReport::whereHas('employee', function ($q) {
            $deptId = $this->managedDeptId();
            if ($deptId !== null) {
                $q->where('department_id', $deptId);
            }
        });

        $stats = [
            'draft'     => (clone $statsBase)->where('status', 'draft')->count(),
            'submitted' => (clone $statsBase)->where('status', 'submitted')->count(),
            'approved'  => (clone $statsBase)->where('status', 'approved')->count(),
            'rejected'  => (clone $statsBase)->where('status', 'rejected')->count(),
        ];

        return view('admin.work-reports.index', compact('reports', 'employees', 'departments', 'stats'));
    }

    public function show(WorkReport $workReport)
    {
        $employee = Employee::findOrFail($workReport->employee_id);
        $this->authorizeEmployee($employee);

        $workReport->load('employee.user');
        return view('admin.work-reports.show', compact('workReport'));
    }

    public function approve(Request $request, WorkReport $workReport)
    {
        $employee = Employee::findOrFail($workReport->employee_id);
        $this->authorizeEmployee($employee);

        if ($workReport->status !== 'submitted') {
            return back()->with('error', 'Only submitted reports can be approved.');
        }

        $workReport->update([
            'status'          => 'approved',
            'reviewer_notes'  => $request->reviewer_notes,
            'reviewed_by'     => auth()->id(),
            'reviewed_at'     => now(),
        ]);

        // Notify the employee
        if ($workReport->employee?->user) {
            $this->notificationService->send(
                $workReport->employee->user,
                'Work Report Approved',
                "Your work report for {$workReport->report_date->format('d M Y')} has been approved.",
                'success', [], '/employee/work-reports'
            );
        }

        return back()->with('success', 'Work report approved.');
    }

    public function reject(Request $request, WorkReport $workReport)
    {
        $employee = Employee::findOrFail($workReport->employee_id);
        $this->authorizeEmployee($employee);

        $request->validate(['reviewer_notes' => 'required|string|max:500']);

        if ($workReport->status !== 'submitted') {
            return back()->with('error', 'Only submitted reports can be rejected.');
        }

        $workReport->update([
            'status'         => 'rejected',
            'reviewer_notes' => $request->reviewer_notes,
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
        ]);

        // Notify the employee
        if ($workReport->employee?->user) {
            $this->notificationService->send(
                $workReport->employee->user,
                'Work Report Rejected',
                "Your report for {$workReport->report_date->format('d M Y')} was rejected. Notes: {$request->reviewer_notes}",
                'danger', [], '/employee/work-reports'
            );
        }

        return back()->with('success', 'Work report rejected.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);

        $query = WorkReport::whereIn('id', $request->ids)->where('status', 'submitted');

        // Restrict bulk approve to manager's dept employees only
        $this->applyRelatedDeptScope($query);

        $query->update(['status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);

        return back()->with('success', count($request->ids) . ' report(s) approved.');
    }
}
