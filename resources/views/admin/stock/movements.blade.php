@extends('layouts.app')
@section('title', 'Stock Movement Audit Log')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Stock</a></li>
    <li class="breadcrumb-item active">Audit Log</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Stock Movement Audit Log</h4>
            <p>Complete audit trail of every stock in, stock out and adjustment transaction</p>
        </div>
        <a href="{{ route('admin.stock.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Stock
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.stock.movements') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Item</label>
            <select name="item_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Items</option>
                @foreach($items as $i)
                    <option value="{{ $i->id }}" @selected(request('item_id')==$i->id)>{{ $i->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Type</label>
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="in" @selected(request('type')==='in')>Stock In</option>
                <option value="out" @selected(request('type')==='out')>Stock Out</option>
                <option value="adjustment" @selected(request('type')==='adjustment')>Adjustment</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Date From</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Date To</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-6 col-md-3 d-flex gap-2 align-self-end">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['item_id','type','date_from','date_to']))
                <a href="{{ route('admin.stock.movements') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Movements Table --}}
<div class="card-glass">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>All Stock Movements</h6>
            @if(request()->hasAny(['item_id','type','date_from','date_to']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $movements->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th class="text-end">Quantity</th>
                    <th>Purpose / Reason</th>
                    <th>Issued To / Vendor</th>
                    <th>Recorded By</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                    <tr>
                        <td style="font-size:.82rem;">{{ $m->date->format('d M Y') }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:.87rem;color:var(--primary);">{{ $m->inventoryItem?->name ?? 'Deleted Item' }}</div>
                            @if($m->inventoryItem)
                                <div style="font-size:.72rem;color:#9ca3af;">{{ $m->inventoryItem->category }}</div>
                            @endif
                        </td>
                        <td>
                            @if($m->type === 'in')
                                <span class="spill spill-success" style="font-size:.72rem;"><i class="bi bi-arrow-down-left me-1"></i>Stock In</span>
                            @elseif($m->type === 'out')
                                <span class="spill spill-danger" style="font-size:.72rem;"><i class="bi bi-arrow-up-right me-1"></i>Stock Out</span>
                            @else
                                <span class="spill spill-warning" style="font-size:.72rem;"><i class="bi bi-sliders me-1"></i>Adjustment</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold" style="font-size:.88rem;">
                            {{ number_format($m->quantity, 2) }}
                            <span style="font-size:.72rem;color:#9ca3af;">{{ $m->inventoryItem?->unit }}</span>
                        </td>
                        <td style="font-size:.82rem;">{{ $m->source_purpose ?? $m->reason ?? '—' }}</td>
                        <td style="font-size:.82rem;color:#6b7280;">{{ $m->issued_to_or_vendor ?? '—' }}</td>
                        <td>
                            <span class="spill spill-secondary" style="font-size:.72rem;">{{ $m->recorder?->name ?? 'System' }}</span>
                        </td>
                        <td style="font-size:.78rem;color:#9ca3af;max-width:150px;">{{ $m->remarks ? Str::limit($m->remarks, 40) : '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="bi bi-journal-text"></i>
                            <p>No stock movements recorded yet.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
        <div class="px-4 py-3 border-top">
            {{ $movements->links() }}
        </div>
    @endif
</div>

@endsection