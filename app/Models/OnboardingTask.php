<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingTask extends Model
{
    protected $fillable = [
        'title', 'description', 'category',
        'is_required', 'due_after_days', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function employeeOnboardings()
    {
        return $this->hasMany(EmployeeOnboarding::class, 'onboarding_task_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
