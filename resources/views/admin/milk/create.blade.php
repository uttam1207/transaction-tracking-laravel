@extends('layouts.app')
@section('title', 'Add Milk Entry')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.milk.index') }}">Milk</a>
    </li>
    <li class="breadcrumb-item active">Add Entry</li>
@endsection

@push('styles')
<style>
.entry-mode-card {
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:16px 10px;border:2px solid #e5e7eb;border-radius:14px;cursor:pointer;
    text-align:center;transition:all .2s ease;background:#fff;user-select:none;
}
.entry-mode-card:hover { border-color:#059669;background:#f0fdf4; }
.entry-mode-card.selected {
    border-color:#059669;background:#ecfdf5;
    box-shadow:0 0 0 3px rgba(5,150,105,.12);
}
.entry-mode-card .mode-icon { font-size:1.7rem;margin-bottom:8px;color:#6b7280;transition:color .2s; }
.entry-mode-card.selected .mode-icon { color:#059669; }
.entry-mode-card .mode-title { font-weight:700;font-size:.88rem;color:#1f2937; }
.entry-mode-card.selected .mode-title { color:#059669; }
.entry-mode-card .mode-desc { font-size:.71rem;color:#9ca3af;margin-top:3px; }
.shed-animal-badge {
    display:inline-flex;align-items:center;gap:6px;background:#ecfdf5;
    border:1px solid #d1fae5;border-radius:8px;padding:6px 12px;
    font-size:.8rem;font-weight:600;color:#059669;
}
</style>
@endpush

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Daily Milk Entry</h4>
            <p>Record morning or evening shift milk production</p>
        </div>
        <a href="{{ route('admin.milk.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Milk
        </a>
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
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">New Entry</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">Milk Production Record</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">Log morning or evening shift production</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.milk.store') }}" method="POST" id="milkForm">
                    @csrf

                    {{-- ── Entry Mode Selector ─────────────────────────────────────── --}}
                    <div class="mb-4">
                        <h6 class="form-section-label mb-3">Entry Mode</h6>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="entry-mode-card selected" id="card-per_animal" onclick="setMode('per_animal')">
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
                        <input type="hidden" name="entry_type" id="entryTypeInput" value="{{ old('entry_type', 'per_animal') }}">
                    </div>

                    <hr class="my-3 opacity-25">

                    {{-- ── Section A: Entry Details ────────────────────────────────── --}}
                    <div class="mb-4">
                        <h6 class="form-section-label">A — Entry Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                    value="{{ old('date', now()->toDateString()) }}" required>
                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
                                <select name="shift" class="form-select @error('shift') is-invalid @enderror">
                                    <option value="Morning" @selected(old('shift') === 'Morning')>Morning Shift</option>
                                    <option value="Evening" @selected(old('shift') === 'Evening')>Evening Shift</option>
                                </select>
                                @error('shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Per Cattle: animal dropdown --}}
                            <div class="col-12" id="section-per_animal">
                                <label class="form-label fw-semibold">
                                    Animal <span class="text-danger">*</span>
                                </label>
                                <select name="animal_id" id="animalSelect" class="form-select @error('animal_id') is-invalid @enderror">
                                    <option value="">— Select Animal —</option>
                                    @foreach ($animals as $a)
                                        <option value="{{ $a->id }}"
                                            data-shed="{{ $a->shed_number }}"
                                            data-status="{{ $a->pregnancy_status }}"
                                            @selected(old('animal_id') == $a->id)>
                                            {{ $a->tag_number }}{{ $a->name ? ' — '.$a->name : '' }}
                                            &nbsp;({{ $a->shed_number ?? 'No Shed' }}){{ $a->pregnancy_status === 'Dry' ? ' [Dry]' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('animal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="animalShedHint" class="mt-2" style="display:none;">
                                    <span class="shed-animal-badge">
                                        <i class="bi bi-house-door"></i>
                                        <span id="animalShedLabel"></span>
                                    </span>
                                </div>
                            </div>

                            {{-- Per Shed: shed dropdown --}}
                            <div class="col-12" id="section-per_shed" style="display:none;">
                                <label class="form-label fw-semibold">
                                    Shed <span class="text-danger">*</span>
                                </label>
                                @if($sheds->isEmpty())
                                    <div class="alert alert-warning py-2 mb-0">No active milking animals found to build shed list.</div>
                                @else
                                    <select name="shed_number" id="shedSelect" class="form-select @error('shed_number') is-invalid @enderror">
                                        <option value="">— Select Shed —</option>
                                        @foreach($sheds as $shed)
                                            <option value="{{ $shed->shed_number }}"
                                                data-count="{{ $shed->animal_count }}"
                                                @selected(old('shed_number') === $shed->shed_number)>
                                                {{ $shed->shed_number }}
                                                &nbsp;({{ $shed->animal_count }} {{ Str::plural('animal', $shed->animal_count) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shed_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div id="shedAnimalHint" class="mt-2" style="display:none;">
                                        <span class="shed-animal-badge">
                                            <i class="bi bi-people-fill"></i>
                                            <span id="shedAnimalCount"></span> animals in this shed
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Entire Farm: info box --}}
                            <div class="col-12" id="section-entire_farm" style="display:none;">
                                <div style="background:#f0fdf4;border:1px dashed #86efac;border-radius:10px;padding:14px 16px;display:flex;align-items:center;gap:10px;">
                                    <i class="bi bi-geo-alt-fill text-success" style="font-size:1.3rem;"></i>
                                    <div>
                                        <div class="fw-semibold" style="color:#166534;font-size:.88rem;">Entire Farm Entry</div>
                                        <div style="color:#4ade80;font-size:.76rem;margin-top:2px;">
                                            This entry will represent total milk from all sheds combined
                                            ({{ $sheds->sum('animal_count') }} active animals across {{ $sheds->count() }} shed{{ $sheds->count()!==1?'s':'' }}).
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Quantity (Liters) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" min="0.1" name="quantity_liters"
                                    class="form-control @error('quantity_liters') is-invalid @enderror"
                                    placeholder="e.g. 75.5" value="{{ old('quantity_liters') }}" required>
                                @error('quantity_liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    {{-- ── Section B: Quality Parameters ───────────────────────────── --}}
                    <div class="mb-4">
                        <h6 class="form-section-label">B — Quality Parameters</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fat %</label>
                                <input type="number" step="0.01" name="fat_percentage"
                                    class="form-control @error('fat_percentage') is-invalid @enderror"
                                    placeholder="e.g. 7.8" value="{{ old('fat_percentage', 7.8) }}">
                                @error('fat_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SNF %</label>
                                <input type="number" step="0.01" name="snf_percentage"
                                    class="form-control @error('snf_percentage') is-invalid @enderror"
                                    placeholder="e.g. 9.0" value="{{ old('snf_percentage', 9.0) }}">
                                @error('snf_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">CLR Value</label>
                                <input type="number" step="0.01" name="clr_value"
                                    class="form-control @error('clr_value') is-invalid @enderror"
                                    placeholder="e.g. 28.5" value="{{ old('clr_value') }}">
                                @error('clr_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Quality Grade</label>
                                <select name="quality_rating" class="form-select @error('quality_rating') is-invalid @enderror">
                                    <option value="Grade A+" @selected(old('quality_rating') === 'Grade A+')>Grade A+ (Premium Fat &gt;8%)</option>
                                    <option value="Grade A"  @selected(old('quality_rating','Grade A') === 'Grade A')>Grade A (Standard 7–8%)</option>
                                    <option value="Grade B"  @selected(old('quality_rating') === 'Grade B')>Grade B (Sub-standard)</option>
                                </select>
                                @error('quality_rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rejected Liters</label>
                                <input type="number" step="0.1" min="0" name="rejected_liters"
                                    class="form-control @error('rejected_liters') is-invalid @enderror"
                                    value="{{ old('rejected_liters', 0) }}">
                                @error('rejected_liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.milk.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Save Entry
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
    // Toggle required on animal_id
    const animalSel = document.getElementById('animalSelect');
    const shedSel   = document.getElementById('shedSelect');
    if (animalSel) animalSel.required = (mode === 'per_animal');
    if (shedSel)   shedSel.required   = (mode === 'per_shed');
}

// Show shed hint when animal is selected
const animalSel = document.getElementById('animalSelect');
if (animalSel) {
    animalSel.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const shed = opt ? opt.dataset.shed : '';
        const hint = document.getElementById('animalShedHint');
        const lbl  = document.getElementById('animalShedLabel');
        if (shed && this.value) {
            lbl.textContent = shed;
            hint.style.display = '';
        } else {
            hint.style.display = 'none';
        }
    });
}

// Show animal count when shed is selected
const shedSel = document.getElementById('shedSelect');
if (shedSel) {
    shedSel.addEventListener('change', function() {
        const opt   = this.options[this.selectedIndex];
        const count = opt ? opt.dataset.count : '';
        const hint  = document.getElementById('shedAnimalHint');
        const lbl   = document.getElementById('shedAnimalCount');
        if (count && this.value) {
            lbl.textContent = count;
            hint.style.display = '';
        } else {
            hint.style.display = 'none';
        }
    });
}

// Init based on old value (validation redirect)
document.addEventListener('DOMContentLoaded', function() {
    const current = document.getElementById('entryTypeInput').value || 'per_animal';
    setMode(current);
});
</script>
@endpush

@endsection
