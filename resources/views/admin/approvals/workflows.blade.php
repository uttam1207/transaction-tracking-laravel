@extends('layouts.app')
@section('title', 'Approval Workflows')
@section('breadcrumb')
    <li class="breadcrumb-item active">Approval Workflows</li>
@endsection

@section('content')
<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Approval Workflows</h4>
            <p>Configure multi-step approval chains for each module</p>
        </div>
        <a href="{{ route('admin.approvals.requests') }}" class="btn btn-sm btn-outline-primary px-4">
            <i class="bi bi-inbox me-1"></i>Pending Requests
        </a>
    </div>
</div>

@foreach($workflows as $wf)
<div class="table-card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <span class="card-title">{{ $wf->name }}</span>
            <span class="badge bg-primary bg-opacity-15 text-primary ms-2" style="font-size:.72rem;">{{ strtoupper(str_replace('_',' ',$wf->module)) }}</span>
            <span class="spill {{ $wf->is_active ? 'spill-active' : 'spill-inactive' }} ms-2">{{ $wf->is_active ? 'Active' : 'Inactive' }}</span>
        </div>
        <button class="btn btn-sm btn-outline-primary px-3" onclick="openEditWorkflow({{ $wf->id }})">
            <i class="bi bi-pencil me-1"></i>Edit
        </button>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead><tr><th>#</th><th>Step Name</th><th>Approver Type</th><th>Approver</th><th>Final</th></tr></thead>
            <tbody>
                @forelse($wf->steps as $step)
                <tr>
                    <td><span style="background:#ede9fe;color:#7c3aed;padding:2px 8px;border-radius:20px;font-size:.75rem;font-weight:700;">{{ $step->step_number }}</span></td>
                    <td style="font-weight:600;font-size:.88rem;">{{ $step->step_name }}</td>
                    <td><span class="badge bg-secondary bg-opacity-15 text-secondary" style="font-size:.72rem;">{{ str_replace('_',' ',$step->approver_type) }}</span></td>
                    <td style="font-size:.82rem;color:#6b7280;">
                        @if($step->approver_type === 'specific_user') {{ $step->approverUser?->name ?? '—' }}
                        @elseif($step->approver_type === 'role') {{ $step->approver_role }}
                        @else {{ ucwords(str_replace('_',' ',$step->approver_type)) }}
                        @endif
                    </td>
                    <td>@if($step->is_final)<span class="badge bg-success bg-opacity-15 text-success" style="font-size:.72rem;">Yes</span>@else —@endif</td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty-state"><i class="bi bi-diagram-2"></i><p>No steps configured</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach

{{-- Create new workflow button --}}
<div class="d-flex justify-content-center mt-3">
    <button class="btn btn-primary-grad px-5" onclick="openNewWorkflow()">
        <i class="bi bi-plus-lg me-1"></i>New Workflow
    </button>
</div>

{{-- Workflow Modal --}}
<div class="modal fade" id="wfModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="wfModalTitle">New Approval Workflow</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="wfForm">
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="flabel">Workflow Name <span class="req">*</span></label>
                            <input type="text" id="wfName" class="form-control" placeholder="e.g. Leave Approval" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Module <span class="req">*</span></label>
                            <select id="wfModule" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="leave">Leave</option>
                                <option value="expense">Expense</option>
                                <option value="purchase">Purchase</option>
                                <option value="attendance_correction">Attendance Correction</option>
                                <option value="employee_transfer">Employee Transfer</option>
                                <option value="salary_revision">Salary Revision</option>
                                <option value="asset_request">Asset Request</option>
                                <option value="employee_promotion">Employee Promotion</option>
                                <option value="exit_request">Exit Request</option>
                            </select>
                        </div>
                    </div>

                    <label class="flabel mb-2">Approval Steps</label>
                    <div id="stepsContainer"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2 px-3" onclick="addStep()">
                        <i class="bi bi-plus-lg me-1"></i>Add Step
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary-grad px-4" onclick="saveWorkflow()">Save Workflow</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@php
    $approversJson  = $approvers->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values();
    $workflowsJson  = $workflows->map(function($w) {
        return [
            'id'     => $w->id,
            'name'   => $w->name,
            'module' => $w->module,
            'steps'  => $w->steps->map(function($s) {
                return [
                    'step_name'       => $s->step_name,
                    'approver_type'   => $s->approver_type,
                    'approver_user_id'=> $s->approver_user_id,
                    'approver_role'   => $s->approver_role,
                    'is_optional'     => $s->is_optional,
                ];
            })->values(),
        ];
    })->values();
