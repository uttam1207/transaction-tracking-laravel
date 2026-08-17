@extends('layouts.app')

@section('title', 'Employee Lifecycle Events')

@section('content')
<div class="page-hero d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-hero-title">Lifecycle Events</h1>
        <p class="page-hero-sub mb-0">Track employee journey from hire to exit</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEventModal">
        <i class="bi bi-plus-lg me-1"></i> Log Event
    </button>
</div>

{{-- Filters --}}
<div class="filter-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <select name="employee_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->user->name ?? '—' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="event_type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Events</option>
                @foreach($eventTypes as $key => $label)
                    <option value="{{ $key }}" {{ request('event_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @if(request()->anyFilled(['employee_id','event_type']))
        <div class="col-auto">
            <a href="{{ route('admin.employee-lifecycle.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
        </div>
        @endif
    </form>
</div>

<div class="table-card">
    <table class="modern-table table mb-0">
        <thead>
            <tr>
                <th>Date</th>
                <th>Employee</th>
                <th>Event</th>
                <th>Description</th>
                <th>Triggered By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
            <tr>
                <td class="text-muted text-nowrap">{{ $event->event_date->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('admin.employee-lifecycle.show', $event->employee_id) }}" class="fw-semibold text-decoration-none">
                        {{ $event->employee->user->name ?? '—' }}
                    </a>
                    <br><small class="text-muted">{{ $event->employee->employee_id ?? '' }}</small>
                </td>
                <td>
                    @php
                        $clsMap = [
                            'hired'       => 'success', 'onboarding_started' => 'info',
                            'probation_started' => 'info', 'probation_ended'  => 'success',
                            'confirmed'   => 'success', 'promoted'            => 'primary',
                            'transferred' => 'primary', 'resigned'            => 'warning',
                            'terminated'  => 'danger',  'exit_interview'      => 'warning',
                            'offboarding' => 'secondary','separated'          => 'secondary',
                            'reinstated'  => 'info',    'suspended'           => 'danger',
                            'warning_issued' => 'warning','contract_renewed'  => 'success',
                        ];
                        $cls = $clsMap[$event->event_type] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $cls }}">{{ $event->label }}</span>
                </td>
                <td>{{ Str::limit($event->description, 60) }}</td>
                <td>{{ $event->triggeredBy->name ?? '—' }}</td>
                <td>
                    <a href="{{ route('admin.employee-lifecycle.show', $event->employee_id) }}" class="btn btn-xs btn-outline-secondary">
                        <i class="bi bi-person-lines-fill"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No events recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $events->links() }}</div>
</div>

{{-- Add Event Modal --}}
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="addEventForm" method="POST" action="{{ route('admin.employee-lifecycle.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Lifecycle Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select employee...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name ?? '—' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event Type <span class="text-danger">*</span></label>
                        <select name="event_type" class="form-select" required>
                            <option value="">Select type...</option>
                            @foreach($eventTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Event Date <span class="text-danger">*</span></label>
                        <input type="date" name="event_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Log Event</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('addEventForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd  = new FormData(this);
    const res = await fetch(this.action, { method: 'POST', headers: {'X-CSRF-TOKEN': fd.get('_token'), 'Accept': 'application/json'}, body: fd });
    const data = await res.json();
    bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
    if (data.success) { APP.toast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
    else APP.toast(data.message ?? 'Error', 'danger');
});
</script>
@endpush
