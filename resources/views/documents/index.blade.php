@extends('layouts.app')
@section('title', 'Documents')

@section('breadcrumb')
    <li class="breadcrumb-item active">Documents</li>
@endsection

@push('styles')
<style>
    /* ── Page Header ── */
    .doc-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 16px;
        padding: 28px 32px;
        color: #fff;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }
    .doc-header::after {
        content: '\F337';
        font-family: 'Bootstrap-icons';
        position: absolute;
        right: 24px; top: 50%;
        transform: translateY(-50%);
        font-size: 7rem;
        opacity: .08;
        line-height: 1;
    }

    /* ── Stat Pills ── */
    .doc-stat {
        background: rgba(255,255,255,.15);
        border-radius: 12px;
        padding: 10px 18px;
        display: inline-flex;
        flex-direction: column;
        min-width: 90px;
    }
    .doc-stat .val { font-size: 1.5rem; font-weight: 700; line-height: 1.1; }
    .doc-stat .lbl { font-size: .68rem; opacity: .8; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px; }

    /* ── Category Tabs ── */
    .cat-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
    .cat-tab {
        padding: 6px 16px;
        border-radius: 50px;
        font-size: .8rem;
        font-weight: 500;
        border: 1.5px solid var(--bs-border-color);
        background: transparent;
        color: var(--bs-body-color);
        text-decoration: none;
        transition: all .2s;
        cursor: pointer;
    }
    .cat-tab:hover { border-color: #4f46e5; color: #4f46e5; }
    .cat-tab.active { background: #4f46e5; border-color: #4f46e5; color: #fff; }

    /* ── Search Bar ── */
    .doc-search {
        position: relative;
        flex: 1;
        max-width: 420px;
    }
    .doc-search .bi-search {
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: .95rem;
    }
    .doc-search input {
        padding-left: 38px;
        border-radius: 10px;
        border: 1.5px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        height: 40px;
        font-size: .875rem;
    }
    .doc-search input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79,70,229,.1);
    }

    /* ── Document Card ── */
    .doc-card {
        border-radius: 14px;
        border: 1.5px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        transition: transform .2s, box-shadow .2s, border-color .2s;
        overflow: hidden;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .doc-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0,0,0,.10);
        border-color: #4f46e5;
    }
    .doc-card-accent {
        height: 4px;
        width: 100%;
    }
    .doc-card-body {
        padding: 20px;
        flex: 1;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .doc-card-body:hover { color: inherit; }

    /* File icon box */
    .doc-icon-box {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }

    .doc-card-footer {
        padding: 12px 20px;
        border-top: 1px solid var(--bs-border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: transparent;
    }

    /* Action buttons — always visible */
    .doc-actions .btn {
        width: 30px; height: 30px;
        padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px;
        font-size: .8rem;
        transition: all .15s;
    }

    /* ── Empty State ── */
    .empty-state {
        border-radius: 16px;
        border: 2px dashed var(--bs-border-color);
        padding: 64px 24px;
        text-align: center;
    }
    .empty-state-icon {
        width: 80px; height: 80px;
        background: rgba(79,70,229,.08);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem;
        color: #4f46e5;
        margin: 0 auto 20px;
    }

    /* ── Upload Modal ── */
    .upload-drop-zone {
        border: 2px dashed var(--bs-border-color);
        border-radius: 12px;
        padding: 32px 20px;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        background: var(--bs-body-bg);
    }
    .upload-drop-zone:hover,
    .upload-drop-zone.drag-over {
        border-color: #4f46e5;
        background: rgba(79,70,229,.04);
    }
    .upload-drop-zone .drop-icon {
        width: 56px; height: 56px;
        background: rgba(79,70,229,.1);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        color: #4f46e5;
        margin: 0 auto 12px;
    }

    /* Category colour map */
    .cat-general  { background: rgba(79,70,229,.1);  color: #4f46e5; }
    .cat-policy   { background: rgba(239,68,68,.1);  color: #ef4444; }
    .cat-hr       { background: rgba(16,185,129,.1); color: #10b981; }
    .cat-finance  { background: rgba(245,158,11,.1); color: #f59e0b; }
    .cat-other    { background: rgba(107,114,128,.1);color: #6b7280; }

    .accent-general  { background: #4f46e5; }
    .accent-policy   { background: #ef4444; }
    .accent-hr       { background: #10b981; }
    .accent-finance  { background: #f59e0b; }
    .accent-other    { background: #6b7280; }
</style>
@endpush

@section('content')

{{-- ── Header ── --}}
<div class="doc-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold mb-1">Company Documents</h4>
            <p class="mb-3 opacity-75 small">All files shared across the organisation — view, download or upload.</p>
            <div class="d-flex gap-3 flex-wrap">
                <div class="doc-stat">
                    <span class="val">{{ $documents->total() }}</span>
                    <span class="lbl">Total Files</span>
                </div>
                <div class="doc-stat">
                    <span class="val">5</span>
                    <span class="lbl">Categories</span>
                </div>
                <div class="doc-stat">
                    <span class="val">{{ $documents->count() }}</span>
                    <span class="lbl">This Page</span>
                </div>
            </div>
        </div>
        <button class="btn btn-light fw-semibold px-4 py-2 flex-shrink-0"
                data-bs-toggle="modal" data-bs-target="#uploadModal" style="border-radius:10px;">
            <i class="bi bi-cloud-upload me-2 text-primary"></i>Upload Document
        </button>
    </div>
</div>

{{-- ── Toolbar: Category Tabs + Search ── --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <form method="GET" id="filterForm" class="d-contents">
        {{-- Category tabs --}}
        <div class="cat-tabs">
            <a href="{{ route('documents.index', array_merge(request()->except('category','page'), [])) }}"
               class="cat-tab {{ !request('category') ? 'active' : '' }}">
                All
            </a>
            @foreach(['general' => 'General', 'policy' => 'Policy', 'hr' => 'HR', 'finance' => 'Finance', 'other' => 'Other'] as $val => $label)
            <a href="{{ route('documents.index', array_merge(request()->except('category','page'), ['category' => $val])) }}"
               class="cat-tab {{ request('category') === $val ? 'active' : '' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <div class="doc-search">
            <i class="bi bi-search"></i>
            <input type="text" name="search" class="form-control w-100"
                   placeholder="Search documents…" value="{{ request('search') }}"
                   onchange="this.form.submit()">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
        </div>
    </form>
</div>

{{-- ── Documents Grid ── --}}
@if($documents->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon"><i class="bi bi-folder2-open"></i></div>
        <h6 class="fw-semibold mb-2">No documents found</h6>
        <p class="text-muted small mb-4">
            @if(request('search') || request('category'))
                Try adjusting your filters or search term.
            @else
                Be the first to upload a document for your team!
            @endif
        </p>
        <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-upload me-2"></i>Upload First Document
        </button>
    </div>
@else
<div class="row g-4">
    @foreach($documents as $doc)
    @php
        $cat = $doc->category ?? 'general';
    @endphp
    <div class="col-sm-6 col-lg-4">
        <div class="doc-card">
            {{-- Accent top bar --}}
            <div class="doc-card-accent accent-{{ $cat }}"></div>

            {{-- Clickable body → opens preview --}}
            <a href="{{ route('documents.preview', $doc) }}" target="_blank" class="doc-card-body">
                <div class="d-flex gap-3 align-items-start">
                    <div class="doc-icon-box cat-{{ $cat }}">
                        <i class="bi {{ $doc->file_icon }}"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold lh-sm mb-1 text-truncate" title="{{ $doc->title }}">
                            {{ $doc->title }}
                        </div>
                        @if($doc->description)
                            <div class="text-muted small text-truncate mb-2">{{ $doc->description }}</div>
                        @endif
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge rounded-pill cat-{{ $cat }} px-2 py-1" style="font-size:.68rem;">
                                {{ ucfirst($cat) }}
                            </span>
                            <span class="text-muted" style="font-size:.72rem;">
                                <i class="bi bi-hdd me-1"></i>{{ $doc->file_size_formatted }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-2 border-top d-flex align-items-center gap-1 text-primary" style="font-size:.72rem;">
                    <i class="bi bi-eye-fill"></i>
                    <span>Click to view in browser</span>
                </div>
            </a>

            {{-- Footer: uploader + actions --}}
            <div class="doc-card-footer">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ $doc->uploader?->avatar_url ?? 'https://ui-avatars.com/api/?name=Unknown&background=6b7280&color=fff' }}" class="rounded-circle border"
                         width="26" height="26" alt="">
                    <div>
                        <div class="fw-semibold lh-1" style="font-size:.78rem;">{{ $doc->uploader?->name ?? 'Deleted User' }}</div>
                        <div class="text-muted" style="font-size:.65rem;">
                            <i class="bi bi-clock me-1"></i>{{ $doc->created_at->format('d M Y') }}
                            · {{ $doc->created_at->format('h:i A') }}
                        </div>
                    </div>
                </div>

                <div class="doc-actions d-flex gap-1">
                    <a href="{{ route('documents.preview', $doc) }}" target="_blank"
                       class="btn btn-outline-secondary" title="View in browser">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('documents.download', $doc) }}"
                       class="btn btn-outline-primary" title="Download">
                        <i class="bi bi-download"></i>
                    </a>
                    @if(auth()->id() === $doc->uploaded_by || auth()->user()->isAdmin())
                    <button class="btn btn-outline-danger" onclick="deleteDoc({{ $doc->id }})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if($documents->hasPages())
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-5">
    <span class="text-muted small">
        Showing <strong>{{ $documents->firstItem() }}</strong>–<strong>{{ $documents->lastItem() }}</strong>
        of <strong>{{ $documents->total() }}</strong> documents
    </span>
    {{ $documents->links('pagination::bootstrap-5') }}
</div>
@endif
@endif

{{-- ── Upload Modal ── --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="fw-bold mb-0">Upload Document</h5>
                    <p class="text-muted small mb-0">Share a file with the entire organisation</p>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="row g-3">

                        {{-- Title --}}
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Document Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                   placeholder="e.g. Employee Handbook 2025" required style="border-radius:10px;">
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Brief description…" style="border-radius:10px;"></textarea>
                        </div>

                        {{-- Category --}}
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Category <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 flex-wrap" id="catOptions">
                                @foreach(['general' => 'General', 'policy' => 'Policy', 'hr' => 'HR', 'finance' => 'Finance', 'other' => 'Other'] as $val => $label)
                                <label class="cat-tab-pick" data-val="{{ $val }}">
                                    <input type="radio" name="category" value="{{ $val }}" class="d-none"
                                           {{ $val === 'general' ? 'checked' : '' }}>
                                    {{ $label }}
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Drop Zone --}}
                        <div class="col-12">
                            <label class="form-label small fw-semibold">File <span class="text-danger">*</span></label>
                            <div class="upload-drop-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                                <div class="drop-icon"><i class="bi bi-cloud-upload"></i></div>
                                <div class="fw-semibold small mb-1">Drag & drop or click to browse</div>
                                <div class="text-muted" style="font-size:.75rem;">PDF, Word, Excel, images, ZIP — max 20 MB</div>
                                <div id="fileInfo" class="mt-2 text-primary small fw-semibold"></div>
                            </div>
                            <input type="file" name="file" id="fileInput" class="d-none" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4"
                            data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius:10px;">
                        <i class="bi bi-cloud-upload me-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<style>
    /* Category radio buttons inside modal */
    .cat-tab-pick {
        padding: 5px 14px;
        border-radius: 50px;
        font-size: .8rem;
        font-weight: 500;
        border: 1.5px solid var(--bs-border-color);
        cursor: pointer;
        transition: all .15s;
        user-select: none;
    }
    .cat-tab-pick:hover { border-color: #4f46e5; color: #4f46e5; }
    .cat-tab-pick.selected { background: #4f46e5; border-color: #4f46e5; color: #fff; }
</style>
<script>
// Category radio pill selection in modal
document.querySelectorAll('.cat-tab-pick').forEach(label => {
    const input = label.querySelector('input');
    if (input.checked) label.classList.add('selected');
    label.addEventListener('click', () => {
        document.querySelectorAll('.cat-tab-pick').forEach(l => l.classList.remove('selected'));
        label.classList.add('selected');
        input.checked = true;
    });
});

// File input & drag-drop
const fileInput  = document.getElementById('fileInput');
const dropZone   = document.getElementById('dropZone');
const fileInfo   = document.getElementById('fileInfo');

function showFileInfo(file) {
    if (!file) return;
    const size = file.size >= 1048576
        ? (file.size / 1048576).toFixed(2) + ' MB'
        : (file.size / 1024).toFixed(1) + ' KB';
    fileInfo.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>${file.name} (${size})`;
}

fileInput.addEventListener('change', () => showFileInfo(fileInput.files[0]));

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', ()  => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const dt  = e.dataTransfer;
    fileInput.files = dt.files;
    showFileInfo(dt.files[0]);
});

// Delete
function deleteDoc(id) {
    APP.confirm('Delete Document', 'This cannot be undone.', () => {
        APP.ajax(`/documents/${id}`, 'DELETE')
            .done(res => { if (res.success) { APP.toast(res.message); location.reload(); } })
            .fail(() => APP.toast('Failed to delete', 'error'));
    });
}
</script>
@endpush
