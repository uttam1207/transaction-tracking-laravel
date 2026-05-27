@extends('layouts.app')

@section('title', 'Fraud Alerts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Fraud Alerts</h4>
        <p class="text-muted mb-0">Monitor and manage fraud detections</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.fraud-alerts.rules') }}" class="btn btn-outline-warning">
            <i class="bi bi-shield-exclamation me-1"></i>Manage Rules
        </a>
        <a href="{{ route('admin.fraud-alerts.blacklist') }}" class="btn btn-outline-danger">
            <i class="bi bi-ban me-1"></i>Blacklist
        </a>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-danger border-4">
            <div class="fs-2 fw-bold text-danger">{{ $stats['open'] ?? 0 }}</div>
            <div class="text-muted">Open Alerts</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-warning border-4">
            <div class="fs-2 fw-bold text-warning">{{ $stats['investigating'] ?? 0 }}</div>
            <div class="text-muted">Investigating</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-success border-4">
            <div class="fs-2 fw-bold text-success">{{ $stats['resolved'] ?? 0 }}</div>
            <div class="text-muted">Resolved</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-info border-4">
            <div class="fs-2 fw-bold text-info">{{ $stats['false_positive'] ?? 0 }}</div>
            <div class="text-muted">False Positives</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.fraud-alerts.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search alerts..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="open" @selected(request('status') === 'open')>Open</option>
                    <option value="investigating" @selected(request('status') === 'investigating')>Investigating</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                    <option value="false_positive" @selected(request('status') === 'false_positive')>False Positive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="severity" class="form-select">
                    <option value="">All Severity</option>
                    <option value="critical" @selected(request('severity') === 'critical')>Critical</option>
                    <option value="high" @selected(request('severity') === 'high')>High</option>
                    <option value="medium" @selected(request('severity') === 'medium')>Medium</option>
                    <option value="low" @selected(request('severity') === 'low')>Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To">
            </div>
            <div class="col-md-1">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="{{ route('admin.fraud-alerts.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Alerts Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Alert ID</th>
                        <th>Transaction</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Risk Score</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                    <tr>
                        <td><code class="small">#{{ $alert->id }}</code></td>
                        <td>
                            @if($alert->transaction)
                            <a href="{{ route('admin.transactions.show', $alert->transaction) }}" class="text-decoration-none">
                                <code class="small">{{ $alert->transaction->transaction_id }}</code>
                            </a>
                            @else <span class="text-muted">N/A</span> @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ ucwords(str_replace('_', ' ', $alert->alert_type)) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $colors = ['critical' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'success'];
                                $sev = $alert->severity ?? 'medium';
                            @endphp
                            <span class="badge bg-{{ $colors[$sev] ?? 'secondary' }}">
                                {{ ucfirst($sev) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                @php $rs = $alert->risk_score ?? 0; @endphp
                                <div class="progress" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-{{ $rs >= 70 ? 'danger' : ($rs >= 40 ? 'warning' : 'success') }}"
                                        style="width: {{ $rs }}%"></div>
                                </div>
                                <small>{{ $rs }}</small>
                            </div>
                        </td>
                        <td>{{ $alert->assignedTo->name ?? '—' }}</td>
                        <td>
                            @php
                                $statusColors = ['open' => 'danger', 'investigating' => 'warning', 'resolved' => 'success', 'false_positive' => 'secondary'];
                                $st = $alert->status ?? 'open';
                            @endphp
                            <span class="badge bg-{{ $statusColors[$st] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                            </span>
                        </td>
                        <td><small>{{ $alert->created_at->format('M d, Y') }}</small></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.fraud-alerts.show', $alert) }}"
                                    class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#assignModal" data-id="{{ $alert->id }}"
                                    title="Assign">
                                    <i class="bi bi-person-check"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check fs-1 d-block mb-2"></i>
                            No fraud alerts found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($alerts->hasPages())
    <div class="card-footer bg-transparent">
        {{ $alerts->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Assign Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Alert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="form-label">Assign To</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select User</option>
                        @foreach($admins ?? [] as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
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

@push('scripts')
<script>
document.getElementById('assignModal').addEventListener('show.bs.modal', function(e) {
    const id = e.relatedTarget.dataset.id;
    document.getElementById('assignForm').action = `/admin/fraud-alerts/${id}/assign`;
});
</script>
@endpush
