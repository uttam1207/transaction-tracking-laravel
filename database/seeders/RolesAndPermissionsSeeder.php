<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            'view_users', 'create_users', 'edit_users', 'delete_users',
            // Employees
            'view_employees', 'create_employees', 'edit_employees', 'delete_employees',
            // Transactions
            'view_transactions', 'create_transactions', 'edit_transactions', 'delete_transactions',
            // Reports
            'view_reports', 'export_reports',
            // Fraud
            'view_fraud_alerts', 'manage_fraud_alerts', 'manage_fraud_rules',
            // Settings
            'manage_settings',
            // Attendance
            'view_attendance', 'manage_attendance', 'approve_leaves',
            // Tasks
            'view_tasks', 'create_tasks', 'edit_tasks', 'delete_tasks', 'approve_tasks',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'super_admin' => Permission::all()->pluck('name')->toArray(),
            'admin' => [
                'view_users', 'create_users', 'edit_users',
                'view_employees', 'create_employees', 'edit_employees',
                'view_transactions', 'create_transactions', 'edit_transactions',
                'view_reports', 'export_reports',
                'view_fraud_alerts', 'manage_fraud_alerts',
                'view_attendance', 'manage_attendance', 'approve_leaves',
                'view_tasks', 'create_tasks', 'edit_tasks', 'approve_tasks',
                'manage_settings',
            ],
            'manager' => [
                'view_employees', 'view_transactions', 'view_reports',
                'view_fraud_alerts', 'view_attendance', 'approve_leaves',
                'view_tasks', 'create_tasks', 'edit_tasks', 'approve_tasks',
            ],
            'employee' => [
                'view_tasks', 'view_attendance',
            ],
            'auditor' => [
                'view_transactions', 'view_reports', 'export_reports', 'view_fraud_alerts',
                'view_attendance', 'view_users', 'view_employees',
            ],
            'viewer' => [
                'view_transactions', 'view_reports', 'view_attendance',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('Roles and permissions seeded!');
    }
}
