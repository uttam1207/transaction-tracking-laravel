<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Team;
use App\Models\Task;
use App\Models\Timesheet;
use App\Models\ProjectMember;
use App\Models\Milestone;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'description', 'client', 'manager_id', 'department_id',
        'start_date', 'end_date', 'status', 'priority', 'progress', 'budget', 'team_members',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'team_members' => 'array',   // kept for legacy reads; use members() for V2
        'budget'       => 'decimal:2',
    ];

    // ── Core relations ────────────────────────────────────────────────────────

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    // ── Phase 3 V2 relations ──────────────────────────────────────────────────

    public function members()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function memberEmployees()
    {
        return $this->belongsToMany(Employee::class, 'project_members')
                    ->withPivot('role', 'is_manager', 'joined_at')
                    ->withTimestamps();
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'project_teams')->withTimestamps();
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'project_departments')->withTimestamps();
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class)->orderBy('sort_order');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getCompletionRateAttribute(): float
    {
        $total = $this->tasks()->count();
        if ($total === 0) return 0;
        $completed = $this->tasks()->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 1);
    }
}
