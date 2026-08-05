@extends('layouts.app')
@section('title', 'Animal Register')

@section('breadcrumb')
    <li class="breadcrumb-item active">Animals</li>
@endsection

@section('content')

{{-- Page Hero --}}
<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Animal Register</h4>
            <p>Cattle master registry — health, breeding, shed & lactation tracking</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.breeds.index') }}" class="btn btn-sm btn-outline-light px-4">
                <i class="bi bi-collection me-1"></i>Breeds
            </a>
            <a href="{{ route('admin.animals.create') }}" class="btn btn-primary-grad btn-sm px-4">
                <i class="bi bi-plus-lg me-1"></i>Add Animal
            </a>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="kpi-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
            <i class="bi bi-collection-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['total'] }}</div>
            <div class="kpi-label">Total Herd</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#0d9488);">
            <i class="bi bi-droplet-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['milking'] }}</div>
            <div class="kpi-label">Milking</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="kpi-card" style="background:linear-gradient(135deg,#475569,#334155);">
            <i class="bi bi-moon-stars-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['dry'] }}</div>
            <div class="kpi-label">Dry</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="kpi-card" style="background:linear-gradient(135deg,#db2777,#9333ea);">
            <i class="bi bi-heart-pulse-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['pregnant'] }}</div>
            <div class="kpi-label">Pregnant</div>
        </div>
    </div>
    <div class="col-12 col-md">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-stars kpi-icon"></i>
            <div class="kpi-value">{{ $summary['calves'] }}</div>
            <div class="kpi-label">Calves</div>
        </div>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter / Search Bar --}}
