@extends('layouts.app')
@section('title', 'Edit Maintenance — ' . $machineMaintenance->machine_name)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.maintenance.show', $machineMaintenance) }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold"><i class="bi bi-pencil me-2"></i>Edit — {{ $machineMaintenance->machine_name }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.maintenance.update', $machineMaintenance) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Machine Name <span class="text-danger">*</span></label>
                        <input type="text" name="machine_name" class="form-control" value="{{ old('machine_name', $machineMaintenance->machine_name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Maintenance Type <span class="text-danger">*</span></label>
                        <select name="maintenance_type" class="form-select" required>
                            @foreach(['Preventive Schedule','Service History','Breakdown Log'] as $t)
                                <option value="{{ $t }}" @selected(old('maintenance_type', $machineMaintenance->maintenance_type) === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Service Date <span class="text-danger">*</span></label>
                        <input type="date" name="service_date" class="form-control" value="{{ old('service_date', $machineMaintenance->service_date->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Next Service Due</label>
                        <input type="date" name="next_service_due" class="form-control" value="{{ old('next_service_due', $machineMaintenance->next_service_due?->toDateString()) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Serviced By</label>
                        <input type="text" name="serviced_by" class="form-control" value="{{ old('serviced_by', $machineMaintenance->serviced_by) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cost (₹)</label>
                        <input type="number" step="0.01" min="0" name="cost" class="form-control" value="{{ old('cost', $machineMaintenance->cost) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $machineMaintenance->description) }}</textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.maintenance.show', $machineMaintenance) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-dark px-4"><i class="bi bi-check-lg me-1"></i>Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
