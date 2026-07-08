@extends('layouts.app')
@section('title', 'Leave Requests')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
    <li class="breadcrumb-item active">Leave Requests</li>
@endsection

@section('content')

<a href="{{ route('admin.attendance.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Attendance</a>

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Leave Requests</h4>
            <p>Review and approve or reject employee leave applications</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat"><div class="v" style="color:#fde047;">{{ $leaves->where('status','pending')->count() }}</div><div class="l">Pending</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#86efac;">{{ $leaves->where('status','approved')->count() }}</div><div class="l">Approved</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#fca5a5;">{{ $leaves->where('status','rejected')->count() }}</div><div class="l">Rejected</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v">{{ $leaves->total() }}</div><div class="l">Total</div></div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="card-header"><span class="card-title">All Leave Requests</span></div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                @php $st = $leave->status ?? 'pending'; @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ $leave->employee->full_name ?? 'Unknown' }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">{{ $leave->employee->employee_id ?? '' }}</div>
                    </td>
                    <td><span style="background:#f0f4ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:600;">{{ ucwords(str_replace('_',' ',$leave->type)) }}</span></td>
                    <td style="font-size:.83rem;">{{ \Carbon\Carbon::parse($leave->from_date)->format('M d, Y') }}</td>
                    <td style="font-size:.83rem;">{{ \Carbon\Carbon::parse($leave->to_date)->format('M d, Y') }}</td>
                    <td>
                        <span style="font-weight:700;font-size:.85rem;">{{ $leave->days }}</span>
                        @if($leave->is_half_day) <span style="background:#dbeafe;color:#2563eb;padding:1px 6px;border-radius:4px;font-size:.65rem;font-weight:700;margin-left:3px;">Half</span> @endif
                    </td>
                    <td style="font-size:.82rem;color:#6b7280;max-width:160px;">
                        <span title="{{ $leave->reason }}">{{ Str::limit($leave->reason,35) }}</span>
                    </td>
                    <td><span class="spill spill-{{ $st }}">{{ ucfirst($st) }}</span></td>
                    <td style="font-size:.78rem;color:#9ca3af;">{{ $leave->created_at->format('M d') }}</td>
                    <td>
                        @if($leave->status === 'pending')
                        <div class="d-flex gap-1">
                            <button class="act-btn act-green" title="Approve" onclick="approveLeave({{ $leave->id }})"><i class="bi bi-check2"></i></button>
                            <button class="act-btn act-delete" title="Reject" onclick="rejectLeave({{ $leave->id }})"><i class="bi bi-x-lg"></i></button>
                        </div>
                        @else
                        <span style="font-size:.78rem;color:#9ca3af;">{{ $leave->approvedBy->name ?? '—' }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9">
                    <div class="empty-state"><i class="bi bi-calendar-minus"></i><p>No leave requests found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
    <div class="pagination-wrap">{{ $leaves->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Reject Leave</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <input type="hidden" name="action" value="reject">
                <div class="modal-body">
                    <label class="flabel">Reason <span class="req">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Provide rejection reason…" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function leaveAction(id, payload, onSuccess) {
    const data = new FormData();
    Object.entries(payload).forEach(([k, v]) => data.append(k, v));
    data.append('_token', document.querySelector('meta[name=csrf-token]').content);

    fetch(`/admin/attendance/leaves/${id}/approve`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) { APP.toast(res.message, 'success'); onSuccess(); }
        else APP.toast(res.message || 'Action failed.', 'error');
    })
    .catch(() => APP.toast('Something went wrong.', 'error'));
}

function approveLeave(id) {
    APP.confirm('Approve this leave request?', '', function() {
        leaveAction(id, { action: 'approve' }, () => setTimeout(() => location.reload(), 1000));
    });
}

function rejectLeave(id) {
    document.getElementById('rejectForm').dataset.leaveId = id;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = this.dataset.leaveId;
    const btn = this.querySelector('[type=submit]');
    const reason = this.querySelector('[name=rejection_reason]').value;
    btn.disabled = true;

    leaveAction(id, { action: 'reject', rejection_reason: reason }, () => {
        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
        setTimeout(() => location.reload(), 800);
    });
    btn.disabled = false;
});
</script>
@endpush
