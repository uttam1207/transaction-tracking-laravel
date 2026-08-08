@extends('layouts.app')
@section('title', 'Breeding & Fertility Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.center') }}">Reports</a></li>
    <li class="breadcrumb-item active">Breeding Report</li>
@endsection

@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#7c3aed,#9333ea,#c084fc);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Module 17 — Reports</div>
            <h4 style="color:#fff;">Breeding & Fertility Report</h4>
            <p style="color:rgba(255,255,255,.75);">AI success rate, pregnancy tracking, expected calving schedule</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.breeding.index') }}" class="btn btn-sm btn-light px-3">
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
            <label class="form-label fw-semibold" style="font-size:.78rem;">From Date (AI Date)</label>
            <input type="date" name="date_from" class="form-control form-control-sm"
                value="{{ request('date_from', now()->startOfYear()->toDateString()) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">To Date</label>
            <input type="date" name="date_to" class="form-control form-control-sm"
                value="{{ request('date_to', now()->toDateString()) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="Confirmed Pregnant" @selected(request('status')==='Confirmed Pregnant')>Confirmed Pregnant</option>
                <option value="Open" @selected(request('status')==='Open')>Open / Not Pregnant</option>
                <option value="Pending Check" @selected(request('status')==='Pending Check')>Pending Check</option>
                <option value="Calved" @selected(request('status')==='Calved')>Calved</option>
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
    use App\Models\BreedingRecord;
    $dateFrom = request('date_from', now()->startOfYear()->toDateString());
    $dateTo   = request('date_to', now()->toDateString());
    $statusFilter = request('status');

    $query = BreedingRecord::with('animal')
        ->whereDate('ai_date', '>=', $dateFrom)
        ->whereDate('ai_date', '<=', $dateTo);
    if ($statusFilter) $query->where('status', $statusFilter);

    $records = $query->latest('ai_date')->get();

    $totalAI          = $records->count();
    $confirmedPregnant = $records->where('is_pregnant', true)->count();
    $successRate      = $totalAI > 0 ? round($confirmedPregnant / $totalAI * 100, 1) : 0;
    $calved           = $records->whereNotNull('actual_calving_date')->count();

    // Upcoming calvings in next 30 days
    $upcomingCalvings = BreedingRecord::with('animal')
        ->whereNull('actual_calving_date')
        ->whereNotNull('expected_calving_date')
        ->whereBetween('expected_calving_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
        ->get();
@endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#9333ea);">
            <i class="bi bi-clipboard2-check-fill kpi-icon"></i>
            <div class="kpi-value">{{ $totalAI }}</div>
            <div class="kpi-label">Total AI Events</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#10b981);">
            <i class="bi bi-heart-fill kpi-icon"></i>
            <div class="kpi-value">{{ $confirmedPregnant }}</div>
            <div class="kpi-label">Confirmed Pregnant</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
            <i class="bi bi-percent kpi-icon"></i>
            <div class="kpi-value">{{ $successRate }}%</div>
            <div class="kpi-label">AI Success Rate</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-calendar-check-fill kpi-icon"></i>
            <div class="kpi-value">{{ $upcomingCalvings->count() }}</div>
            <div class="kpi-label">Calvings in 30 Days</div>
        </div>
    </div>
</div>

{{-- Upcoming Calving Alert --}}
@if($upcomingCalvings->count() > 0)
<div class="alert mb-4 d-flex align-items-start gap-3" style="background:#fef3c7;border:1px solid #fcd34d;border-radius:12px;padding:14px 18px;">
    <i class="bi bi-exclamation-triangle-fill" style="color:#d97706;font-size:1.2rem;flex-shrink:0;margin-top:2px;"></i>
    <div>
        <div class="fw-bold" style="color:#92400e;font-size:.88rem;">Upcoming Calvings — {{ $upcomingCalvings->count() }} animal(s) expected to calve within 30 days</div>
        <div class="mt-2 d-flex flex-wrap gap-2">
            @foreach($upcomingCalvings as $uc)
            @php $daysAway = now()->diffInDays($uc->expected_calving_date); @endphp
            <span style="font-size:.75rem;background:#fde68a;color:#78350f;padding:3px 10px;border-radius:20px;font-weight:600;">
                {{ $uc->animal?->tag_number ?? 'Unknown' }}
                {{ $uc->animal?->name ? '('.$uc->animal->name.')' : '' }}
                — {{ $uc->expected_calving_date->format('d M') }}
                ({{ $daysAway === 0 ? 'Today' : "in {$daysAway}d" }})
            </span>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Breeding Records Table --}}
