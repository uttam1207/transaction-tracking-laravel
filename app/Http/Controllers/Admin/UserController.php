<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('department', 'employee')->withTrashed(false);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::active()->get();

        return view('admin.users.index', compact('users', 'departments'));
    }

    public function create()
    {
        $departments = Department::active()->get();
        return view('admin.users.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', 'string', \Illuminate\Validation\Rule::in(Role::pluck('name')->toArray())],
            'department_id' => 'nullable|exists:departments,id',
            'password' => 'required|min:8|confirmed',
            'status' => 'required|in:active,inactive,pending,suspended',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => Str::slug($request->name) . rand(100, 999),
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'department_id' => $request->department_id,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        // Assign spatie role
        $user->assignRole($request->role);

        // Create an Employee record for all non-super_admin roles
        if ($request->role !== 'super_admin') {
            $nextNum = (Employee::withTrashed()->max('id') ?? 0) + 1;
            $employeeId = 'EMP-' . str_pad($nextNum, 5, '0', \STR_PAD_LEFT);

            Employee::create([
                'user_id'         => $user->id,
                'employee_id'     => $employeeId,
                'department_id'   => $request->department_id,
                'designation'     => ucfirst(str_replace('_', ' ', $request->role)),
                'employment_type' => 'full_time',
                'work_location'   => 'office',
                'joining_date'    => now()->toDateString(),
                'status'          => 'active',
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'User created successfully.', 'user' => $user]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load('employee.department', 'loginHistories', 'auditLogs');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $departments = Department::active()->get();
        return view('admin.users.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => ['required', 'string', \Illuminate\Validation\Rule::in(Role::pluck('name')->toArray())],
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'required|in:active,inactive,pending,suspended',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'role', 'department_id', 'status']);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => 'image|mimes:jpg,jpeg,png,gif|max:2048']);
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        // If this user has no employee record and is not a super_admin, create one now
        if ($request->role !== 'super_admin' && !$user->employee) {
            $nextNum = (Employee::withTrashed()->max('id') ?? 0) + 1;
            $employeeId = 'EMP-' . str_pad($nextNum, 5, '0', \STR_PAD_LEFT);

            Employee::create([
                'user_id'         => $user->id,
                'employee_id'     => $employeeId,
                'department_id'   => $request->department_id,
                'designation'     => ucfirst(str_replace('_', ' ', $request->role)),
                'employment_type' => 'full_time',
                'work_location'   => 'office',
                'joining_date'    => now()->toDateString(),
                'status'          => 'active',
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'User updated successfully.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete yourself.'], 422);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }

    public function toggleStatus(User $user)
    {
        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active'
        ]);

        return response()->json([
            'success' => true,
            'status' => $user->status,
            'message' => 'User status updated.',
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array',
        ]);

        $query = User::whereIn('id', $request->ids)
                     ->where('id', '!=', auth()->id());

        match($request->action) {
            'activate' => $query->update(['status' => 'active']),
            'deactivate' => $query->update(['status' => 'inactive']),
            'delete' => $query->delete(),
        };

        return response()->json(['success' => true, 'message' => 'Bulk action completed.']);
    }
}
