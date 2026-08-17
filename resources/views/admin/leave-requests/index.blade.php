@extends('layouts.app')
@section('title', 'Leave Requests')

@section('content')

<div class="page-hero d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="page-hero-title">Leave Requests</h1>
        <p class="page-hero-sub mb-0">Review and action all employee leave requests</p>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card-glass p-4 text-center" style="border-top:4px solid #f59e0b;">
            <div style="font-size:2rem;font-weight:800;color:#f59e0b;line-height:1;">{{ $stats['pending'] }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-glass p-4 text-center" style="border-top:4px solid #16a34a;">
            <div style="font-size:2rem;font-weight:800;color:#16a34a;line-height:1;">{{ $stats['approved'] }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Approved</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-glass p-4 text-center" style="border-top:4px solid #dc2626;">
            <div style="font-size:2rem;font-weight:800;color:#dc2626;line-height:1;">{{ $stats['rejected'] }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Rejected</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card-glass p-4 text-center" style="border-top:4px solid #6366f1;">
            <div style="font-size:2rem;font-weight:800;color:#6366f1;line-height:1;">{{ $stats['this_month'] }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">This Month</div>
        </div>
    </div>
</div>

{{-- Filters --}}
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
        <div class="col-auto">
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach($leaveTypes as $lt)
                    <option value="{{ $lt }}" {{ request('type') === $lt ? 'selected' : '' }}>{{ ucfirst($lt) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="employee_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->user->name ?? '—' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From">
        </div>
        <div class="col-auto">
            <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary-grad px-3">Filter</button>
        </div>
        @if(request()->hasAny(['status','type','department_id','employee_id','from','to']))
        <div class="col-auto">
            <a href="{{ route('admin.leave-requests.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
        </div>
        @endif
    </form>
</div>

{{-- Bulk Action Bar --}}
<div id="bulkBar" class="d-none mb-2 d-flex align-items-center gap-2 p-2" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;">
    <span id="bulkCount" class="text-primary fw-semibold" style="font-size:.85rem;">0 selected</span>
    <button class="btn btn-sm btn-success ms-2" onclick="bulkAction('approve')"><i class="bi bi-check-lg me-1"></i>Approve Selected</button>
    <button class="btn btn-sm btn-danger" onclick="bulkAction('reject')"><i class="bi bi-x-lg me-1"></i>Reject Selected</button>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th style="width:36px;"><input type="checkbox" id="chkAll" class="form-check-input"></th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Period</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                @php
                    $statusColors = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'];
                    $sc = $statusColors[$leave->status] ?? 'secondary';
                @endphp
                <tr>
                    <td>
                        @if($leave->status === 'pending')
                        <input type="checkbox" class="form-check-input row-chk" value="{{ $leave->id }}">
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold" style="font-size:.87rem;color:#111827;">{{ $leave->employee->user->name ?? '—' }}</div>
                        <small class="text-muted">{{ $leave->employee->employee_id ?? '' }}</small>
                    </td>
                    <td style="font-size:.83rem;color:#6b7280;">{{ $leave->employee->department->name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-dark border" style="font-size:.75rem;">{{ ucfirst($leave->type) }}</span>
                    </td>
                    <td style="font-size:.83rem;white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($leave->from_date)->format('d M Y') }}
                        @if($leave->from_date != $leave->to_date)
                            &rarr; {{ \Carbon\Carbon::parse($leave->to_date)->format('d M Y') }}
                        @endif
                    </td>
                    <td style="font-size:.87rem;font-weight:700;color:#374151;">{{ $leave->days }}d</td>
                    <td style="font-size:.82rem;color:#6b7280;max-width:180px;">
                        <span title="{{ $leave->reason }}">{{ Str::limit($leave->reason, 50) }}</span>
                        @if($leave->rejection_reason)
                            <br><small class="text-danger"><i class="bi bi-chat-left-text me-1"></i>{{ Str::limit($leave->rejection_reason, 40) }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} border border-{{ $sc }}-subtle" style="font-size:.75rem;">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;color:#9ca3af;white-space:nowrap;">{{ $leave->created_at->format('d M Y') }}</td>
                    <td>
                        @if($leave->status === 'pending')
                        <div class="d-flex gap-1">
                            <button class="btn btn-xs btn-success" title="Approve" onclick="singleAction({{ $leave->id }}, 'approve')">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button class="btn btn-xs btn-danger" title="Reject" onclick="openRejectModal({{ $leave->id }})">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        @elseif($leave->approver)
                        <small class="text-muted" style="font-size:.77rem;">by {{ $leave->approver->name }}</small>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state"><i class="bi bi-calendar-x"></i><p>No leave requests found</p></div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $leaves->links() }}</div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                <textarea id="rejectionReason" class="form-control" rows="3" placeholder="Provide a reason..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnConfirmReject">Reject</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrf    = document.querySelector('meta[name=csrf-token]').content;
const baseUrl = "{{ url('admin/leave-requests') }}";
let rejectLeaveId = null;

// ── Checkbox select-all ───────────────────────────────────────────────
const chkAll = document.getElementById('chkAll');
const bulkBar = document.getElementById('bulkBar');
const bulkCount = document.getElementById('bulkCount');

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-chk:checked');
    if (checked.length > 0) {
        bulkBar.classList.remove('d-none');
        bulkCount.textContent = checked.length + ' selected';
    } else {
        bulkBar.classList.add('d-none');
    }
}

chkAll.addEventListener('change', function() {
    document.querySelectorAll('.row-chk').forEach(c => c.checked = this.checked);
    updateBulkBar();
});
document.querySelectorAll('.row-chk').forEach(c => c.addEventListener('change', updateBulkBar));

// ── Single action ─────────────────────────────────────────────────────
async function singleAction(id, action, reason = null) {
    try {
        const body = { action };
        if (reason) body.rejection_reason = reason;

        const res  = await fetch(baseUrl + '/' + id + '/action', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (data.success) {
            APP.toast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            APP.toast(data.message ?? 'Error', 'danger');
        }
    } catch (e) {
        APP.toast('Network error.', 'danger');
    }
}

// ── Reject modal ──────────────────────────────────────────────────────
function openRejectModal(id) {
    rejectLeaveId = id;
    document.getElementById('rejectionReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

document.getElementById('btnConfirmReject').addEventListener('click', function() {
    const reason = document.getElementById('rejectionReason').value.trim();
    if (!reason) { APP.toast('Please provide a rejection reason.', 'warning'); return; }
    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
    singleAction(rejectLeaveId, 'reject', reason);
});

// ── Bulk action ───────────────────────────────────────────────────────
async function bulkAction(action) {
    const ids = Array.from(document.querySelectorAll('.row-chk:checked')).map(c => c.value);
    if (!ids.length) return;

    if (!await APP.confirm(`${action === 'approve' ? 'Approve' : 'Reject'} ${ids.length} leave request(s)?`)) return;

    try {
        const res  = await fetch(baseUrl + '/bulk-action', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ leave_ids: ids, action }),
        });
        const data = await res.json();
        if (data.success) {
            APP.toast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            APP.toast(data.message ?? 'Error', 'danger');
        }
    } catch (e) {
        APP.toast('Network error.', 'danger');
    }
}
</script>
@endpush
