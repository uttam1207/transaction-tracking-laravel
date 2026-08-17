@extends('layouts.app')

@section('title', 'Enrollments — ' . $trainingProgram->title)

@section('content')
<div class="page-hero d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-hero-title">{{ $trainingProgram->title }}</h1>
        <p class="page-hero-sub mb-0">
            Manage enrollments &nbsp;·&nbsp;
            <span class="badge bg-{{ $trainingProgram->status === 'ongoing' ? 'success' : ($trainingProgram->status === 'completed' ? 'secondary' : 'primary') }}">
                {{ ucfirst($trainingProgram->status) }}
            </span>
            &nbsp;·&nbsp; {{ $trainingProgram->mode }}
            @if($trainingProgram->start_date)
                &nbsp;·&nbsp; {{ $trainingProgram->start_date->format('d M Y') }}
                @if($trainingProgram->end_date) – {{ $trainingProgram->end_date->format('d M Y') }} @endif
            @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#enrollModal">
            <i class="bi bi-person-plus me-1"></i> Enroll Employees
        </button>
        <a href="{{ route('admin.training.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- Filter bar --}}
<div class="filter-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach(['enrolled','attending','completed','failed','withdrawn'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        @if(request('status'))
        <div class="col-auto">
            <a href="{{ route('admin.training.enrollments', $trainingProgram) }}" class="btn btn-outline-secondary btn-sm">Clear</a>
        </div>
        @endif
    </form>
</div>

<div class="table-card">
    <table class="modern-table table mb-0">
        <thead>
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Status</th>
                <th>Score</th>
                <th>Completed</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($enrollments as $enrollment)
            <tr>
                <td class="text-muted">{{ $enrollments->firstItem() + $loop->index }}</td>
                <td>
                    <div class="fw-semibold">{{ $enrollment->employee->user->name ?? '—' }}</div>
                    <small class="text-muted">{{ $enrollment->employee->employee_id ?? '' }}</small>
                </td>
                <td>{{ $enrollment->employee->department->name ?? '—' }}</td>
                <td>
                    @php
                        $cls = match($enrollment->status) {
                            'completed'  => 'success',
                            'attending'  => 'primary',
                            'failed'     => 'danger',
                            'withdrawn'  => 'secondary',
                            default      => 'info',
                        };
                    @endphp
                    <span class="badge bg-{{ $cls }}">{{ ucfirst($enrollment->status) }}</span>
                </td>
                <td>{{ $enrollment->score !== null ? $enrollment->score . '/100' : '—' }}</td>
                <td>{{ $enrollment->completed_at ? $enrollment->completed_at->format('d M Y') : '—' }}</td>
                <td>
                    <button class="btn btn-xs btn-outline-primary"
                        onclick="openUpdateModal({{ $enrollment->id }}, '{{ $enrollment->status }}', {{ $enrollment->score ?? 'null' }})">
                        Update
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No enrollments yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-3">{{ $enrollments->links() }}</div>
</div>

{{-- Enroll Modal --}}
<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="enrollForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enroll Employees</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Select Employees <span class="text-danger">*</span></label>
                    <select name="employee_ids[]" class="form-select" multiple size="8" required>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->user->name ?? '—' }} ({{ $emp->employee_id ?? $emp->id }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Enroll</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Update Enrollment Modal --}}
<div class="modal fade" id="updateEnrollmentModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form id="updateEnrollmentForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(['enrolled','attending','completed','failed','withdrawn'] as $s)
                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Score (0–100)</label>
                        <input type="number" name="score" class="form-control" min="0" max="100" step="0.5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Feedback</label>
                        <textarea name="feedback" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const enrollBase = "{{ route('admin.training.enroll', $trainingProgram) }}";
const updateBase  = "{{ url('admin/training/enrollments') }}";

document.getElementById('enrollForm').action = enrollBase;

function openUpdateModal(id, status, score) {
    const f = document.getElementById('updateEnrollmentForm');
    f.action = updateBase + '/' + id;
    f.querySelector('[name=status]').value = status;
    f.querySelector('[name=score]').value  = score ?? '';
    new bootstrap.Modal(document.getElementById('updateEnrollmentModal')).show();
}

['enrollForm','updateEnrollmentForm'].forEach(id => {
    document.getElementById(id).addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd  = new FormData(this);
        const res = await fetch(this.action, { method: 'POST', headers: {'X-CSRF-TOKEN': fd.get('_token'), 'Accept': 'application/json'}, body: fd });
        const data = await res.json();
        bootstrap.Modal.getInstance(document.querySelector('.modal.show')).hide();
        if (data.success) { APP.toast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
        else APP.toast(data.message ?? 'Error', 'danger');
    });
});
</script>
@endpush
