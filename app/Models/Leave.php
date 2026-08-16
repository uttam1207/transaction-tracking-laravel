<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\User;
use App\Models\LeaveType;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'leave_type_id', 'type', 'from_date', 'to_date', 'days', 'reason',
        'status', 'approved_by', 'rejection_reason', 'actioned_at',
        'is_half_day', 'half_day_period',
    ];

    protected $casts = [
        'from_date'   => 'date',
        'to_date'     => 'date',
        'actioned_at' => 'datetime',
        'is_half_day' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Normalised leave type (Phase 2). Falls back to legacy `type` enum string. */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getEffectiveLeaveTypeAttribute(): string
    {
        return $this->leaveType?->name ?? ucfirst(str_replace('_', ' ', $this->type ?? 'other'));
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
