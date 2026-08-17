<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentJob extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'job_code', 'department_id', 'designation_id', 'vacancies',
        'description', 'requirements', 'employment_type',
        'salary_range_min', 'salary_range_max', 'posted_date', 'closing_date',
        'location', 'status', 'created_by',
    ];

    protected $casts = [
        'posted_date'      => 'date',
        'closing_date'     => 'date',
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
    ];

    public function department()   { return $this->belongsTo(Department::class); }
    public function designation()  { return $this->belongsTo(Designation::class); }
    public function createdBy()    { return $this->belongsTo(User::class, 'created_by'); }
    public function applications() { return $this->hasMany(RecruitmentApplication::class, 'job_id'); }

    public function scopeOpen($query)   { return $query->where('status', 'open'); }
    public function scopeActive($query) { return $query->whereIn('status', ['open', 'on_hold']); }

    public function getApplicationCountAttribute(): int
    {
        return $this->applications()->count();
    }
}
