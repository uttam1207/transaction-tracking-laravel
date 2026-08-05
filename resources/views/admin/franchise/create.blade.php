@extends('layouts.app')
@section('title', 'Register Franchise')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.franchise.index') }}">Franchise</a></li>
    <li class="breadcrumb-item active">Register</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Register Franchise Outlet</h4>
            <p>Create a new franchise agreement and outlet registration</p>
        </div>
        <a href="{{ route('admin.franchise.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Franchise
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
                <div class="card-glass-icon" style="background:linear-gradient(135deg,#0891b2,#4f46e5);"><i class="bi bi-shop-window"></i></div>
                <div class="card-glass-title">New Franchise Registration</div>
            </div>

            <form action="{{ route('admin.franchise.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Franchise Code <span class="text-danger">*</span></label>
                        <input type="text" name="franchise_code" class="form-control @error('franchise_code') is-invalid @enderror"
                            placeholder="e.g. ASD-FRAN-02" value="{{ old('franchise_code') }}" required>
                        @error('franchise_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Owner Name <span class="text-danger">*</span></label>
                        <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror"
                            placeholder="e.g. Suresh Verma" value="{{ old('owner_name') }}" required>
                        @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror"
                            placeholder="e.g. Damoh Station Road" value="{{ old('location') }}" required>
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                        <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror"
                            placeholder="+91 98260 00000" value="{{ old('contact_number') }}" required>
                        @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Agreement Date <span class="text-danger">*</span></label>
                        <input type="date" name="agreement_date" class="form-control @error('agreement_date') is-invalid @enderror"
                            value="{{ old('agreement_date', now()->toDateString()) }}" required>
                        @error('agreement_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Investment (&#8377;) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">&#8377;</span>
                            <input type="number" step="0.01" min="0" name="investment_amount"
                                class="form-control @error('investment_amount') is-invalid @enderror"
                                value="{{ old('investment_amount', 500000) }}" required>
                            @error('investment_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Royalty % <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" min="0" name="royalty_percentage"
                            class="form-control @error('royalty_percentage') is-invalid @enderror"
                            value="{{ old('royalty_percentage', 5.0) }}" required>
                        @error('royalty_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.franchise.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-save me-1"></i>Register Franchise
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
