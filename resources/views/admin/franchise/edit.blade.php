@extends('layouts.app')
@section('title', 'Edit Franchise — ' . $franchise->franchise_code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.franchise.index') }}">Franchise</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.franchise.show', $franchise) }}">{{ $franchise->franchise_code }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Franchise</h4>
            <p>{{ $franchise->franchise_code }} &mdash; {{ $franchise->owner_name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.franchise.show', $franchise) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.franchise.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#0891b2,#4f46e5);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-shop-window" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Franchise</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $franchise->franchise_code }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ $franchise->owner_name }} &mdash; {{ $franchise->location }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.franchise.update', $franchise) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <h6 class="form-section-label">A — Franchise Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Franchise Code <span class="text-danger">*</span></label>
                                <input type="text" name="franchise_code" class="form-control @error('franchise_code') is-invalid @enderror"
                                    value="{{ old('franchise_code', $franchise->franchise_code) }}" required>
                                @error('franchise_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Owner Name <span class="text-danger">*</span></label>
                                <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror"
                                    value="{{ old('owner_name', $franchise->owner_name) }}" required>
                                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                                    value="{{ old('location', $franchise->location) }}" required>
                                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                                <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror"
                                    value="{{ old('contact_number', $franchise->contact_number) }}" required>
                                @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Agreement Date <span class="text-danger">*</span></label>
                                <input type="date" name="agreement_date" class="form-control @error('agreement_date') is-invalid @enderror"
                                    value="{{ old('agreement_date', $franchise->agreement_date->toDateString()) }}" required>
                                @error('agreement_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach(['Active','Inactive','Suspended'] as $s)
                                        <option value="{{ $s }}" @selected(old('status',$franchise->status)===$s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">B — Financial Terms</h6>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Investment (&#8377;) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">&#8377;</span>
                                    <input type="number" step="0.01" min="0" name="investment_amount"
                                        class="form-control @error('investment_amount') is-invalid @enderror"
                                        value="{{ old('investment_amount', $franchise->investment_amount) }}" required>
                                    @error('investment_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Royalty % <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="100" name="royalty_percentage"
                                    class="form-control @error('royalty_percentage') is-invalid @enderror"
                                    value="{{ old('royalty_percentage', $franchise->royalty_percentage) }}" required>
                                @error('royalty_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Milk Collected (L)</label>
                                <input type="number" step="0.1" min="0" name="milk_collected_liters"
                                    class="form-control @error('milk_collected_liters') is-invalid @enderror"
                                    value="{{ old('milk_collected_liters', $franchise->milk_collected_liters) }}">
                                @error('milk_collected_liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.franchise.show', $franchise) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Update Franchise
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
