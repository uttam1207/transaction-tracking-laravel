@extends('layouts.app')
@section('title', 'Employee Assets')
@section('breadcrumb')
    <li class="breadcrumb-item active">Employee Assets</li>
@endsection

@section('content')
<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div><h4>Employee Assets</h4><p>Track company assets issued to employees</p></div>
        <button class="btn btn-sm btn-primary-grad px-4" onclick="openIssueAsset()"><i class="bi bi-plus-lg me-1"></i>Issue Asset</button>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach([['Issued',$stats['issued'],'primary','box-seam'],['Returned',$stats['returned'],'success','box-arrow-in-down'],['Damaged',$stats['damaged'],'warning','exclamation-triangle'],['Lost',$stats['lost'],'danger','slash-circle']] as [$label,$val,$color,$icon])
    <div class="col-md-3">
        <div class="stat-card" style="border-left:4px solid var(--bs-{{ $color }});">
            <div class="stat-icon bg-{{ $color }} bg-opacity-10"><i class="bi bi-{{ $icon }} text-{{ $color }}"></i></div>
            <div><div class="stat-value">{{ $val }}</div><div class="stat-label">{{ $label }}</div></div>
        </div>
    </div>
    @endforeach
</div>

<div class="filter-card mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="flabel">Search</label><input name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Asset name, code, serial…" style="border-radius:8px;"></div>
        <div class="col-md-3"><label class="flabel">Employee</label>
            <select name="employee_id" class="form-select form-select-sm">
                <option value="">All Employees</option>
                @foreach($employees as $e)<option value="{{ $e->id }}" {{ request('employee_id')==$e->id?'selected':'' }}>{{ $e->user?->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="flabel">Category</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach(['laptop','phone','tablet','vehicle','furniture','equipment','access_card','other'] as $c)<option value="{{ $c }}" {{ request('category')===$c?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$c)) }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="flabel">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                @foreach(['issued','returned','damaged','lost'] as $s)<option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-sm btn-primary-grad px-4">Filter</button><a href="{{ route('admin.employee-assets.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a></div>
    </form>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">Assets</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $assets->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead><tr><th>Asset</th><th>Category</th><th>Employee</th><th>Issued</th><th>Return Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($assets as $asset)
                @php $sc = ['issued'=>'primary','returned'=>'success','damaged'=>'warning','lost'=>'danger']; @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:.88rem;">{{ $asset->asset_name }}</div>
                        <span style="background:#f0f4ff;color:#4f46e5;padding:2px 7px;border-radius:6px;font-size:.7rem;font-family:monospace;">{{ $asset->asset_code }}</span>
                    </td>
                    <td style="font-size:.83rem;">{{ ucfirst(str_replace('_',' ',$asset->category)) }}</td>
                    <td style="font-size:.83rem;">{{ $asset->employee?->user?->name ?? '—' }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $asset->issued_date->format('d M Y') }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ $asset->return_date?->format('d M Y') ?? '—' }}</td>
                    <td><span class="spill spill-{{ $sc[$asset->status]??'secondary' }}">{{ ucfirst($asset->status) }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" onclick="openReturnAsset({{ $asset->id }}, '{{ $asset->status }}')"><i class="bi bi-arrow-return-left"></i></button>
                            <button class="act-btn act-delete" onclick="deleteAsset({{ $asset->id }})"><i class="bi bi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="bi bi-box-seam"></i><p>No assets issued</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($assets->hasPages())
    <div class="pagination-wrap">{{ $assets->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Issue Asset Modal --}}
<div class="modal fade" id="assetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold">Issue Asset</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="flabel">Employee <span class="req">*</span></label>
                        <select id="assetEmployee" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            <option value="">— Select —</option>@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->user?->name }} ({{ $e->employee_id }})</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="flabel">Asset Name <span class="req">*</span></label><input type="text" id="assetName" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4"><label class="flabel">Asset Code <span class="req">*</span></label><input type="text" id="assetCode" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;"></div>
                    <div class="col-md-4"><label class="flabel">Category</label>
                        <select id="assetCategory" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @foreach(['laptop','phone','tablet','vehicle','furniture','equipment','access_card','other'] as $c)<option value="{{ $c }}">{{ ucfirst(str_replace('_',' ',$c)) }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="flabel">Serial Number</label><input type="text" id="assetSerial" class="form-control" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4"><label class="flabel">Issued Date <span class="req">*</span></label><input type="date" id="assetIssuedDate" class="form-control" value="{{ date('Y-m-d') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-4"><label class="flabel">Condition</label>
                        <select id="assetCondition" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            <option value="good">Good</option><option value="fair">Fair</option><option value="poor">Poor</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="flabel">Notes</label><textarea id="assetNotes" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-primary-grad px-4" onclick="issueAsset()">Issue Asset</button>
            </div>
        </div>
    </div>
</div>

{{-- Return/Update Modal --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title fw-bold">Update Asset Status</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="returnAssetId">
                <div class="row g-3">
                    <div class="col-12"><label class="flabel">Status</label>
                        <select id="returnStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            <option value="returned">Returned</option><option value="damaged">Damaged</option><option value="lost">Lost</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="flabel">Return Date</label><input type="date" id="returnDate" class="form-control" value="{{ date('Y-m-d') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;"></div>
                    <div class="col-md-6"><label class="flabel">Return Condition</label>
                        <select id="returnCondition" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            <option value="good">Good</option><option value="fair">Fair</option><option value="poor">Poor</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="flabel">Notes</label><textarea id="returnNotes" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;"></textarea></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-primary-grad px-4" onclick="saveReturn()">Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;
function openIssueAsset() { new bootstrap.Modal(document.getElementById('assetModal')).show(); }
function openReturnAsset(id, status) {
    document.getElementById('returnAssetId').value = id;
    document.getElementById('returnStatus').value  = status === 'issued' ? 'returned' : status;
    new bootstrap.Modal(document.getElementById('returnModal')).show();
}
function issueAsset() {
    const p = {
        employee_id: document.getElementById('assetEmployee').value,
        asset_name: document.getElementById('assetName').value,
        asset_code: document.getElementById('assetCode').value,
        category: document.getElementById('assetCategory').value,
        serial_number: document.getElementById('assetSerial').value,
        issued_date: document.getElementById('assetIssuedDate').value,
        condition_on_issue: document.getElementById('assetCondition').value,
        notes: document.getElementById('assetNotes').value,
    };
    fetch('{{ route("admin.employee-assets.store") }}', {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body:JSON.stringify(p),
    }).then(r=>r.json()).then(d=>{if(d.success){APP.toast('Asset issued.');setTimeout(()=>location.reload(),800);}else APP.toast(d.message||'Error','error');});
}
function saveReturn() {
    const id = document.getElementById('returnAssetId').value;
    const p  = {
        status: document.getElementById('returnStatus').value,
        return_date: document.getElementById('returnDate').value,
        condition_on_return: document.getElementById('returnCondition').value,
        notes: document.getElementById('returnNotes').value,
    };
    fetch(`/admin/employee-assets/${id}`, {
        method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body:JSON.stringify(p),
    }).then(r=>r.json()).then(d=>{if(d.success){APP.toast('Asset updated.');setTimeout(()=>location.reload(),800);}else APP.toast(d.message||'Error','error');});
}
function deleteAsset(id) {
    APP.confirm('Delete asset record?','This cannot be undone.',()=>{
        fetch(`/admin/employee-assets/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}})
            .then(r=>r.json()).then(d=>{if(d.success){APP.toast('Deleted.');setTimeout(()=>location.reload(),800);}});
    });
}
</script>
@endpush
