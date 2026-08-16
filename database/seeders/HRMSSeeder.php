<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;
use App\Models\SalaryComponent;

class HRMSSeeder extends Seeder
{
    public function run(): void
    {
        // ── Standard Leave Types ───────────────────────────────────────────────
        $leaveTypes = [
            [
                'name'                => 'Annual Leave',
                'code'                => 'ANNUAL',
                'color'               => '#4f46e5',
                'max_days_per_year'   => 21,
                'carry_forward_days'  => 5,
                'is_paid'             => true,
                'requires_approval'   => true,
                'applicable_after_days' => 90,
                'is_active'           => true,
                'description'         => 'Standard annual paid leave entitlement',
            ],
            [
                'name'                => 'Sick Leave',
                'code'                => 'SICK',
                'color'               => '#dc2626',
                'max_days_per_year'   => 10,
                'carry_forward_days'  => 0,
                'is_paid'             => true,
                'requires_approval'   => false,
                'applicable_after_days' => 0,
                'is_active'           => true,
                'description'         => 'Medical and illness-related leave',
            ],
            [
                'name'                => 'Casual Leave',
                'code'                => 'CASUAL',
                'color'               => '#16a34a',
                'max_days_per_year'   => 7,
                'carry_forward_days'  => 0,
                'is_paid'             => true,
                'requires_approval'   => true,
                'applicable_after_days' => 0,
                'is_active'           => true,
                'description'         => 'Short-notice personal leave',
            ],
            [
                'name'                => 'Maternity Leave',
                'code'                => 'MATERNITY',
                'color'               => '#db2777',
                'max_days_per_year'   => 90,
                'carry_forward_days'  => 0,
                'is_paid'             => true,
                'requires_approval'   => true,
                'applicable_after_days' => 180,
                'is_active'           => true,
                'description'         => 'Maternity leave as per statutory requirements',
            ],
            [
                'name'                => 'Paternity Leave',
                'code'                => 'PATERNITY',
                'color'               => '#0891b2',
                'max_days_per_year'   => 5,
                'carry_forward_days'  => 0,
                'is_paid'             => true,
                'requires_approval'   => true,
                'applicable_after_days' => 180,
                'is_active'           => true,
                'description'         => 'Paternity leave for new fathers',
            ],
            [
                'name'                => 'Unpaid Leave',
                'code'                => 'UNPAID',
                'color'               => '#9ca3af',
                'max_days_per_year'   => 30,
                'carry_forward_days'  => 0,
                'is_paid'             => false,
                'requires_approval'   => true,
                'applicable_after_days' => 0,
                'is_active'           => true,
                'description'         => 'Leave without pay',
            ],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::firstOrCreate(['code' => $lt['code']], $lt);
        }

        // ── Standard Salary Components ─────────────────────────────────────────
        $components = [
            [
                'name'             => 'Basic Salary',
                'code'             => 'BASIC',
                'type'             => 'earning',
                'calculation_type' => 'fixed',
                'default_value'    => 0,
                'is_mandatory'     => true,
                'is_taxable'       => true,
                'sort_order'       => 1,
                'is_active'        => true,
                'description'      => 'Base salary component',
            ],
            [
                'name'             => 'House Rent Allowance',
                'code'             => 'HRA',
                'type'             => 'earning',
                'calculation_type' => 'percentage_of_basic',
                'default_value'    => 20.00,
                'is_mandatory'     => false,
                'is_taxable'       => false,
                'sort_order'       => 2,
                'is_active'        => true,
                'description'      => 'HRA at 20% of basic salary',
            ],
            [
                'name'             => 'Travel Allowance',
                'code'             => 'TA',
                'type'             => 'earning',
                'calculation_type' => 'fixed',
                'default_value'    => 0,
                'is_mandatory'     => false,
                'is_taxable'       => false,
                'sort_order'       => 3,
                'is_active'        => true,
                'description'      => 'Fixed monthly travel allowance',
            ],
            [
                'name'             => 'Provident Fund',
                'code'             => 'PF',
                'type'             => 'deduction',
                'calculation_type' => 'percentage_of_basic',
                'default_value'    => 12.00,
                'is_mandatory'     => true,
                'is_taxable'       => false,
                'sort_order'       => 1,
                'is_active'        => true,
                'description'      => 'Employee PF contribution at 12% of basic',
            ],
            [
                'name'             => 'Professional Tax',
                'code'             => 'PT',
                'type'             => 'deduction',
                'calculation_type' => 'fixed',
                'default_value'    => 200.00,
                'is_mandatory'     => false,
                'is_taxable'       => false,
                'sort_order'       => 2,
                'is_active'        => true,
                'description'      => 'Monthly professional tax deduction',
            ],
        ];

        foreach ($components as $comp) {
            SalaryComponent::firstOrCreate(['code' => $comp['code']], $comp);
        }

        $this->command->info('HRMSSeeder: ' . count($leaveTypes) . ' leave types and ' . count($components) . ' salary components seeded.');
    }
}
