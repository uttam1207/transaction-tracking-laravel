<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'leave_type_id', 'year',
        'allocated_days', 'used_days', 'carried_forward_days',
    ];

    protected $casts = [
        'allocated_days'        => 'decimal:1',
        'used_days'             => 'decimal:1',
        'carried_forward_days'  => 'decimal:1',
        'year'                  => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getRemainingDaysAttribute(): float
    {
        return max(0, (float) $this->allocated_days
                      + (float) $this->carried_forward_days
                      - (float) $this->used_days);
    }
}
