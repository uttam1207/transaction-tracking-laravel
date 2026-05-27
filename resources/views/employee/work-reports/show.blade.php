@extends('layouts.app')

@section('title', 'Work Report Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('employee.work-reports.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Reports
        </a>
        <h4 class="mb-0 fw-bold mt-1">
            Work Report — {{ \Carbon\Carbon::parse($report->report_date)->format('d F Y') }}
        </h4>
    </div>
    <div class="d-flex gap-2">
        @php
            $statusColors = ['draft' => 'secondary', 'submitted' => 'info', 'approved' => 'success', 'rejected' => 'danger'];
            $st = $report->status ?? 'draft';
        @endphp
        <span class="badge bg-{{ $statusColors[$st] }} fs-6">{{ ucfirst($st) }}</span>
        @if($report->status === 'draft')
        <form action="{{ route('employee.work-reports.submit', $report) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send me-1"></i>Submit
            </button>
        </form>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Work Summary</div>
            <div class="card-body">
                <p>{{ $report->summary }}</p>
            </div>
        </div>

        @if($report->tasks_completed && count($report->tasks_completed))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Tasks Completed</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @foreach($report->tasks_completed as $task)
                    <li class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        {{ $task }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if($report->reviewer_notes)
        <div class="card border-0 shadow-sm border-{{ $st === 'rejected' ? 'danger' : 'success' }}">
            <div class="card-header bg-transparent fw-semibold">
                <i class="bi bi-chat-left-text me-2"></i>Reviewer Notes
            </div>
            <div class="card-body">
                <p class="mb-1">{{ $report->reviewer_notes }}</p>
                @if($report->reviewedBy)
                <small class="text-muted">
                    — {{ $report->reviewedBy->name }},
                    {{ \Carbon\Carbon::parse($report->reviewed_at)->format('M d, Y H:i') }}
                </small>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">Report Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Report Date</label>
                    <div class="fw-semibold">{{ \Carbon\Carbon::parse($report->report_date)->format('d M Y') }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Hours Worked</label>
                    <div class="fw-semibold fs-4">{{ number_format($report->hours_worked, 1) }}h</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Productivity Score</label>
                    @php $score = $report->productivity_score ?? 0; @endphp
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-fill" style="height: 8px;">
                            <div class="progress-bar bg-{{ $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger') }}"
                                style="width: {{ $score }}%"></div>
                        </div>
                        <strong>{{ $score }}%</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Status</label>
                    <div>
                        <span class="badge bg-{{ $statusColors[$st] }} fs-6">{{ ucfirst($st) }}</span>
                    </div>
                </div>
                @if($report->submitted_at)
                <div class="mb-3">
                    <label class="text-muted small">Submitted At</label>
                    <div>{{ \Carbon\Carbon::parse($report->submitted_at)->format('M d, Y H:i') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