<div class="card-glass overflow-hidden">
    <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
        <span class="fw-bold"><i class="bi bi-heart-pulse-fill me-2 text-purple"></i>Breeding Records ({{ $records->count() }})</span>
        <a href="{{ route('admin.breeding.create') }}" class="btn btn-sm btn-outline-primary px-3">
            <i class="bi bi-plus-lg me-1"></i>Add Record
        </a>
    </div>
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Animal</th>
                    <th>Heat Date</th>
                    <th>AI Date</th>
                    <th>Semen Code</th>
                    <th>Preg. Check</th>
                    <th>Pregnant?</th>
                    <th>Exp. Calving</th>
                    <th>Actual Calving</th>
                    <th>Calf Tag</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr>
                    <td>
                        <div class="fw-semibold" style="font-size:.85rem;color:var(--primary);">{{ $r->animal?->name ?? '—' }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">{{ $r->animal?->tag_number ?? 'ID:'.$r->animal_id }}</div>
                    </td>
                    <td style="font-size:.82rem;">{{ $r->heat_date?->format('d M Y') ?? '—' }}</td>
                    <td style="font-size:.82rem;font-weight:600;">{{ $r->ai_date?->format('d M Y') ?? '—' }}</td>
                    <td><code style="font-size:.75rem;background:#f5f7fa;padding:2px 6px;border-radius:4px;">{{ $r->bull_semen_code ?? '—' }}</code></td>
                    <td style="font-size:.82rem;">{{ $r->pregnancy_check_date?->format('d M Y') ?? '—' }}</td>
                    <td>
                        @if($r->pregnancy_check_date)
                            @if($r->is_pregnant)
                                <span class="spill spill-success" style="font-size:.72rem;">Yes</span>
                            @else
                                <span class="spill spill-danger" style="font-size:.72rem;">No</span>
                            @endif
                        @else
                            <span class="spill spill-secondary" style="font-size:.72rem;">Pending</span>
                        @endif
                    </td>
                    <td style="font-size:.82rem;">
                        @if($r->expected_calving_date)
                            @php $daysAway = now()->diffInDays($r->expected_calving_date, false); @endphp
                            {{ $r->expected_calving_date->format('d M Y') }}
                            @if(!$r->actual_calving_date && $daysAway <= 7 && $daysAway >= 0)
                                <span class="spill spill-warning" style="font-size:.68rem;">{{ $daysAway === 0 ? 'Today' : "{$daysAway}d" }}</span>
                            @endif
                        @else
                            <span style="color:#d1d5db;">—</span>
                        @endif
                    </td>
                    <td style="font-size:.82rem;color:#059669;font-weight:600;">{{ $r->actual_calving_date?->format('d M Y') ?? '—' }}</td>
                    <td><code style="font-size:.75rem;background:#f0fdf4;padding:2px 6px;border-radius:4px;color:#059669;">{{ $r->calf_tag_number ?? '—' }}</code></td>
                    <td>
                        @php
                            $st = $r->status ?? 'Pending Check';
                            $stColor = match($st) {
                                'Confirmed Pregnant' => 'spill-success',
                                'Calved' => 'spill-primary',
                                'Open' => 'spill-danger',
                                default => 'spill-secondary',
                            };
                        @endphp
                        <span class="spill {{ $stColor }}" style="font-size:.72rem;">{{ $st }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="empty-state">
                        <i class="bi bi-heart-pulse"></i>
                        <p>No breeding records for the selected period.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
