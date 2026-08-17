<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $fillable = [
        'pr_number', 'requested_by', 'department_id', 'required_date',
        'purpose', 'priority', 'status', 'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'required_date' => 'date',
        'approved_at'   => 'datetime',
    ];

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function rfqs()
    {
        return $this->hasMany(Rfq::class, 'purchase_request_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'secondary',
            'submitted' => 'info',
            'approved'  => 'success',
            'rejected'  => 'danger',
            'converted' => 'primary',
            default     => 'dark',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low'    => 'secondary',
            'normal' => 'info',
            'high'   => 'warning',
            'urgent' => 'danger',
            default  => 'dark',
        };
    }

    public static function generateNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->count() + 1;
        return 'PR-' . $year . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
}
