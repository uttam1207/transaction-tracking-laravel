@extends('layouts.app')

@section('title', 'New Purchase Request')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">New Purchase Request</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.purchase-requests.index') }}">Purchase Requests</a></li>
                <li class="breadcrumb-item active">New</li>
            </ol></nav>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.purchase-requests.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold">Request Details</div>
                    <div class="card-body row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select…</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Required Date</label>
                            <input type="date" name="required_date" class="form-control" value="{{ old('required_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                @foreach (['low','normal','high','urgent'] as $p)
                                    <option value="{{ $p }}" @selected(old('priority','normal') === $p)>{{ ucfirst($p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Purpose / Justification</label>
                            <input type="text" name="purpose" class="form-control" placeholder="Why is this needed?" value="{{ old('purpose') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold d-flex justify-content-between">
                        <span>Items Requested</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
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
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.purchase-requests.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Purchase Request</button>
            </div>
        </div>
    </form>
</div>

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
        <td><input type="number" name="items[${i}][estimated_total]" class="form-control form-control-sm text-end bg-light" step="0.01" min="0" placeholder="0.00" id="et_${i}" readonly></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('item_${i}').remove()"><i class="bi bi-trash"></i></button></td>
    </tr>`;
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);
}

function calcTotal(i) {
    const qty = parseFloat(document.querySelector(`[name="items[${i}][quantity]"]`)?.value) || 0;
    const up  = parseFloat(document.getElementById(`up_${i}`)?.value) || 0;
    const et  = document.getElementById(`et_${i}`);
    if (et) et.value = (qty * up).toFixed(2);
}

addItem();
</script>
@endpush
@endsection
