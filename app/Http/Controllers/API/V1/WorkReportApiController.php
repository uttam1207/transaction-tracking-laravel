<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\WorkReport;
use Illuminate\Http\Request;

class WorkReportApiController extends Controller
{
    /**
     * @OA\Get(path="/api/v1/work-reports", tags={"Work Reports"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="List of work reports"))
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = WorkReport::with('employee.user');

        if ($user->isEmployee()) {
            $employee = $user->employee;
            if (!$employee) return response()->json(['data' => []]);
            $query->where('employee_id', $employee->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->month) {
            $query->whereMonth('report_date', $request->month);
        }
        if ($request->year) {
            $query->whereYear('report_date', $request->year);
        }

        $reports = $query->latest('report_date')->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $reports->map(fn($r) => $this->formatReport($r)),
            'meta' => ['total' => $reports->total(), 'current_page' => $reports->currentPage(), 'last_page' => $reports->lastPage()],
        ]);
    }

    /**
     * @OA\Post(path="/api/v1/work-reports", tags={"Work Reports"}, security={{"sanctum":{}}},
     *   @OA\Response(response=201, description="Work report created"))
     */
    public function store(Request $request)
    {
        $request->validate([
            'report_date'       => 'required|date|before_or_equal:today',
            'summary'           => 'required|string|min:10',
            'hours_worked'      => 'required|numeric|min:0.5|max:24',
            'tasks_completed'   => 'nullable|array',
            'tasks_completed.*' => 'string|max:255',
            'productivity_score'=> 'nullable|integer|min:0|max:100',
        ]);

        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json(['error' => 'Employee profile not found.'], 422);
        }

        $exists = WorkReport::where('employee_id', $employee->id)
            ->where('report_date', $request->report_date)->exists();

        if ($exists) {
            return response()->json(['error' => 'A report for this date already exists.'], 422);
        }

        $report = WorkReport::create([
            'employee_id'       => $employee->id,
            'report_date'       => $request->report_date,
            'summary'           => $request->summary,
            'hours_worked'      => $request->hours_worked,
            'tasks_completed'   => $request->tasks_completed ?? [],
            'productivity_score'=> $request->productivity_score ?? 80,
            'status'            => $request->submit ? 'submitted' : 'draft',
        ]);

        return response()->json(['success' => true, 'data' => $this->formatReport($report)], 201);
    }

    /**
     * @OA\Get(path="/api/v1/work-reports/{id}", tags={"Work Reports"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Work report details"))
     */
    public function show(WorkReport $report)
    {
        $this->authorizeReport($report);
        return response()->json(['data' => $this->formatReport($report)]);
    }

    /**
     * @OA\Post(path="/api/v1/work-reports/{id}/submit", tags={"Work Reports"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Report submitted"))
     */
    public function submit(WorkReport $report)
    {
        $this->authorizeReport($report);

        if ($report->status !== 'draft') {
            return response()->json(['error' => 'Only draft reports can be submitted.'], 422);
        }

        $report->update(['status' => 'submitted', 'submitted_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Report submitted for review.']);
    }

    private function formatReport(WorkReport $r): array
    {
        return [
            'id'                => $r->id,
            'report_date'       => $r->report_date,
            'summary'           => $r->summary,
            'hours_worked'      => (float) $r->hours_worked,
            'tasks_completed'   => $r->tasks_completed ?? [],
            'productivity_score'=> $r->productivity_score,
            'status'            => $r->status,
            'reviewer_notes'    => $r->reviewer_notes,
            'submitted_at'      => $r->submitted_at?->toIso8601String(),
            'created_at'        => $r->created_at->toIso8601String(),
        ];
    }

    private function authorizeReport(WorkReport $report): void
    {
        $user = auth()->user();
        if ($user->isEmployee()) {
            $employee = $user->employee;
            abort_unless($employee && $report->employee_id === $employee->id, 403, 'Access denied.');
        }
    }
}
