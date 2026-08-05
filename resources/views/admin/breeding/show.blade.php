@extends('layouts.app')
@section('title', 'Breeding — ' . $breedingRecord->animal->tag_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.breeding.index') }}">Breeding</a></li>
    <li class="breadcrumb-item active">{{ $breedingRecord->animal->tag_number }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Breeding Record</h4>
            <p>{{ $breedingRecord->animal->tag_number }}{{ $breedingRecord->animal->name ? ' — '.$breedingRecord->animal->name : '' }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.breeding.edit', $breedingRecord) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.breeding.destroy', $breedingRecord) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Delete this breeding record?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.breeding.index') }}" class="btn btn-sm btn-outline-secondary px-4">
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
            <div style="background:linear-gradient(135deg,#2563eb,#4f46e5);padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:100px;width:90px;height:90px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:58px;height:58px;background:rgba(255,255,255,.18);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-arrow-repeat" style="font-size:1.55rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $breedingRecord->animal->tag_number }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:3px;">{{ $breedingRecord->animal->name ?? 'Unnamed' }} &mdash; Breeding Record</div>
                    </div>
                    <div class="ms-auto">
                        @php
                            $sColor = match($breedingRecord->status) {
                                'Confirmed Pregnant' => 'spill-primary',
                                'AI Done'            => 'spill-info',
                                'Heat Detected'      => 'spill-warning',
                                'Calved'             => 'spill-success',
                                default              => 'spill-danger',
                            };
                        @endphp
                        <span class="spill {{ $sColor }}" style="font-size:.8rem;">{{ $breedingRecord->status }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-fire me-1"></i>Heat Detection Date</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $breedingRecord->heat_date->format('d F Y') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-syringe me-1"></i>AI Date</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $breedingRecord->ai_date ? $breedingRecord->ai_date->format('d F Y') : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-upc me-1"></i>Bull Semen Code</div>
                            <div>
                                @if($breedingRecord->bull_semen_code)
                                    <code style="background:#e0e7ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.85rem;">{{ $breedingRecord->bull_semen_code }}</code>
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clipboard2-pulse me-1"></i>Pregnancy Check</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $breedingRecord->pregnancy_check_date ? $breedingRecord->pregnancy_check_date->format('d F Y') : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;"><i class="bi bi-heart me-1"></i>Pregnant</div>
                            @if($breedingRecord->is_pregnant === null) <span class="spill spill-secondary">Unknown</span>
                            @elseif($breedingRecord->is_pregnant) <span class="spill spill-success">Yes</span>
                            @else <span class="spill spill-danger">No</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar-check me-1"></i>Expected Calving</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $breedingRecord->expected_calving_date ? $breedingRecord->expected_calving_date->format('d F Y') : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar2-check me-1"></i>Actual Calving</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $breedingRecord->actual_calving_date ? $breedingRecord->actual_calving_date->format('d F Y') : '—' }}</div>
                        </div>
                    </div>
                    @if($breedingRecord->calf_tag_number)
                    <div class="col-sm-6">
                        <div style="background:#ecfdf5;border-radius:10px;padding:14px 16px;border:1px solid #d1fae5;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-tag me-1"></i>Calf Tag Number</div>
                            <div class="fw-bold" style="color:#059669;">{{ $breedingRecord->calf_tag_number }}</div>
                        </div>
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clock me-1"></i>Recorded At</div>
                            <div style="color:#6b7280;font-size:.85rem;">{{ $breedingRecord->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
