<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTraining extends Model
{
    protected $fillable = [
        'employee_id', 'training_program_id', 'status',
        'score', 'certificate_path', 'completed_at', 'feedback',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'score'        => 'decimal:2',
    ];

    public function employee()        { return $this->belongsTo(Employee::class); }
    public function trainingProgram() { return $this->belongsTo(TrainingProgram::class, 'training_program_id'); }
}
