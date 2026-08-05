@extends('layouts.app')
@section('title', 'Health & Veterinary Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Health</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Health & Veterinary Management</h4>
            <p>Vaccination schedules, medicine usage, vet doctor visits, body temperature & treatment history</p>
        </div>
        <a href="{{ route('admin.health.create') }}" class="btn btn-primary-grad btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>Log Health Event
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#9f1239);">
            <i class="bi bi-thermometer-high kpi-icon"></i>
            <div class="kpi-value">{{ $summary['sick_animals'] }}</div>
            <div class="kpi-label">Sick Animals</div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-capsule-pill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['under_treatment'] }}</div>
            <div class="kpi-label">Under Treatment</div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kpi-card" style="background:linear-gradient(135deg,#16a34a,#059669);">
            <i class="bi bi-shield-fill-check kpi-icon"></i>
            <div class="kpi-value">{{ $summary['recent_vaccinations'] }}</div>
            <div class="kpi-label">Vaccinations This Month</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.health.index') }}">
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
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Record Type</label>
            <select name="record_type" class="form-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach(['Vaccination','Treatment','Deworming','Injury','Routine Check'] as $t)
                    <option value="{{ $t }}" @selected(request('record_type')===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','record_type']))
                <a href="{{ route('admin.health.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
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
            <h6 class="mb-0 fw-bold"><i class="bi bi-journal-medical me-2 text-primary"></i>Health History</h6>
            @if(request()->hasAny(['search','record_type']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $records->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Date</th><th>Animal</th><th>Type</th>
                    <th>Symptoms / Vaccine</th><th>Doctor</th><th class="text-end">Cost</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $h)
                    <tr>
                        <td style="font-size:.82rem;color:#374151;">{{ $h->date?->format('d M Y') }}</td>
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $h->animal?->tag_number }}</div>
                            <div style="font-size:.73rem;color:#9ca3af;">{{ $h->animal?->name }}</div>
                        </td>
                        <td>
                            @php
                                $tColor = match($h->record_type) {
                                    'Vaccination'   => 'spill-success',
                                    'Treatment'     => 'spill-danger',
                                    'Deworming'     => 'spill-info',
                                    'Injury'        => 'spill-warning',
                                    default         => 'spill-secondary',
                                };
                            @endphp
                            <span class="spill {{ $tColor }}">{{ $h->record_type }}</span>
                        </td>
                        <td style="font-size:.82rem;max-width:180px;">{{ Str::limit($h->disease_symptoms ?? $h->treatment_given, 40) }}</td>
                        <td style="font-size:.82rem;">{{ $h->vet_doctor_name ?? '&#8212;' }}</td>
                        <td class="text-end fw-bold" style="color:#dc2626;">&#8377;{{ number_format($h->cost,2) }}</td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.health.show',$h) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.health.edit',$h) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state"><i class="bi bi-hospital"></i><p>No health records logged yet</p></td></tr>
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
