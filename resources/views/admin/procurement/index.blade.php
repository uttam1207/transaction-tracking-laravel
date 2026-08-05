@extends('layouts.app')
@section('title', 'Procurement & Purchase Orders')

@section('breadcrumb')
    <li class="breadcrumb-item active">Procurement</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Procurement & Vendor Management</h4>
            <p>Purchase orders (PO), vendor management, quotations, GRN, bills & payments</p>
        </div>
        <a href="{{ route('admin.procurement.create') }}" class="btn btn-primary-grad btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>Create PO
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#4f46e5);">
            <i class="bi bi-person-check-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['total_vendors'] }}</div>
            <div class="kpi-label">Registered Vendors</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#0d9488);">
            <i class="bi bi-box-seam-fill kpi-icon"></i>
            <div class="kpi-value" style="font-size:1.4rem;">&#8377;{{ number_format($summary['total_po_value'],0) }}</div>
            <div class="kpi-label">Procurement Value</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#dc2626);">
            <i class="bi bi-hourglass-split kpi-icon"></i>
            <div class="kpi-value">{{ $summary['pending_pos'] }}</div>
            <div class="kpi-label">Pending Orders</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.procurement.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Order</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;"><i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i></span>
                <input type="text" name="search" class="form-control" placeholder="PO number or vendor name&#8230;" value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['Draft','Sent','Received','Paid'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.procurement.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Table --}}
<div class="card-glass">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Purchase Orders Log</h6>
            @if(request()->hasAny(['search','status']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $orders->total() }} orders</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>PO Number</th><th>Vendor</th><th>Order Date</th>
                    <th class="text-end">Amount</th><th>Status</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $o->po_number }}</div>
                        </td>
                        <td class="fw-semibold" style="font-size:.83rem;">{{ $o->vendor?->name }}</td>
                        <td style="font-size:.82rem;">{{ $o->order_date?->format('d M Y') }}</td>
                        <td class="text-end fw-bold text-success">&#8377;{{ number_format($o->total_amount,2) }}</td>
                        <td>
                            @php
                                $oColor = match($o->status) {
                                    'Paid'     => 'spill-success',
                                    'Received' => 'spill-info',
                                    'Sent'     => 'spill-warning',
                                    default    => 'spill-secondary',
                                };
                            @endphp
                            <span class="spill {{ $oColor }}">{{ $o->status }}</span>
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.procurement.show',$o) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.procurement.edit',$o) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state"><i class="bi bi-cart-check"></i><p>No purchase orders logged</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $orders->firstItem() }}</strong>&#8211;<strong>{{ $orders->lastItem() }}</strong> of <strong>{{ $orders->total() }}</strong> orders
            </div>
            {{ $orders->links() }}
        </div>
    @endif
</div>

@endsection
