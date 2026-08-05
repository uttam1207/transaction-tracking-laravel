@extends('layouts.app')
@section('title', 'Maintenance — ' . $machineMaintenance->machine_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.maintenance.index') }}">Maintenance</a></li>
    <li class="breadcrumb-item active">{{ $machineMaintenance->machine_name }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $machineMaintenance->machine_name }}</h4>
            <p>{{ $machineMaintenance->maintenance_type }} &mdash; {{ $machineMaintenance->service_date->format('d M Y') }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.maintenance.edit', $machineMaintenance) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.maintenance.destroy', $machineMaintenance) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Delete this maintenance record?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-sm btn-outline-secondary px-4">
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
            <div style="background:linear-gradient(135deg,#dc2626,#b91c1c);padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:100px;width:90px;height:90px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div style="width:58px;height:58px;background:rgba(255,255,255,.18);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-tools" style="font-size:1.55rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $machineMaintenance->machine_name }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:3px;">Serviced on {{ $machineMaintenance->service_date->format('d F Y') }}</div>
                    </div>
                    <div class="ms-auto">
                        @php
                            $tColor = match($machineMaintenance->maintenance_type) {
                                'Preventive Schedule' => 'spill-success',
                                'Service History'     => 'spill-info',
                                default               => 'spill-danger',
                            };
                        @endphp
                        <span class="spill {{ $tColor }}" style="font-size:.8rem;">{{ $machineMaintenance->maintenance_type }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-tools me-1"></i>Machine Name</div>
                            <div class="fw-bold" style="color:#1f2937;">{{ $machineMaintenance->machine_name }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-calendar3 me-1"></i>Service Date</div>
                            <div class="fw-semibold" style="color:#1f2937;">{{ $machineMaintenance->service_date->format('d F Y') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-person me-1"></i>Serviced By</div>
                            <div style="color:#374151;">{{ $machineMaintenance->serviced_by ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-currency-rupee me-1"></i>Cost</div>
                            <div class="fw-bold" style="color:#dc2626;font-size:1rem;">{{ $machineMaintenance->cost ? '₹' . number_format($machineMaintenance->cost,2) : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-card-text me-1"></i>Description</div>
                            <div style="color:#374151;line-height:1.6;">{{ $machineMaintenance->description ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:{{ $machineMaintenance->next_service_due && $machineMaintenance->next_service_due->lte(now()->addDays(14)) ? '#fef2f2' : '#f8fafc' }};border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-bell me-1"></i>Next Service Due</div>
                            @if($machineMaintenance->next_service_due)
                                @php $isPast = $machineMaintenance->next_service_due->isPast(); $isDue = $machineMaintenance->next_service_due->lte(now()->addDays(14)); @endphp
                                <div class="fw-bold" style="color:{{ $isPast ? '#dc2626' : ($isDue ? '#d97706' : '#059669') }};">
                                    {{ $machineMaintenance->next_service_due->format('d F Y') }}
                                    @if($isPast)<span class="spill spill-danger ms-1">Overdue</span>
                                    @elseif($isDue)<span class="spill spill-warning ms-1">Due Soon</span>
                                    @endif
                                </div>
                            @else
                                <div style="color:#9ca3af;">—</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:14px 16px;">
                            <div style="font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;"><i class="bi bi-clock me-1"></i>Recorded At</div>
                            <div style="color:#6b7280;font-size:.85rem;">{{ $machineMaintenance->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
