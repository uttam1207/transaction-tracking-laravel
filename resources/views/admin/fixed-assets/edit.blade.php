@extends('layouts.app')
@section('title', 'Edit — ' . $fixedAsset->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fixed-assets.index') }}">Fixed Assets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.fixed-assets.show', $fixedAsset) }}">{{ $fixedAsset->asset_code }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between" style="position:relative;z-index:1;">
        <div>
            <h4 style="margin:0;font-weight:800;">Edit Asset</h4>
            <p style="opacity:.8;margin:2px 0 0;font-size:.85rem;">{{ $fixedAsset->asset_code }} — {{ $fixedAsset->name }}</p>
        </div>
        <a href="{{ route('admin.fixed-assets.show', $fixedAsset) }}" class="btn btn-sm"
           style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.fixed-assets.update', $fixedAsset) }}">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-glass p-4 mb-4">
                <h6 style="font-weight:700;color:#374151;margin-bottom:18px;border-bottom:1px solid #f3f4f6;padding-bottom:10px;">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Asset Information
                </h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $fixedAsset->name) }}" required style="border-radius:9px;">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Asset Type <span class="text-danger">*</span></label>
                        <select name="asset_type" id="assetTypeSelect" class="form-select @error('asset_type') is-invalid @enderror" required style="border-radius:9px;">
                            @foreach(array_keys(\App\Models\FixedAsset::ASSET_TYPES) as $type)
                                <option value="{{ $type }}" {{ old('asset_type', $fixedAsset->asset_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('asset_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Serial / Tag Number</label>
                        <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $fixedAsset->serial_number) }}" style="border-radius:9px;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $fixedAsset->location) }}" style="border-radius:9px;">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Vendor / Supplier</label>
                        <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $fixedAsset->vendor) }}" style="border-radius:9px;">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" style="border-radius:9px;">{{ old('description', $fixedAsset->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-glass p-4">
                <h6 style="font-weight:700;color:#374151;margin-bottom:18px;border-bottom:1px solid #f3f4f6;padding-bottom:10px;">
                    <i class="bi bi-cash-coin me-2 text-success"></i>Financial Details
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror"
                               value="{{ old('purchase_date', $fixedAsset->purchase_date->format('Y-m-d')) }}" required style="border-radius:9px;">
                        @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Purchase Value (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="purchase_value" id="purchaseValue" class="form-control @error('purchase_value') is-invalid @enderror"
                               value="{{ old('purchase_value', $fixedAsset->purchase_value) }}" step="0.01" min="0" required style="border-radius:9px;">
                        @error('purchase_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Salvage Value (₹)</label>
                        <input type="number" name="salvage_value" id="salvageValue" class="form-control"
                               value="{{ old('salvage_value', $fixedAsset->salvage_value) }}" step="0.01" min="0" style="border-radius:9px;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Useful Life (Years) <span class="text-danger">*</span></label>
                        <input type="number" name="useful_life_years" id="usefulLife" class="form-control @error('useful_life_years') is-invalid @enderror"
                               value="{{ old('useful_life_years', $fixedAsset->useful_life_years) }}" min="1" max="99" required style="border-radius:9px;">
                        @error('useful_life_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-glass p-4 mb-4">
                <h6 style="font-weight:700;color:#374151;margin-bottom:18px;border-bottom:1px solid #f3f4f6;padding-bottom:10px;">
                    <i class="bi bi-graph-down-arrow me-2 text-warning"></i>Depreciation
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Method <span class="text-danger">*</span></label>
                        <select name="depreciation_method" id="deprMethod" class="form-select @error('depreciation_method') is-invalid @enderror" required style="border-radius:9px;">
                            <option value="straight_line"    {{ old('depreciation_method', $fixedAsset->depreciation_method) == 'straight_line'    ? 'selected' : '' }}>Straight Line (SLM)</option>
                            <option value="reducing_balance" {{ old('depreciation_method', $fixedAsset->depreciation_method) == 'reducing_balance' ? 'selected' : '' }}>Reducing Balance (WDV)</option>
                            <option value="none"             {{ old('depreciation_method', $fixedAsset->depreciation_method) == 'none' ? 'selected' : '' }}>None (Land / Non-Depreciable)</option>
                        </select>
                        @error('depreciation_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12" id="deprRateRow">
                        <label class="form-label fw-semibold">Annual Rate (%)</label>
                        <input type="number" name="depreciation_rate" id="deprRate" class="form-control"
                               value="{{ old('depreciation_rate', $fixedAsset->depreciation_rate) }}" step="0.01" min="0" max="100"
                               placeholder="Auto-computed if blank" style="border-radius:9px;">
                        <div class="form-text" id="deprRateHint">Leave blank to auto-compute.</div>
                    </div>
                </div>
            </div>

            <div class="card-glass p-4 mb-4">
                <h6 style="font-weight:700;color:#374151;margin-bottom:18px;border-bottom:1px solid #f3f4f6;padding-bottom:10px;">
                    <i class="bi bi-activity me-2 text-info"></i>Status
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="statusSelect" class="form-select @error('status') is-invalid @enderror" required style="border-radius:9px;">
                            @foreach(\App\Models\FixedAsset::STATUSES as $key => $cfg)
                                <option value="{{ $key }}" {{ old('status', $fixedAsset->status) == $key ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="disposalFields" style="display:{{ in_array($fixedAsset->status, ['disposed','written_off']) ? '' : 'none' }};" class="col-12">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Disposal Date</label>
                                <input type="date" name="disposal_date" class="form-control" style="border-radius:9px;"
                                       value="{{ old('disposal_date', $fixedAsset->disposal_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Disposal Value (₹)</label>
                                <input type="number" name="disposal_value" class="form-control" style="border-radius:9px;"
                                       value="{{ old('disposal_value', $fixedAsset->disposal_value) }}" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" style="border-radius:9px;">{{ old('notes', $fixedAsset->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary-grad w-100">
                    <i class="bi bi-check-lg me-1"></i>Save Changes
                </button>
                <a href="{{ route('admin.fixed-assets.show', $fixedAsset) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.getElementById('statusSelect').addEventListener('change', function() {
    document.getElementById('disposalFields').style.display =
        ['disposed','written_off'].includes(this.value) ? '' : 'none';
});
document.getElementById('deprMethod').addEventListener('change', function() {
    document.getElementById('deprRateRow').style.display = this.value === 'none' ? 'none' : '';
});
if (document.getElementById('deprMethod').value === 'none') {
    document.getElementById('deprRateRow').style.display = 'none';
}
</script>
@endpush
