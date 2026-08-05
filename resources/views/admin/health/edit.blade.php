@extends('layouts.app')
@section('title', 'Edit Health Record #' . $healthRecord->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.health.index') }}">Health</a></li>
    <li class="breadcrumb-item active">Edit #{{ $healthRecord->id }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Health Record</h4>
            <p>#{{ $healthRecord->id }} &mdash; {{ $healthRecord->animal?->tag_number }} &mdash; {{ $healthRecord->record_type }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.health.show', $healthRecord) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.health.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#dc2626,#9f1239);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-heart-pulse-fill" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Health Record</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $healthRecord->animal?->tag_number ?? 'Record #'.$healthRecord->id }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ $healthRecord->record_type }} &mdash; {{ $healthRecord->date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.health.update', $healthRecord) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <h6 class="form-section-label">A — Health Event</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Animal <span class="text-danger">*</span></label>
                                <select name="animal_id" class="form-select @error('animal_id') is-invalid @enderror" required>
                                    <option value="">— Select Animal —</option>
                                    @foreach($animals as $a)
                                        <option value="{{ $a->id }}" @selected(old('animal_id',$healthRecord->animal_id)==$a->id)>
                                            {{ $a->tag_number }}{{ $a->name ? ' — '.$a->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('animal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Record Type <span class="text-danger">*</span></label>
                                <select name="record_type" class="form-select @error('record_type') is-invalid @enderror" required>
                                    @foreach(['Vaccination','Deworming','Treatment','Doctor Visit','Emergency'] as $t)
                                        <option value="{{ $t }}" @selected(old('record_type',$healthRecord->record_type)===$t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                                @error('record_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                    value="{{ old('date', $healthRecord->date->toDateString()) }}" required>
                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Disease / Symptoms</label>
                                <input type="text" name="disease_symptoms" class="form-control @error('disease_symptoms') is-invalid @enderror"
                                    placeholder="Describe symptoms" value="{{ old('disease_symptoms', $healthRecord->disease_symptoms) }}">
                                @error('disease_symptoms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Treatment Given</label>
                                <input type="text" name="treatment_given" class="form-control @error('treatment_given') is-invalid @enderror"
                                    value="{{ old('treatment_given', $healthRecord->treatment_given) }}">
                                @error('treatment_given')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">B — Clinical Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Medicine Used</label>
                                <input type="text" name="medicine_used" class="form-control @error('medicine_used') is-invalid @enderror"
                                    value="{{ old('medicine_used', $healthRecord->medicine_used) }}">
                                @error('medicine_used')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vet / Doctor Name</label>
                                <input type="text" name="vet_doctor_name" class="form-control @error('vet_doctor_name') is-invalid @enderror"
                                    value="{{ old('vet_doctor_name', $healthRecord->vet_doctor_name) }}">
                                @error('vet_doctor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Body Temp (°F)</label>
                                <input type="number" step="0.1" name="body_temp" class="form-control @error('body_temp') is-invalid @enderror"
                                    value="{{ old('body_temp', $healthRecord->body_temp) }}">
                                @error('body_temp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cost (&#8377;)</label>
                                <div class="input-group">
                                    <span class="input-group-text">&#8377;</span>
                                    <input type="number" step="0.01" min="0" name="cost"
                                        class="form-control @error('cost') is-invalid @enderror"
                                        value="{{ old('cost', $healthRecord->cost) }}">
                                    @error('cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status / Outcome</label>
                                <input type="text" name="status" class="form-control @error('status') is-invalid @enderror"
                                    placeholder="e.g. Recovered" value="{{ old('status', $healthRecord->status) }}">
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.health.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Update Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
