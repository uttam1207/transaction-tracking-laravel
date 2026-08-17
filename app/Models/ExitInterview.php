<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExitInterview extends Model
{
    protected $fillable = [
        'employee_id', 'resignation_date', 'last_working_date',
        'notice_period_days', 'reason', 'detailed_reason',
        'exit_interview_date', 'interviewer_id', 'feedback',
        'satisfaction_rating', 'rehire_eligible', 'status',
    ];

    protected $casts = [
        'resignation_date'    => 'date',
        'last_working_date'   => 'date',
        'exit_interview_date' => 'date',
        'rehire_eligible'     => 'boolean',
    ];

    public function employee()    { return $this->belongsTo(Employee::class); }
    public function interviewer() { return $this->belongsTo(User::class, 'interviewer_id'); }
}
