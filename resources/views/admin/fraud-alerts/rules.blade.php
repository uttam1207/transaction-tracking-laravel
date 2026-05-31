@extends('layouts.app')
@section('title', 'Fraud Detection Rules')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fraud-alerts.index') }}">Fraud Alerts</a></li>
    <li class="breadcrumb-item active">Rules</li>
@endsection

@section('content')

<a href="{{ route('admin.fraud-alerts.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Alerts</a>

<div class="page-hero" style="background:linear-gradient(135deg,#7f1d1d,#991b1b,#7f1d1d);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Fraud Detection Rules</h4>
            <p>Configure automated fraud detection rules and thresholds</p>
        </div>
        <button class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);"
            data-bs-toggle="modal" data-bs-target="#createRuleModal">
            <i class="bi bi-plus-circle me-1"></i>Add Rule
        </button>
    </div>
</div>

@if($rules->isEmpty())
<div class="table-card">
    <div class="empty-state" style="padding:56px 0;"><i class="bi bi-shield-exclamation"></i><p>No fraud rules configured</p></div>
</div>
@else
<div class="row g-3">
    @foreach($rules as $rule)
    @php
        $sevColors = ['critical'=>'danger','high'=>'warning','medium'=>'info','low'=>'success'];
        $actColors = ['flag'=>'warning','block'=>'danger','alert'=>'info','review'=>'secondary'];
    @endphp
    <div class="col-md-6">
        <div style="background:#fff;border-radius:14px;border:1.5px solid #f0f0f5;overflow:hidden;height:100%;">
            <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <span style="font-weight:700;font-size:.9rem;color:#111827;">{{ $rule->name }}</span>
                    <span style="background:#f0f4ff;color:#4f46e5;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:700;font-family:monospace;margin-left:8px;">
                        {{ strtoupper(str_replace('_',' ',$rule->type)) }}
                    </span>
                </div>
                <div class="form-check form-switch mb-0" style="padding-left:2.5em;">
                    <input class="form-check-input" type="checkbox" @checked($rule->is_active)
                        onchange="toggleRule({{ $rule->id }}, this.checked)"
                        style="cursor:pointer;">
                </div>
            </div>
            <div style="padding:16px 18px;">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Action</div>
                        <span class="spill spill-{{ $actColors[$rule->action] ?? 'secondary' }}" style="font-size:.7rem;">{{ ucfirst($rule->action) }}</span>
                    </div>
                    <div class="col-6">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Risk Score</div>
                        <span style="font-weight:700;font-size:.85rem;color:#dc2626;">+{{ $rule->risk_score }} pts</span>
                    </div>
                    <div class="col-6">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Severity</div>
                        <span class="spill sev-{{ $rule->severity ?? 'medium' }}" style="font-size:.7rem;">{{ ucfirst($rule->severity) }}</span>
                    </div>
                    <div class="col-6">
                        <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Priority</div>
                        <span style="font-weight:700;font-size:.85rem;color:#374151;">{{ $rule->priority }}</span>
                    </div>
                </div>

                @if($rule->conditions)
                <div>
                    <div style="font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">Conditions</div>
                    <pre style="background:#f8f9fa;border:1.5px solid #e5e7eb;border-radius:8px;padding:10px;font-size:.76rem;color:#374151;max-height:100px;overflow:auto;margin:0;">{{ json_encode($rule->conditions, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Create Rule Modal --}}
<div class="modal fade" id="createRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-shield-plus me-2"></i>Create Fraud Rule</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.fraud-alerts.rules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Rule Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Rule Type <span class="req">*</span></label>
                            <select name="type" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="amount_threshold">Amount Threshold</option>
                                <option value="velocity_check">Velocity Check</option>
                                <option value="blacklist_check">Blacklist Check</option>
                                <option value="geo_restriction">Geo Restriction</option>
                                <option value="duplicate_detection">Duplicate Detection</option>
                                <option value="pattern_match">Pattern Match</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Action <span class="req">*</span></label>
                            <select name="action" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="flag">Flag</option>
                                <option value="block">Block</option>
                                <option value="alert">Alert</option>
                                <option value="review">Review</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Severity <span class="req">*</span></label>
                            <select name="severity" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Risk Score (+) <span class="req">*</span></label>
                            <input type="number" name="risk_score" class="form-control" min="1" max="100" value="20" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Priority <span style="color:#9ca3af;font-size:.74rem;">(lower = higher)</span></label>
                            <input type="number" name="priority" class="form-control" min="1" value="10"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Status</label>
                            <select name="is_active" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Conditions (JSON)</label>
                            <textarea name="conditions" class="form-control" rows="4"
                                placeholder='{"amount": {"operator": ">", "value": 10000}}'
                                style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;font-size:.82rem;resize:vertical;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-4" style="border-radius:9px;">Create Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleRule(id, isActive) {
    fetch(`/admin/fraud-alerts/rules/${id}/toggle`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ is_active: isActive })
    }).then(r => r.json()).then(data => {
        APP.toast(isActive ? 'Rule activated' : 'Rule deactivated', 'info');
    });
}
</script>
@endpush
