@extends('layouts.app')
@section('title', 'Recruitment')
@section('breadcrumb')
    <li class="breadcrumb-item active">Recruitment</li>
@endsection

@section('content')
<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div><h4>Recruitment</h4><p>Manage job postings and track applications</p></div>
        <button class="btn btn-sm btn-primary-grad px-4" onclick="openCreateJob()"><i class="bi bi-plus-lg me-1"></i>New Job</button>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
    @foreach([['Open','open',$stats['open'],'success','briefcase'],['Filled','filled',$stats['filled'],'primary','person-check'],['Draft','draft',$stats['draft'],'warning','file-earmark']] as [$label,$key,$val,$color,$icon])
    <div class="col-md-4">
        <div class="stat-card" style="border-left:4px solid var(--bs-{{ $color }});">
            <div class="stat-icon bg-{{ $color }} bg-opacity-10"><i class="bi bi-{{ $icon }} text-{{ $color }}"></i></div>
            <div><div class="stat-value">{{ $val }}</div><div class="stat-label">{{ $label }}</div></div>
        </div>
    </div>
    @endforeach
</div>

<div class="filter-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="flabel">Search</label><input name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Title or code…" style="border-radius:8px;"></div>
        <div class="col-md-3"><label class="flabel">Department</label>
            <select name="department_id" class="form-select form-select-sm">
                <option value="">All Departments</option>
                @foreach($departments as $d)<option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="flabel">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach(['draft','open','on_hold','closed','filled'] as $s)<option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-sm btn-primary-grad px-4">Filter</button><a href="{{ route('admin.recruitment.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a></div>
    </form>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">Job Postings</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $jobs->total() }} jobs</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr><th>Job</th><th>Department</th><th>Vacancies</th><th>Closing</th><th>Applications</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:.88rem;">{{ $job->title }}</div>
                        <span style="background:#f0f4ff;color:#4f46e5;padding:2px 7px;border-radius:6px;font-size:.7rem;font-family:monospace;">{{ $job->job_code }}</span>
                    </td>
                    <td style="font-size:.83rem;">{{ $job->department?->name ?? '—' }}</td>
                    <td style="font-size:.83rem;text-align:center;">{{ $job->vacancies }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $job->closing_date?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.recruitment.applications', $job) }}" class="badge bg-purple bg-opacity-15 text-purple" style="font-size:.75rem;border-radius:20px;font-weight:700;text-decoration:none;background:#ede9fe!important;color:#7c3aed!important;">
                            {{ $job->application_count }} apps
                        </a>
                    </td>
                    <td>
                        @php $sc = ['open'=>'success','closed'=>'secondary','draft'=>'warning','filled'=>'primary','on_hold'=>'info']; @endphp
                        <span class="spill spill-{{ $sc[$job->status]??'secondary' }}">{{ ucfirst(str_replace('_',' ',$job->status)) }}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" onclick="openEditJob({{ $job->id }})"><i class="bi bi-pencil"></i></button>
                            <button class="act-btn act-delete" onclick="deleteJob({{ $job->id }})"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-briefcase"></i><p>No job postings yet</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($jobs->hasPages())
    <div class="pagination-wrap">{{ $jobs->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Job Modal --}}
<div class="modal fade" id="jobModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold" id="jobModalTitle">New Job Posting</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="flabel">Job Title <span class="req">*</span></label><input type="text" id="jobTitle" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-3"><label class="flabel">Code <span class="req">*</span></label><input type="text" id="jobCode" class="form-control" placeholder="JOB-001" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;"></div>
                    <div class="col-md-3"><label class="flabel">Vacancies</label><input type="number" id="jobVacancies" class="form-control" value="1" min="1" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4"><label class="flabel">Department</label>
                        <select id="jobDept" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;"><option value="">— None —</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-4"><label class="flabel">Designation</label>
                        <select id="jobDesig" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;"><option value="">— None —</option>@foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-4"><label class="flabel">Status</label>
                        <select id="jobStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @foreach(['draft','open','on_hold','closed','filled'] as $s)<option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label class="flabel">Posted Date</label><input type="date" id="jobPosted" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-3"><label class="flabel">Closing Date</label><input type="date" id="jobClosing" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-3"><label class="flabel">Min Salary</label><input type="number" id="jobSalMin" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-3"><label class="flabel">Max Salary</label><input type="number" id="jobSalMax" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-12"><label class="flabel">Description</label><textarea id="jobDesc" class="form-control" rows="3" style="border-radius:9px;border:1.5px solid #e5e7eb;"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-primary-grad px-4" onclick="saveJob()" id="jobSubmitBtn">Post Job</button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;
const allJobs   = @json($jobs->items());
let editingJobId = null;

function openCreateJob() {
    editingJobId = null;
    document.getElementById('jobModalTitle').textContent = 'New Job Posting';
    document.getElementById('jobSubmitBtn').textContent  = 'Post Job';
    ['jobTitle','jobCode','jobDesc','jobPosted','jobClosing','jobSalMin','jobSalMax'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('jobVacancies').value = 1;
    document.getElementById('jobStatus').value = 'draft';
    new bootstrap.Modal(document.getElementById('jobModal')).show();
}

function openEditJob(id) {
    const j = allJobs.find(x => x.id === id);
    if (!j) return;
    editingJobId = id;
    document.getElementById('jobModalTitle').textContent = 'Edit Job';
    document.getElementById('jobSubmitBtn').textContent  = 'Save Changes';
    document.getElementById('jobTitle').value     = j.title;
    document.getElementById('jobCode').value      = j.job_code;
    document.getElementById('jobVacancies').value = j.vacancies;
    document.getElementById('jobStatus').value    = j.status;
    document.getElementById('jobPosted').value    = j.posted_date || '';
    document.getElementById('jobClosing').value   = j.closing_date || '';
    document.getElementById('jobSalMin').value    = j.salary_range_min || '';
    document.getElementById('jobSalMax').value    = j.salary_range_max || '';
    document.getElementById('jobDesc').value      = j.description || '';
    document.getElementById('jobDept').value      = j.department_id || '';
    document.getElementById('jobDesig').value     = j.designation_id || '';
    new bootstrap.Modal(document.getElementById('jobModal')).show();
}

function saveJob() {
    const payload = {
        title: document.getElementById('jobTitle').value, job_code: document.getElementById('jobCode').value,
        department_id: document.getElementById('jobDept').value, designation_id: document.getElementById('jobDesig').value,
        vacancies: document.getElementById('jobVacancies').value, status: document.getElementById('jobStatus').value,
        posted_date: document.getElementById('jobPosted').value, closing_date: document.getElementById('jobClosing').value,
        salary_range_min: document.getElementById('jobSalMin').value, salary_range_max: document.getElementById('jobSalMax').value,
        description: document.getElementById('jobDesc').value, employment_type: 'full_time',
    };
    const url    = editingJobId ? `/admin/recruitment/${editingJobId}` : '{{ route("admin.recruitment.store") }}';
    const method = editingJobId ? 'PUT' : 'POST';
    fetch(url, { method, headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json' }, body: JSON.stringify(payload) })
        .then(r=>r.json()).then(d => {
            if (d.success) { APP.toast(d.message); setTimeout(() => location.reload(), 800); }
            else APP.toast(d.message || 'Error', 'error');
        });
}

function deleteJob(id) {
    APP.confirm('Delete job posting?', 'All applications will also be removed.', () => {
        fetch(`/admin/recruitment/${id}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'} })
            .then(r=>r.json()).then(d => { if(d.success){APP.toast('Deleted.');setTimeout(()=>location.reload(),800);}else APP.toast(d.message||'Error','error'); });
    });
}
</script>
@endsection
