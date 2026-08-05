@extends('layouts.app')
@section('title', 'Record Stock Out')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Stock</a></li>
    <li class="breadcrumb-item active">Stock Out</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Record Stock Out</h4>
            <p>Log consumed or issued inventory &mdash; feeding, treatment or maintenance</p>
        </div>
        <a href="{{ route('admin.stock.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#dc2626,#b91c1c);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-arrow-up-circle-fill" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Outgoing Inventory</div>
                        <div style="font-size:1.05rem;font-weight:800;color:#fff;letter-spacing:-.01em;">Stock Out Entry</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">Record consumed or issued stock</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.stock.out.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <h6 class="form-section-label">A &mdash; Entry Details</h6>
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
                                            @selected(old('inventory_item_id', request('item_id')) == $item->id)>
                                            {{ $item->name }} &mdash; {{ $item->category }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('inventory_item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="itemInfo" class="mt-1" style="font-size:.75rem;color:#6b7280;display:none;">
                                    Unit: <strong id="itemUnit"></strong> &nbsp;|&nbsp; Available: <strong id="itemStock" style="color:#059669;"></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Quantity Consumed / Issued <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="quantity"
                                    class="form-control @error('quantity') is-invalid @enderror"
                                    placeholder="e.g. 180" value="{{ old('quantity') }}" required>
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Purpose / Usage <span class="text-danger">*</span></label>
                                <select name="source_purpose" class="form-select @error('source_purpose') is-invalid @enderror" required>
                                    <option value="Daily Feed Feeding" @selected(old('source_purpose')==='Daily Feed Feeding')>Daily Cattle Feeding</option>
                                    <option value="Veterinary Treatment" @selected(old('source_purpose')==='Veterinary Treatment')>Veterinary / Treatment</option>
                                    <option value="Maintenance" @selected(old('source_purpose')==='Maintenance')>Equipment / Maintenance</option>
                                    <option value="Cleaning" @selected(old('source_purpose')==='Cleaning')>Cleaning &amp; Sanitation</option>
                                    <option value="Office Usage" @selected(old('source_purpose')==='Office Usage')>Office / Consumables</option>
                                </select>
                                @error('source_purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">B &mdash; Additional Info</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Issued To (Employee / Department)</label>
                                <input type="text" name="issued_to_or_vendor" class="form-control"
                                    placeholder="e.g. Akhilesh / Shed No. 1"
                                    value="{{ old('issued_to_or_vendor') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Remarks / Notes</label>
                                <textarea name="remarks" class="form-control" rows="2"
                                    placeholder="Feeding session details, animal IDs treated&#8230;">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">
                            <i class="bi bi-check-lg me-1"></i>Save Stock Out
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
        const avail = parseFloat(opt.dataset.available).toFixed(2);
        const stockEl = document.getElementById('itemStock');
        stockEl.textContent = avail + ' ' + opt.dataset.unit;
        stockEl.style.color = parseFloat(avail) <= 0 ? '#dc2626' : '#059669';
        info.style.display = '';
    } else {
        info.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', showUnit);
</script>
@endpush