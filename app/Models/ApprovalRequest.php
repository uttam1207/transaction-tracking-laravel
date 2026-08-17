<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'workflow_id', 'requestable_type', 'requestable_id',
        'requested_by', 'status', 'current_step',
        'title', 'description', 'submitted_at', 'resolved_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function requestable()
    {
        return $this->morphTo();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function actions()
    {
        return $this->hasMany(ApprovalAction::class, 'request_id')->latest();
    }

    public function currentStepModel()
    {
        return $this->workflow->steps()->where('step_number', $this->current_step)->first();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
