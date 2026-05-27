<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['manager', 'employee'];
        $users = User::whereIn('role', $roles)->get();

        $engDept = Department::where('code', 'ENG')->first();
        $designations = ['Software Engineer', 'Senior Developer', 'Product Manager', 'QA Engineer', 'DevOps Engineer'];

        foreach ($users as $index => $user) {
            if (!$user->employee) {
                Employee::create([
                    'user_id' => $user->id,
                    'employee_id' => 'EMP-' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                    'department_id' => $user->department_id ?? $engDept?->id,
                    'designation' => $designations[$index % count($designations)],
                    'joining_date' => now()->subMonths(rand(1, 24))->format('Y-m-d'),
                    'employment_type' => 'full_time',
                    'work_location' => ['office', 'remote', 'hybrid'][rand(0, 2)],
                    'salary' => rand(50000, 150000),
                    'status' => 'active',
                    'performance_score' => rand(60, 100),
                    'annual_leave_balance' => 21,
                    'sick_leave_balance' => 10,
                    'shift_timing' => ['start' => '09:00', 'end' => '18:00'],
                ]);
            }
        }

        $this->command->info('Employees seeded!');
    }
}
