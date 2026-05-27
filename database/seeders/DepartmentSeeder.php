<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Engineering', 'code' => 'ENG', 'description' => 'Software Development & Engineering'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Financial Operations & Accounting'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'HR & People Management'],
            ['name' => 'Sales & Marketing', 'code' => 'SALES', 'description' => 'Sales and Marketing'],
            ['name' => 'Operations', 'code' => 'OPS', 'description' => 'Business Operations'],
            ['name' => 'Customer Support', 'code' => 'CS', 'description' => 'Customer Service & Support'],
            ['name' => 'Legal & Compliance', 'code' => 'LEGAL', 'description' => 'Legal & Regulatory Compliance'],
            ['name' => 'IT Infrastructure', 'code' => 'IT', 'description' => 'IT Infrastructure & Security'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }

        $this->command->info('Departments seeded!');
    }
}
