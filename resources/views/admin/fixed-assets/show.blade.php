@extends('layouts.app')
@section('title', $fixedAsset->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fixed-assets.index') }}">Fixed Assets</a></li>
    <li class="breadcrumb-item active">{{ $fixedAsset->asset_code }}</li>
@endsection

@php
    $fmt  = fn($v) => '₹' . number_format($v, 2);
    $tc   = $fixedAsset->type_config;
    $sc   = $fixedAsset->status_config;
    $dp   = $fixedAsset->depreciation_percent;
    $bv   = $fixedAsset->book_value;
    $dpColor = $dp >= 75 ? '#dc2626' : ($dp >= 50 ? '#d97706' : '#059669');
@endphp

@section('content')

<style>
.asset-kpi { background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #f0f0f0;box-shadow:0 2px 6px rgba(0,0,0,.04); }
.asset-kpi-val { font-size:1.3rem;font-weight:800;color:#111827;line-height:1; }
.asset-kpi-lbl { font-size:.74rem;color:#6b7280;margin-top:3px; }
.depr-bar { height:10px;border-radius:5px;background:#f3f4f6;overflow:hidden;margin-top:6px; }
.depr-bar-fill { height:100%;border-radius:5px; }
.sched-row-past { background:#f8fafc; }
.sched-row-now  { background:#fffbeb;font-weight:700; }
</style>

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div class="d-flex align-items-center gap-3">
            <div style="background:rgba(255,255,255,.2);border-radius:14px;padding:12px 16px;font-size:1.6rem;">
                <i class="bi bi-{{ $tc['icon'] }}"></i>
            </div>
            <div>
                <h4 style="margin:0;font-weight:800;">{{ $fixedAsset->name }}</h4>
                <p style="opacity:.8;margin:2px 0 0;font-size:.85rem;">
                    {{ $fixedAsset->asset_code }} &nbsp;·&nbsp; {{ $fixedAsset->asset_type }}
                    @if($fixedAsset->location) &nbsp;·&nbsp; <i class="bi bi-geo-alt"></i> {{ $fixedAsset->location }} @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.fixed-assets.edit', $fixedAsset) }}"
               class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <a href="{{ route('admin.fixed-assets.index') }}"
               class="btn btn-sm" style="background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);border-radius:9px;backdrop-filter:blur(4px);">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

{{-- ── KPI Row ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="asset-kpi">
            <div class="asset-kpi-val">{{ $fmt($fixedAsset->purchase_value) }}</div>
            <div class="asset-kpi-lbl">Purchase Value</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="asset-kpi">
            <div class="asset-kpi-val" style="color:{{ $dpColor }};">{{ $fmt($bv) }}</div>
            <div class="asset-kpi-lbl">Current Book Value</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="asset-kpi">
            <div class="asset-kpi-val" style="color:#dc2626;">{{ $fmt($fixedAsset->accumulated_depreciation) }}</div>
            <div class="asset-kpi-lbl">Accumulated Depr.</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="asset-kpi">
            <div class="asset-kpi-val">{{ $fixedAsset->age_string }}</div>
            <div class="asset-kpi-lbl">Age &nbsp;·&nbsp; {{ $fixedAsset->remaining_life }}y remaining</div>
        </div>
    </div>
</div>

{{-- Depreciation progress bar --}}
@if($fixedAsset->depreciation_method !== 'none')
<div class="card-glass p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span style="font-size:.83rem;font-weight:600;color:#374151;">Depreciation Progress</span>
        <span style="font-size:.83rem;font-weight:700;color:{{ $dpColor }};">{{ $dp }}% depreciated</span>
    </div>
    <div class="depr-bar">
        <div class="depr-bar-fill" style="width:{{ $dp }}%;background:{{ $dpColor }};"></div>
    </div>
    <div class="d-flex justify-content-between mt-2" style="font-size:.72rem;color:#9ca3af;">
        <span>{{ $fmt($fixedAsset->salvage_value) }} salvage</span>
        <span>{{ $fmt($fixedAsset->purchase_value) }} purchase</span>
    </div>
</div>
@endif

<div class="row g-4">
    {{-- Left: Details --}}
    <div class="col-lg-5">
        <div class="card-glass p-4 mb-4">
            <h6 style="font-weight:700;color:#374151;margin-bottom:16px;border-bottom:1px solid #f3f4f6;padding-bottom:10px;">
                <i class="bi bi-info-circle me-2 text-primary"></i>Asset Details
            </h6>
            <dl class="dl">
                <dt>Asset Code</dt>
                <dd style="font-family:monospace;color:#4f46e5;font-weight:700;">{{ $fixedAsset->asset_code }}</dd>

                <dt>Type</dt>
                <dd>
                    <span style="padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;color:{{ $tc['color'] }};background:{{ $tc['bg'] }};">
                        <i class="bi bi-{{ $tc['icon'] }} me-1"></i>{{ $fixedAsset->asset_type }}
                    </span>
                </dd>

                <dt>Status</dt>
                <dd>
                    <span style="padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;color:{{ $sc['color'] }};background:{{ $sc['bg'] }};">
                        {{ $sc['label'] }}
                    </span>
                </dd>

                @if($fixedAsset->serial_number)
                <dt>Serial / Tag No.</dt>
                <dd style="font-family:monospace;">{{ $fixedAsset->serial_number }}</dd>
                @endif

                @if($fixedAsset->location)
                <dt>Location</dt>
                <dd>{{ $fixedAsset->location }}</dd>
                @endif

                @if($fixedAsset->vendor)
                <dt>Vendor / Supplier</dt>
                <dd>{{ $fixedAsset->vendor }}</dd>
                @endif

                <dt>Purchase Date</dt>
                <dd>{{ $fixedAsset->purchase_date->format('d M Y') }}</dd>

                @if($fixedAsset->description)
                <dt>Description</dt>
                <dd>{{ $fixedAsset->description }}</dd>
                @endif
            </dl>
        </div>

        <div class="card-glass p-4">
            <h6 style="font-weight:700;color:#374151;margin-bottom:16px;border-bottom:1px solid #f3f4f6;padding-bottom:10px;">
                <i class="bi bi-graph-down-arrow me-2 text-warning"></i>Depreciation Settings
            </h6>
            <dl class="dl">
                <dt>Method</dt>
                <dd>
                    @php
                        $methodLabel = ['straight_line'=>'Straight Line (SLM)', 'reducing_balance'=>'Reducing Balance (WDV)', 'none'=>'None'];
                    @endphp
                    {{ $methodLabel[$fixedAsset->depreciation_method] ?? $fixedAsset->depreciation_method }}
                </dd>
                <dt>Purchase Value</dt>
                <dd>{{ $fmt($fixedAsset->purchase_value) }}</dd>
                <dt>Salvage Value</dt>
                <dd>{{ $fmt($fixedAsset->salvage_value) }}</dd>
                <dt>Useful Life</dt>
                <dd>{{ $fixedAsset->useful_life_years }} years</dd>
                @if($fixedAsset->depreciation_rate)
                <dt>Annual Rate</dt>
                <dd>{{ $fixedAsset->depreciation_rate }}%</dd>
                @endif
                @if($fixedAsset->depreciation_method !== 'none')
                <dt>Annual Depreciation</dt>
                <dd>{{ $fmt($fixedAsset->annual_depreciation) }}</dd>
                @endif
            </dl>
        </div>
    </div>

    {{-- Right: Depreciation Schedule --}}
    <div class="col-lg-7">
        @if(count($fixedAsset->depreciation_schedule) > 0)
        <div class="table-card">
            <div class="card-header">
                <span class="card-title"><i class="bi bi-table me-2"></i>Depreciation Schedule</span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0" style="font-size:.83rem;">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th class="text-end">Opening BV</th>
                            <th class="text-end">Depreciation</th>
                            <th class="text-end">Closing BV</th>
                            <th class="text-end">% Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentYear = floor($fixedAsset->years_elapsed); @endphp
                        @foreach($fixedAsset->depreciation_schedule as $row)
                        <tr class="{{ $row['is_past'] ? 'sched-row-past' : '' }} {{ $row['year'] == $currentYear + 1 ? 'sched-row-now' : '' }}">
                            <td>
                                Year {{ $row['year'] }}
                                @if($row['year'] == $currentYear + 1)
                                    <span style="font-size:.68rem;background:#fef3c7;color:#d97706;padding:1px 6px;border-radius:4px;margin-left:4px;">Current</span>
                                @elseif($row['is_past'])
                                    <span style="font-size:.68rem;background:#f3f4f6;color:#9ca3af;padding:1px 6px;border-radius:4px;margin-left:4px;">Done</span>
                                @endif
                            </td>
                            <td class="text-end" style="color:#374151;">{{ $fmt($row['opening_bv']) }}</td>
                            <td class="text-end" style="color:#dc2626;">- {{ $fmt($row['depreciation']) }}</td>
                            <td class="text-end" style="font-weight:{{ $row['year'] == $currentYear + 1 ? '700' : '400' }};color:#059669;">
                                {{ $fmt($row['closing_bv']) }}
                            </td>
                            <td class="text-end" style="color:#6b7280;">
                                {{ number_format(($row['closing_bv'] / max(1, $fixedAsset->purchase_value)) * 100, 1) }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;font-weight:700;border-top:2px solid #e5e7eb;">
                            <td>Total</td>
                            <td class="text-end" style="color:#374151;">{{ $fmt($fixedAsset->purchase_value) }}</td>
                            <td class="text-end" style="color:#dc2626;">- {{ $fmt($fixedAsset->purchase_value - $fixedAsset->salvage_value) }}</td>
                            <td class="text-end" style="color:#059669;">{{ $fmt($fixedAsset->salvage_value) }}</td>
                            <td class="text-end" style="color:#6b7280;">
                                {{ number_format(($fixedAsset->salvage_value / max(1, $fixedAsset->purchase_value)) * 100, 1) }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        @if($fixedAsset->notes)
        <div class="card-glass p-4 mt-4">
            <h6 style="font-weight:700;color:#374151;margin-bottom:12px;">
                <i class="bi bi-sticky me-2 text-muted"></i>Notes
            </h6>
            <p style="font-size:.85rem;color:#374151;margin:0;white-space:pre-line;">{{ $fixedAsset->notes }}</p>
        </div>
        @endif

        @if($fixedAsset->status === 'disposed' || $fixedAsset->disposal_date)
        <div class="card-glass p-4 mt-4" style="border-left:4px solid #dc2626;">
            <h6 style="font-weight:700;color:#dc2626;margin-bottom:12px;">
                <i class="bi bi-x-circle me-2"></i>Disposal Details
            </h6>
            <dl class="dl">
                @if($fixedAsset->disposal_date)
                <dt>Disposal Date</dt>
                <dd>{{ $fixedAsset->disposal_date->format('d M Y') }}</dd>
                @endif
                @if($fixedAsset->disposal_value !== null)
                <dt>Disposal Value</dt>
                <dd>{{ $fmt($fixedAsset->disposal_value) }}</dd>
                <dt>Gain / (Loss) on Disposal</dt>
                @php $gainLoss = $fixedAsset->disposal_value - $fixedAsset->book_value; @endphp
                <dd style="color:{{ $gainLoss >= 0 ? '#059669' : '#dc2626' }};font-weight:700;">
                    {{ $gainLoss >= 0 ? '+' : '' }}{{ $fmt($gainLoss) }}
                </dd>
                @endif
            </dl>
        </div>
        @endif
    </div>
</div>

@endsection
