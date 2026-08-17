<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeReportingHistory extends Model
{
    protected $fillable = [
        'employee_id', 'reports_to_employee_id',
        'relationship_type', 'started_at', 'ended_at', 'notes',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at'   => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reportsTo()
    {
        return $this->belongsTo(Employee::class, 'reports_to_employee_id');
    }

    public function scopeCurrent($query)
    {
        return $query->whereNull('ended_at');
    }
}
