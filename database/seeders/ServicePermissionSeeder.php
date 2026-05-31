<?php

namespace Database\Seeders;

use App\Models\ServicePermission;
use Illuminate\Database\Seeder;

class ServicePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['service_key' => 'transactions',  'service_name' => 'Transactions',      'description' => 'View and manage financial transactions',     'icon' => 'arrow-left-right',    'allowed_roles' => ['admin', 'auditor'],            'sort_order' => 1],
            ['service_key' => 'fraud_alerts',  'service_name' => 'Fraud Alerts',      'description' => 'Monitor and manage fraud alerts',            'icon' => 'shield-exclamation',  'allowed_roles' => ['admin', 'auditor'],            'sort_order' => 2],
            ['service_key' => 'users',         'service_name' => 'User Management',   'description' => 'Create and manage user accounts',            'icon' => 'people',              'allowed_roles' => ['admin'],                       'sort_order' => 3],
            ['service_key' => 'employees',     'service_name' => 'Employees',         'description' => 'Manage employee records',                    'icon' => 'person-badge',        'allowed_roles' => ['admin', 'manager'],            'sort_order' => 4],
            ['service_key' => 'attendance',    'service_name' => 'Attendance',        'description' => 'Track and approve attendance',               'icon' => 'calendar-check',      'allowed_roles' => ['admin', 'manager'],            'sort_order' => 5],
            ['service_key' => 'tasks',         'service_name' => 'Tasks',             'description' => 'Assign and manage tasks',                    'icon' => 'list-task',           'allowed_roles' => ['admin', 'manager'],            'sort_order' => 6],
            ['service_key' => 'work_reports',  'service_name' => 'Work Reports',      'description' => 'Review employee work reports',               'icon' => 'file-text',           'allowed_roles' => ['admin', 'manager'],            'sort_order' => 7],
            ['service_key' => 'timesheets',    'service_name' => 'Timesheets',        'description' => 'Approve and manage timesheets',              'icon' => 'clock',               'allowed_roles' => ['admin', 'manager'],            'sort_order' => 8],
            ['service_key' => 'teams',         'service_name' => 'Teams',             'description' => 'Manage team assignments',                    'icon' => 'people-fill',         'allowed_roles' => ['admin', 'manager'],            'sort_order' => 9],
            ['service_key' => 'shifts',        'service_name' => 'Shifts',            'description' => 'Manage employee shifts',                     'icon' => 'calendar3',           'allowed_roles' => ['admin', 'manager'],            'sort_order' => 10],
            ['service_key' => 'reports',       'service_name' => 'Reports',           'description' => 'View transaction and HR reports',            'icon' => 'bar-chart',           'allowed_roles' => ['admin', 'auditor'],            'sort_order' => 11],
            ['service_key' => 'departments',   'service_name' => 'Departments',       'description' => 'Manage company departments',                 'icon' => 'building',            'allowed_roles' => ['admin'],                       'sort_order' => 12],
            ['service_key' => 'holidays',      'service_name' => 'Holidays',          'description' => 'Manage company holiday calendar',            'icon' => 'calendar-event',      'allowed_roles' => ['admin'],                       'sort_order' => 13],
            ['service_key' => 'projects',      'service_name' => 'Projects',          'description' => 'Manage company projects',                    'icon' => 'kanban',              'allowed_roles' => ['admin', 'manager'],            'sort_order' => 14],
            ['service_key' => 'queue',         'service_name' => 'Queue Monitor',     'description' => 'Monitor background job queues',              'icon' => 'cpu',                 'allowed_roles' => [],                              'sort_order' => 15],
            ['service_key' => 'settings',      'service_name' => 'Settings',          'description' => 'Configure application settings',             'icon' => 'gear',                'allowed_roles' => [],                              'sort_order' => 16],
            ['service_key' => 'wallets',       'service_name' => 'Wallets',           'description' => 'Manage user wallets and add funds',          'icon' => 'wallet2',             'allowed_roles' => [],                              'sort_order' => 17],
            ['service_key' => 'permissions',   'service_name' => 'Permissions',       'description' => 'Manage service access by role',              'icon' => 'shield-lock',         'allowed_roles' => [],                              'sort_order' => 18],
        ];

        foreach ($services as $service) {
            ServicePermission::updateOrCreate(
                ['service_key' => $service['service_key']],
                array_merge($service, ['is_active' => true])
            );
        }

        $this->command->info('Service permissions seeded!');
    }
}
