<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\WorkReport;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class WorkReportController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $query = WorkReport::with('employee.user');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->month) {
            $query->whereMonth('report_date', $request->month);
        }
        if ($request->year) {
            $query->whereYear('report_date', $request->year);
        }
        if ($request->department_id) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }

        $reports     = $query->latest('report_date')->paginate(20);
        $employees   = Employee::with('user')->where('status', 'active')->get();
        $departments = Department::orderBy('name')->get();

        $stats = [
            'draft'     => WorkReport::where('status', 'draft')->count(),
            'submitted' => WorkReport::where('status', 'submitted')->count(),
            'approved'  => WorkReport::where('status', 'approved')->count(),
            'rejected'  => WorkReport::where('status', 'rejected')->count(),
        ];

        return view('admin.work-reports.index', compact('reports', 'employees', 'departments', 'stats'));
    }

    public function show(WorkReport $workReport)
    {
        $workReport->load('employee.user');
        return view('admin.work-reports.show', compact('workReport'));
    }

    public function approve(Request $request, WorkReport $workReport)
    {
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

        WorkReport::whereIn('id', $request->ids)
            ->where('status', 'submitted')
            ->update(['status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);

        return back()->with('success', count($request->ids) . ' report(s) approved.');
    }
}
