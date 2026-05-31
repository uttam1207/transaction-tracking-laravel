@extends('layouts.app')
@section('title', 'User — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')

<a href="{{ route('admin.users.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Users</a>

@php
    $statusColor = match($user->status) { 'active'=>'active', 'inactive'=>'inactive', 'suspended'=>'danger', default=>'warning' };
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $user->avatar_url }}" class="rounded-circle"
                style="width:56px;height:56px;border:2.5px solid rgba(255,255,255,.4);object-fit:cover;" alt="">
            <div>
                <h4 style="margin:0;font-weight:800;">{{ $user->name }}</h4>
                <p style="opacity:.8;margin:2px 0 0;font-size:.85rem;">{{ $user->email }}</p>
            </div>
        </div>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
            <i class="bi bi-pencil me-1"></i>Edit User
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Profile --}}
    <div class="col-lg-4">
        <div class="info-card text-center" style="padding:24px 20px;">
            <img src="{{ $user->avatar_url }}" class="rounded-circle mx-auto mb-3"
                style="width:88px;height:88px;border:3px solid #e0e7ff;object-fit:cover;display:block;" alt="">
            <div style="font-size:1.05rem;font-weight:800;color:#111827;">{{ $user->name }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">{{ $user->email }}</div>
            <div class="d-flex align-items-center justify-content-center gap-2 mt-3">
                <span style="background:#ede9fe;color:#7c3aed;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:700;">
                    {{ ucwords(str_replace('_', ' ', $user->role)) }}
                </span>
                <span class="spill spill-{{ $statusColor }}" style="font-size:.75rem;">{{ ucfirst($user->status) }}</span>
            </div>

            <div style="border-top:1px solid #f3f4f6;margin-top:20px;padding-top:16px;text-align:left;">
                <dl class="dl">
                    @if($user->phone)
                    <dt><i class="bi bi-telephone me-1 text-muted"></i>Phone</dt>
                    <dd>{{ $user->phone }}</dd>
                    @endif
                    <dt><i class="bi bi-at me-1 text-muted"></i>Username</dt>
                    <dd style="font-family:monospace;color:#4f46e5;font-weight:700;">{{ $user->username }}</dd>
                    <dt><i class="bi bi-building me-1 text-muted"></i>Department</dt>
                    <dd>{{ $user->department?->name ?? 'N/A' }}</dd>
                    <dt><i class="bi bi-calendar me-1 text-muted"></i>Joined</dt>
                    <dd>{{ $user->created_at->format('d M Y') }}</dd>
                    <dt><i class="bi bi-clock me-1 text-muted"></i>Last Login</dt>
                    <dd>{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Right: Activity --}}
    <div class="col-lg-8">
        <div class="table-card mb-3">
            <div class="card-header"><span class="card-title">Recent Login History</span></div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Browser / Device</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->loginHistories->take(10) as $login)
                        <tr>
                            <td style="font-family:monospace;font-size:.82rem;color:#4f46e5;">{{ $login->ip_address ?? 'N/A' }}</td>
                            <td style="font-size:.8rem;color:#6b7280;max-width:180px;">{{ $login->user_agent ? Str::limit($login->user_agent, 40) : 'N/A' }}</td>
                            <td>
                                <span class="spill spill-{{ $login->status === 'success' ? 'success' : 'danger' }}" style="font-size:.7rem;">
                                    {{ ucfirst($login->status ?? 'unknown') }}
                                </span>
                            </td>
                            <td style="font-size:.78rem;color:#9ca3af;">{{ $login->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4">
                            <div class="empty-state"><i class="bi bi-clock-history"></i><p>No login history</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card">
            <div class="card-header"><span class="card-title">Recent Activity</span></div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->auditLogs->take(10) as $log)
                        <tr>
                            <td>
                                <span style="background:#eff6ff;color:#2563eb;padding:3px 9px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:monospace;">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td style="font-size:.82rem;color:#6b7280;">{{ Str::limit($log->description ?? $log->event ?? '', 60) }}</td>
                            <td style="font-size:.78rem;color:#9ca3af;">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3">
                            <div class="empty-state"><i class="bi bi-activity"></i><p>No activity found</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
