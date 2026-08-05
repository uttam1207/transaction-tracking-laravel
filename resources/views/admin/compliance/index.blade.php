@extends('layouts.app')
@section('title', 'Compliance & Government Document Center')

@section('breadcrumb')
    <li class="breadcrumb-item active">Compliance</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Compliance & Certificates</h4>
            <p>FSSAI license, Animal Insurance, Vaccination certificates, Bank Loan documents & Audit files</p>
        </div>
        <a href="{{ route('admin.compliance.create') }}" class="btn btn-primary-grad btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>Log Document
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-6">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-shield-fill-check kpi-icon"></i>
            <div class="kpi-value">{{ $summary['active_docs'] }}</div>
            <div class="kpi-label">Active Documents</div>
        </div>
    </div>
    <div class="col-6 col-md-6">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-exclamation-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['expiring_soon'] }}</div>
            <div class="kpi-label">Expiring / Action Required</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.compliance.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Document</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;"><i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Document title or number&#8230;" value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Category</label>
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach(['FSSAI','Animal Insurance','Vaccination Certificate','Bank Loan','Audit Report','Other'] as $cat)
                    <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['Active','Expired','Pending Renewal','Action Required'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','category','status']))
                <a href="{{ route('admin.compliance.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Table --}}
<div class="card-glass">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <div class="d-flex align-items-center gap-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-folder-check me-2 text-primary"></i>Compliance Repository</h6>
            @if(request()->hasAny(['search','category','status']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $documents->total() }} documents</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Title</th><th>Category</th><th>Number</th>
                    <th>Issue Date</th><th>Expiry Date</th><th>Status</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $d)
                    <tr>
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $d->document_title }}</div>
                        </td>
                        <td><span class="spill spill-info">{{ $d->category }}</span></td>
                        <td><code style="font-size:.78rem;background:#f5f7fa;border:1px solid #e5e7eb;border-radius:5px;padding:2px 6px;">{{ $d->document_number ?? '&#8212;' }}</code></td>
                        <td style="font-size:.82rem;">{{ $d->issue_date?->format('d M Y') ?? '&#8212;' }}</td>
                        <td style="font-size:.82rem;">
                            @if($d->expiry_date)
                                @php $expiring = $d->expiry_date->lte(now()->addDays(30)); @endphp
                                <span class="{{ $expiring ? 'text-danger fw-bold' : '' }}">
                                    {{ $d->expiry_date->format('d M Y') }}
                                    @if($expiring)<i class="bi bi-exclamation-circle ms-1 text-danger"></i>@endif
                                </span>
                            @else
                                &#8212;
                            @endif
                        </td>
                        <td>
                            @php
                                $sClass = match($d->status) {
                                    'Active'           => 'spill-success',
                                    'Expired'          => 'spill-danger',
                                    'Pending Renewal'  => 'spill-warning',
                                    default            => 'spill-secondary',
                                };
                            @endphp
                            <span class="spill {{ $sClass }}">{{ $d->status }}</span>
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.compliance.show',$d) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.compliance.edit',$d) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state"><i class="bi bi-shield-check"></i><p>No compliance documents recorded</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($documents->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $documents->firstItem() }}</strong>&#8211;<strong>{{ $documents->lastItem() }}</strong> of <strong>{{ $documents->total() }}</strong> documents
            </div>
            {{ $documents->links() }}
        </div>
    @endif
</div>

@endsection
