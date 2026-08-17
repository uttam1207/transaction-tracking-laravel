<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePromotion extends Model
{
    protected $fillable = [
        'employee_id', 'from_designation_id', 'to_designation_id',
        'effective_date', 'salary_before', 'salary_after',
        'reason', 'status', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'approved_at'    => 'datetime',
        'salary_before'  => 'decimal:2',
        'salary_after'   => 'decimal:2',
    ];

    public function employee()         { return $this->belongsTo(Employee::class); }
    public function fromDesignation()  { return $this->belongsTo(Designation::class, 'from_designation_id'); }
    public function toDesignation()    { return $this->belongsTo(Designation::class, 'to_designation_id'); }
    public function approvedBy()       { return $this->belongsTo(User::class, 'approved_by'); }
}