<form method="GET" action="{{ route('admin.animals.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Animal</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;">
                    <i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i>
                </span>
                <input type="text" name="search" class="form-control" placeholder="Tag No. or Name…"
                    value="{{ request('search') }}"
                    style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Pregnancy</label>
            <select name="pregnancy_status" class="form-select" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach(['Open','Inseminated','Pregnant','Dry'] as $ps)
                    <option value="{{ $ps }}" @selected(request('pregnancy_status')===$ps)>{{ $ps }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All</option>
                @foreach(['Active','Sold','Dead'] as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Breed</label>
            <select name="breed" class="form-select" onchange="this.form.submit()">
                <option value="">All Breeds</option>
                @foreach($allBreeds as $b)
                    <option value="{{ $b->name }}" @selected(request('breed')===$b->name)>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','pregnancy_status','status','breed']))
                <a href="{{ route('admin.animals.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                    style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Animals Table --}}
<div class="card-glass">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-card-list me-2 text-primary"></i>Herd Registry</h6>
            @if(request()->hasAny(['search','pregnancy_status','status','breed']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;">
                    <i class="bi bi-funnel-fill me-1"></i>Filtered
                </span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $animals->total() }} animals</span>
    </div>

    <div class="table-responsive">
        <table class="table modern-table mb-0" style="min-width:860px;">
            <thead>
                <tr>
                    <th style="width:52px;"></th>
                    <th>Tag / Name</th>
                    <th>Breed</th>
                    <th class="text-center" style="width:90px;">Lactation</th>
                    <th class="text-center" style="width:90px;">Weight</th>
                    <th style="width:140px;">Health</th>
                    <th style="width:120px;">Pregnancy</th>
                    <th style="width:80px;">Shed</th>
                    <th style="width:90px;">Status</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($animals as $a)
                    @php
                        $pregColor = match($a->pregnancy_status) {
                            'Pregnant'    => 'spill-primary',
                            'Inseminated' => 'spill-info',
                            'Dry'         => 'spill-secondary',
                            default       => 'spill-success',
                        };
                        $healthColor = match($a->health_status) {
                            'Healthy'         => 'spill-success',
                            'Under Treatment' => 'spill-warning',
                            default           => 'spill-danger',
                        };
                        $statusColor = match($a->status) {
                            'Active' => 'spill-success',
                            'Sold'   => 'spill-secondary',
                            default  => 'spill-danger',
                        };
                        $breedLower  = strtolower($a->breed ?? '');
                        $avatarGrad  = str_contains($breedLower, 'buffalo')
                            ? 'linear-gradient(135deg,#2563eb,#4f46e5)'
                            : (str_contains($breedLower, 'cow') || str_contains($breedLower, 'gir') || str_contains($breedLower, 'sahiwal') || str_contains($breedLower, 'hf')
                                ? 'linear-gradient(135deg,#059669,#0d9488)'
                                : 'linear-gradient(135deg,#d97706,#ea580c)');
                        $initials = strtoupper(substr($a->tag_number, -2));
                    @endphp
                    <tr>
                        {{-- Avatar --}}
                        <td style="padding-left:16px;">
                            <div style="width:38px;height:38px;border-radius:10px;background:{{ $avatarGrad }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:.68rem;font-weight:800;letter-spacing:.02em;">
                                {{ $initials }}
                            </div>
                        </td>

                        {{-- Tag / Name --}}
                        <td>
                            <a href="{{ route('admin.animals.show', $a) }}"
                                style="font-weight:700;color:var(--primary);font-size:.88rem;text-decoration:none;line-height:1.25;display:block;"
                                class="hover-underline">{{ $a->tag_number }}</a>
                            <div style="font-size:.73rem;color:#9ca3af;margin-top:1px;">{{ $a->name ?: '—' }}</div>
                        </td>

                        {{-- Breed --}}
                        <td>
                            <span style="font-size:.82rem;font-weight:600;color:#374151;">{{ $a->breed }}</span>
                        </td>

                        {{-- Lactation --}}
                        <td class="text-center">
                            @if($a->lactation_number > 0)
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:var(--primary-soft);color:var(--primary);font-size:.78rem;font-weight:700;">{{ $a->lactation_number }}</span>
                            @else
                                <span style="font-size:.75rem;color:#9ca3af;font-style:italic;">Calf</span>
                            @endif
                        </td>

                        {{-- Weight --}}
                        <td class="text-center">
                            @if($a->current_weight)
                                <span style="font-size:.83rem;font-weight:600;color:#374151;">{{ number_format($a->current_weight) }}<span style="font-size:.66rem;color:#9ca3af;font-weight:400;"> kg</span></span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>

                        {{-- Health --}}
                        <td><span class="spill {{ $healthColor }}">{{ $a->health_status }}</span></td>

                        {{-- Pregnancy --}}
                        <td><span class="spill {{ $pregColor }}">{{ $a->pregnancy_status }}</span></td>

                        {{-- Shed --}}
                        <td>
                            <span style="font-size:.78rem;background:#f5f7fa;border:1px solid #e5e7eb;border-radius:6px;padding:3px 8px;color:#374151;font-weight:600;">
                                {{ $a->shed_number }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td><span class="spill {{ $statusColor }}">{{ $a->status }}</span></td>

                        {{-- Actions --}}
                        <td style="text-align:center;">
                            <a href="{{ route('admin.animals.show', $a) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.animals.edit', $a) }}" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="empty-state">
                            <i class="bi bi-collection"></i>
                            <p>{{ request()->hasAny(['search','pregnancy_status','status','breed']) ? 'No animals match your filters.' : 'No animals registered yet.' }}</p>
                            @unless(request()->hasAny(['search','pregnancy_status','status','breed']))
                                <a href="{{ route('admin.animals.create') }}" class="btn btn-primary-grad btn-sm px-4 mt-2">
                                    <i class="bi bi-plus-lg me-1"></i>Add First Animal
                                </a>
                            @endunless
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($animals->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $animals->firstItem() }}</strong>–<strong>{{ $animals->lastItem() }}</strong> of <strong>{{ $animals->total() }}</strong> animals
            </div>
            {{ $animals->links() }}
        </div>
    @endif
</div>

@endsection
