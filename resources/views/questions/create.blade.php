@extends('layouts.app')

@section('title', 'Ask a Question')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('questions.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-semibold">Ask a Question</h4>
            <p class="text-muted small mb-0">Share a question with your team</p>
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
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-question-circle text-primary me-2"></i>
                        Question Details
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('questions.store') }}" method="POST">
                        @csrf

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
                                value="{{ old('title') }}"
                                placeholder="What is your question? Be specific and clear…"
                                maxlength="255"
                                autofocus
                            >
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Minimum 10 characters. A clear title gets better answers.</div>
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
                                placeholder="Describe your question in detail. Include any relevant context, what you have already tried, or any error messages…"
                            >{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Minimum 20 characters. The more detail you provide, the better the answers.</div>
                            @enderror
                        </div>

                        {{-- Tips --}}
                        <div class="alert alert-info border-0 mb-4" style="background:rgba(59,130,246,.08);">
                            <div class="fw-semibold mb-2">
                                <i class="bi bi-lightbulb text-info me-1"></i> Tips for a great question
                            </div>
                            <ul class="mb-0 small text-muted">
                                <li>Summarise the problem in the title</li>
                                <li>Provide enough context in the body</li>
                                <li>Mention what you have already tried</li>
                                <li>Keep it focused — one question per post</li>
                            </ul>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('questions.index') }}" class="btn btn-outline-secondary px-4">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-send me-1"></i> Post Question
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
