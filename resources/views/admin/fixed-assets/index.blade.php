@extends('layouts.app')
@section('title', 'Fixed Assets')

@section('breadcrumb')
    <li class="breadcrumb-item active">Fixed Assets</li>
@endsection

@php
    $fmt = fn($v) => '₹' . number_format($v, 2);
@endphp

@section('content')

<style>
.fa-stat-card { background:#fff; border-radius:16px; padding:22px 24px; border:1px solid #f0f0f0; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.fa-stat-icon { width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem; }
.fa-stat-val  { font-size:1.45rem;font-weight:800;color:#111827;line-height:1.1; }
.fa-stat-lbl  { font-size:.78rem;color:#6b7280;margin-top:3px; }
.asset-badge  { padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;display:inline-flex;align-items:center;gap:4px; }
.depr-bar     { height:6px;border-radius:3px;background:#f3f4f6;overflow:hidden; }
.depr-bar-fill{ height:100%;border-radius:3px;transition:width .3s; }
.tfoot-total  { background:#f8fafc;font-weight:700;border-top:2px solid #e5e7eb; }
</style>

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4 style="margin:0;font-weight:800;">Fixed Assets</h4>
            <p style="opacity:.8;margin:2px 0 0;font-size:.85rem;">Track company assets, book values &amp; depreciation</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.fixed-assets.create') }}"
               class="btn btn-sm px-3"
               style="background:#4f46e5;color:#fff;border-radius:9px;font-size:.83rem;font-weight:600;border:none;">
                <i class="bi bi-plus-lg me-1"></i>Add Asset
            </a>
        </div>
    </div>
</div>

{{-- ── Summary Stats ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="fa-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="fa-stat-icon" style="background:#ede9fe;">
                    <i class="bi bi-boxes" style="color:#7c3aed;"></i>
                </div>
                <div>
                    <div class="fa-stat-val">{{ $totalCount }}</div>
                    <div class="fa-stat-lbl">Total Assets</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="fa-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="fa-stat-icon" style="background:#dbeafe;">
                    <i class="bi bi-cash-stack" style="color:#2563eb;"></i>
                </div>
                <div>
                    <div class="fa-stat-val" style="font-size:1.1rem;">{{ $fmt($totalPurchase) }}</div>
                    <div class="fa-stat-lbl">Purchase Value</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="fa-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="fa-stat-icon" style="background:#d1fae5;">
                    <i class="bi bi-graph-up" style="color:#059669;"></i>
                </div>
                <div>
                    <div class="fa-stat-val" style="font-size:1.1rem;">{{ $fmt($totalBookValue) }}</div>
                    <div class="fa-stat-lbl">Current Book Value</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="fa-stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="fa-stat-icon" style="background:#fee2e2;">
                    <i class="bi bi-arrow-down-circle" style="color:#dc2626;"></i>
                </div>
                <div>
                    <div class="fa-stat-val" style="font-size:1.1rem;">{{ $fmt($totalDepr) }}</div>
                    <div class="fa-stat-lbl">Accumulated Depr.</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter Bar ── --}}
<form method="GET" class="card-glass p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search name, code, serial…" value="{{ request('search') }}"
                   style="border-radius:8px;">
        </div>
        <div class="col-md-3">
            <select name="asset_type" class="form-select form-select-sm" style="border-radius:8px;">
                <option value="">All Types</option>
                @foreach(array_keys(\App\Models\FixedAsset::ASSET_TYPES) as $type)
                    <option value="{{ $type }}" {{ request('asset_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm" style="border-radius:8px;">
                <option value="">All Statuses</option>
                @foreach(\App\Models\FixedAsset::STATUSES as $key => $cfg)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary-grad w-100">Filter</button>
            <a href="{{ route('admin.fixed-assets.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </div>
    </div>
</form>

{{-- ── Assets Table ── --}}
<div class="table-card">
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th style="width:42px;">#</th>
                    <th>Asset</th>
                    <th>Type</th>
                    <th>Purchase Date</th>
                    <th class="text-end">Purchase Value</th>
                    <th class="text-end">Book Value</th>
                    <th style="width:120px;">Depreciation</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $i => $asset)
                @php
                    $tc  = $asset->type_config;
                    $sc  = $asset->status_config;
                    $dp  = $asset->depreciation_percent;
                    $bv  = $asset->book_value;
                    $dpColor = $dp >= 75 ? '#dc2626' : ($dp >= 50 ? '#d97706' : '#059669');
                @endphp
                <tr>
                    <td style="color:#9ca3af;font-size:.78rem;">{{ $assets->firstItem() + $i }}</td>
                    <td>
                        <div style="font-weight:700;font-size:.88rem;color:#111827;">{{ $asset->name }}</div>
                        <div style="font-size:.74rem;color:#6b7280;font-family:monospace;">
                            {{ $asset->asset_code }}
                            @if($asset->serial_number)
                                &nbsp;·&nbsp;{{ $asset->serial_number }}
                            @endif
                        </div>
                        @if($asset->location)
                            <div style="font-size:.72rem;color:#9ca3af;margin-top:1px;">
                                <i class="bi bi-geo-alt"></i> {{ $asset->location }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="asset-badge" style="color:{{ $tc['color'] }};background:{{ $tc['bg'] }};">
                            <i class="bi bi-{{ $tc['icon'] }}"></i> {{ $asset->asset_type }}
                        </span>
                    </td>
                    <td style="font-size:.83rem;color:#374151;">
                        {{ $asset->purchase_date->format('d M Y') }}
                        <div style="font-size:.72rem;color:#9ca3af;">{{ $asset->age_string }}</div>
                    </td>
                    <td class="text-end" style="font-weight:600;font-size:.88rem;color:#374151;">
                        {{ $fmt($asset->purchase_value) }}
                    </td>
                    <td class="text-end">
                        <span style="font-weight:700;font-size:.88rem;color:{{ $dpColor }};">
                            {{ $fmt($bv) }}
                        </span>
                        @if($asset->depreciation_method !== 'none')
                            <div style="font-size:.7rem;color:#9ca3af;">
                                -{{ $fmt($asset->accumulated_depreciation) }} depr.
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($asset->depreciation_method !== 'none')
                            <div style="font-size:.72rem;color:#6b7280;margin-bottom:4px;">{{ $dp }}%</div>
                            <div class="depr-bar">
                                <div class="depr-bar-fill" style="width:{{ $dp }}%;background:{{ $dpColor }};"></div>
                            </div>
                        @else
                            <span style="font-size:.72rem;color:#9ca3af;">No depreciation</span>
                        @endif
                    </td>
                    <td>
                        <span class="asset-badge" style="color:{{ $sc['color'] }};background:{{ $sc['bg'] }};">
                            {{ $sc['label'] }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('admin.fixed-assets.show', $asset) }}"
                               class="btn btn-xs btn-outline-primary" style="border-radius:7px;font-size:.74rem;padding:3px 8px;"
                               title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.fixed-assets.edit', $asset) }}"
                               class="btn btn-xs btn-outline-secondary" style="border-radius:7px;font-size:.74rem;padding:3px 8px;"
                               title="Edit"><i class="bi bi-pencil"></i></a>
                            @if(auth()->user()->role === 'super_admin')
                            <button type="button" onclick="deleteAsset({{ $asset->id }}, '{{ addslashes($asset->name) }}')"
                                    class="btn btn-xs btn-outline-danger" style="border-radius:7px;font-size:.74rem;padding:3px 8px;"
                                    title="Delete"><i class="bi bi-trash"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="bi bi-box2-heart"></i>
                            <p>No fixed assets found. <a href="{{ route('admin.fixed-assets.create') }}">Add your first asset.</a></p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($assets->count() > 0)
            <tfoot>
                <tr class="tfoot-total">
                    <td colspan="4" style="font-size:.83rem;color:#374151;">
                        <i class="bi bi-calculator me-1"></i>
                        Total ({{ $assets->total() }} assets)
                    </td>
                    <td class="text-end" style="color:#2563eb;">{{ $fmt($totalPurchase) }}</td>
                    <td class="text-end" style="color:#059669;">{{ $fmt($totalBookValue) }}</td>
                    <td colspan="3" style="font-size:.78rem;color:#dc2626;">
                        <i class="bi bi-arrow-down"></i> {{ $fmt($totalDepr) }} depreciated
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    @if($assets->hasPages())
    <div class="px-3 py-3 border-top" style="border-color:#f3f4f6!important;">
        {{ $assets->links() }}
    </div>
    @endif
</div>

{{-- Delete forms --}}
<div id="deleteFormsContainer"></div>

@endsection

@push('scripts')
<script>
function deleteAsset(id, name) {
    APP.confirm('Delete Asset', `Permanently delete "${name}"? This cannot be undone.`, () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/fixed-assets/${id}`;
        form.innerHTML = `@csrf @method('DELETE')`.replace('@csrf', '<input type="hidden" name="_token" value="{{ csrf_token() }}">').replace("@method('DELETE')", '<input type="hidden" name="_method" value="DELETE">');
        document.body.appendChild(form);
        form.submit();
    });
}
</script>
@endpush
