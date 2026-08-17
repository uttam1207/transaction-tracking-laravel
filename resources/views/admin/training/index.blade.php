@extends('layouts.app')
@section('title', 'Training Programs')
@section('breadcrumb')
    <li class="breadcrumb-item active">Training</li>
@endsection

@section('content')
<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div><h4>Training Programs</h4><p>Manage employee training and skill development</p></div>
        <button class="btn btn-sm btn-primary-grad px-4" onclick="openCreate()"><i class="bi bi-plus-lg me-1"></i>New Program</button>
    </div>
</div>

<div class="filter-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4"><label class="flabel">Search</label><input name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Program title…" style="border-radius:8px;"></div>
        <div class="col-md-3"><label class="flabel">Department</label>
            <select name="department_id" class="form-select form-select-sm">
                <option value="">All</option>@foreach($departments as $d)<option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="flabel">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>@foreach(['planned','ongoing','completed','cancelled'] as $s)<option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-sm btn-primary-grad px-4">Filter</button><a href="{{ route('admin.training.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a></div>
    </form>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">Programs</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $programs->total() }} programs</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead><tr><th>Program</th><th>Dept</th><th>Trainer</th><th>Mode</th><th>Dates</th><th>Hours</th><th>Mandatory</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($programs as $prog)
                @php $sc=['planned'=>'warning','ongoing'=>'primary','completed'=>'success','cancelled'=>'secondary']; @endphp
                <tr>
                    <td>
                        <a href="{{ route('admin.training.enrollments', $prog) }}" style="font-weight:700;font-size:.88rem;color:#4f46e5;text-decoration:none;">{{ $prog->title }}</a>
                        @if($prog->is_mandatory)<span class="badge bg-danger bg-opacity-15 text-danger ms-1" style="font-size:.68rem;">Mandatory</span>@endif
                    </td>
                    <td style="font-size:.82rem;">{{ $prog->department?->name ?? 'All' }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $prog->trainer ?? '—' }}</td>
                    <td><span class="badge bg-secondary bg-opacity-15 text-secondary" style="font-size:.72rem;">{{ ucfirst($prog->mode) }}</span></td>
                    <td style="font-size:.8rem;color:#6b7280;">{{ $prog->start_date?->format('d M') ?? '—' }} → {{ $prog->end_date?->format('d M Y') ?? '—' }}</td>
                    <td style="font-size:.83rem;text-align:center;">{{ $prog->duration_hours }}h</td>
                    <td style="text-align:center;">@if($prog->is_mandatory)<i class="bi bi-check-circle-fill text-success"></i>@else—@endif</td>
                    <td><span class="spill spill-{{ $sc[$prog->status]??'secondary' }}">{{ ucfirst($prog->status) }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" onclick="openEdit({{ $prog->id }})"><i class="bi bi-pencil"></i></button>
                            <button class="act-btn act-delete" onclick="deleteProg({{ $prog->id }})"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9"><div class="empty-state"><i class="bi bi-mortarboard"></i><p>No training programs yet</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($programs->hasPages())
    <div class="pagination-wrap">{{ $programs->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create/Edit Modal --}}
<div class="modal fade" id="progModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold" id="progModalTitle">New Training Program</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="flabel">Title <span class="req">*</span></label><input type="text" id="progTitle" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4"><label class="flabel">Status</label>
                        <select id="progStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @foreach(['planned','ongoing','completed','cancelled'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="flabel">Department</label>
                        <select id="progDept" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;"><option value="">All Departments</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-4"><label class="flabel">Mode</label>
                        <select id="progMode" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            <option value="offline">Offline</option><option value="online">Online</option><option value="hybrid">Hybrid</option>
                        </select>
                    </div>
                    <div class="col-md-4"><label class="flabel">Duration (hours)</label><input type="number" id="progHours" class="form-control" min="0" step="0.5" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4"><label class="flabel">Trainer</label><input type="text" id="progTrainer" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4"><label class="flabel">Venue</label><input type="text" id="progVenue" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4"><label class="flabel">Start Date</label><input type="date" id="progStart" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4"><label class="flabel">End Date</label><input type="date" id="progEnd" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4 d-flex align-items-end pb-1">
                        <div class="form-check"><input class="form-check-input" type="checkbox" id="progMandatory"><label class="form-check-label" style="font-size:.84rem;">Mandatory</label></div>
                    </div>
                    <div class="col-12"><label class="flabel">Description</label><textarea id="progDesc" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-primary-grad px-4" onclick="saveProg()" id="progSubmitBtn">Create</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;
const allProgs  = @json($programs->items());
let editingProgId = null;

function openCreate() {
    editingProgId = null;
    document.getElementById('progModalTitle').textContent = 'New Training Program';
    document.getElementById('progSubmitBtn').textContent  = 'Create';
    ['progTitle','progTrainer','progVenue','progStart','progEnd','progDesc'].forEach(id => document.getElementById(id).value='');
    document.getElementById('progHours').value = 0;
    document.getElementById('progStatus').value = 'planned';
    document.getElementById('progMode').value   = 'offline';
    document.getElementById('progDept').value   = '';
    document.getElementById('progMandatory').checked = false;
    new bootstrap.Modal(document.getElementById('progModal')).show();
}

function openEdit(id) {
    const p = allProgs.find(x => x.id === id); if(!p) return;
    editingProgId = id;
    document.getElementById('progModalTitle').textContent = 'Edit Program';
    document.getElementById('progSubmitBtn').textContent  = 'Save';
    document.getElementById('progTitle').value   = p.title;
    document.getElementById('progStatus').value  = p.status;
    document.getElementById('progDept').value    = p.department_id || '';
    document.getElementById('progMode').value    = p.mode;
    document.getElementById('progHours').value   = p.duration_hours || 0;
    document.getElementById('progTrainer').value = p.trainer || '';
    document.getElementById('progVenue').value   = p.venue || '';
    document.getElementById('progStart').value   = p.start_date || '';
    document.getElementById('progEnd').value     = p.end_date || '';
    document.getElementById('progDesc').value    = p.description || '';
    document.getElementById('progMandatory').checked = !!p.is_mandatory;
    new bootstrap.Modal(document.getElementById('progModal')).show();
}

function saveProg() {
    const payload = {
        title: document.getElementById('progTitle').value, status: document.getElementById('progStatus').value,
        department_id: document.getElementById('progDept').value, mode: document.getElementById('progMode').value,
        duration_hours: document.getElementById('progHours').value, trainer: document.getElementById('progTrainer').value,
        venue: document.getElementById('progVenue').value, start_date: document.getElementById('progStart').value,
        end_date: document.getElementById('progEnd').value, description: document.getElementById('progDesc').value,
        is_mandatory: document.getElementById('progMandatory').checked,
    };
    const url    = editingProgId ? `/admin/training/${editingProgId}` : '{{ route("admin.training.store") }}';
    const method = editingProgId ? 'PUT' : 'POST';
    fetch(url, { method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:JSON.stringify(payload) })
        .then(r=>r.json()).then(d=>{if(d.success){APP.toast(d.message);setTimeout(()=>location.reload(),800);}else APP.toast(d.message||'Error','error');});
}

function deleteProg(id) {
    APP.confirm('Delete program?','All enrollments will be removed.',()=>{
        fetch(`/admin/training/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}})
            .then(r=>r.json()).then(d=>{if(d.success){APP.toast('Deleted.');setTimeout(()=>location.reload(),800);}});
    });
}
</script>
@endpush
