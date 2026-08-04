@extends('layouts.app')
@section('title', 'Compliance — ' . $complianceDocument->document_title)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-success"><i class="bi bi-shield-check me-2"></i>{{ $complianceDocument->document_title }}</h1>
            <p class="text-muted mb-0">{{ $complianceDocument->category }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.compliance.edit', $complianceDocument) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.compliance.destroy', $complianceDocument) }}" onsubmit="return confirm('Delete this compliance document?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.compliance.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <dl class="row mb-0">
            <dt class="col-5 text-muted">Document Title</dt><dd class="col-7 fw-bold">{{ $complianceDocument->document_title }}</dd>
            <dt class="col-5 text-muted">Category</dt><dd class="col-7"><span class="badge bg-success bg-opacity-10 text-success border">{{ $complianceDocument->category }}</span></dd>
            <dt class="col-5 text-muted">Document Number</dt><dd class="col-7 font-monospace">{{ $complianceDocument->document_number ?? '—' }}</dd>
            <dt class="col-5 text-muted">Issue Date</dt><dd class="col-7">{{ $complianceDocument->issue_date ? $complianceDocument->issue_date->format('d M Y') : '—' }}</dd>
            <dt class="col-5 text-muted">Expiry Date</dt>
            <dd class="col-7">
                @if($complianceDocument->expiry_date)
                    <span class="fw-bold {{ $complianceDocument->expiry_date->isPast() ? 'text-danger' : ($complianceDocument->expiry_date->diffInDays() <= 30 ? 'text-warning' : 'text-success') }}">
                        {{ $complianceDocument->expiry_date->format('d M Y') }}
                        @if($complianceDocument->expiry_date->isPast())
                            <span class="badge bg-danger ms-1">Expired</span>
                        @elseif($complianceDocument->expiry_date->diffInDays() <= 30)
                            <span class="badge bg-warning text-dark ms-1">Expiring Soon</span>
                        @endif
                    </span>
                @else
                    —
                @endif
            </dd>
            <dt class="col-5 text-muted">Status</dt>
            <dd class="col-7"><span class="badge bg-{{ $complianceDocument->status === 'Active' ? 'success' : ($complianceDocument->status === 'Expiring Soon' ? 'warning text-dark' : 'danger') }}">{{ $complianceDocument->status }}</span></dd>
            <dt class="col-5 text-muted">Recorded At</dt><dd class="col-7 small text-muted">{{ $complianceDocument->created_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
