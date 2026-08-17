<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    /**
     * Create an approval request for the given module and model instance.
     *
     * @param  string  $module     e.g. 'leave', 'expense', 'employee_transfer'
     * @param  Model   $subject    The model being approved (Leave, Expense, etc.)
     * @param  User    $requester  The user submitting the request
     * @param  string  $title      Human-readable request title
     * @param  string  $description Optional description
     */
    public function createRequest(
        string $module,
        Model  $subject,
        User   $requester,
        string $title,
        string $description = ''
    ): ?ApprovalRequest {
        $workflow = ApprovalWorkflow::forModule($module);

        if (!$workflow) {
            return null; // No workflow configured — caller handles direct approval
        }

        return DB::transaction(function () use ($workflow, $subject, $requester, $title, $description) {
            return ApprovalRequest::create([
                'workflow_id'      => $workflow->id,
                'requestable_type' => get_class($subject),
                'requestable_id'   => $subject->getKey(),
                'requested_by'     => $requester->id,
                'status'           => 'pending',
                'current_step'     => 1,
                'title'            => $title,
                'description'      => $description,
                'submitted_at'     => now(),
            ]);
        });
    }

    /**
     * Process an approval action (approve / reject / return) on a request.
     *
     * @param  ApprovalRequest  $request
     * @param  User             $actor
     * @param  string           $action   'approved' | 'rejected' | 'returned'
     * @param  string           $notes
     * @return bool             true if fully resolved, false if still pending
     */
    public function processAction(
        ApprovalRequest $request,
        User            $actor,
        string          $action,
        string          $notes = ''
    ): bool {
        if (!$request->isPending()) {
            return false;
        }

        $step = $request->workflow->steps()->where('step_number', $request->current_step)->first();

        if (!$step) {
            return false;
        }

        DB::transaction(function () use ($request, $step, $actor, $action, $notes) {
            ApprovalAction::create([
                'request_id' => $request->id,
                'step_id'    => $step->id,
                'actor_id'   => $actor->id,
                'action'     => $action,
                'notes'      => $notes,
                'acted_at'   => now(),
            ]);

            if ($action === 'approved') {
                if ($step->is_final) {
                    // Last step approved — mark request fully approved
                    $request->update([
                        'status'      => 'approved',
                        'resolved_at' => now(),
                    ]);
                } else {
                    // Advance to next step
                    $nextStep = $request->workflow->steps()
                        ->where('step_number', '>', $request->current_step)
                        ->orderBy('step_number')
                        ->first();

                    if ($nextStep) {
                        $request->update(['current_step' => $nextStep->step_number]);
                    } else {
                        // No more steps — fully approved
                        $request->update([
                            'status'      => 'approved',
                            'resolved_at' => now(),
                        ]);
                    }
                }
            } elseif ($action === 'rejected') {
                $request->update([
                    'status'      => 'rejected',
                    'resolved_at' => now(),
                ]);
            } elseif ($action === 'returned') {
                // Return to previous step or back to step 1
                $prevStep = max(1, $request->current_step - 1);
                $request->update(['current_step' => $prevStep]);
            }
        });

        $request->refresh();
        return in_array($request->status, ['approved', 'rejected']);
    }

    /**
     * Determine who should act on the current step of the request.
     */
    public function getCurrentApprover(ApprovalRequest $request): ?User
    {
        $step = $request->currentStepModel();
        return $step?->resolveApprover($request);
    }

    /**
     * Get all pending requests visible to the given user (they are an approver).
     */
    public function pendingForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return ApprovalRequest::with(['workflow', 'requester', 'requestable'])
            ->pending()
            ->get()
            ->filter(function (ApprovalRequest $req) use ($user) {
                $approver = $this->getCurrentApprover($req);
                return $approver && $approver->id === $user->id;
            })
            ->values();
    }
}
