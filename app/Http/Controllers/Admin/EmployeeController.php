<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('user', 'department', 'manager.user');

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            })->orWhere('employee_id', 'like', '%' . $request->search . '%');
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->work_location) {
            $query->where('work_location', $request->work_location);
        }

        $employees = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::active()->get();

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::active()->get();
        $managers = Employee::with('user')->active()->get();
        return view('admin.employees.create', compact('departments', 'managers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|min:8',
            'department_id' => 'required|exists:departments,id',
            'designation' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'work_location' => 'required|in:office,remote,hybrid',
            'salary' => 'nullable|numeric|min:0',
            'manager_id' => 'nullable|exists:employees,id',
        ]);

        // Create user account
        $user = User::create([
            'name' => $request->name,
            'username' => Str::slug($request->name) . rand(100, 999),
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'employee',
            'department_id' => $request->department_id,
            'status' => 'active',
        ]);
        $user->assignRole('employee');

        // Generate employee ID
        $employeeId = 'EMP-' . str_pad(Employee::count() + 1, 5, '0', STR_PAD_LEFT);

        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_id' => $employeeId,
            'department_id' => $request->department_id,
            'manager_id' => $request->manager_id,
            'designation' => $request->designation,
            'team' => $request->team,
            'joining_date' => $request->joining_date,
            'employment_type' => $request->employment_type,
            'work_location' => $request->work_location,
            'salary' => $request->salary,
            'status' => 'active',
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully. Employee ID: ' . $employeeId);
    }

    public function show(Employee $employee)
    {
        $employee->load('user', 'department', 'manager.user', 'tasks', 'leaves', 'attendance');
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        $departments = Department::active()->get();
        $managers = Employee::with('user')->active()->where('id', '!=', $employee->id)->get();
        return view('admin.employees.edit', compact('employee', 'departments', 'managers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'department_id' => 'required|exists:departments,id',
            'designation' => 'required|string|max:255',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'work_location' => 'required|in:office,remote,hybrid',
            'status' => 'required|in:active,inactive,on_leave,terminated',
        ]);

        $employee->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'department_id' => $request->department_id,
        ]);

        $employee->update($request->only([
            'department_id', 'designation', 'team', 'employment_type',
            'work_location', 'salary', 'status', 'manager_id', 'address',
            'city', 'state', 'country', 'joining_date',
        ]));

        return redirect()->route('admin.employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(['success' => true, 'message' => 'Employee deleted.']);
    }

    public function performance(Employee $employee)
    {
        $stats = [
            'tasks_completed' => $employee->tasks()->where('status', 'completed')->count(),
            'tasks_pending' => $employee->tasks()->whereIn('status', ['pending', 'in_progress'])->count(),
            'avg_work_hours' => round($employee->attendance()->whereMonth('date', now()->month)->avg('work_hours'), 2),
            'attendance_percentage' => $this->getAttendancePercentage($employee),
            'work_reports_submitted' => $employee->workReports()->where('status', 'submitted')->count(),
        ];

        return response()->json($stats);
    }

    private function getAttendancePercentage(Employee $employee): float
    {
        $month = now()->month;
        $year = now()->year;
        $present = $employee->attendance()
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->whereIn('status', ['present', 'late'])->count();

        $totalDays = now()->day;
        return $totalDays > 0 ? round(($present / $totalDays) * 100, 1) : 0;
    }
}
