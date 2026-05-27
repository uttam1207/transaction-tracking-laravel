<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'employee_id', 'department_id', 'manager_id', 'designation',
        'team', 'joining_date', 'leaving_date', 'employment_type', 'salary',
        'salary_currency', 'bank_account', 'tax_id', 'emergency_contact',
        'address', 'city', 'state', 'country', 'postal_code', 'work_location',
        'shift_timing', 'annual_leave_balance', 'sick_leave_balance', 'status',
        'performance_score',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'leaving_date' => 'date',
        'emergency_contact' => 'array',
        'shift_timing' => 'array',
        'salary' => 'decimal:2',
        'performance_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class)->whereDate('date', today());
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function workReports()
    {
        return $this->hasMany(WorkReport::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown';
    }

    public function getEmailAttribute(): string
    {
        return $this->user?->email ?? '';
    }

    public function getTodayWorkHoursAttribute(): float
    {
        return $this->todayAttendance?->work_hours ?? 0;
    }

    public function getIsCheckedInAttribute(): bool
    {
        $today = $this->todayAttendance;
        return $today && $today->check_in && !$today->check_out;
    }
}
