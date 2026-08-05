@extends('layouts.app')
@section('title', 'Create Purchase Order')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item active">Create PO</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Create Purchase Order</h4>
            <p>Issue a new purchase order to a registered vendor</p>
        </div>
        <a href="{{ route('admin.procurement.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Procurement
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card-glass p-4">
            <div class="card-glass-header">
                <div class="card-glass-icon" style="background:linear-gradient(135deg,#2563eb,#4f46e5);"><i class="bi bi-file-earmark-plus-fill"></i></div>
                <div class="card-glass-title">Purchase Order Details</div>
            </div>

            <form action="{{ route('admin.procurement.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">PO Number <span class="text-danger">*</span></label>
                        <input type="text" name="po_number" class="form-control @error('po_number') is-invalid @enderror"
                            placeholder="e.g. PO-2026-002" value="{{ old('po_number') }}" required>
                        @error('po_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Order Date <span class="text-danger">*</span></label>
                        <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror"
                            value="{{ old('order_date', now()->toDateString()) }}" required>
                        @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Vendor <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                            <option value="">— Select Vendor —</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}" @selected(old('vendor_id')==$v->id)>{{ $v->name }} ({{ $v->category }})</option>
                            @endforeach
                        </select>
                        @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Total Amount (&#8377;) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8377;</span>
                            <input type="number" step="0.01" min="0" name="total_amount"
                                class="form-control @error('total_amount') is-invalid @enderror"
                                placeholder="e.g. 15000" value="{{ old('total_amount') }}" required>
                            @error('total_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(['Draft','Sent','Received','Paid'] as $s)
                                <option value="{{ $s }}" @selected(old('status','Draft')===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3"
                            placeholder="Items ordered, delivery notes…">{{ old('remarks') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.procurement.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-save me-1"></i>Save Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
