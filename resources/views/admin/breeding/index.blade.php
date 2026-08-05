@extends('layouts.app')
@section('title', 'Breeding Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Breeding</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Breeding Management</h4>
            <p>Heat detection, Artificial Insemination (AI), bull semen inventory &amp; pregnancy checks</p>
        </div>
        <a href="{{ route('admin.breeding.create') }}" class="btn btn-primary-grad btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>Log AI Event
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#4f46e5);">
            <i class="bi bi-arrow-repeat kpi-icon"></i>
            <div class="kpi-value">{{ $summary['inseminated'] }}</div>
            <div class="kpi-label">AI Done (Pending)</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-heart-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['pregnant'] }}</div>
            <div class="kpi-label">Confirmed Pregnant</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
            <i class="bi bi-calendar-check-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['expected_calving_month'] }}</div>
            <div class="kpi-label">Calving This Month</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">
            <i class="bi bi-exclamation-triangle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['repeat_breeders'] }}</div>
            <div class="kpi-label">Repeat Breeders</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.breeding.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Animal</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;"><i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Tag No. or Animal Name&#8230;" value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['Heat Detected','AI Done','Confirmed Pregnant','Calved','Repeat Breeder'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.breeding.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
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
            <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Breeding Records</h6>
            @if(request()->hasAny(['search','status']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $records->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Animal</th><th>Heat Date</th><th>AI Date</th>
                    <th>Semen Code</th><th>Expected Calving</th><th>Status</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                    @php
                        $statusColor = match($r->status) {
                            'Confirmed Pregnant' => 'spill-primary',
                            'AI Done'            => 'spill-info',
                            'Heat Detected'      => 'spill-warning',
                            'Calved'             => 'spill-success',
                            default              => 'spill-danger',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $r->animal?->tag_number }}</div>
                            <div style="font-size:.73rem;color:#9ca3af;">{{ $r->animal?->name }}</div>
                        </td>
                        <td style="font-size:.82rem;">{{ $r->heat_date?->format('d M Y') }}</td>
                        <td style="font-size:.82rem;">{{ $r->ai_date ? $r->ai_date->format('d M Y') : '&#8212;' }}</td>
                        <td><code style="font-size:.78rem;background:#f5f7fa;border:1px solid #e5e7eb;border-radius:5px;padding:2px 6px;">{{ $r->bull_semen_code ?? '&#8212;' }}</code></td>
                        <td style="font-size:.82rem;">{{ $r->expected_calving_date ? $r->expected_calving_date->format('d M Y') : '&#8212;' }}</td>
                        <td><span class="spill {{ $statusColor }}">{{ $r->status }}</span></td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.breeding.show',$r) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.breeding.edit',$r) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state"><i class="bi bi-heart-pulse"></i><p>No breeding records found</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $records->firstItem() }}</strong>&#8211;<strong>{{ $records->lastItem() }}</strong> of <strong>{{ $records->total() }}</strong> records
            </div>
            {{ $records->links() }}
        </div>
    @endif
</div>

@endsection