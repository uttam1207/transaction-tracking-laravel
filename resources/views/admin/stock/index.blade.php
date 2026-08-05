@extends('layouts.app')
@section('title', 'Stock Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Stock</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Stock Management</h4>
            <p>Monitor inventory balances, record stock in/out and track all movements</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.stock.in') }}" class="btn btn-sm px-3" style="background:#059669;color:#fff;border-radius:9px;font-size:.83rem;font-weight:600;border:none;">
                <i class="bi bi-arrow-down-circle me-1"></i>Stock In
            </a>
            <a href="{{ route('admin.stock.out') }}" class="btn btn-sm px-3" style="background:#dc2626;color:#fff;border-radius:9px;font-size:.83rem;font-weight:600;border:none;">
                <i class="bi bi-arrow-up-circle me-1"></i>Stock Out
            </a>
            <a href="{{ route('admin.stock.adjustment') }}" class="btn btn-sm px-3" style="background:#d97706;color:#fff;border-radius:9px;font-size:.83rem;font-weight:600;border:none;">
                <i class="bi bi-sliders me-1"></i>Adjustment
            </a>
            <a href="{{ route('admin.stock.movements') }}" class="btn btn-sm btn-outline-secondary px-3" style="border-radius:9px;font-size:.83rem;">
                <i class="bi bi-journal-text me-1"></i>Audit Log
            </a>
            <a href="{{ route('admin.stock.export.pdf') }}" class="btn btn-sm btn-outline-secondary px-3" style="border-radius:9px;font-size:.83rem;">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3" style="border-radius:9px;font-size:.83rem;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear me-1"></i>Manage
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:12px;border:1px solid #e5e7eb;min-width:175px;">
                    <li><a class="dropdown-item py-2" href="{{ route('admin.stock-items.index') }}"><i class="bi bi-box-seam me-2 text-primary"></i>Stock Items</a></li>
                    <li><a class="dropdown-item py-2" href="{{ route('admin.stock-categories.index') }}"><i class="bi bi-tag me-2 text-success"></i>Categories</a></li>
                    <li><a class="dropdown-item py-2" href="{{ route('admin.stock-types.index') }}"><i class="bi bi-layers me-2 text-warning"></i>Item Types</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
            <i class="bi bi-box-seam kpi-icon"></i>
            <div class="kpi-value">{{ $summary['total_items'] }}</div>
            <div class="kpi-label">Total Stock Items</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-exclamation-triangle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['low_stock'] }}</div>
            <div class="kpi-label">Low Stock Items</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">
            <i class="bi bi-x-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['out_of_stock'] }}</div>
            <div class="kpi-label">Out of Stock</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-arrow-down-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ number_format($summary['received_this_month'], 0) }}</div>
            <div class="kpi-label">Received This Month</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.stock.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Item</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;"><i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Item name&#8230;" value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Category</label>
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->name }}" @selected(request('category')===$cat->name)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="optimal" @selected(request('status')==='optimal')>Optimal</option>
                <option value="low_stock" @selected(request('status')==='low_stock')>Low Stock</option>
                <option value="out_of_stock" @selected(request('status')==='out_of_stock')>Out of Stock</option>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','category','status']))
                <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Stock Table --}}
<div class="card-glass">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-columns-reverse me-2 text-primary"></i>Current Stock Balances</h6>
            @if(request()->hasAny(['search','category','status']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-3">
            <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $items->count() }} items</span>
            <a href="{{ route('admin.stock-items.index') }}" class="btn btn-sm btn-outline-primary px-3" style="border-radius:8px;font-size:.78rem;">
                <i class="bi bi-box-seam me-1"></i>Manage Items
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Unit</th>
                    <th class="text-end">Available</th>
                    <th class="text-end">Min Stock</th>
                    <th>Status</th>
                    <th style="width:100px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    @php
                        $statusColor = match($item->stock_status) {
                            'Optimal'      => 'spill-success',
                            'Low Stock'    => 'spill-warning',
                            'Out of Stock' => 'spill-danger',
                            default        => 'spill-secondary',
                        };
                        $qtyColor = match($item->stock_status) {
                            'Out of Stock' => '#dc2626',
                            'Low Stock'    => '#d97706',
                            default        => '#059669',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $item->name }}</div>
                            @if($item->description)
                                <div style="font-size:.72rem;color:#9ca3af;">{{ Str::limit($item->description, 55) }}</div>
                            @endif
                        </td>
                        <td><span class="spill spill-primary" style="font-size:.72rem;">{{ $item->category }}</span></td>
                        <td><span style="font-size:.8rem;color:#6b7280;">{{ $item->item_type ?? '—' }}</span></td>
                        <td><code style="font-size:.78rem;background:#f5f7fa;border:1px solid #e5e7eb;border-radius:5px;padding:2px 6px;">{{ $item->unit }}</code></td>
                        <td class="text-end">
                            <span class="fw-bold" style="color:{{ $qtyColor }};font-size:.9rem;">{{ number_format($item->available_quantity, 2) }}</span>
                        </td>
                        <td class="text-end" style="font-size:.82rem;color:#6b7280;">{{ number_format($item->min_stock, 2) }}</td>
                        <td><span class="spill {{ $statusColor }}" style="font-size:.72rem;">{{ $item->stock_status }}</span></td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.stock.in', ['item_id' => $item->id]) }}" class="act-btn act-view" title="Stock In"><i class="bi bi-plus-circle"></i></a>
                            <a href="{{ route('admin.stock.out', ['item_id' => $item->id]) }}" class="act-btn act-delete" title="Stock Out"><i class="bi bi-dash-circle"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="bi bi-boxes"></i>
                            <p>No stock items found. <a href="{{ route('admin.stock-items.index') }}">Add items</a> first.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection