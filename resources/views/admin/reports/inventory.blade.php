@extends('layouts.app')
@section('title', 'Inventory & Stock Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.center') }}">Reports</a></li>
    <li class="breadcrumb-item active">Inventory Report</li>
@endsection

@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#0f766e,#0d9488,#2dd4bf);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Module 17 — Reports</div>
            <h4 style="color:#fff;">Inventory & Stock Report</h4>
            <p style="color:rgba(255,255,255,.75);">Stock levels, alerts, expiry tracking, movements summary</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.stock.export.pdf') }}" class="btn btn-sm btn-light px-3">
                <i class="bi bi-file-pdf me-1"></i>Export PDF
            </a>
            <a href="{{ route('admin.stock.export.excel') }}" class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('admin.reports.center') }}" class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-grid-3x3-gap me-1"></i>All Reports
            </a>
        </div>
    </div>
</div>

@php
    use App\Models\InventoryCategory;
    $categories = InventoryCategory::orderBy('name')->get();
@endphp

{{-- Filter Bar --}}
<div class="card-glass p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Search Item</label>
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Item name…" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Category</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->name }}" @selected(request('category')===$cat->name)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="optimal" @selected(request('status')==='optimal')>Optimal</option>
                <option value="low" @selected(request('status')==='low')>Low Stock</option>
                <option value="out" @selected(request('status')==='out')>Out of Stock</option>
                <option value="expiring" @selected(request('status')==='expiring')>Expiring Soon</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary-grad btn-sm w-100">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </div>
    </form>
</div>

@php
    use App\Models\InventoryItem;
    use App\Models\StockMovement;

    $itemsQuery = InventoryItem::query();
    if (request('search')) $itemsQuery->where('name', 'like', '%'.request('search').'%');
    if (request('category')) $itemsQuery->where('category', request('category'));

    $allItems = $itemsQuery->get();

    // Apply status filter in PHP
    $items = match(request('status')) {
        'low'      => $allItems->filter(fn($i) => $i->stock_status === 'Low Stock'),
        'out'      => $allItems->filter(fn($i) => $i->stock_status === 'Out of Stock'),
        'expiring' => $allItems->filter(fn($i) => $i->is_expiring_soon || $i->is_expired),
        default    => $allItems,
    };

    $totalItems    = $allItems->count();
    $lowStock      = $allItems->filter(fn($i) => $i->stock_status === 'Low Stock')->count();
    $outOfStock    = $allItems->filter(fn($i) => $i->stock_status === 'Out of Stock')->count();
    $expiringSoon  = $allItems->filter(fn($i) => $i->is_expiring_soon)->count();
    $expired       = $allItems->filter(fn($i) => $i->is_expired)->count();

    // Stock value (quantity × unit cost if available, otherwise just quantity)
    // Recent stock movements (last 30 days)
    $recentIn  = StockMovement::where('type', 'in') ->where('created_at', '>=', now()->subDays(30))->sum('quantity');
    $recentOut = StockMovement::where('type', 'out')->where('created_at', '>=', now()->subDays(30))->sum('quantity');

    $byCategory = $items->groupBy('category');
@endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
            <i class="bi bi-boxes kpi-icon"></i>
            <div class="kpi-value">{{ $totalItems }}</div>
            <div class="kpi-label">Total Items</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-check-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $totalItems - $lowStock - $outOfStock }}</div>
            <div class="kpi-label">Optimal Stock</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-exclamation-triangle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $lowStock }}</div>
            <div class="kpi-label">Low Stock</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">
            <i class="bi bi-x-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $outOfStock }}</div>
            <div class="kpi-label">Out of Stock</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#9333ea);">
            <i class="bi bi-clock-history kpi-icon"></i>
            <div class="kpi-value">{{ $expiringSoon }}</div>
            <div class="kpi-label">Expiring Soon</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#6b7280,#374151);">
            <i class="bi bi-slash-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $expired }}</div>
            <div class="kpi-label">Expired</div>
        </div>
    </div>
</div>

{{-- Stock Movement Summary (30-day) --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card-glass p-4">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#9ca3af;letter-spacing:.06em;">Stock In (last 30 days)</div>
            <div class="fw-bold mt-1" style="font-size:1.8rem;color:#059669;letter-spacing:-1px;">
                +{{ number_format($recentIn, 0) }} <small style="font-size:.75rem;font-weight:400;color:#6b7280;">units</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-glass p-4">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#9ca3af;letter-spacing:.06em;">Stock Out (last 30 days)</div>
            <div class="fw-bold mt-1" style="font-size:1.8rem;color:#dc2626;letter-spacing:-1px;">
                -{{ number_format($recentOut, 0) }} <small style="font-size:.75rem;font-weight:400;color:#6b7280;">units</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-glass p-4">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#9ca3af;letter-spacing:.06em;">Net Movement</div>
            @php $net = $recentIn - $recentOut; @endphp
            <div class="fw-bold mt-1" style="font-size:1.8rem;color:{{ $net >= 0 ? '#059669' : '#dc2626' }};letter-spacing:-1px;">
                {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 0) }} <small style="font-size:.75rem;font-weight:400;color:#6b7280;">units</small>
            </div>
        </div>
    </div>
</div>

{{-- Inventory Table --}}
<div class="card-glass overflow-hidden">
    <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
        <span class="fw-bold">
            <i class="bi bi-list-columns-reverse me-2 text-teal"></i>
            Stock Items ({{ $items->count() }})
            @if(request()->hasAny(['search','category','status']))
                <span class="badge ms-2" style="background:#e0f2fe;color:#0284c7;font-size:.7rem;font-weight:600;">Filtered</span>
            @endif
        </span>
        <a href="{{ route('admin.stock.in') }}" class="btn btn-sm btn-outline-success px-3">
            <i class="bi bi-plus-lg me-1"></i>Stock In
        </a>
    </div>
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th class="text-end">Available</th>
                    <th class="text-end">Min Stock</th>
                    <th>Expiry</th>
                    <th>Status</th>
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
                        <div class="fw-semibold" style="font-size:.87rem;color:var(--primary);">{{ $item->name }}</div>
                        @if($item->description)
                            <div style="font-size:.72rem;color:#9ca3af;">{{ Str::limit($item->description, 55) }}</div>
                        @endif
                    </td>
                    <td><span class="spill spill-primary" style="font-size:.72rem;">{{ $item->category }}</span></td>
                    <td><code style="font-size:.78rem;background:#f5f7fa;border:1px solid #e5e7eb;border-radius:5px;padding:2px 6px;">{{ $item->unit }}</code></td>
                    <td class="text-end">
                        <span class="fw-bold" style="color:{{ $qtyColor }};font-size:.9rem;">{{ number_format($item->available_quantity, 2) }}</span>
                    </td>
                    <td class="text-end" style="font-size:.82rem;color:#6b7280;">{{ number_format($item->min_stock, 2) }}</td>
                    <td style="font-size:.78rem;">
                        @if($item->expiry_date)
                            @if($item->is_expired)
                                <span class="spill spill-danger" style="font-size:.7rem;">Expired</span>
                            @elseif($item->is_expiring_soon)
                                <span style="color:#d97706;font-weight:600;">{{ $item->expiry_date->format('d M Y') }}<br><small style="color:#f59e0b;">{{ $item->days_to_expiry }}d left</small></span>
                            @else
                                <span style="color:#6b7280;">{{ $item->expiry_date->format('d M Y') }}</span>
                            @endif
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                    <td><span class="spill {{ $statusColor }}" style="font-size:.72rem;">{{ $item->stock_status }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="bi bi-boxes"></i>
                        <p>No items match the current filters.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
