@extends('layouts.app')
@section('title', 'New Purchase Request')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchase-requests.index') }}">Purchase Requests</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>New Purchase Request</h4>
            <p>Create a new internal purchase request for approval</p>
        </div>
        <a href="{{ route('admin.purchase-requests.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm">
    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('admin.purchase-requests.store') }}">
    @csrf
    <div class="row g-4">

        {{-- Request Details --}}
        <div class="col-12">
            <div class="card-glass p-4">
                <h6 class="form-section-label">A — Request Details</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">— Select —</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Required Date</label>
                        <input type="date" name="required_date" class="form-control" value="{{ old('required_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select" required>
                            @foreach (['low','normal','high','urgent'] as $p)
                                <option value="{{ $p }}" @selected(old('priority','normal') === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Purpose / Justification</label>
                        <input type="text" name="purpose" class="form-control"
                            placeholder="Why is this needed?" value="{{ old('purpose') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="col-12">
            <div class="card-glass overflow-hidden">
                <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                    <h6 class="fw-bold mb-0" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;color:#0d9488;">
                        <i class="bi bi-list-ul me-2"></i>B — Items Requested
                    </h6>
                    <button type="button" class="btn btn-sm btn-outline-primary px-3" onclick="addItem()">
                        <i class="bi bi-plus-lg me-1"></i>Add Item
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th style="min-width:200px">Item Name <span class="text-danger">*</span></th>
                                <th>Description</th>
                                <th style="width:100px">Qty <span class="text-danger">*</span></th>
                                <th style="width:80px">Unit</th>
                                <th style="width:140px">Est. Unit Price</th>
                                <th style="width:140px">Est. Total</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.purchase-requests.index') }}" class="btn btn-outline-secondary px-4" style="border-radius:10px;">Cancel</a>
            <button type="submit" class="btn btn-primary-grad px-5" style="border-radius:10px;">
                <i class="bi bi-send me-2"></i>Create Purchase Request
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
let idx = 0;

function addItem() {
    const i = idx++;
    const row = `<tr id="item_${i}">
        <td><input type="text" name="items[${i}][item_name]" class="form-control form-control-sm" required placeholder="Item name"></td>
        <td><input type="text" name="items[${i}][description]" class="form-control form-control-sm" placeholder="Specs / details"></td>
        <td><input type="number" name="items[${i}][quantity]" class="form-control form-control-sm" step="0.01" min="0.01" required value="1" oninput="calcTotal(${i})"></td>
        <td><input type="text" name="items[${i}][unit]" class="form-control form-control-sm" value="pcs"></td>
        <td><input type="number" name="items[${i}][estimated_unit_price]" class="form-control form-control-sm text-end" step="0.01" min="0" placeholder="0.00" oninput="calcTotal(${i})" id="up_${i}"></td>
        <td><input type="number" name="items[${i}][estimated_total]" class="form-control form-control-sm text-end" step="0.01" min="0" placeholder="0.00" id="et_${i}" readonly style="background:#f5f7fa;"></td>
        <td><button type="button" class="act-btn" style="color:#dc2626;" onclick="document.getElementById('item_${i}').remove()"><i class="bi bi-trash"></i></button></td>
    </tr>`;
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);
}

function calcTotal(i) {
    const qty = parseFloat(document.querySelector(`[name="items[${i}][quantity]"]`)?.value) || 0;
    const up  = parseFloat(document.getElementById(`up_${i}`)?.value) || 0;
    const et  = document.getElementById(`et_${i}`);
    if (et) et.value = (qty * up).toFixed(2);
}

addItem(); // start with one row
</script>
@endpush
