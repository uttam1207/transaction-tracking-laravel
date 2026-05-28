<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function query()
    {
        return Employee::with(['user', 'department'])->latest();
    }

    public function headings(): array
    {
        return [
            'Employee ID', 'Full Name', 'Email', 'Department', 'Designation',
            'Employment Type', 'Work Location', 'Team', 'Status',
            'Joining Date', 'Performance Score',
            'Annual Leave Balance', 'Sick Leave Balance',
        ];
    }

    public function map($emp): array
    {
        return [
            $emp->employee_id,
            $emp->full_name,
            $emp->email,
            $emp->department?->name ?? 'N/A',
            $emp->designation,
            $emp->employment_type,
            $emp->work_location,
            $emp->team ?? 'N/A',
            $emp->status,
            $emp->joining_date?->format('Y-m-d'),
            $emp->performance_score,
            $emp->annual_leave_balance,
            $emp->sick_leave_balance,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                  'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                             'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}
