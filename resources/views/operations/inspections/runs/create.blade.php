@extends('index')

@section('title', 'Add Run — ' . $inspection->report_number . ' — TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Add Inspection Run</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.edit', $inspection) }}">{{ $inspection->report_number }}</a></li>
                <li class="breadcrumb-item">Add Run</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
                <button type="submit" form="createRunForm" class="btn btn-primary">
                    <i class="feather-plus me-1"></i>Create Run
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="createRunForm" action="{{ route('inspections.runs.store', $inspection) }}" method="POST">
            @csrf

            {{-- ── Row 1: Run header ─────────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Run Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label fw-semibold">Inspection Type</label>
                            <select id="inspectionTypeSelect"
                                    name="inspection_type_id"
                                    class="form-select @error('inspection_type_id') is-invalid @enderror">
                                <option value="">— Select type —</option>
                                @foreach($inspectionTypes as $t)
                                    <option value="{{ $t->id }}"
                                            data-sections="{{ $t->defaultSections->pluck('id')->toJson() }}"
                                            @selected(old('inspection_type_id') == $t->id)>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('inspection_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Choosing a type auto-enables default sections below.</small>
                        </div>
                        <div class="col-lg-7">
                            <label class="form-label fw-semibold">Remarks</label>
                            <input type="text" name="remarks"
                                   class="form-control @error('remarks') is-invalid @enderror"
                                   value="{{ old('remarks') }}"
                                   placeholder="e.g. Final random check, 2nd re-inspection…">
                            @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Row 2: Section selector ────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0">Inspection Sections</h5>
                        <small class="text-muted">Select the sections that apply to this run. Only checked sections will appear in the form and report.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-xs btn-light-brand" id="selectAll">
                            <i class="feather-check-square me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-xs btn-light-danger" id="clearAll">
                            <i class="feather-square me-1"></i>Clear All
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="sectionGrid">
                        @foreach($allSections as $section)
                        @php
                            $typeColors = [
                                'images'       => 'purple',
                                'workmanship'  => 'primary',
                                'aql'          => 'success',
                                'checklist'    => 'info',
                                'container'    => 'warning',
                                'verification' => 'warning',
                                'review'       => 'secondary',
                            ];
                            $color = $typeColors[$section->section_type] ?? 'secondary';
                        @endphp
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <label class="section-card d-flex align-items-start gap-3 p-3 rounded border cursor-pointer user-select-none"
                                   for="sec_{{ $section->id }}"
                                   data-section-id="{{ $section->id }}">
                                <div class="pt-1">
                                    <input type="checkbox"
                                           class="form-check-input section-checkbox"
                                           name="section_ids[]"
                                           id="sec_{{ $section->id }}"
                                           value="{{ $section->id }}"
                                           {{ in_array($section->id, old('section_ids', [])) ? 'checked' : '' }}>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="{{ $section->icon }} text-{{ $color }}" style="font-size:15px;flex-shrink:0"></i>
                                        <span class="fw-semibold fs-13 text-truncate">{{ $section->name }}</span>
                                    </div>
                                    <small class="text-muted lh-sm d-block">{{ Str::limit($section->description, 70) }}</small>
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }} mt-2 fs-10 text-uppercase">{{ $section->section_type }}</span>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>

                    @error('section_ids')
                    <div class="alert alert-soft-danger mt-3 mb-0">{{ $message }}</div>
                    @enderror
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Map: type_id → [section_id, ...]
    const typeDefaults = {};
    document.querySelectorAll('#inspectionTypeSelect option[data-sections]').forEach(opt => {
        try { typeDefaults[opt.value] = JSON.parse(opt.dataset.sections); } catch {}
    });

    const typeSelect = document.getElementById('inspectionTypeSelect');
    const checkboxes = document.querySelectorAll('.section-checkbox');

    // When type changes, check default sections + uncheck the rest
    typeSelect.addEventListener('change', function () {
        const defaults = typeDefaults[this.value] || [];
        checkboxes.forEach(cb => {
            cb.checked = defaults.includes(parseInt(cb.value));
            updateCardStyle(cb);
        });
    });

    // Card visual toggle
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => updateCardStyle(cb));
        updateCardStyle(cb); // init
    });

    function updateCardStyle(cb) {
        const card = cb.closest('.section-card');
        card.classList.toggle('border-primary', cb.checked);
        card.classList.toggle('bg-soft-primary', cb.checked);
        card.style.opacity = cb.checked ? '1' : '0.65';
    }

    // Select / Clear all
    document.getElementById('selectAll').addEventListener('click', () => {
        checkboxes.forEach(cb => { cb.checked = true; updateCardStyle(cb); });
    });
    document.getElementById('clearAll').addEventListener('click', () => {
        checkboxes.forEach(cb => { cb.checked = false; updateCardStyle(cb); });
    });

    // Trigger on page load if old value is set (validation re-display)
    if (typeSelect.value) {
        // blade already set the checked attribute, just refresh visual styles
        checkboxes.forEach(cb => updateCardStyle(cb));
    }
})();
</script>
@endpush
