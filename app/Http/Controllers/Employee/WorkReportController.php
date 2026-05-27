<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\WorkReport;
use Illuminate\Http\Request;

class WorkReportController extends Controller
{
    public function index()
    {
        $employee = auth()->user()->employee;
        $reports = WorkReport::where('employee_id', $employee->id)->latest('report_date')->paginate(10);
        return view('employee.work-reports.index', compact('reports'));
    }

    public function create()
    {
        $employee = auth()->user()->employee;
        $today = today()->format('Y-m-d');
        $existing = WorkReport::where('employee_id', $employee->id)->whereDate('report_date', today())->first();
        $tasks = $employee->tasks()->whereIn('status', ['in_progress', 'completed'])->get();

        return view('employee.work-reports.create', compact('existing', 'tasks', 'today'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'summary' => 'required|string|min:20',
            'hours_worked' => 'required|numeric|min:0|max:24',
            'productivity_score' => 'nullable|integer|min:0|max:100',
        ]);

        $employee = auth()->user()->employee;

        $report = WorkReport::updateOrCreate(
            ['employee_id' => $employee->id, 'report_date' => $request->report_date],
            [
                'summary' => $request->summary,
                'tasks_completed' => $request->tasks_completed ?? [],
                'hours_worked' => $request->hours_worked,
                'productivity_score' => $request->productivity_score ?? 0,
                'status' => 'draft',
            ]
        );

        return response()->json(['success' => true, 'message' => 'Report saved as draft.', 'report' => $report]);
    }

    public function submit(WorkReport $report)
    {
        $this->authorizeReport($report);

        if ($report->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Report already submitted.'], 422);
        }

        $report->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Report submitted for review.']);
    }

    public function show(WorkReport $report)
    {
        $this->authorizeReport($report);
        return view('employee.work-reports.show', compact('report'));
    }

    private function authorizeReport(WorkReport $report): void
    {
        $employee = auth()->user()->employee;
        if (!$employee || $report->employee_id !== $employee->id) {
            abort(403);
        }
    }
}
