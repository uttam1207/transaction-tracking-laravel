<?php

namespace App\Traits;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

/**
 * ScopesByDepartment
 *
 * Apply to any admin controller to restrict data by department or team.
 *
 * Role hierarchy:
 *   super_admin / admin / hr  → unrestricted (all employees visible)
 *   manager                   → department-level (Department.manager_id = user.id)
 *   team_lead                 → team-level    (Team.team_lead_id = user.id)
 */
trait ScopesByDepartment
{
    // ── Resolved IDs ─────────────────────────────────────────────────────────

    /**
     * The department_id this user manages, or null if unrestricted.
     * Manager   → resolved via Department.manager_id (authoritative lookup)
     * Team lead → resolved via their team's parent department
     * Admin/HR  → null (unrestricted)
     */
    protected function managedDeptId(): ?int
    {
        $user = auth()->user();

        // Admins and HR see everything
        if ($user->isAdmin() || $user->isSuperAdmin() || $user->isHr()) {
            return null;
        }

        if ($user->isManager()) {
            // Authoritative: find the department officially assigned to this manager
            $dept = \App\Models\Department::where('manager_id', $user->id)->first();
            return $dept?->id ?? $user->employee?->department_id;
        }

        if ($user->isTeamLead()) {
            // Team lead is scoped to their team's parent department
            $team = \App\Models\Team::where('team_lead_id', $user->id)->first();
            return $team?->department_id ?? $user->employee?->department_id;
        }

        return $user->employee?->department_id ?? null;
    }

    /**
     * The team_id this user leads, or null for all other roles.
     * Only team_lead role returns a non-null value.
     */
    protected function managedTeamId(): ?int
    {
        $user = auth()->user();

        if (!$user->isTeamLead()) {
            return null;
        }

        return \App\Models\Team::where('team_lead_id', $user->id)->value('id');
    }

    /**
     * Employee IDs belonging to the team lead's team.
     * Returns null for non-team-leads (use dept-level scoping instead).
     *
     * @return int[]|null
     */
    protected function managedMemberIds(): ?array
    {
        $teamId = $this->managedTeamId();

        if ($teamId === null) {
            return null;
        }

        return DB::table('team_members')
            ->where('team_id', $teamId)
            ->pluck('employee_id')
            ->all();
    }

    // ── Authorization ─────────────────────────────────────────────────────────

    /**
     * Abort 403 if the given employee is outside the current user's scope.
     * No-op for admins / HR (they can access any employee).
     */
    protected function authorizeEmployee(Employee $employee): void
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isSuperAdmin() || $user->isHr()) {
            return;
        }

        // Team lead: employee must be in their team
        $teamId = $this->managedTeamId();
        if ($teamId !== null) {
            $inTeam = DB::table('team_members')
                ->where('team_id', $teamId)
                ->where('employee_id', $employee->id)
                ->exists();

            if (!$inTeam) {
                abort(403, 'You can only access employees within your team.');
            }
            return;
        }

        // Manager: employee must be in their department
        $deptId = $this->managedDeptId();
        if ($deptId !== null && (int) $employee->department_id !== $deptId) {
            abort(403, 'You can only access employees within your department.');
        }
    }

    /**
     * Non-aborting check — returns true if the current user may manage the employee.
     * Used in bulk operations to skip (not hard-reject) unauthorized employees.
     */
    protected function canManageEmployee(int $empId): bool
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isSuperAdmin() || $user->isHr()) {
            return true;
        }

        $teamId = $this->managedTeamId();
        if ($teamId !== null) {
            return DB::table('team_members')
                ->where('team_id', $teamId)
                ->where('employee_id', $empId)
                ->exists();
        }

        $deptId = $this->managedDeptId();
        if ($deptId === null) {
            return true;
        }

        return (int) Employee::find($empId)?->department_id === $deptId;
    }

    // ── Query Scoping ─────────────────────────────────────────────────────────

    /**
     * Apply scope to an Employee query builder.
     *
     * Team lead  → whereIn('id', team member ids)
     * Manager    → where('department_id', dept_id)
     * Admin/HR   → no-op (all employees)
     */
    protected function applyDeptScope($query)
    {
        $memberIds = $this->managedMemberIds();
        if ($memberIds !== null) {
            return $query->whereIn('id', $memberIds);
        }

        $deptId = $this->managedDeptId();
        if ($deptId !== null) {
            $query->where('department_id', $deptId);
        }

        return $query;
    }

    /**
     * Apply scope to a query whose records relate to employees via a relationship
     * (e.g. Attendance, Leave, WorkReport, Timesheet, PerformanceReview).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $relation  Eloquent relationship name pointing to Employee
     */
    protected function applyRelatedDeptScope($query, string $relation = 'employee')
    {
        $memberIds = $this->managedMemberIds();
        if ($memberIds !== null) {
            return $query->whereHas($relation, fn($q) => $q->whereIn('id', $memberIds));
        }

        $deptId = $this->managedDeptId();
        if ($deptId !== null) {
            $query->whereHas($relation, fn($q) => $q->where('department_id', $deptId));
        }

        return $query;
    }

    /**
     * Returns the complete list of managed employee IDs:
     *   Team lead  → team member IDs
     *   Manager    → all employee IDs in their department
     *   Admin/HR   → null (unrestricted — caller should skip filtering)
     *
     * @return int[]|null
     */
    protected function managedEmployeeIds(): ?array
    {
        $memberIds = $this->managedMemberIds();
        if ($memberIds !== null) {
            return $memberIds;
        }

        $deptId = $this->managedDeptId();
        if ($deptId === null) {
            return null;
        }

        return Employee::where('department_id', $deptId)->pluck('id')->all();
    }

    /**
     * Convenience: an Employee query already scoped to this user's dept / team.
     * Useful for building employee dropdowns in forms.
     */
    protected function deptEmployeeQuery()
    {
        return $this->applyDeptScope(Employee::with('user')->active());
    }
}
