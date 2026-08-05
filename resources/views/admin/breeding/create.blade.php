@extends('layouts.app')
@section('title', 'Log Breeding / AI Event')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.breeding.index') }}">Breeding</a></li>
    <li class="breadcrumb-item active">Log Event</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Log Breeding / AI Event</h4>
            <p>Record heat detection, artificial insemination & pregnancy tracking</p>
        </div>
        <a href="{{ route('admin.breeding.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Breeding
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
                <div class="card-glass-icon"><i class="bi bi-arrow-repeat"></i></div>
                <div class="card-glass-title">Breeding / AI Record</div>
            </div>

            <form action="{{ route('admin.breeding.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
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
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(['Heat Detected','AI Done','Confirmed Pregnant','Calved','Repeat Breeder','Not Pregnant'] as $s)
                                <option value="{{ $s }}" @selected(old('status','AI Done')===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Heat Detection Date <span class="text-danger">*</span></label>
                        <input type="date" name="heat_date" class="form-control @error('heat_date') is-invalid @enderror"
                            value="{{ old('heat_date', now()->toDateString()) }}" required>
                        @error('heat_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">AI Date</label>
                        <input type="date" name="ai_date" class="form-control @error('ai_date') is-invalid @enderror"
                            value="{{ old('ai_date') }}">
                        @error('ai_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Expected Calving Date</label>
                        <input type="date" name="expected_calving_date" class="form-control @error('expected_calving_date') is-invalid @enderror"
                            value="{{ old('expected_calving_date') }}">
                        @error('expected_calving_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Bull Semen Code / Batch</label>
                        <input type="text" name="bull_semen_code" class="form-control @error('bull_semen_code') is-invalid @enderror"
                            placeholder="e.g. SEM-MUR-884" value="{{ old('bull_semen_code') }}">
                        @error('bull_semen_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.breeding.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-save me-1"></i>Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
