@extends('layouts.app')

@section('title', 'Stock Movement Audit Log')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-bold"><i class="bi bi-journal-text text-primary me-2"></i>Stock Movement Audit Log</h1>
            <p class="text-muted mb-0">Complete audit trail of every stock in, stock out, and stock adjustment transaction.</p>
        </div>
        <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Current Stock
        </a>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.stock.movements') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Inventory Item</label>
                    <select name="item_id" class="form-select">
                        <option value="">-- All Items --</option>
                        @foreach($items as $i)
                            <option value="{{ $i->id }}" {{ request('item_id') == $i->id ? 'selected' : '' }}>{{ $i->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Type</label>
                    <select name="type" class="form-select">
                        <option value="">-- All Types --</option>
                        <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Stock In</option>
                        <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Stock Out</option>
                        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Log Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th class="text-end">Quantity</th>
                        <th>Source / Purpose / Reason</th>
                        <th>Issued To / Vendor</th>
                        <th>Recorded By</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td>{{ $m->date->format('d-M-Y') }}</td>
                            <td class="fw-bold">{{ $m->inventoryItem?->name ?? 'Deleted Item' }}</td>
                            <td>
                                @if($m->type === 'in')
                                    <span class="badge bg-success"><i class="bi bi-arrow-down-left"></i> Stock In</span>
                                @elseif($m->type === 'out')
                                    <span class="badge bg-danger"><i class="bi bi-arrow-up-right"></i> Stock Out</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-sliders"></i> Adjustment</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                {{ number_format($m->quantity, 2) }} {{ $m->inventoryItem?->unit }}
                            </td>
                            <td>{{ $m->source_purpose ?? $m->reason ?? '—' }}</td>
                            <td>{{ $m->issued_to_or_vendor ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $m->recorder?->name ?? 'System' }}</span></td>
                            <td class="small text-muted">{{ $m->remarks ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No stock movements recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
