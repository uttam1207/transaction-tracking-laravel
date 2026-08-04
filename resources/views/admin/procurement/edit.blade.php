@extends('layouts.app')
@section('title', 'Edit PO — ' . $purchaseOrder->po_number)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.procurement.show', $purchaseOrder) }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold text-secondary"><i class="bi bi-pencil me-2"></i>Edit — {{ $purchaseOrder->po_number }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.procurement.update', $purchaseOrder) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">PO Number <span class="text-danger">*</span></label>
                        <input type="text" name="po_number" class="form-control" value="{{ old('po_number', $purchaseOrder->po_number) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Vendor <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="">Select vendor…</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}" @selected(old('vendor_id', $purchaseOrder->vendor_id) == $v->id)>{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Order Date <span class="text-danger">*</span></label>
                        <input type="date" name="order_date" class="form-control" value="{{ old('order_date', $purchaseOrder->order_date->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['Draft','Sent','Received','Paid'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $purchaseOrder->status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Total Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="total_amount" class="form-control" value="{{ old('total_amount', $purchaseOrder->total_amount) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ old('remarks', $purchaseOrder->remarks) }}">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.procurement.show', $purchaseOrder) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-secondary px-4"><i class="bi bi-check-lg me-1"></i>Update Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
