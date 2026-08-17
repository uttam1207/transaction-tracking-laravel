@extends('layouts.app')

@section('title', 'PR — ' . $pr->pr_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">{{ $pr->pr_number }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.purchase-requests.index') }}">Purchase Requests</a></li>
                <li class="breadcrumb-item active">{{ $pr->pr_number }}</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            @if ($pr->status === 'draft')
            <button class="btn btn-info btn-sm" onclick="submitPR({{ $pr->id }})"><i class="bi bi-send me-1"></i> Submit</button>
            @endif
            @if ($pr->status === 'submitted')
            <button class="btn btn-success btn-sm" onclick="approvePR({{ $pr->id }})"><i class="bi bi-check-circle me-1"></i> Approve</button>
            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="bi bi-x-circle me-1"></i> Reject</button>
            @endif
            <a href="{{ route('admin.purchase-requests.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Request Details</span>
                    <span class="badge bg-{{ $pr->status_color }}">{{ ucfirst($pr->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-sm-3"><div class="text-muted small">PR Number</div><div class="fw-semibold">{{ $pr->pr_number }}</div></div>
                        <div class="col-sm-3"><div class="text-muted small">Requested By</div><div>{{ $pr->requestedBy?->name ?? '—' }}</div></div>
                        <div class="col-sm-3"><div class="text-muted small">Department</div><div>{{ $pr->department?->name ?? '—' }}</div></div>
                        <div class="col-sm-3"><div class="text-muted small">Priority</div><div><span class="badge bg-{{ $pr->priority_color }}">{{ ucfirst($pr->priority) }}</span></div></div>
                        <div class="col-sm-3"><div class="text-muted small">Required Date</div><div>{{ $pr->required_date?->format('d M Y') ?? '—' }}</div></div>
                        <div class="col-sm-3"><div class="text-muted small">Created</div><div>{{ $pr->created_at->format('d M Y') }}</div></div>
                        @if ($pr->purpose)
                        <div class="col-12"><div class="text-muted small">Purpose</div><div>{{ $pr->purpose }}</div></div>
                        @endif
                        @if ($pr->rejection_reason)
                        <div class="col-12"><div class="text-muted small text-danger">Rejection Reason</div><div class="text-danger">{{ $pr->rejection_reason }}</div></div>
                        @endif
                    </div>

                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th>Unit</th>
                                <th class="text-end">Est. Unit Price</th>
                                <th class="text-end">Est. Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pr->items as $i => $item)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $item->item_name }}</td>
                                <td class="text-muted small">{{ $item->description ?? '—' }}</td>
                                <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ $item->unit }}</td>
                                <td class="text-end">{{ $item->estimated_unit_price ? number_format($item->estimated_unit_price, 2) : '—' }}</td>
                                <td class="text-end">{{ $item->estimated_total ? number_format($item->estimated_total, 2) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @if ($pr->items->whereNotNull('estimated_total')->sum('estimated_total') > 0)
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="6" class="text-end">Estimated Total</td>
                                <td class="text-end">{{ number_format($pr->items->sum('estimated_total'), 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold">Approval Info</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7"><span class="badge bg-{{ $pr->status_color }}">{{ ucfirst($pr->status) }}</span></dd>
                        @if ($pr->approvedBy)
                        <dt class="col-5 text-muted">Actioned By</dt>
                        <dd class="col-7">{{ $pr->approvedBy->name }}</dd>
                        <dt class="col-5 text-muted">Actioned At</dt>
                        <dd class="col-7">{{ $pr->approved_at?->format('d M Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
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
                <button type="button" class="btn btn-danger btn-sm" onclick="doReject()">Reject</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function submitPR(id) {
    if (!confirm('Submit for approval?')) return;
    fetch(`/admin/purchase-requests/${id}/submit`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}

function approvePR(id) {
    if (!confirm('Approve this purchase request?')) return;
    fetch(`/admin/purchase-requests/${id}/approve`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}

function doReject() {
    const reason = document.getElementById('rejectReason').value.trim();
    if (!reason) { alert('Please enter a reason.'); return; }
    fetch(`/admin/purchase-requests/{{ $pr->id }}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ rejection_reason: reason })
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}
</script>
@endpush
@endsection
