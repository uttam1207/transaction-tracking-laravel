@extends('layouts.app')

@section('title', 'Ask a Question')

@section('content')
<div class="container-fluid py-4">

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card-glass overflow-hidden">
                <div style="background:linear-gradient(135deg,#2563eb,#4f46e5);padding:22px 28px;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                    <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-question-circle-fill" style="font-size:1.4rem;color:#fff;"></i>
                        </div>
                        <div>
                            <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Community</div>
                            <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">Ask a Question</div>
                            <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">Share a question with your team</div>
                        </div>
                    </div>
                </div>
                <div class="p-4">

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
                            <button type="submit" class="btn btn-primary-grad px-5">
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
