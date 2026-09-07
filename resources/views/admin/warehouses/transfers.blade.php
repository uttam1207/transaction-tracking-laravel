@extends('layouts.app')
@section('title', 'Stock Transfers')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Inventory</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
    <li class="breadcrumb-item active">Transfers</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Stock Transfers</h4>
            <p>Track inventory movements between warehouses</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.warehouses.transfer.create') }}" class="btn btn-primary-grad btn-sm px-4">
                <i class="bi bi-plus-lg me-1"></i> New Transfer
            </a>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-glass mb-3 px-4 py-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <select name="warehouse_id" class="form-select">
                <option value="">All Warehouses</option>
                @foreach ($warehouses as $wh)
                    <option value="{{ $wh->id }}" @selected(request('warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach (['draft','in_transit','completed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary-grad btn-sm px-4">Filter</button>
            <a href="{{ route('admin.warehouses.transfers') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
        </div>
    </form>
</div>

<div class="card-glass overflow-hidden">
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
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
                        <button class="act-btn text-success" onclick="completeTransfer({{ $t->id }})">
                            <i class="bi bi-check-lg"></i>
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

@push('scripts')
<script>
function completeTransfer(id) {
    if (!confirm('Mark transfer as completed and update stock levels?')) return;
    fetch(`/admin/warehouses/transfers/${id}/complete`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else APP.toast(d.message, 'error'); });
}
</script>
@endpush

@endsection
