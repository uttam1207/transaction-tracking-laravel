<?php

namespace Database\Seeders;

use App\Models\ServicePermission;
use Illuminate\Database\Seeder;

class ServicePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $services = [

            // ── Core / Finance ───────────────────────────────────────────
            ['sort_order' =>  1,  'service_key' => 'transactions',       'service_name' => 'Transactions',          'icon' => 'arrow-left-right',   'description' => 'View and manage financial transactions',          'allowed_roles' => ['admin', 'auditor']],
            ['sort_order' =>  2,  'service_key' => 'fraud_alerts',       'service_name' => 'Fraud Alerts',          'icon' => 'shield-exclamation', 'description' => 'Monitor and manage fraud alerts',                 'allowed_roles' => ['admin', 'auditor']],
            ['sort_order' =>  3,  'service_key' => 'wallets',            'service_name' => 'Wallets',               'icon' => 'wallet2',            'description' => 'Manage user wallets and fund transfers',          'allowed_roles' => []],
            ['sort_order' =>  4,  'service_key' => 'expenses',           'service_name' => 'Expenses',              'icon' => 'receipt',            'description' => 'Track and approve expense records',               'allowed_roles' => ['admin', 'manager', 'auditor']],
            ['sort_order' =>  5,  'service_key' => 'expense_categories', 'service_name' => 'Expense Categories',   'icon' => 'tags',               'description' => 'Manage expense category definitions',             'allowed_roles' => ['admin']],
            ['sort_order' =>  6,  'service_key' => 'salaries',           'service_name' => 'Salary & Payroll',      'icon' => 'cash-coin',          'description' => 'Process and view employee salaries',              'allowed_roles' => ['admin']],

            // ── HR / People ───────────────────────────────────────────────
            ['sort_order' => 10,  'service_key' => 'users',              'service_name' => 'User Management',       'icon' => 'people',             'description' => 'Create and manage user accounts',                 'allowed_roles' => ['admin']],
            ['sort_order' => 11,  'service_key' => 'employees',          'service_name' => 'Employees',             'icon' => 'person-badge',       'description' => 'Manage employee profiles and records',            'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 12,  'service_key' => 'departments',        'service_name' => 'Departments',           'icon' => 'building',           'description' => 'Manage company department structure',             'allowed_roles' => ['admin']],
            ['sort_order' => 13,  'service_key' => 'teams',              'service_name' => 'Teams',                 'icon' => 'people-fill',        'description' => 'Manage team assignments and members',             'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 14,  'service_key' => 'attendance',         'service_name' => 'Attendance',            'icon' => 'calendar-check',     'description' => 'Track and approve employee attendance',           'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 15,  'service_key' => 'shifts',             'service_name' => 'Shifts',                'icon' => 'calendar3',          'description' => 'Configure and manage employee shifts',            'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 16,  'service_key' => 'holidays',           'service_name' => 'Holidays',              'icon' => 'calendar-event',     'description' => 'Manage company holiday calendar',                 'allowed_roles' => ['admin']],
            ['sort_order' => 17,  'service_key' => 'tasks',              'service_name' => 'Tasks',                 'icon' => 'list-task',          'description' => 'Assign and track employee tasks',                 'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 18,  'service_key' => 'projects',           'service_name' => 'Projects',              'icon' => 'kanban',             'description' => 'Manage and track company projects',               'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 19,  'service_key' => 'work_reports',       'service_name' => 'Work Reports',          'icon' => 'file-text',          'description' => 'Review and approve employee work reports',        'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 20,  'service_key' => 'timesheets',         'service_name' => 'Timesheets',            'icon' => 'clock',              'description' => 'Approve and manage employee timesheets',          'allowed_roles' => ['admin', 'manager']],

            // ── Dairy Farm Operations ─────────────────────────────────────
            ['sort_order' => 30,  'service_key' => 'animals',            'service_name' => 'Animals',               'icon' => 'egg-fried',          'description' => 'Manage livestock and animal records',             'allowed_roles' => ['admin', 'manager', 'employee', 'farmer']],
            ['sort_order' => 31,  'service_key' => 'breeds',             'service_name' => 'Breeds',                'icon' => 'diagram-3',          'description' => 'Manage animal breed definitions',                 'allowed_roles' => ['admin', 'manager', 'farmer']],
            ['sort_order' => 32,  'service_key' => 'milk',               'service_name' => 'Milk Production',       'icon' => 'droplet-half',       'description' => 'Record and track daily milk production',          'allowed_roles' => ['admin', 'manager', 'employee', 'farmer']],
            ['sort_order' => 33,  'service_key' => 'breeding',           'service_name' => 'Breeding',              'icon' => 'heart-pulse',        'description' => 'Manage animal breeding records',                  'allowed_roles' => ['admin', 'manager', 'employee', 'farmer']],
            ['sort_order' => 34,  'service_key' => 'health',             'service_name' => 'Animal Health',         'icon' => 'capsule',            'description' => 'Track health records and vet visits',             'allowed_roles' => ['admin', 'manager', 'employee', 'farmer']],
            ['sort_order' => 35,  'service_key' => 'feed',               'service_name' => 'Feed Calculator',       'icon' => 'basket',             'description' => 'Calculate and track animal feed',                 'allowed_roles' => ['admin', 'manager', 'employee', 'farmer']],
            ['sort_order' => 36,  'service_key' => 'farm',               'service_name' => 'Farm Management',       'icon' => 'house-door',         'description' => 'Manage farm facilities and infrastructure',        'allowed_roles' => ['admin', 'manager', 'farmer']],

            ['sort_order' => 39,  'service_key' => 'fixed_assets',       'service_name' => 'Fixed Assets',          'icon' => 'box2-heart',         'description' => 'Track company fixed assets and depreciation',     'allowed_roles' => ['admin', 'manager', 'auditor']],

            // ── Inventory / Operations ────────────────────────────────────
            ['sort_order' => 40,  'service_key' => 'stock',              'service_name' => 'Inventory & Stock',     'icon' => 'boxes',              'description' => 'Manage inventory items and stock levels',         'allowed_roles' => ['admin', 'manager', 'farmer']],
            ['sort_order' => 41,  'service_key' => 'maintenance',        'service_name' => 'Maintenance',           'icon' => 'wrench',             'description' => 'Log and manage equipment maintenance',            'allowed_roles' => ['admin', 'manager', 'employee', 'farmer']],
            ['sort_order' => 42,  'service_key' => 'compliance',         'service_name' => 'Compliance',            'icon' => 'patch-check',        'description' => 'Manage compliance records and documents',         'allowed_roles' => ['admin', 'auditor', 'farmer']],

            // ── CRM / Sales ───────────────────────────────────────────────
            ['sort_order' => 50,  'service_key' => 'contacts',           'service_name' => 'Contacts',              'icon' => 'person-lines-fill',  'description' => 'Manage contact and connection directory',         'allowed_roles' => ['admin', 'manager', 'employee']],
            ['sort_order' => 51,  'service_key' => 'contact_categories', 'service_name' => 'Contact Categories',   'icon' => 'tags',               'description' => 'Manage dynamic categories for contacts',          'allowed_roles' => ['admin']],
            ['sort_order' => 52,  'service_key' => 'crm',                'service_name' => 'CRM / Customers',       'icon' => 'people-fill',        'description' => 'Manage customer relationships',                   'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 53,  'service_key' => 'franchise',          'service_name' => 'Franchise',             'icon' => 'shop',               'description' => 'Manage franchise partners and agreements',        'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 54,  'service_key' => 'procurement',        'service_name' => 'Procurement',           'icon' => 'cart-check',         'description' => 'Manage purchase orders and suppliers',            'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 55,  'service_key' => 'vendors',            'service_name' => 'Vendors',               'icon' => 'truck',              'description' => 'Manage vendor and supplier records',              'allowed_roles' => ['admin', 'manager']],
            ['sort_order' => 56,  'service_key' => 'sales',              'service_name' => 'Sales',                 'icon' => 'graph-up-arrow',     'description' => 'Manage sales orders and revenue tracking',        'allowed_roles' => ['admin', 'manager']],

            // ── Reports ───────────────────────────────────────────────────
            ['sort_order' => 60,  'service_key' => 'reports',            'service_name' => 'Reports',               'icon' => 'bar-chart',          'description' => 'View financial, HR and operational reports',      'allowed_roles' => ['admin', 'auditor']],
            ['sort_order' => 61,  'service_key' => 'report_center',      'service_name' => 'Report Center',         'icon' => 'file-earmark-bar-graph', 'description' => 'Advanced reports and analytics dashboard',    'allowed_roles' => ['admin', 'auditor']],

            // ── Knowledge / Documents ─────────────────────────────────────
            ['sort_order' => 70,  'service_key' => 'documents',          'service_name' => 'Documents',             'icon' => 'folder2-open',       'description' => 'Upload and manage company documents',             'allowed_roles' => ['admin', 'manager', 'employee']],
            ['sort_order' => 71,  'service_key' => 'questions',          'service_name' => 'Q&A / Knowledge Base',  'icon' => 'question-circle',    'description' => 'Manage questions and knowledge base articles',    'allowed_roles' => ['admin', 'manager', 'employee']],

            // ── System / Super Admin ──────────────────────────────────────
            ['sort_order' => 90,  'service_key' => 'queue',              'service_name' => 'Queue Monitor',         'icon' => 'cpu',                'description' => 'Monitor background job queues',                   'allowed_roles' => []],
            ['sort_order' => 91,  'service_key' => 'settings',           'service_name' => 'Settings',              'icon' => 'gear',               'description' => 'Configure application-wide settings',             'allowed_roles' => []],
            ['sort_order' => 92,  'service_key' => 'permissions',        'service_name' => 'Service Permissions',   'icon' => 'shield-lock',        'description' => 'Manage role-based service access',                'allowed_roles' => []],
        ];

        foreach ($services as $service) {
            ServicePermission::updateOrCreate(
                ['service_key' => $service['service_key']],
                array_merge($service, ['is_active' => true])
            );
        }

        $this->command->info('✓ ' . count($services) . ' service permissions seeded!');
    }
}
