@extends('layouts.app')
@section('title', 'User — ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0 fw-bold">User Profile</h5>
        <div class="text-muted small">Detailed view for {{ $user->name }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit User
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <img src="{{ $user->avatar_url }}" class="rounded-circle border mb-3" width="90" height="90" alt="">
                <h6 class="fw-bold mb-1">{{ $user->name }}</h6>
                <div class="text-muted small mb-2">{{ $user->email }}</div>
                <span class="badge bg-primary-subtle text-primary">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span>
                <div class="mt-2">
                    @if($user->status === 'active')
                        <span class="badge bg-success-subtle text-success">Active</span>
                    @elseif($user->status === 'inactive')
                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                    @elseif($user->status === 'suspended')
                        <span class="badge bg-danger-subtle text-danger">Suspended</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning">Pending</span>
                    @endif
                </div>
            </div>
            <div class="card-footer bg-transparent text-start">
                <div class="d-flex flex-column gap-2 small">
                    @if($user->phone)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-telephone me-1"></i>Phone</span>
                        <span>{{ $user->phone }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-at me-1"></i>Username</span>
                        <span>{{ $user->username }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-building me-1"></i>Department</span>
                        <span>{{ $user->department?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-calendar me-1"></i>Joined</span>
                        <span>{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="bi bi-clock me-1"></i>Last Login</span>
                        <span>{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-8">
        <!-- Login History -->
        <div class="card mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-semibold">Recent Login History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">IP Address</th>
                                <th>Browser / Device</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->loginHistories->take(10) as $login)
                            <tr>
                                <td class="ps-3 small">{{ $login->ip_address ?? 'N/A' }}</td>
                                <td class="small">{{ $login->user_agent ? Str::limit($login->user_agent, 40) : 'N/A' }}</td>
                                <td>
                                    @if($login->status === 'success')
                                        <span class="badge bg-success-subtle text-success">Success</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">Failed</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $login->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">No login history</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Audit Logs -->
        <div class="card">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-semibold">Recent Activity</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Action</th>
                                <th>Description</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->auditLogs->take(10) as $log)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-primary-subtle text-primary small">{{ $log->action }}</span>
                                </td>
                                <td class="small">{{ Str::limit($log->description ?? $log->event ?? '', 60) }}</td>
                                <td class="small text-muted">{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No activity found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
