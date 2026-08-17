@extends('layouts.app')
@section('title', 'Approval Requests')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.approvals.workflows') }}">Workflows</a></li>
    <li class="breadcrumb-item active">Requests</li>
@endsection

@section('content')
<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Approval Requests</h4>
            <p>Review and action pending approval requests across all modules</p>
        </div>
    </div>
</div>

<div class="filter-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="flabel">Module</label>
            <select name="module" class="form-select form-select-sm">
                <option value="">All Modules</option>
                @foreach($workflows as $wf)
                <option value="{{ $wf->module }}" {{ request('module') === $wf->module ? 'selected' : '' }}>{{ $wf->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="pending" {{ request('status')==='pending' ? 'selected':'' }}>Pending</option>
                <option value="approved" {{ request('status')==='approved' ? 'selected':'' }}>Approved</option>
                <option value="rejected" {{ request('status')==='rejected' ? 'selected':'' }}>Rejected</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-primary-grad px-4">Filter</button>
            <a href="{{ route('admin.approvals.requests') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">Requests</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $requests->total() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Module</th>
                    <th>Requested By</th>
                    <th>Step</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td style="font-weight:600;font-size:.88rem;">{{ $req->title }}</td>
                    <td><span class="badge bg-info bg-opacity-15 text-info" style="font-size:.72rem;">{{ strtoupper(str_replace('_',' ',$req->workflow->module)) }}</span></td>
                    <td style="font-size:.83rem;">{{ $req->requester?->name }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">Step {{ $req->current_step }} of {{ $req->workflow->steps->count() }}</td>
                    <td>
                        @php $colors = ['pending'=>'warning','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary']; @endphp
                        <span class="badge bg-{{ $colors[$req->status]??'secondary' }} bg-opacity-15 text-{{ $colors[$req->status]??'secondary' }}" style="font-size:.72rem;">{{ ucfirst($req->status) }}</span>
                    </td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $req->submitted_at?->format('d M Y') }}</td>
                    <td>
                        @if($req->status === 'pending')
                        <div class="d-flex gap-1">
                            <button class="act-btn" style="background:#d1fae5;color:#065f46;" title="Approve" onclick="actOnRequest({{ $req->id }}, 'approved')"><i class="bi bi-check-lg"></i></button>
                            <button class="act-btn" style="background:#fee2e2;color:#991b1b;" title="Reject"  onclick="actOnRequest({{ $req->id }}, 'rejected')"><i class="bi bi-x-lg"></i></button>
                            <button class="act-btn" style="background:#fef3c7;color:#92400e;" title="Return"  onclick="actOnRequest({{ $req->id }}, 'returned')"><i class="bi bi-arrow-return-left"></i></button>
                        </div>
                        @else
                        <span style="font-size:.8rem;color:#9ca3af;">Resolved</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox"></i><p>No requests found</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div class="pagination-wrap">{{ $requests->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Action Notes Modal --}}
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold" id="actionModalTitle">Approval Action</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="flabel">Notes (optional)</label>
                <textarea id="actionNotes" class="form-control" rows="3" style="border-radius:9px;border:1.5px solid #e5e7eb;"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-primary-grad px-4" onclick="submitAction()">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;
let pendingRequestId = null, pendingAction = null;

function actOnRequest(id, action) {
    pendingRequestId = id; pendingAction = action;
    const titles = { approved: 'Approve Request', rejected: 'Reject Request', returned: 'Return Request' };
    document.getElementById('actionModalTitle').textContent = titles[action] || 'Action';
    document.getElementById('actionNotes').value = '';
    new bootstrap.Modal(document.getElementById('actionModal')).show();
}

function submitAction() {
    fetch(`/admin/approvals/requests/${pendingRequestId}/action`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ action: pendingAction, notes: document.getElementById('actionNotes').value }),
    }).then(r => r.json()).then(d => {
        if (d.success) { APP.toast('Action recorded.'); setTimeout(() => location.reload(), 800); }
        else APP.toast(d.message || 'Error.', 'error');
    });
}
</script>
@endpush
