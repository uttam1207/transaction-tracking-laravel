@extends('layouts.app')

@section('title', 'Edit Question')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('questions.show', $question) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-semibold">Edit Question</h4>
            <p class="text-muted small mb-0">Update your question details</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Edit Question
                    </h6>
                    <span class="badge bg-secondary">Q#{{ $question->id }}</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('questions.update', $question) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Title --}}
                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">
                                Title <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                class="form-control form-control-lg @error('title') is-invalid @enderror"
                                value="{{ old('title', $question->title) }}"
                                placeholder="What is your question? Be specific and clear…"
                                maxlength="255"
                                autofocus
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Minimum 10 characters.</div>
                            @enderror
                        </div>

                        {{-- Body --}}
                        <div class="mb-4">
                            <label for="body" class="form-label fw-semibold">
                                Details <span class="text-danger">*</span>
                            </label>
                            <textarea
                                id="body"
                                name="body"
                                rows="10"
                                class="form-control @error('body') is-invalid @enderror"
                                placeholder="Describe your question in detail…"
                            >{{ old('body', $question->body) }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Minimum 20 characters.</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="mb-4">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="open"     {{ old('status', $question->status) === 'open'     ? 'selected' : '' }}>Open</option>
                                <option value="resolved" {{ old('status', $question->status) === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed"   {{ old('status', $question->status) === 'closed'   ? 'selected' : '' }}>Closed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <strong>Open</strong> — accepting answers &nbsp;·&nbsp;
                                <strong>Resolved</strong> — answer accepted &nbsp;·&nbsp;
                                <strong>Closed</strong> — no new answers
                            </div>
                        </div>

                        {{-- Pin (super_admin only) --}}
                        @if(auth()->user()->isSuperAdmin())
                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        id="is_pinned"
                                        name="is_pinned"
                                        value="1"
                                        {{ old('is_pinned', $question->is_pinned) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label fw-semibold" for="is_pinned">
                                        <i class="bi bi-pin-angle me-1 text-warning"></i> Pin this question
                                    </label>
                                </div>
                                <div class="form-text">Pinned questions appear at the top of the list.</div>
                            </div>
                        @endif

                        {{-- Meta info --}}
                        <div class="alert alert-light border mb-4">
                            <div class="d-flex gap-4 small text-muted">
                                <span><i class="bi bi-person me-1"></i>{{ $question->user->name }}</span>
                                <span><i class="bi bi-calendar me-1"></i>{{ $question->created_at->format('d M Y, H:i') }}</span>
                                <span><i class="bi bi-eye me-1"></i>{{ number_format($question->views) }} views</span>
                                <span><i class="bi bi-chat me-1"></i>{{ $question->answers->count() }} answers</span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('questions.show', $question) }}" class="btn btn-outline-secondary px-4">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-warning px-5">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
