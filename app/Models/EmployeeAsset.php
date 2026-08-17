<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAsset extends Model
{
    protected $fillable = [
        'employee_id', 'asset_name', 'asset_code', 'category',
        'serial_number', 'description', 'issued_date', 'return_date',
        'condition_on_issue', 'condition_on_return', 'status',
        'issued_by', 'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'return_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }
}
