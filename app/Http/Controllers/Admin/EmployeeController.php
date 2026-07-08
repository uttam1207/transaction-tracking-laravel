<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EmployeesExport;
use App\Http\Controllers\Controller;
use App\Imports\EmployeesImport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('user', 'department', 'manager.user');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($uq) use ($request) {
                    $uq->where('name', 'like', '%' . $request->search . '%')
                       ->orWhere('email', 'like', '%' . $request->search . '%');
                })->orWhere('employee_id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->employment_type) {
            $query->where('employment_type', $request->employment_type);
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
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'nullable|string|max:255',
            'email'           => 'required|email|unique:users',
            'phone'           => 'nullable|string|max:20',
            'password'        => 'required|min:8',
            'department_id'   => 'required|exists:departments,id',
            'designation'     => 'required|string|max:255',
            'joining_date'    => 'nullable|date',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'work_location'   => 'required|in:office,remote,hybrid',
            'salary'          => 'nullable|numeric|min:0',
            'manager_id'      => 'nullable|exists:employees,id',
            'role'            => 'nullable|string|in:employee,manager,admin,auditor,viewer',
        ]);

        $name = trim($request->first_name . ' ' . ($request->last_name ?? ''));
        $role = $request->role ?? 'employee';

        // Create user account
        $user = User::create([
            'name'          => $name,
            'username'      => Str::slug($name) . rand(100, 999),
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'role'          => $role,
            'department_id' => $request->department_id,
            'status'        => 'active',
        ]);
        $user->assignRole($role);

        // Generate employee ID using max id to avoid duplicates from count()
        $nextNum = (Employee::withTrashed()->max('id') ?? 0) + 1;
        $employeeId = 'EMP-' . str_pad($nextNum, 5, '0', \STR_PAD_LEFT);

        Employee::create([
            'user_id'         => $user->id,
            'employee_id'     => $employeeId,
            'department_id'   => $request->department_id,
            'manager_id'      => $request->manager_id,
            'designation'     => $request->designation,
            'team'            => $request->team,
            'joining_date'    => $request->joining_date ?? now()->toDateString(),
            'employment_type' => $request->employment_type,
            'work_location'   => $request->work_location,
            'salary'          => $request->salary,
            'status'          => 'active',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully. ID: ' . $employeeId,
            ]);
        }

        return redirect()->route('admin.employees.index')->with('success', 'Employee created. ID: ' . $employeeId);
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
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'nullable|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $employee->user_id,
            'phone'           => 'nullable|string|max:20',
            'department_id'   => 'required|exists:departments,id',
            'designation'     => 'nullable|string|max:255',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'work_location'   => 'required|in:office,remote,hybrid',
            'status'          => 'required|in:active,inactive,on_leave,terminated',
            'salary'          => 'nullable|numeric|min:0',
            'performance_score'      => 'nullable|numeric|min:0|max:100',
            'annual_leave_balance'   => 'nullable|integer|min:0',
            'sick_leave_balance'     => 'nullable|integer|min:0',
            'password'        => 'nullable|min:8',
        ]);

        $name = trim($request->first_name . ' ' . ($request->last_name ?? ''));

        // Sync user status: inactive/terminated/on_leave employees can't log in
        $userStatus = $request->status === 'active' ? 'active' : 'inactive';

        $userUpdate = [
            'name'          => $name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'department_id' => $request->department_id,
            'status'        => $userStatus,
        ];
        if ($request->filled('password')) {
            $userUpdate['password'] = Hash::make($request->password);
        }
        $employee->user->update($userUpdate);

        $employee->update($request->only([
            'department_id', 'designation', 'team', 'employment_type',
            'work_location', 'salary', 'status', 'manager_id', 'address',
            'city', 'state', 'country', 'joining_date',
            'performance_score', 'annual_leave_balance', 'sick_leave_balance',
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
        $employee->load('user', 'department', 'manager.user');

        $now = now();

        // Task stats
        $tasks = $employee->tasks();
        $taskStats = [
            'total'     => (clone $tasks)->count(),
            'completed' => (clone $tasks)->where('status', 'completed')->count(),
            'in_progress' => (clone $tasks)->where('status', 'in_progress')->count(),
            'pending'   => (clone $tasks)->whereIn('status', ['pending', 'assigned'])->count(),
            'overdue'   => (clone $tasks)->whereNotIn('status', ['completed', 'cancelled'])
                                ->whereNotNull('due_date')->where('due_date', '<', $now)->count(),
        ];

        // Attendance stats this month
        $attendance = $employee->attendance()->whereMonth('date', $now->month)->whereYear('date', $now->year);
        $attendanceStats = [
            'present'    => (clone $attendance)->whereIn('status', ['present', 'late'])->count(),
            'absent'     => (clone $attendance)->where('status', 'absent')->count(),
            'late'       => (clone $attendance)->where('status', 'late')->count(),
            'percentage' => $this->getAttendancePercentage($employee),
            'avg_hours'  => round((clone $attendance)->avg('work_hours') ?? 0, 1),
        ];

        // Work reports
        $reportsStats = [
            'total'     => $employee->workReports()->count(),
            'approved'  => $employee->workReports()->where('status', 'approved')->count(),
            'submitted' => $employee->workReports()->where('status', 'submitted')->count(),
            'rejected'  => $employee->workReports()->where('status', 'rejected')->count(),
        ];

        // Monthly attendance chart (last 6 months)
        $chartLabels = [];
        $chartPresent = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $chartLabels[] = $month->format('M Y');
            $chartPresent[] = $employee->attendance()
                ->whereMonth('date', $month->month)
                ->whereYear('date', $month->year)
                ->whereIn('status', ['present', 'late'])
                ->count();
        }

        // Recent tasks
        $recentTasks = $employee->tasks()->with('project')
            ->latest('updated_at')->limit(5)->get();

        // Recent work reports
        $recentReports = $employee->workReports()->latest()->limit(5)->get();

        return view('admin.employees.performance', compact(
            'employee', 'taskStats', 'attendanceStats', 'reportsStats',
            'chartLabels', 'chartPresent', 'recentTasks', 'recentReports'
        ));
    }

    public function exportExcel()
    {
        return Excel::download(new EmployeesExport(), 'employees_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportCsv()
    {
        return Excel::download(new EmployeesExport(), 'employees_' . now()->format('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function importTemplate()
    {
        $headers = ['employee_id','full_name','email','password','department','designation',
                    'employment_type','work_location','team','joining_date'];
        $filename = 'employees_import_template.csv';
        $callback = function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fputcsv($handle, ['EMP-001','John Doe','john@example.com','Password@123','Engineering',
                              'Developer','full_time','office','Backend Team',date('Y-m-d')]);
            fclose($handle);
        };
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:2048']);

        $import = new EmployeesImport();
        Excel::import($import, $request->file('file'));

        $msg = "Import complete: {$import->imported} imported, {$import->skipped} skipped.";
        if (!empty($import->errors)) {
            $msg .= ' Errors: ' . implode('; ', array_slice($import->errors, 0, 3));
        }

        return back()->with('success', $msg);
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
