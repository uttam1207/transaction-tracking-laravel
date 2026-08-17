<?php

namespace App\Traits;

use App\Models\Employee;

/**
 * ScopesByDepartment
 *
 * Apply to any admin controller to automatically restrict data
 * to the authenticated manager's own department.
 *
 * Admins and super_admins are never restricted — all helpers return
 * null / no-op for those roles so existing behaviour is preserved.
 */
trait ScopesByDepartment
{
    /**
     * The authenticated manager's department_id, or null if the user
     * is an admin / super_admin (unrestricted).
     */
    protected function managedDeptId(): ?int
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return null;
        }

        return $user->employee?->department_id ?? null;
    }

    /**
     * Abort 403 if the given employee does not belong to the manager's dept.
     * No-op for admins.
     */
    protected function authorizeEmployee(Employee $employee): void
    {
        $deptId = $this->managedDeptId();

        if ($deptId !== null && (int) $employee->department_id !== $deptId) {
            abort(403, 'You can only access employees within your own department.');
        }
    }

    /**
     * Apply a WHERE department_id filter to an Employee query for managers.
     * Admins pass through unchanged.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query  Employee query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDeptScope($query)
    {
        $deptId = $this->managedDeptId();

        if ($deptId !== null) {
            $query->where('department_id', $deptId);
        }

        return $query;
    }

    /**
     * Apply department scoping to a query whose records belong to
     * employees via a relationship (e.g. Attendance, Leave, WorkReport,
     * PerformanceReview).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $relation  Eloquent relationship name on the model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyRelatedDeptScope($query, string $relation = 'employee')
    {
        $deptId = $this->managedDeptId();

        if ($deptId !== null) {
            $query->whereHas($relation, fn($q) => $q->where('department_id', $deptId));
        }

        return $query;
    }

    /**
     * Returns an array of employee IDs in the manager's department,
     * or null if the user is unrestricted (admin/super_admin).
     * Useful for filtering tasks where the FK is `assigned_to` (not a relation).
     *
     * @return int[]|null
     */
    protected function managedEmployeeIds(): ?array
    {
        $deptId = $this->managedDeptId();

        if ($deptId === null) {
            return null;
        }

        return Employee::where('department_id', $deptId)->pluck('id')->all();
    }

    /**
     * Get a base Employee query already scoped to the manager's dept.
     * Convenience wrapper used when building employee dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function deptEmployeeQuery()
    {
        return $this->applyDeptScope(Employee::with('user')->active());
    }
}
