@extends('layouts.app')
@section('title', 'Farm & Land Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Farm</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Farm & Land Management</h4>
            <p>Land records, Napier grass plantation, crop yield, fertilizer, diesel & water consumption</p>
        </div>
        <a href="{{ route('admin.farm.create') }}" class="btn btn-primary-grad btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>Log Activity
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#65a30d,#16a34a);">
            <i class="bi bi-tree-fill kpi-icon"></i>
            <div class="kpi-value">{{ number_format($summary['total_yield'],0) }} <small style="font-size:.5em;font-weight:600;">kg</small></div>
            <div class="kpi-label">Total Crop Yield</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#ea580c,#dc2626);">
            <i class="bi bi-fuel-pump-fill kpi-icon"></i>
            <div class="kpi-value">{{ number_format($summary['total_diesel'],1) }} <small style="font-size:.5em;font-weight:600;">L</small></div>
            <div class="kpi-label">Diesel Used</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0891b2,#0284c7);">
            <i class="bi bi-water kpi-icon"></i>
            <div class="kpi-value">{{ number_format($summary['total_water'],0) }} <small style="font-size:.5em;font-weight:600;">L</small></div>
            <div class="kpi-label">Water Usage</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#92400e,#78350f);">
            <i class="bi bi-map-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['active_plots'] }}</div>
            <div class="kpi-label">Farm Plots</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.farm.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Plot</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;"><i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Plot name or crop type&#8230;" value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Crop Type</label>
            <select name="crop_type" class="form-select" onchange="this.form.submit()">
                <option value="">All Crops</option>
                @foreach($cropTypes as $ct)
                    <option value="{{ $ct }}" @selected(request('crop_type')===$ct)>{{ $ct }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','crop_type']))
                <a href="{{ route('admin.farm.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
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
            <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Farm Activity Logs</h6>
            @if(request()->hasAny(['search','crop_type']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $records->total() }} plots</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Plot Name</th><th>Crop</th><th>Plantation</th>
                    <th class="text-end">Yield (kg)</th><th class="text-end">Diesel (L)</th><th class="text-end">Water (L)</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $f)
                    <tr>
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $f->plot_name }}</div>
                        </td>
                        <td><span class="spill spill-success">{{ $f->crop_type }}</span></td>
                        <td style="font-size:.82rem;">{{ $f->plantation_date?->format('d M Y') ?? '&#8212;' }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format($f->yield_kg,1) }}</td>
                        <td class="text-end" style="font-size:.83rem;">{{ number_format($f->diesel_liters,1) }}</td>
                        <td class="text-end" style="font-size:.83rem;">{{ number_format($f->water_usage_liters,0) }}</td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.farm.show',$f) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.farm.edit',$f) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state"><i class="bi bi-tree"></i><p>No farm records logged yet</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $records->firstItem() }}</strong>&#8211;<strong>{{ $records->lastItem() }}</strong> of <strong>{{ $records->total() }}</strong> plots
            </div>
            {{ $records->links() }}
        </div>
    @endif
</div>

@endsection
