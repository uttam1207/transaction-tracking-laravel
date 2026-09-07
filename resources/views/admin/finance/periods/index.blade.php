@extends('layouts.app')
@section('title', 'Financial Periods')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.journal.index') }}">Finance</a></li>
    <li class="breadcrumb-item active">Financial Periods</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Financial Periods</h4>
            <p>Manage fiscal periods for accounting</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary-grad btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
                <i class="bi bi-plus-lg me-1"></i> New Period
            </button>
        </div>
    </div>
</div>

<div class="card-glass overflow-hidden">
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
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
                        @if ($period->status === 'open')
                        <button class="act-btn me-1" onclick="closePeriod({{ $period->id }}, '{{ addslashes($period->name) }}')" title="Close & Recalculate">
                            <i class="bi bi-lock"></i>
                        </button>
                        @endif
                        @if ($period->status !== 'locked')
                        <div class="dropdown d-inline-block">
                            <button class="act-btn dropdown-toggle" data-bs-toggle="dropdown" title="Change Status">
                                <i class="bi bi-three-dots"></i>
                            </button>
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
                <button type="button" class="btn btn-primary-grad btn-sm px-4" onclick="createPeriod()">Create Period</button>
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
        else APP.toast(d.message ?? 'Error', 'error');
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
        else APP.toast(d.message, 'error');
    });
}

function closePeriod(id, name) {
    if (!confirm(`Close period "${name}"?\n\nThis will recalculate all ledger balances and mark the period as Closed.`)) return;
    fetch(`/admin/finance/periods/${id}/close`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => {
        if (d.success) { APP.toast(d.message, 'success'); setTimeout(() => location.reload(), 800); }
        else APP.toast(d.message, 'error');
    });
}
</script>
@endpush
@endsection
