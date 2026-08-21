@extends('layouts.app')
@section('title', 'Add Fixed Asset')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.fixed-assets.index') }}">Fixed Assets</a></li>
    <li class="breadcrumb-item active">Add Asset</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between" style="position:relative;z-index:1;">
        <div>
            <h4 style="margin:0;font-weight:800;">Register Fixed Asset</h4>
            <p style="opacity:.8;margin:2px 0 0;font-size:.85rem;">Add a new company asset with depreciation details</p>
        </div>
        <a href="{{ route('admin.fixed-assets.index') }}" class="btn btn-sm"
           style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.fixed-assets.store') }}">
    @csrf

    <div class="row g-4">
        {{-- Left: Basic Info --}}
        <div class="col-lg-7">
            <div class="card-glass p-4 mb-4">
                <h6 style="font-weight:700;color:#374151;margin-bottom:18px;border-bottom:1px solid #f3f4f6;padding-bottom:10px;">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Asset Information
                </h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. Tractor MF 241" required
                               style="border-radius:9px;">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Asset Type <span class="text-danger">*</span></label>
                        <select name="asset_type" id="assetTypeSelect" class="form-select @error('asset_type') is-invalid @enderror" required style="border-radius:9px;">
                            <option value="">— Select —</option>
                            @foreach(array_keys(\App\Models\FixedAsset::ASSET_TYPES) as $type)
                                <option value="{{ $type }}" {{ old('asset_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('asset_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Serial / Tag Number</label>
                        <input type="text" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror"
                               value="{{ old('serial_number') }}" placeholder="Optional" style="border-radius:9px;">
                        @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Location</label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                               value="{{ old('location') }}" placeholder="e.g. Farm A, Shed B" style="border-radius:9px;">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Vendor / Supplier</label>
                        <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror"
                               value="{{ old('vendor') }}" placeholder="Seller or manufacturer name" style="border-radius:9px;">
                        @error('vendor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="2" placeholder="Asset details, model, specifications…"
                                  style="border-radius:9px;">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                               value="{{ old('purchase_date') }}" required style="border-radius:9px;">
                        @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Purchase Value (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="purchase_value" id="purchaseValue" class="form-control @error('purchase_value') is-invalid @enderror"
                               value="{{ old('purchase_value') }}" step="0.01" min="0" required
                               placeholder="0.00" style="border-radius:9px;">
                        @error('purchase_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Salvage / Residual Value (₹)</label>
                        <input type="number" name="salvage_value" id="salvageValue" class="form-control @error('salvage_value') is-invalid @enderror"
                               value="{{ old('salvage_value', 0) }}" step="0.01" min="0"
                               placeholder="0.00" style="border-radius:9px;">
                        <div class="form-text">Estimated value at end of useful life</div>
                        @error('salvage_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Useful Life (Years) <span class="text-danger">*</span></label>
                        <input type="number" name="useful_life_years" id="usefulLife" class="form-control @error('useful_life_years') is-invalid @enderror"
                               value="{{ old('useful_life_years', 5) }}" min="1" max="99" required style="border-radius:9px;">
                        @error('useful_life_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Depreciation + Status --}}
        <div class="col-lg-5">
            <div class="card-glass p-4 mb-4">
                <h6 style="font-weight:700;color:#374151;margin-bottom:18px;border-bottom:1px solid #f3f4f6;padding-bottom:10px;">
                    <i class="bi bi-graph-down-arrow me-2 text-warning"></i>Depreciation
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Method <span class="text-danger">*</span></label>
                        <select name="depreciation_method" id="deprMethod" class="form-select @error('depreciation_method') is-invalid @enderror" required style="border-radius:9px;">
                            <option value="straight_line"   {{ old('depreciation_method','straight_line') == 'straight_line'    ? 'selected' : '' }}>Straight Line (SLM)</option>
                            <option value="reducing_balance"{{ old('depreciation_method') == 'reducing_balance' ? 'selected' : '' }}>Reducing Balance (WDV)</option>
                            <option value="none"            {{ old('depreciation_method') == 'none' ? 'selected' : '' }}>None (Land / Non-Depreciable)</option>
                        </select>
                        @error('depreciation_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12" id="deprRateRow">
                        <label class="form-label fw-semibold">Annual Depreciation Rate (%)</label>
                        <input type="number" name="depreciation_rate" id="deprRate" class="form-control @error('depreciation_rate') is-invalid @enderror"
                               value="{{ old('depreciation_rate') }}" step="0.01" min="0" max="100"
                               placeholder="Auto-computed if blank" style="border-radius:9px;">
                        <div class="form-text" id="deprRateHint">Leave blank to auto-compute from useful life.</div>
                        @error('depreciation_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    {{-- Live preview --}}
                    <div class="col-12" id="deprPreview" style="display:none;">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px;font-size:.83rem;">
                            <div class="d-flex justify-content-between mb-1">
                                <span style="color:#6b7280;">Annual Depreciation</span>
                                <span id="prevAnnual" style="font-weight:700;color:#374151;">—</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span style="color:#6b7280;">Book Value after Year 1</span>
                                <span id="prevY1BV" style="font-weight:700;color:#059669;">—</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-glass p-4 mb-4">
                <h6 style="font-weight:700;color:#374151;margin-bottom:18px;border-bottom:1px solid #f3f4f6;padding-bottom:10px;">
                    <i class="bi bi-activity me-2 text-info"></i>Status
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Asset Status <span class="text-danger">*</span></label>
                        <select name="status" id="statusSelect" class="form-select @error('status') is-invalid @enderror" required style="border-radius:9px;">
                            @foreach(\App\Models\FixedAsset::STATUSES as $key => $cfg)
                                <option value="{{ $key }}" {{ old('status', 'active') == $key ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div id="disposalFields" style="display:none;" class="col-12">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Disposal Date</label>
                                <input type="date" name="disposal_date" class="form-control" style="border-radius:9px;"
                                       value="{{ old('disposal_date') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Disposal Value (₹)</label>
                                <input type="number" name="disposal_value" class="form-control" style="border-radius:9px;"
                                       value="{{ old('disposal_value') }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="3" placeholder="Any additional notes…"
                                  style="border-radius:9px;">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary-grad w-100">
                    <i class="bi bi-check-lg me-1"></i>Register Asset
                </button>
                <a href="{{ route('admin.fixed-assets.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
const fmt = v => '₹' + parseFloat(v||0).toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2});

function updateDeprPreview() {
    const pv      = parseFloat(document.getElementById('purchaseValue').value) || 0;
    const sv      = parseFloat(document.getElementById('salvageValue').value) || 0;
    const life    = parseInt(document.getElementById('usefulLife').value) || 1;
    const method  = document.getElementById('deprMethod').value;
    const rateEl  = document.getElementById('deprRate');
    const preview = document.getElementById('deprPreview');
    const rateRow = document.getElementById('deprRateRow');

    if (method === 'none') {
        rateRow.style.display = 'none';
        preview.style.display = 'none';
        return;
    }
    rateRow.style.display = '';

    if (pv <= 0) { preview.style.display = 'none'; return; }

    let annual, y1bv;
    if (method === 'straight_line') {
        document.getElementById('deprRateHint').textContent = 'Leave blank to auto-compute (100 / useful life years).';
        annual = (pv - sv) / life;
        y1bv   = pv - annual;
    } else {
        document.getElementById('deprRateHint').textContent = 'Required for reducing balance method.';
        const rate = parseFloat(rateEl.value) || 0;
        if (rate <= 0) { preview.style.display = 'none'; return; }
        annual = pv * (rate / 100);
        y1bv   = pv - annual;
    }

    document.getElementById('prevAnnual').textContent = fmt(annual);
    document.getElementById('prevY1BV').textContent   = fmt(Math.max(sv, y1bv));
    preview.style.display = '';
}

// Status → show/hide disposal fields
document.getElementById('statusSelect').addEventListener('change', function() {
    document.getElementById('disposalFields').style.display =
        ['disposed','written_off'].includes(this.value) ? '' : 'none';
});

['purchaseValue','salvageValue','usefulLife','deprRate'].forEach(id => {
    document.getElementById(id).addEventListener('input', updateDeprPreview);
});
document.getElementById('deprMethod').addEventListener('change', updateDeprPreview);
updateDeprPreview();
</script>
@endpush
