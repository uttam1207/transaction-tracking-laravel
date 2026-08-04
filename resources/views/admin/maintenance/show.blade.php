@extends('layouts.app')
@section('title', 'Maintenance — ' . $machineMaintenance->machine_name)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark"><i class="bi bi-tools me-2"></i>{{ $machineMaintenance->machine_name }}</h1>
            <p class="text-muted mb-0">{{ $machineMaintenance->maintenance_type }} — {{ $machineMaintenance->service_date->format('d M Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.maintenance.edit', $machineMaintenance) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.maintenance.destroy', $machineMaintenance) }}" onsubmit="return confirm('Delete this maintenance record?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.maintenance.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <dl class="row mb-0">
            <dt class="col-5 text-muted">Machine Name</dt><dd class="col-7 fw-bold">{{ $machineMaintenance->machine_name }}</dd>
            <dt class="col-5 text-muted">Maintenance Type</dt><dd class="col-7"><span class="badge bg-dark bg-opacity-10 text-dark border">{{ $machineMaintenance->maintenance_type }}</span></dd>
            <dt class="col-5 text-muted">Service Date</dt><dd class="col-7">{{ $machineMaintenance->service_date->format('d F Y') }}</dd>
            <dt class="col-5 text-muted">Serviced By</dt><dd class="col-7">{{ $machineMaintenance->serviced_by ?? '—' }}</dd>
            <dt class="col-5 text-muted">Description</dt><dd class="col-7">{{ $machineMaintenance->description ?? '—' }}</dd>
            <dt class="col-5 text-muted">Cost</dt><dd class="col-7 fw-bold text-danger">{{ $machineMaintenance->cost ? '₹' . number_format($machineMaintenance->cost, 2) : '—' }}</dd>
            <dt class="col-5 text-muted">Next Service Due</dt>
            <dd class="col-7">
                @if($machineMaintenance->next_service_due)
                    <span class="fw-bold {{ $machineMaintenance->next_service_due->isPast() ? 'text-danger' : ($machineMaintenance->next_service_due->diffInDays() <= 14 ? 'text-warning' : 'text-success') }}">
                        {{ $machineMaintenance->next_service_due->format('d M Y') }}
                        @if($machineMaintenance->next_service_due->isPast())
                            <span class="badge bg-danger ms-1">Overdue</span>
                        @elseif($machineMaintenance->next_service_due->diffInDays() <= 14)
                            <span class="badge bg-warning text-dark ms-1">Due Soon</span>
                        @endif
                    </span>
                @else
                    —
                @endif
            </dd>
            <dt class="col-5 text-muted">Recorded At</dt><dd class="col-7 small text-muted">{{ $machineMaintenance->created_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
