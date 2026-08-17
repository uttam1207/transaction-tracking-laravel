<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'workflow_id', 'step_number', 'step_name', 'approver_type',
        'approver_user_id', 'approver_role', 'is_final', 'is_optional',
    ];

    protected $casts = [
        'is_final'    => 'boolean',
        'is_optional' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function approverUser()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function actions()
    {
        return $this->hasMany(ApprovalAction::class, 'step_id');
    }

    /**
     * Resolve the approver user for a given request context.
     * Used by ApprovalService to determine who should act next.
     */
    public function resolveApprover(ApprovalRequest $request): ?User
    {
        $requester = $request->requester;
        $employee  = $requester->employee;

        return match ($this->approver_type) {
            'specific_user'   => $this->approverUser,
            'role'            => User::where('role', $this->approver_role)->active()->first(),
            'manager'         => $employee?->manager?->user,
            'department_head' => $employee?->department?->manager,
            'team_lead'       => $employee?->teams()->first()?->teamLead,
            'hr'              => User::where('role', 'hr')->active()->first(),
            default           => null,
        };
    }
}
