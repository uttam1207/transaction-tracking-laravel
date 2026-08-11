@extends('layouts.app')
@section('title', 'Contacts & Connections')

@section('breadcrumb')
    <li class="breadcrumb-item active">Contacts</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Contacts &amp; Connections</h4>
            <p>Centralized directory — vendors, clients, vets, contractors &amp; more, organised by category</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.contact-categories.index') }}" class="btn btn-sm btn-outline-light px-4">
                <i class="bi bi-tags me-1"></i>Categories
            </a>
            <a href="{{ route('admin.contacts.create') }}" class="btn btn-primary-grad btn-sm px-4">
                <i class="bi bi-plus-lg me-1"></i>Add Contact
            </a>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
            <i class="bi bi-people-fill kpi-icon"></i>
            <div class="kpi-value">{{ $totalContacts }}</div>
            <div class="kpi-label">Total Contacts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#0d9488);">
            <i class="bi bi-person-check-fill kpi-icon"></i>
            <div class="kpi-value">{{ $activeContacts }}</div>
            <div class="kpi-label">Active</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-tags-fill kpi-icon"></i>
            <div class="kpi-value">{{ $totalCategories }}</div>
            <div class="kpi-label">Categories</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0891b2,#0284c7);">
            <i class="bi bi-person-plus-fill kpi-icon"></i>
            <div class="kpi-value">{{ $recentContacts }}</div>
            <div class="kpi-label">Added (30 days)</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Category Quick-Filter Chips --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.contacts.index', array_merge(request()->except('category_id'), [])) }}"
       class="badge px-3 py-2 text-decoration-none {{ !request('category_id') ? 'bg-primary' : 'bg-light text-dark border' }}"
       style="font-size:.78rem;border-radius:20px;">
        All
    </a>
    @foreach($categories as $cat)
        <a href="{{ route('admin.contacts.index', array_merge(request()->except('category_id'), ['category_id' => $cat->id])) }}"
           class="badge px-3 py-2 text-decoration-none {{ request('category_id') == $cat->id ? 'text-white' : 'bg-light text-dark border' }}"
           style="font-size:.78rem;border-radius:20px;{{ request('category_id') == $cat->id ? 'background:'.$cat->color.'!important;' : '' }}">
            <i class="bi {{ $cat->icon }} me-1"></i>{{ $cat->name }}
            <span class="ms-1 opacity-75">({{ $cat->contacts_count ?? $cat->contacts()->count() }})</span>
        </a>
    @endforeach
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.contacts.index') }}">
    @if(request('category_id'))
        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
    @endif
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Search</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#f5f7fa;border-right:0;border-color:#e5e7eb;">
                    <i class="bi bi-search" style="color:#9ca3af;font-size:.8rem;"></i>
                </span>
                <input type="text" name="search" class="form-control" placeholder="Name, phone, email, company or city…"
                    value="{{ request('search') }}"
                    style="border-left:0!important;border-color:#e5e7eb!important;padding-left:0!important;">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;color:#6b7280;margin-bottom:4px;">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="1" @selected(request('status')==='1')>Active</option>
                <option value="0" @selected(request('status')==='0')>Inactive</option>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad flex-fill" style="height:42px;border-radius:9px;font-size:.85rem;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status']) || request('category_id'))
                <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
                   style="height:42px;width:42px;border-radius:9px;flex-shrink:0;" title="Clear filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- Contacts Table --}}