@endphp
@push('scripts')
<script>
const csrfToken    = document.querySelector('meta[name=csrf-token]').content;
const allUsers     = @json($approversJson);
const allWorkflows = @json($workflowsJson);
let stepIdx = 0, editingId = null;

function addStep(data = {}) {
    const userOpts = allUsers.map(u => `<option value="${u.id}" ${data.approver_user_id == u.id ? 'selected':''}>${u.name}</option>`).join('');
    const html = `
    <div class="step-row border rounded p-3 mb-2 bg-light" id="step_${stepIdx}">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong style="font-size:.85rem;">Step ${stepIdx + 1}</strong>
            <button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="removeStep(${stepIdx})"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" placeholder="Step name" value="${data.step_name||''}" data-field="step_name" style="border-radius:7px;">
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" data-field="approver_type" onchange="toggleApproverFields(${stepIdx}, this.value)" style="border-radius:7px;">
                    ${['specific_user','role','manager','department_head','team_lead','hr'].map(t =>
                        `<option value="${t}" ${data.approver_type===t?'selected':''}>${t.replace(/_/g,' ')}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-3 approver-user-wrap-${stepIdx}" style="display:${data.approver_type==='specific_user'?'':'none'};">
                <select class="form-select form-select-sm" data-field="approver_user_id" style="border-radius:7px;">
                    <option value="">— Select User —</option>${userOpts}
                </select>
            </div>
            <div class="col-md-3 approver-role-wrap-${stepIdx}" style="display:${data.approver_type==='role'?'':'none'};">
                <input type="text" class="form-control form-control-sm" placeholder="Role name" value="${data.approver_role||''}" data-field="approver_role" style="border-radius:7px;">
            </div>
            <div class="col-md-2">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" data-field="is_optional" ${data.is_optional?'checked':''}>
                    <label class="form-check-label" style="font-size:.82rem;">Optional</label>
                </div>
            </div>
        </div>
    </div>`;
    document.getElementById('stepsContainer').insertAdjacentHTML('beforeend', html);
    if (data.approver_type) toggleApproverFields(stepIdx, data.approver_type);
    stepIdx++;
}

function toggleApproverFields(idx, type) {
    document.querySelector(`.approver-user-wrap-${idx}`).style.display = type === 'specific_user' ? '' : 'none';
    document.querySelector(`.approver-role-wrap-${idx}`).style.display = type === 'role' ? '' : 'none';
}

function removeStep(idx) {
    document.getElementById(`step_${idx}`)?.remove();
}

function collectSteps() {
    return [...document.querySelectorAll('#stepsContainer .step-row')].map(row => ({
        step_name:        row.querySelector('[data-field=step_name]').value,
        approver_type:    row.querySelector('[data-field=approver_type]').value,
        approver_user_id: row.querySelector('[data-field=approver_user_id]')?.value || null,
        approver_role:    row.querySelector('[data-field=approver_role]')?.value || null,
        is_optional:      row.querySelector('[data-field=is_optional]').checked,
    }));
}

function openNewWorkflow() {
    editingId = null;
    document.getElementById('wfModalTitle').textContent = 'New Approval Workflow';
    document.getElementById('wfName').value = '';
    document.getElementById('stepsContainer').innerHTML = '';
    stepIdx = 0;
    addStep();
    new bootstrap.Modal(document.getElementById('wfModal')).show();
}

function openEditWorkflow(id) {
    const wf = allWorkflows.find(w => w.id === id);
    if (!wf) return;
    editingId = id;
    document.getElementById('wfModalTitle').textContent = 'Edit Approval Workflow';
    document.getElementById('wfName').value  = wf.name;
    document.getElementById('wfModule').value = wf.module;
    document.getElementById('stepsContainer').innerHTML = '';
    stepIdx = 0;
    (wf.steps || []).forEach(s => addStep(s));
    new bootstrap.Modal(document.getElementById('wfModal')).show();
}

function saveWorkflow() {
    const payload = {
        _token: csrfToken,
        name: document.getElementById('wfName').value,
        module: document.getElementById('wfModule').value,
        steps: collectSteps(),
    };
    fetch('{{ route("admin.approvals.workflows.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    }).then(r => r.json()).then(d => {
        if (d.success) { APP.toast('Workflow saved.'); setTimeout(() => location.reload(), 1000); }
        else APP.toast(d.message || 'Error saving.', 'error');
    });
}
</script>
@endpush
