@extends('layouts.app')
@section('title', 'Documents')

@section('breadcrumb')
    <li class="breadcrumb-item active">Documents</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold">Documents</h5>
        <div class="text-muted small">Company documents shared with everyone</div>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="bi bi-upload me-1"></i>Upload Document
    </button>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search by title or description..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach(['general' => 'General', 'policy' => 'Policy', 'hr' => 'HR', 'finance' => 'Finance', 'other' => 'Other'] as $val => $label)
                        <option value="{{ $val }}" {{ request('category') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-search"></i></button>
                <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Documents Grid -->
@if($documents->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-folder2-open fs-1 text-muted"></i>
        <div class="mt-3 text-muted">No documents found. Be the first to upload one!</div>
    </div>
</div>
@else
<div class="row g-3">
    @foreach($documents as $doc)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border hover-shadow">
            <a href="{{ route('documents.preview', $doc) }}" target="_blank"
               class="card-body text-decoration-none text-body d-block">
                <div class="d-flex align-items-start gap-3">
                    <div class="fs-1">
                        <i class="bi {{ $doc->file_icon }}"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold text-truncate" title="{{ $doc->title }}">{{ $doc->title }}</div>
                        @if($doc->description)
                            <div class="small text-muted text-truncate mt-1">{{ $doc->description }}</div>
                        @endif
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            <span class="badge bg-primary-subtle text-primary small">
                                {{ ucfirst($doc->category) }}
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary small">
                                {{ $doc->file_size_formatted }}
                            </span>
                        </div>
                        <div class="text-primary mt-2" style="font-size:.72rem">
                            <i class="bi bi-eye me-1"></i>Click to view
                        </div>
                    </div>
                </div>
            </a>
            <div class="card-footer bg-transparent py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $doc->uploader->avatar_url }}" class="rounded-circle" width="22" height="22" alt="">
                        <div>
                            <div class="small fw-semibold lh-1">{{ $doc->uploader->name }}</div>
                            <div class="text-muted" style="font-size:.65rem">{{ $doc->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('documents.preview', $doc) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary py-0 px-2" title="View in browser">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('documents.download', $doc) }}"
                           class="btn btn-sm btn-outline-primary py-0 px-2" title="Download">
                            <i class="bi bi-download"></i>
                        </a>
                        @if(auth()->id() === $doc->uploaded_by || auth()->user()->isAdmin())
                        <button class="btn btn-sm btn-outline-danger py-0 px-2"
                                onclick="deleteDoc({{ $doc->id }})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($documents->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <span class="small text-muted">Showing {{ $documents->firstItem() }}–{{ $documents->lastItem() }} of {{ $documents->total() }}</span>
    {{ $documents->links('pagination::bootstrap-5') }}
</div>
@endif
@endif

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-upload me-2"></i>Upload Document</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Document Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Employee Handbook 2024" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Brief description of this document..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="general">General</option>
                                <option value="policy">Policy</option>
                                <option value="hr">HR</option>
                                <option value="finance">Finance</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">File *</label>
                            <input type="file" name="file" class="form-control" required id="fileInput">
                            <div class="form-text">Max 20 MB. PDF, Word, Excel, images, ZIP etc.</div>
                            <div id="fileInfo" class="small text-muted mt-1"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload me-1"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .hover-shadow { transition: box-shadow 0.2s, transform 0.2s; }
    .hover-shadow:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.10); transform: translateY(-2px); }
</style>
@endpush

@push('scripts')
<script>
document.getElementById('fileInput')?.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const size = file.size >= 1048576
            ? (file.size / 1048576).toFixed(2) + ' MB'
            : (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('fileInfo').textContent = file.name + ' (' + size + ')';
    }
});

function deleteDoc(id) {
    APP.confirm('Delete Document', 'This cannot be undone.', () => {
        APP.ajax(`/documents/${id}`, 'DELETE')
            .done(res => { if (res.success) { APP.toast(res.message); location.reload(); } })
            .fail(() => APP.toast('Failed to delete', 'error'));
    });
}
</script>
@endpush
