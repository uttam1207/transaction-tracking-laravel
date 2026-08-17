<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLead extends Model
{
    protected $fillable = [
        'name', 'company', 'email', 'phone', 'source',
        'status', 'priority', 'deal_value', 'expected_close',
        'notes', 'assigned_to', 'converted_customer_id', 'converted_at', 'created_by',
    ];

    protected $casts = [
        'deal_value'     => 'decimal:2',
        'expected_close' => 'date',
        'converted_at'   => 'datetime',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedCustomer()
    {
        return $this->belongsTo(CrmCustomer::class, 'converted_customer_id');
    }

    public function activities()
    {
        return $this->hasMany(CrmLeadActivity::class, 'lead_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'new'         => 'secondary',
            'contacted'   => 'info',
            'qualified'   => 'primary',
            'proposal'    => 'warning',
            'negotiation' => 'orange',
            'won'         => 'success',
            'lost'        => 'danger',
            default       => 'dark',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low'    => 'secondary',
            'medium' => 'warning',
            'high'   => 'danger',
            default  => 'dark',
        };
    }

    public function isConverted(): bool
    {
        return $this->status === 'won' && $this->converted_customer_id !== null;
    }
}
