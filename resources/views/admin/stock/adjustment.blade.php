@extends('layouts.app')
@section('title', 'Stock Adjustment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Stock</a></li>
    <li class="breadcrumb-item active">Adjustment</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Stock Adjustment</h4>
            <p>Correct inventory balances after physical count, damage or expiry</p>
        </div>
        <a href="{{ route('admin.stock.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#d97706,#ea580c);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-sliders" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Inventory Correction</div>
                        <div style="font-size:1.05rem;font-weight:800;color:#fff;letter-spacing:-.01em;">Stock Adjustment</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">Increase or decrease stock for audit, damage, expiry corrections</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.stock.adjustment.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <h6 class="form-section-label">A &mdash; Adjustment Details</h6>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                    value="{{ old('date', now()->toDateString()) }}" required>
                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Inventory Item <span class="text-danger">*</span></label>
                                <select name="inventory_item_id" id="itemSelect" class="form-select @error('inventory_item_id') is-invalid @enderror" required onchange="showUnit()">
                                    <option value="">&#8212; Select Item &#8212;</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}"
                                            data-unit="{{ $item->unit }}"
                                            data-available="{{ $item->available_quantity }}"
                                            @selected(old('inventory_item_id') == $item->id)>
                                            {{ $item->name }} &mdash; {{ $item->category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('inventory_item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="itemInfo" class="mt-1" style="font-size:.75rem;color:#6b7280;display:none;">
                                    Unit: <strong id="itemUnit"></strong> &nbsp;|&nbsp; Current Stock: <strong id="itemStock"></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Adjustment Type <span class="text-danger">*</span></label>
                                <select name="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                                    <option value="Increase" @selected(old('adjustment_type')==='Increase')>&#43; Increase Stock</option>
                                    <option value="Decrease" @selected(old('adjustment_type')==='Decrease')>&#8722; Decrease Stock</option>
                                </select>
                                @error('adjustment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Adjustment Quantity <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="quantity"
                                    class="form-control @error('quantity') is-invalid @enderror"
                                    placeholder="Quantity to adjust" value="{{ old('quantity') }}" required>
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">B &mdash; Reason &amp; Notes</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Reason for Adjustment <span class="text-danger">*</span></label>
                                <select name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                                    <option value="Physical Stock Verification Audit" @selected(old('reason')==='Physical Stock Verification Audit')>Physical Stock Verification Audit</option>
                                    <option value="Damaged / Spilled Fodder" @selected(old('reason')==='Damaged / Spilled Fodder')>Damaged / Spilled Fodder</option>
                                    <option value="Expired Medicine / Chemicals" @selected(old('reason')==='Expired Medicine / Chemicals')>Expired Medicine / Chemicals</option>
                                    <option value="Correction of Entry Error" @selected(old('reason')==='Correction of Entry Error')>Correction of Entry Error</option>
                                    <option value="Other" @selected(old('reason')==='Other')>Other</option>
                                </select>
                                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Remarks / Explanatory Notes</label>
                                <textarea name="remarks" class="form-control" rows="2"
                                    placeholder="Provide context for audit records&#8230;">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5" style="background:linear-gradient(135deg,#d97706,#ea580c);">
                            <i class="bi bi-check-lg me-1"></i>Save Adjustment
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showUnit() {
    const sel = document.getElementById('itemSelect');
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('itemInfo');
    if (opt.value) {
        document.getElementById('itemUnit').textContent = opt.dataset.unit;
        document.getElementById('itemStock').textContent = parseFloat(opt.dataset.available).toFixed(2) + ' ' + opt.dataset.unit;
        info.style.display = '';
    } else {
        info.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', showUnit);
</script>
@endpush