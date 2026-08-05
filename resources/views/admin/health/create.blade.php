@extends('layouts.app')
@section('title', 'Log Health Event')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.health.index') }}">Health</a></li>
    <li class="breadcrumb-item active">Log Event</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Log Health Event</h4>
            <p>Record vaccination, treatment, deworming or veterinary visit</p>
        </div>
        <a href="{{ route('admin.health.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Health
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
                <div class="card-glass-icon" style="background:linear-gradient(135deg,#dc2626,#9f1239);"><i class="bi bi-thermometer-high"></i></div>
                <div class="card-glass-title">Health / Veterinary Record</div>
            </div>

            <form action="{{ route('admin.health.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Animal <span class="text-danger">*</span></label>
                        <select name="animal_id" class="form-select @error('animal_id') is-invalid @enderror" required>
                            <option value="">— Choose Animal —</option>
                            @foreach($animals as $a)
                                <option value="{{ $a->id }}" @selected(old('animal_id')==$a->id)>{{ $a->tag_number }} — {{ $a->name }}</option>
                            @endforeach
                        </select>
                        @error('animal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Record Type <span class="text-danger">*</span></label>
                        <select name="record_type" class="form-select @error('record_type') is-invalid @enderror" required>
                            @foreach(['Vaccination','Deworming','Treatment','Doctor Visit','Emergency'] as $t)
                                <option value="{{ $t }}" @selected(old('record_type')===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                        @error('record_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                            value="{{ old('date', now()->toDateString()) }}" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Veterinary Doctor</label>
                        <input type="text" name="vet_doctor_name" class="form-control @error('vet_doctor_name') is-invalid @enderror"
                            placeholder="e.g. Dr. Verma" value="{{ old('vet_doctor_name') }}">
                        @error('vet_doctor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Disease / Symptoms / Notes</label>
                        <input type="text" name="disease_symptoms" class="form-control @error('disease_symptoms') is-invalid @enderror"
                            placeholder="e.g. Fever, FMD Vaccine" value="{{ old('disease_symptoms') }}">
                        @error('disease_symptoms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Treatment Given / Medicine</label>
                        <input type="text" name="treatment_given" class="form-control @error('treatment_given') is-invalid @enderror"
                            placeholder="e.g. Albendazole 100ml" value="{{ old('treatment_given') }}">
                        @error('treatment_given')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.health.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-save me-1"></i>Save Health Log
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
