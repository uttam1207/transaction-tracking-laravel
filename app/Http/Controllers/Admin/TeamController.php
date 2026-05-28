<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        // Group employees by their 'team' field
        $query = Employee::with(['user', 'department', 'manager.user'])
            ->whereNotNull('team')
            ->where('status', 'active');

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->search) {
            $query->where('team', 'like', '%' . $request->search . '%');
        }

        // Get distinct teams with member counts
        $teams = $query->get()
            ->groupBy('team')
            ->map(fn($members, $name) => [
                'name'        => $name,
                'members'     => $members,
                'count'       => $members->count(),
                'department'  => $members->first()?->department?->name,
                'manager'     => $members->where('manager_id', null)->first()?->full_name
                                 ?? $members->first()?->manager?->full_name,
            ]);

        $departments = Department::orderBy('name')->get();

        // Employees with no team
        $unassigned = Employee::with(['user', 'department'])
            ->where('status', 'active')
            ->whereNull('team')
            ->orWhere('team', '')
            ->count();

        return view('admin.teams.index', compact('teams', 'departments', 'unassigned'));
    }

    public function assignTeam(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'team' => 'required|string|max:100',
        ]);

        Employee::whereIn('id', $request->employee_ids)
            ->update(['team' => $request->team]);

        return back()->with('success', count($request->employee_ids) . ' employee(s) assigned to team "' . $request->team . '".');
    }

    public function removeFromTeam(Employee $employee)
    {
        $employee->update(['team' => null]);
        return back()->with('success', $employee->full_name . ' removed from team.');
    }
}
