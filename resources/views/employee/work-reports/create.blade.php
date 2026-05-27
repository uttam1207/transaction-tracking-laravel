@extends('layouts.app')

@section('title', 'Create Work Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('employee.work-reports.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Reports
        </a>
        <h4 class="mb-0 fw-bold mt-1">Create Work Report</h4>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('employee.work-reports.store') }}" method="POST">
            @csrf
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Report Date</label>
                    <input type="date" name="report_date" class="form-control @error('report_date') is-invalid @enderror"
                        value="{{ old('report_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                    @error('report_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hours Worked</label>
                    <input type="number" name="hours_worked" class="form-control @error('hours_worked') is-invalid @enderror"
                        value="{{ old('hours_worked', 8) }}" step="0.5" min="0" max="24" required>
                    @error('hours_worked')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Productivity Score (0-100)</label>
                    <input type="number" name="productivity_score" class="form-control"
                        value="{{ old('productivity_score', 80) }}" min="0" max="100">
                </div>
                <div class="col-12">
                    <label class="form-label">Work Summary</label>
                    <textarea name="summary" class="form-control @error('summary') is-invalid @enderror"
                        rows="4" required placeholder="Describe what you worked on today...">{{ old('summary') }}</textarea>
                    @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Tasks Completed Today</label>
                    <div id="tasksList">
                        <div class="input-group mb-2">
                            <input type="text" name="tasks_completed[]" class="form-control"
                                placeholder="Enter a task you completed...">
                            <button type="button" class="btn btn-outline-secondary" onclick="addTaskField()">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                    <i class="bi bi-save me-1"></i>Save as Draft
                </button>
                <button type="submit" name="action" value="submit" class="btn btn-primary">
                    <i class="bi bi-send me-1"></i>Submit Report
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addTaskField() {
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="tasks_completed[]" class="form-control" placeholder="Enter a task you completed...">
        <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
            <i class="bi bi-trash"></i>
        </button>
    `;
    document.getElementById('tasksList').appendChild(div);
}
</script>
@endpush
