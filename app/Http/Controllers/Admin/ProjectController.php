<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use App\Traits\ScopesByDepartment;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ScopesByDepartment;

    public function index(Request $request)
    {
        $query = Project::with('manager', 'department')->withCount('tasks');

        // Restrict managers / team leads to their department's projects
        $deptId = $this->managedDeptId();
        if ($deptId !== null) {
            $query->where('department_id', $deptId);
        } elseif ($request->department) {
            // Dept filter only available to unrestricted users (admin/HR)
            $query->where('department_id', $request->department);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }
        if ($request->status) {
            $query->where('status', $request->status);
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

        $data = $request->only(
            'name', 'code', 'manager_id', 'department_id',
            'start_date', 'end_date', 'budget', 'status', 'description'
        );

        // Managers / team leads can only create projects in their own department
        $deptId = $this->managedDeptId();
        if ($deptId !== null) {
            $data['department_id'] = $deptId;
        }

        $project = Project::create($data);

        return response()->json(['success' => true, 'message' => 'Project created.', 'project' => $project]);
    }

    public function show(Project $project)
    {
        // Authorization: managers/team leads can only view their dept's projects
        $deptId = $this->managedDeptId();
        if ($deptId !== null && (int) $project->department_id !== $deptId) {
            abort(403, 'You can only view projects within your department.');
        }

        $project->load(['manager', 'department', 'tasks.assignedTo.user']);
        $employees = $this->deptEmployeeQuery()->get();

        return view('admin.projects.show', compact('project', 'employees'));
    }

    public function update(Request $request, Project $project)
    {
        $deptId = $this->managedDeptId();
        if ($deptId !== null && (int) $project->department_id !== $deptId) {
            return response()->json(['success' => false, 'message' => 'You can only update projects in your department.'], 403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'status'   => 'required|in:planning,active,on_hold,completed,cancelled',
            'end_date' => 'nullable|date',
            'budget'   => 'nullable|numeric|min:0',
        ]);

        $data = $request->only(
            'name', 'code', 'manager_id', 'department_id',
            'start_date', 'end_date', 'budget', 'status', 'description'
        );

        // Prevent reassigning project to another department
        if ($deptId !== null) {
            $data['department_id'] = $deptId;
        }

        $project->update($data);

        return response()->json(['success' => true, 'message' => 'Project updated.']);
    }

    public function destroy(Project $project)
    {
        $deptId = $this->managedDeptId();
        if ($deptId !== null && (int) $project->department_id !== $deptId) {
            return response()->json(['success' => false, 'message' => 'You can only delete projects in your department.'], 403);
        }

        if ($project->tasks()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Remove all tasks before deleting the project.'], 422);
        }
        $project->delete();
        return response()->json(['success' => true, 'message' => 'Project deleted.']);
    }
}
