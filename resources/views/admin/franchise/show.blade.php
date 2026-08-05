@extends('layouts.app')
@section('title', 'Franchise — ' . $franchise->franchise_code)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.franchise.index') }}">Franchise</a></li>
    <li class="breadcrumb-item active">{{ $franchise->franchise_code }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $franchise->franchise_code }}</h4>
            <p>{{ $franchise->owner_name }} &mdash; {{ $franchise->location }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.franchise.edit', $franchise) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.franchise.destroy', $franchise) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Delete this franchise?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.franchise.index') }}" class="btn btn-sm btn-outline-secondary px-4">
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
            <div style="background:linear-gradient(135deg,#0891b2,#4f46e5);padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:100px;width:90px;height:90px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:58px;height:58px;background:rgba(255,255,255,.18);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-shop-window" style="font-size:1.55rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $franchise->franchise_code }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:3px;">{{ $franchise->owner_name }} &mdash; {{ $franchise->location }}</div>
                    </div>
                    <div class="ms-auto d-flex gap-2 flex-wrap">
                        @php $fColor = $franchise->status==='Active' ? 'spill-success' : ($franchise->status==='Suspended' ? 'spill-danger' : 'spill-secondary'); @endphp
                        <span class="spill {{ $fColor }}" style="font-size:.8rem;">{{ $franchise->status }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-upc-scan me-1"></i>Franchise Code</div>
                            <div class="fw-bold" style="color:var(--primary);font-size:.95rem;">{{ $franchise->franchise_code }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-person me-1"></i>Owner Name</div>
                            <div class="fw-bold" style="color:#1f2937;">{{ $franchise->owner_name }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-geo-alt me-1"></i>Location</div>
                            <div style="color:#374151;">{{ $franchise->location }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-telephone me-1"></i>Contact</div>
                            <div style="color:#374151;">{{ $franchise->contact_number }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar3 me-1"></i>Agreement Date</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $franchise->agreement_date->format('d F Y') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-percent me-1"></i>Royalty %</div>
                            <div class="fw-bold" style="color:#1f2937;">{{ number_format($franchise->royalty_percentage,1) }}%</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#ecfdf5;border-radius:10px;padding:14px 16px;border:1px solid #d1fae5;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-bank me-1"></i>Investment</div>
                            <div class="fw-bold" style="color:#059669;font-size:1.05rem;">&#8377;{{ number_format($franchise->investment_amount,0) }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-droplet me-1"></i>Milk Collected</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $franchise->milk_collected_liters ? number_format($franchise->milk_collected_liters,1).' L' : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clock me-1"></i>Registered At</div>
                            <div style="color:#6b7280;font-size:.85rem;">{{ $franchise->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
