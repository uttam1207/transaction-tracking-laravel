@extends('layouts.app')
@section('title', 'Milk Production')

@section('breadcrumb')
    <li class="breadcrumb-item active">Milk</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Milk Production Management</h4>
            <p>Daily morning &amp; evening milk production, Fat, SNF, CLR and rejection tracking</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success bg-opacity-20 text-success px-3 py-2" style="font-size:.82rem;">
                <i class="bi bi-calendar2-check me-1"></i>{{ now()->format('d M Y') }}
            </span>
            <a href="{{ route('admin.milk.create') }}" class="btn btn-primary-grad btn-sm px-4">
                <i class="bi bi-plus-lg me-1"></i>Add Entry
            </a>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#0d9488);">
            <i class="bi bi-droplet-fill kpi-icon"></i>
            <div class="kpi-value">{{ number_format($todayTotal,1) }} <small style="font-size:.55em;font-weight:600;">L</small></div>
            <div class="kpi-label">Total Today</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-sunrise-fill kpi-icon"></i>
            <div class="kpi-value">{{ number_format($morningTotal,1) }} <small style="font-size:.55em;font-weight:600;">L</small></div>
            <div class="kpi-label">Morning Shift</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
            <i class="bi bi-moon-fill kpi-icon"></i>
            <div class="kpi-value">{{ number_format($eveningTotal,1) }} <small style="font-size:.55em;font-weight:600;">L</small></div>
            <div class="kpi-label">Evening Shift</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0891b2,#0284c7);">
            <i class="bi bi-graph-up kpi-icon"></i>
            <div class="kpi-value">{{ number_format($avgFat,1) }} <small style="font-size:.55em;font-weight:600;">%</small></div>
            <div class="kpi-label">Avg Fat</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.milk.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Date</label>
            <input type="date" name="date" class="form-control" value="{{ request('date', now()->toDateString()) }}">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Shift</label>
            <select name="shift" class="form-select" onchange="this.form.submit()">
                <option value="">All Shifts</option>
                <option value="Morning" @selected(request('shift')==='Morning')>Morning</option>
                <option value="Evening" @selected(request('shift')==='Evening')>Evening</option>
            </select>
        </div>
        <div class="col-6 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['shift']) || request('date') !== now()->toDateString())
                <a href="{{ route('admin.milk.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
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
        <h6 class="mb-0 fw-bold"><i class="bi bi-list-stars me-2 text-primary"></i>Production Records</h6>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $entries->total() }} entries</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Date</th><th>Shift</th><th>Animal / Source</th>
                    <th class="text-end">Qty (L)</th><th class="text-end">Fat</th><th class="text-end">SNF</th><th class="text-end">CLR</th>
                    <th>Quality</th><th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $e)
                    <tr>
                        <td style="font-size:.82rem;color:#374151;">{{ $e->date?->format('d M Y') }}</td>
                        <td>
                            <span class="spill {{ $e->shift==='Morning' ? 'spill-warning' : 'spill-primary' }}">
                                <i class="bi bi-{{ $e->shift==='Morning' ? 'sunrise' : 'moon' }} me-1"></i>{{ $e->shift }}
                            </span>
                        </td>
                        <td class="fw-semibold" style="font-size:.83rem;">
                            {{ $e->animal ? $e->animal->tag_number.' — '.$e->animal->name : 'Batch Shed Total' }}
                        </td>
                        <td class="text-end fw-bold text-success">{{ number_format($e->quantity_liters,1) }}</td>
                        <td class="text-end" style="font-size:.83rem;">{{ number_format($e->fat_percentage,2) }}</td>
                        <td class="text-end" style="font-size:.83rem;">{{ number_format($e->snf_percentage,2) }}</td>
                        <td class="text-end" style="font-size:.83rem;">{{ $e->clr_value ? number_format($e->clr_value,2) : '—' }}</td>
                        <td><span class="spill spill-success">{{ $e->quality_rating }}</span></td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.milk.show',$e) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.milk.edit',$e) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state"><i class="bi bi-droplet"></i><p>No milk entries for this date</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($entries->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $entries->firstItem() }}</strong>–<strong>{{ $entries->lastItem() }}</strong> of <strong>{{ $entries->total() }}</strong> entries
            </div>
            {{ $entries->links() }}
        </div>
    @endif
</div>

@endsection
