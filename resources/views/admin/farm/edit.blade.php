@extends('layouts.app')
@section('title', 'Edit Farm Record #' . $farmRecord->id)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.farm.index') }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold text-success"><i class="bi bi-pencil me-2"></i>Edit Farm Record #{{ $farmRecord->id }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.farm.update', $farmRecord) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Plot Name <span class="text-danger">*</span></label>
                        <input type="text" name="plot_name" class="form-control" value="{{ old('plot_name', $farmRecord->plot_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Crop Type <span class="text-danger">*</span></label>
                        <input type="text" name="crop_type" class="form-control" value="{{ old('crop_type', $farmRecord->crop_type) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Plantation Date</label>
                        <input type="date" name="plantation_date" class="form-control" value="{{ old('plantation_date', $farmRecord->plantation_date?->toDateString()) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Harvest Date</label>
                        <input type="date" name="harvest_date" class="form-control" value="{{ old('harvest_date', $farmRecord->harvest_date?->toDateString()) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fertilizer Used</label>
                        <input type="text" name="fertilizer_used" class="form-control" value="{{ old('fertilizer_used', $farmRecord->fertilizer_used) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Yield (kg)</label>
                        <input type="number" step="0.1" min="0" name="yield_kg" class="form-control" value="{{ old('yield_kg', $farmRecord->yield_kg) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Diesel (Liters)</label>
                        <input type="number" step="0.1" min="0" name="diesel_liters" class="form-control" value="{{ old('diesel_liters', $farmRecord->diesel_liters) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Water Usage (Liters)</label>
                        <input type="number" step="0.1" min="0" name="water_usage_liters" class="form-control" value="{{ old('water_usage_liters', $farmRecord->water_usage_liters) }}">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.farm.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
