@extends('layouts.app')
@section('title', 'Shift Management')
@section('breadcrumb')
    <li class="breadcrumb-item active">Shifts</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Shift Management</h4>
            <p>Assign and manage employee shift timings</p>
        </div>
        <button class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1.5px solid rgba(255,255,255,.4);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);"
            data-bs-toggle="modal" data-bs-target="#bulkShiftModal" id="bulkAssignBtn">
            <i class="bi bi-calendar-range me-1"></i>Bulk Assign Shift
        </button>
    </div>
</div>

{{-- Shift Type Cards --}}
<div class="row g-3 mb-3">
    @foreach($shifts as $key => $shift)
    <div class="col-sm-6 col-lg-3">
        <div class="info-card text-center" style="padding:16px;border-top:4px solid #6366f1;">
            <div style="font-size:1.6rem;font-weight:800;color:#6366f1;line-height:1;">{{ $shiftCounts[$key] ?? 0 }}</div>
            <div style="font-size:.85rem;font-weight:600;color:#111827;margin-top:4px;">{{ $shift['label'] }}</div>
            <div style="font-size:.76rem;color:#9ca3af;margin-top:2px;">
                @if($shift['start']) {{ $shift['start'] }} – {{ $shift['end'] }} @else Flexible hours @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="flabel">Department</label>
            <select name="department_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:200px;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="flabel">Shift</label>
            <select name="shift" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:180px;">
                <option value="">All Shifts</option>
                @foreach($shifts as $key => $shift)
                    <option value="{{ $key }}" {{ request('shift') === $key ? 'selected' : '' }}>{{ $shift['label'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th style="width:36px;">
                        <input type="checkbox" id="selectAll" class="form-check-input" style="width:15px;height:15px;cursor:pointer;">
                    </th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Current Shift</th>
                    <th>Hours</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                @php
                    $shift = $emp->shift_timing ?? [];
                    $shiftType = $shift['type'] ?? 'day';
                    $shiftLabel = $shift['label'] ?? 'Day Shift';
                @endphp
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input row-check" value="{{ $emp->id }}"
                            style="width:15px;height:15px;cursor:pointer;">
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $emp->user?->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($emp->full_name).'&size=28&background=6366f1&color=fff' }}"
                                class="rounded-circle" style="width:28px;height:28px;object-fit:cover;flex-shrink:0;" alt="">
                            <div>
                                <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ $emp->full_name }}</div>
                                <div style="font-size:.73rem;color:#9ca3af;">{{ $emp->employee_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.84rem;color:#374151;">{{ $emp->department?->name ?? '—' }}</td>
                    <td style="font-size:.83rem;color:#6b7280;">{{ $emp->designation }}</td>
                    <td>
                        <span class="spill spill-info" style="font-size:.72rem;">{{ $shiftLabel }}</span>
                    </td>
                    <td style="font-size:.83rem;color:#6b7280;">
                        @if(!empty($shift['start']))
                            {{ $shift['start'] }} – {{ $shift['end'] }}
                        @else
                            Flexible
                        @endif
                    </td>
                    <td>
                        <button class="act-btn act-edit" title="Edit Shift"
                            onclick="openEditModal({{ $emp->id }}, '{{ $shiftType }}', '{{ $shift['start'] ?? '' }}', '{{ $shift['end'] ?? '' }}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-people"></i><p>No employees found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="pagination-wrap">{{ $employees->withQueryString()->links() }}</div>
    @endif
</div>

{{-- Edit Shift Modal --}}
<div class="modal fade" id="editShiftModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form method="POST" id="editShiftForm" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Edit Shift</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="flabel">Shift Type</label>
                    <select name="shift_type" id="editShiftType" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        @foreach($shifts as $key => $shift)
                        <option value="{{ $key }}">{{ $shift['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col">
                        <label class="flabel">Start</label>
                        <input type="time" name="start_time" id="editStartTime" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                    <div class="col">
                        <label class="flabel">End</label>
                        <input type="time" name="end_time" id="editEndTime" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;">
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

{{-- Bulk Assign Modal --}}
<div class="modal fade" id="bulkShiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.shifts.bulk-assign') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-calendar-range me-2"></i>Bulk Assign Shift</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="flabel">Shift <span class="req">*</span></label>
                    <select name="shift_type" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        @foreach($shifts as $key => $shift)
                        <option value="{{ $key }}">{{ $shift['label'] }}
                            @if($shift['start']) ({{ $shift['start'] }} – {{ $shift['end'] }}) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div id="bulkSelectedInfo" class="d-none" style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:9px;padding:10px 14px;font-size:.83rem;color:#1d4ed8;"></div>
                <input type="hidden" name="employee_ids" id="bulkIds">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary-grad px-4">Assign Shift</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(id, shiftType, start, end) {
    document.getElementById('editShiftForm').action = `/admin/shifts/${id}`;
    document.getElementById('editShiftType').value = shiftType;
    document.getElementById('editStartTime').value = start;
    document.getElementById('editEndTime').value = end;
    new bootstrap.Modal(document.getElementById('editShiftModal')).show();
}
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
document.getElementById('bulkShiftModal').addEventListener('show.bs.modal', function () {
    const ids = [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    document.getElementById('bulkIds').value = ids.join(',');
    const info = document.getElementById('bulkSelectedInfo');
    info.textContent = ids.length > 0
        ? `${ids.length} employee(s) selected for bulk assignment.`
        : 'No employees selected — will apply to all visible employees.';
    info.classList.remove('d-none');
});
</script>
@endpush
