@extends('layouts.app')
@section('title', 'Edit Breeding Record #' . $breedingRecord->id)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.breeding.index') }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold text-info"><i class="bi bi-pencil me-2"></i>Edit Breeding Record #{{ $breedingRecord->id }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.breeding.update', $breedingRecord) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Animal <span class="text-danger">*</span></label>
                        <select name="animal_id" class="form-select" required>
                            <option value="">Select animal…</option>
                            @foreach($animals as $a)
                                <option value="{{ $a->id }}" @selected(old('animal_id', $breedingRecord->animal_id) == $a->id)>
                                    {{ $a->tag_number }}{{ $a->name ? ' — ' . $a->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Heat Detection Date <span class="text-danger">*</span></label>
                        <input type="date" name="heat_date" class="form-control" value="{{ old('heat_date', $breedingRecord->heat_date->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">AI Date</label>
                        <input type="date" name="ai_date" class="form-control" value="{{ old('ai_date', $breedingRecord->ai_date?->toDateString()) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Bull Semen Code</label>
                        <input type="text" name="bull_semen_code" class="form-control" value="{{ old('bull_semen_code', $breedingRecord->bull_semen_code) }}" placeholder="e.g. HF-001">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['Heat Detected','AI Done','Confirmed Pregnant','Calved','Repeat Breeder'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $breedingRecord->status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Pregnancy Check Date</label>
                        <input type="date" name="pregnancy_check_date" class="form-control" value="{{ old('pregnancy_check_date', $breedingRecord->pregnancy_check_date?->toDateString()) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Is Pregnant?</label>
                        <select name="is_pregnant" class="form-select">
                            <option value="">— Not Checked —</option>
                            <option value="1" @selected(old('is_pregnant', $breedingRecord->is_pregnant) == '1')>Yes</option>
                            <option value="0" @selected(old('is_pregnant', $breedingRecord->is_pregnant) === false)>No</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Expected Calving Date</label>
                        <input type="date" name="expected_calving_date" class="form-control" value="{{ old('expected_calving_date', $breedingRecord->expected_calving_date?->toDateString()) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Actual Calving Date</label>
                        <input type="date" name="actual_calving_date" class="form-control" value="{{ old('actual_calving_date', $breedingRecord->actual_calving_date?->toDateString()) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Calf Tag Number</label>
                        <input type="text" name="calf_tag_number" class="form-control" value="{{ old('calf_tag_number', $breedingRecord->calf_tag_number) }}" placeholder="e.g. ASD-CALF-001">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.breeding.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-info text-white px-4"><i class="bi bi-check-lg me-1"></i>Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
