@extends('layouts.app')
@section('title', 'Edit PO — ' . $purchaseOrder->po_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.procurement.show', $purchaseOrder) }}">{{ $purchaseOrder->po_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Purchase Order</h4>
            <p>{{ $purchaseOrder->po_number }} &mdash; {{ $purchaseOrder->vendor?->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.procurement.show', $purchaseOrder) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.procurement.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#059669,#0d9488);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-box-seam-fill" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Purchase Order</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $purchaseOrder->po_number }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ $purchaseOrder->vendor?->name }} &mdash; {{ $purchaseOrder->order_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.procurement.update', $purchaseOrder) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <h6 class="form-section-label">A — Order Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">PO Number <span class="text-danger">*</span></label>
                                <input type="text" name="po_number" class="form-control @error('po_number') is-invalid @enderror"
                                    value="{{ old('po_number', $purchaseOrder->po_number) }}" required>
                                @error('po_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vendor <span class="text-danger">*</span></label>
                                <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                                    <option value="">— Select Vendor —</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}" @selected(old('vendor_id',$purchaseOrder->vendor_id)==$v->id)>{{ $v->name }}</option>
                                    @endforeach
                                </select>
                                @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Order Date <span class="text-danger">*</span></label>
                                <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror"
                                    value="{{ old('order_date', $purchaseOrder->order_date->toDateString()) }}" required>
                                @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach(['Draft','Sent','Received','Paid'] as $s)
                                        <option value="{{ $s }}" @selected(old('status',$purchaseOrder->status)===$s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Total Amount (&#8377;) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">&#8377;</span>
                                    <input type="number" step="0.01" min="0.01" name="total_amount"
                                        class="form-control @error('total_amount') is-invalid @enderror"
                                        value="{{ old('total_amount', $purchaseOrder->total_amount) }}" required>
                                    @error('total_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Remarks</label>
                                <input type="text" name="remarks" class="form-control @error('remarks') is-invalid @enderror"
                                    value="{{ old('remarks', $purchaseOrder->remarks) }}">
                                @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.procurement.show', $purchaseOrder) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Update Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
