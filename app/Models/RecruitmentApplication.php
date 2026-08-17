<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentApplication extends Model
{
    protected $fillable = [
        'job_id', 'applicant_name', 'applicant_email', 'applicant_phone',
        'resume_path', 'cover_letter', 'stage', 'notes',
        'offered_salary', 'interview_date', 'applied_at',
    ];

    protected $casts = [
        'applied_at'     => 'datetime',
        'interview_date' => 'date',
        'offered_salary' => 'decimal:2',
    ];

    public function job()
    {
        return $this->belongsTo(RecruitmentJob::class, 'job_id');
    }

    public static array $stageLabels = [
        'applied'              => 'Applied',
        'screening'            => 'Screening',
        'shortlisted'          => 'Shortlisted',
        'interview_scheduled'  => 'Interview Scheduled',
        'interviewed'          => 'Interviewed',
        'offer_sent'           => 'Offer Sent',
        'hired'                => 'Hired',
        'rejected'             => 'Rejected',
    ];
}
