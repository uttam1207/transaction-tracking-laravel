@extends('layouts.app')
@section('title', 'Machinery & Equipment Maintenance')

@section('breadcrumb')
    <li class="breadcrumb-item active">Maintenance</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Maintenance Tracking</h4>
            <p>Milking machines, generators, pumps, tractors, service history & breakdown logs</p>
        </div>
        <a href="{{ route('admin.maintenance.create') }}" class="btn btn-primary-grad btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>Log Maintenance
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-6">
        <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">
            <i class="bi bi-tools kpi-icon"></i>
            <div class="kpi-value">&#8377;{{ number_format($summary['total_cost'],0) }}</div>
            <div class="kpi-label">Maintenance Expenditure</div>
        </div>
    </div>
    <div class="col-6 col-md-6">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#b45309);">
            <i class="bi bi-bell-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['due_soon'] }}</div>
            <div class="kpi-label">Services Due (14 Days)</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.maintenance.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Machine</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;"><i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Machine name&#8230;" value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Type</label>
            <select name="maintenance_type" class="form-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach(['Preventive Schedule','Service History','Breakdown Log'] as $t)
                    <option value="{{ $t }}" @selected(request('maintenance_type')===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','maintenance_type']))
                <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
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
            <h6 class="mb-0 fw-bold"><i class="bi bi-gear-wide-connected me-2 text-primary"></i>Service History & Logs</h6>
            @if(request()->hasAny(['search','maintenance_type']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $logs->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Machine</th><th>Type</th><th>Service Date</th>
                    <th>Serviced By</th><th class="text-end">Cost</th><th>Next Due</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $m)
                    <tr>
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $m->machine_name }}</div>
                        </td>
                        <td>
                            @php
                                $mColor = match($m->maintenance_type) {
                                    'Preventive Schedule' => 'spill-success',
                                    'Service History'     => 'spill-info',
                                    default               => 'spill-danger',
                                };
                            @endphp
                            <span class="spill {{ $mColor }}">{{ $m->maintenance_type }}</span>
                        </td>
                        <td style="font-size:.82rem;">{{ $m->service_date?->format('d M Y') }}</td>
                        <td style="font-size:.82rem;">{{ $m->serviced_by ?? '&#8212;' }}</td>
                        <td class="text-end fw-bold" style="color:#dc2626;">&#8377;{{ number_format($m->cost,2) }}</td>
                        <td style="font-size:.82rem;">
                            @if($m->next_service_due)
                                @php $isDue = $m->next_service_due->lte(now()->addDays(14)); @endphp
                                <span class="{{ $isDue ? 'text-danger fw-bold' : '' }}">
                                    {{ $m->next_service_due->format('d M Y') }}
                                    @if($isDue)<i class="bi bi-exclamation-circle ms-1 text-danger"></i>@endif
                                </span>
                            @else
                                &#8212;
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.maintenance.show',$m) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.maintenance.edit',$m) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state"><i class="bi bi-tools"></i><p>No maintenance logs recorded</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $logs->firstItem() }}</strong>&#8211;<strong>{{ $logs->lastItem() }}</strong> of <strong>{{ $logs->total() }}</strong> records
            </div>
            {{ $logs->links() }}
        </div>
    @endif
</div>

@endsection
