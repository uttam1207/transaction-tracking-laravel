<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with(['department', 'teamLead', 'members.user'])
                     ->withCount('members');

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        $teams       = $query->orderBy('department_id')->orderBy('name')->paginate(15)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();
        $employees   = Employee::with('user')->active()->orderBy('id')->get();
        $leads       = User::where('status', 'active')
                           ->whereIn('role', ['super_admin', 'admin', 'manager', 'hr_manager'])
                           ->orderBy('name')->get();

        $unassigned = Employee::active()->whereDoesntHave('teams')->count();

        return view('admin.teams.index', compact('teams', 'departments', 'employees', 'leads', 'unassigned'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:teams,code',
            'description'   => 'nullable|string',
            'team_lead_id'  => 'nullable|exists:users,id',
            'is_active'     => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        Team::create($data);

        return back()->with('success', 'Team "' . $data['name'] . '" created successfully.');
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:50|unique:teams,code,' . $team->id,
            'description'   => 'nullable|string',
            'team_lead_id'  => 'nullable|exists:users,id',
            'is_active'     => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $team->update($data);

        return back()->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        if ($team->members()->count() > 0) {
            return back()->with('error', 'Cannot delete a team that has members. Remove all members first.');
        }
        $team->delete();
        return back()->with('success', 'Team deleted.');
    }

    /** Add one or more employees to a team. */
    public function addMembers(Request $request, Team $team)
    {
        $request->validate([
            'employee_ids'   => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'role_in_team'   => 'nullable|string|max:100',
        ]);

        $pivot = [];
        foreach ($request->employee_ids as $empId) {
            $pivot[$empId] = [
                'role_in_team' => $request->role_in_team ?? 'Member',
                'joined_at'    => today()->toDateString(),
            ];
        }
        $team->members()->syncWithoutDetaching($pivot);

        return back()->with('success', count($request->employee_ids) . ' member(s) added to team.');
    }

    /** Remove a single employee from a team. */
    public function removeMember(Team $team, Employee $employee)
    {
        $team->members()->detach($employee->id);
        return back()->with('success', ($employee->user?->name ?? 'Employee') . ' removed from team.');
    }
}
