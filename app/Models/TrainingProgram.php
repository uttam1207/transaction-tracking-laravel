<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'trainer', 'department_id',
        'start_date', 'end_date', 'duration_hours',
        'is_mandatory', 'mode', 'venue', 'status', 'created_by',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'is_mandatory'   => 'boolean',
        'duration_hours' => 'decimal:1',
    ];

    public function department() { return $this->belongsTo(Department::class); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }

    public function enrollments()
    {
        return $this->hasMany(EmployeeTraining::class, 'training_program_id');
    }

    public function enrolledEmployees()
    {
        return $this->belongsToMany(Employee::class, 'employee_trainings', 'training_program_id', 'employee_id')
                    ->withPivot('status', 'score', 'certificate_path', 'completed_at')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planned', 'ongoing']);
    }
}
