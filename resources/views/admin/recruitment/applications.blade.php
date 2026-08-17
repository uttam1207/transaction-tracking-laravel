@extends('layouts.app')
@section('title', 'Applications — ' . $recruitmentJob->title)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.recruitment.index') }}">Recruitment</a></li>
    <li class="breadcrumb-item active">{{ $recruitmentJob->title }}</li>
@endsection

@section('content')
<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $recruitmentJob->title }}</h4>
            <p>{{ $recruitmentJob->department?->name }} &bull; {{ $recruitmentJob->vacancies }} vacanc{{ $recruitmentJob->vacancies===1?'y':'ies' }} &bull; <span class="badge bg-success bg-opacity-15 text-success">{{ ucfirst($recruitmentJob->status) }}</span></p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" onclick="openAddApp()"><i class="bi bi-plus-lg me-1"></i>Add Application</button>
    </div>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">Applications</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $applications->total() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead><tr><th>Applicant</th><th>Contact</th><th>Stage</th><th>Interview</th><th>Applied</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($applications as $app)
                @php $stageColors = ['applied'=>'secondary','screening'=>'warning','shortlisted'=>'info','interview_scheduled'=>'primary','interviewed'=>'primary','offer_sent'=>'success','hired'=>'success','rejected'=>'danger']; @endphp
                <tr>
                    <td style="font-weight:700;font-size:.88rem;">{{ $app->applicant_name }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $app->applicant_email }}<br>{{ $app->applicant_phone }}</td>
                    <td>
                        <select class="form-select form-select-sm" style="border-radius:8px;font-size:.78rem;width:auto;" onchange="updateStage({{ $app->id }}, this.value)">
                            @foreach(\App\Models\RecruitmentApplication::$stageLabels as $val=>$label)
                            <option value="{{ $val }}" {{ $app->stage===$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $app->interview_date?->format('d M Y') ?? '—' }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $app->applied_at->format('d M Y') }}</td>
                    <td>
                        @if($app->resume_path)
                        <a href="{{ asset('storage/'.$app->resume_path) }}" target="_blank" class="act-btn" title="Resume"><i class="bi bi-file-earmark-pdf"></i></a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state"><i class="bi bi-people"></i><p>No applications yet</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($applications->hasPages())
    <div class="pagination-wrap">{{ $applications->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Add Application Modal --}}
<div class="modal fade" id="appModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold">Add Application</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12"><label class="flabel">Name <span class="req">*</span></label><input type="text" id="appName" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-6"><label class="flabel">Email <span class="req">*</span></label><input type="email" id="appEmail" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-6"><label class="flabel">Phone</label><input type="text" id="appPhone" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-6"><label class="flabel">Stage</label>
                        <select id="appStage" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @foreach(\App\Models\RecruitmentApplication::$stageLabels as $val=>$label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="flabel">Interview Date</label><input type="date" id="appInterview" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-12"><label class="flabel">Notes</label><textarea id="appNotes" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-primary-grad px-4" onclick="saveApp()">Add</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

function openAddApp() { new bootstrap.Modal(document.getElementById('appModal')).show(); }

function saveApp() {
    const payload = {
        applicant_name: document.getElementById('appName').value,
        applicant_email: document.getElementById('appEmail').value,
        applicant_phone: document.getElementById('appPhone').value,
        stage: document.getElementById('appStage').value,
        interview_date: document.getElementById('appInterview').value,
        notes: document.getElementById('appNotes').value,
    };
    fetch('{{ route("admin.recruitment.applications.store", $recruitmentJob) }}', {
        method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json' },
        body: JSON.stringify(payload),
    }).then(r=>r.json()).then(d=>{
        if(d.success){APP.toast('Application added.');setTimeout(()=>location.reload(),800);}
        else APP.toast(d.message||'Error','error');
    });
}

function updateStage(id, stage) {
    fetch(`/admin/recruitment/applications/${id}`, {
        method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({ stage }),
    }).then(r=>r.json()).then(d=>{ if(d.success) APP.toast('Stage updated.'); else APP.toast('Error','error'); });
}
</script>
@endpush
