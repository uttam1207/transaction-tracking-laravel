@extends('layouts.app')
@section('title', 'Q&A — Questions & Answers')

@push('styles')
<style>
.qa-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: .75rem;
}
.qa-title { font-size: 1.35rem; font-weight: 800; color: var(--bs-heading-color, #0f172a); margin: 0; }

/* Filter tabs */
.qa-tabs {
    display: flex;
    gap: 4px;
    background: var(--bs-tertiary-bg, #f1f5f9);
    border-radius: 10px;
    padding: 4px;
    flex-wrap: wrap;
}
.qa-tab {
    padding: 6px 14px;
    border-radius: 7px;
    font-size: .8rem;
    font-weight: 600;
    color: var(--bs-secondary-color, #64748b);
    text-decoration: none;
    transition: all .18s;
    white-space: nowrap;
}
.qa-tab:hover { background: var(--bs-body-bg, #fff); color: var(--bs-body-color, #0f172a); }
.qa-tab.active { background: #fff; color: #059669; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
[data-bs-theme="dark"] .qa-tab.active { background: #1e293b; }

/* Search bar */
.qa-search-wrap {
    position: relative;
    flex: 1;
    min-width: 200px;
    max-width: 340px;
}
.qa-search-wrap .bi { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .9rem; }
.qa-search-wrap input {
    padding-left: 36px;
    border-radius: 9px;
    border: 1.5px solid var(--bs-border-color, #e2e8f0);
    height: 38px;
    font-size: .85rem;
    width: 100%;
    background: var(--bs-body-bg, #fff);
    color: var(--bs-body-color);
}
.qa-search-wrap input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,.1); outline: none; }

/* Question card */
.qa-card {
    background: var(--bs-body-bg, #fff);
    border: 1.5px solid var(--bs-border-color, #e2e8f0);
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 12px;
    transition: border-color .18s, box-shadow .18s;
    position: relative;
}
.qa-card:hover { border-color: #059669; box-shadow: 0 4px 18px rgba(5,150,105,.1); }
.qa-card.pinned { border-color: #f59e0b; background: color-mix(in srgb, #fef3c7 6%, var(--bs-body-bg)); }

.qa-pin-badge {
    position: absolute;
    top: 14px; right: 16px;
    font-size: .68rem; font-weight: 700;
    color: #d97706;
    display: flex; align-items: center; gap: 4px;
}

.qa-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--bs-body-color, #0f172a);
    text-decoration: none;
    margin-bottom: 6px;
    display: block;
    padding-right: 70px;
    line-height: 1.35;
}
.qa-card-title:hover { color: #059669; }

.qa-card-body {
    font-size: .82rem;
    color: var(--bs-secondary-color, #64748b);
    margin-bottom: 14px;
    line-height: 1.55;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.qa-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.qa-meta-item {
    display: flex; align-items: center; gap: 5px;
    font-size: .75rem;
    color: var(--bs-secondary-color, #64748b);
}
.qa-meta-item i { font-size: .78rem; }
.qa-meta-item.answers { color: #059669; font-weight: 600; }
.qa-meta-item.accepted { color: #059669; }

.qa-avatar {
    width: 22px; height: 22px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

/* Status badge */
.status-open     { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.status-resolved { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
.status-closed   { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
[data-bs-theme="dark"] .status-open     { background: rgba(21,128,61,.18); border-color: rgba(21,128,61,.3); }
[data-bs-theme="dark"] .status-resolved { background: rgba(29,78,216,.18); border-color: rgba(29,78,216,.3); }
[data-bs-theme="dark"] .status-closed   { background: rgba(100,116,139,.18); border-color: rgba(100,116,139,.3); }

.qa-status-badge {
    font-size: .67rem; font-weight: 700;
    border-radius: 20px; padding: 2px 9px;
    letter-spacing: .3px; text-transform: uppercase;
    white-space: nowrap;
}

/* Empty state */
.qa-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--bs-secondary-color, #64748b);
}
.qa-empty i { font-size: 2.5rem; margin-bottom: 12px; color: #cbd5e1; }
.qa-empty h5 { font-size: 1rem; font-weight: 700; margin-bottom: 6px; }
.qa-empty p { font-size: .85rem; }

/* Stat strip */
.qa-stat-strip {
    display: flex;
    gap: 16px;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}
.qa-stat {
    background: var(--bs-body-bg, #fff);
    border: 1.5px solid var(--bs-border-color, #e2e8f0);
    border-radius: 10px;
    padding: 10px 16px;
    display: flex; align-items: center; gap: 10px;
    min-width: 110px;
}
.qa-stat-num { font-size: 1.2rem; font-weight: 800; color: var(--bs-body-color); }
.qa-stat-lbl { font-size: .72rem; color: var(--bs-secondary-color); font-weight: 500; }
.qa-stat i { font-size: 1.1rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="qa-header">
        <div>
            <h1 class="qa-title"><i class="bi bi-chat-square-text me-2" style="color:#059669;"></i>Questions & Answers</h1>
            <p class="text-muted mb-0" style="font-size:.82rem;">Ask questions, share knowledge, and help each other.</p>
        </div>
        <a href="{{ route('questions.create') }}" class="btn btn-success btn-sm px-4 fw-600" style="border-radius:9px; font-weight:600;">
            <i class="bi bi-plus-lg me-1"></i>Ask a Question
        </a>
    </div>

    {{-- Stat strip --}}
    <div class="qa-stat-strip">
        <div class="qa-stat">
            <i class="bi bi-chat-dots text-success"></i>
            <div><div class="qa-stat-num">{{ $counts['all'] }}</div><div class="qa-stat-lbl">Total</div></div>
        </div>
        <div class="qa-stat">
            <i class="bi bi-circle-fill text-success" style="font-size:.7rem;"></i>
            <div><div class="qa-stat-num">{{ $counts['open'] }}</div><div class="qa-stat-lbl">Open</div></div>
        </div>
        <div class="qa-stat">
            <i class="bi bi-check-circle-fill text-primary"></i>
            <div><div class="qa-stat-num">{{ $counts['resolved'] }}</div><div class="qa-stat-lbl">Resolved</div></div>
        </div>
        <div class="qa-stat">
            <i class="bi bi-person-fill" style="color:#059669;"></i>
            <div><div class="qa-stat-num">{{ $counts['mine'] }}</div><div class="qa-stat-lbl">My Questions</div></div>
        </div>
    </div>

    {{-- Filter + Search bar --}}
    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <div class="qa-tabs">
            <a href="{{ route('questions.index') }}"               class="qa-tab {{ !request('filter') ? 'active' : '' }}">All</a>
            <a href="{{ route('questions.index', ['filter'=>'open']) }}"     class="qa-tab {{ request('filter')==='open'     ? 'active' : '' }}">Open</a>
            <a href="{{ route('questions.index', ['filter'=>'resolved']) }}" class="qa-tab {{ request('filter')==='resolved' ? 'active' : '' }}">Resolved</a>
            <a href="{{ route('questions.index', ['filter'=>'mine']) }}"     class="qa-tab {{ request('filter')==='mine'     ? 'active' : '' }}">My Questions</a>
        </div>

        <form method="GET" action="{{ route('questions.index') }}" class="qa-search-wrap">
            @if(request('filter'))<input type="hidden" name="filter" value="{{ request('filter') }}">@endif
            <i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search questions…">
        </form>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 mb-3" style="border-radius:10px; font-size:.85rem;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif

    {{-- Question list --}}
    @forelse($questions as $question)
    <div class="qa-card {{ $question->is_pinned ? 'pinned' : '' }}">

        @if($question->is_pinned)
        <div class="qa-pin-badge"><i class="bi bi-pin-angle-fill"></i> Pinned</div>
        @endif

        {{-- Title --}}
        <a href="{{ route('questions.show', $question) }}" class="qa-card-title">
            {{ $question->title }}
        </a>

        {{-- Excerpt --}}
        <div class="qa-card-body">{{ Str::limit(strip_tags($question->body), 160) }}</div>

        {{-- Meta row --}}
        <div class="qa-meta">
            {{-- Status badge --}}
            <span class="qa-status-badge status-{{ $question->status }}">
                {{ ucfirst($question->status) }}
            </span>

            {{-- Answers count --}}
            <span class="qa-meta-item answers">
                <i class="bi bi-chat-left-text-fill"></i>
                {{ $question->answers_count ?? $question->answers->count() }}
                {{ Str::plural('answer', $question->answers->count()) }}
            </span>

            @if($question->acceptedAnswer)
            <span class="qa-meta-item accepted">
                <i class="bi bi-check-circle-fill"></i> Answered
            </span>
            @endif

            {{-- Views --}}
            <span class="qa-meta-item">
                <i class="bi bi-eye"></i> {{ $question->views }}
            </span>

            {{-- Author --}}
            <span class="qa-meta-item ms-auto">
                <img src="{{ $question->user->avatar_url }}" class="qa-avatar" alt="">
                {{ $question->user->name }}
                <span class="text-muted">· {{ $question->created_at->diffForHumans() }}</span>
            </span>
        </div>
    </div>
    @empty
    <div class="qa-empty">
        <i class="bi bi-chat-square-text d-block"></i>
        <h5>No questions yet</h5>
        <p>Be the first to ask a question.</p>
        <a href="{{ route('questions.create') }}" class="btn btn-success btn-sm mt-2" style="border-radius:9px;">
            <i class="bi bi-plus-lg me-1"></i>Ask a Question
        </a>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if($questions->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $questions->links() }}
    </div>
    @endif

</div>
@endsection
