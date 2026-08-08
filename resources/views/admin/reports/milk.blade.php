@extends('layouts.app')
@section('title', 'Milk Production Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.center') }}">Reports</a></li>
    <li class="breadcrumb-item active">Milk Production</li>
@endsection

@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#0369a1,#0891b2,#06b6d4);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Module 17 — Reports</div>
            <h4 style="color:#fff;">Milk Production Report</h4>
            <p style="color:rgba(255,255,255,.75);">Shift-wise yield, fat/SNF analysis, rejection tracking</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.reports.center') }}" class="btn btn-sm btn-light px-3">
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
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', now()->startOfMonth()->toDateString()) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">To Date</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', now()->toDateString()) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Shift</label>
            <select name="shift" class="form-select form-select-sm">
                <option value="">All Shifts</option>
                <option value="Morning" @selected(request('shift')=='Morning')>Morning</option>
                <option value="Evening" @selected(request('shift')=='Evening')>Evening</option>
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
    use App\Models\MilkEntry;
    $dateFrom = request('date_from', now()->startOfMonth()->toDateString());
    $dateTo   = request('date_to', now()->toDateString());
    $shiftFilter = request('shift');

    $query = MilkEntry::with('animal')
        ->whereDate('date', '>=', $dateFrom)
        ->whereDate('date', '<=', $dateTo);
    if ($shiftFilter) $query->where('shift', $shiftFilter);

    $entries = $query->orderBy('date', 'desc')->get();

    $totalLiters    = $entries->sum('quantity_liters');
    $totalRejected  = $entries->sum('rejected_liters');
    $avgFat         = $entries->whereNotNull('fat_percentage')->avg('fat_percentage');
    $avgSnf         = $entries->whereNotNull('snf_percentage')->avg('snf_percentage');
    $morningTotal   = $entries->where('shift','Morning')->sum('quantity_liters');
    $eveningTotal   = $entries->where('shift','Evening')->sum('quantity_liters');
@endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card teal">
            <div class="kpi-label">Total Yield</div>
            <div class="kpi-value">{{ number_format($totalLiters, 1) }}</div>
            <div class="kpi-sub text-muted">liters in period</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card blue">
            <div class="kpi-label">Morning Yield</div>
            <div class="kpi-value">{{ number_format($morningTotal, 1) }}</div>
            <div class="kpi-sub text-muted">liters</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card amber">
            <div class="kpi-label">Evening Yield</div>
            <div class="kpi-value">{{ number_format($eveningTotal, 1) }}</div>
            <div class="kpi-sub text-muted">liters</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card red">
            <div class="kpi-label">Rejected</div>
            <div class="kpi-value">{{ number_format($totalRejected, 1) }}</div>
            <div class="kpi-sub text-muted">liters</div>
        </div>
    </div>
</div>

{{-- Quality Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card-glass p-4 text-center">
            <div style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">Avg Fat %</div>
            <div style="font-size:2.5rem;font-weight:800;color:#0d9488;letter-spacing:-2px;">{{ $avgFat ? number_format($avgFat, 2) : '—' }}%</div>
            <div style="font-size:.75rem;color:#9ca3af;">Target: 6.0–8.0%</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-glass p-4 text-center">
            <div style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">Avg SNF %</div>
            <div style="font-size:2.5rem;font-weight:800;color:#4f46e5;letter-spacing:-2px;">{{ $avgSnf ? number_format($avgSnf, 2) : '—' }}%</div>
            <div style="font-size:.75rem;color:#9ca3af;">Target: 8.5–9.5%</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-glass p-4 text-center">
            <div style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;">Rejection Rate</div>
            <div style="font-size:2.5rem;font-weight:800;color:{{ $totalLiters > 0 && ($totalRejected/$totalLiters)*100 > 5 ? '#ef4444' : '#10b981' }};letter-spacing:-2px;">
                {{ $totalLiters > 0 ? number_format(($totalRejected / $totalLiters) * 100, 1) : 0 }}%
            </div>
            <div style="font-size:.75rem;color:#9ca3af;">Target: &lt;3%</div>
        </div>
    </div>
</div>

{{-- Entries Table --}}
<div class="card-glass overflow-hidden">
    <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
        <span class="fw-bold">Milk Entries ({{ $entries->count() }} records)</span>
        <a href="{{ route('admin.milk.index') }}" class="btn btn-sm btn-outline-primary px-3">
            <i class="bi bi-pencil-square me-1"></i>Manage Entries
        </a>
    </div>
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Animal</th>
                    <th>Yield (L)</th>
                    <th>Fat %</th>
                    <th>SNF %</th>
                    <th>CLR</th>
                    <th>Rejected (L)</th>
                    <th>Quality</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries->take(50) as $e)
                <tr>
                    <td style="font-size:.82rem;font-weight:600;">{{ $e->date->format('d M Y') }}</td>
                    <td>
                        <span class="spill {{ $e->shift === 'Morning' ? 'spill-blue' : 'spill-amber' }}">
                            <i class="bi bi-{{ $e->shift === 'Morning' ? 'sunrise' : 'sunset' }} me-1"></i>{{ $e->shift }}
                        </span>
                    </td>
                    <td style="font-size:.82rem;">{{ $e->animal?->name ?? ('Animal #' . $e->animal_id) }}</td>
                    <td class="fw-bold text-teal">{{ number_format($e->quantity_liters, 2) }}</td>
                    <td style="font-size:.82rem;">{{ $e->fat_percentage ? $e->fat_percentage . '%' : '—' }}</td>
                    <td style="font-size:.82rem;">{{ $e->snf_percentage ? $e->snf_percentage . '%' : '—' }}</td>
                    <td style="font-size:.82rem;">{{ $e->clr_value ?? '—' }}</td>
                    <td style="font-size:.82rem;color:{{ $e->rejected_liters > 0 ? '#ef4444' : '#9ca3af' }};">
                        {{ $e->rejected_liters > 0 ? number_format($e->rejected_liters, 2) : '—' }}
                    </td>
                    <td>
                        @php $qual = $e->quality_rating ?? 'good'; @endphp
                        <span class="spill spill-{{ $qual === 'excellent' ? 'green' : ($qual === 'good' ? 'blue' : ($qual === 'average' ? 'amber' : 'red')) }}">
                            {{ ucfirst($qual) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-droplet" style="font-size:2rem;opacity:.3;"></i>
                        <div class="mt-2">No milk entries for selected period.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($entries->count() > 0)
            <tfoot>
                <tr style="background:rgba(13,148,136,.05);font-weight:700;">
                    <td colspan="3" class="text-end">TOTAL</td>
                    <td>{{ number_format($totalLiters, 2) }} L</td>
                    <td>{{ $avgFat ? number_format($avgFat,2).'%' : '—' }}</td>
                    <td>{{ $avgSnf ? number_format($avgSnf,2).'%' : '—' }}</td>
                    <td>—</td>
                    <td style="color:#ef4444;">{{ number_format($totalRejected, 2) }} L</td>
                    <td>—</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
