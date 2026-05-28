@extends('layouts.app')
@section('title', 'Team Management')
@section('breadcrumb')
    <li class="breadcrumb-item active">Teams</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Team Management</h4>
        <p class="text-muted small mb-0">Organise employees into teams</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal">
        <i class="bi bi-people-fill me-1"></i>Assign Team
    </button>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-15 text-primary"><i class="bi bi-diagram-3"></i></div>
                <div>
                    <div class="h4 mb-0 fw-bold">{{ $teams->count() }}</div>
                    <div class="text-muted small">Total Teams</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-15 text-success"><i class="bi bi-people"></i></div>
                <div>
                    <div class="h4 mb-0 fw-bold">{{ $teams->sum('count') }}</div>
                    <div class="text-muted small">Members Assigned</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-15 text-warning"><i class="bi bi-person-x"></i></div>
                <div>
                    <div class="h4 mb-0 fw-bold">{{ $unassigned }}</div>
                    <div class="text-muted small">Unassigned</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search team name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Teams --}}
@if($teams->isEmpty())
<div class="card shadow-sm"><div class="card-body text-center py-5 text-muted">No teams found. Assign employees to teams to get started.</div></div>
@else
<div class="row g-3">
    @foreach($teams as $teamName => $team)
    <div class="col-md-6 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">{{ $teamName }}</div>
                    <div class="text-muted small">{{ $team['department'] ?? '—' }}</div>
                </div>
                <span class="badge bg-primary">{{ $team['count'] }} members</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($team['members']->take(5) as $member)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $member->user?->avatar_url }}" class="rounded-circle" width="28" height="28" alt="">
                            <div>
                                <div class="small fw-semibold">{{ $member->full_name }}</div>
                                <div class="text-muted" style="font-size:.7rem">{{ $member->designation }}</div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.teams.remove', $member) }}">
                            @csrf
                            <button class="btn btn-xs btn-outline-danger py-0 px-1" title="Remove from team">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                    </li>
                    @endforeach
                    @if($team['count'] > 5)
                    <li class="list-group-item text-center text-muted small py-2">
                        +{{ $team['count'] - 5 }} more members
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Assign Team Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.teams.assign') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Assign to Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Team Name</label>
                    <input type="text" name="team" class="form-control" placeholder="e.g. Backend Team" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Employees</label>
                    <select name="employee_ids[]" class="form-select" multiple required size="8">
                        @foreach(\App\Models\Employee::with('user')->where('status','active')->get() as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->designation }})</option>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>
@endsection
