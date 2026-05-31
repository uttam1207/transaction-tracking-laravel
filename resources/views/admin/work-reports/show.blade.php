@extends('layouts.app')
@section('title', 'Work Report Detail')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.work-reports.index') }}">Work Reports</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')

<a href="{{ route('admin.work-reports.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Work Reports</a>

@php
    $st = $workReport->status ?? 'draft';
    $sc = $workReport->productivity_score ?? 0;
    $scoreColor = $sc >= 80 ? '#16a34a' : ($sc >= 50 ? '#d97706' : '#dc2626');
    $heroBg = $st === 'approved' ? 'linear-gradient(135deg,#14532d,#166534)' : ($st === 'rejected' ? 'linear-gradient(135deg,#7f1d1d,#991b1b)' : ($st === 'submitted' ? 'linear-gradient(135deg,#78350f,#92400e)' : 'linear-gradient(135deg,#1e1b4b,#312e81)'));
@endphp

<div class="page-hero" style="background:{{ $heroBg }};">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Work Report — {{ $workReport->report_date?->format('d M Y') }}</h4>
            <p style="opacity:.8;">{{ $workReport->employee?->full_name }} &bull; {{ $workReport->employee?->designation }}</p>
        </div>
        <span class="spill spill-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : ($st === 'submitted' ? 'warning' : 'secondary')) }}" style="font-size:.85rem;padding:6px 16px;">{{ ucfirst($st) }}</span>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Report Details --}}
    <div class="col-lg-8">
        <div class="info-card">
            <div class="info-card-hdr">
                <i class="bi bi-journal-text me-2"></i>Report Summary
            </div>
            <div class="info-card-body">
                <p style="font-size:.9rem;color:#374151;line-height:1.7;">{{ $workReport->summary }}</p>
            </div>
        </div>

        @if(!empty($workReport->tasks_completed))
        <div class="info-card mt-3">
            <div class="info-card-hdr"><i class="bi bi-check2-square me-2"></i>Tasks Completed</div>
            <div class="info-card-body">
                <ul style="margin:0;padding:0;list-style:none;">
                    @foreach((array)$workReport->tasks_completed as $task)
                    <li style="display:flex;align-items:flex-start;gap:10px;padding:7px 0;border-bottom:1px solid #f3f4f6;">
                        <i class="bi bi-check-circle-fill" style="color:#16a34a;margin-top:2px;flex-shrink:0;"></i>
                        <span style="font-size:.85rem;color:#374151;">{{ $task }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @if($workReport->reviewer_notes)
        <div class="info-card mt-3" style="border-left:4px solid {{ $workReport->status === 'rejected' ? '#dc2626' : '#16a34a' }};">
            <div class="info-card-hdr" style="color:{{ $workReport->status === 'rejected' ? '#dc2626' : '#16a34a' }};">
                <i class="bi bi-chat-quote me-2"></i>Reviewer Notes
            </div>
            <div class="info-card-body">
                <p style="font-size:.88rem;color:#374151;margin:0;">{{ $workReport->reviewer_notes }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Right: Info + Review --}}
    <div class="col-lg-4">
        {{-- Report Info --}}
        <div class="info-card mb-3">
            <div class="info-card-hdr"><i class="bi bi-info-circle me-2"></i>Report Info</div>
            <div class="info-card-body">
                <dl class="dl">
                    <dt>Hours Worked</dt>
                    <dd><span style="background:#eff6ff;color:#2563eb;padding:3px 10px;border-radius:6px;font-weight:700;font-size:.82rem;">{{ $workReport->hours_worked }}h</span></dd>
                    <dt>Productivity Score</dt>
                    <dd>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                <div style="width:{{ $sc }}%;height:100%;background:{{ $scoreColor }};border-radius:3px;"></div>
                            </div>
                            <span style="font-weight:700;color:{{ $scoreColor }};font-size:.83rem;">{{ $sc }}%</span>
                        </div>
                    </dd>
                    <dt>Report Date</dt>
                    <dd>{{ $workReport->report_date?->format('d M Y') ?? '—' }}</dd>
                    <dt>Submitted At</dt>
                    <dd>{{ $workReport->submitted_at?->format('d M Y H:i') ?? '—' }}</dd>
                    <dt>Created</dt>
                    <dd>{{ $workReport->created_at->format('d M Y') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Review Actions --}}
        @if($workReport->status === 'submitted')
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-clipboard-check me-2"></i>Review Report</div>
            <div class="info-card-body">
                <form method="POST" action="{{ route('admin.work-reports.approve', $workReport) }}" class="mb-3">
                    @csrf
                    <label class="flabel">Reviewer Notes <span style="color:#9ca3af;font-size:.74rem;">(optional)</span></label>
                    <textarea name="reviewer_notes" rows="2" class="form-control mb-2"
                        placeholder="Well done on this report…"
                        style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;font-size:.84rem;"></textarea>
                    <button type="submit" class="btn btn-sm w-100" style="background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border:none;border-radius:9px;font-weight:600;padding:8px;">
                        <i class="bi bi-check-circle me-1"></i>Approve Report
                    </button>
                </form>

                <div style="border-top:1px solid #f3f4f6;padding-top:16px;">
                    <form method="POST" action="{{ route('admin.work-reports.reject', $workReport) }}">
                        @csrf
                        <label class="flabel">Rejection Reason <span class="req">*</span></label>
                        <textarea name="reviewer_notes" rows="2" class="form-control mb-2" required
                            placeholder="Explain the issue…"
                            style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;font-size:.84rem;"></textarea>
                        <button type="submit" class="btn btn-sm w-100 btn-danger" style="border-radius:9px;font-weight:600;padding:8px;">
                            <i class="bi bi-x-circle me-1"></i>Reject Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
