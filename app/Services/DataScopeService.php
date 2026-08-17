<?php

namespace App\Services;

use App\Models\RoleDataScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * DataScopeService
 *
 * Resolves the data visibility scope for a user+permission pair and
 * applies the appropriate Eloquent query constraints automatically.
 *
 * Usage in a controller:
 *
 *   $scope = app(DataScopeService::class);
 *   $query = Task::query();
 *   $scope->apply($query, auth()->user(), 'task.view');
 *   $tasks = $query->get();
 */
class DataScopeService
{
    /**
     * Apply scope constraints to an Eloquent query.
     *
     * @param  Builder  $query       The base query (e.g. Task::query())
     * @param  User     $user        Authenticated user
     * @param  string   $permission  MODULE.ACTION e.g. 'task.view'
     * @param  string   $relation    Name of the relationship that connects to
     *                               the scoping column (e.g. 'employee', 'assignedTo')
     *                               Used for DEPARTMENT/TEAM/BRANCH scopes.
     *                               Pass '' if the scope column is directly on the model.
     * @return Builder
     */
    public function apply(Builder $query, User $user, string $permission, string $relation = ''): Builder
    {
        // Super admins always get GLOBAL
        if ($user->isSuperAdmin()) {
            return $query;
        }

        $scope = RoleDataScope::resolveForUser($user, $permission);

        if (!$scope) {
            // No scope rule found → fall back to OWN (most restrictive safe default)
            return $this->applyOwnScope($query, $user, $relation);
        }

        return match ($scope['scope_type']) {
            'GLOBAL'     => $query,  // unrestricted
            'COMPANY'    => $query,  // single-company system — treat as global for now
            'BRANCH'     => $this->applyBranchScope($query, $user, $relation),
            'DEPARTMENT' => $this->applyDepartmentScope($query, $user, $relation),
            'TEAM'       => $this->applyTeamScope($query, $user, $relation),
            'PROJECT'    => $this->applyProjectScope($query, $user),
            'OWN'        => $this->applyOwnScope($query, $user, $relation),
            default      => $this->applyOwnScope($query, $user, $relation),
        };
    }

    /**
     * Resolve the scope type string for a user+permission without applying it.
     * Useful for UI decisions (e.g. hide filters the user can't use).
     */
    public function resolveScope(User $user, string $permission): string
    {
        if ($user->isSuperAdmin()) return 'GLOBAL';
        $scope = RoleDataScope::resolveForUser($user, $permission);
        return $scope['scope_type'] ?? 'OWN';
    }

    // ── Private scope applicators ─────────────────────────────────────────────

    private function applyBranchScope(Builder $query, User $user, string $relation): Builder
    {
        $branchId = $user->employee?->branch_id;
        if (!$branchId) return $query;

        if ($relation) {
            $query->whereHas($relation, fn($q) => $q->where('branch_id', $branchId));
        } else {
            $query->where('branch_id', $branchId);
        }
        return $query;
    }

    private function applyDepartmentScope(Builder $query, User $user, string $relation): Builder
    {
        $deptId = $user->employee?->department_id;
        if (!$deptId) return $query;

        if ($relation) {
            $query->whereHas($relation, fn($q) => $q->where('department_id', $deptId));
        } else {
            $query->where('department_id', $deptId);
        }
        return $query;
    }

    private function applyTeamScope(Builder $query, User $user, string $relation): Builder
    {
        $teamIds = $user->employee?->teams()->pluck('teams.id')->toArray() ?? [];
        if (empty($teamIds)) return $query;

        if ($relation) {
            $query->whereHas($relation, fn($q) =>
                $q->whereHas('teams', fn($t) => $t->whereIn('teams.id', $teamIds))
            );
        } else {
            $query->whereHas('teams', fn($q) => $q->whereIn('teams.id', $teamIds));
        }
        return $query;
    }

    private function applyProjectScope(Builder $query, User $user): Builder
    {
        // Records linked to projects where the user is a member
        $projectIds = \App\Models\ProjectMember::where('employee_id', $user->employee?->id)
            ->pluck('project_id')
            ->toArray();

        if (empty($projectIds)) return $query->whereRaw('0=1'); // no access
        return $query->whereIn('project_id', $projectIds);
    }

    private function applyOwnScope(Builder $query, User $user, string $relation): Builder
    {
        $employeeId = $user->employee?->id;
        $userId     = $user->id;

        if ($relation) {
            // Try to scope via relation FK
            $query->where(function ($q) use ($employeeId, $userId) {
                $q->where('assigned_to', $employeeId)
                  ->orWhere('employee_id', $employeeId)
                  ->orWhere('requested_by', $userId)
                  ->orWhere('user_id', $userId);
            });
        } else {
            $query->where(function ($q) use ($employeeId, $userId) {
                $q->where('assigned_to', $employeeId)
                  ->orWhere('employee_id', $employeeId)
                  ->orWhere('user_id', $userId);
            });
        }
        return $query;
    }
}
