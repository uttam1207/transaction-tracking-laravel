<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')
            ->with('manager')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name',
            'code'        => 'nullable|string|max:20|unique:departments,code',
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|exists:users,id',
        ]);

        Department::create($request->only('name', 'code', 'description', 'manager_id') + ['is_active' => true]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Department created.']);
        }
        return back()->with('success', 'Department created successfully.');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code'        => 'nullable|string|max:20|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|exists:users,id',
            'is_active'   => 'boolean',
        ]);

        $department->update($request->only('name', 'code', 'description', 'manager_id', 'is_active'));

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Department updated.']);
        }
        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete department with active employees.'], 422);
        }

        $department->delete();
        return response()->json(['success' => true, 'message' => 'Department deleted.']);
    }
}
