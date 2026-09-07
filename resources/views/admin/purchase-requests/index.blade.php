@extends('layouts.app')
@section('title', 'Purchase Requests')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item active">Purchase Requests</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Purchase Requests</h4>
            <p>Review, approve or reject internal purchase requests</p>
        </div>
        <a href="{{ route('admin.purchase-requests.create') }}" class="btn btn-primary-grad btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>New PR
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    @php
    $statCards = [
        ['label' => 'Draft',     'count' => $stats['draft'],     'color' => '#6b7280', 'bg' => '#f9fafb', 'icon' => 'bi-file-earmark'],
        ['label' => 'Submitted', 'count' => $stats['submitted'], 'color' => '#0284c7', 'bg' => '#eff6ff', 'icon' => 'bi-send'],
        ['label' => 'Approved',  'count' => $stats['approved'],  'color' => '#059669', 'bg' => '#f0fdf4', 'icon' => 'bi-check-circle'],
        ['label' => 'Rejected',  'count' => $stats['rejected'],  'color' => '#dc2626', 'bg' => '#fef2f2', 'icon' => 'bi-x-circle'],
    ];
    @endphp
    @foreach ($statCards as $card)
    <div class="col-6 col-md-3">
        <div style="background:{{ $card['bg'] }};border-radius:14px;padding:18px 20px;border:1.5px solid {{ $card['bg'] }};">
            <div class="d-flex align-items-center gap-3">
                <div style="width:44px;height:44px;background:{{ $card['color'] }}18;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi {{ $card['icon'] }}" style="font-size:1.2rem;color:{{ $card['color'] }};"></i>
                </div>
                <div>
                    <div style="font-size:1.6rem;font-weight:800;color:{{ $card['color'] }};line-height:1;">{{ $card['count'] }}</div>
                    <div style="font-size:.72rem;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-top:2px;">{{ $card['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3 border-0 shadow-sm">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.purchase-requests.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;">
                    <i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i>
                </span>
                <input type="text" name="search" class="form-control" placeholder="PR # or purpose…"
                    value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach (['draft','submitted','approved','rejected','converted'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Priority</label>
            <select name="priority" class="form-select" onchange="this.form.submit()">
                <option value="">All Priority</option>
                @foreach (['low','normal','high','urgent'] as $p)
                    <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Department</label>
            <select name="department_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Depts</option>
                @foreach ($departments as $d)
                    <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status','priority','department_id']))
                <a href="{{ route('admin.purchase-requests.index') }}"
                    class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                    style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Table --}}
<div class="card-glass overflow-hidden">
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>PR #</th>
                    <th>Requested By</th>
                    <th>Department</th>
                    <th>Required Date</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $pr)
                <tr>
                    <td>
                        <a href="{{ route('admin.purchase-requests.show', $pr) }}" class="fw-semibold" style="color:var(--primary);text-decoration:none;">
                            {{ $pr->pr_number }}
                        </a>
                    </td>
                    <td style="font-size:.85rem;">{{ $pr->requestedBy?->name ?? '—' }}</td>
                    <td class="text-muted" style="font-size:.82rem;">{{ $pr->department?->name ?? '—' }}</td>
                    <td style="font-size:.82rem;">{{ $pr->required_date?->format('d M Y') ?? '—' }}</td>
                    <td><span class="badge bg-{{ $pr->priority_color }}">{{ ucfirst($pr->priority) }}</span></td>
                    <td><span class="badge bg-{{ $pr->status_color }}">{{ ucfirst($pr->status) }}</span></td>
                    <td class="text-muted" style="font-size:.82rem;">{{ $pr->approvedBy?->name ?? '—' }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.purchase-requests.show', $pr) }}" class="act-btn" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if ($pr->status === 'draft')
                            <button class="act-btn" style="color:#0284c7;" onclick="submitPR({{ $pr->id }})" title="Submit">
                                <i class="bi bi-send"></i>
                            </button>
                            @endif
                            @if ($pr->status === 'submitted')
                            <button class="act-btn" style="color:#059669;" onclick="approvePR({{ $pr->id }})" title="Approve">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button class="act-btn" style="color:#dc2626;" onclick="openRejectModal({{ $pr->id }})" title="Reject">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-state">
                        <i class="bi bi-file-earmark-text"></i>
                        <p>No purchase requests found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($requests->hasPages())
    <div class="px-4 py-3 border-top">{{ $requests->withQueryString()->links() }}</div>
    @endif
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-x-circle me-2 text-danger"></i>Reject PR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <textarea id="rejectReason" class="form-control" rows="3" placeholder="Reason for rejection…" style="border-radius:10px;"></textarea>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button type="button" class="btn btn-danger px-4" id="confirmRejectBtn" style="border-radius:10px;">Reject</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let rejectPrId = null;

function submitPR(id) {
    APP.confirm('Submit PR', 'Submit this purchase request for approval?', () => {
        fetch(`/admin/purchase-requests/${id}/submit`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => {
            if (d.success) { APP.toast(d.message ?? 'PR submitted.', 'success'); setTimeout(() => location.reload(), 800); }
            else APP.toast(d.message, 'error');
        });
    });
}

function approvePR(id) {
    APP.confirm('Approve PR', 'Approve this purchase request?', () => {
        fetch(`/admin/purchase-requests/${id}/approve`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => {
            if (d.success) { APP.toast(d.message ?? 'PR approved.', 'success'); setTimeout(() => location.reload(), 800); }
            else APP.toast(d.message, 'error');
        });
    });
}

function openRejectModal(id) {
    rejectPrId = id;
    document.getElementById('rejectReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

document.getElementById('confirmRejectBtn').addEventListener('click', function () {
    const reason = document.getElementById('rejectReason').value.trim();
    if (!reason) { APP.toast('Please enter a rejection reason.', 'error'); return; }
    fetch(`/admin/purchase-requests/${rejectPrId}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ rejection_reason: reason })
    }).then(r => r.json()).then(d => {
        if (d.success) { APP.toast(d.message ?? 'PR rejected.', 'success'); setTimeout(() => location.reload(), 800); }
        else APP.toast(d.message, 'error');
    });
});
</script>
@endpush
