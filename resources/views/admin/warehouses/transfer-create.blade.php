@extends('layouts.app')

@section('title', 'New Stock Transfer')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">New Stock Transfer</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.transfers') }}">Transfers</a></li>
                <li class="breadcrumb-item active">New</li>
            </ol></nav>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.warehouses.transfer.store') }}">
        @csrf
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold">Transfer Details</div>
                    <div class="card-body row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From Warehouse <span class="text-danger">*</span></label>
                            <select name="from_warehouse_id" class="form-select" required>
                                <option value="">Select…</option>
                                @foreach ($warehouses as $wh)
                                    <option value="{{ $wh->id }}" @selected(old('from_warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Warehouse <span class="text-danger">*</span></label>
                            <select name="to_warehouse_id" class="form-select" required>
                                <option value="">Select…</option>
                                @foreach ($warehouses as $wh)
                                    <option value="{{ $wh->id }}" @selected(old('to_warehouse_id') == $wh->id)>{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                            <input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold d-flex justify-content-between">
                        <span>Items to Transfer</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:250px">Item <span class="text-danger">*</span></th>
                                        <th style="width:130px">Quantity <span class="text-danger">*</span></th>
                                        <th>Remarks</th>
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
                <a href="{{ route('admin.warehouses.transfers') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Transfer</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
const items = @json($items->map(fn($i) => ['id' => $i->id, 'name' => $i->name]));
let idx = 0;

function addItem() {
    const i = idx++;
    const opts = items.map(it => `<option value="${it.id}">${it.name}</option>`).join('');
    const row = `<tr id="row_${i}">
        <td><select name="items[${i}][inventory_item_id]" class="form-select form-select-sm" required><option value="">Select item…</option>${opts}</select></td>
        <td><input type="number" name="items[${i}][quantity]" class="form-control form-control-sm text-end" step="0.001" min="0.001" value="1" required></td>
        <td><input type="text" name="items[${i}][remarks]" class="form-control form-control-sm" placeholder="Remarks"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('row_${i}').remove()"><i class="bi bi-trash"></i></button></td>
    </tr>`;
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', row);
}

addItem();
</script>
@endpush
@endsection
