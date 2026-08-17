<?php

namespace Database\Seeders;

use App\Models\RoleDataScope;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * All MODULE.ACTION permissions for the ERP.
     */
    private array $permissions = [
        // Employees
        'employee.view', 'employee.create', 'employee.edit', 'employee.delete',
        'employee.export',
        // Departments
        'department.view', 'department.create', 'department.edit', 'department.delete',
        // Teams
        'team.view', 'team.create', 'team.edit', 'team.delete',
        // Tasks
        'task.view', 'task.create', 'task.assign', 'task.edit', 'task.delete', 'task.complete',
        // Projects
        'project.view', 'project.create', 'project.edit', 'project.delete',
        // Attendance
        'attendance.view', 'attendance.manage',
        // Leave
        'leave.view', 'leave.apply', 'leave.approve', 'leave.reject',
        // Payroll
        'payroll.view', 'payroll.manage',
        // Expense
        'expense.view', 'expense.create', 'expense.approve',
        // Reports
        'report.view', 'report.export',
        // Approvals
        'approval.view', 'approval.manage',
        // Recruitment
        'recruitment.view', 'recruitment.create', 'recruitment.edit', 'recruitment.delete',
        // Training
        'training.view', 'training.create', 'training.manage',
        // Assets
        'asset.view', 'asset.issue', 'asset.manage',
        // Transfers
        'transfer.view', 'transfer.create', 'transfer.approve',
        // Performance
        'performance.view', 'performance.create', 'performance.manage',
        // HR Admin
        'hr.manage',
    ];

    /**
     * Role → data scope mappings for key permissions.
     * scope_type: GLOBAL | COMPANY | BRANCH | DEPARTMENT | TEAM | PROJECT | OWN
     */
    private array $scopeDefaults = [
        'super_admin' => ['scope_type' => 'GLOBAL', 'permissions' => '*'],
        'admin'       => ['scope_type' => 'COMPANY', 'permissions' => '*'],
        'hr'          => ['scope_type' => 'COMPANY', 'permissions' => ['employee.view','employee.edit','attendance.view','leave.view','leave.approve','leave.reject','payroll.view','payroll.manage','performance.view','performance.manage','hr.manage','recruitment.view','recruitment.create','recruitment.edit','training.view','training.create','training.manage','transfer.view','transfer.approve','report.view','report.export','asset.view','asset.issue','asset.manage']],
        'manager'     => ['scope_type' => 'DEPARTMENT', 'permissions' => ['employee.view','task.view','task.create','task.assign','task.edit','attendance.view','attendance.manage','leave.view','leave.approve','leave.reject','report.view','performance.view','performance.create','transfer.view','transfer.create','asset.view']],
        'employee'    => ['scope_type' => 'OWN', 'permissions' => ['task.view','task.complete','leave.apply','expense.view','expense.create','attendance.view','performance.view','training.view','asset.view']],
    ];

    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        // ── Create all permissions ────────────────────────────────────────────
        foreach ($this->permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->command->info('Permissions created: ' . count($this->permissions));

        // ── Create roles (if not exist) and assign permissions ───────────────
        foreach ($this->scopeDefaults as $roleName => $config) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            $permsToAssign = $config['permissions'] === '*'
                ? $this->permissions
                : $config['permissions'];

            $role->syncPermissions($permsToAssign);

            $this->command->info("Role '{$roleName}' — " . count($permsToAssign) . " permissions assigned.");

            // ── Seed data scopes ──────────────────────────────────────────────
            $scopeType = $config['scope_type'];
            $scopeCol  = match($scopeType) {
                'DEPARTMENT' => 'department_id',
                'BRANCH'     => 'branch_id',
                'TEAM'       => 'team_id',
                default      => null,
            };

            foreach ($permsToAssign as $perm) {
                RoleDataScope::updateOrCreate(
                    ['role_id' => $role->id, 'permission' => $perm],
                    ['scope_type' => $scopeType, 'scope_column' => $scopeCol, 'is_active' => true]
                );
            }
        }

        // Clear data scope cache
        RoleDataScope::clearCache();

        $this->command->info('Data scopes seeded successfully.');
    }
}
