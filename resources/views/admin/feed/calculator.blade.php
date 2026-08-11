@extends('layouts.app')
@section('title', 'Feed Calculator & Alerts')

@section('breadcrumb')
    <li class="breadcrumb-item active">Feed Calculator</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Feed Calculator &amp; Alerts</h4>
            <p>Daily feed requirement auto-calculation, stock depletion forecast &amp; proactive shortage alerts</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.feed.groups.sync') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary px-4" title="Sync animal group counts from actual animal records">
                    <i class="bi bi-arrow-repeat me-1"></i>Sync from Animals
                </button>
            </form>
            <a href="{{ route('admin.stock.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-boxes me-1"></i>View Stock
            </a>
        </div>
    </div>
</div>

{{-- Alerts --}}
@if(count($data['alerts']) > 0)
    <div class="mb-4">
        @foreach($data['alerts'] as $alert)
            <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show d-flex align-items-center gap-3 mb-2" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07);">
                <i class="bi {{ $alert['icon'] }}" style="font-size:1.3rem;flex-shrink:0;"></i>
                <div style="font-size:.87rem;font-weight:600;">{{ $alert['message'] }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-3 mb-4" style="border-radius:12px;border:none;box-shadow:0 2px 8px rgba(0,0,0,.07);">
        <i class="bi bi-check-circle-fill" style="font-size:1.3rem;flex-shrink:0;"></i>
        <div style="font-size:.87rem;font-weight:600;">Feed Stock Optimal &mdash; All feed items have sufficient stock for upcoming feeding cycles.</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" style="border-radius:12px;">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" style="border-radius:12px;">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0d9488,#0891b2);">
            <i class="bi bi-people-fill kpi-icon"></i>
            <div class="kpi-value">{{ $data['total_animals'] }}</div>
            <div class="kpi-label">Total Cattle</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-droplet-fill kpi-icon"></i>
            <div class="kpi-value">{{ $data['milking_animals'] }}</div>
            <div class="kpi-label">Milking</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#4f46e5);">
            <i class="bi bi-heart-fill kpi-icon"></i>
            <div class="kpi-value">{{ $data['pregnant_animals'] }}</div>
            <div class="kpi-label">Pregnant</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#6b7280,#4b5563);">
            <i class="bi bi-moon-fill kpi-icon"></i>
            <div class="kpi-value">{{ $data['dry_animals'] }}</div>
            <div class="kpi-label">Dry</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-star-fill kpi-icon"></i>
            <div class="kpi-value">{{ $data['calves'] }}</div>
            <div class="kpi-label">Calves</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
            <i class="bi bi-calculator-fill kpi-icon"></i>
            <div class="kpi-value">{{ number_format(array_sum($data['totals']), 0) }}</div>
            <div class="kpi-label">Daily Feed (kg)</div>
        </div>
    </div>
</div>

{{-- Feed Depletion Forecast --}}
<div class="card-glass mb-4">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="bi bi-graph-down me-2 text-primary"></i>Feed Depletion &amp; Stock Forecast</h6>
        <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;">Auto-Calculated</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Feed Item</th>
                    <th class="text-end">Available Stock</th>
                    <th class="text-end">Daily Need</th>
                    <th class="text-end">Weekly Need</th>
                    <th class="text-end">Monthly Need</th>
                    <th class="text-center">Days Left</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['stock_comparison'] as $feedName => $row)
                    @php
                        $daysLeft = $row['days_left'];
                        $daysColor = $daysLeft <= 3 ? '#dc2626' : ($daysLeft <= 7 ? '#d97706' : '#059669');
                        $statusColor = $daysLeft <= 3 ? 'spill-danger' : ($row['available'] <= $row['min_stock'] ? 'spill-warning' : 'spill-success');
                        $statusText  = $daysLeft <= 3 ? 'Critical' : ($row['available'] <= $row['min_stock'] ? 'Below Reorder' : 'Sufficient');
                    @endphp
                    <tr>
                        <td class="fw-bold" style="font-size:.87rem;">{{ $feedName }}</td>
                        <td class="text-end fw-bold" style="color:var(--primary);font-size:.88rem;">
                            {{ number_format($row['available'], 1) }} <span style="font-size:.72rem;color:#9ca3af;">{{ $row['unit'] }}</span>
                        </td>
                        <td class="text-end fw-bold" style="font-size:.88rem;">{{ number_format($row['daily_need'], 1) }} <span style="font-size:.72rem;color:#9ca3af;">{{ $row['unit'] }}</span></td>
                        <td class="text-end" style="font-size:.82rem;color:#6b7280;">{{ number_format($row['weekly_need'], 1) }}</td>
                        <td class="text-end" style="font-size:.82rem;color:#6b7280;">{{ number_format($row['monthly_need'], 1) }}</td>
                        <td class="text-center">
                            <span class="fw-bold" style="font-size:.9rem;color:{{ $daysColor }};">
                                @if($daysLeft >= 999) &infin; @else {{ $daysLeft }} days @endif
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="spill {{ $statusColor }}" style="font-size:.72rem;">{{ $statusText }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="bi bi-calculator"></i>
                            <p>No feed plans configured yet. Add animal groups and feed items below.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Management Section --}}
