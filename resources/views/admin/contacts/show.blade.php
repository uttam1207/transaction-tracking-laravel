@extends('layouts.app')
@section('title', $contact->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.contacts.index') }}">Contacts</a></li>
    <li class="breadcrumb-item active">{{ $contact->name }}</li>
@endsection

@section('content')

@php
    $catColor = $contact->category->color ?? '#6366f1';
    $catIcon  = $contact->category->icon  ?? 'bi-people';
    $initials = strtoupper(substr($contact->name, 0, 1) . (str_contains($contact->name, ' ') ? substr($contact->name, strpos($contact->name,' ')+1, 1) : ''));
@endphp

<div class="page-hero" style="background:linear-gradient(135deg, {{ $catColor }}dd, {{ $catColor }}88); margin-bottom:0; border-radius:16px 16px 0 0;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div class="d-flex align-items-center gap-4">
            <div style="width:64px;height:64px;border-radius:16px;background:rgba(255,255,255,0.25);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:#fff;border:2px solid rgba(255,255,255,0.4);">
                {{ $initials }}
            </div>
            <div>
                <h4 class="mb-1">{{ $contact->name }}</h4>
                @if($contact->designation || $contact->company)
                    <p class="mb-1" style="opacity:.85;">
                        {{ $contact->designation }}
                        @if($contact->designation && $contact->company) &mdash; @endif
                        {{ $contact->company }}
                    </p>
                @endif
                <span class="badge px-3 py-1" style="background:rgba(255,255,255,0.25);font-size:.75rem;border-radius:20px;">
                    <i class="bi {{ $catIcon }} me-1"></i>{{ $contact->category->name ?? '—' }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.contacts.edit', $contact) }}" class="btn btn-sm btn-outline-light px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-light px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row g-4 mt-0">

    {{-- Left: Details --}}
    <div class="col-lg-8">

        {{-- Contact Details --}}
        <div class="form-section mb-4">
            <div class="form-section-header"><i class="bi bi-telephone"></i>Contact Details</div>
            <div class="form-section-body">
                <div class="row g-3">
                    @if($contact->phone)
                    <div class="col-md-6">
                        <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Primary Phone</div>
                        <a href="tel:{{ $contact->phone }}" style="font-size:.95rem;font-weight:600;color:#374151;text-decoration:none;">
                            <i class="bi bi-telephone-fill text-success me-1"></i>{{ $contact->phone }}
                        </a>
                    </div>
                    @endif
                    @if($contact->alternate_phone)
                    <div class="col-md-6">
                        <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Alternate Phone</div>
                        <a href="tel:{{ $contact->alternate_phone }}" style="font-size:.95rem;font-weight:600;color:#374151;text-decoration:none;">
                            <i class="bi bi-telephone-fill text-info me-1"></i>{{ $contact->alternate_phone }}
                        </a>
                    </div>
                    @endif
                    @if($contact->email)
                    <div class="col-12">
                        <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Email</div>
                        <a href="mailto:{{ $contact->email }}" style="font-size:.9rem;font-weight:600;color:#4f46e5;text-decoration:none;">
                            <i class="bi bi-envelope-fill me-1"></i>{{ $contact->email }}
                        </a>
                    </div>
                    @endif
                    @if(!$contact->phone && !$contact->alternate_phone && !$contact->email)
                    <div class="col-12 text-muted" style="font-size:.85rem;">No contact details recorded.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Address --}}
        @if($contact->address || $contact->city || $contact->state || $contact->pincode)
        <div class="form-section mb-4">
            <div class="form-section-header"><i class="bi bi-geo-alt"></i>Address</div>
            <div class="form-section-body">
                <div style="font-size:.9rem;color:#374151;line-height:1.8;">
                    @if($contact->address)
                        <div>{{ $contact->address }}</div>
                    @endif
                    @if($contact->city || $contact->state)
                        <div>{{ implode(', ', array_filter([$contact->city, $contact->state])) }}</div>
                    @endif
                    @if($contact->pincode)
                        <div>PIN: {{ $contact->pincode }}</div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($contact->notes)
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-sticky"></i>Notes</div>
            <div class="form-section-body">
                <p style="font-size:.9rem;color:#374151;white-space:pre-line;margin:0;">{{ $contact->notes }}</p>
            </div>
        </div>
        @endif

    </div>

    {{-- Right: Meta --}}
    <div class="col-lg-4">
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-info-circle"></i>Record Info</div>
            <div class="form-section-body">
                <div class="mb-3">
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Status</div>
                    <span class="spill {{ $contact->is_active ? 'spill-success' : 'spill-danger' }}">
                        {{ $contact->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="mb-3">
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Category</div>
                    <span class="badge px-2 py-1" style="background:{{ $catColor }}20;color:{{ $catColor }};font-size:.78rem;font-weight:600;border-radius:6px;">
                        <i class="bi {{ $catIcon }} me-1"></i>{{ $contact->category->name ?? '—' }}
                    </span>
                </div>
                <div class="mb-3">
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Added On</div>
                    <div style="font-size:.85rem;color:#374151;">{{ $contact->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Last Updated</div>
                    <div style="font-size:.85rem;color:#374151;">{{ $contact->updated_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="form-section mt-4">
            <div class="form-section-header"><i class="bi bi-lightning"></i>Quick Actions</div>
            <div class="form-section-body d-flex flex-column gap-2">
                @if($contact->phone)
                <a href="tel:{{ $contact->phone }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-telephone me-1"></i>Call Primary
                </a>
                @endif
                @if($contact->email)
                <a href="mailto:{{ $contact->email }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-envelope me-1"></i>Send Email
                </a>
                @endif
                <a href="{{ route('admin.contacts.edit', $contact) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit Contact
                </a>
                <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}"
                      onsubmit="return confirm('Delete this contact permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash me-1"></i>Delete Contact
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection