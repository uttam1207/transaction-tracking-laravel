@extends('layouts.app')
@section('title', 'Edit Maintenance — ' . $machineMaintenance->machine_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.index') }}">Maintenance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.show', $machineMaintenance) }}">{{ $machineMaintenance->machine_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Maintenance Log</h4>
            <p>{{ $machineMaintenance->machine_name }} &mdash; {{ $machineMaintenance->maintenance_type }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.maintenance.show', $machineMaintenance) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#dc2626,#b91c1c);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-tools" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Maintenance Log</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $machineMaintenance->machine_name }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ $machineMaintenance->maintenance_type }} &mdash; {{ $machineMaintenance->service_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.maintenance.update', $machineMaintenance) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <h6 class="form-section-label">A — Service Event</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Machine Name <span class="text-danger">*</span></label>
                                <input type="text" name="machine_name" class="form-control @error('machine_name') is-invalid @enderror"
                                    value="{{ old('machine_name', $machineMaintenance->machine_name) }}" required>
                                @error('machine_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Maintenance Type <span class="text-danger">*</span></label>
                                <select name="maintenance_type" class="form-select @error('maintenance_type') is-invalid @enderror" required>
                                    @foreach(['Preventive Schedule','Service History','Breakdown Log'] as $t)
                                        <option value="{{ $t }}" @selected(old('maintenance_type',$machineMaintenance->maintenance_type)===$t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                                @error('maintenance_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Service Date <span class="text-danger">*</span></label>
                                <input type="date" name="service_date" class="form-control @error('service_date') is-invalid @enderror"
                                    value="{{ old('service_date', $machineMaintenance->service_date->toDateString()) }}" required>
                                @error('service_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Next Service Due</label>
                                <input type="date" name="next_service_due" class="form-control @error('next_service_due') is-invalid @enderror"
                                    value="{{ old('next_service_due', $machineMaintenance->next_service_due?->toDateString()) }}">
                                @error('next_service_due')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Serviced By</label>
                                <input type="text" name="serviced_by" class="form-control @error('serviced_by') is-invalid @enderror"
                                    value="{{ old('serviced_by', $machineMaintenance->serviced_by) }}">
                                @error('serviced_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Cost (&#8377;)</label>
                                <div class="input-group">
                                    <span class="input-group-text">&#8377;</span>
                                    <input type="number" step="0.01" min="0" name="cost"
                                        class="form-control @error('cost') is-invalid @enderror"
                                        value="{{ old('cost', $machineMaintenance->cost) }}">
                                    @error('cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description / Work Done</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $machineMaintenance->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.maintenance.show', $machineMaintenance) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Update Maintenance Log
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
