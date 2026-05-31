@extends('layouts.app')
@section('title', 'Create Work Report')

@section('content')

<a href="{{ route('employee.work-reports.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Reports</a>

<div class="page-hero">
    <div style="position:relative;z-index:1;">
        <h4>Create Work Report</h4>
        <p>Document your daily work activities and progress</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <form action="{{ route('employee.work-reports.store') }}" method="POST" id="reportForm">
            @csrf
            <div class="form-section">
                <div class="form-section-hdr"><i class="bi bi-journal-text me-2"></i>Report Details</div>
                <div class="form-section-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="flabel">Report Date <span class="req">*</span></label>
                            <input type="date" name="report_date" class="form-control @error('report_date') is-invalid @enderror"
                                value="{{ old('report_date', date('Y-m-d')) }}"
                                max="{{ date('Y-m-d') }}" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('report_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Hours Worked <span class="req">*</span></label>
                            <input type="number" name="hours_worked" class="form-control @error('hours_worked') is-invalid @enderror"
                                value="{{ old('hours_worked', 8) }}" step="0.5" min="0" max="24" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('hours_worked')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Productivity Score <span style="color:#9ca3af;font-size:.74rem;">(0–100)</span></label>
                            <input type="number" name="productivity_score" class="form-control"
                                value="{{ old('productivity_score', 80) }}" min="0" max="100"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;"
                                oninput="updateScoreBar(this.value)">
                            <div style="height:5px;background:#f3f4f6;border-radius:3px;overflow:hidden;margin-top:8px;">
                                <div id="scoreBar" style="width:80%;height:100%;background:#16a34a;border-radius:3px;transition:width .2s,background .2s;"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Work Summary <span class="req">*</span></label>
                            <textarea name="summary" rows="4" class="form-control @error('summary') is-invalid @enderror"
                                required placeholder="Describe what you worked on today…"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;resize:vertical;">{{ old('summary') }}</textarea>
                            @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section mt-3">
                <div class="form-section-hdr"><i class="bi bi-check2-all me-2"></i>Tasks Completed</div>
                <div class="form-section-body">
                    <div id="tasksList">
                        <div class="input-group mb-2">
                            <span class="input-group-text" style="background:#f8f9fa;border:1.5px solid #e5e7eb;border-right:none;border-radius:9px 0 0 9px;">
                                <i class="bi bi-check-circle" style="color:#16a34a;"></i>
                            </span>
                            <input type="text" name="tasks_completed[]" class="form-control"
                                placeholder="Enter a task you completed today…"
                                style="border:1.5px solid #e5e7eb;border-left:none;border-radius:0 9px 9px 0;font-size:.85rem;">
                            <button type="button" class="btn btn-sm" onclick="addTaskField()"
                                style="background:#6366f1;color:#fff;border:none;border-radius:0 9px 9px 0;margin-left:6px;width:36px;">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm" onclick="addTaskField()"
                        style="background:#f0f4ff;color:#6366f1;border:1.5px dashed #c7d2fe;border-radius:8px;font-size:.8rem;padding:6px 16px;margin-top:4px;">
                        <i class="bi bi-plus me-1"></i>Add Another Task
                    </button>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" name="action" value="draft" class="btn btn-sm btn-outline-secondary px-4" style="border-radius:9px;">
                    <i class="bi bi-save me-1"></i>Save as Draft
                </button>
                <button type="submit" name="action" value="submit" class="btn btn-sm btn-primary-grad px-4">
                    <i class="bi bi-send me-1"></i>Submit Report
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div style="background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:14px;padding:20px;color:#fff;">
            <div style="font-size:.8rem;font-weight:700;opacity:.6;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Tips</div>
            <ul style="padding-left:16px;font-size:.84rem;line-height:1.8;opacity:.85;margin:0;">
                <li>Be specific about what you accomplished</li>
                <li>List each major task separately</li>
                <li>Rate your productivity honestly</li>
                <li>Include any blockers or challenges</li>
                <li>Submit by end of day for best record</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateScoreBar(val) {
    const bar = document.getElementById('scoreBar');
    bar.style.width = val + '%';
    bar.style.background = val >= 80 ? '#16a34a' : (val >= 50 ? '#d97706' : '#dc2626');
}

function addTaskField() {
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <span class="input-group-text" style="background:#f8f9fa;border:1.5px solid #e5e7eb;border-right:none;border-radius:9px 0 0 9px;">
            <i class="bi bi-check-circle" style="color:#16a34a;"></i>
        </span>
        <input type="text" name="tasks_completed[]" class="form-control"
            placeholder="Enter a task you completed today…"
            style="border:1.5px solid #e5e7eb;border-left:none;border-radius:0 9px 9px 0;font-size:.85rem;">
        <button type="button" class="btn btn-sm" onclick="this.closest('.input-group').remove()"
            style="background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;border-radius:0 9px 9px 0;margin-left:6px;width:36px;">
            <i class="bi bi-trash"></i>
        </button>
    `;
    document.getElementById('tasksList').appendChild(div);
}
</script>
@endpush
