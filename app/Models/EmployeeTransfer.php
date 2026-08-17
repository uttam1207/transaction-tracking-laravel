<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTransfer extends Model
{
    protected $fillable = [
        'employee_id', 'from_department_id', 'to_department_id',
        'from_branch_id', 'to_branch_id', 'effective_date', 'reason',
        'status', 'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'approved_at'    => 'datetime',
    ];

    public function employee()   { return $this->belongsTo(Employee::class); }
    public function fromDepartment() { return $this->belongsTo(Department::class, 'from_department_id'); }
    public function toDepartment()   { return $this->belongsTo(Department::class, 'to_department_id'); }
    public function fromBranch() { return $this->belongsTo(Branch::class, 'from_branch_id'); }
    public function toBranch()   { return $this->belongsTo(Branch::class, 'to_branch_id'); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }

    public function scopePending($query) { return $query->where('status', 'pending'); }
}
