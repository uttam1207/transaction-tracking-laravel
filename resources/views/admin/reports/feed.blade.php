@extends('layouts.app')
@section('title', 'Feed Requirement Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.center') }}">Reports</a></li>
    <li class="breadcrumb-item active">Feed Report</li>
@endsection

@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#78350f,#d97706,#fbbf24);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Module 17 — Reports</div>
            <h4 style="color:#fff;">Feed Requirement & Stock Report</h4>
            <p style="color:rgba(255,255,255,.75);">Daily, weekly, monthly feed requirements vs available stock</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.feed.calculator') }}" class="btn btn-sm btn-light px-3">
                <i class="bi bi-calculator me-1"></i>Feed Calculator
            </a>
            <a href="{{ route('admin.reports.center') }}" class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-grid-3x3-gap me-1"></i>All Reports
            </a>
        </div>
    </div>
</div>

@php
    $feedService = app(App\Services\FeedCalculationService::class);
    $data = $feedService->getFeedCalculationSummary();
    $stockComparison = $data['stock_comparison'];
    $totalDailyFeed = array_sum(array_column($stockComparison, 'daily_need'));
@endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card amber">
            <div class="kpi-label">Total Animals (Groups)</div>
            <div class="kpi-value">{{ $data['total_animals'] }}</div>
            <div class="kpi-sub text-muted">head</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card teal">
            <div class="kpi-label">Total Daily Feed</div>
            <div class="kpi-value">{{ number_format($totalDailyFeed, 0) }}</div>
            <div class="kpi-sub text-muted">kg/day</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card blue">
            <div class="kpi-label">Weekly Requirement</div>
            <div class="kpi-value">{{ number_format($totalDailyFeed * 7, 0) }}</div>
            <div class="kpi-sub text-muted">kg/week</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card green">
            <div class="kpi-label">Monthly Requirement</div>
            <div class="kpi-value">{{ number_format($totalDailyFeed * 30, 0) }}</div>
            <div class="kpi-sub text-muted">kg/month</div>
        </div>
    </div>
</div>

{{-- Feed Stock Comparison Table --}}
<div class="card-glass overflow-hidden mb-4">
    <div class="px-4 py-3 border-bottom">
        <span class="fw-bold">Feed Stock vs Daily Requirements</span>
    </div>
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Feed Item</th>
                    <th>Available Stock</th>
                    <th>Daily Need</th>
                    <th>Weekly Need</th>
                    <th>Monthly Need</th>
                    <th>Days Left</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockComparison as $feedName => $stock)
                @php
                    $dl = $stock['days_left'];
                    $statusColor = $dl <= ($data['danger_days'] ?? 3) ? '#ef4444' : ($dl <= ($data['warning_days'] ?? 7) ? '#f59e0b' : '#10b981');
                    $statusLabel = $dl <= ($data['danger_days'] ?? 3) ? 'Critical' : ($dl <= ($data['warning_days'] ?? 7) ? 'Low' : 'Adequate');
                @endphp
                <tr>
                    <td class="fw-semibold" style="font-size:.85rem;">{{ $feedName }}</td>
                    <td class="fw-bold" style="font-size:.9rem;color:{{ $statusColor }};">
                        {{ number_format($stock['available'], 1) }} {{ $stock['unit'] }}
                    </td>
                    <td style="font-size:.85rem;">{{ number_format($stock['daily_need'], 2) }} {{ $stock['unit'] }}</td>
                    <td style="font-size:.85rem;">{{ number_format($stock['weekly_need'], 1) }} {{ $stock['unit'] }}</td>
                    <td style="font-size:.85rem;">{{ number_format($stock['monthly_need'], 1) }} {{ $stock['unit'] }}</td>
                    <td>
                        <span class="fw-bold" style="font-size:1rem;color:{{ $statusColor }};">
                            {{ $dl >= 999 ? '∞' : $dl }}
                        </span>
                        <span style="font-size:.75rem;color:#9ca3af;">days</span>
                    </td>
                    <td>
                        <span class="spill spill-{{ $statusLabel === 'Critical' ? 'red' : ($statusLabel === 'Low' ? 'amber' : 'green') }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Group-wise Feed Plan --}}
<div class="card-glass overflow-hidden">
    <div class="px-4 py-3 border-bottom">
        <span class="fw-bold">Group-wise Daily Feed Plan</span>
    </div>
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Animal Group</th>
                    <th>Head Count</th>
                    @php $allFeedItems = collect($data['groups'])->flatMap(fn($g) => array_keys($g['requirements']))->unique()->sort()->values(); @endphp
                    @foreach($allFeedItems as $fi)
                    <th>{{ $fi }} (kg)</th>
                    @endforeach
                    <th>Total/Day (kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['groups'] as $group)
                @php $groupTotal = array_sum(array_column($group['requirements'], 'total_daily')); @endphp
                <tr>
                    <td class="fw-semibold" style="font-size:.85rem;">{{ $group['name'] }}</td>
                    <td style="font-size:.85rem;color:#4f46e5;font-weight:700;">{{ $group['count'] }}</td>
                    @foreach($allFeedItems as $fi)
                    <td style="font-size:.82rem;">
                        @if(isset($group['requirements'][$fi]))
                            {{ number_format($group['requirements'][$fi]['total_daily'], 2) }}
                            <span style="font-size:.68rem;color:#9ca3af;">({{ $group['requirements'][$fi]['per_animal'] }}/head)</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="fw-bold text-success">{{ number_format($groupTotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:rgba(245,158,11,.05);font-weight:700;">
                    <td>TOTAL</td>
                    <td>{{ $data['total_animals'] }}</td>
                    @foreach($allFeedItems as $fi)
                    <td class="text-warning">{{ isset($stockComparison[$fi]) ? number_format($stockComparison[$fi]['daily_need'], 2) : '—' }}</td>
                    @endforeach
                    <td class="text-warning">{{ number_format($totalDailyFeed, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection
