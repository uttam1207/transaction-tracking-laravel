<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeTraining;
use App\Models\TrainingProgram;
use App\Traits\ScopesByDepartment;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    use ScopesByDepartment;

    public function index(Request $request)
    {
        $query = TrainingProgram::with('department', 'createdBy');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->department_id && $this->managedDeptId() === null) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Managers see only their dept programs or global ones
        $deptId = $this->managedDeptId();
        if ($deptId !== null) {
            $query->where(fn($q) => $q->where('department_id', $deptId)->orWhereNull('department_id'));
        }

        $programs    = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();

        return view('admin.training.index', compact('programs', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'          => 'required|string|max:150',
            'department_id'  => 'nullable|exists:departments,id',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'duration_hours' => 'nullable|numeric|min:0',
            'mode'           => 'required|in:online,offline,hybrid',
            'status'         => 'required|in:planned,ongoing,completed,cancelled',
            'is_mandatory'   => 'nullable|boolean',
        ]);

        $program = TrainingProgram::create(array_merge(
            $request->only(['title', 'description', 'trainer', 'department_id',
                            'start_date', 'end_date', 'duration_hours',
                            'is_mandatory', 'mode', 'venue', 'status']),
            ['created_by' => auth()->id()]
        ));

        return response()->json(['success' => true, 'message' => 'Training program created.', 'program' => $program]);
    }

    public function update(Request $request, TrainingProgram $trainingProgram)
    {
        $request->validate([
            'title'  => 'required|string|max:150',
            'status' => 'required|in:planned,ongoing,completed,cancelled',
            'mode'   => 'required|in:online,offline,hybrid',
        ]);

        $trainingProgram->update($request->only([
            'title', 'description', 'trainer', 'department_id',
            'start_date', 'end_date', 'duration_hours',
            'is_mandatory', 'mode', 'venue', 'status',
        ]));

        return response()->json(['success' => true, 'message' => 'Program updated.']);
    }

    public function destroy(TrainingProgram $trainingProgram)
    {
        $trainingProgram->delete();
        return response()->json(['success' => true, 'message' => 'Program deleted.']);
    }

    // ── Enrollments ───────────────────────────────────────────────────────────

    public function enrollments(Request $request, TrainingProgram $trainingProgram)
    {
        $enrollments = $trainingProgram->enrollments()->with('employee.user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->paginate(20);

        $employees = $this->deptEmployeeQuery()->get();

        return view('admin.training.enrollments', compact('trainingProgram', 'enrollments', 'employees'));
    }

    public function enroll(Request $request, TrainingProgram $trainingProgram)
    {
        $request->validate([
            'employee_ids'   => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $enrolled = 0;
        foreach ($request->employee_ids as $empId) {
            $employee = Employee::find($empId);
            if (!$employee) continue;
            $this->authorizeEmployee($employee);

            EmployeeTraining::firstOrCreate(
                ['employee_id' => $empId, 'training_program_id' => $trainingProgram->id],
                ['status' => 'enrolled']
            );
            $enrolled++;
        }

        return response()->json(['success' => true, 'message' => "{$enrolled} employee(s) enrolled."]);
    }

    public function updateEnrollment(Request $request, EmployeeTraining $employeeTraining)
    {
        $request->validate([
            'status' => 'required|in:enrolled,attending,completed,failed,withdrawn',
            'score'  => 'nullable|numeric|min:0|max:100',
        ]);

        $employeeTraining->update($request->only(['status', 'score', 'feedback', 'completed_at']));
        if ($request->status === 'completed' && !$employeeTraining->completed_at) {
            $employeeTraining->update(['completed_at' => now()]);
        }

        return response()->json(['success' => true, 'message' => 'Enrollment updated.']);
    }
}
