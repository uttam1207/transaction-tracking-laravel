<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Employee;
use App\Models\User;
use App\Models\Project;
use App\Models\TaskComment;
use App\Models\Timesheet;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_id', 'title', 'description', 'assigned_to', 'assigned_by', 'project_id',
        'priority', 'status', 'due_date', 'started_at', 'completed_at',
        'estimated_hours', 'actual_hours', 'progress', 'tags', 'rejection_reason',
        'is_recurring', 'recurrence_type', 'recurring_ends_at', 'parent_task_id',
    ];

    protected $casts = [
        'due_date'          => 'date',
        'recurring_ends_at' => 'date',
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
        'tags'              => 'array',
        'actual_hours'      => 'float',
        'is_recurring'      => 'boolean',
    ];

    public function parentTask()
    {
        return $this->belongsTo(static::class, 'parent_task_id');
    }

    public function childTasks()
    {
        return $this->hasMany(static::class, 'parent_task_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', today())
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'completed' => 'success',
            'in_progress' => 'primary',
            'review' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }
}
