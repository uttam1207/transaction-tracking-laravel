<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLifecycleEvent extends Model
{
    protected $fillable = [
        'employee_id', 'event_type', 'event_date',
        'description', 'triggered_by', 'metadata',
    ];

    protected $casts = [
        'event_date' => 'date',
        'metadata'   => 'array',
    ];

    public static array $labels = [
        'hired'                => 'Hired',
        'onboarding_started'   => 'Onboarding Started',
        'onboarding_completed' => 'Onboarding Completed',
        'probation_started'    => 'Probation Started',
        'probation_extended'   => 'Probation Extended',
        'probation_passed'     => 'Probation Passed',
        'confirmed'            => 'Confirmed',
        'promoted'             => 'Promoted',
        'transferred'          => 'Transferred',
        'department_changed'   => 'Department Changed',
        'role_changed'         => 'Role Changed',
        'salary_revised'       => 'Salary Revised',
        'resigned'             => 'Resigned',
        'terminated'           => 'Terminated',
        'rehired'              => 'Rehired',
        'exit_completed'       => 'Exit Completed',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    public function getLabelAttribute(): string
    {
        return self::$labels[$this->event_type] ?? ucwords(str_replace('_', ' ', $this->event_type));
    }
}
