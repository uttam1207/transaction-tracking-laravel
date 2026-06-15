@extends('layouts.app')
@section('title', Str::limit($question->title, 60))

@push('styles')
<style>
/* ── Layout ── */
.qa-show-wrap { max-width: 860px; margin: 0 auto; }

/* ── Question block ── */
.qa-question-card {
    background: var(--bs-body-bg);
    border: 1.5px solid var(--bs-border-color, #e2e8f0);
    border-radius: 16px;
    padding: 26px 28px;
    margin-bottom: 24px;
}
.qa-q-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--bs-body-color);
    line-height: 1.3;
    margin-bottom: 14px;
}
.qa-q-body {
    font-size: .9rem;
    line-height: 1.75;
    color: var(--bs-body-color);
    white-space: pre-wrap;
    word-break: break-word;
    margin-bottom: 18px;
}
.qa-q-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-top: 14px;
    border-top: 1px solid var(--bs-border-color, #e2e8f0);
    flex-wrap: wrap;
}
.qa-avatar-lg {
    width: 34px; height: 34px;
    border-radius: 50%; object-fit: cover;
}
.qa-author-name { font-size: .82rem; font-weight: 600; color: var(--bs-body-color); }
.qa-author-meta { font-size: .72rem; color: var(--bs-secondary-color); }
.qa-actions { margin-left: auto; display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
.qa-btn-sm {
    font-size: .75rem; font-weight: 600;
    border-radius: 7px; padding: 4px 11px;
    border: 1.5px solid transparent; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
    transition: all .15s; white-space: nowrap;
}
.qa-btn-edit   { border-color: #e2e8f0; color: var(--bs-secondary-color); background: transparent; }
.qa-btn-edit:hover { border-color: #059669; color: #059669; background: rgba(5,150,105,.06); }
.qa-btn-delete { border-color: #fee2e2; color: #dc2626; background: transparent; }
.qa-btn-delete:hover { background: #fef2f2; }
.qa-btn-accept { border-color: #bbf7d0; color: #15803d; background: rgba(21,128,61,.06); }
.qa-btn-accept:hover { background: #dcfce7; }

/* Status badge */
.qa-status { font-size: .7rem; font-weight: 700; border-radius: 20px; padding: 3px 10px; letter-spacing: .3px; text-transform: uppercase; }
.status-open     { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.status-resolved { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
.status-closed   { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
[data-bs-theme="dark"] .status-open     { background: rgba(21,128,61,.18); border-color: rgba(21,128,61,.3); }
[data-bs-theme="dark"] .status-resolved { background: rgba(29,78,216,.18); border-color: rgba(29,78,216,.3); }
[data-bs-theme="dark"] .status-closed   { background: rgba(100,116,139,.18); border-color: rgba(100,116,139,.3); }

/* ── Edit form (inline, hidden by default) ── */
.qa-edit-form {
    display: none;
    margin-top: 16px;
    background: var(--bs-tertiary-bg, #f8fafc);
    border: 1.5px solid var(--bs-border-color);
    border-radius: 12px;
    padding: 18px 20px;
}
.qa-edit-form.show { display: block; }
.qa-edit-form textarea {
    width: 100%; min-height: 120px;
    background: var(--bs-body-bg); color: var(--bs-body-color);
    border: 1.5px solid var(--bs-border-color); border-radius: 9px;
    padding: 10px 14px; font-size: .875rem; resize: vertical;
}
.qa-edit-form textarea:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); outline: none; }
.qa-edit-form input[type="text"] {
    width: 100%;
    background: var(--bs-body-bg); color: var(--bs-body-color);
    border: 1.5px solid var(--bs-border-color); border-radius: 9px;
    padding: 8px 14px; font-size: .875rem; margin-bottom: 10px;
}
.qa-edit-form input[type="text"]:focus { border-color: #059669; outline: none; box-shadow: 0 0 0 3px rgba(5,150,105,.1); }
.qa-edit-form select {
    border: 1.5px solid var(--bs-border-color); border-radius: 9px;
    padding: 7px 12px; font-size: .8rem; background: var(--bs-body-bg); color: var(--bs-body-color);
}
.qa-edit-label { font-size: .76rem; font-weight: 600; color: var(--bs-secondary-color); margin-bottom: 5px; display: block; text-transform: uppercase; letter-spacing: .2px; }

/* ── Answers section ── */
.qa-answers-header {
    font-size: .88rem; font-weight: 700;
    color: var(--bs-secondary-color);
    text-transform: uppercase; letter-spacing: .4px;
    margin-bottom: 12px;
    display: flex; align-items: center; gap: 8px;
}
.qa-answer-card {
    background: var(--bs-body-bg);
    border: 1.5px solid var(--bs-border-color, #e2e8f0);
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 12px;
    position: relative;
    transition: border-color .18s;
}
.qa-answer-card.accepted {
    border-color: #059669;
    background: color-mix(in srgb, #dcfce7 8%, var(--bs-body-bg));
}
[data-bs-theme="dark"] .qa-answer-card.accepted {
    background: rgba(5,150,105,.07);
}
.qa-accepted-badge {
    position: absolute; top: 12px; right: 14px;
    font-size: .67rem; font-weight: 700; color: #15803d;
    background: #dcfce7; border: 1px solid #bbf7d0;
    border-radius: 20px; padding: 2px 9px;
    display: flex; align-items: center; gap: 4px;
    text-transform: uppercase; letter-spacing: .3px;
}
[data-bs-theme="dark"] .qa-accepted-badge { background: rgba(21,128,61,.2); border-color: rgba(21,128,61,.3); color: #4ade80; }

.qa-answer-body {
    font-size: .88rem; line-height: 1.72;
    color: var(--bs-body-color);
    white-space: pre-wrap; word-break: break-word;
    margin-bottom: 14px;
    padding-right: 80px;
}
.qa-answer-meta {
    display: flex; align-items: center; gap: 10px;
    border-top: 1px solid var(--bs-border-color, #e2e8f0);
    padding-top: 12px; flex-wrap: wrap;
}

/* ── Post answer form ── */
.qa-post-answer {
    background: var(--bs-body-bg);
    border: 1.5px solid var(--bs-border-color, #e2e8f0);
    border-radius: 16px;
    padding: 22px 24px;
    margin-top: 24px;
}
.qa-post-answer h5 { font-size: .95rem; font-weight: 700; margin-bottom: 12px; color: var(--bs-body-color); }
.qa-post-answer textarea {
    width: 100%; min-height: 110px;
    background: var(--bs-tertiary-bg, #f8fafc); color: var(--bs-body-color);
    border: 1.5px solid var(--bs-border-color); border-radius: 10px;
    padding: 12px 14px; font-size: .875rem; resize: vertical; margin-bottom: 12px;
}
.qa-post-answer textarea:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); outline: none; }

/* Breadcrumb */
.qa-breadcrumb { font-size: .8rem; color: var(--bs-secondary-color); margin-bottom: 16px; }
.qa-breadcrumb a { color: #059669; text-decoration: none; }
.qa-breadcrumb a:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
<div class="qa-show-wrap">

    {{-- Breadcrumb --}}
    <div class="qa-breadcrumb">
        <a href="{{ route('questions.index') }}"><i class="bi bi-chat-square-text me-1"></i>Q&A</a>
        <span class="mx-2">›</span>
        <span>{{ Str::limit($question->title, 60) }}</span>
    </div>

    {{-- Flash message --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-3" style="border-radius:10px; font-size:.85rem;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ══ QUESTION ══ --}}
    <div class="qa-question-card">
        <h1 class="qa-q-title">{{ $question->title }}</h1>
        <div class="qa-q-body">{{ $question->body }}</div>

        {{-- Question meta --}}
        <div class="qa-q-meta">
            <img src="{{ $question->user->avatar_url }}" class="qa-avatar-lg" alt="">
            <div>
                <div class="qa-author-name">{{ $question->user->name }}</div>
                <div class="qa-author-meta">
                    {{ ucwords(str_replace('_', ' ', $question->user->role)) }}
                    · {{ $question->created_at->format('d M Y, g:i a') }}
                    · <i class="bi bi-eye"></i> {{ $question->views }} views
                </div>
            </div>

            <span class="qa-status status-{{ $question->status }}">{{ ucfirst($question->status) }}</span>

            @if($question->is_pinned)
            <span style="font-size:.72rem; color:#d97706; font-weight:600;"><i class="bi bi-pin-angle-fill"></i> Pinned</span>
            @endif

            {{-- Edit / Delete (owner or super_admin) --}}
            <div class="qa-actions">
                @if($question->canEdit(auth()->user()))
                <button class="qa-btn-sm qa-btn-edit" onclick="toggleEditQuestion()">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                @endif
                @if($question->canDelete(auth()->user()))
                <form method="POST" action="{{ route('questions.destroy', $question) }}"
                      onsubmit="return confirm('Delete this question and all its answers?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="qa-btn-sm qa-btn-delete">
                        <i class="bi bi-trash3"></i> Delete
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- ── Inline edit form for question ── --}}
        @if($question->canEdit(auth()->user()))
        <div class="qa-edit-form" id="editQuestionForm">
            <form method="POST" action="{{ route('questions.update', $question) }}">
                @csrf @method('PUT')
                <label class="qa-edit-label">Title</label>
                <input type="text" name="title" value="{{ old('title', $question->title) }}" required>
                <label class="qa-edit-label">Body</label>
                <textarea name="body" required style="min-height:140px;">{{ old('body', $question->body) }}</textarea>
                <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                    <label class="qa-edit-label mb-0">Status</label>
                    <select name="status">
                        <option value="open"     {{ $question->status==='open'     ? 'selected' : '' }}>Open</option>
                        <option value="resolved" {{ $question->status==='resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed"   {{ $question->status==='closed'   ? 'selected' : '' }}>Closed</option>
                    </select>
                    @if(auth()->user()->isSuperAdmin())
                    <label class="ms-3 d-flex align-items-center gap-2 qa-edit-label mb-0" style="cursor:pointer;">
                        <input type="checkbox" name="is_pinned" value="1" {{ $question->is_pinned ? 'checked' : '' }}>
                        Pin question
                    </label>
                    @endif
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" onclick="toggleEditQuestion()" class="qa-btn-sm qa-btn-edit">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm fw-600" style="border-radius:7px; font-size:.78rem; font-weight:600; padding:4px 14px;">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>

    {{-- ══ ANSWERS ══ --}}
    <div class="qa-answers-header">
        <i class="bi bi-chat-left-text-fill" style="color:#059669;"></i>
        {{ $question->answers->count() }} {{ Str::plural('Answer', $question->answers->count()) }}
    </div>

    @forelse($question->answers as $answer)
    <div class="qa-answer-card {{ $answer->is_accepted ? 'accepted' : '' }}" id="answer-{{ $answer->id }}">

        @if($answer->is_accepted)
        <div class="qa-accepted-badge"><i class="bi bi-check-circle-fill"></i> Accepted Answer</div>
        @endif

        {{-- Body --}}
        <div class="qa-answer-body" id="answer-body-{{ $answer->id }}">{{ $answer->body }}</div>

        {{-- Meta --}}
        <div class="qa-answer-meta">
            <img src="{{ $answer->user->avatar_url }}" class="qa-avatar-lg" alt="">
            <div>
                <div class="qa-author-name">{{ $answer->user->name }}</div>
                <div class="qa-author-meta">
                    {{ ucwords(str_replace('_', ' ', $answer->user->role)) }}
                    · {{ $answer->created_at->format('d M Y, g:i a') }}
                    @if($answer->updated_at->gt($answer->created_at))
                    · <span style="color:#f59e0b; font-size:.71rem;"><i class="bi bi-pencil"></i> edited {{ $answer->updated_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>

            <div class="qa-actions">
                {{-- Accept answer (question owner or super_admin, when not already accepted) --}}
                @if(!$answer->is_accepted && (auth()->user()->isSuperAdmin() || $question->user_id === auth()->id()))
                <form method="POST" action="{{ route('questions.answers.accept', [$question, $answer]) }}">
                    @csrf
                    <button type="submit" class="qa-btn-sm qa-btn-accept">
                        <i class="bi bi-check-circle"></i> Accept
                    </button>
                </form>
                @endif

                {{-- Edit (own answer or super_admin) --}}
                @if($answer->canEdit(auth()->user()))
                <button class="qa-btn-sm qa-btn-edit" onclick="toggleEditAnswer({{ $answer->id }})">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                @endif

                {{-- Delete (own answer or super_admin) --}}
                @if($answer->canDelete(auth()->user()))
                <form method="POST" action="{{ route('questions.answers.destroy', [$question, $answer]) }}"
                      onsubmit="return confirm('Delete this answer?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="qa-btn-sm qa-btn-delete">
                        <i class="bi bi-trash3"></i> Delete
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- ── Inline edit form for answer ── --}}
        @if($answer->canEdit(auth()->user()))
        <div class="qa-edit-form" id="editAnswerForm{{ $answer->id }}">
            <form method="POST" action="{{ route('questions.answers.update', [$question, $answer]) }}">
                @csrf @method('PUT')
                <label class="qa-edit-label">Edit Answer</label>
                <textarea name="body" required>{{ old('body', $answer->body) }}</textarea>
                <div class="d-flex gap-2 mt-2 justify-content-end">
                    <button type="button" onclick="toggleEditAnswer({{ $answer->id }})" class="qa-btn-sm qa-btn-edit">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm fw-600" style="border-radius:7px; font-size:.78rem; font-weight:600; padding:4px 14px;">Save Answer</button>
                </div>
            </form>
        </div>
        @endif
    </div>
    @empty
    <div class="text-center py-5" style="color:var(--bs-secondary-color);">
        <i class="bi bi-chat-right-dots" style="font-size:2rem; color:#cbd5e1;"></i>
        <p class="mt-2 mb-0" style="font-size:.85rem;">No answers yet. Be the first to answer!</p>
    </div>
    @endforelse

    {{-- ══ POST ANSWER FORM ══ --}}
    @if($question->status !== 'closed')
    <div class="qa-post-answer">
        <h5><i class="bi bi-reply-fill me-2" style="color:#059669;"></i>Your Answer</h5>

        @error('body')
        <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius:9px; font-size:.82rem; border:none; background:#fef2f2; color:#b91c1c;">
            <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}
        </div>
        @enderror

        <form method="POST" action="{{ route('questions.answers.store', $question) }}">
            @csrf
            <textarea name="body" placeholder="Write a clear, helpful answer… (min. 10 characters)" required>{{ old('body') }}</textarea>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-success px-4 fw-600" style="border-radius:9px; font-weight:600;">
                    <i class="bi bi-send me-2"></i>Post Answer
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="alert d-flex align-items-center gap-2 py-2 px-3 mt-4" style="background:var(--bs-tertiary-bg); border:1.5px solid var(--bs-border-color); border-radius:10px; font-size:.85rem; color:var(--bs-secondary-color);">
        <i class="bi bi-lock-fill"></i> This question is closed. No new answers can be posted.
    </div>
    @endif

</div>
</div>
@endsection

@push('scripts')
<script>
function toggleEditQuestion() {
    const form = document.getElementById('editQuestionForm');
    form.classList.toggle('show');
    window.scrollTo({ top: form.offsetTop - 80, behavior: 'smooth' });
}

function toggleEditAnswer(id) {
    const form = document.getElementById('editAnswerForm' + id);
    form.classList.toggle('show');
    if (form.classList.contains('show')) {
        window.scrollTo({ top: form.offsetTop - 80, behavior: 'smooth' });
    }
}

// Scroll to hash anchor (e.g. #answer-5)
if (window.location.hash) {
    const el = document.querySelector(window.location.hash);
    if (el) setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
}
</script>
@endpush
