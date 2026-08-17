<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDepartment extends Model
{
    protected $fillable = [
        'employee_id', 'department_id', 'is_primary',
        'role_in_department', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'is_primary'  => 'boolean',
        'started_at'  => 'date',
        'ended_at'    => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeCurrent($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
