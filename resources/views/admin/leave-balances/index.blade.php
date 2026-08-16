@extends('layouts.app')
@section('title', 'Leave Balances')
@section('breadcrumb')
    <li class="breadcrumb-item active">Leave Balances</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Leave Balances</h4>
            <p>Track and manage employee leave entitlements for {{ $year }}</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#bulkModal">
            <i class="bi bi-lightning-charge me-1"></i>Bulk Allocate
        </button>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="flabel">Year</label>
            <select name="year" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:110px;">
                @foreach(range(now()->year + 1, now()->year - 2) as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="flabel">Leave Type</label>
            <select name="leave_type_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:180px;">
                <option value="">All Types</option>
                @foreach($leaveTypes as $lt)
                <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ $lt->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="flabel">Department</label>
            <select name="department_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:170px;">
                <option value="">All Depts</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        @if(request()->hasAny(['year','leave_type_id','department_id']))
            <a href="{{ route('admin.leave-balances.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
        @endif
    </form>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">Employee Leave Balances — {{ $year }}</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $balances->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Leave Type</th>
                    <th>Allocated</th>
                    <th>Used</th>
                    <th>Carried Forward</th>
                    <th>Remaining</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($balances as $bal)
                @php
                    $remaining = max(0, $bal->allocated_days + $bal->carried_forward_days - $bal->used_days);
                    $usedPct   = $bal->allocated_days > 0 ? min(100, round(($bal->used_days / $bal->allocated_days) * 100)) : 0;
                @endphp
                <tr>
                    <td>
                        <div>
                            <div style="font-weight:700;font-size:.86rem;color:#111827;">{{ $bal->employee->user->name ?? '—' }}</div>
                            <div style="font-size:.75rem;color:#6b7280;">{{ $bal->employee->employee_id ?? '' }}</div>
                        </div>
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $bal->employee->department->name ?? '—' }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            @if($bal->leaveType->color ?? null)
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $bal->leaveType->color }};display:inline-block;flex-shrink:0;"></span>
                            @endif
                            <span style="font-size:.83rem;font-weight:600;">{{ $bal->leaveType->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td style="font-weight:600;color:#374151;">{{ number_format($bal->allocated_days, 1) }}</td>
                    <td>
                        <span style="color:{{ $usedPct >= 80 ? '#dc2626' : '#374151' }};font-weight:600;">{{ number_format($bal->used_days, 1) }}</span>
                    </td>
                    <td style="color:#374151;">{{ number_format($bal->carried_forward_days, 1) }}</td>
                    <td>
                        <span style="background:{{ $remaining > 0 ? '#dcfce7' : '#fee2e2' }};color:{{ $remaining > 0 ? '#16a34a' : '#dc2626' }};padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">
                            {{ number_format($remaining, 1) }}
                        </span>
                    </td>
                    <td>
                        <button class="act-btn act-edit" title="Adjust Balance"
                            onclick="openAdjust({{ $bal->id }}, '{{ addslashes($bal->employee->user->name ?? '') }}', '{{ addslashes($bal->leaveType->name ?? '') }}', {{ $bal->allocated_days }}, {{ $bal->carried_forward_days }})">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8">
                    <div class="empty-state"><i class="bi bi-calendar2-check"></i><p>No leave balances found. Use Bulk Allocate to create them.</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($balances->hasPages())
    <div class="pagination-wrap">{{ $balances->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Bulk Allocate Modal --}}
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Bulk Allocate Leave</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.leave-balances.bulk-allocate') }}">
                @csrf
                <div class="modal-body">
                    <p style="font-size:.85rem;color:#6b7280;margin-bottom:16px;">
                        Allocates leave balances for all active employees based on the leave type's configured max days.
                        Existing balances for the same year will be skipped.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Year <span class="req">*</span></label>
                            <select name="year" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach(range(now()->year + 1, now()->year - 1) as $y)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Leave Type <span class="req">*</span></label>
                            <select name="leave_type_id" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">— Select —</option>
                                @foreach($leaveTypes as $lt)
                                <option value="{{ $lt->id }}">{{ $lt->name }} ({{ $lt->max_days_per_year }}d)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">Allocate</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Adjust Balance Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="adjustTitle">Adjust Balance</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adjustForm" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <div class="modal-body">
                    <p id="adjustInfo" style="font-size:.84rem;color:#6b7280;margin-bottom:14px;"></p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Allocated Days</label>
                            <input type="number" name="allocated_days" id="adjAllocated" class="form-control" min="0" max="366" step="0.5" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Carried Forward Days</label>
                            <input type="number" name="carried_forward_days" id="adjCarryFwd" class="form-control" min="0" max="366" step="0.5" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAdjust(id, empName, ltName, allocated, carryFwd) {
    document.getElementById('adjustTitle').textContent = 'Adjust Balance';
    document.getElementById('adjustInfo').textContent = `${empName} — ${ltName}`;
    document.getElementById('adjustForm').action = `/admin/leave-balances/${id}`;
    document.getElementById('adjAllocated').value = allocated;
    document.getElementById('adjCarryFwd').value = carryFwd;
    new bootstrap.Modal(document.getElementById('adjustModal')).show();
}
</script>
@endpush
