@extends('layouts.app')
@section('title', 'Animal & Population Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.center') }}">Reports</a></li>
    <li class="breadcrumb-item active">Animal Report</li>
@endsection

@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#065f46,#059669,#10b981);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Module 17 — Reports</div>
            <h4 style="color:#fff;">Animal & Population Report</h4>
            <p style="color:rgba(255,255,255,.75);">Herd breakdown by type, reproductive status, health, lactation cycle</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.animals.index') }}" class="btn btn-sm btn-light px-3">
                <i class="bi bi-plus-lg me-1"></i>Manage Herd
            </a>
            <a href="{{ route('admin.reports.center') }}" class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-grid-3x3-gap me-1"></i>All Reports
            </a>
        </div>
    </div>
</div>

@php
    use App\Models\Animal;
    $animals     = Animal::orderBy('animal_type')->orderBy('pregnancy_status')->get();
    $total       = $animals->count();
    $byPregnancy = $animals->groupBy('pregnancy_status');
    $byHealth    = $animals->groupBy('health_status');
    $byAnimalType = $animals->groupBy('animal_type');
    $byLifecycle = $animals->groupBy('status');

    // Milking = Active, not Dry, with at least 1 lactation
    $milking = $animals->where('status', 'Active')
        ->filter(fn($a) => $a->pregnancy_status !== 'Dry' && ($a->lactation_number ?? 0) > 0)
        ->count();
@endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#10b981);">
            <i class="bi bi-collection-fill kpi-icon"></i>
            <div class="kpi-value">{{ $total }}</div>
            <div class="kpi-label">Total Herd</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
            <i class="bi bi-droplet-fill kpi-icon"></i>
            <div class="kpi-value">{{ $milking }}</div>
            <div class="kpi-label">Milking</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#4f46e5);">
            <i class="bi bi-heart-fill kpi-icon"></i>
            <div class="kpi-value">{{ $byPregnancy->get('Pregnant', collect())->count() }}</div>
            <div class="kpi-label">Pregnant</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-moon-fill kpi-icon"></i>
            <div class="kpi-value">{{ $byPregnancy->get('Dry', collect())->count() }}</div>
            <div class="kpi-label">Dry</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">
            <i class="bi bi-thermometer-high kpi-icon"></i>
            <div class="kpi-value">{{ $byHealth->get('Sick', collect())->count() }}</div>
            <div class="kpi-label">Sick</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#9333ea);">
            <i class="bi bi-bandaid-fill kpi-icon"></i>
            <div class="kpi-value">{{ $byHealth->get('Under Treatment', collect())->count() }}</div>
            <div class="kpi-label">Under Treatment</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- By Animal Type --}}
    <div class="col-md-4">
        <div class="card-glass p-4 h-100">
            <h6 class="fw-bold mb-3" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">
                <i class="bi bi-tags-fill me-1 text-primary"></i>By Animal Type
            </h6>
            @foreach(['Cow','Buffalo','Bull','Heifer','Calf'] as $t)
            @php $cnt = $byAnimalType->get($t, collect())->count(); @endphp
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span style="font-size:.85rem;font-weight:600;">{{ $t }}</span>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:80px;height:6px;border-radius:4px;background:#f1f5f9;overflow:hidden;">
                        <div style="width:{{ $total > 0 ? ($cnt/$total*100) : 0 }}%;height:100%;background:#4f46e5;border-radius:4px;"></div>
                    </div>
                    <span class="fw-bold text-primary" style="font-size:.85rem;min-width:20px;">{{ $cnt }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- By Reproductive Status --}}
    <div class="col-md-4">
        <div class="card-glass p-4 h-100">
            <h6 class="fw-bold mb-3" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">
                <i class="bi bi-heart-pulse-fill me-1 text-success"></i>By Reproductive Status
            </h6>
            @foreach(['Open','Inseminated','Pregnant','Dry'] as $ps)
            @php $cnt = $byPregnancy->get($ps, collect())->count(); @endphp
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span style="font-size:.85rem;font-weight:600;">{{ $ps }}</span>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:80px;height:6px;border-radius:4px;background:#f1f5f9;overflow:hidden;">
                        <div style="width:{{ $total > 0 ? ($cnt/$total*100) : 0 }}%;height:100%;background:#10b981;border-radius:4px;"></div>
                    </div>
                    <span class="fw-bold text-success" style="font-size:.85rem;min-width:20px;">{{ $cnt }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- By Health Status --}}
    <div class="col-md-4">
        <div class="card-glass p-4 h-100">
            <h6 class="fw-bold mb-3" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">
                <i class="bi bi-clipboard2-pulse-fill me-1 text-danger"></i>By Health Status
            </h6>
            @foreach(['Healthy','Sick','Under Treatment'] as $hs)
            @php
                $cnt = $byHealth->get($hs, collect())->count();
                $color = match($hs) { 'Healthy' => '#10b981', 'Sick' => '#ef4444', 'Under Treatment' => '#f59e0b', default => '#6b7280' };
            @endphp
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span style="font-size:.85rem;font-weight:600;">{{ $hs }}</span>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:80px;height:6px;border-radius:4px;background:#f1f5f9;overflow:hidden;">
                        <div style="width:{{ $total > 0 ? ($cnt/$total*100) : 0 }}%;height:100%;background:{{ $color }};border-radius:4px;"></div>
                    </div>
                    <span class="fw-bold" style="font-size:.85rem;min-width:20px;color:{{ $color }};">{{ $cnt }}</span>
                </div>
            </div>
            @endforeach
            @php $activeTotal = $byLifecycle->get('Active', collect())->count(); @endphp
            <hr class="my-3 opacity-25">
            <div class="d-flex justify-content-between">
                <span style="font-size:.78rem;color:#6b7280;">Active</span>
                <span class="fw-bold text-success" style="font-size:.82rem;">{{ $activeTotal }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span style="font-size:.78rem;color:#6b7280;">Sold</span>
                <span class="fw-bold" style="font-size:.82rem;color:#6b7280;">{{ $byLifecycle->get('Sold', collect())->count() }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span style="font-size:.78rem;color:#6b7280;">Deceased</span>
                <span class="fw-bold" style="font-size:.82rem;color:#ef4444;">{{ $byLifecycle->get('Deceased', collect())->count() }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Animals Table --}}
<div class="card-glass overflow-hidden">
    <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="bi bi-list-columns-reverse me-2 text-success"></i>Complete Herd Register ({{ $total }} animals)</span>
        <a href="{{ route('admin.animals.create') }}" class="btn btn-sm btn-outline-success px-3">
            <i class="bi bi-plus-lg me-1"></i>Add Animal
        </a>
    </div>
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Tag / Name</th>
                    <th>Type</th>
                    <th>Breed</th>
                    <th>Reproductive</th>
                    <th>Health</th>
                    <th>Status</th>
                    <th>Shed</th>
                    <th>Lactation #</th>
                    <th>Weight (kg)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($animals as $animal)
                <tr>
                    <td>
                        <div class="fw-semibold" style="font-size:.85rem;color:var(--primary);">{{ $animal->name ?? '—' }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">{{ $animal->tag_number }}</div>
                    </td>
                    <td style="font-size:.82rem;">{{ $animal->animal_type ?? '—' }}</td>
                    <td style="font-size:.82rem;">{{ $animal->breed ?? '—' }}</td>
                    <td>
                        @php $preg = $animal->pregnancy_status ?? 'Open'; @endphp
                        <span class="spill {{ match($preg) { 'Pregnant' => 'spill-blue', 'Dry' => 'spill-warning', 'Inseminated' => 'spill-primary', default => 'spill-secondary' } }}" style="font-size:.72rem;">
                            {{ $preg }}
                        </span>
                    </td>
                    <td>
                        @php $hs = $animal->health_status ?? 'Healthy'; @endphp
                        <span class="spill {{ match($hs) { 'Healthy' => 'spill-success', 'Sick' => 'spill-danger', 'Under Treatment' => 'spill-warning', default => 'spill-secondary' } }}" style="font-size:.72rem;">
                            {{ $hs }}
                        </span>
                    </td>
                    <td>
                        @php $st = $animal->status ?? 'Active'; @endphp
                        <span class="spill {{ match($st) { 'Active' => 'spill-success', 'Sold' => 'spill-secondary', 'Deceased' => 'spill-danger', default => 'spill-secondary' } }}" style="font-size:.72rem;">
                            {{ $st }}
                        </span>
                    </td>
                    <td style="font-size:.82rem;">{{ $animal->shed_number ?? '—' }}</td>
                    <td style="font-size:.82rem;font-weight:600;text-align:center;">{{ $animal->lactation_number ?? 0 }}</td>
                    <td style="font-size:.82rem;">{{ $animal->current_weight ? number_format($animal->current_weight, 1) : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="empty-state">
                        <i class="bi bi-collection" style="font-size:2rem;opacity:.3;"></i>
                        <p>No animals registered yet. <a href="{{ route('admin.animals.create') }}">Add first animal</a></p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
