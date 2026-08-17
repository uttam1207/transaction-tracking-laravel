<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeOnboarding extends Model
{
    protected $fillable = [
        'employee_id', 'onboarding_task_id', 'assigned_to',
        'due_date', 'completed_at', 'status', 'notes',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'completed_at' => 'datetime',
    ];

    public function employee()       { return $this->belongsTo(Employee::class); }
    public function task()           { return $this->belongsTo(OnboardingTask::class, 'onboarding_task_id'); }
    public function assignedTo()     { return $this->belongsTo(User::class, 'assigned_to'); }

    public function scopePending($query)   { return $query->where('status', 'pending'); }
    public function scopeCompleted($query) { return $query->where('status', 'completed'); }
}
