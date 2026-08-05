@extends('layouts.app')
@section('title', 'Breed Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.animals.index') }}">Animals</a></li>
    <li class="breadcrumb-item active">Breeds</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Breed Management</h4>
            <p>Add, edit and delete animal breeds used across the herd register</p>
        </div>
        <a href="{{ route('admin.animals.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Animals
        </a>
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
            <i class="bi bi-collection-fill kpi-icon"></i>
            <div class="kpi-value">{{ $breeds->count() }}</div>
            <div class="kpi-label">Total Breeds</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0891b2,#2563eb);">
            <i class="bi bi-water kpi-icon"></i>
            <div class="kpi-value">{{ $breeds->where('animal_type','Buffalo')->count() }}</div>
            <div class="kpi-label">Buffalo Breeds</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-star-fill kpi-icon"></i>
            <div class="kpi-value">{{ $breeds->where('animal_type','Cow')->count() }}</div>
            <div class="kpi-label">Cow Breeds</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#0d9488);">
            <i class="bi bi-bar-chart-fill kpi-icon"></i>
            <div class="kpi-value">{{ $breeds->sum('animals_count') }}</div>
            <div class="kpi-label">Animals Registered</div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Add Breed Form --}}
    <div class="col-md-4">
        <div class="card-glass p-4">
            <h6 class="form-section-label"><i class="bi bi-plus-circle me-1"></i>Add New Breed</h6>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2">
                    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2">
                    {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.breeds.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Breed Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        placeholder="e.g. Murrah" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Animal Type <span class="text-danger">*</span></label>
                    <select name="animal_type" class="form-select @error('animal_type') is-invalid @enderror" required>
                        @foreach(['Buffalo','Cow','Goat','Sheep','Other'] as $t)
                            <option value="{{ $t }}" @selected(old('animal_type')===$t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('animal_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                        rows="3" placeholder="Brief notes about this breed (optional)">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary-grad w-100">
                    <i class="bi bi-save me-1"></i>Save Breed
                </button>
            </form>
        </div>
    </div>

    {{-- Breeds Table --}}
    <div class="col-md-8">
        <div class="card-glass">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-collection me-2 text-primary"></i>Breed Registry</h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $breeds->count() }} breeds</span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Breed Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-center">Animals</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($breeds as $breed)
                            <tr>
                                <td class="fw-semibold">{{ $breed->name }}</td>
                                <td>
                                    @php
                                        $typeColor = match($breed->animal_type) {
                                            'Buffalo' => 'spill-primary',
                                            'Cow'     => 'spill-success',
                                            'Goat'    => 'spill-warning',
                                            'Sheep'   => 'spill-info',
                                            default   => 'spill-secondary',
                                        };
                                    @endphp
                                    <span class="spill {{ $typeColor }}">{{ $breed->animal_type }}</span>
                                </td>
                                <td class="text-muted" style="font-size:.82rem;max-width:220px;">
                                    {{ $breed->description ? Str::limit($breed->description, 60) : '—' }}
                                </td>
                                <td class="text-center">
                                    @if($breed->animals_count > 0)
                                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">{{ $breed->animals_count }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="act-btn act-edit"
                                        onclick="openEditModal({{ $breed->id }}, '{{ addslashes($breed->name) }}', '{{ $breed->animal_type }}', '{{ addslashes($breed->description ?? '') }}')"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.breeds.destroy', $breed) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Delete breed \'{{ addslashes($breed->name) }}\'? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="act-btn act-delete" title="Delete"
                                            {{ $breed->animals_count > 0 ? 'disabled title=Animals assigned — cannot delete' : '' }}>
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state"><i class="bi bi-collection"></i><p>No breeds registered yet</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Edit Breed Modal --}}
<div class="modal fade" id="editBreedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;border:1px solid #e5e7eb;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Breed</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBreedForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body px-4 pb-0">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Breed Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Animal Type <span class="text-danger">*</span></label>
                        <select name="animal_type" id="editAnimalType" class="form-select" required>
                            @foreach(['Buffalo','Cow','Goat','Sheep','Other'] as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-check-lg me-1"></i>Update Breed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(id, name, animalType, description) {
    document.getElementById('editName').value        = name;
    document.getElementById('editAnimalType').value  = animalType;
    document.getElementById('editDescription').value = description;
    document.getElementById('editBreedForm').action  = '/admin/breeds/' + id;
    new bootstrap.Modal(document.getElementById('editBreedModal')).show();
}
</script>
@endpush
