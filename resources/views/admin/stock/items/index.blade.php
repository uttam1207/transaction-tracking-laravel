@extends('layouts.app')
@section('title', 'Manage Stock Items')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Stock</a></li>
    <li class="breadcrumb-item active">Items</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Manage Stock Items</h4>
            <p>Add, edit and delete inventory items &mdash; assign categories and types</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.stock-items.create') }}" class="btn btn-primary-grad btn-sm px-4">
                <i class="bi bi-plus-lg me-1"></i>Add New Item
            </a>
            <a href="{{ route('admin.stock.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back to Stock
            </a>
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
<form method="GET" action="{{ route('admin.stock-items.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search</label>
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
        <div class="col-6 col-md-2 d-flex gap-2 align-self-end">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','category']))
                <a href="{{ route('admin.stock-items.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Items Table --}}
<div class="card-glass">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2 text-primary"></i>All Stock Items</h6>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $items->count() }} items</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Unit</th>
                    <th class="text-end">Min Stock</th>
                    <th class="text-center">Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td style="color:#9ca3af;font-size:.8rem;">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:.87rem;">{{ $item->name }}</div>
                            @if($item->description)
                                <div style="font-size:.72rem;color:#9ca3af;">{{ Str::limit($item->description, 50) }}</div>
                            @endif
                        </td>
                        <td><span class="spill spill-primary" style="font-size:.72rem;">{{ $item->category }}</span></td>
                        <td><span style="font-size:.8rem;color:#6b7280;">{{ $item->item_type ?? '—' }}</span></td>
                        <td><code style="font-size:.78rem;background:#f5f7fa;border:1px solid #e5e7eb;border-radius:5px;padding:2px 6px;">{{ $item->unit }}</code></td>
                        <td class="text-end" style="font-size:.82rem;color:#6b7280;">{{ number_format($item->min_stock, 2) }}</td>
                        <td class="text-center">
                            @if($item->is_active)
                                <span class="spill spill-success" style="font-size:.72rem;">Active</span>
                            @else
                                <span class="spill spill-secondary" style="font-size:.72rem;">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.stock-items.edit', $item) }}" class="act-btn act-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.stock-items.destroy', $item) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete \'{{ addslashes($item->name) }}\'? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="act-btn act-delete" title="Delete">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="bi bi-box-seam"></i>
                            <p>No stock items yet. <a href="{{ route('admin.stock-items.create') }}">Add the first item</a>.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection