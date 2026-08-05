@extends('layouts.app')
@section('title', 'Animal — ' . $animal->tag_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.animals.index') }}">Animals</a></li>
    <li class="breadcrumb-item active">{{ $animal->tag_number }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $animal->tag_number }}{{ $animal->name ? ' — '.$animal->name : '' }}</h4>
            <p>{{ $animal->breed }} &mdash; Shed {{ $animal->shed_number }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.animals.edit', $animal) }}" class="btn btn-sm btn-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.animals.destroy', $animal) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger px-4" onclick="return confirm('Remove this animal from the register?')">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.animals.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Left: Animal Profile --}}
    <div class="col-lg-5">
        <div class="card-glass overflow-hidden mb-4">
            @php
                $breedLower = strtolower($animal->breed ?? '');
                $avatarGrad = str_contains($breedLower,'buffalo') ? 'linear-gradient(135deg,#2563eb,#4f46e5)'
                    : (str_contains($breedLower,'gir')||str_contains($breedLower,'cow')||str_contains($breedLower,'holstein')
                        ? 'linear-gradient(135deg,#059669,#0d9488)' : 'linear-gradient(135deg,#d97706,#ea580c)');
            @endphp
            <div style="background:{{ $avatarGrad }};padding:26px 30px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:120px;height:120px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:60px;height:60px;background:rgba(255,255,255,.2);border-radius:15px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.4rem;font-weight:800;color:#fff;letter-spacing:-.02em;">
                        {{ strtoupper(substr($animal->tag_number,-2)) }}
                    </div>
                    <div>
                        <div style="font-size:1.15rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $animal->tag_number }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:.82rem;margin-top:2px;">{{ $animal->name ?? 'Unnamed' }}</div>
                    </div>
                    <div class="ms-auto d-flex flex-column gap-1 align-items-end">
                        @php
                            $hColor = $animal->health_status==='Healthy' ? 'spill-success' : ($animal->health_status==='Sick' ? 'spill-danger' : 'spill-warning');
                        @endphp
                        <span class="spill {{ $hColor }}" style="font-size:.75rem;">{{ $animal->health_status }}</span>
                        <span class="spill {{ $animal->status==='Active' ? 'spill-success' : 'spill-secondary' }}" style="font-size:.75rem;">{{ $animal->status }}</span>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-tag me-1"></i>Tag No.</div>
                            <div class="fw-bold" style="color:var(--primary);font-size:.9rem;">{{ $animal->tag_number }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-layers me-1"></i>Breed</div>
                            <div style="color:#374151;font-size:.88rem;">{{ $animal->breed }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-calendar me-1"></i>Date of Birth</div>
                            <div style="color:#374151;font-size:.88rem;">{{ $animal->dob ? $animal->dob->format('d M Y') : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-123 me-1"></i>Lactation No.</div>
                            <div class="fw-bold" style="color:#1f2937;font-size:.95rem;">{{ $animal->lactation_number }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-speedometer me-1"></i>Weight</div>
                            <div style="color:#374151;font-size:.88rem;">{{ $animal->current_weight ? $animal->current_weight.' kg' : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-house me-1"></i>Shed</div>
                            <div style="color:#374151;font-size:.88rem;">{{ $animal->shed_number }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-heart me-1"></i>Pregnancy</div>
                            <div><span class="spill spill-info" style="font-size:.75rem;">{{ $animal->pregnancy_status }}</span></div>
                        </div>
                    </div>
                    {{-- Animal Source --}}
                    <div class="col-6">
                        <div style="background:{{ $animal->born_in_farm ? '#f0fdf4' : '#eff6ff' }};border-radius:10px;padding:12px 14px;border:1px solid {{ $animal->born_in_farm ? '#bbf7d0' : '#bfdbfe' }};">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;">
                                <i class="bi bi-{{ $animal->born_in_farm ? 'house-heart-fill' : 'cart-check-fill' }} me-1"></i>Animal Source
                            </div>
                            <div class="fw-bold" style="color:{{ $animal->born_in_farm ? '#16a34a' : '#2563eb' }};font-size:.88rem;">
                                {{ $animal->born_in_farm ? 'Born in Own Farm' : 'Purchased from Outside' }}
                            </div>
                        </div>
                    </div>
                    @if($animal->purchase_date || $animal->purchase_cost)
                    <div class="col-6">
                        <div style="background:#ecfdf5;border-radius:10px;padding:12px 14px;border:1px solid #d1fae5;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-currency-rupee me-1"></i>Purchase Cost</div>
                            <div class="fw-bold" style="color:#059669;font-size:.9rem;">{{ $animal->purchase_cost ? '₹'.number_format($animal->purchase_cost,0) : '—' }}</div>
                        </div>
                    </div>
                    @endif
                    @if($animal->purchase_from)
                    <div class="col-12">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;"><i class="bi bi-shop me-1"></i>Purchase From</div>
                            <div style="color:#374151;font-size:.88rem;">{{ $animal->purchase_from }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Actions & Health --}}
    <div class="col-lg-7">
        {{-- Log Action --}}
        <div class="card-glass mb-4">
            <div class="d-flex align-items-center justify-content-between gap-3 px-4 py-3 border-bottom flex-wrap">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#059669,#16a34a);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-plus-circle" style="color:#fff;font-size:.9rem;"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Log Action</h6>
                </div>
                <a href="{{ route('admin.action-types.index') }}" target="_blank"
                    class="btn btn-sm btn-outline-secondary px-3" style="font-size:.75rem;">
                    <i class="bi bi-list-check me-1"></i>Manage Action Types
                </a>
            </div>
            <div class="p-4">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
                        <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.animals.actions.store', $animal) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Row 1: action type · date · cost --}}
                    <div class="row g-2 mb-2">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold" style="font-size:.75rem;margin-bottom:3px;">Action Type <span class="text-danger">*</span></label>
                            <select name="action_type" class="form-select form-select-sm @error('action_type') is-invalid @enderror" required>
                                <option value="">— Select Action Type —</option>
                                @foreach($actionTypes as $at)
                                    <option value="{{ $at->name }}" @selected(old('action_type') === $at->name)>
                                        {{ $at->name }}
                                        @if($at->is_system) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('action_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size:.75rem;margin-bottom:3px;">Action Date <span class="text-danger">*</span></label>
                            <input type="date" name="action_date"
                                class="form-control form-control-sm @error('action_date') is-invalid @enderror"
                                value="{{ old('action_date', now()->toDateString()) }}" required>
                            @error('action_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold" style="font-size:.75rem;margin-bottom:3px;">Cost (&#8377;) <span class="text-danger">*</span></label>
                            <input type="number" name="cost" step="0.01" min="0"
                                class="form-control form-control-sm @error('cost') is-invalid @enderror"
                                placeholder="0.00" value="{{ old('cost', 0) }}" required>
                            @error('cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Row 2: notes · upload --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.75rem;margin-bottom:3px;">Notes</label>
                            <input type="text" name="notes"
                                class="form-control form-control-sm @error('notes') is-invalid @enderror"
                                placeholder="Brief notes (optional)" value="{{ old('notes') }}">
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.75rem;margin-bottom:3px;">
                                <i class="bi bi-paperclip me-1"></i>Attach Document / Photo
                            </label>
                            <input type="file" name="document" id="docUpload"
                                class="form-control form-control-sm @error('document') is-invalid @enderror"
                                accept=".pdf,.jpg,.jpeg,.png,.gif,.webp"
                                onchange="previewFile(this)">
                            @error('document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="filePreview" class="mt-1" style="display:none;font-size:.72rem;color:#6b7280;"></div>
                        </div>
                    </div>

                    <button class="btn btn-primary-grad btn-sm px-4">
                        <i class="bi bi-save me-1"></i>Save Action
                    </button>
                    <span style="font-size:.7rem;color:#9ca3af;margin-left:10px;">Max file size: 5 MB &bull; PDF, JPG, PNG supported</span>
                </form>
            </div>
        </div>

        {{-- Action History --}}
        <div class="card-glass mb-4">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#4f46e5);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-clock-history" style="color:#fff;font-size:.9rem;"></i>
                    </div>
                    <h6 class="mb-0 fw-bold">Action History</h6>
                </div>
                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $animal->actions->count() }} records</span>
            </div>
            <div class="px-4 pb-2">
                @forelse($animal->actions->sortByDesc('action_date') as $action)
                    @php
                        $ext = $action->document_path ? strtolower(pathinfo($action->document_path, PATHINFO_EXTENSION)) : null;
                        $docIcon = match(true) {
                            $ext === 'pdf'                              => ['bi-file-pdf-fill', '#dc2626'],
                            in_array($ext, ['jpg','jpeg','png','gif','webp']) => ['bi-image-fill', '#2563eb'],
                            $ext !== null                               => ['bi-file-earmark-fill', '#6b7280'],
                            default                                     => [null, null],
                        };
                    @endphp
                    <div class="py-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <span class="spill spill-primary me-2" style="font-size:.74rem;">{{ $action->action_type }}</span>
                                <span style="font-size:.83rem;color:#374151;">{{ $action->action_date->format('d M Y') }}</span>
                                @if($action->notes)
                                    <span style="font-size:.76rem;color:#9ca3af;"> &mdash; {{ $action->notes }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                @if($action->cost)
                                    <span class="fw-bold" style="color:#059669;font-size:.84rem;">&#8377;{{ number_format($action->cost, 0) }}</span>
                                @endif
                                <form action="{{ route('admin.animals.actions.destroy', [$animal, $action]) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this action record?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="act-btn act-delete" title="Delete action" style="width:26px;height:26px;font-size:.7rem;">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        {{-- Document attachment link --}}
                        @if($action->document_path)
                            <div class="mt-1">
                                <a href="{{ Storage::url($action->document_path) }}" target="_blank"
                                    style="font-size:.75rem;color:{{ $docIcon[1] }};text-decoration:none;display:inline-flex;align-items:center;gap:4px;padding:3px 8px;background:{{ $ext === 'pdf' ? '#fef2f2' : '#eff6ff' }};border-radius:6px;border:1px solid {{ $ext === 'pdf' ? '#fecaca' : '#bfdbfe' }};">
                                    <i class="bi {{ $docIcon[0] }}"></i>
                                    {{ basename($action->document_path) }}
                                    <i class="bi bi-box-arrow-up-right" style="font-size:.6rem;"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-4 text-center" style="color:#9ca3af;font-size:.85rem;">
                        <i class="bi bi-journal-x d-block mb-2" style="font-size:1.8rem;"></i>No actions logged yet
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Health Records --}}
        <div class="card-glass">
            <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#dc2626,#9f1239);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="bi bi-heart-pulse" style="color:#fff;font-size:.9rem;"></i>
                </div>
                <h6 class="mb-0 fw-bold">Recent Health Records</h6>
            </div>
            <div class="px-4 pb-2">
                @forelse($animal->healthRecords->sortByDesc('date')->take(6) as $h)
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div>
                            @php $htColor = match($h->record_type) { 'Vaccination'=>'spill-success','Treatment'=>'spill-danger','Deworming'=>'spill-info',default=>'spill-warning' }; @endphp
                            <span class="spill {{ $htColor }} me-2" style="font-size:.74rem;">{{ $h->record_type }}</span>
                            <span style="font-size:.83rem;color:#374151;">{{ $h->date->format('d M Y') }}</span>
                            @if($h->disease_symptoms)<span style="font-size:.76rem;color:#9ca3af;"> &mdash; {{ Str::limit($h->disease_symptoms,30) }}</span>@endif
                        </div>
                        @if($h->cost)
                            <span class="fw-bold" style="color:#dc2626;font-size:.84rem;">&#8377;{{ number_format($h->cost,0) }}</span>
                        @endif
                    </div>
                @empty
                    <div class="py-4 text-center" style="color:#9ca3af;font-size:.85rem;">
                        <i class="bi bi-heart d-block mb-2" style="font-size:1.8rem;"></i>No health records
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function previewFile(input) {
    const preview = document.getElementById('filePreview');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const sizeMB = (file.size / 1048576).toFixed(2);
        const ext = file.name.split('.').pop().toLowerCase();
        const icon = ext === 'pdf' ? '📄' : ['jpg','jpeg','png','gif','webp'].includes(ext) ? '🖼️' : '📎';
        preview.style.display = 'block';
        preview.innerHTML = icon + ' <strong>' + file.name + '</strong> (' + sizeMB + ' MB)';
    } else {
        preview.style.display = 'none';
    }
}
</script>
@endpush
