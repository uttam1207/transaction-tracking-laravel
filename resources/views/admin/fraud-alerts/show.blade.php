@extends('layouts.app')

@section('title', 'Fraud Alert Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.fraud-alerts.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Alerts
        </a>
        <h4 class="mb-0 fw-bold mt-1">Fraud Alert #{{ $fraudAlert->id }}</h4>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#statusModal">
            <i class="bi bi-arrow-repeat me-1"></i>Update Status
        </button>
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal">
            <i class="bi bi-person-check me-1"></i>Assign
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        {{-- Alert Info --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold">Alert Summary</div>
            <div class="card-body">
                @php
                    $colors = ['critical' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'success'];
                    $sev = $fraudAlert->severity ?? 'medium';
                    $statusColors = ['open' => 'danger', 'investigating' => 'warning', 'resolved' => 'success', 'false_positive' => 'secondary'];
                    $st = $fraudAlert->status ?? 'open';
                @endphp
                <div class="mb-3">
                    <label class="text-muted small d-block">Severity</label>
                    <span class="badge bg-{{ $colors[$sev] ?? 'secondary' }} fs-6">{{ ucfirst($sev) }}</span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Status</label>
                    <span class="badge bg-{{ $statusColors[$st] ?? 'secondary' }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $st)) }}
                    </span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Alert Type</label>
                    <span>{{ ucwords(str_replace('_', ' ', $fraudAlert->alert_type)) }}</span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Risk Score</label>
                    <div class="d-flex align-items-center gap-2">
                        @php $rs = $fraudAlert->risk_score ?? 0; @endphp
                        <div class="progress flex-fill" style="height: 10px;">
                            <div class="progress-bar bg-{{ $rs >= 70 ? 'danger' : ($rs >= 40 ? 'warning' : 'success') }}"
                                style="width: {{ $rs }}%"></div>
                        </div>
                        <strong>{{ $rs }}/100</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Assigned To</label>
                    <span>{{ $fraudAlert->assignedTo->name ?? 'Unassigned' }}</span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small d-block">Detected At</label>
                    <span>{{ $fraudAlert->created_at->format('M d, Y H:i') }}</span>
                </div>
                @if($fraudAlert->resolved_at)
                <div>
                    <label class="text-muted small d-block">Resolved At</label>
                    <span>{{ \Carbon\Carbon::parse($fraudAlert->resolved_at)->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        @if($fraudAlert->resolution_notes)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Resolution Notes</div>
            <div class="card-body">
                <p class="mb-0">{{ $fraudAlert->resolution_notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        {{-- Description --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold">Description</div>
            <div class="card-body">
                <p class="mb-0">{{ $fraudAlert->description }}</p>
            </div>
        </div>

        {{-- Transaction Details --}}
        @if($fraudAlert->transaction)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Related Transaction</span>
                <a href="{{ route('admin.transactions.show', $fraudAlert->transaction) }}" class="btn btn-sm btn-outline-primary">
                    View Transaction
                </a>
            </div>
            <div class="card-body">
                @php $tx = $fraudAlert->transaction; @endphp
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Transaction ID</label>
                        <div><code>{{ $tx->transaction_id }}</code></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Amount</label>
                        <div class="fw-semibold">{{ $tx->currency }} {{ number_format($tx->amount, 2) }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Sender</label>
                        <div>{{ $tx->sender_name }} ({{ $tx->sender_account }})</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Receiver</label>
                        <div>{{ $tx->receiver_name }} ({{ $tx->receiver_account }})</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Country</label>
                        <div>{{ $tx->country }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">IP Address</label>
                        <div><code>{{ $tx->ip_address }}</code></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Metadata --}}
        @if($fraudAlert->metadata)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Detection Metadata</div>
            <div class="card-body">
                <pre class="mb-0 small bg-light p-3 rounded">{{ json_encode($fraudAlert->metadata, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Status Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Alert Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.fraud-alerts.status', $fraudAlert) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="open" @selected($fraudAlert->status === 'open')>Open</option>
                            <option value="investigating" @selected($fraudAlert->status === 'investigating')>Investigating</option>
                            <option value="resolved" @selected($fraudAlert->status === 'resolved')>Resolved</option>
                            <option value="false_positive" @selected($fraudAlert->status === 'false_positive')>False Positive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resolution Notes</label>
                        <textarea name="resolution_notes" class="form-control" rows="3"
                            placeholder="Add notes about this alert...">{{ $fraudAlert->resolution_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Assign Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.fraud-alerts.assign', $fraudAlert) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Assign To</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select User</option>
                        @foreach($admins ?? [] as $admin)
                            <option value="{{ $admin->id }}" @selected($fraudAlert->assigned_to == $admin->id)>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
