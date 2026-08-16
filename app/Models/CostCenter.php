<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id', 'branch_id', 'name', 'code',
        'description', 'monthly_budget', 'is_active',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
