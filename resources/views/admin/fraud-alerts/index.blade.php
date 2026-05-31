@extends('layouts.app')
@section('title', 'Fraud Alerts')

@section('breadcrumb')
    <li class="breadcrumb-item active">Fraud Alerts</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Fraud Alerts</h4>
            <p>Monitor, investigate and resolve flagged transactions</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            @foreach(['open'=>['Open','#ef4444'],'investigating'=>['Investigating','#f59e0b'],'resolved'=>['Resolved','#22c55e'],'false_positive'=>['False +ve','#6b7280']] as $k=>$v)
            <div class="page-hero-stat">
                <div class="v" style="color:{{ $v[1] }};">{{ $stats[$k] ?? 0 }}</div>
                <div class="l">{{ $v[0] }}</div>
            </div>
            @if(!$loop->last)<div class="hero-vr"></div>@endif
            @endforeach
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            <a href="{{ route('admin.fraud-alerts.rules') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:8px;font-size:.8rem;font-weight:600;">
                <i class="bi bi-shield-exclamation me-1"></i>Rules
            </a>
            <a href="{{ route('admin.fraud-alerts.blacklist') }}" class="btn btn-sm" style="background:rgba(239,68,68,.25);color:#fca5a5;border:1px solid rgba(239,68,68,.35);border-radius:8px;font-size:.8rem;font-weight:600;">
                <i class="bi bi-ban me-1"></i>Blacklist
            </a>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.fraud-alerts.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <div class="position-relative">
                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.8rem;pointer-events:none;"></i>
                <input type="text" name="search" class="form-control ps-4" placeholder="Search alerts…" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="open" @selected(request('status')==='open')>Open</option>
                <option value="investigating" @selected(request('status')==='investigating')>Investigating</option>
                <option value="resolved" @selected(request('status')==='resolved')>Resolved</option>
                <option value="false_positive" @selected(request('status')==='false_positive')>False Positive</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="severity" class="form-select">
                <option value="">All Severity</option>
                <option value="critical" @selected(request('severity')==='critical')>Critical</option>
                <option value="high" @selected(request('severity')==='high')>High</option>
                <option value="medium" @selected(request('severity')==='medium')>Medium</option>
                <option value="low" @selected(request('severity')==='low')>Low</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-filter btn-primary flex-fill"><i class="bi bi-search"></i></button>
            <a href="{{ route('admin.fraud-alerts.index') }}" class="btn btn-filter btn-outline-secondary px-3"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="card-header">
        <span class="card-title">All Alerts
            @if($alerts->total())
                <span style="margin-left:8px;background:#fee2e2;color:#dc2626;font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:20px;">{{ number_format($alerts->total()) }}</span>
            @endif
        </span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Alert ID</th>
                    <th>Transaction</th>
                    <th>Type</th>
                    <th>Severity</th>
                    <th>Risk Score</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                @php
                    $sev = $alert->severity ?? 'medium';
                    $st  = $alert->status  ?? 'open';
                    $rs  = $alert->risk_score ?? 0;
                    $riskCls = $rs >= 70 ? 'danger' : ($rs >= 40 ? 'warning' : 'success');
                @endphp
                <tr>
                    <td><span style="font-family:monospace;font-size:.78rem;font-weight:700;color:#7c3aed;">#{{ $alert->id }}</span></td>
                    <td>
                        @if($alert->transaction)
                            <a href="{{ route('admin.transactions.show', $alert->transaction) }}" style="font-family:monospace;font-size:.78rem;color:#4f46e5;text-decoration:none;font-weight:700;">
                                {{ $alert->transaction->transaction_id }}
                            </a>
                        @else <span style="color:#9ca3af;font-size:.82rem;">N/A</span> @endif
                    </td>
                    <td><span style="background:#f3f4f6;color:#374151;padding:3px 8px;border-radius:6px;font-size:.73rem;font-weight:600;">{{ ucwords(str_replace('_',' ',$alert->alert_type)) }}</span></td>
                    <td><span class="spill spill-{{ $sev == 'critical' || $sev == 'high' ? 'danger' : ($sev == 'medium' ? 'warning' : 'success') }} sev-{{ $sev }}">{{ ucfirst($sev) }}</span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:5px;border-radius:3px;background:#e5e7eb;overflow:hidden;min-width:50px;">
                                <div style="height:100%;width:{{ $rs }}%;background:{{ $rs>=70?'#ef4444':($rs>=40?'#f59e0b':'#22c55e') }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:.75rem;font-weight:700;color:{{ $rs>=70?'#dc2626':($rs>=40?'#d97706':'#16a34a') }};">{{ $rs }}</span>
                        </div>
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $alert->assignedTo->name ?? '—' }}</td>
                    <td><span class="spill spill-{{ $st }}">{{ ucfirst(str_replace('_',' ',$st)) }}</span></td>
                    <td style="font-size:.78rem;color:#6b7280;white-space:nowrap;">{{ $alert->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.fraud-alerts.show', $alert) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <button class="act-btn act-edit" data-bs-toggle="modal" data-bs-target="#assignModal" data-id="{{ $alert->id }}" title="Assign"><i class="bi bi-person-check"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9">
                    <div class="empty-state"><i class="bi bi-shield-check"></i><p>No fraud alerts found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($alerts->hasPages())
    <div class="pagination-wrap">
        <span class="pagination-info">Showing {{ $alerts->firstItem() }}–{{ $alerts->lastItem() }} of {{ number_format($alerts->total()) }}</span>
        {{ $alerts->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Assign Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Assign Alert</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="flabel">Assign To</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select User</option>
                        @foreach($admins ?? [] as $admin)
                            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('assignModal').addEventListener('show.bs.modal', function(e) {
    document.getElementById('assignForm').action = `/admin/fraud-alerts/${e.relatedTarget.dataset.id}/assign`;
});
</script>
@endpush
