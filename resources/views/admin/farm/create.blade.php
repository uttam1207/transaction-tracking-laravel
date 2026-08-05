@extends('layouts.app')
@section('title', 'Log Farm Activity')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.farm.index') }}">Farm</a></li>
    <li class="breadcrumb-item active">Log Activity</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Log Farm Harvest / Input</h4>
            <p>Record land activity, crop yield, diesel, water and fertilizer usage</p>
        </div>
        <a href="{{ route('admin.farm.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Farm
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
                <div class="card-glass-icon" style="background:linear-gradient(135deg,#65a30d,#16a34a);"><i class="bi bi-tree-fill"></i></div>
                <div class="card-glass-title">Farm Activity Record</div>
            </div>

            <form action="{{ route('admin.farm.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Plot Name <span class="text-danger">*</span></label>
                        <input type="text" name="plot_name" class="form-control @error('plot_name') is-invalid @enderror"
                            placeholder="e.g. Plot A - Anoo Village"
                            value="{{ old('plot_name') }}" required>
                        @error('plot_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Crop Type <span class="text-danger">*</span></label>
                        <input type="text" name="crop_type" class="form-control @error('crop_type') is-invalid @enderror"
                            value="{{ old('crop_type','Napier Grass') }}" required>
                        @error('crop_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Plantation Date</label>
                        <input type="date" name="plantation_date" class="form-control @error('plantation_date') is-invalid @enderror"
                            value="{{ old('plantation_date') }}">
                        @error('plantation_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Harvest Date</label>
                        <input type="date" name="harvest_date" class="form-control @error('harvest_date') is-invalid @enderror"
                            value="{{ old('harvest_date') }}">
                        @error('harvest_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Yield (kg)</label>
                        <input type="number" step="0.1" min="0" name="yield_kg"
                            class="form-control @error('yield_kg') is-invalid @enderror"
                            value="{{ old('yield_kg', 0) }}">
                        @error('yield_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Diesel (L)</label>
                        <input type="number" step="0.1" min="0" name="diesel_liters"
                            class="form-control @error('diesel_liters') is-invalid @enderror"
                            value="{{ old('diesel_liters', 0) }}">
                        @error('diesel_liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Water Usage (L)</label>
                        <input type="number" step="1" min="0" name="water_usage_liters"
                            class="form-control @error('water_usage_liters') is-invalid @enderror"
                            value="{{ old('water_usage_liters', 0) }}">
                        @error('water_usage_liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Fertilizer Used</label>
                        <input type="text" name="fertilizer_used" class="form-control @error('fertilizer_used') is-invalid @enderror"
                            placeholder="e.g. Organic Cow Dung Compost" value="{{ old('fertilizer_used') }}">
                        @error('fertilizer_used')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.farm.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-save me-1"></i>Save Farm Log
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
