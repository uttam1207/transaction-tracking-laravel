<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'color', 'max_days_per_year', 'carry_forward_days',
        'is_paid', 'requires_approval', 'applicable_after_days',
        'is_active', 'description',
    ];

    protected $casts = [
        'is_paid'            => 'boolean',
        'requires_approval'  => 'boolean',
        'is_active'          => 'boolean',
        'max_days_per_year'  => 'integer',
        'carry_forward_days' => 'integer',
        'applicable_after_days' => 'integer',
    ];

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function balances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
