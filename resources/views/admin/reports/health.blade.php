@extends('layouts.app')
@section('title', 'Health & Treatment Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.center') }}">Reports</a></li>
    <li class="breadcrumb-item active">Health Report</li>
@endsection

@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#b91c1c,#dc2626,#f87171);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Module 17 — Reports</div>
            <h4 style="color:#fff;">Health & Treatment Report</h4>
            <p style="color:rgba(255,255,255,.75);">Vaccination records, disease treatment, veterinary costs</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.health.index') }}" class="btn btn-sm btn-light px-3">
                <i class="bi bi-plus-lg me-1"></i>Manage Records
            </a>
            <a href="{{ route('admin.reports.center') }}" class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-grid-3x3-gap me-1"></i>All Reports
            </a>
        </div>
    </div>
</div>

{{-- Filter Bar --}}
<div class="card-glass p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">From Date</label>
            <input type="date" name="date_from" class="form-control form-control-sm"
                value="{{ request('date_from', now()->startOfMonth()->toDateString()) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">To Date</label>
            <input type="date" name="date_to" class="form-control form-control-sm"
                value="{{ request('date_to', now()->toDateString()) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Record Type</label>
            <select name="record_type" class="form-select form-select-sm">
                <option value="">All Types</option>
                @foreach(['Vaccination','Treatment','Check-up','Surgery','Deworming','Other'] as $t)
                    <option value="{{ $t }}" @selected(request('record_type')===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary-grad btn-sm w-100">
                <i class="bi bi-funnel me-1"></i>Apply Filter
            </button>
        </div>
    </form>
</div>

@php
    use App\Models\HealthRecord;
    use App\Models\Animal;
    $dateFrom   = request('date_from', now()->startOfMonth()->toDateString());
    $dateTo     = request('date_to', now()->toDateString());
    $typeFilter = request('record_type');

    $query = HealthRecord::with('animal')
        ->whereDate('date', '>=', $dateFrom)
        ->whereDate('date', '<=', $dateTo);
    if ($typeFilter) $query->where('record_type', $typeFilter);

    $records      = $query->latest('date')->get();
    $totalRecords = $records->count();
    $totalCost    = $records->sum('cost');
    $byType       = $records->groupBy('record_type');
    $activeCases  = Animal::where('health_status', 'Under Treatment')->count();
    $sickAnimals  = Animal::where('health_status', 'Sick')->count();
@endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">
            <i class="bi bi-clipboard2-pulse-fill kpi-icon"></i>
            <div class="kpi-value">{{ $totalRecords }}</div>
            <div class="kpi-label">Total Records</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-currency-rupee kpi-icon"></i>
            <div class="kpi-value">₹{{ number_format($totalCost, 0) }}</div>
            <div class="kpi-label">Total Vet Cost</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#9333ea);">
            <i class="bi bi-bandaid-fill kpi-icon"></i>
            <div class="kpi-value">{{ $activeCases }}</div>
            <div class="kpi-label">Under Treatment</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0891b2,#0369a1);">
            <i class="bi bi-heart-pulse-fill kpi-icon"></i>
            <div class="kpi-value">{{ $byType->get('Vaccination', collect())->count() }}</div>
            <div class="kpi-label">Vaccinations</div>
        </div>
    </div>
</div>

@if($activeCases > 0 || $sickAnimals > 0)
<div class="alert mb-4 d-flex align-items-start gap-3" style="background:#fef2f2;border:1px solid #fca5a5;border-radius:12px;padding:14px 18px;">
    <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;font-size:1.2rem;flex-shrink:0;margin-top:2px;"></i>
    <div>
        <div class="fw-bold" style="color:#991b1b;font-size:.88rem;">
            {{ $sickAnimals }} sick animal(s) and {{ $activeCases }} under treatment
        </div>
        <div style="font-size:.78rem;color:#b91c1c;margin-top:3px;">
            <a href="{{ route('admin.animals.index') }}" style="color:#dc2626;">View in Animal Management →</a>
        </div>
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    {{-- Records by Type --}}
    <div class="col-md-5">
        <div class="card-glass p-4 h-100">
            <h6 class="fw-bold mb-3" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">
                <i class="bi bi-bar-chart-fill me-1 text-danger"></i>Records by Type
            </h6>
            @forelse($byType as $type => $typeRecords)
            @php
                $cost = $typeRecords->sum('cost');
                $typeColor = match($type) {
                    'Vaccination' => '#4f46e5',
                    'Treatment' => '#dc2626',
                    'Check-up' => '#059669',
                    'Surgery' => '#7c3aed',
                    'Deworming' => '#0891b2',
                    default => '#6b7280'
                };
            @endphp
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <span style="font-size:.85rem;font-weight:600;color:{{ $typeColor }};">{{ $type ?: 'Other' }}</span>
                    <div style="font-size:.72rem;color:#9ca3af;">₹{{ number_format($cost, 0) }}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:90px;height:6px;border-radius:4px;background:#f1f5f9;overflow:hidden;">
                        <div style="width:{{ $totalRecords > 0 ? ($typeRecords->count()/$totalRecords*100) : 0 }}%;height:100%;background:{{ $typeColor }};border-radius:4px;"></div>
                    </div>
                    <span class="fw-bold" style="font-size:.85rem;min-width:24px;color:{{ $typeColor }};">{{ $typeRecords->count() }}</span>
                </div>
            </div>
            @empty
            <p class="text-muted" style="font-size:.82rem;">No records for period.</p>
            @endforelse
            @if($totalCost > 0)
            <hr class="opacity-25 my-2">
            <div class="d-flex justify-content-between">
                <span style="font-size:.82rem;font-weight:700;">Total Cost</span>
                <span class="fw-bold text-danger">₹{{ number_format($totalCost, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Top 5 costliest treatments --}}
    <div class="col-md-7">
        <div class="card-glass p-4 h-100">
            <h6 class="fw-bold mb-3" style="font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">
                <i class="bi bi-sort-down me-1 text-warning"></i>Top Treatments by Cost
            </h6>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size:.82rem;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="font-size:.72rem;color:#6b7280;">Animal</th>
                            <th style="font-size:.72rem;color:#6b7280;">Type</th>
                            <th style="font-size:.72rem;color:#6b7280;">Date</th>
                            <th style="font-size:.72rem;color:#6b7280;text-align:right;">Cost (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records->sortByDesc('cost')->take(5) as $top)
                        <tr>
                            <td>{{ $top->animal?->name ?? $top->animal?->tag_number ?? 'Unknown' }}</td>
                            <td><span class="spill spill-secondary" style="font-size:.7rem;">{{ $top->record_type }}</span></td>
                            <td>{{ $top->date->format('d M Y') }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($top->cost, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Records Table --}}
<div class="card-glass overflow-hidden">
    <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="bi bi-list-columns-reverse me-2 text-danger"></i>Health Records ({{ $totalRecords }})</span>
        <a href="{{ route('admin.health.create') }}" class="btn btn-sm btn-outline-danger px-3">
            <i class="bi bi-plus-lg me-1"></i>Add Record
        </a>
    </div>
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Animal</th>
                    <th>Type</th>
                    <th>Symptoms</th>
                    <th>Treatment</th>
                    <th>Medicine</th>
                    <th>Vet</th>
                    <th>Temp (°C)</th>
                    <th class="text-end">Cost (₹)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td style="font-size:.82rem;font-weight:600;">{{ $r->date->format('d M Y') }}</td>
                    <td>
                        <div class="fw-semibold" style="font-size:.82rem;color:var(--primary);">{{ $r->animal?->name ?? '—' }}</div>
                        <div style="font-size:.7rem;color:#9ca3af;">{{ $r->animal?->tag_number }}</div>
                    </td>
                    <td>
                        @php
                            $typeColor = match($r->record_type) {
                                'Vaccination' => 'spill-primary',
                                'Treatment' => 'spill-danger',
                                'Check-up' => 'spill-success',
                                'Surgery' => 'spill-warning',
                                'Deworming' => 'spill-info',
                                default => 'spill-secondary'
                            };
                        @endphp
                        <span class="spill {{ $typeColor }}" style="font-size:.72rem;">{{ $r->record_type }}</span>
                    </td>
                    <td style="font-size:.78rem;max-width:130px;">{{ Str::limit($r->disease_symptoms, 35) ?: '—' }}</td>
                    <td style="font-size:.78rem;max-width:130px;">{{ Str::limit($r->treatment_given, 35) ?: '—' }}</td>
                    <td style="font-size:.78rem;">{{ $r->medicine_used ?: '—' }}</td>
                    <td style="font-size:.78rem;">{{ $r->vet_doctor_name ?: '—' }}</td>
                    <td style="font-size:.82rem;">{{ $r->body_temp ? number_format($r->body_temp, 1) : '—' }}</td>
                    <td class="text-end fw-bold" style="font-size:.85rem;color:#dc2626;">
                        {{ $r->cost > 0 ? '₹'.number_format($r->cost, 2) : '—' }}
                    </td>
                    <td>
                        <span class="spill {{ $r->status === 'Recovered' ? 'spill-success' : ($r->status === 'Ongoing' ? 'spill-warning' : 'spill-secondary') }}" style="font-size:.72rem;">
                            {{ $r->status ?? '—' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="empty-state">
                        <i class="bi bi-bandaid"></i>
                        <p>No health records for the selected period.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($totalCost > 0)
            <tfoot>
                <tr style="background:rgba(220,38,38,.05);font-weight:700;">
                    <td colspan="8" class="text-end">Total Veterinary Cost</td>
                    <td class="text-end" style="color:#dc2626;">₹{{ number_format($totalCost, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
