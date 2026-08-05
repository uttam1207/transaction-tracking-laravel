@extends('layouts.app')
@section('title', 'Health — ' . $healthRecord->animal->tag_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.health.index') }}">Health</a></li>
    <li class="breadcrumb-item active">{{ $healthRecord->animal->tag_number }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Health Record</h4>
            <p>{{ $healthRecord->animal->tag_number }} &mdash; {{ $healthRecord->record_type }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.health.edit', $healthRecord) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.health.destroy', $healthRecord) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Delete this health record?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.health.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4 justify-content-center">
    <div class="col-lg-9">
        <div class="card-glass overflow-hidden">
            <div style="background:linear-gradient(135deg,#dc2626,#9f1239);padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:100px;width:90px;height:90px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:58px;height:58px;background:rgba(255,255,255,.18);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-heart-pulse" style="font-size:1.55rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-.01em;">
                            {{ $healthRecord->animal->tag_number }}
                            @if($healthRecord->animal->name)<span style="font-weight:400;font-size:1rem;"> — {{ $healthRecord->animal->name }}</span>@endif
                        </div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:3px;">{{ $healthRecord->date->format('d F Y') }}</div>
                    </div>
                    <div class="ms-auto">
                        @php
                            $tColor = match($healthRecord->record_type) {
                                'Vaccination' => 'spill-success',
                                'Treatment'   => 'spill-danger',
                                'Deworming'   => 'spill-info',
                                'Injury'      => 'spill-warning',
                                default       => 'spill-secondary',
                            };
                        @endphp
                        <span class="spill {{ $tColor }}" style="font-size:.8rem;">{{ $healthRecord->record_type }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-tag me-1"></i>Animal</div>
                            <div class="fw-bold" style="color:var(--primary);">
                                <a href="{{ route('admin.animals.show', $healthRecord->animal) }}" style="color:var(--primary);text-decoration:none;">{{ $healthRecord->animal->tag_number }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar3 me-1"></i>Date</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $healthRecord->date->format('d F Y') }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#fef2f2;border-radius:10px;padding:14px 16px;border:1px solid #fecaca;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-exclamation-triangle me-1"></i>Disease / Symptoms</div>
                            <div style="color:#374151;line-height:1.6;">{{ $healthRecord->disease_symptoms ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f0fdf4;border-radius:10px;padding:14px 16px;border:1px solid #bbf7d0;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-capsule me-1"></i>Treatment Given</div>
                            <div style="color:#374151;line-height:1.6;">{{ $healthRecord->treatment_given ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-capsule-pill me-1"></i>Medicine Used</div>
                            <div style="color:#374151;">{{ $healthRecord->medicine_used ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-person-badge me-1"></i>Vet / Doctor</div>
                            <div style="color:#374151;">{{ $healthRecord->vet_doctor_name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-thermometer me-1"></i>Body Temp</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $healthRecord->body_temp ? $healthRecord->body_temp.' °F' : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-currency-rupee me-1"></i>Cost</div>
                            <div class="fw-bold" style="color:#dc2626;font-size:1rem;">{{ $healthRecord->cost ? '₹'.number_format($healthRecord->cost,2) : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clock me-1"></i>Recorded At</div>
                            <div style="color:#6b7280;font-size:.85rem;">{{ $healthRecord->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
