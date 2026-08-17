<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalAction;
use App\Models\ApprovalRequest;
use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class ApprovalWorkflowController extends Controller
{
    public function __construct(private ApprovalService $approvalService) {}

    // ── Workflow CRUD ─────────────────────────────────────────────────────────

    public function index()
    {
        $workflows = ApprovalWorkflow::with('steps.approverUser')->get();
        $approvers = User::active()->orderBy('name')->get();
        return view('admin.approvals.workflows', compact('workflows', 'approvers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'module'      => 'required|in:leave,expense,purchase,attendance_correction,employee_transfer,salary_revision,asset_request,employee_promotion,exit_request',
            'description' => 'nullable|string',
            'steps'       => 'required|array|min:1',
            'steps.*.step_name'       => 'required|string|max:100',
            'steps.*.approver_type'   => 'required|in:specific_user,role,department_head,team_lead,hr,manager',
            'steps.*.approver_user_id'=> 'nullable|exists:users,id',
            'steps.*.approver_role'   => 'nullable|string|max:50',
            'steps.*.is_optional'     => 'nullable|boolean',
        ]);

        $workflow = ApprovalWorkflow::updateOrCreate(
            ['module' => $request->module],
            ['name' => $request->name, 'description' => $request->description, 'is_active' => true]
        );

        $workflow->steps()->delete();
        foreach ($request->steps as $i => $step) {
            ApprovalStep::create([
                'workflow_id'      => $workflow->id,
                'step_number'      => $i + 1,
                'step_name'        => $step['step_name'],
                'approver_type'    => $step['approver_type'],
                'approver_user_id' => $step['approver_user_id'] ?? null,
                'approver_role'    => $step['approver_role'] ?? null,
                'is_final'         => ($i === count($request->steps) - 1),
                'is_optional'      => (bool) ($step['is_optional'] ?? false),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Workflow saved.']);
    }

    public function destroy(ApprovalWorkflow $approvalWorkflow)
    {
        $approvalWorkflow->steps()->delete();
        $approvalWorkflow->delete();
        return response()->json(['success' => true, 'message' => 'Workflow deleted.']);
    }

    // ── Approval Requests (queue) ─────────────────────────────────────────────

    public function requests(Request $request)
    {
        $query = ApprovalRequest::with(['workflow', 'requester', 'requestable']);

        if ($request->module) {
            $query->whereHas('workflow', fn($q) => $q->where('module', $request->module));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $requests  = $query->latest()->paginate(20)->withQueryString();
        $workflows = ApprovalWorkflow::all();
        return view('admin.approvals.requests', compact('requests', 'workflows'));
    }

    public function processAction(Request $request, ApprovalRequest $approvalRequest)
    {
        $request->validate([
            'action' => 'required|in:approved,rejected,returned',
            'notes'  => 'nullable|string|max:500',
        ]);

        $resolved = $this->approvalService->processAction(
            $approvalRequest,
            auth()->user(),
            $request->action,
            $request->notes ?? ''
        );

        return response()->json([
            'success'  => true,
            'resolved' => $resolved,
            'status'   => $approvalRequest->fresh()->status,
            'message'  => 'Action recorded.',
        ]);
    }
}
