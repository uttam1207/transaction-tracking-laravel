<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('manager', 'department')->withCount('tasks');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->department) {
            $query->where('department_id', $request->department);
        }

        $projects    = $query->latest()->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();
        $managers    = User::active()->orderBy('name')->get();

        return view('admin.projects.index', compact('projects', 'departments', 'managers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:20|unique:projects,code',
            'manager_id'    => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after:start_date',
            'budget'        => 'nullable|numeric|min:0',
            'status'        => 'required|in:planning,active,on_hold,completed,cancelled',
        ]);

        $project = Project::create($request->only(
            'name', 'code', 'manager_id', 'department_id',
            'start_date', 'end_date', 'budget', 'status', 'description'
        ));

        return response()->json(['success' => true, 'message' => 'Project created.', 'project' => $project]);
    }

    public function show(Project $project)
    {
        $project->load(['manager', 'department', 'tasks.assignedTo.user']);
        $employees = Employee::with('user')->active()->get();
        return view('admin.projects.show', compact('project', 'employees'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'status'        => 'required|in:planning,active,on_hold,completed,cancelled',
            'end_date'      => 'nullable|date',
            'budget'        => 'nullable|numeric|min:0',
        ]);

        $project->update($request->only(
            'name', 'code', 'manager_id', 'department_id',
            'start_date', 'end_date', 'budget', 'status', 'description'
        ));

        return response()->json(['success' => true, 'message' => 'Project updated.']);
    }

    public function destroy(Project $project)
    {
        if ($project->tasks()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Remove all tasks before deleting the project.'], 422);
        }
        $project->delete();
        return response()->json(['success' => true, 'message' => 'Project deleted.']);
    }
}
