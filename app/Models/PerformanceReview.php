<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'reviewer_id', 'period', 'period_start', 'period_end',
        'overall_rating', 'status', 'strengths', 'areas_for_improvement',
        'goals_for_next_period', 'employee_comments', 'submitted_at', 'acknowledged_at',
    ];

    protected $casts = [
        'period_start'    => 'date',
        'period_end'      => 'date',
        'overall_rating'  => 'decimal:1',
        'submitted_at'    => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function goals()
    {
        return $this->hasMany(PerformanceGoal::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['closed']);
    }

    public function getRatingColorAttribute(): string
    {
        $r = (float) $this->overall_rating;
        if ($r >= 4.5) return 'success';
        if ($r >= 3.5) return 'info';
        if ($r >= 2.5) return 'warning';
        return 'danger';
    }
}
