<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'report_date', 'summary', 'tasks_completed', 'hours_worked',
        'productivity_score', 'status', 'reviewed_by', 'reviewer_notes',
        'submitted_at', 'reviewed_at', 'attachments',
    ];

    protected $casts = [
        'report_date' => 'date',
        'tasks_completed' => 'array',
        'attachments' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'hours_worked' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('report_date', now()->month)
                     ->whereYear('report_date', now()->year);
    }
}
