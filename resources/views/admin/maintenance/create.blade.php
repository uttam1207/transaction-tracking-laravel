@extends('layouts.app')
@section('title', 'Log Machine Maintenance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.index') }}">Maintenance</a></li>
    <li class="breadcrumb-item active">Log Maintenance</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Log Machine Maintenance</h4>
            <p>Record service history, breakdown logs and preventive schedules</p>
        </div>
        <a href="{{ route('admin.maintenance.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Maintenance
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
                <div class="card-glass-icon" style="background:linear-gradient(135deg,#dc2626,#b91c1c);"><i class="bi bi-tools"></i></div>
                <div class="card-glass-title">Maintenance Record</div>
            </div>

            <form action="{{ route('admin.maintenance.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Machine Name <span class="text-danger">*</span></label>
                        <input type="text" name="machine_name" class="form-control @error('machine_name') is-invalid @enderror"
                            placeholder="e.g. DeLaval Milking Machine" value="{{ old('machine_name') }}" required>
                        @error('machine_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Maintenance Type <span class="text-danger">*</span></label>
                        <select name="maintenance_type" class="form-select @error('maintenance_type') is-invalid @enderror" required>
                            @foreach(['Preventive Schedule','Service History','Breakdown Log'] as $t)
                                <option value="{{ $t }}" @selected(old('maintenance_type')===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                        @error('maintenance_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Service Date <span class="text-danger">*</span></label>
                        <input type="date" name="service_date" class="form-control @error('service_date') is-invalid @enderror"
                            value="{{ old('service_date', now()->toDateString()) }}" required>
                        @error('service_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Next Service Due</label>
                        <input type="date" name="next_service_due" class="form-control @error('next_service_due') is-invalid @enderror"
                            value="{{ old('next_service_due') }}">
                        @error('next_service_due')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Serviced By</label>
                        <input type="text" name="serviced_by" class="form-control @error('serviced_by') is-invalid @enderror"
                            placeholder="e.g. DeLaval Service MP" value="{{ old('serviced_by') }}">
                        @error('serviced_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cost (&#8377;)</label>
                        <div class="input-group">
                            <span class="input-group-text">&#8377;</span>
                            <input type="number" step="0.01" min="0" name="cost"
                                class="form-control @error('cost') is-invalid @enderror"
                                value="{{ old('cost', 0) }}">
                            @error('cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description / Work Done</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                            placeholder="e.g. Vacuum pump check and liner replacement">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-save me-1"></i>Save Maintenance Log
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
