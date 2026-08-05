@extends('layouts.app')
@section('title', 'Farm — ' . $farmRecord->plot_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.farm.index') }}">Farm</a></li>
    <li class="breadcrumb-item active">{{ $farmRecord->plot_name }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $farmRecord->plot_name }}</h4>
            <p>{{ $farmRecord->crop_type }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.farm.edit', $farmRecord) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.farm.destroy', $farmRecord) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Delete this farm record?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.farm.index') }}" class="btn btn-sm btn-outline-secondary px-4">
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
            <div style="background:linear-gradient(135deg,#65a30d,#16a34a);padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:100px;width:90px;height:90px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:58px;height:58px;background:rgba(255,255,255,.18);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-tree-fill" style="font-size:1.55rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $farmRecord->plot_name }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:3px;">{{ $farmRecord->crop_type }}</div>
                    </div>
                    <div class="ms-auto">
                        <div style="background:rgba(255,255,255,.18);border-radius:10px;padding:8px 14px;text-align:center;">
                            <div style="font-size:1.2rem;font-weight:800;color:#fff;">{{ number_format($farmRecord->yield_kg,0) }}</div>
                            <div style="font-size:.7rem;color:rgba(255,255,255,.8);font-weight:600;">kg Yield</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-map me-1"></i>Plot Name</div>
                            <div class="fw-bold" style="color:#1f2937;">{{ $farmRecord->plot_name }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;"><i class="bi bi-flower1 me-1"></i>Crop Type</div>
                            <span class="spill spill-success">{{ $farmRecord->crop_type }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar3 me-1"></i>Plantation Date</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $farmRecord->plantation_date ? $farmRecord->plantation_date->format('d F Y') : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar2-check me-1"></i>Harvest Date</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $farmRecord->harvest_date ? $farmRecord->harvest_date->format('d F Y') : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#ecfdf5;border-radius:10px;padding:14px 16px;border:1px solid #d1fae5;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-tree me-1"></i>Yield</div>
                            <div class="fw-bold" style="color:#059669;font-size:1rem;">{{ number_format($farmRecord->yield_kg,1) }} <small style="font-size:.7em;">kg</small></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-fuel-pump me-1"></i>Diesel Used</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ number_format($farmRecord->diesel_liters,1) }} L</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-water me-1"></i>Water Usage</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ number_format($farmRecord->water_usage_liters,0) }} L</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-bag me-1"></i>Fertilizer Used</div>
                            <div style="color:#374151;">{{ $farmRecord->fertilizer_used ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clock me-1"></i>Recorded At</div>
                            <div style="color:#6b7280;font-size:.85rem;">{{ $farmRecord->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
