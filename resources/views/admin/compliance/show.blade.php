@extends('layouts.app')
@section('title', 'Compliance — ' . $complianceDocument->document_title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.compliance.index') }}">Compliance</a></li>
    <li class="breadcrumb-item active">{{ $complianceDocument->document_title }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $complianceDocument->document_title }}</h4>
            <p>{{ $complianceDocument->category }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.compliance.edit', $complianceDocument) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.compliance.destroy', $complianceDocument) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Delete this document?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.compliance.index') }}" class="btn btn-sm btn-outline-secondary px-4">
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
            <div style="background:linear-gradient(135deg,#059669,#16a34a);padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:100px;width:90px;height:90px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:58px;height:58px;background:rgba(255,255,255,.18);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-shield-fill-check" style="font-size:1.55rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $complianceDocument->document_title }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:3px;">{{ $complianceDocument->category }}</div>
                    </div>
                    <div class="ms-auto">
                        @php
                            $sClass = match($complianceDocument->status) {
                                'Active'          => 'spill-success',
                                'Expired'         => 'spill-danger',
                                'Pending Renewal' => 'spill-warning',
                                default           => 'spill-secondary',
                            };
                        @endphp
                        <span class="spill {{ $sClass }}" style="font-size:.8rem;">{{ $complianceDocument->status }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-file-earmark-text me-1"></i>Document Title</div>
                            <div class="fw-bold" style="color:#1f2937;">{{ $complianceDocument->document_title }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;"><i class="bi bi-folder me-1"></i>Category</div>
                            <span class="spill spill-info">{{ $complianceDocument->category }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-upc me-1"></i>Document Number</div>
                            <div>
                                @if($complianceDocument->document_number)
                                    <code style="background:#e0e7ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.85rem;">{{ $complianceDocument->document_number }}</code>
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar3 me-1"></i>Issue Date</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $complianceDocument->issue_date ? $complianceDocument->issue_date->format('d F Y') : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        @php
                            $expiring = $complianceDocument->expiry_date && $complianceDocument->expiry_date->lte(now()->addDays(30));
                            $expired  = $complianceDocument->expiry_date && $complianceDocument->expiry_date->isPast();
                        @endphp
                        <div style="background:{{ $expired ? '#fef2f2' : ($expiring ? '#fffbeb' : '#f8fafc') }};border-radius:10px;padding:14px 16px;border:1px solid {{ $expired ? '#fecaca' : ($expiring ? '#fde68a' : 'transparent') }};">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar-x me-1"></i>Expiry Date</div>
                            @if($complianceDocument->expiry_date)
                                <div class="fw-bold" style="color:{{ $expired ? '#dc2626' : ($expiring ? '#d97706' : '#059669') }};">
                                    {{ $complianceDocument->expiry_date->format('d F Y') }}
                                    @if($expired) <span class="spill spill-danger ms-1">Expired</span>
                                    @elseif($expiring) <span class="spill spill-warning ms-1">Expiring Soon</span>
                                    @endif
                                </div>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clock me-1"></i>Recorded At</div>
                            <div style="color:#6b7280;font-size:.85rem;">{{ $complianceDocument->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
