@extends('layouts.app')
@section('title', 'Edit Health Record #' . $healthRecord->id)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.health.index') }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold text-danger"><i class="bi bi-pencil me-2"></i>Edit Health Record #{{ $healthRecord->id }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.health.update', $healthRecord) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Animal <span class="text-danger">*</span></label>
                        <select name="animal_id" class="form-select" required>
                            <option value="">Select animal…</option>
                            @foreach($animals as $a)
                                <option value="{{ $a->id }}" @selected(old('animal_id', $healthRecord->animal_id) == $a->id)>
                                    {{ $a->tag_number }}{{ $a->name ? ' — ' . $a->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Record Type <span class="text-danger">*</span></label>
                        <select name="record_type" class="form-select" required>
                            @foreach(['Vaccination','Deworming','Treatment','Doctor Visit','Emergency'] as $t)
                                <option value="{{ $t }}" @selected(old('record_type', $healthRecord->record_type) === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', $healthRecord->date->toDateString()) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Disease / Symptoms</label>
                        <input type="text" name="disease_symptoms" class="form-control" value="{{ old('disease_symptoms', $healthRecord->disease_symptoms) }}" placeholder="Describe symptoms">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Treatment Given</label>
                        <input type="text" name="treatment_given" class="form-control" value="{{ old('treatment_given', $healthRecord->treatment_given) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Medicine Used</label>
                        <input type="text" name="medicine_used" class="form-control" value="{{ old('medicine_used', $healthRecord->medicine_used) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Vet / Doctor Name</label>
                        <input type="text" name="vet_doctor_name" class="form-control" value="{{ old('vet_doctor_name', $healthRecord->vet_doctor_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Body Temperature (°F)</label>
                        <input type="number" step="0.1" name="body_temp" class="form-control" value="{{ old('body_temp', $healthRecord->body_temp) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cost (₹)</label>
                        <input type="number" step="0.01" min="0" name="cost" class="form-control" value="{{ old('cost', $healthRecord->cost) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Status</label>
                        <input type="text" name="status" class="form-control" value="{{ old('status', $healthRecord->status) }}" placeholder="e.g. Recovered">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.health.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-danger px-4"><i class="bi bi-check-lg me-1"></i>Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
