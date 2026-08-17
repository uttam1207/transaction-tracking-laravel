@extends('layouts.app')
@section('title', 'Designations')
@section('breadcrumb')
    <li class="breadcrumb-item active">Designations</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Designations</h4>
            <p>Define employee roles and seniority levels</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#desigModal" onclick="openCreate()">
            <i class="bi bi-plus-lg me-1"></i>Add Designation
        </button>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="flabel">Search</label>
            <input type="text" name="search" class="form-control" placeholder="Designation or code…"
                value="{{ request('search') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:220px;">
        </div>
        <div>
            <label class="flabel">Level</label>
            <select name="level" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:160px;">
                <option value="">All Levels</option>
                <option value="1" {{ request('level') == 1 ? 'selected' : '' }}>Executive (L1)</option>
                <option value="2" {{ request('level') == 2 ? 'selected' : '' }}>Senior (L2)</option>
                <option value="3" {{ request('level') == 3 ? 'selected' : '' }}>Mid-Level (L3)</option>
                <option value="4" {{ request('level') == 4 ? 'selected' : '' }}>Junior (L4)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        @if(request()->hasAny(['search','level']))
            <a href="{{ route('admin.designations.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
        @endif
    </form>
</div>

<div class="table-card">
    <div class="card-header">
        <span class="card-title">All Designations</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Designation</th>
                    <th>Code</th>
                    <th>Level</th>
                    <th>Employees</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($designations as $desig)
                <tr>
                    <td style="font-weight:700;font-size:.88rem;color:#111827;">{{ $desig->name }}</td>
                    <td>
                        @if($desig->code)
                        <span style="background:#f0f4ff;color:#4f46e5;padding:3px 8px;border-radius:6px;font-size:.72rem;font-weight:700;font-family:monospace;">{{ $desig->code }}</span>
                        @else<span style="color:#9ca3af;">—</span>@endif
                    </td>
                    <td>
                        @if($desig->level)
                        @php
                            $badges = [
                                1 => ['bg'=>'#7c3aed','color'=>'#fff'],   // Executive  — Purple
                                2 => ['bg'=>'#0ea5e9','color'=>'#fff'],   // Senior     — Sky Blue
                                3 => ['bg'=>'#10b981','color'=>'#fff'],   // Mid-Level  — Emerald
                                4 => ['bg'=>'#64748b','color'=>'#fff'],   // Junior     — Slate
                            ];
                            $labels = [1=>'Executive',2=>'Senior',3=>'Mid-Level',4=>'Junior'];
                            $b = $badges[$desig->level] ?? ['bg'=>'#64748b','color'=>'#fff'];
                            $l = $labels[$desig->level] ?? "L{$desig->level}";
                        @endphp
                        <span style="background:{{ $b['bg'] }};color:{{ $b['color'] }};font-size:.72rem;font-weight:700;border-radius:20px;padding:3px 12px;display:inline-block;letter-spacing:.3px;">{{ $l }}</span>
                        @else<span style="color:#9ca3af;">—</span>@endif
                    </td>
                    <td>
                        <span style="background:#ede9fe;color:#7c3aed;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:700;">
                            {{ $desig->employees_count }}
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:#6b7280;max-width:200px;">{{ Str::limit($desig->description, 55) ?: '—' }}</td>
                    <td><span class="spill {{ $desig->is_active ? 'spill-active' : 'spill-inactive' }}">{{ $desig->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" title="Edit"
                                onclick="openEdit({{ $desig->id }}, '{{ addslashes($desig->name) }}', '{{ $desig->code }}', {{ $desig->level ?? 'null' }}, '{{ addslashes($desig->description ?? '') }}', {{ $desig->is_active ? 1 : 0 }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" title="Delete" onclick="deleteDesig({{ $desig->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-briefcase"></i><p>No designations found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($designations->hasPages())
    <div class="pagination-wrap">{{ $designations->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="desigModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="desigModalTitle">Add Designation</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="desigForm" method="POST">
                @csrf
                <span id="desigMethod"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="flabel">Designation Name <span class="req">*</span></label>
                            <input type="text" name="name" id="dName" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Code</label>
                            <input type="text" name="code" id="dCode" class="form-control" placeholder="CEO" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Seniority Level</label>
                            <select name="level" id="dLevel" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">— Select Level —</option>
                                <option value="1">L1 — Executive</option>
                                <option value="2">L2 — Senior</option>
                                <option value="3">L3 — Mid-Level</option>
                                <option value="4">L4 — Junior</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Status</label>
                            <select name="is_active" id="dStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" id="dDesc" class="form-control" rows="2" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="desigSubmitBtn">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

function openCreate() {
    document.getElementById('desigModalTitle').textContent = 'Add Designation';
    document.getElementById('desigForm').action = '{{ route("admin.designations.store") }}';
    document.getElementById('desigMethod').innerHTML = '';
    document.getElementById('desigSubmitBtn').textContent = 'Create';
    ['dName','dCode','dDesc'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('dLevel').value = '';
    document.getElementById('dStatus').value = '1';
}

function openEdit(id, name, code, level, desc, isActive) {
    document.getElementById('desigModalTitle').textContent = 'Edit Designation';
    document.getElementById('desigForm').action = `/admin/designations/${id}`;
    document.getElementById('desigMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('desigSubmitBtn').textContent = 'Save Changes';
    document.getElementById('dName').value   = name;
    document.getElementById('dCode').value   = code || '';
    document.getElementById('dLevel').value  = level || '';
    document.getElementById('dDesc').value   = desc || '';
    document.getElementById('dStatus').value = isActive;
    new bootstrap.Modal(document.getElementById('desigModal')).show();
}

function deleteDesig(id) {
    APP.confirm('Delete designation?', 'Employees assigned to this designation will lose their FK link.', function() {
        fetch(`/admin/designations/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Designation deleted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Cannot delete.', 'error');
        });
    });
}
</script>
@endpush
