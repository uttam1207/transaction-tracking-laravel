@extends('layouts.app')

@section('title', 'Fraud Detection Rules')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.fraud-alerts.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Alerts
        </a>
        <h4 class="mb-0 fw-bold mt-1">Fraud Detection Rules</h4>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRuleModal">
        <i class="bi bi-plus-circle me-1"></i>Add Rule
    </button>
</div>

<div class="row g-4">
    @forelse($rules as $rule)
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-semibold">{{ $rule->name }}</span>
                    <span class="badge bg-secondary ms-2">{{ strtoupper(str_replace('_', ' ', $rule->type)) }}</span>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" @checked($rule->is_active)
                        onchange="toggleRule({{ $rule->id }}, this.checked)">
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="text-muted small">Action</label>
                        <div>
                            @php
                                $actionColors = ['flag' => 'warning', 'block' => 'danger', 'alert' => 'info', 'review' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $actionColors[$rule->action] ?? 'secondary' }}">
                                {{ ucfirst($rule->action) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small">Risk Score</label>
                        <div class="fw-semibold">+{{ $rule->risk_score }} pts</div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small">Severity</label>
                        <div>
                            @php $colors = ['critical' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'success']; @endphp
                            <span class="badge bg-{{ $colors[$rule->severity] ?? 'secondary' }}">
                                {{ ucfirst($rule->severity) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small">Priority</label>
                        <div class="fw-semibold">{{ $rule->priority }}</div>
                    </div>
                </div>

                @if($rule->conditions)
                <div>
                    <label class="text-muted small">Conditions</label>
                    <pre class="small bg-light p-2 rounded mb-0">{{ json_encode($rule->conditions, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5 text-muted">
            <i class="bi bi-shield-exclamation fs-1 d-block mb-2"></i>
            No fraud rules configured
        </div>
    </div>
    @endforelse
</div>

{{-- Create Rule Modal --}}
<div class="modal fade" id="createRuleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Fraud Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.fraud-alerts.rules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Rule Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Rule Type</label>
                            <select name="type" class="form-select" required>
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
                            <label class="form-label">Action</label>
                            <select name="action" class="form-select" required>
                                <option value="flag">Flag</option>
                                <option value="block">Block</option>
                                <option value="alert">Alert</option>
                                <option value="review">Review</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Severity</label>
                            <select name="severity" class="form-select" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Risk Score (+)</label>
                            <input type="number" name="risk_score" class="form-control" min="1" max="100" value="20" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority (lower = higher)</label>
                            <input type="number" name="priority" class="form-control" min="1" value="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Conditions (JSON)</label>
                            <textarea name="conditions" class="form-control" rows="4"
                                placeholder='{"amount": {"operator": ">", "value": 10000}}'></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Rule</button>
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
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({ is_active: isActive })
    }).then(r => r.json()).then(data => {
        APP.toast(isActive ? 'Rule activated' : 'Rule deactivated', 'info');
    });
}
</script>
@endpush
