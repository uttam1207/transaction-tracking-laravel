@extends('layouts.app')

@section('title', 'Journal Entry — ' . $entry->entry_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">{{ $entry->entry_number }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.finance.journal.index') }}">Journal Entries</a></li>
                <li class="breadcrumb-item active">{{ $entry->entry_number }}</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            @if ($entry->status === 'draft')
            <button class="btn btn-success btn-sm" onclick="postEntry({{ $entry->id }})">
                <i class="bi bi-check-circle me-1"></i> Post Entry
            </button>
            @endif
            @if ($entry->status === 'posted')
            <button class="btn btn-warning btn-sm" onclick="reverseEntry({{ $entry->id }})">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reverse
            </button>
            @endif
            <a href="{{ route('admin.finance.journal.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Entry Details</span>
                    <span class="badge bg-{{ $entry->status_color }}">{{ ucfirst($entry->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3 pb-3 border-bottom">
                        <div class="col-sm-3"><div class="text-muted small">Entry Number</div><div class="fw-semibold">{{ $entry->entry_number }}</div></div>
                        <div class="col-sm-3"><div class="text-muted small">Date</div><div>{{ $entry->entry_date->format('d M Y') }}</div></div>
                        <div class="col-sm-3"><div class="text-muted small">Type</div><div><span class="badge bg-light text-dark border">{{ ucfirst($entry->type) }}</span></div></div>
                        <div class="col-sm-3"><div class="text-muted small">Period</div><div>{{ $entry->period?->name ?? '—' }}</div></div>
                        @if ($entry->reference)
                        <div class="col-sm-4"><div class="text-muted small">Reference</div><div>{{ $entry->reference }}</div></div>
                        @endif
                        @if ($entry->description)
                        <div class="col-12"><div class="text-muted small">Description</div><div>{{ $entry->description }}</div></div>
                        @endif
                    </div>

                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th>Description</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entry->lines as $line)
                            <tr>
                                <td>
                                    <span class="text-muted small">{{ $line->account->code }}</span>
                                    {{ $line->account->name }}
                                </td>
                                <td class="text-muted small">{{ $line->description ?? '—' }}</td>
                                <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit, 2) : '' }}</td>
                                <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit, 2) : '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-end">Totals</td>
                                <td class="text-end">{{ number_format($entry->total_debit, 2) }}</td>
                                <td class="text-end">{{ number_format($entry->total_credit, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    @if (!$entry->isBalanced())
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Unbalanced:</strong> Debits and credits do not match.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold">Audit</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Created by</dt>
                        <dd class="col-7">{{ $entry->createdBy?->name ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Created at</dt>
                        <dd class="col-7">{{ $entry->created_at->format('d M Y H:i') }}</dd>
                        @if ($entry->posted_at)
                        <dt class="col-5 text-muted">Posted by</dt>
                        <dd class="col-7">{{ $entry->postedBy?->name ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Posted at</dt>
                        <dd class="col-7">{{ $entry->posted_at->format('d M Y H:i') }}</dd>
                        @endif
                        @if ($entry->reversalOf)
                        <dt class="col-5 text-muted">Reversal of</dt>
                        <dd class="col-7"><a href="{{ route('admin.finance.journal.show', $entry->reversalOf) }}">{{ $entry->reversalOf->entry_number }}</a></dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function postEntry(id) {
    if (!confirm('Post this entry? It cannot be edited after posting.')) return;
    fetch(`/admin/finance/journal/${id}/post`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message);
    });
}
function reverseEntry(id) {
    if (!confirm('Create a reversal entry?')) return;
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
