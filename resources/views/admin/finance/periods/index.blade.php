@extends('layouts.app')

@section('title', 'Financial Periods')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Financial Periods</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Financial Periods</li>
            </ol></nav>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
            <i class="bi bi-plus-lg me-1"></i> New Period
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periods as $period)
                        <tr>
                            <td class="fw-semibold">{{ $period->name }}</td>
                            <td><span class="badge bg-light text-dark border text-capitalize">{{ $period->type }}</span></td>
                            <td>{{ $period->start_date->format('d M Y') }}</td>
                            <td>{{ $period->end_date->format('d M Y') }}</td>
                            <td>
                                @php $color = match($period->status) { 'open' => 'success', 'closed' => 'secondary', 'locked' => 'danger', default => 'dark' }; @endphp
                                <span class="badge bg-{{ $color }}">{{ ucfirst($period->status) }}</span>
                            </td>
                            <td class="small text-muted">{{ $period->createdBy?->name ?? '—' }}</td>
                            <td class="text-end">
                                @if ($period->status !== 'locked')
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Status</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach (['open', 'closed', 'locked'] as $s)
                                        @if ($s !== $period->status)
                                        <li><a class="dropdown-item" href="#" onclick="updatePeriod({{ $period->id }}, '{{ $s }}')">Mark {{ ucfirst($s) }}</a></li>
                                        @endif
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No financial periods defined.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($periods->hasPages())
            <div class="px-3 py-2">{{ $periods->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- Add Period Modal --}}
<div class="modal fade" id="addPeriodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">New Financial Period</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" id="p_name" class="form-control" placeholder="e.g. FY 2026-27, April 2026">
                </div>
                <div class="mb-3">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select id="p_type" class="form-select">
                        <option value="month">Month</option>
                        <option value="quarter">Quarter</option>
                        <option value="year">Year</option>
                    </select>
                </div>
                <div class="row g-2">
                    <div class="col">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" id="p_start" class="form-control">
                    </div>
                    <div class="col">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" id="p_end" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="createPeriod()">Create Period</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function createPeriod() {
    fetch('{{ route("admin.finance.periods.store") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: document.getElementById('p_name').value,
            type: document.getElementById('p_type').value,
            start_date: document.getElementById('p_start').value,
            end_date: document.getElementById('p_end').value,
        })
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message ?? 'Error');
    });
}

function updatePeriod(id, status) {
    if (!confirm(`Mark period as ${status}?`)) return;
    fetch(`/admin/finance/periods/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ _method: 'PATCH', status })
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message);
    });
}
</script>
@endpush
@endsection
