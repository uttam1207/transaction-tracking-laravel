@extends('layouts.app')
@section('title', 'Edit Invoice — ' . $salesOrder->invoice_number)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.sales.show', $salesOrder) }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold text-success"><i class="bi bi-pencil me-2"></i>Edit — {{ $salesOrder->invoice_number }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.sales.update', $salesOrder) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number', $salesOrder->invoice_number) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Customer</label>
                        <select name="crm_customer_id" class="form-select">
                            <option value="">— Walk-in Customer —</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('crm_customer_id', $salesOrder->crm_customer_id) == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Item Type <span class="text-danger">*</span></label>
                        <select name="item_type" class="form-select" required>
                            @foreach(['Milk Sales','Animal Sales','Feed Sales','Dung Sales','Franchise Royalty'] as $t)
                                <option value="{{ $t }}" @selected(old('item_type', $salesOrder->item_type) === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" class="form-control" value="{{ old('sale_date', $salesOrder->sale_date->toDateString()) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity" id="qty" class="form-control" value="{{ old('quantity', $salesOrder->quantity) }}" required oninput="calcTotal()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Rate (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="rate" id="rate" class="form-control" value="{{ old('rate', $salesOrder->rate) }}" required oninput="calcTotal()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Total Amount</label>
                        <input type="text" id="total_display" class="form-control bg-light" value="₹{{ number_format($salesOrder->total_amount, 2) }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Status <span class="text-danger">*</span></label>
                        <select name="payment_status" class="form-select" required>
                            @foreach(['Paid','Pending','Partial'] as $s)
                                <option value="{{ $s }}" @selected(old('payment_status', $salesOrder->payment_status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.sales.show', $salesOrder) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Update Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function calcTotal() {
    const q = parseFloat(document.getElementById('qty').value) || 0;
    const r = parseFloat(document.getElementById('rate').value) || 0;
    document.getElementById('total_display').value = '₹' + (q * r).toLocaleString('en-IN', {minimumFractionDigits:2});
}
</script>
@endsection
