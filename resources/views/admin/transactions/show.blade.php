@extends('layouts.app')
@section('title', 'Transaction Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transactions</a></li>
    <li class="breadcrumb-item active">{{ $transaction->transaction_id }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h5 class="mb-1 fw-bold">{{ $transaction->transaction_id }}</h5>
        <div class="text-muted small">Created {{ $transaction->created_at->format('M d, Y H:i:s') }}</div>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-{{ $transaction->status_badge }}-subtle text-{{ $transaction->status_badge }} fs-6 px-3 py-2">
            {{ ucfirst($transaction->status) }}
        </span>
        @if($transaction->is_flagged)
            <span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-flag-fill me-1"></i>Flagged</span>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Transaction Details -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header py-3"><h6 class="mb-0 fw-semibold">Transaction Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="small text-muted">Amount</div>
                            <div class="h4 fw-bold text-success">{{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted">Category</div>
                            <div class="fw-semibold">{{ $transaction->category }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted">Payment Method</div>
                            <div>{{ $transaction->payment_method ?? 'N/A' }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted">Reference</div>
                            <div>{{ $transaction->reference ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="small text-muted">Type</div>
                            <span class="badge bg-{{ $transaction->type == 'debit' ? 'danger' : 'success' }}-subtle text-{{ $transaction->type == 'debit' ? 'danger' : 'success' }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted">Fee</div>
                            <div>{{ $transaction->currency }} {{ number_format($transaction->fee, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted">Net Amount</div>
                            <div class="fw-semibold">{{ $transaction->currency }} {{ number_format($transaction->net_amount, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <div class="small text-muted">IP Address</div>
                            <div>{{ $transaction->ip_address ?? 'N/A' }}
                                @if($transaction->country) <span class="badge bg-secondary-subtle text-secondary">{{ $transaction->country }}</span> @endif
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="small text-muted text-uppercase fw-bold mb-2">Sender</h6>
                        <div class="mb-1"><strong>{{ $transaction->sender_name ?? 'N/A' }}</strong></div>
                        <div class="small text-muted">Account: {{ $transaction->sender_account ?? 'N/A' }}</div>
                        <div class="small text-muted">Bank: {{ $transaction->sender_bank ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small text-muted text-uppercase fw-bold mb-2">Receiver</h6>
                        <div class="mb-1"><strong>{{ $transaction->receiver_name ?? 'N/A' }}</strong></div>
                        <div class="small text-muted">Account: {{ $transaction->receiver_account ?? 'N/A' }}</div>
                        <div class="small text-muted">Bank: {{ $transaction->receiver_bank ?? 'N/A' }}</div>
                    </div>
                </div>

                @if($transaction->description)
                    <hr>
                    <div class="small text-muted mb-1">Description</div>
                    <p class="mb-0">{{ $transaction->description }}</p>
                @endif
            </div>
        </div>

        <!-- Transaction Timeline -->
        <div class="card">
            <div class="card-header py-3"><h6 class="mb-0 fw-semibold">Activity Timeline</h6></div>
            <div class="card-body">
                @forelse($transaction->logs as $log)
                <div class="d-flex gap-3 mb-3">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;">
                        <i class="bi bi-arrow-right-circle small"></i>
                    </div>
                    <div>
                        <div class="small fw-semibold">{{ ucwords(str_replace('_', ' ', $log->action)) }}</div>
                        @if($log->from_status && $log->to_status)
                            <div class="small text-muted">
                                <span class="badge bg-secondary-subtle text-secondary">{{ $log->from_status }}</span>
                                <i class="bi bi-arrow-right mx-1"></i>
                                <span class="badge bg-primary-subtle text-primary">{{ $log->to_status }}</span>
                            </div>
                        @endif
                        @if($log->notes) <div class="small text-muted">{{ $log->notes }}</div> @endif
                        <div class="small text-muted">{{ $log->performer?->name ?? 'System' }} &bull; {{ $log->created_at->format('M d, H:i') }}</div>
                    </div>
                </div>
                @empty
                <p class="text-muted small">No activity yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Risk & Fraud Panel -->
    <div class="col-lg-4">
        <!-- Risk Score -->
        <div class="card mb-4">
            <div class="card-header py-3"><h6 class="mb-0 fw-semibold">Risk Assessment</h6></div>
            <div class="card-body text-center">
                @php $riskColor = $transaction->risk_score >= 70 ? 'danger' : ($transaction->risk_score >= 40 ? 'warning' : 'success'); @endphp
                <div class="display-4 fw-bold text-{{ $riskColor }} mb-1">{{ $transaction->risk_score }}</div>
                <div class="text-muted small mb-3">Risk Score / 100</div>
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-{{ $riskColor }}" style="width: {{ $transaction->risk_score }}%"></div>
                </div>
                <span class="badge bg-{{ $riskColor }}-subtle text-{{ $riskColor }} fs-6 px-3 py-2">
                    {{ ucfirst($transaction->risk_level) }} Risk
                </span>
            </div>
        </div>

        <!-- Update Status -->
        <div class="card mb-4">
            <div class="card-header py-3"><h6 class="mb-0 fw-semibold">Update Status</h6></div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label small">New Status</label>
                    <select id="newStatus" class="form-select form-select-sm">
                        @foreach(['pending','processing','success','failed','cancelled','reversed'] as $s)
                            <option value="{{ $s }}" {{ $transaction->status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Notes</label>
                    <textarea id="statusNotes" class="form-control form-control-sm" rows="2"></textarea>
                </div>
                <button class="btn btn-primary btn-sm w-100" onclick="updateStatus()">Update Status</button>
            </div>
        </div>

        <!-- Fraud Alerts -->
        @if($transaction->fraudAlerts->count())
        <div class="card">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-semibold text-danger">
                    <i class="bi bi-shield-exclamation me-1"></i>Fraud Alerts ({{ $transaction->fraudAlerts->count() }})
                </h6>
            </div>
            <div class="card-body">
                @foreach($transaction->fraudAlerts as $alert)
                <div class="d-flex gap-2 mb-3 p-2 rounded bg-danger-subtle">
                    <span class="badge bg-{{ $alert->severity_badge }}-subtle text-{{ $alert->severity_badge }} align-self-start">{{ $alert->severity }}</span>
                    <div>
                        <div class="small fw-semibold">{{ $alert->alert_type }}</div>
                        <div class="small text-muted">{{ $alert->description }}</div>
                        <div class="smaller text-muted">Score: {{ $alert->risk_score }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateStatus() {
    const status = document.getElementById('newStatus').value;
    const notes = document.getElementById('statusNotes').value;
    APP.ajax('/admin/transactions/{{ $transaction->id }}/status', 'POST', { status, notes })
        .done(res => { if (res.success) { APP.toast('Status updated!'); setTimeout(() => location.reload(), 1000); } });
}
</script>
@endpush
