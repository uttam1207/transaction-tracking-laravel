@extends('layouts.app')
@section('title', 'Shift Management')
@section('breadcrumb')
    <li class="breadcrumb-item active">Shifts</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Shift Management</h4>
        <p class="text-muted small mb-0">Assign and manage employee shift timings</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkShiftModal">
        <i class="bi bi-calendar-range me-1"></i>Bulk Assign Shift
    </button>
</div>

{{-- Shift Type Cards --}}
<div class="row g-3 mb-4">
    @foreach($shifts as $key => $shift)
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="fw-semibold">{{ $shift['label'] }}</div>
                    <span class="badge bg-primary bg-opacity-15 text-primary">{{ $shiftCounts[$key] ?? 0 }}</span>
                </div>
                <div class="text-muted small">
                    @if($shift['start'])
                        {{ $shift['start'] }} – {{ $shift['end'] }}
                    @else
                        Flexible hours
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="shift" class="form-select form-select-sm">
                    <option value="">All Shifts</option>
                    @foreach($shifts as $key => $shift)
                        <option value="{{ $key }}" {{ request('shift') === $key ? 'selected' : '' }}>{{ $shift['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
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
                        <td><input type="checkbox" class="form-check-input row-check" value="{{ $emp->id }}"></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $emp->user?->avatar_url }}" class="rounded-circle" width="28" height="28" alt="">
                                <div>
                                    <div class="fw-semibold">{{ $emp->full_name }}</div>
                                    <div class="text-muted" style="font-size:.7rem">{{ $emp->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $emp->department?->name ?? '—' }}</td>
                        <td>{{ $emp->designation }}</td>
                        <td>
                            <span class="badge bg-info bg-opacity-15 text-info">{{ $shiftLabel }}</span>
                        </td>
                        <td class="text-muted">
                            @if(!empty($shift['start']))
                                {{ $shift['start'] }} – {{ $shift['end'] }}
                            @else
                                Flexible
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-xs btn-outline-primary py-0 px-2"
                                onclick="openEditModal({{ $emp->id }}, '{{ $shiftType }}', '{{ $shift['start'] ?? '' }}', '{{ $shift['end'] ?? '' }}')"
                                title="Edit Shift">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No employees found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
        <div class="px-3 py-2">{{ $employees->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

{{-- Edit Shift Modal --}}
<div class="modal fade" id="editShiftModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" id="editShiftForm" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header">
                <h6 class="modal-title">Edit Shift</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small">Shift Type</label>
                    <select name="shift_type" id="editShiftType" class="form-select form-select-sm">
                        @foreach($shifts as $key => $shift)
                        <option value="{{ $key }}">{{ $shift['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col">
                        <label class="form-label small">Start</label>
                        <input type="time" name="start_time" id="editStartTime" class="form-control form-control-sm">
                    </div>
                    <div class="col">
                        <label class="form-label small">End</label>
                        <input type="time" name="end_time" id="editEndTime" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Assign Modal --}}
<div class="modal fade" id="bulkShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.shifts.bulk-assign') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Bulk Assign Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Shift</label>
                    <select name="shift_type" class="form-select" required>
                        @foreach($shifts as $key => $shift)
                        <option value="{{ $key }}">{{ $shift['label'] }}
                            @if($shift['start']) ({{ $shift['start'] }} – {{ $shift['end'] }}) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div id="bulkSelectedInfo" class="alert alert-info small d-none"></div>
                <input type="hidden" name="employee_ids" id="bulkIds">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign Shift</button>
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
    info.textContent = ids.length > 0 ? `${ids.length} employee(s) selected` : 'No employees selected — will apply to all visible.';
    info.classList.remove('d-none');
});
</script>
@endpush
