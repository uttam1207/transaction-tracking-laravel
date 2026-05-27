@extends('layouts.app')

@section('title', 'Leave Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.attendance.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Attendance
        </a>
        <h4 class="mb-0 fw-bold mt-1">Leave Requests</h4>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-warning">{{ $leaves->where('status', 'pending')->count() }}</div>
            <div class="text-muted">Pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-success">{{ $leaves->where('status', 'approved')->count() }}</div>
            <div class="text-muted">Approved</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-danger">{{ $leaves->where('status', 'rejected')->count() }}</div>
            <div class="text-muted">Rejected</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-primary">{{ $leaves->total() }}</div>
            <div class="text-muted">Total</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $leave->employee->full_name ?? 'Unknown' }}</div>
                            <small class="text-muted">{{ $leave->employee->employee_id ?? '' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ ucwords(str_replace('_', ' ', $leave->type)) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($leave->from_date)->format('M d, Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($leave->to_date)->format('M d, Y') }}</td>
                        <td>
                            {{ $leave->days }}
                            @if($leave->is_half_day)
                                <span class="badge bg-info ms-1">Half</span>
                            @endif
                        </td>
                        <td>
                            <span data-bs-toggle="tooltip" title="{{ $leave->reason }}">
                                {{ Str::limit($leave->reason, 30) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                                $st = $leave->status ?? 'pending';
                            @endphp
                            <span class="badge bg-{{ $statusColors[$st] ?? 'secondary' }}">
                                {{ ucfirst($st) }}
                            </span>
                        </td>
                        <td><small>{{ $leave->created_at->format('M d') }}</small></td>
                        <td>
                            @if($leave->status === 'pending')
                            <div class="btn-group btn-group-sm">
                                <form action="{{ route('admin.attendance.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                                <button class="btn btn-danger btn-sm" title="Reject"
                                    onclick="rejectLeave({{ $leave->id }})">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            @else
                            <small class="text-muted">
                                {{ $leave->approvedBy->name ?? '—' }}
                            </small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-minus fs-1 d-block mb-2"></i>
                            No leave requests found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($leaves->hasPages())
    <div class="card-footer bg-transparent">
        {{ $leaves->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div class="modal-body">
                    <label class="form-label">Rejection Reason</label>
                    <textarea name="rejection_reason" class="form-control" rows="3" required
                        placeholder="Please provide a reason for rejection..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Leave</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function rejectLeave(id) {
    document.getElementById('rejectForm').action = `/admin/attendance/leaves/${id}/approve`;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endpush
