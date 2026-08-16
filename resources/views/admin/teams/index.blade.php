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
            <p>Organise employees into cross-functional teams</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat"><div class="v">{{ $teams->total() }}</div><div class="l">Teams</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#fde047;">{{ $unassigned }}</div><div class="l">Unassigned</div></div>
            <div class="hero-vr"></div>
            <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#teamModal" onclick="openCreate()">
                <i class="bi bi-plus-lg me-1"></i>New Team
            </button>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="flabel">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Team name or code…"
                value="{{ request('search') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:220px;">
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
        <div>
            <label class="flabel">Status</label>
            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:130px;">
                <option value="">All</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        @if(request()->hasAny(['search','department_id','status']))
            <a href="{{ route('admin.teams.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
        @endif
    </form>
</div>

<div class="table-card">
    <div class="card-header">
        <span class="card-title">All Teams</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Team</th>
                    <th>Code</th>
                    <th>Department</th>
                    <th>Team Lead</th>
                    <th>Members</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teams as $team)
                <tr>
                    <td style="font-weight:700;font-size:.88rem;color:#111827;">{{ $team->name }}</td>
                    <td>
                        @if($team->code)
                        <span style="background:#f0f4ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:monospace;">{{ $team->code }}</span>
                        @else<span style="color:#9ca3af;">—</span>@endif
                    </td>
                    <td style="font-size:.84rem;color:#374151;">{{ $team->department->name ?? '—' }}</td>
                    <td>
                        @if($team->teamLead)
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $team->teamLead->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($team->teamLead->name).'&size=24&background=6366f1&color=fff' }}"
                                class="rounded-circle" style="width:24px;height:24px;object-fit:cover;" alt="">
                            <span style="font-size:.83rem;">{{ $team->teamLead->name }}</span>
                        </div>
                        @else<span style="color:#9ca3af;font-size:.83rem;">—</span>@endif
                    </td>
                    <td>
                        <span style="background:#ede9fe;color:#7c3aed;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">
                            {{ $team->members_count }}
                        </span>
                    </td>
                    <td><span class="spill {{ $team->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $team->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn" title="Manage Members" style="background:#f0f9ff;color:#0284c7;border:1px solid #e0f2fe;"
                                onclick="openMembers({{ $team->id }}, '{{ addslashes($team->name) }}')">
                                <i class="bi bi-people-fill"></i>
                            </button>
                            <button class="act-btn act-edit" title="Edit"
                                onclick="openEdit({{ $team->id }}, '{{ addslashes($team->name) }}', '{{ $team->code }}', '{{ addslashes($team->description) }}', {{ $team->department_id ?? 'null' }}, {{ $team->team_lead_id ?? 'null' }}, {{ $team->is_active ? 1 : 0 }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" title="Delete" onclick="deleteTeam({{ $team->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-diagram-3"></i><p>No teams found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($teams->hasPages())
    <div class="pagination-wrap">{{ $teams->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create / Edit Team Modal --}}
<div class="modal fade" id="teamModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="teamModalTitle">New Team</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="teamForm" method="POST">
                @csrf
                <span id="teamMethod"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="flabel">Team Name <span class="req">*</span></label>
                            <input type="text" name="name" id="tName" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Code</label>
                            <input type="text" name="code" id="tCode" class="form-control" placeholder="TEAM01" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" id="tDesc" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Department</label>
                            <select name="department_id" id="tDept" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">No Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Team Lead</label>
                            <select name="team_lead_id" id="tLead" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">No Lead</option>
                                @foreach($leads as $lead)
                                    <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status</label>
                            <select name="is_active" id="tStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="teamSubmitBtn">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Members Modal --}}
<div class="modal fade" id="membersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="membersModalTitle"><i class="bi bi-people-fill me-2"></i>Manage Members</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMembersForm" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Add Employees</label>
                            <select name="employee_ids[]" class="form-select" multiple size="7"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} — {{ $emp->display_designation }}</option>
                                @endforeach
                            </select>
                            <div style="font-size:.74rem;color:#9ca3af;margin-top:4px;">Hold Ctrl/Cmd to select multiple</div>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Role in Team</label>
                            <input type="text" name="role_in_team" class="form-control" placeholder="e.g. Developer, QA…"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Add Members</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

function openCreate() {
    document.getElementById('teamModalTitle').textContent = 'New Team';
    document.getElementById('teamForm').action = '{{ route("admin.teams.store") }}';
    document.getElementById('teamMethod').innerHTML = '';
    document.getElementById('teamSubmitBtn').textContent = 'Create';
    ['tName','tCode','tDesc'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('tDept').value = '';
    document.getElementById('tLead').value = '';
    document.getElementById('tStatus').value = '1';
}

function openEdit(id, name, code, desc, deptId, leadId, isActive) {
    document.getElementById('teamModalTitle').textContent = 'Edit Team';
    document.getElementById('teamForm').action = `/admin/teams/${id}`;
    document.getElementById('teamMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('teamSubmitBtn').textContent = 'Save Changes';
    document.getElementById('tName').value   = name;
    document.getElementById('tCode').value   = code || '';
    document.getElementById('tDesc').value   = desc || '';
    document.getElementById('tDept').value   = deptId || '';
    document.getElementById('tLead').value   = leadId || '';
    document.getElementById('tStatus').value = isActive;
    new bootstrap.Modal(document.getElementById('teamModal')).show();
}

function openMembers(teamId, teamName) {
    document.getElementById('membersModalTitle').innerHTML = '<i class="bi bi-people-fill me-2"></i>Members — ' + teamName;
    document.getElementById('addMembersForm').action = `/admin/teams/${teamId}/members`;
    new bootstrap.Modal(document.getElementById('membersModal')).show();
}

function deleteTeam(id) {
    APP.confirm('Delete team?', 'All member assignments will be removed.', function() {
        fetch(`/admin/teams/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Team deleted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Cannot delete team.', 'error');
        });
    });
}
</script>
@endpush
