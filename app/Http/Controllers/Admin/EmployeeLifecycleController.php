<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeLifecycleEvent;
use App\Models\EmployeeTraining;
use App\Traits\ScopesByDepartment;
use Illuminate\Http\Request;

class EmployeeLifecycleController extends Controller
{
    use ScopesByDepartment;

    public function index(Request $request)
    {
        $query = EmployeeLifecycleEvent::with('employee.user', 'triggeredBy');
        $this->applyRelatedDeptScope($query);

        if ($request->event_type) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->employee_id) {
            $empIds = $this->managedEmployeeIds();
            if ($empIds === null || in_array((int) $request->employee_id, $empIds)) {
                $query->where('employee_id', $request->employee_id);
            }
        }

        $events    = $query->latest('event_date')->paginate(25)->withQueryString();
        $employees = $this->deptEmployeeQuery()->get();
        $eventTypes = EmployeeLifecycleEvent::$labels;

        return view('admin.employee-lifecycle.index', compact('events', 'employees', 'eventTypes'));
    }

    public function show(Employee $employee)
    {
        $this->authorizeEmployee($employee);
        $employee->load('user', 'department', 'branch', 'designation');

        $events = EmployeeLifecycleEvent::where('employee_id', $employee->id)
            ->with('triggeredBy')
            ->latest('event_date')
            ->get();

        $departmentHistory = EmployeeDepartment::where('employee_id', $employee->id)
            ->with('department')
            ->orderByDesc('started_at')
            ->get();

        $trainings = EmployeeTraining::where('employee_id', $employee->id)
            ->with('trainingProgram')
            ->latest()
            ->get();

        return view('admin.employee-lifecycle.show', compact('employee', 'events', 'departmentHistory', 'trainings'));
    }

    public function store(Request $request)
    {
        $employee = Employee::findOrFail($request->employee_id);
        $this->authorizeEmployee($employee);

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'event_type'  => 'required|in:' . implode(',', array_keys(EmployeeLifecycleEvent::$labels)),
            'event_date'  => 'required|date',
            'description' => 'nullable|string',
            'metadata'    => 'nullable|array',
        ]);

        $event = EmployeeLifecycleEvent::create([
            'employee_id'  => $request->employee_id,
            'event_type'   => $request->event_type,
            'event_date'   => $request->event_date,
            'description'  => $request->description,
            'triggered_by' => auth()->id(),
            'metadata'     => $request->metadata,
        ]);

        return response()->json(['success' => true, 'message' => 'Event recorded.', 'event' => $event]);
    }
}
