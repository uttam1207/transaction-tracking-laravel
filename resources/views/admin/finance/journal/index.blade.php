@extends('layouts.app')

@section('title', 'Journal Entries')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Journal Entries</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Journal Entries</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.finance.journal.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Entry
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-secondary">{{ $stats['draft'] }}</div>
                <div class="text-muted small">Draft Entries</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-success">{{ $stats['posted'] }}</div>
                <div class="text-muted small">Posted Entries</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Entry #, description…" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach (['draft','posted','reversed','cancelled'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach ($types as $t)
                            <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="period_id" class="form-select form-select-sm">
                        <option value="">All Periods</option>
                        @foreach ($periods as $p)
                            <option value="{{ $p->id }}" @selected(request('period_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto d-flex gap-1">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-sm btn-outline-primary">Filter</button>
                    <a href="{{ route('admin.finance.journal.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Entry #</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Period</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entries as $entry)
                        <tr>
                            <td><a href="{{ route('admin.finance.journal.show', $entry) }}" class="text-primary fw-semibold">{{ $entry->entry_number }}</a></td>
                            <td>{{ $entry->entry_date->format('d M Y') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst($entry->type) }}</span></td>
                            <td class="text-muted small">{{ Str::limit($entry->description, 40) }}</td>
                            <td class="small text-muted">{{ $entry->period?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($entry->total_debit, 2) }}</td>
                            <td class="text-end">{{ number_format($entry->total_credit, 2) }}</td>
                            <td><span class="badge bg-{{ $entry->status_color }}">{{ ucfirst($entry->status) }}</span></td>
                            <td class="small text-muted">{{ $entry->createdBy?->name ?? '—' }}</td>
                            <td class="text-end d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.finance.journal.show', $entry) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                @if ($entry->status === 'draft')
                                <button class="btn btn-sm btn-outline-success" onclick="postEntry({{ $entry->id }})"><i class="bi bi-check-lg"></i></button>
                                @endif
                                @if ($entry->status === 'posted')
                                <button class="btn btn-sm btn-outline-warning" onclick="reverseEntry({{ $entry->id }})"><i class="bi bi-arrow-counterclockwise"></i></button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">No journal entries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($entries->hasPages())
            <div class="px-3 py-2">{{ $entries->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function postEntry(id) {
    if (!confirm('Post this journal entry? It cannot be edited after posting.')) return;
    fetch(`/admin/finance/journal/${id}/post`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message);
    });
}

function reverseEntry(id) {
    if (!confirm('Create a reversal entry for this journal entry?')) return;
    fetch(`/admin/finance/journal/${id}/reverse`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message);
    });
}
</script>
@endpush
@endsection
