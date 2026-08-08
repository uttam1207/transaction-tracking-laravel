<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'         => 'super_admin',
                'display_name' => 'Super Admin',
                'description'  => 'Full unrestricted access to all modules and settings.',
                'color'        => '#7c3aed',
                'icon'         => 'shield-lock-fill',
                'is_system'    => true,
                'is_active'    => true,
                'sort_order'   => 1,
            ],
            [
                'name'         => 'admin',
                'display_name' => 'Admin',
                'description'  => 'Administrative access to most modules except system settings.',
                'color'        => '#2563eb',
                'icon'         => 'person-gear',
                'is_system'    => true,
                'is_active'    => true,
                'sort_order'   => 2,
            ],
            [
                'name'         => 'manager',
                'display_name' => 'Manager',
                'description'  => 'Manages teams, tasks, attendance and work reports.',
                'color'        => '#0891b2',
                'icon'         => 'briefcase-fill',
                'is_system'    => true,
                'is_active'    => true,
                'sort_order'   => 3,
            ],
            [
                'name'         => 'employee',
                'display_name' => 'Employee',
                'description'  => 'Standard employee access — workspace, tasks, attendance.',
                'color'        => '#16a34a',
                'icon'         => 'person-badge',
                'is_system'    => true,
                'is_active'    => true,
                'sort_order'   => 4,
            ],
            [
                'name'         => 'auditor',
                'display_name' => 'Auditor',
                'description'  => 'Read-only access to transactions, reports and fraud alerts.',
                'color'        => '#d97706',
                'icon'         => 'clipboard2-check-fill',
                'is_system'    => true,
                'is_active'    => true,
                'sort_order'   => 5,
            ],
            [
                'name'         => 'viewer',
                'display_name' => 'Viewer',
                'description'  => 'Limited read-only access to select modules.',
                'color'        => '#6b7280',
                'icon'         => 'eye-fill',
                'is_system'    => true,
                'is_active'    => true,
                'sort_order'   => 6,
            ],
            // ── ASDairy Farm-Specific Roles ──
            [
                'name'         => 'veterinarian',
                'display_name' => 'Veterinarian',
                'description'  => 'Access to animal health records, breeding management, and health treatments.',
                'color'        => '#059669',
                'icon'         => 'heart-pulse-fill',
                'is_system'    => false,
                'is_active'    => true,
                'sort_order'   => 7,
            ],
            [
                'name'         => 'farm_supervisor',
                'display_name' => 'Farm Supervisor',
                'description'  => 'Oversees daily farm operations: animals, milk, feed, stock, and maintenance.',
                'color'        => '#0d9488',
                'icon'         => 'house-check-fill',
                'is_system'    => false,
                'is_active'    => true,
                'sort_order'   => 8,
            ],
            [
                'name'         => 'worker',
                'display_name' => 'Worker',
                'description'  => 'Farm worker with basic access: milk entry logging and attendance.',
                'color'        => '#92400e',
                'icon'         => 'person-workspace',
                'is_system'    => false,
                'is_active'    => true,
                'sort_order'   => 9,
            ],
            [
                'name'         => 'investor',
                'display_name' => 'Investor',
                'description'  => 'Read-only financial access: revenue, expenses, payroll summary and reports.',
                'color'        => '#1d4ed8',
                'icon'         => 'graph-up-arrow',
                'is_system'    => false,
                'is_active'    => true,
                'sort_order'   => 10,
            ],
            [
                'name'         => 'franchise_owner',
                'display_name' => 'Franchise Owner',
                'description'  => 'Access to franchise management, procurement, sales, and CRM for assigned franchise.',
                'color'        => '#7c3aed',
                'icon'         => 'shop-window',
                'is_system'    => false,
                'is_active'    => true,
                'sort_order'   => 11,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                array_merge($role, ['guard_name' => 'web', 'updated_at' => now()])
            );
        }
    }
}
