<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class EmployeesImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public int $imported = 0;
    public int $skipped  = 0;
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            try {
                $email = strtolower(trim($row['email'] ?? ''));

                if (!$email || User::where('email', $email)->exists()) {
                    $this->skipped++;
                    continue;
                }

                $dept = null;
                if (!empty($row['department'])) {
                    $dept = Department::where('name', 'like', '%' . trim($row['department']) . '%')->first();
                }

                $user = User::create([
                    'name'     => trim($row['full_name'] ?? $row['name'] ?? 'Unknown'),
                    'email'    => $email,
                    'password' => Hash::make($row['password'] ?? 'Employee@123'),
                    'status'   => 'active',
                ]);

                $user->assignRole('employee');

                Employee::create([
                    'user_id'         => $user->id,
                    'employee_id'     => $row['employee_id'] ?? 'EMP-' . strtoupper(Str::random(6)),
                    'department_id'   => $dept?->id,
                    'designation'     => $row['designation'] ?? 'Employee',
                    'employment_type' => $row['employment_type'] ?? 'full_time',
                    'work_location'   => $row['work_location'] ?? 'office',
                    'team'            => $row['team'] ?? null,
                    'status'          => 'active',
                    'joining_date'    => $row['joining_date'] ?? today(),
                ]);

                $this->imported++;
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Row skipped: " . $e->getMessage();
            }
        }
    }
}
