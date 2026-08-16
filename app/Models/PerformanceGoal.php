<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_review_id', 'title', 'description',
        'target_value', 'achieved_value', 'rating', 'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, 'performance_review_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed'   => 'success',
            'in_progress' => 'primary',
            'missed'      => 'danger',
            default       => 'secondary',
        };
    }
}
