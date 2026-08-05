@extends('layouts.app')
@section('title', 'Milk Entry #' . $milkEntry->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.milk.index') }}">Milk</a></li>
    <li class="breadcrumb-item active">Entry #{{ $milkEntry->id }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Milk Entry #{{ $milkEntry->id }}</h4>
            <p>{{ $milkEntry->date->format('d M Y') }} &mdash; {{ $milkEntry->shift }} Shift</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.milk.edit', $milkEntry) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.milk.destroy', $milkEntry) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Delete this milk entry?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.milk.index') }}" class="btn btn-sm btn-outline-secondary px-4">
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
            <div style="background:linear-gradient(135deg,#059669,#0d9488);padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:100px;width:90px;height:90px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:58px;height:58px;background:rgba(255,255,255,.18);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-droplet-fill" style="font-size:1.55rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ number_format($milkEntry->quantity_liters,1) }} Litres</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:3px;">{{ $milkEntry->date->format('d F Y') }} &mdash; {{ $milkEntry->shift }} Shift</div>
                    </div>
                    <div class="ms-auto d-flex gap-2 flex-wrap">
                        <span class="spill {{ $milkEntry->shift==='Morning' ? 'spill-warning' : 'spill-primary' }}" style="font-size:.8rem;">
                            <i class="bi bi-{{ $milkEntry->shift==='Morning' ? 'sunrise' : 'moon' }} me-1"></i>{{ $milkEntry->shift }}
                        </span>
                        <span class="spill spill-success" style="font-size:.8rem;">{{ $milkEntry->quality_rating }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-tag me-1"></i>Animal / Source</div>
                            <div class="fw-bold" style="color:#1f2937;">
                                {{ $milkEntry->animal ? $milkEntry->animal->tag_number . ' — ' . $milkEntry->animal->name : 'Batch Shed Total' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#ecfdf5;border-radius:10px;padding:14px 16px;border:1px solid #d1fae5;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-droplet me-1"></i>Quantity</div>
                            <div class="fw-bold" style="color:#059669;font-size:1.15rem;">{{ number_format($milkEntry->quantity_liters,1) }} <small style="font-size:.65em;">L</small></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-percent me-1"></i>Fat %</div>
                            <div class="fw-bold" style="color:#1f2937;font-size:1rem;">{{ number_format($milkEntry->fat_percentage,1) }}%</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-percent me-1"></i>SNF %</div>
                            <div class="fw-bold" style="color:#1f2937;font-size:1rem;">{{ number_format($milkEntry->snf_percentage,1) }}%</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-star me-1"></i>Quality Grade</div>
                            <div><span class="spill spill-success">{{ $milkEntry->quality_rating }}</span></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-x-circle me-1"></i>Rejected Litres</div>
                            <div class="fw-bold" style="color:{{ ($milkEntry->rejected_liters??0)>0 ? '#dc2626' : '#059669' }};">
                                {{ number_format($milkEntry->rejected_liters??0,1) }} L
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clock me-1"></i>Recorded At</div>
                            <div style="color:#6b7280;font-size:.85rem;">{{ $milkEntry->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
