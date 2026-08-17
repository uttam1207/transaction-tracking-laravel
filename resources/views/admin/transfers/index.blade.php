@extends('layouts.app')

@section('title', 'Employee Transfers')

@section('content')
<div class="page-hero d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-hero-title">Employee Transfers</h1>
        <p class="page-hero-sub mb-0">Manage department and branch transfers</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTransferModal">
        <i class="bi bi-plus-lg me-1"></i> New Transfer Request
    </button>
</div>

{{-- Filter bar --}}
<div class="filter-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        @if(request('status'))
        <div class="col-auto">
            <a href="{{ route('admin.transfers.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
        </div>
        @endif
    </form>
</div>

<div class="table-card">
    <table class="modern-table table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>From</th>
                <th>To</th>
                <th>Effective Date</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfers as $transfer)
            <tr>
                <td class="text-muted">{{ $transfers->firstItem() + $loop->index }}</td>
                <td>
                    <div class="fw-semibold">{{ $transfer->employee->user->name ?? '—' }}</div>
                    <small class="text-muted">{{ $transfer->employee->employee_id ?? '' }}</small>
                </td>
                <td>
                    <div>{{ $transfer->fromDepartment->name ?? '—' }}</div>
                    @if($transfer->fromBranch)<small class="text-muted">{{ $transfer->fromBranch->name }}</small>@endif
                </td>
                <td>
                    <div>{{ $transfer->toDepartment->name ?? '—' }}</div>
                    @if($transfer->toBranch)<small class="text-muted">{{ $transfer->toBranch->name }}</small>@endif
                </td>
                <td class="text-nowrap">{{ \Carbon\Carbon::parse($transfer->effective_date)->format('d M Y') }}</td>
                <td>
                    <span class="badge bg-{{ $transfer->status === 'approved' ? 'success' : ($transfer->status === 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucfirst($transfer->status) }}
                    </span>
                </td>
                <td>{{ Str::limit($transfer->reason, 40) }}</td>
                <td>
                    @if($transfer->status === 'pending')
                    <div class="d-flex gap-1">
                        <button class="btn btn-xs btn-success" onclick="approveTransfer({{ $transfer->id }})">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-xs btn-danger" onclick="openRejectModal({{ $transfer->id }})">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    @elseif($transfer->approvedBy)
                    <small class="text-muted">by {{ $transfer->approvedBy->name ?? '—' }}</small>
                    @else
                    —
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No transfer requests found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $transfers->links() }}</div>
</div>

{{-- Create Transfer Modal --}}
<div class="modal fade" id="createTransferModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="createTransferForm" method="POST" action="{{ route('admin.transfers.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Transfer Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name ?? '—' }} ({{ $emp->employee_id ?? $emp->id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transfer To Department</label>
                        <select name="to_department_id" class="form-select">
                            <option value="">No change</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transfer To Branch</label>
                        <select name="to_branch_id" class="form-select">
                            <option value="">No change</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Effective Date <span class="text-danger">*</span></label>
                        <input type="date" name="effective_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form id="rejectForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const approveBase = "{{ url('admin/transfers') }}";

async function approveTransfer(id) {
    if (!await APP.confirm('Approve this transfer? This will update the employee\'s department and branch.')) return;
    const res  = await fetch(approveBase + '/' + id + '/approve', {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    });
    const data = await res.json();
    if (data.success) { APP.toast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
    else APP.toast(data.message ?? 'Error', 'danger');
}

function openRejectModal(id) {
    document.getElementById('rejectForm').action = approveBase + '/' + id + '/reject';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

['createTransferForm','rejectForm'].forEach(id => {
    document.getElementById(id).addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd  = new FormData(this);
        const res = await fetch(this.action, { method: 'POST', headers: {'X-CSRF-TOKEN': fd.get('_token'), 'Accept': 'application/json'}, body: fd });
        const data = await res.json();
        bootstrap.Modal.getInstance(document.querySelector('.modal.show')).hide();
        if (data.success) { APP.toast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
        else APP.toast(data.message ?? 'Error', 'danger');
    });
});
</script>
@endpush
