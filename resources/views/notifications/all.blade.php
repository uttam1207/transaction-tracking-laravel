@extends('layouts.app')
@section('title', 'Notification History')

@section('breadcrumb')
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#1e1b4b,#4f46e5);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4 class="mb-1">Notification History</h4>
            <p class="mb-0">All your past notifications in one place</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($unreadCount > 0)
            <span style="background:rgba(255,255,255,.15);border-radius:20px;padding:4px 14px;font-size:.78rem;font-weight:700;color:#fff;">
                {{ $unreadCount }} unread
            </span>
            <form method="POST" action="{{ route('notifications.all.read-all') }}">
                @csrf
                <button type="submit" style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);border-radius:10px;padding:7px 16px;color:#fff;font-size:.82rem;font-weight:600;cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.2)'">
                    <i class="bi bi-check2-all me-1"></i>Mark All Read
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card-glass">
    @if($notifications->isEmpty())
        <div style="padding:60px 20px;text-align:center;">
            <i class="bi bi-bell-slash" style="font-size:3rem;color:#d1d5db;display:block;margin-bottom:16px;"></i>
            <div style="font-size:1rem;font-weight:600;color:#6b7280;margin-bottom:6px;">No notifications yet</div>
            <div style="font-size:.83rem;color:#9ca3af;">You'll see alerts and updates here when they arrive.</div>
        </div>
    @else
        @foreach($notifications as $n)
        @php
            $colorMap = ['info'=>'#0891b2','success'=>'#059669','warning'=>'#d97706','danger'=>'#dc2626','primary'=>'#4f46e5'];
            $bgMap    = ['info'=>'#e0f2fe','success'=>'#d1fae5','warning'=>'#fef3c7','danger'=>'#fee2e2','primary'=>'#ede9fe'];
            $clr = $colorMap[$n->type ?? 'info'] ?? '#4f46e5';
            $bg  = $bgMap[$n->type ?? 'info'] ?? '#ede9fe';
        @endphp
        <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 20px;
                    border-bottom:1px solid #f3f4f6;
                    background:{{ $n->is_read ? '#fff' : '#fafbff' }};
                    transition:background .15s;"
             onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='{{ $n->is_read ? '#fff' : '#fafbff' }}'">

            {{-- Icon --}}
            <div style="width:38px;height:38px;border-radius:10px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;">
                <i class="bi bi-{{ $n->icon ?? 'bell' }}" style="font-size:1rem;color:{{ $clr }};"></i>
            </div>

            {{-- Content --}}
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:.85rem;font-weight:{{ $n->is_read ? '500' : '700' }};color:#111827;">
                        {{ $n->title }}
                    </span>
                    @if(!$n->is_read)
                    <span style="width:7px;height:7px;border-radius:50%;background:#4f46e5;display:inline-block;flex-shrink:0;"></span>
                    @endif
                    <span style="font-size:.7rem;color:#9ca3af;margin-left:auto;">
                        {{ $n->created_at->diffForHumans() }}
                    </span>
                </div>
                <div style="font-size:.8rem;color:#6b7280;margin-top:3px;white-space:pre-line;">{{ $n->message }}</div>
                @if($n->link)
                <a href="{{ $n->link }}" style="font-size:.75rem;color:#4f46e5;font-weight:600;text-decoration:none;margin-top:4px;display:inline-block;">
                    View details <i class="bi bi-arrow-right"></i>
                </a>
                @endif
            </div>

            {{-- Read indicator / timestamp --}}
            <div style="font-size:.7rem;color:#d1d5db;white-space:nowrap;flex-shrink:0;text-align:right;margin-top:4px;">
                {{ $n->created_at->format('d M Y') }}<br>
                {{ $n->created_at->format('H:i') }}
                @if($n->is_read && $n->read_at)
                <br><span style="color:#86efac;font-size:.65rem;"><i class="bi bi-check2-all"></i> Read</span>
                @endif
            </div>
        </div>
        @endforeach

        {{-- Pagination --}}
        @if($notifications->hasPages())
        <div style="padding:16px 20px;">
            {{ $notifications->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
