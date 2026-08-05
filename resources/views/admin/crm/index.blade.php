@extends('layouts.app')
@section('title', 'CRM & Customer Relationships')

@section('breadcrumb')
    <li class="breadcrumb-item active">CRM</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>CRM & Lead Management</h4>
            <p>Milk Buyers, Animal Buyers, Franchise Leads, Investors, Govt Officials & Vet Doctors</p>
        </div>
        <a href="{{ route('admin.crm.create') }}" class="btn btn-primary-grad btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>Add Contact
        </a>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#2563eb,#4f46e5);">
            <i class="bi bi-people-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['total_buyers'] }}</div>
            <div class="kpi-label">Milk Buyers</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-star-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['franchise_leads'] }}</div>
            <div class="kpi-label">Franchise Leads</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#0d9488);">
            <i class="bi bi-cash-coin kpi-icon"></i>
            <div class="kpi-value">{{ $summary['investors'] }}</div>
            <div class="kpi-label">Investors</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#db2777);">
            <i class="bi bi-graph-up-arrow kpi-icon"></i>
            <div class="kpi-value" style="font-size:1.35rem;">&#8377;{{ number_format($summary['total_business'],0) }}</div>
            <div class="kpi-label">Business Value</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.crm.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search Contact</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;"><i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Name, phone or email&#8230;" value="{{ request('search') }}" style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Category</label>
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach(['Milk Buyer','Animal Buyer','Franchise Lead','Investor','Govt Official','Vet Doctor'] as $cat)
                    <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['Active','Inactive','Lead','Prospect'] as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','category','status']))
                <a href="{{ route('admin.crm.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear">
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
            <h6 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-primary"></i>CRM Directory</h6>
            @if(request()->hasAny(['search','category','status']))
                <span class="badge" style="background:var(--primary-soft);color:var(--primary);font-size:.72rem;font-weight:600;padding:4px 10px;border-radius:20px;"><i class="bi bi-funnel-fill me-1"></i>Filtered</span>
            @endif
        </div>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $customers->total() }} contacts</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Name</th><th>Category</th><th>Phone</th>
                    <th class="text-end">Business Value</th><th>Status</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td>
                            <div class="fw-bold" style="color:var(--primary);font-size:.87rem;">{{ $c->name }}</div>
                            <div style="font-size:.73rem;color:#9ca3af;">{{ $c->email ?? '' }}</div>
                        </td>
                        <td><span class="spill spill-info">{{ $c->category }}</span></td>
                        <td style="font-size:.82rem;">{{ $c->phone ?? '&#8212;' }}</td>
                        <td class="text-end fw-bold text-success">&#8377;{{ number_format($c->total_business_value,0) }}</td>
                        <td>
                            @php $sColor = $c->status === 'Active' ? 'spill-success' : ($c->status === 'Lead' ? 'spill-warning' : 'spill-secondary'); @endphp
                            <span class="spill {{ $sColor }}">{{ $c->status }}</span>
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('admin.crm.show',$c) }}" class="act-btn act-view"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.crm.edit',$c) }}" class="act-btn act-edit"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state"><i class="bi bi-people"></i><p>No CRM contacts registered</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $customers->firstItem() }}</strong>&#8211;<strong>{{ $customers->lastItem() }}</strong> of <strong>{{ $customers->total() }}</strong> contacts
            </div>
            {{ $customers->links() }}
        </div>
    @endif
</div>

@endsection
