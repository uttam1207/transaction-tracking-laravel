@extends('layouts.app')
@section('title', 'Franchise Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Franchise</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Franchise Management</h4>
            <p>Franchise applications, agreements, investment tracking, milk collection & royalty fees</p>
        </div>
        <a href="{{ route('admin.franchise.create') }}" class="btn btn-primary-grad btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>Register Franchise
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0891b2,#4f46e5);">
            <i class="bi bi-shop-window kpi-icon"></i>
            <div class="kpi-value">{{ $summary['total_active'] }}</div>
            <div class="kpi-label">Active Franchises</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-bank kpi-icon"></i>
            <div class="kpi-value" style="font-size:1.4rem;">&#8377;{{ number_format($summary['total_investment'],0) }}</div>
            <div class="kpi-label">Investment Capital</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#0891b2);">
            <i class="bi bi-droplet-fill kpi-icon"></i>
            <div class="kpi-value">{{ number_format($summary['total_milk_collected'],0) }} <small style="font-size:.5em;font-weight:600;">L</small></div>
            <div class="kpi-label">Milk via Outlets</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.franchise.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Franchise</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;"><i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Code, owner name or location&#8230;" value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['Active','Inactive','Suspended'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.franchise.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
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
            <h6 class="mb-0 fw-bold"><i class="bi bi-shop-window me-2 text-primary"></i>Franchise Outlets</h6>
            @if(request()->hasAny(['search','status']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $franchises->total() }} outlets</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Code</th><th>Owner</th><th>Location</th>
                    <th class="text-end">Investment</th><th class="text-end">Royalty</th><th>Status</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($franchises as $f)
                    <tr>
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $f->franchise_code }}</div>
                        </td>
                        <td class="fw-semibold" style="font-size:.83rem;">{{ $f->owner_name }}</td>
                        <td style="font-size:.82rem;">{{ $f->location }}</td>
                        <td class="text-end fw-bold text-success">&#8377;{{ number_format($f->investment_amount,0) }}</td>
                        <td class="text-end" style="font-size:.83rem;">{{ number_format($f->royalty_percentage,1) }}%</td>
                        <td>
                            @php $fColor = $f->status === 'Active' ? 'spill-success' : ($f->status === 'Suspended' ? 'spill-danger' : 'spill-secondary'); @endphp
                            <span class="spill {{ $fColor }}">{{ $f->status }}</span>
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.franchise.show',$f) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.franchise.edit',$f) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state"><i class="bi bi-shop"></i><p>No franchises registered</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($franchises->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $franchises->firstItem() }}</strong>&#8211;<strong>{{ $franchises->lastItem() }}</strong> of <strong>{{ $franchises->total() }}</strong> outlets
            </div>
            {{ $franchises->links() }}
        </div>
    @endif
</div>

@endsection