<div class="card-glass">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="bi bi-card-list me-2 text-primary"></i>Directory</h6>
        <span style="font-size:.78rem;color:#6b7280;font-weight:600;">{{ $contacts->total() }} contacts</span>
    </div>

    <div class="table-responsive">
        <table class="table modern-table mb-0" style="min-width:860px;">
            <thead>
                <tr>
                    <th style="width:52px;"></th>
                    <th>Name / Company</th>
                    <th>Category</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>City</th>
                    <th style="width:90px;">Status</th>
                    <th style="width:80px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $c)
                    @php
                        $initials = strtoupper(substr($c->name, 0, 1) . (str_contains($c->name, ' ') ? substr($c->name, strpos($c->name,' ')+1, 1) : ''));
                        $catColor = $c->category->color ?? '#6366f1';
                    @endphp
                    <tr>
                        {{-- Avatar --}}
                        <td style="padding-left:16px;">
                            <div style="width:38px;height:38px;border-radius:10px;background:{{ $catColor }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:800;">
                                {{ $initials }}
                            </div>
                        </td>

                        {{-- Name / Company --}}
                        <td>
                            <a href="{{ route('admin.contacts.show', $c) }}"
                               style="font-weight:700;color:var(--primary);font-size:.88rem;text-decoration:none;display:block;line-height:1.25;">
                                {{ $c->name }}
                            </a>
                            @if($c->company)
                                <div style="font-size:.73rem;color:#6b7280;margin-top:1px;">
                                    <i class="bi bi-building me-1"></i>{{ $c->company }}
                                    @if($c->designation)
                                        <span class="text-muted"> &mdash; {{ $c->designation }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>

                        {{-- Category --}}
                        <td>
                            <span class="badge px-2 py-1" style="background:{{ $catColor }}20;color:{{ $catColor }};font-size:.72rem;font-weight:600;border-radius:6px;">
                                <i class="bi {{ $c->category->icon ?? 'bi-people' }} me-1"></i>{{ $c->category->name ?? '—' }}
                            </span>
                        </td>

                        {{-- Phone --}}
                        <td style="font-size:.83rem;color:#374151;">
                            @if($c->phone)
                                <a href="tel:{{ $c->phone }}" style="color:#374151;text-decoration:none;">
                                    <i class="bi bi-telephone me-1 text-success"></i>{{ $c->phone }}
                                </a>
                                @if($c->alternate_phone)
                                    <div style="font-size:.72rem;color:#9ca3af;margin-top:1px;">
                                        <i class="bi bi-telephone-fill me-1"></i>{{ $c->alternate_phone }}
                                    </div>
                                @endif
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>

                        {{-- Email --}}
                        <td style="font-size:.82rem;">
                            @if($c->email)
                                <a href="mailto:{{ $c->email }}" style="color:#4f46e5;text-decoration:none;">
                                    <i class="bi bi-envelope me-1"></i>{{ $c->email }}
                                </a>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>

                        {{-- City --}}
                        <td style="font-size:.82rem;color:#6b7280;">
                            @if($c->city)
                                <i class="bi bi-geo-alt me-1"></i>{{ $c->city }}
                                @if($c->state)
                                    <span class="text-muted">, {{ $c->state }}</span>
                                @endif
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            <span class="spill {{ $c->is_active ? 'spill-success' : 'spill-danger' }}">
                                {{ $c->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td style="text-align:center;">
                            <a href="{{ route('admin.contacts.show', $c) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.contacts.edit', $c) }}" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.contacts.destroy', $c) }}" style="display:inline;"
                                  onsubmit="return confirm('Delete contact {{ addslashes($c->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="act-btn act-delete" title="Delete" style="border:none;background:none;cursor:pointer;padding:0;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <i class="bi bi-people"></i>
                            <p>{{ request()->hasAny(['search','status','category_id']) ? 'No contacts match your filters.' : 'No contacts added yet.' }}</p>
                            @unless(request()->hasAny(['search','status','category_id']))
                                <a href="{{ route('admin.contacts.create') }}" class="btn btn-primary-grad btn-sm px-4 mt-2">
                                    <i class="bi bi-plus-lg me-1"></i>Add First Contact
                                </a>
                            @endunless
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($contacts->hasPages())
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div style="font-size:.78rem;color:#9ca3af;">
                Showing <strong>{{ $contacts->firstItem() }}</strong>–<strong>{{ $contacts->lastItem() }}</strong>
                of <strong>{{ $contacts->total() }}</strong> contacts
            </div>
            {{ $contacts->links() }}
        </div>
    @endif
</div>

@endsection