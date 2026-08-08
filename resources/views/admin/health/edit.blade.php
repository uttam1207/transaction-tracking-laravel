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

                <form action="{{ route('admin.health.update', $healthRecord) }}" method="POST" enctype="multipart/form-data">
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

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">C — Medical Report / Document</h6>
                        <div class="row g-3">
                            @if($healthRecord->report_path)
                            <div class="col-12">
                                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:10px;">
                                    @php $fext = strtolower(pathinfo($healthRecord->report_path, PATHINFO_EXTENSION)); @endphp
                                    <i class="bi {{ $fext === 'pdf' ? 'bi-file-pdf-fill' : 'bi-image-fill' }}"
                                        style="font-size:1.3rem;color:{{ $fext === 'pdf' ? '#dc2626' : '#2563eb' }};flex-shrink:0;"></i>
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:.72rem;color:#6b7280;">Current report</div>
                                        <a href="{{ asset('uploads/' . $healthRecord->report_path) }}" target="_blank"
                                            style="font-size:.82rem;color:#059669;font-weight:600;word-break:break-all;">
                                            {{ basename($healthRecord->report_path) }}
                                            <i class="bi bi-box-arrow-up-right ms-1" style="font-size:.6rem;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-paperclip me-1"></i>{{ $healthRecord->report_path ? 'Replace Report' : 'Attach Medical Report / Prescription' }}
                                </label>
                                <input type="file" name="report_file" id="healthReportFileEdit"
                                    class="form-control @error('report_file') is-invalid @enderror"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    onchange="showFileName(this,'healthEditFileHint')">
                                <div id="healthEditFileHint" class="form-text text-muted" style="font-size:.75rem;">
                                    PDF, JPG, PNG — max 10 MB. Leave empty to keep current file.
                                </div>
                                @error('report_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
