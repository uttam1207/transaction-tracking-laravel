<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PerformanceGoal;
use App\Models\PerformanceReview;
use App\Models\User;
use App\Traits\ScopesByDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceReviewController extends Controller
{
    use ScopesByDepartment;

    public function index(Request $request)
    {
        $query = PerformanceReview::with(['employee.user', 'reviewer']);

        // Always scope managers to their own department
        $this->applyRelatedDeptScope($query);

        if ($request->search) {
            $query->whereHas('employee.user', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->period) {
            $query->where('period', 'like', '%' . $request->period . '%');
        }
        if ($request->employee_id) {
            $empIds = $this->managedEmployeeIds();
            if ($empIds === null || in_array((int) $request->employee_id, $empIds)) {
                $query->where('employee_id', $request->employee_id);
            }
        }

        $reviews     = $query->latest()->paginate(15)->withQueryString();
        $employees   = $this->deptEmployeeQuery()->get();
        $reviewers   = User::active()->orderBy('name')->get();
        $departments = Department::active()->orderBy('name')->get();

        return view('admin.performance-reviews.index', compact('reviews', 'employees', 'reviewers', 'departments'));
    }

    public function store(Request $request)
    {
        // Managers can only create reviews for their own dept employees
        $empIds = $this->managedEmployeeIds();
        if ($empIds !== null && !in_array((int) $request->employee_id, $empIds)) {
            abort(403, 'You can only create reviews for employees in your department.');
        }

        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'reviewer_id'   => 'required|exists:users,id',
            'period'        => 'required|string|max:20',
            'period_start'  => 'required|date',
            'period_end'    => 'required|date|after:period_start',
            'status'        => 'required|in:draft,submitted,acknowledged,closed',
            'goals'         => 'nullable|array',
            'goals.*.title' => 'required|string|max:200',
            'goals.*.status'=> 'required|in:pending,in_progress,completed,missed',
        ]);

        // Prevent duplicate review for the same employee + period
        $exists = PerformanceReview::where('employee_id', $request->employee_id)
            ->where('period', $request->period)
            ->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'A performance review for this employee and period already exists.',
            ], 422);
        }

        DB::transaction(function () use ($request) {
            $review = PerformanceReview::create([
                'employee_id'  => $request->employee_id,
                'reviewer_id'  => $request->reviewer_id,
                'period'       => $request->period,
                'period_start' => $request->period_start,
                'period_end'   => $request->period_end,
                'status'       => $request->status ?? 'draft',
            ]);

            foreach ($request->goals ?? [] as $goal) {
                PerformanceGoal::create([
                    'performance_review_id' => $review->id,
                    'title'                 => $goal['title'],
                    'description'           => $goal['description'] ?? null,
                    'target_value'          => $goal['target_value'] ?? null,
                    'status'                => $goal['status'] ?? 'pending',
                ]);
            }
        });

        return back()->with('success', 'Performance review created.');
    }

    public function show(PerformanceReview $performanceReview)
    {
        $employee = Employee::findOrFail($performanceReview->employee_id);
        $this->authorizeEmployee($employee);

        $performanceReview->load(['employee.user', 'employee.department', 'reviewer', 'goals']);
        return view('admin.performance-reviews.show', compact('performanceReview'));
    }

    public function update(Request $request, PerformanceReview $performanceReview)
    {
        $employee = Employee::findOrFail($performanceReview->employee_id);
        $this->authorizeEmployee($employee);
        $request->validate([
            'overall_rating'          => 'nullable|numeric|min:1|max:5',
            'status'                  => 'required|in:draft,submitted,acknowledged,closed',
            'strengths'               => 'nullable|string',
            'areas_for_improvement'   => 'nullable|string',
            'goals_for_next_period'   => 'nullable|string',
            'employee_comments'       => 'nullable|string',
        ]);

        $performanceReview->update($request->only([
            'overall_rating', 'status', 'strengths',
            'areas_for_improvement', 'goals_for_next_period', 'employee_comments',
        ]));

        return response()->json(['success' => true, 'message' => 'Review updated.']);
    }

    public function submit(PerformanceReview $performanceReview)
    {
        $employee = Employee::findOrFail($performanceReview->employee_id);
        $this->authorizeEmployee($employee);

        if ($performanceReview->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Only draft reviews can be submitted.'], 422);
        }
        $performanceReview->update(['status' => 'submitted', 'submitted_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Review submitted.']);
    }

    public function acknowledge(PerformanceReview $performanceReview)
    {
        $employee = Employee::findOrFail($performanceReview->employee_id);
        $this->authorizeEmployee($employee);

        if ($performanceReview->status !== 'submitted') {
            return response()->json(['success' => false, 'message' => 'Only submitted reviews can be acknowledged.'], 422);
        }
        $performanceReview->update(['status' => 'acknowledged', 'acknowledged_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Review acknowledged.']);
    }

    public function destroy(PerformanceReview $performanceReview)
    {
        $employee = Employee::findOrFail($performanceReview->employee_id);
        $this->authorizeEmployee($employee);

        if ($performanceReview->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft reviews can be deleted.',
            ], 422);
        }
        $performanceReview->goals()->delete();
        $performanceReview->delete();
        return response()->json(['success' => true]);
    }
}
