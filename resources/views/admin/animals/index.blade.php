@extends('layouts.app')
@section('title', 'Animal Management - Animal Register')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-primary"><i class="bi bi-card-checklist me-2"></i>Module 2 — Animal Register</h1>
            <p class="text-muted mb-0">Cattle master registry, health tracking, shed numbers, and action logs.</p>
        </div>
        <a href="{{ route('admin.animals.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New Animal
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Total Herd</div>
                <div class="fs-3 fw-bold text-primary">{{ $summary['total'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Milking Animals</div>
                <div class="fs-3 fw-bold text-success">{{ $summary['milking'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Dry Animals</div>
                <div class="fs-3 fw-bold text-secondary">{{ $summary['dry'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Pregnant</div>
                <div class="fs-3 fw-bold text-info">{{ $summary['pregnant'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center bg-primary bg-opacity-10">
                <div class="text-muted small">Calves Registered</div>
                <div class="fs-3 fw-bold text-primary">{{ $summary['calves'] }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tag Number</th>
                        <th>Name</th>
                        <th>Breed</th>
                        <th>Lactation</th>
                        <th>Current Weight</th>
                        <th>Health Status</th>
                        <th>Pregnancy Status</th>
                        <th>Shed</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($animals as $a)
                        <tr>
                            <td><strong class="text-primary">{{ $a->tag_number }}</strong></td>
                            <td>{{ $a->name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $a->breed }}</span></td>
                            <td>Lactation {{ $a->lactation_number }}</td>
                            <td>{{ $a->current_weight ? $a->current_weight . ' kg' : '—' }}</td>
                            <td><span class="badge bg-success bg-opacity-10 text-success">{{ $a->health_status }}</span></td>
                            <td>
                                @if($a->pregnancy_status === 'Pregnant')
                                    <span class="badge bg-info text-dark">Pregnant</span>
                                @elseif($a->pregnancy_status === 'Dry')
                                    <span class="badge bg-secondary">Dry</span>
                                @else
                                    <span class="badge bg-light text-dark border">{{ $a->pregnancy_status }}</span>
                                @endif
                            </td>
                            <td>{{ $a->shed_number }}</td>
                            <td><span class="badge bg-{{ $a->status === 'Active' ? 'success' : ($a->status === 'Sold' ? 'secondary' : 'danger') }}">{{ $a->status }}</span></td>
                            <td>
                                <a href="{{ route('admin.animals.show', $a) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="{{ route('admin.animals.edit', $a) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No animals registered yet. Click Add New Animal to register.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($animals->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $animals->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
