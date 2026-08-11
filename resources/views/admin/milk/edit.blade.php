@extends('layouts.app')
@section('title', 'Edit Milk Entry #' . $milkEntry->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.milk.index') }}">Milk</a></li>
    <li class="breadcrumb-item active">Edit #{{ $milkEntry->id }}</li>
@endsection

@push('styles')
<style>
.entry-mode-card {
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:14px 10px;border:2px solid #e5e7eb;border-radius:14px;cursor:pointer;
    text-align:center;transition:all .2s ease;background:#fff;user-select:none;
}
.entry-mode-card:hover { border-color:#059669;background:#f0fdf4; }
.entry-mode-card.selected {
    border-color:#059669;background:#ecfdf5;
    box-shadow:0 0 0 3px rgba(5,150,105,.12);
}
.entry-mode-card .mode-icon { font-size:1.5rem;margin-bottom:6px;color:#6b7280;transition:color .2s; }
.entry-mode-card.selected .mode-icon { color:#059669; }
.entry-mode-card .mode-title { font-weight:700;font-size:.85rem;color:#1f2937; }
.entry-mode-card.selected .mode-title { color:#059669; }
.entry-mode-card .mode-desc { font-size:.7rem;color:#9ca3af;margin-top:2px; }
</style>
@endpush

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Milk Entry</h4>
            <p>#{{ $milkEntry->id }} &mdash; {{ $milkEntry->date->format('d M Y') }} &mdash; {{ $milkEntry->shift }} Shift</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.milk.show', $milkEntry) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.milk.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#059669,#0d9488);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-droplet-fill" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Milk Entry</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $milkEntry->date->format('d M Y') }} &mdash; {{ $milkEntry->shift }} Shift</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ number_format($milkEntry->quantity_liters,1) }} L</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.milk.update', $milkEntry) }}" method="POST">
                    @csrf @method('PUT')

                    {{-- ── Entry Mode Selector ──────────────────────────────────── --}}
                    <div class="mb-4">
                        <h6 class="form-section-label mb-3">Entry Mode</h6>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="entry-mode-card" id="card-per_animal" onclick="setMode('per_animal')">
                                    <i class="bi bi-tag-fill mode-icon"></i>
                                    <div class="mode-title">Per Cattle</div>
                                    <div class="mode-desc">Individual animal</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="entry-mode-card" id="card-per_shed" onclick="setMode('per_shed')">
                                    <i class="bi bi-house-door-fill mode-icon"></i>
                                    <div class="mode-title">Per Shed</div>
                                    <div class="mode-desc">Shed aggregate total</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="entry-mode-card" id="card-entire_farm" onclick="setMode('entire_farm')">
                                    <i class="bi bi-geo-alt-fill mode-icon"></i>
                                    <div class="mode-title">Entire Farm</div>
                                    <div class="mode-desc">All sheds combined</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="entry_type" id="entryTypeInput"
                            value="{{ old('entry_type', $milkEntry->entry_type ?? 'per_animal') }}">
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">A — Entry Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                    value="{{ old('date', $milkEntry->date->toDateString()) }}" required>
                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
                                <select name="shift" class="form-select @error('shift') is-invalid @enderror" required>
                                    <option value="Morning" @selected(old('shift',$milkEntry->shift)==='Morning')>Morning Shift</option>
                                    <option value="Evening" @selected(old('shift',$milkEntry->shift)==='Evening')>Evening Shift</option>
                                </select>
                                @error('shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Per Cattle --}}
                            <div class="col-12" id="section-per_animal">
                                <label class="form-label fw-semibold">Animal <span class="text-danger">*</span></label>
                                <select name="animal_id" id="animalSelect" class="form-select @error('animal_id') is-invalid @enderror">
                                    <option value="">— Select Animal —</option>
                                    @foreach($animals as $a)
                                        <option value="{{ $a->id }}"
                                            data-shed="{{ $a->shed_number }}"
                                            data-status="{{ $a->pregnancy_status }}"
                                            @selected(old('animal_id',$milkEntry->animal_id)==$a->id)>
                                            {{ $a->tag_number }}{{ $a->name ? ' — '.$a->name : '' }}
                                            &nbsp;({{ $a->shed_number ?? 'No Shed' }}){{ $a->pregnancy_status === 'Dry' ? ' [Dry]' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('animal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Per Shed --}}
                            <div class="col-12" id="section-per_shed" style="display:none;">
                                <label class="form-label fw-semibold">Shed <span class="text-danger">*</span></label>
                                @if($sheds->isEmpty())
                                    <div class="alert alert-warning py-2 mb-0">No active milking animals found.</div>
                                @else
                                    <select name="shed_number" id="shedSelect" class="form-select @error('shed_number') is-invalid @enderror">
                                        <option value="">— Select Shed —</option>
                                        @foreach($sheds as $shed)
                                            <option value="{{ $shed->shed_number }}"
                                                @selected(old('shed_number', $milkEntry->shed_number) === $shed->shed_number)>
                                                {{ $shed->shed_number }}
                                                &nbsp;({{ $shed->animal_count }} {{ Str::plural('animal', $shed->animal_count) }})
                                            </option>
                                        @endforeach
                                        {{-- If saved shed_number not in active sheds, still show it --}}
                                        @if($milkEntry->shed_number && !$sheds->pluck('shed_number')->contains($milkEntry->shed_number))
                                            <option value="{{ $milkEntry->shed_number }}" selected>
                                                {{ $milkEntry->shed_number }} (archived)
                                            </option>
                                        @endif
                                    </select>
                                    @error('shed_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @endif
                            </div>

                            {{-- Entire Farm --}}
                            <div class="col-12" id="section-entire_farm" style="display:none;">
                                <div style="background:#f0fdf4;border:1px dashed #86efac;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:10px;">
                                    <i class="bi bi-geo-alt-fill text-success" style="font-size:1.3rem;"></i>
                                    <div>
                                        <div class="fw-semibold" style="color:#166534;font-size:.88rem;">Entire Farm Entry</div>
                                        <div style="color:#4ade80;font-size:.76rem;margin-top:2px;">Represents total milk from all sheds combined.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Quantity (Liters) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" min="0.1" name="quantity_liters"
                                    class="form-control @error('quantity_liters') is-invalid @enderror"
                                    value="{{ old('quantity_liters', $milkEntry->quantity_liters) }}" required>
                                @error('quantity_liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rejected Liters</label>
                                <input type="number" step="0.1" min="0" name="rejected_liters"
                                    class="form-control @error('rejected_liters') is-invalid @enderror"
                                    value="{{ old('rejected_liters', $milkEntry->rejected_liters) }}">
                                @error('rejected_liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">B — Quality Parameters</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Fat <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="1" max="15" name="fat_percentage"
                                    class="form-control @error('fat_percentage') is-invalid @enderror"
                                    value="{{ old('fat_percentage', $milkEntry->fat_percentage) }}">
                                @error('fat_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">SNF <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="1" max="15" name="snf_percentage"
                                    class="form-control @error('snf_percentage') is-invalid @enderror"
                                    value="{{ old('snf_percentage', $milkEntry->snf_percentage) }}">
                                @error('snf_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">CLR Value</label>
                                <input type="number" step="0.01" min="0" max="50" name="clr_value"
                                    class="form-control @error('clr_value') is-invalid @enderror"
                                    value="{{ old('clr_value', $milkEntry->clr_value) }}">
                                @error('clr_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Quality Grade</label>
                                <select name="quality_rating" class="form-select @error('quality_rating') is-invalid @enderror">
                                    @foreach(['Grade A+','Grade A','Grade B'] as $g)
                                        <option value="{{ $g }}" @selected(old('quality_rating',$milkEntry->quality_rating)===$g)>{{ $g }}</option>
                                    @endforeach
                                </select>
                                @error('quality_rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.milk.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Update Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const modes = ['per_animal','per_shed','entire_farm'];

function setMode(mode) {
    document.getElementById('entryTypeInput').value = mode;
    modes.forEach(m => {
        document.getElementById('card-' + m).classList.toggle('selected', m === mode);
        const s = document.getElementById('section-' + m);
        if (s) s.style.display = (m === mode) ? '' : 'none';
    });
    const animalSel = document.getElementById('animalSelect');
    const shedSel   = document.getElementById('shedSelect');
    if (animalSel) animalSel.required = (mode === 'per_animal');
    if (shedSel)   shedSel.required   = (mode === 'per_shed');
}

document.addEventListener('DOMContentLoaded', function() {
    const current = document.getElementById('entryTypeInput').value || 'per_animal';
    setMode(current);
});
</script>
@endpush

@endsection
