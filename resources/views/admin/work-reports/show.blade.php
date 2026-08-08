@extends('layouts.app')
@section('title', 'Work Report Detail')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.work-reports.index') }}">Work Reports</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')

@php
    $st = $workReport->status ?? 'draft';
    $sc = $workReport->productivity_score ?? 0;
    $scoreColor = $sc >= 80 ? '#16a34a' : ($sc >= 50 ? '#d97706' : '#dc2626');
    $heroBg = $st === 'approved' ? 'linear-gradient(135deg,#14532d,#166534)' : ($st === 'rejected' ? 'linear-gradient(135deg,#7f1d1d,#991b1b)' : ($st === 'submitted' ? 'linear-gradient(135deg,#78350f,#92400e)' : 'linear-gradient(135deg,#1e1b4b,#312e81)'));
    $reviewGrad = $workReport->status === 'rejected' ? 'linear-gradient(135deg,#7f1d1d,#991b1b)' : 'linear-gradient(135deg,#14532d,#166534)';
@endphp

<div class="page-hero" style="background:{{ $heroBg }};">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Work Report — {{ $workReport->report_date?->format('d M Y') }}</h4>
            <p style="opacity:.8;">{{ $workReport->employee?->full_name }} &bull; {{ $workReport->employee?->designation }}</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('admin.work-reports.index') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:9px;flex-shrink:0;">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <span class="spill spill-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : ($st === 'submitted' ? 'warning' : 'secondary')) }}" style="font-size:.85rem;padding:6px 16px;">{{ ucfirst($st) }}</span>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Report Details --}}
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">
            <div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-journal-text" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Report Summary</span>
                </div>
            </div>
            <div class="p-4">
                <p style="font-size:.9rem;color:#374151;line-height:1.7;margin:0;">{{ $workReport->summary }}</p>
            </div>
        </div>

        @if(!empty($workReport->tasks_completed))
        <div class="card-glass overflow-hidden mt-3">
            <div style="background:linear-gradient(135deg,#16a34a,#059669);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check2-square" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Tasks Completed</span>
                </div>
            </div>
            <div class="p-4">
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
        <div class="card-glass overflow-hidden mt-3">
            <div style="background:{{ $reviewGrad }};padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-chat-quote" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Reviewer Notes</span>
                </div>
            </div>
            <div class="p-4">
                <p style="font-size:.88rem;color:#374151;margin:0;">{{ $workReport->reviewer_notes }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Right: Info + Review --}}
    <div class="col-lg-4">
        {{-- Report Info --}}
        <div class="card-glass overflow-hidden mb-3">
            <div style="background:linear-gradient(135deg,#374151,#1f2937);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Report Info</span>
                </div>
            </div>
            <div class="p-4">
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
        <div class="card-glass overflow-hidden">
            <div style="background:linear-gradient(135deg,#374151,#1f2937);padding:14px 20px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clipboard-check" style="color:rgba(255,255,255,.85);font-size:.9rem;"></i>
                    <span style="font-size:.82rem;font-weight:700;color:#fff;">Review Report</span>
                </div>
            </div>
            <div class="p-4">
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