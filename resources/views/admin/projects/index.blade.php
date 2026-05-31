@extends('layouts.app')
@section('title', 'Project Management')

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Project Management</h4>
            <p>Manage projects and track progress</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-4">
                <div class="page-hero-stat"><div class="v" style="color:#86efac;">{{ $projects->where('status','active')->count() }}</div><div class="l">Active</div></div>
                <div class="hero-vr"></div>
                <div class="page-hero-stat"><div class="v" style="color:#93c5fd;">{{ $projects->where('status','planning')->count() }}</div><div class="l">Planning</div></div>
                <div class="hero-vr"></div>
                <div class="page-hero-stat"><div class="v">{{ $projects->total() }}</div><div class="l">Total</div></div>
            </div>
            <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                <i class="bi bi-plus-circle me-1"></i>New Project
            </button>
        </div>
    </div>
</div>

<div class="filter-card">
    <form method="GET" action="{{ route('admin.projects.index') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="flabel">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Search name, code…"
                value="{{ request('search') }}"
                style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
        </div>
        <div class="col-md-2">
            <label class="flabel">Status</label>
            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Status</option>
                @foreach(['planning','active','on_hold','completed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="flabel">Department</label>
            <select name="department" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        </div>
        <div class="col-md-auto">
            <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-outline-secondary px-3">Reset</a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="card-header"><span class="card-title">All Projects</span></div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Department</th>
                    <th>Manager</th>
                    <th>Timeline</th>
                    <th>Budget</th>
                    <th>Tasks</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                @php
                    $sMap = ['planning'=>'info','active'=>'success','on_hold'=>'warning','completed'=>'secondary','cancelled'=>'danger'];
                    $ps = $project->status ?? 'planning';
                    $isOverdue = $project->end_date && \Carbon\Carbon::parse($project->end_date)->isPast() && $ps !== 'completed';
                @endphp
                <tr {{ $isOverdue ? 'style=background:#fff5f5;' : '' }}>
                    <td>
                        <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ $project->name }}</div>
                        @if($project->code)
                        <div style="font-size:.72rem;color:#9ca3af;font-family:monospace;">{{ $project->code }}</div>
                        @endif
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $project->department->name ?? '—' }}</td>
                    <td style="font-size:.83rem;color:#374151;">{{ $project->manager->name ?? '—' }}</td>
                    <td>
                        <div style="font-size:.8rem;color:#374151;">
                            {{ \Carbon\Carbon::parse($project->start_date)->format('M d') }}
                            @if($project->end_date)
                                → <span style="{{ $isOverdue ? 'color:#dc2626;font-weight:700;' : '' }}">
                                    {{ \Carbon\Carbon::parse($project->end_date)->format('M d, Y') }}
                                </span>
                            @endif
                        </div>
                        @if($isOverdue)<span class="spill spill-danger" style="font-size:.65rem;">Overdue</span>@endif
                    </td>
                    <td style="font-size:.83rem;">
                        @if($project->budget)
                            <span style="font-weight:700;color:#374151;">${{ number_format($project->budget) }}</span>
                        @else <span style="color:#9ca3af;">—</span> @endif
                    </td>
                    <td>
                        <span style="background:#ede9fe;color:#7c3aed;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">{{ $project->tasks_count }}</span>
                    </td>
                    <td><span class="spill spill-{{ $sMap[$ps] ?? 'secondary' }}">{{ ucwords(str_replace('_',' ',$ps)) }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.projects.show', $project) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            <button class="act-btn act-delete" onclick="deleteProject({{ $project->id }})" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8">
                    <div class="empty-state"><i class="bi bi-kanban"></i><p>No projects found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($projects->hasPages())
    <div class="pagination-wrap">{{ $projects->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create Project Modal --}}
<div class="modal fade" id="createProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-kanban me-2"></i>Create New Project</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.projects.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="flabel">Project Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Code</label>
                            <input type="text" name="code" class="form-control" placeholder="PROJ-001"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Manager</label>
                            <select name="manager_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">Select Manager</option>
                                @foreach($managers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Department</label>
                            <select name="department_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status <span class="req">*</span></label>
                            <select name="status" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="planning">Planning</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Start Date <span class="req">*</span></label>
                            <input type="date" name="start_date" class="form-control" required value="{{ date('Y-m-d') }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">End Date</label>
                            <input type="date" name="end_date" class="form-control"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Budget ($)</label>
                            <input type="number" name="budget" class="form-control" step="0.01" min="0"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteProject(id) {
    APP.confirm('Delete this project?', 'This action cannot be undone.', function() {
        fetch(`/admin/projects/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            APP.toast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1000);
        });
    });
}
</script>
@endpush
