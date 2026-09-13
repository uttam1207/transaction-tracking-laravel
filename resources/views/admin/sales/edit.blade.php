@extends('layouts.app')
@section('title', 'Edit Invoice — ' . $salesOrder->invoice_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sales.show', $salesOrder) }}">{{ $salesOrder->invoice_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Sales Invoice</h4>
            <p>{{ $salesOrder->invoice_number }} &mdash; {{ $salesOrder->item_type }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales.show', $salesOrder) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#059669,#16a34a);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-receipt-cutoff" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Sales Invoice</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $salesOrder->invoice_number }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ $salesOrder->item_type }} &mdash; {{ $salesOrder->sale_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.sales.update', $salesOrder) }}" method="POST" id="saleForm">
                    @csrf @method('PUT')

                    {{-- A — Invoice Details --}}
                    <div class="mb-4">
                        <h6 class="form-section-label">A — Invoice Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Invoice Number <span class="text-danger">*</span></label>
                                <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror"
                                    value="{{ old('invoice_number', $salesOrder->invoice_number) }}" required>
                                @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                                <input type="date" name="sale_date" class="form-control @error('sale_date') is-invalid @enderror"
                                    value="{{ old('sale_date', $salesOrder->sale_date->toDateString()) }}" required>
                                @error('sale_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Customer</label>
                                <select name="crm_customer_id" class="form-select @error('crm_customer_id') is-invalid @enderror">
                                    <option value="">— Walk-in / Retail —</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" @selected(old('crm_customer_id', $salesOrder->crm_customer_id) == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('crm_customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Item Type <span class="text-danger">*</span>
                                    <a href="{{ route('admin.sales.item-types.index') }}" class="ms-2" style="font-size:.75rem;font-weight:400;" target="_blank">
                                        <i class="bi bi-gear-fill me-1"></i>Manage Types
                                    </a>
                                </label>
                                <select name="sale_item_type_id" id="saleItemType" class="form-select @error('sale_item_type_id') is-invalid @enderror" required>
                                    <option value="">— Select Type —</option>
                                    @foreach($itemTypes as $t)
                                        <option value="{{ $t->id }}"
                                            data-milk="{{ $t->is_milk_type ? 'true' : 'false' }}"
                                            @selected(old('sale_item_type_id', $salesOrder->sale_item_type_id) == $t->id)>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sale_item_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    {{-- B — Pricing --}}
                    <div class="mb-4">
                        <h6 class="form-section-label">B — Pricing</h6>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Quantity (Litres) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="quantity" id="saleQty"
                                    class="form-control @error('quantity') is-invalid @enderror"
                                    value="{{ old('quantity', $salesOrder->quantity) }}" required>
                                @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Regular rate (non-milk) --}}
                            <div class="col-md-6" id="rateRow">
                                <label class="form-label fw-semibold">Rate (&#8377;/unit) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="rate" id="saleRate"
                                    class="form-control @error('rate') is-invalid @enderror"
                                    value="{{ old('rate', $salesOrder->saleItemType?->is_milk_type ? '' : $salesOrder->rate) }}">
                                @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Milk: Fat % --}}
                            <div class="col-md-4 d-none" id="fatRow">
                                <label class="form-label fw-semibold">
                                    Fat % <span class="text-danger">*</span>
                                    <span class="ms-1" style="font-size:.72rem;color:#6b7280;font-weight:400;">(e.g. 3.5)</span>
                                </label>
                                <input type="number" step="0.01" min="0.01" max="100" name="fat_percentage" id="saleFat"
                                    class="form-control @error('fat_percentage') is-invalid @enderror"
                                    value="{{ old('fat_percentage', $salesOrder->fat_percentage) }}">
                                @error('fat_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Milk: Fat Rate --}}
                            <div class="col-md-4 d-none" id="fatRateRow">
                                <label class="form-label fw-semibold">
                                    Fat Rate (&#8377;) <span class="text-danger">*</span>
                                    <span class="ms-1" style="font-size:.72rem;color:#6b7280;font-weight:400;">(per fat point/L)</span>
                                </label>
                                <input type="number" step="0.01" min="0.01" name="fat_rate" id="saleFatRate"
                                    class="form-control @error('fat_rate') is-invalid @enderror"
                                    value="{{ old('fat_rate', $salesOrder->fat_rate) }}">
                                @error('fat_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Computed total preview --}}
                            <div class="col-md-4 d-none" id="milkTotalPreviewRow">
                                <label class="form-label fw-semibold" style="color:#059669;">Calculated Amount</label>
                                <div class="form-control" id="milkTotalPreview" style="background:#f0fdf4;color:#059669;font-weight:700;">—</div>
                                <div style="font-size:.7rem;color:#6b7280;margin-top:3px;">Qty &times; Fat% &times; Fat Rate</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                                <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                    @foreach(['Paid','Pending','Partial'] as $s)
                                        <option value="{{ $s }}" @selected(old('payment_status', $salesOrder->payment_status) === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                @error('payment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.sales.show', $salesOrder) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Update Invoice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const typeSelect    = document.getElementById('saleItemType');
    const rateRow       = document.getElementById('rateRow');
    const fatRow        = document.getElementById('fatRow');
    const fatRateRow    = document.getElementById('fatRateRow');
    const previewRow    = document.getElementById('milkTotalPreviewRow');
    const previewEl     = document.getElementById('milkTotalPreview');
    const qtyInput      = document.getElementById('saleQty');
    const rateInput     = document.getElementById('saleRate');
    const fatInput      = document.getElementById('saleFat');
    const fatRateInput  = document.getElementById('saleFatRate');

    function isMilk() {
        const opt = typeSelect.options[typeSelect.selectedIndex];
        return opt && opt.dataset.milk === 'true';
    }

    function toggleFields() {
        const milk = isMilk();
        rateRow.classList.toggle('d-none', milk);
        fatRow.classList.toggle('d-none', !milk);
        fatRateRow.classList.toggle('d-none', !milk);
        previewRow.classList.toggle('d-none', !milk);

        rateInput.required    = !milk;
        fatInput.required     = milk;
        fatRateInput.required = milk;

        if (!milk) {
            fatInput.value     = '';
            fatRateInput.value = '';
        }
        recalc();
    }

    function recalc() {
        if (!isMilk()) return;
        const qty     = parseFloat(qtyInput.value)     || 0;
        const fat     = parseFloat(fatInput.value)     || 0;
        const fatRate = parseFloat(fatRateInput.value) || 0;
        const total   = qty * fat * fatRate;
        previewEl.textContent = total > 0 ? '₹' + total.toFixed(2) : '—';
    }

    typeSelect.addEventListener('change', toggleFields);
    qtyInput.addEventListener('input', recalc);
    fatInput.addEventListener('input', recalc);
    fatRateInput.addEventListener('input', recalc);

    // On load: trigger toggle so existing values are shown correctly.
    // If the selected option has no type (e.g. item type was deactivated) but the
    // record already has fat data, force-show the fat fields using the PHP flag.
    const hasFatData = {{ $salesOrder->fat_percentage ? 'true' : 'false' }};
    if (hasFatData && !isMilk()) {
        rateRow.classList.add('d-none');
        fatRow.classList.remove('d-none');
        fatRateRow.classList.remove('d-none');
        previewRow.classList.remove('d-none');
        rateInput.required    = false;
        fatInput.required     = true;
        fatRateInput.required = true;
        recalc();
    } else {
        toggleFields();
    }
})();
</script>
@endpush

@endsection
