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

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

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

@forelse($teams as $team)
<div class="table-card mb-3">
    {{-- Team header --}}
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div>
                <span class="card-title me-2">{{ $team->name }}</span>
                @if($team->code)
                <span style="background:#f0f4ff;color:#4f46e5;padding:2px 8px;border-radius:6px;font-size:.71rem;font-weight:700;font-family:monospace;vertical-align:middle;">{{ $team->code }}</span>
                @endif
                <span class="spill {{ $team->is_active ? 'spill-active' : 'spill-inactive' }} ms-2">{{ $team->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="d-flex align-items-center gap-3" style="font-size:.82rem;color:#6b7280;">
                @if($team->department)
                <span><i class="bi bi-building me-1"></i>{{ $team->department->name }}</span>
                @endif
                @if($team->teamLead)
                <span class="d-flex align-items-center gap-1">
                    <img src="{{ 'https://ui-avatars.com/api/?name='.urlencode($team->teamLead->name).'&size=20&background=6366f1&color=fff' }}"
                        class="rounded-circle" style="width:20px;height:20px;" alt="">
                    <span>{{ $team->teamLead->name }}</span>
                    <span style="background:#fef3c7;color:#92400e;font-size:.67rem;font-weight:700;padding:1px 6px;border-radius:20px;">Lead</span>
                </span>
                @endif
                <span style="background:#ede9fe;color:#7c3aed;padding:2px 9px;border-radius:20px;font-size:.74rem;font-weight:700;">
                    {{ $team->members->count() }} member{{ $team->members->count() !== 1 ? 's' : '' }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-1">
            <button class="btn btn-sm px-3" title="Add Members"
                style="background:#f0f9ff;color:#0284c7;border:1px solid #e0f2fe;border-radius:8px;font-size:.78rem;"
                onclick="openMembers({{ $team->id }}, '{{ addslashes($team->name) }}')">
                <i class="bi bi-person-plus me-1"></i>Add Members
            </button>
            <button class="act-btn act-edit" title="Edit"
                onclick="openEdit({{ $team->id }}, '{{ addslashes($team->name) }}', '{{ $team->code }}', '{{ addslashes($team->description) }}', {{ $team->department_id ?? 'null' }}, {{ $team->team_lead_id ?? 'null' }}, {{ $team->is_active ? 1 : 0 }})">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="act-btn act-delete" title="Delete" onclick="deleteTeam({{ $team->id }})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    {{-- Members list --}}
    @if($team->members->count())
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th style="width:40%;">Employee</th>
                    <th>Designation</th>
                    <th>Role in Team</th>
                    <th>Joined</th>
                    <th style="width:60px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($team->members as $member)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ 'https://ui-avatars.com/api/?name='.urlencode($member->full_name).'&size=30&background=6366f1&color=fff' }}"
                                class="rounded-circle" style="width:30px;height:30px;flex-shrink:0;" alt="">
                            <div>
                                <div style="font-weight:600;font-size:.85rem;color:#111827;">{{ $member->full_name }}</div>
                                <div style="font-size:.73rem;color:#9ca3af;">{{ $member->user?->email ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $member->display_designation }}</td>
                    <td>
                        @php
                            $roleColors = [
                                'Team Lead'       => ['bg'=>'#fef3c7','color'=>'#92400e'],
                                'Developer'       => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                                'Designer'        => ['bg'=>'#fce7f3','color'=>'#9d174d'],
                                'QA Engineer'     => ['bg'=>'#d1fae5','color'=>'#065f46'],
                                'DevOps'          => ['bg'=>'#ede9fe','color'=>'#5b21b6'],
                                'Business Analyst'=> ['bg'=>'#ffedd5','color'=>'#9a3412'],
                                'Project Manager' => ['bg'=>'#e0e7ff','color'=>'#3730a3'],
                                'Coordinator'     => ['bg'=>'#f0fdf4','color'=>'#14532d'],
                            ];
                            $role = $member->pivot->role_in_team ?? 'Member';
                            $rc   = $roleColors[$role] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                        @endphp
                        <span style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }};padding:2px 10px;border-radius:20px;font-size:.74rem;font-weight:600;display:inline-block;">{{ $role }}</span>
                    </td>
                    <td style="font-size:.8rem;color:#9ca3af;">
                        {{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('d M Y') : '—' }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.teams.members.remove', [$team->id, $member->id]) }}"
                            onsubmit="return confirm('Remove {{ addslashes($member->full_name) }} from this team?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="act-btn act-delete" title="Remove from team" style="width:28px;height:28px;font-size:.75rem;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="py-3 text-center" style="color:#9ca3af;font-size:.84rem;border-top:1px solid #f3f4f6;">
        <i class="bi bi-people me-1"></i>No members yet — click <strong>Add Members</strong> to get started.
    </div>
    @endif
</div>
@empty
<div class="table-card">
    <div class="empty-state"><i class="bi bi-diagram-3"></i><p>No teams found</p></div>
</div>
@endforelse

@if($teams->hasPages())
<div class="pagination-wrap">{{ $teams->links('pagination::bootstrap-5') }}</div>
@endif

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
            <div class="modal-header" style="border-bottom:1.5px solid #f3f4f6;padding-bottom:14px;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:34px;height:34px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:9px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-people-fill text-white" style="font-size:.9rem;"></i>
                    </div>
                    <h6 class="modal-title fw-bold mb-0" id="membersModalTitle">Manage Members</h6>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:20px 24px;">
                <form id="addMembersForm" method="POST">
                    @csrf
                    <div class="row g-3">
                        {{-- Employee multi-select --}}
                        <div class="col-12">
                            <label class="flabel mb-1">Select Employees <span class="req">*</span></label>
                            {{-- Search box --}}
                            <div style="position:relative;margin-bottom:8px;">
                                <i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.8rem;pointer-events:none;"></i>
                                <input type="text" id="empSearch" placeholder="Search by name or designation…"
                                    oninput="filterEmpList(this.value)"
                                    style="width:100%;padding:8px 12px 8px 32px;border:1.5px solid #e5e7eb;border-radius:9px;font-size:.83rem;outline:none;background:#fff;">
                            </div>
                            {{-- Checkbox list --}}
                            <div id="empList" style="max-height:230px;overflow-y:auto;border:1.5px solid #e5e7eb;border-radius:10px;background:#fafafa;">
                                @forelse($employees as $emp)
                                <label class="emp-item d-flex align-items-center gap-2 px-3 py-2"
                                    data-name="{{ strtolower($emp->full_name . ' ' . $emp->display_designation) }}"
                                    style="cursor:pointer;border-bottom:1px solid #f3f4f6;margin:0;transition:background .12s;">
                                    <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}"
                                        class="emp-cb"
                                        style="width:16px;height:16px;accent-color:#6366f1;flex-shrink:0;cursor:pointer;"
                                        onchange="updateEmpCount()">
                                    <div style="line-height:1.3;">
                                        <div style="font-size:.84rem;font-weight:600;color:#111827;">{{ $emp->full_name }}</div>
                                        <div style="font-size:.73rem;color:#6b7280;">{{ $emp->display_designation }}
                                            @if($emp->department)<span style="color:#d1d5db;margin:0 4px;">·</span>{{ $emp->department->name }}@endif
                                        </div>
                                    </div>
                                </label>
                                @empty
                                <div class="text-center py-4" style="color:#9ca3af;font-size:.83rem;">No employees available</div>
                                @endforelse
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1" style="font-size:.74rem;color:#9ca3af;">
                                <span><span id="selectedCount" style="font-weight:700;color:#6366f1;">0</span> selected</span>
                                <button type="button" onclick="clearEmpSelection()" class="btn btn-link btn-sm p-0" style="font-size:.74rem;color:#9ca3af;text-decoration:none;">Clear all</button>
                            </div>
                        </div>

                        {{-- Role in Team --}}
                        <div class="col-md-6">
                            <label class="flabel mb-1">Role in Team</label>
                            <select name="role_in_team" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                                <option value="">— Select Role —</option>
                                <option value="Member">Member</option>
                                <option value="Team Lead">Team Lead</option>
                                <option value="Developer">Developer</option>
                                <option value="Designer">Designer</option>
                                <option value="QA Engineer">QA Engineer</option>
                                <option value="DevOps">DevOps</option>
                                <option value="Business Analyst">Business Analyst</option>
                                <option value="Project Manager">Project Manager</option>
                                <option value="Coordinator">Coordinator</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary-grad px-4">
                            <i class="bi bi-person-plus me-1"></i>Add Members
                        </button>
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
    document.getElementById('membersModalTitle').textContent = 'Members — ' + teamName;
    document.getElementById('addMembersForm').action = `/admin/teams/${teamId}/members`;
    // Reset search and checkboxes
    document.getElementById('empSearch').value = '';
    filterEmpList('');
    document.querySelectorAll('.emp-cb').forEach(cb => cb.checked = false);
    updateEmpCount();
    new bootstrap.Modal(document.getElementById('membersModal')).show();
}

function filterEmpList(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#empList .emp-item').forEach(item => {
        item.style.display = item.dataset.name.includes(q) ? '' : 'none';
    });
}

function updateEmpCount() {
    const count = document.querySelectorAll('.emp-cb:checked').length;
    document.getElementById('selectedCount').textContent = count;
}

function clearEmpSelection() {
    document.querySelectorAll('.emp-cb').forEach(cb => cb.checked = false);
    updateEmpCount();
}

// Hover effect on emp items
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.emp-item').forEach(item => {
        item.addEventListener('mouseenter', () => item.style.background = '#f0f4ff');
        item.addEventListener('mouseleave', () => {
            item.style.background = item.querySelector('.emp-cb').checked ? '#eef2ff' : '';
        });
        item.querySelector('.emp-cb').addEventListener('change', function () {
            item.style.background = this.checked ? '#eef2ff' : '';
        });
    });
});

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
