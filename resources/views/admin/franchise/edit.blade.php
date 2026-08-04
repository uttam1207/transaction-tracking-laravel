@extends('layouts.app')
@section('title', 'Edit Franchise — ' . $franchise->franchise_code)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.franchise.show', $franchise) }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold text-warning"><i class="bi bi-pencil me-2"></i>Edit — {{ $franchise->franchise_code }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.franchise.update', $franchise) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Franchise Code <span class="text-danger">*</span></label>
                        <input type="text" name="franchise_code" class="form-control" value="{{ old('franchise_code', $franchise->franchise_code) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Owner Name <span class="text-danger">*</span></label>
                        <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $franchise->owner_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $franchise->location) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Contact Number <span class="text-danger">*</span></label>
                        <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $franchise->contact_number) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Agreement Date <span class="text-danger">*</span></label>
                        <input type="date" name="agreement_date" class="form-control" value="{{ old('agreement_date', $franchise->agreement_date->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['Active','Inactive','Suspended'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $franchise->status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Investment (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="investment_amount" class="form-control" value="{{ old('investment_amount', $franchise->investment_amount) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Royalty % <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="royalty_percentage" class="form-control" value="{{ old('royalty_percentage', $franchise->royalty_percentage) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Milk Collected (L)</label>
                        <input type="number" step="0.1" min="0" name="milk_collected_liters" class="form-control" value="{{ old('milk_collected_liters', $franchise->milk_collected_liters) }}">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.franchise.show', $franchise) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-warning text-dark px-4"><i class="bi bi-check-lg me-1"></i>Update Franchise</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
