<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Designation;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // ── Company ───────────────────────────────────────────────────────────
        $company = Company::firstOrCreate(
            ['code' => 'ASDAIRY'],
            [
                'name'                 => 'ASDairy',
                'tagline'              => 'Dairy Management Excellence',
                'currency'             => 'INR',
                'timezone'             => 'Asia/Kolkata',
                'financial_year_start' => 4, // April
                'is_active'            => true,
            ]
        );

        // ── Headquarters Branch ───────────────────────────────────────────────
        Branch::firstOrCreate(
            ['code' => 'HQ'],
            [
                'company_id'       => $company->id,
                'name'             => 'Head Office',
                'is_headquarters'  => true,
                'is_active'        => true,
            ]
        );

        // ── Standard Designations ─────────────────────────────────────────────
        $designations = [
            // L1 — Executive
            ['code' => 'CEO',  'name' => 'Chief Executive Officer',   'level' => 1],
            ['code' => 'COO',  'name' => 'Chief Operating Officer',   'level' => 1],
            ['code' => 'CFO',  'name' => 'Chief Financial Officer',   'level' => 1],
            ['code' => 'CTO',  'name' => 'Chief Technology Officer',  'level' => 1],
            // L2 — Senior
            ['code' => 'DM',   'name' => 'Dairy Manager',             'level' => 2],
            ['code' => 'FM',   'name' => 'Farm Manager',              'level' => 2],
            ['code' => 'HR',   'name' => 'HR Manager',                'level' => 2],
            ['code' => 'ACC',  'name' => 'Accounts Manager',          'level' => 2],
            // L3 — Mid-Level
            ['code' => 'SUP',  'name' => 'Supervisor',                'level' => 3],
            ['code' => 'VET',  'name' => 'Veterinarian',              'level' => 3],
            ['code' => 'TECH', 'name' => 'Technician',                'level' => 3],
            ['code' => 'ACCT', 'name' => 'Accountant',                'level' => 3],
            // L4 — Junior
            ['code' => 'OPR',  'name' => 'Operator',                  'level' => 4],
            ['code' => 'DRIV', 'name' => 'Driver',                    'level' => 4],
            ['code' => 'CLN',  'name' => 'Cleaner',                   'level' => 4],
            ['code' => 'GARD', 'name' => 'Guard',                     'level' => 4],
        ];

        foreach ($designations as $d) {
            Designation::firstOrCreate(
                ['code' => $d['code']],
                ['name' => $d['name'], 'level' => $d['level'], 'is_active' => true]
            );
        }

        $this->command->info('OrganizationSeeder: company, HQ branch, and ' . count($designations) . ' designations seeded.');
    }
}
