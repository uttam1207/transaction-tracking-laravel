@extends('layouts.app')

@section('title', 'Purchase Requests')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Purchase Requests</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Purchase Requests</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.purchase-requests.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New PR
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @php
        $statCards = [
            ['label' => 'Draft',     'count' => $stats['draft'],     'color' => 'secondary'],
            ['label' => 'Submitted', 'count' => $stats['submitted'], 'color' => 'info'],
            ['label' => 'Approved',  'count' => $stats['approved'],  'color' => 'success'],
            ['label' => 'Rejected',  'count' => $stats['rejected'],  'color' => 'danger'],
        ];
        @endphp
        @foreach ($statCards as $card)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-{{ $card['color'] }}">{{ $card['count'] }}</div>
                <div class="text-muted small">{{ $card['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="PR # or purpose…" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach (['draft','submitted','approved','rejected','converted'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priority</option>
                        @foreach (['low','normal','high','urgent'] as $p)
                            <option value="{{ $p }}" @selected(request('priority') === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">All Depts</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-sm btn-outline-primary">Filter</button>
                    <a href="{{ route('admin.purchase-requests.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                            <td><a href="{{ route('admin.purchase-requests.show', $pr) }}" class="text-primary fw-semibold">{{ $pr->pr_number }}</a></td>
                            <td>{{ $pr->requestedBy?->name ?? '—' }}</td>
                            <td class="text-muted small">{{ $pr->department?->name ?? '—' }}</td>
                            <td class="small">{{ $pr->required_date?->format('d M Y') ?? '—' }}</td>
                            <td><span class="badge bg-{{ $pr->priority_color }}">{{ ucfirst($pr->priority) }}</span></td>
                            <td><span class="badge bg-{{ $pr->status_color }}">{{ ucfirst($pr->status) }}</span></td>
                            <td class="small text-muted">{{ $pr->approvedBy?->name ?? '—' }}</td>
                            <td class="text-end d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.purchase-requests.show', $pr) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                @if ($pr->status === 'draft')
                                <button class="btn btn-sm btn-outline-info" onclick="submitPR({{ $pr->id }})"><i class="bi bi-send"></i></button>
                                @endif
                                @if ($pr->status === 'submitted')
                                <button class="btn btn-sm btn-outline-success" onclick="approvePR({{ $pr->id }})"><i class="bi bi-check-lg"></i></button>
                                <button class="btn btn-sm btn-outline-danger" onclick="openRejectModal({{ $pr->id }})"><i class="bi bi-x-lg"></i></button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No purchase requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($requests->hasPages())
            <div class="px-3 py-2">{{ $requests->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Reject PR</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <textarea id="rejectReason" class="form-control" rows="3" placeholder="Reason for rejection…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmRejectBtn">Reject</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let rejectPrId = null;

function submitPR(id) {
    if (!confirm('Submit this purchase request for approval?')) return;
    fetch(`/admin/purchase-requests/${id}/submit`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}

function approvePR(id) {
    if (!confirm('Approve this purchase request?')) return;
    fetch(`/admin/purchase-requests/${id}/approve`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}

function openRejectModal(id) {
    rejectPrId = id;
    document.getElementById('rejectReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

document.getElementById('confirmRejectBtn').addEventListener('click', function() {
    const reason = document.getElementById('rejectReason').value.trim();
    if (!reason) { alert('Please enter a rejection reason.'); return; }
    fetch(`/admin/purchase-requests/${rejectPrId}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ rejection_reason: reason })
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message);
    });
});
</script>
@endpush
@endsection
