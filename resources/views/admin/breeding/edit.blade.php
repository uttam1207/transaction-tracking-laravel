@extends('layouts.app')
@section('title', 'Edit Breeding Record #' . $breedingRecord->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.breeding.index') }}">Breeding</a></li>
    <li class="breadcrumb-item active">Edit #{{ $breedingRecord->id }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Breeding Record</h4>
            <p>#{{ $breedingRecord->id }} &mdash; {{ $breedingRecord->animal?->tag_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.breeding.show', $breedingRecord) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.breeding.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#2563eb,#4f46e5);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-arrow-repeat" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Breeding Record</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $breedingRecord->animal?->tag_number ?? 'Record #'.$breedingRecord->id }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ $breedingRecord->status }} &mdash; Heat {{ $breedingRecord->heat_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.breeding.update', $breedingRecord) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <h6 class="form-section-label">A — Breeding Event</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Animal <span class="text-danger">*</span></label>
                                <select name="animal_id" class="form-select @error('animal_id') is-invalid @enderror" required>
                                    <option value="">— Select Animal —</option>
                                    @foreach($animals as $a)
                                        <option value="{{ $a->id }}" @selected(old('animal_id',$breedingRecord->animal_id)==$a->id)>
                                            {{ $a->tag_number }}{{ $a->name ? ' — '.$a->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('animal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Heat Detection Date <span class="text-danger">*</span></label>
                                <input type="date" name="heat_date" class="form-control @error('heat_date') is-invalid @enderror"
                                    value="{{ old('heat_date', $breedingRecord->heat_date->toDateString()) }}" required>
                                @error('heat_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">AI Date</label>
                                <input type="date" name="ai_date" class="form-control @error('ai_date') is-invalid @enderror"
                                    value="{{ old('ai_date', $breedingRecord->ai_date?->toDateString()) }}">
                                @error('ai_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bull Semen Code</label>
                                <input type="text" name="bull_semen_code" class="form-control @error('bull_semen_code') is-invalid @enderror"
                                    placeholder="e.g. HF-001" value="{{ old('bull_semen_code', $breedingRecord->bull_semen_code) }}">
                                @error('bull_semen_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach(['Heat Detected','AI Done','Confirmed Pregnant','Calved','Repeat Breeder','Not Pregnant'] as $s)
                                        <option value="{{ $s }}" @selected(old('status',$breedingRecord->status)===$s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">B — Pregnancy & Calving</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pregnancy Check Date</label>
                                <input type="date" name="pregnancy_check_date" class="form-control @error('pregnancy_check_date') is-invalid @enderror"
                                    value="{{ old('pregnancy_check_date', $breedingRecord->pregnancy_check_date?->toDateString()) }}">
                                @error('pregnancy_check_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Is Pregnant?</label>
                                <select name="is_pregnant" class="form-select @error('is_pregnant') is-invalid @enderror">
                                    <option value="">— Not Checked —</option>
                                    <option value="1" @selected(old('is_pregnant',$breedingRecord->is_pregnant)==='1')>Yes</option>
                                    <option value="0" @selected(old('is_pregnant',$breedingRecord->is_pregnant)==='0')>No</option>
                                </select>
                                @error('is_pregnant')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Expected Calving Date</label>
                                <input type="date" name="expected_calving_date" class="form-control @error('expected_calving_date') is-invalid @enderror"
                                    value="{{ old('expected_calving_date', $breedingRecord->expected_calving_date?->toDateString()) }}">
                                @error('expected_calving_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Actual Calving Date</label>
                                <input type="date" name="actual_calving_date" class="form-control @error('actual_calving_date') is-invalid @enderror"
                                    value="{{ old('actual_calving_date', $breedingRecord->actual_calving_date?->toDateString()) }}">
                                @error('actual_calving_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Calf Tag Number</label>
                                <input type="text" name="calf_tag_number" class="form-control @error('calf_tag_number') is-invalid @enderror"
                                    placeholder="e.g. ASD-CALF-001" value="{{ old('calf_tag_number', $breedingRecord->calf_tag_number) }}">
                                @error('calf_tag_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">C — Certificate / Document</h6>
                        <div class="row g-3">
                            @if($breedingRecord->certificate_path)
                            <div class="col-12">
                                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:10px;">
                                    @php $fext = strtolower(pathinfo($breedingRecord->certificate_path, PATHINFO_EXTENSION)); @endphp
                                    <i class="bi {{ $fext === 'pdf' ? 'bi-file-pdf-fill' : 'bi-image-fill' }}"
                                        style="font-size:1.3rem;color:{{ $fext === 'pdf' ? '#dc2626' : '#2563eb' }};flex-shrink:0;"></i>
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:.72rem;color:#6b7280;">Current certificate</div>
                                        <a href="{{ asset('uploads/' . $breedingRecord->certificate_path) }}" target="_blank"
                                            style="font-size:.82rem;color:#059669;font-weight:600;word-break:break-all;">
                                            {{ basename($breedingRecord->certificate_path) }}
                                            <i class="bi bi-box-arrow-up-right ms-1" style="font-size:.6rem;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-paperclip me-1"></i>{{ $breedingRecord->certificate_path ? 'Replace Certificate' : 'Attach Certificate / AI Receipt' }}
                                </label>
                                <input type="file" name="certificate_file" id="breedingCertFileEdit"
                                    class="form-control @error('certificate_file') is-invalid @enderror"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    onchange="showFileName(this,'breedingEditFileHint')">
                                <div id="breedingEditFileHint" class="form-text text-muted" style="font-size:.75rem;">
                                    PDF, JPG, PNG — max 10 MB. Leave empty to keep current file.
                                </div>
                                @error('certificate_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.breeding.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
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

@push('scripts')
<script>
function showFileName(input, hintId) {
    const hint = document.getElementById(hintId);
    if (input.files && input.files[0]) {
        const f = input.files[0];
        const mb = (f.size / 1048576).toFixed(2);
        hint.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><strong>' + f.name + '</strong> (' + mb + ' MB)';
    }
}
</script>
@endpush
