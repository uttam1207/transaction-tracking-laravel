@extends('layouts.app')
@section('title', 'Action Type Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.animals.index') }}">Animals</a></li>
    <li class="breadcrumb-item active">Action Types</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Action Type Management</h4>
            <p>Add, rename and delete animal action types used in the Log Action form</p>
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
            <i class="bi bi-list-check kpi-icon"></i>
            <div class="kpi-value">{{ $actionTypes->count() }}</div>
            <div class="kpi-label">Total Types</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#0891b2,#2563eb);">
            <i class="bi bi-lock-fill kpi-icon"></i>
            <div class="kpi-value">{{ $actionTypes->where('is_system', true)->count() }}</div>
            <div class="kpi-label">System Types</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#0d9488);">
            <i class="bi bi-plus-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $actionTypes->where('is_system', false)->count() }}</div>
            <div class="kpi-label">Custom Types</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#ea580c);">
            <i class="bi bi-check-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $actionTypes->where('is_active', true)->count() }}</div>
            <div class="kpi-label">Active Types</div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Add Action Type Form --}}
    <div class="col-md-4">
        <div class="card-glass p-4">
            <h6 class="form-section-label"><i class="bi bi-plus-circle me-1"></i>Add New Action Type</h6>

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

            <form action="{{ route('admin.action-types.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Action Type Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="e.g. Weight Check, Hoof Trimming"
                        value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary-grad w-100">
                    <i class="bi bi-save me-1"></i>Save Action Type
                </button>
            </form>

            <div class="mt-4 p-3" style="background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb;">
                <div style="font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
                    <i class="bi bi-info-circle me-1"></i>Note
                </div>
                <div style="font-size:.78rem;color:#6b7280;line-height:1.5;">
                    <i class="bi bi-lock-fill me-1 text-primary"></i><strong>System types</strong> (the 9 built-in ones) cannot be deleted.<br>
                    Custom types you add here can be renamed or deleted freely.
                </div>
            </div>
        </div>
    </div>

    {{-- Action Types Table --}}
    <div class="col-md-8">
        <div class="card-glass">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>All Action Types</h6>
                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $actionTypes->count() }} types</span>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action Type Name</th>
                            <th class="text-center">Kind</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actionTypes as $type)
                            <tr>
                                <td style="color:#9ca3af;font-size:.8rem;">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">
                                    {{ $type->name }}
                                    @if($type->is_system)
                                        <i class="bi bi-lock-fill ms-1" style="font-size:.7rem;color:#9ca3af;" title="System type — cannot be deleted"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($type->is_system)
                                        <span class="spill spill-primary" style="font-size:.72rem;">System</span>
                                    @else
                                        <span class="spill spill-success" style="font-size:.72rem;">Custom</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="act-btn act-edit"
                                        onclick="openEditModal({{ $type->id }}, '{{ addslashes($type->name) }}')"
                                        title="Rename">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if(!$type->is_system)
                                        <form action="{{ route('admin.action-types.destroy', $type) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete action type \'{{ addslashes($type->name) }}\'? Existing records will keep the old name.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="act-btn act-delete" title="Delete">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="act-btn" disabled title="System type — cannot be deleted" style="opacity:.35;cursor:not-allowed;">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="bi bi-list-check"></i>
                                    <p>No action types found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Edit / Rename Modal --}}
<div class="modal fade" id="editTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;border:1px solid #e5e7eb;">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Rename Action Type</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTypeForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body px-4 pb-0">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Action Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editTypeName" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-grad px-4">
                        <i class="bi bi-check-lg me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(id, name) {
    document.getElementById('editTypeName').value = name;
    document.getElementById('editTypeForm').action = '/admin/action-types/' + id;
    new bootstrap.Modal(document.getElementById('editTypeModal')).show();
}
</script>
@endpush