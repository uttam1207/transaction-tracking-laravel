<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Department;
use App\Models\Branch;
use App\Models\Shift;
use App\Models\Designation;
use App\Models\Team;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\SalaryStructure;
use App\Models\PerformanceReview;
use App\Models\Task;
use App\Models\WorkReport;
use App\Models\Timesheet;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'employee_id', 'department_id', 'branch_id', 'manager_id',
        'designation', 'designation_id', 'shift_id',
        'team', 'joining_date', 'leaving_date', 'probation_end_date',
        'notice_period_days', 'profile_photo',
        'employment_type', 'salary', 'salary_currency', 'bank_account', 'tax_id',
        'emergency_contact', 'address', 'city', 'state', 'country', 'postal_code',
        'work_location', 'shift_timing', 'annual_leave_balance', 'sick_leave_balance',
        'status', 'performance_score',
    ];

    protected $casts = [
        'joining_date'      => 'date',
        'leaving_date'      => 'date',
        'probation_end_date'=> 'date',
        'notice_period_days'=> 'integer',
        'emergency_contact' => 'array',
        'shift_timing'      => 'array',
        'salary'            => 'decimal:2',
        'performance_score' => 'decimal:2',
    ];

    // ── Core relationships ────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /** Normalised shift (Phase 1). Falls back to legacy shift_timing JSON if null. */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /** Normalised designation (Phase 1). Falls back to legacy designation string if null. */
    public function designationModel()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function manager()
    {
        return $this->belongsTo(static::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(static::class, 'manager_id');
    }

    /** Teams the employee belongs to via team_members pivot. */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
                    ->withPivot('role_in_team', 'joined_at')
                    ->withTimestamps();
    }

    // ── HR relationships ──────────────────────────────────────────────────────

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

    // ── Phase 2 HR relationships ──────────────────────────────────────────────

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }

    /**
     * Find the most specific salary structure for this employee:
     * 1. Employee-level structure → 2. Department-level → 3. Default structure
     */
    public function salaryStructure(): ?SalaryStructure
    {
        return SalaryStructure::active()
            ->where(function ($q) {
                $q->where('employee_id', $this->id)
                  ->orWhere(function ($q2) {
                      $q2->whereNull('employee_id')
                         ->where('department_id', $this->department_id);
                  })
                  ->orWhere(function ($q3) {
                      $q3->whereNull('employee_id')
                         ->whereNull('department_id')
                         ->where('is_default', true);
                  });
            })
            ->orderByRaw("CASE WHEN employee_id = {$this->id} THEN 0 WHEN department_id = " . ((int) $this->department_id) . " THEN 1 ELSE 2 END")
            ->first();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown';
    }

    public function getEmailAttribute(): string
    {
        return $this->user?->email ?? '';
    }

    public function getDisplayDesignationAttribute(): string
    {
        return $this->designationModel?->name ?? $this->designation ?? '—';
    }

    public function getEffectiveShiftStartAttribute(): ?string
    {
        // Prefer normalised FK; fall back to legacy JSON
        if ($this->shift_id && $this->shift) {
            return substr($this->shift->start_time ?? '', 0, 5);
        }
        return $this->shift_timing['start'] ?? null;
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
