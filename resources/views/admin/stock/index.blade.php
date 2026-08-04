@extends('layouts.app')

@section('title', 'Stock Management - Current Stock')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-bold"><i class="bi bi-boxes text-primary me-2"></i>Stock Management</h1>
            <p class="text-muted mb-0">Monitor quantity movement and current available balances across ASDairy farm.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.stock.in') }}" class="btn btn-success">
                <i class="bi bi-arrow-down-left-circle me-1"></i> Stock In
            </a>
            <a href="{{ route('admin.stock.out') }}" class="btn btn-danger">
                <i class="bi bi-arrow-up-right-circle me-1"></i> Stock Out
            </a>
            <a href="{{ route('admin.stock.adjustment') }}" class="btn btn-warning text-dark">
                <i class="bi bi-sliders me-1"></i> Stock Adjustment
            </a>
            <a href="{{ route('admin.stock.movements') }}" class="btn btn-outline-secondary">
                <i class="bi bi-journal-text me-1"></i> Audit Movements
            </a>
            <a href="{{ route('admin.stock.export.pdf') }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary me-3">
                            <i class="bi bi-box-seam fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Stock Items</div>
                            <div class="fs-4 fw-bold">{{ number_format($summary['total_items']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning me-3">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Low Stock Items</div>
                            <div class="fs-4 fw-bold text-warning">{{ number_format($summary['low_stock']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger me-3">
                            <i class="bi bi-x-circle fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Out of Stock Items</div>
                            <div class="fs-4 fw-bold text-danger">{{ number_format($summary['out_of_stock']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success me-3">
                            <i class="bi bi-arrow-down-circle fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Received This Month</div>
                            <div class="fs-4 fw-bold text-success">{{ number_format($summary['received_this_month'], 1) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.stock.index') }}" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search item name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">-- All Stock Status --</option>
                        <option value="optimal" {{ request('status') == 'optimal' ? 'selected' : '' }}>Optimal</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
                    <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Current Stock Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent py-3">
            <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-columns-reverse me-2 text-primary"></i>Current Stock Balances</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th class="text-end">Available Quantity</th>
                        <th class="text-end">Minimum Stock (Reorder)</th>
                        <th>Stock Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="fw-bold text-dark">{{ $item->name }}</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $item->category }}</span></td>
                            <td><code>{{ $item->unit }}</code></td>
                            <td class="text-end fw-bold text-lg">
                                {{ number_format($item->available_quantity, 2) }} {{ $item->unit }}
                            </td>
                            <td class="text-end text-muted">{{ number_format($item->min_stock, 2) }} {{ $item->unit }}</td>
                            <td>
                                <span class="badge {{ $item->stock_status_badge }} px-3 py-2">
                                    {{ $item->stock_status }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.stock.in', ['item_id' => $item->id]) }}" class="btn btn-outline-success" title="Stock In">
                                        <i class="bi bi-plus-circle"></i> In
                                    </a>
                                    <a href="{{ route('admin.stock.out', ['item_id' => $item->id]) }}" class="btn btn-outline-danger" title="Stock Out">
                                        <i class="bi bi-dash-circle"></i> Out
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No inventory items found. Run ASDairy Seeder or add items.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
