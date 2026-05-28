@extends('layouts.app')

@section('title', 'Project Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Project Management</h4>
        <p class="text-muted mb-0">Manage projects and track progress</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectModal">
        <i class="bi bi-plus-circle me-1"></i>New Project
    </button>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @php
        $statusCounts = $projects->groupBy('status');
    @endphp
    @foreach(['active' => ['label'=>'Active','color'=>'success'], 'planning' => ['label'=>'Planning','color'=>'info'], 'on_hold' => ['label'=>'On Hold','color'=>'warning'], 'completed' => ['label'=>'Completed','color'=>'primary']] as $s => $cfg)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3 border-start border-{{ $cfg['color'] }} border-4">
            <div class="fs-2 fw-bold text-{{ $cfg['color'] }}">
                {{ $projects->where('status', $s)->count() }}
            </div>
            <div class="text-muted">{{ $cfg['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.projects.index') }}" class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search name, code..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(['planning','active','on_hold','completed','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>
                            {{ ucwords(str_replace('_', ' ', $s)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Project Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Department</th>
                        <th>Manager</th>
                        <th>Timeline</th>
                        <th>Budget</th>
                        <th>Tasks</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                    @php
                        $statusColors = ['planning' => 'info', 'active' => 'success', 'on_hold' => 'warning', 'completed' => 'primary', 'cancelled' => 'danger'];
                        $ps = $project->status ?? 'planning';
                        $isOverdue = $project->end_date && \Carbon\Carbon::parse($project->end_date)->isPast() && $ps !== 'completed';
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $project->name }}</div>
                            <small class="text-muted"><code>{{ $project->code ?? '—' }}</code></small>
                        </td>
                        <td>{{ $project->department->name ?? '—' }}</td>
                        <td>{{ $project->manager->name ?? '—' }}</td>
                        <td>
                            <small>
                                {{ \Carbon\Carbon::parse($project->start_date)->format('M d') }}
                                @if($project->end_date)
                                    → <span class="{{ $isOverdue ? 'text-danger' : '' }}">
                                        {{ \Carbon\Carbon::parse($project->end_date)->format('M d, Y') }}
                                    </span>
                                @endif
                            </small>
                        </td>
                        <td>
                            @if($project->budget)
                                ${{ number_format($project->budget) }}
                            @else — @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary rounded-pill">{{ $project->tasks_count }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$ps] ?? 'secondary' }}">
                                {{ ucwords(str_replace('_', ' ', $ps)) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.projects.show', $project) }}"
                                    class="btn btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-outline-danger"
                                    onclick="deleteProject({{ $project->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-kanban fs-1 d-block mb-2"></i>
                            No projects found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($projects->hasPages())
    <div class="card-footer bg-transparent">{{ $projects->withQueryString()->links() }}</div>
    @endif
</div>

{{-- Create Project Modal --}}
<div class="modal fade" id="createProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.projects.store') }}" method="POST" id="createProjectForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control" placeholder="PROJ-001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Manager</label>
                            <select name="manager_id" class="form-select">
                                <option value="">Select Manager</option>
                                @foreach($managers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="planning">Planning</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Budget ($)</label>
                            <input type="number" name="budget" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Project</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteProject(id) {
    APP.confirm('Delete this project?', function() {
        fetch(`/admin/projects/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            APP.toast(data.message, data.success ? 'success' : 'danger');
            if (data.success) setTimeout(() => location.reload(), 1000);
        });
    });
}
</script>
@endpush
