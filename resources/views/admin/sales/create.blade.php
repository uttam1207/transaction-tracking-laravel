@extends('layouts.app')
@section('title', 'Create Sales Invoice')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
    <li class="breadcrumb-item active">Create Invoice</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Create Sales Invoice</h4>
            <p>Issue a new sales invoice for milk, animal, feed or franchise royalty</p>
        </div>
        <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Sales
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
                <div class="card-glass-icon" style="background:linear-gradient(135deg,#059669,#16a34a);"><i class="bi bi-receipt-cutoff"></i></div>
                <div class="card-glass-title">Sales Invoice Details</div>
            </div>

            <form action="{{ route('admin.sales.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror"
                            placeholder="e.g. INV-2026-010" value="{{ old('invoice_number') }}" required>
                        @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" name="sale_date" class="form-control @error('sale_date') is-invalid @enderror"
                            value="{{ old('sale_date', now()->toDateString()) }}" required>
                        @error('sale_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Customer</label>
                        <select name="crm_customer_id" class="form-select @error('crm_customer_id') is-invalid @enderror">
                            <option value="">— Direct Retail Customer —</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('crm_customer_id')==$c->id)>{{ $c->name }} ({{ $c->category }})</option>
                            @endforeach
                        </select>
                        @error('crm_customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Sale Item Type <span class="text-danger">*</span></label>
                        <select name="item_type" class="form-select @error('item_type') is-invalid @enderror" required>
                            @foreach(['Milk Sales','Animal Sales','Feed Sales','Dung Sales','Franchise Royalty'] as $t)
                                <option value="{{ $t }}" @selected(old('item_type')===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                        @error('item_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity"
                            class="form-control @error('quantity') is-invalid @enderror"
                            placeholder="e.g. 100" value="{{ old('quantity') }}" required>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Rate (&#8377;) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="rate"
                            class="form-control @error('rate') is-invalid @enderror"
                            placeholder="e.g. 60" value="{{ old('rate') }}" required>
                        @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                        <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                            @foreach(['Paid','Pending','Partial'] as $s)
                                <option value="{{ $s }}" @selected(old('payment_status','Paid')===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('payment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-save me-1"></i>Save Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
