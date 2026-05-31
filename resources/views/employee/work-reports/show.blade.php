@extends('layouts.app')
@section('title', 'Work Report Details')

@section('content')

<a href="{{ route('employee.work-reports.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Reports</a>

@php
    $st = $report->status ?? 'draft';
    $score = $report->productivity_score ?? 0;
    $scoreColor = $score >= 80 ? '#16a34a' : ($score >= 60 ? '#d97706' : '#dc2626');
    $heroBg = $st === 'approved' ? 'linear-gradient(135deg,#14532d,#166534)' : ($st === 'rejected' ? 'linear-gradient(135deg,#7f1d1d,#991b1b)' : ($st === 'submitted' ? 'linear-gradient(135deg,#78350f,#92400e)' : 'linear-gradient(135deg,#1e1b4b,#312e81)'));
@endphp

<div class="page-hero" style="background:{{ $heroBg }};">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Work Report — {{ \Carbon\Carbon::parse($report->report_date)->format('d F Y') }}</h4>
            <p style="opacity:.8;">{{ auth()->user()->name }}</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="spill spill-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : ($st === 'submitted' ? 'warning' : 'secondary')) }}" style="font-size:.85rem;padding:6px 16px;">{{ ucfirst($st) }}</span>
            @if($report->status === 'draft')
            <form action="{{ route('employee.work-reports.submit', $report) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary-grad px-4">
                    <i class="bi bi-send me-1"></i>Submit Report
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-journal-text me-2"></i>Work Summary</div>
            <div class="info-card-body">
                <p style="font-size:.9rem;color:#374151;line-height:1.7;margin:0;">{{ $report->summary }}</p>
            </div>
        </div>

        @if($report->tasks_completed && count($report->tasks_completed))
        <div class="info-card mt-3">
            <div class="info-card-hdr"><i class="bi bi-check2-all me-2"></i>Tasks Completed</div>
            <div class="info-card-body">
                <ul style="margin:0;padding:0;list-style:none;">
                    @foreach($report->tasks_completed as $task)
                    <li style="display:flex;align-items:flex-start;gap:10px;padding:7px 0;border-bottom:1px solid #f3f4f6;">
                        <i class="bi bi-check-circle-fill" style="color:#16a34a;margin-top:2px;flex-shrink:0;"></i>
                        <span style="font-size:.85rem;color:#374151;">{{ $task }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if($report->reviewer_notes)
        <div class="info-card mt-3" style="border-left:4px solid {{ $st === 'rejected' ? '#dc2626' : '#16a34a' }};">
            <div class="info-card-hdr" style="color:{{ $st === 'rejected' ? '#dc2626' : '#16a34a' }};">
                <i class="bi bi-chat-quote me-2"></i>Reviewer Notes
            </div>
            <div class="info-card-body">
                <p style="font-size:.88rem;color:#374151;margin-bottom:8px;">{{ $report->reviewer_notes }}</p>
                @if($report->reviewedBy)
                <div style="font-size:.78rem;color:#9ca3af;">
                    — {{ $report->reviewedBy->name }},
                    {{ \Carbon\Carbon::parse($report->reviewed_at)->format('M d, Y H:i') }}
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-info-circle me-2"></i>Report Details</div>
            <div class="info-card-body">
                <dl class="dl">
                    <dt>Report Date</dt>
                    <dd style="font-weight:600;">{{ \Carbon\Carbon::parse($report->report_date)->format('d M Y') }}</dd>
                    <dt>Hours Worked</dt>
                    <dd><span style="background:#eff6ff;color:#2563eb;padding:3px 10px;border-radius:6px;font-weight:700;">{{ number_format($report->hours_worked, 1) }}h</span></dd>
                    <dt>Productivity Score</dt>
                    <dd>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                <div style="width:{{ $score }}%;height:100%;background:{{ $scoreColor }};border-radius:3px;"></div>
                            </div>
                            <span style="font-weight:700;color:{{ $scoreColor }};font-size:.83rem;">{{ $score }}%</span>
                        </div>
                    </dd>
                    <dt>Status</dt>
                    <dd><span class="spill spill-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : ($st === 'submitted' ? 'warning' : 'secondary')) }}">{{ ucfirst($st) }}</span></dd>
                    @if($report->submitted_at)
                    <dt>Submitted At</dt>
                    <dd>{{ \Carbon\Carbon::parse($report->submitted_at)->format('M d, Y H:i') }}</dd>
                    @endif
                    <dt>Created</dt>
                    <dd>{{ $report->created_at->format('M d, Y') }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
