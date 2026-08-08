<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'year', 'month_number', 'month_label',
        'basic_salary', 'hra', 'other_allowances', 'gross_salary',
        'pf_deduction', 'tax_deduction', 'other_deductions', 'net_salary',
        'days_worked', 'days_absent', 'payment_status', 'payment_date',
        'payment_mode', 'remarks',
    ];

    protected $casts = [
        'basic_salary'     => 'decimal:2',
        'hra'              => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'gross_salary'     => 'decimal:2',
        'pf_deduction'     => 'decimal:2',
        'tax_deduction'    => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_salary'       => 'decimal:2',
        'days_worked'      => 'decimal:1',
        'days_absent'      => 'decimal:1',
        'payment_date'     => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
