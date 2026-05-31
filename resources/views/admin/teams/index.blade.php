@extends('layouts.app')
@section('title', 'Team Management')
@section('breadcrumb')
    <li class="breadcrumb-item active">Teams</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Team Management</h4>
            <p>Organise employees into teams</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat"><div class="v">{{ $teams->count() }}</div><div class="l">Teams</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#86efac;">{{ $teams->sum('count') }}</div><div class="l">Assigned</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#fde047;">{{ $unassigned }}</div><div class="l">Unassigned</div></div>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="d-flex align-items-end justify-content-between gap-3 flex-wrap">
        <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
            <div>
                <label class="flabel">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search team name…"
                    value="{{ request('search') }}"
                    style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:220px;">
            </div>
            <div>
                <label class="flabel">Department</label>
                <select name="department_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:180px;">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        </form>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#assignModal">
            <i class="bi bi-people-fill me-1"></i>Assign Team
        </button>
    </div>
</div>

@if($teams->isEmpty())
<div class="table-card">
    <div class="empty-state" style="padding:48px 0;"><i class="bi bi-diagram-3"></i><p>No teams found. Assign employees to teams to get started.</p></div>
</div>
@else
<div class="row g-3">
    @foreach($teams as $teamName => $team)
    <div class="col-md-6 col-xl-4">
        <div style="background:#fff;border-radius:14px;border:1.5px solid #f0f0f5;height:100%;overflow:hidden;">
            <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:16px 18px;color:#fff;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-weight:800;font-size:.95rem;">{{ $teamName }}</div>
                    <div style="font-size:.76rem;opacity:.75;margin-top:2px;">{{ $team['department'] ?? '—' }}</div>
                </div>
                <span style="background:rgba(255,255,255,.2);color:#fff;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">
                    {{ $team['count'] }} members
                </span>
            </div>
            <ul style="margin:0;padding:0;list-style:none;">
                @foreach($team['members']->take(5) as $member)
                <li style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid #f3f4f6;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img src="{{ $member->user?->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($member->full_name).'&size=28&background=6366f1&color=fff' }}"
                            class="rounded-circle" style="width:28px;height:28px;object-fit:cover;flex-shrink:0;" alt="">
                        <div>
                            <div style="font-weight:600;font-size:.84rem;color:#111827;">{{ $member->full_name }}</div>
                            <div style="font-size:.72rem;color:#9ca3af;">{{ $member->designation }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.teams.remove', $member) }}">
                        @csrf
                        <button type="submit" class="act-btn act-delete" title="Remove from team" style="width:26px;height:26px;font-size:.7rem;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </form>
                </li>
                @endforeach
                @if($team['count'] > 5)
                <li style="padding:8px 16px;text-align:center;font-size:.78rem;color:#9ca3af;">
                    +{{ $team['count'] - 5 }} more members
                </li>
                @endif
            </ul>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Assign Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.teams.assign') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-people-fill me-2"></i>Assign to Team</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="flabel">Team Name <span class="req">*</span></label>
                        <input type="text" name="team" class="form-control" placeholder="e.g. Backend Team" required
                            style="border-radius:9px;border:1.5px solid #e5e7eb;">
                    </div>
                    <div class="col-12">
                        <label class="flabel">Select Employees <span class="req">*</span></label>
                        <select name="employee_ids[]" class="form-select" multiple required size="8"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                            @foreach(\App\Models\Employee::with('user')->where('status','active')->get() as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->designation }})</option>
                            @endforeach
                        </select>
                        <div style="font-size:.74rem;color:#9ca3af;margin-top:4px;">Hold Ctrl/Cmd to select multiple</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary-grad px-4">Assign</button>
            </div>
        </form>
    </div>
</div>
@endsection
