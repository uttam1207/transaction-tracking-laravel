@extends('layouts.app')

@section('title', 'Stock Transfers')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Stock Transfers</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
                <li class="breadcrumb-item active">Transfers</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.warehouses.transfer.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> New Transfer
        </a>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">All Warehouses</option>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach (['draft','in_transit','completed','cancelled'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-outline-primary">Filter</button>
                    <a href="{{ route('admin.warehouses.transfers') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
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
                            <th>Transfer #</th>
                            <th>Date</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transfers as $t)
                        <tr>
                            <td class="fw-semibold">{{ $t->transfer_number }}</td>
                            <td>{{ $t->transfer_date->format('d M Y') }}</td>
                            <td>{{ $t->fromWarehouse?->name ?? '—' }}</td>
                            <td>{{ $t->toWarehouse?->name ?? '—' }}</td>
                            <td>
                                @php $color = match($t->status) { 'draft'=>'secondary','in_transit'=>'info','completed'=>'success','cancelled'=>'danger',default=>'dark' }; @endphp
                                <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
                            </td>
                            <td class="small text-muted">{{ $t->createdBy?->name ?? '—' }}</td>
                            <td class="text-end">
                                @if (in_array($t->status, ['draft', 'in_transit']))
                                <button class="btn btn-sm btn-outline-success" onclick="completeTransfer({{ $t->id }})">
                                    <i class="bi bi-check-lg"></i> Complete
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No transfers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transfers->hasPages())
            <div class="px-3 py-2">{{ $transfers->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function completeTransfer(id) {
    if (!confirm('Mark transfer as completed and update stock levels?')) return;
    fetch(`/admin/warehouses/transfers/${id}/complete`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}
</script>
@endpush
@endsection
