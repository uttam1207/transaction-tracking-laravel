<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $engDept = Department::where('code', 'ENG')->first();
        $hrDept = Department::where('code', 'HR')->first();
        $finDept = Department::where('code', 'FIN')->first();

        $users = [
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'super@demo.com',
                'phone' => '+1234567890',
                'password' => Hash::make('Admin@123'),
                'role' => 'super_admin',
                'status' => 'active',
                'department_id' => null,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'email' => 'admin@demo.com',
                'phone' => '+1234567891',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'status' => 'active',
                'department_id' => $engDept?->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'John Manager',
                'username' => 'jmanager',
                'email' => 'manager@demo.com',
                'phone' => '+1234567892',
                'password' => Hash::make('Admin@123'),
                'role' => 'manager',
                'status' => 'active',
                'department_id' => $engDept?->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Employee',
                'username' => 'jemployee',
                'email' => 'emp@demo.com',
                'phone' => '+1234567893',
                'password' => Hash::make('Admin@123'),
                'role' => 'employee',
                'status' => 'active',
                'department_id' => $engDept?->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Audit User',
                'username' => 'auditor',
                'email' => 'auditor@demo.com',
                'phone' => '+1234567894',
                'password' => Hash::make('Admin@123'),
                'role' => 'auditor',
                'status' => 'active',
                'department_id' => $finDept?->id,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(['email' => $userData['email']], $userData);
            $user->assignRole($userData['role']);
        }

        $this->command->info('Users seeded! Login with admin@demo.com / Admin@123');
    }
}
