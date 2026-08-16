@extends('layouts.app')
@section('title', 'Company Profile')
@section('breadcrumb')
    <li class="breadcrumb-item active">Company</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div class="d-flex align-items-center gap-4">
            @if($company->logo_url)
            <img src="{{ $company->logo_url }}" alt="Logo" style="width:60px;height:60px;border-radius:12px;object-fit:contain;background:#fff;padding:6px;border:1.5px solid rgba(255,255,255,.3);">
            @else
            <div style="width:60px;height:60px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.6rem;">
                <i class="bi bi-building-fill-gear" style="color:#fff;"></i>
            </div>
            @endif
            <div>
                <h4 style="margin:0;">{{ $company->name }}</h4>
                <p style="margin:0;opacity:.8;">{{ $company->tagline ?? 'Company Profile' }}</p>
            </div>
        </div>
        <a href="{{ route('admin.company.edit') }}" class="btn btn-sm btn-primary-grad px-4">
            <i class="bi bi-pencil me-1"></i>Edit Profile
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Identity Card --}}
    <div class="col-md-6">
        <div class="table-card h-100">
            <div class="card-header"><span class="card-title"><i class="bi bi-info-circle me-2"></i>Identity</span></div>
            <div style="padding:20px;">
                <table class="table table-sm mb-0" style="font-size:.86rem;">
                    <tr><th style="width:140px;color:#6b7280;font-weight:500;">Name</th><td style="font-weight:600;">{{ $company->name }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Code</th><td><span style="font-family:monospace;background:#f0f4ff;color:#4f46e5;padding:2px 8px;border-radius:6px;font-size:.78rem;">{{ $company->code ?? '—' }}</span></td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">GST Number</th><td>{{ $company->gst_number ?: '—' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">PAN Number</th><td>{{ $company->pan_number ?: '—' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">CIN Number</th><td>{{ $company->cin_number ?: '—' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Currency</th><td>{{ $company->currency ?? 'INR' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Timezone</th><td>{{ $company->timezone ?? 'Asia/Kolkata' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Fin. Year Start</th>
                        <td>{{ collect([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'])->get($company->financial_year_start, 'April') }}</td>
                    </tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Status</th>
                        <td><span class="spill {{ $company->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $company->is_active ? 'Active' : 'Inactive' }}</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Contact Card --}}
    <div class="col-md-6">
        <div class="table-card h-100">
            <div class="card-header"><span class="card-title"><i class="bi bi-telephone me-2"></i>Contact & Location</span></div>
            <div style="padding:20px;">
                <table class="table table-sm mb-0" style="font-size:.86rem;">
                    <tr><th style="width:140px;color:#6b7280;font-weight:500;">Phone</th><td>{{ $company->phone ?: '—' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Email</th><td>{{ $company->email ?: '—' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Website</th>
                        <td>@if($company->website)<a href="{{ $company->website }}" target="_blank" style="color:#4f46e5;">{{ $company->website }}</a>@else—@endif</td>
                    </tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Address</th><td>{{ $company->address ?: '—' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">City</th><td>{{ $company->city ?: '—' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">State</th><td>{{ $company->state ?: '—' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Country</th><td>{{ $company->country ?: '—' }}</td></tr>
                    <tr><th style="color:#6b7280;font-weight:500;">Postal Code</th><td>{{ $company->postal_code ?: '—' }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Branches Summary --}}
    <div class="col-12">
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title"><i class="bi bi-diagram-2 me-2"></i>Branches ({{ $branches->count() }})</span>
                <a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-outline-primary px-3" style="border-radius:8px;font-size:.8rem;">Manage Branches</a>
            </div>
            @if($branches->isEmpty())
            <div class="empty-state" style="padding:32px 0;"><i class="bi bi-building"></i><p>No branches yet. <a href="{{ route('admin.branches.index') }}">Add one.</a></p></div>
            @else
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead><tr><th>Branch</th><th>Code</th><th>City</th><th>Manager</th><th>HQ</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($branches as $branch)
                        <tr>
                            <td style="font-weight:600;font-size:.87rem;">{{ $branch->name }}</td>
                            <td><span style="font-family:monospace;font-size:.75rem;background:#f0f4ff;color:#4f46e5;padding:2px 7px;border-radius:5px;">{{ $branch->code ?? '—' }}</span></td>
                            <td style="font-size:.83rem;color:#374151;">{{ $branch->city ?: '—' }}</td>
                            <td style="font-size:.83rem;color:#374151;">{{ $branch->manager->name ?? '—' }}</td>
                            <td>@if($branch->is_headquarters)<span style="background:#fef3c7;color:#d97706;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:700;">HQ</span>@else—@endif</td>
                            <td><span class="spill {{ $branch->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