<div class="row g-4">

    {{-- Animal Groups Management (Left) --}}
    <div class="col-md-5">
        <div class="card-glass overflow-hidden">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>Animal Groups</h6>
                <button class="btn btn-sm btn-primary-grad px-3" style="font-size:.78rem;border-radius:8px;" onclick="toggleSection('addGroupForm', this)">
                    <i class="bi bi-plus-lg me-1"></i>Add Group
                </button>
            </div>

            {{-- Add Group Form --}}
            <div id="addGroupForm" style="display:none;background:#f8fafc;border-bottom:1px solid #e5e7eb;" class="p-3">
                @if($errors->has('name') || $errors->has('group_key'))
                    <div class="alert alert-danger py-2 mb-2 small">
                        @foreach($errors->only(['name','group_key','head_count']) as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif
                <form action="{{ route('admin.feed.groups.store') }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-12">
                            <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                placeholder="Group Name (e.g. Milking Buffaloes)" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-7">
                            <input type="text" name="group_key" class="form-control form-control-sm @error('group_key') is-invalid @enderror"
                                placeholder="Key (e.g. lactating)" value="{{ old('group_key') }}" required>
                            <div style="font-size:.68rem;color:#9ca3af;margin-top:2px;">Unique slug, no spaces</div>
                        </div>
                        <div class="col-5">
                            <input type="number" min="0" name="head_count" class="form-control form-control-sm"
                                placeholder="Head Count" value="{{ old('head_count', 0) }}" required>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary-grad btn-sm flex-fill">
                                <i class="bi bi-save me-1"></i>Save Group
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="toggleSection('addGroupForm', null)">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Groups Table --}}
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th class="text-center" style="width:130px;">Head Count</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($animalGroups as $ag)
                            <tr>
                                <td>
                                    <div class="fw-semibold" style="font-size:.87rem;">{{ $ag->name }}</div>
                                    <code style="font-size:.7rem;color:#9ca3af;">{{ $ag->group_key }}</code>
                                </td>
                                <td>
                                    <form action="{{ route('admin.feed.groups.update', $ag) }}" method="POST" class="d-flex gap-1 align-items-center justify-content-center">
                                        @csrf
                                        <input type="number" min="0" name="head_count" class="form-control form-control-sm text-center"
                                            value="{{ $ag->head_count }}" style="width:65px;border-radius:7px;">
                                        <button type="submit" class="act-btn act-view" title="Save" style="flex-shrink:0;">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{ route('admin.feed.groups.destroy', $ag) }}" method="POST"
                                        onsubmit="return confirm('Delete group \'{{ addslashes($ag->name) }}\' and all its feed plans?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="act-btn act-delete" title="Delete">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <p>No animal groups yet</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Feed Plan Configuration (Right) --}}
    <div class="col-md-7">
        <div class="card-glass overflow-hidden">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-sliders2 me-2 text-primary"></i>Feed Plan &mdash; Daily kg per Animal</h6>
                <button class="btn btn-sm btn-primary-grad px-3" style="font-size:.78rem;border-radius:8px;" onclick="toggleSection('addPlanForm', this)">
                    <i class="bi bi-plus-lg me-1"></i>Add Feed Item
                </button>
            </div>

            {{-- Add Feed Item Form --}}
            <div id="addPlanForm" style="display:none;background:#f8fafc;border-bottom:1px solid #e5e7eb;" class="p-3">
                <form action="{{ route('admin.feed.plans.item.store') }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Animal Group <span class="text-danger">*</span></label>
                            <select name="animal_group_id" class="form-select form-select-sm @error('animal_group_id') is-invalid @enderror" required>
                                <option value="">&#8212; Select Group &#8212;</option>
                                @foreach($animalGroups as $ag)
                                    <option value="{{ $ag->id }}" @selected(old('animal_group_id')==$ag->id)>{{ $ag->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Feed Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="feed_item_name" class="form-control form-control-sm @error('feed_item_name') is-invalid @enderror"
                                placeholder="e.g. Green Fodder, Concentrate" value="{{ old('feed_item_name') }}"
                                list="feed-items-list" required>
                            <datalist id="feed-items-list">
                                <option value="Green Fodder">
                                <option value="Dry Fodder">
                                <option value="Concentrate">
                                <option value="Mineral Mixture">
                                <option value="Silage">
                                <option value="Bran">
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Daily kg/Head <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="quantity_per_animal_kg"
                                class="form-control form-control-sm @error('quantity_per_animal_kg') is-invalid @enderror"
                                placeholder="e.g. 5.0" value="{{ old('quantity_per_animal_kg') }}" required>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary-grad btn-sm flex-fill">
                                <i class="bi bi-save me-1"></i>Add to Feed Plan
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="toggleSection('addPlanForm', null)">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Feed Plans Table --}}
            {{-- Use HTML form attribute trick to allow delete mini-forms within table rows --}}
            <form id="batchSaveForm" action="{{ route('admin.feed.plans.update') }}" method="POST">
                @csrf
            </form>

            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Animal Group</th>
                            <th>Feed Item</th>
                            <th class="text-end" style="width:130px;">Daily kg/Head</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $planIdx = 0; @endphp
                        @forelse($animalGroups as $ag)
                            @foreach($ag->feedPlans as $fp)
                                <tr>
                                    <td>
                                        <span class="spill spill-primary" style="font-size:.72rem;">{{ $ag->name }}</span>
                                    </td>
                                    <td class="fw-semibold" style="font-size:.85rem;">{{ $fp->feed_item_name }}</td>
                                    <td class="text-end">
                                        <input type="hidden" name="plans[{{ $planIdx }}][id]" value="{{ $fp->id }}" form="batchSaveForm">
                                        <input type="number" step="0.01" min="0"
                                            name="plans[{{ $planIdx }}][quantity_per_animal_kg]"
                                            value="{{ $fp->quantity_per_animal_kg }}"
                                            form="batchSaveForm"
                                            class="form-control form-control-sm text-end d-inline-block"
                                            style="width:90px;border-radius:7px;">
                                        @php $planIdx++; @endphp
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.feed.plans.item.destroy', $fp) }}" method="POST"
                                            onsubmit="return confirm('Remove \'{{ addslashes($fp->feed_item_name) }}\' from {{ addslashes($ag->name) }} feed plan?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="act-btn act-delete" title="Remove">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                        @endforelse
                        @if($animalGroups->sum(fn($ag) => $ag->feedPlans->count()) === 0)
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="bi bi-sliders2"></i>
                                    <p>No feed plan items. Click "Add Feed Item" above.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($animalGroups->sum(fn($ag) => $ag->feedPlans->count()) > 0)
                <div class="px-4 py-3 border-top d-flex justify-content-end">
                    <button type="submit" form="batchSaveForm" class="btn btn-primary-grad px-5">
                        <i class="bi bi-save me-1"></i>Save All Quantities
                    </button>
                </div>
            @endif
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function toggleSection(id, btn) {
    const el = document.getElementById(id);
    const visible = el.style.display !== 'none';
    el.style.display = visible ? 'none' : 'block';
    if (btn) {
        btn.innerHTML = visible
            ? '<i class="bi bi-plus-lg me-1"></i>' + (id === 'addGroupForm' ? 'Add Group' : 'Add Feed Item')
            : '<i class="bi bi-x-lg me-1"></i>Cancel';
    }
}

// Auto-open add form if there were validation errors
@if($errors->hasAny(['name','group_key','head_count']))
    document.addEventListener('DOMContentLoaded', () => toggleSection('addGroupForm', document.querySelector('[onclick*="addGroupForm"]')));
@endif
@if($errors->hasAny(['animal_group_id','feed_item_name','quantity_per_animal_kg']))
    document.addEventListener('DOMContentLoaded', () => toggleSection('addPlanForm', document.querySelector('[onclick*="addPlanForm"]')));
@endif
</script>
@endpush