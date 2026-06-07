@extends('index')

@section('title', 'Section Assignments — ' . $inspectionType->name . ' — TradeSyncERP')

@section('content')
<div class="nxl-content">

    {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Section Assignments</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.inspection-types.index') }}">Inspection Types</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.inspection-types.show', $inspectionType) }}">{{ $inspectionType->name }}</a></li>
                <li class="breadcrumb-item">Sections</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('masters.inspection-types.show', $inspectionType) }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
                <button type="submit" form="sectionsForm" class="btn btn-primary">
                    <i class="feather-save me-2"></i>Save
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- ── Form ────────────────────────────────────────────────────────── --}}
        <form id="sectionsForm"
              action="{{ route('masters.inspection-types.sections.sync', $inspectionType) }}"
              method="POST">
            @csrf

            {{-- Toolbar: count + bulk actions + search --}}
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center flex-wrap gap-2">

                        {{-- Selected count --}}
                        <span class="fw-semibold fs-14 me-1">
                            <span id="selected-count">{{ $inspectionType->sectionDefaults->count() }}</span>
                            of {{ $sections->count() }} sections enabled
                        </span>

                        {{-- Bulk selection --}}
                        <button type="button" id="select-all-btn" class="btn btn-xs btn-outline-success">
                            <i class="feather-check-square me-1"></i>Select All
                        </button>
                        <button type="button" id="clear-all-btn" class="btn btn-xs btn-outline-secondary">
                            <i class="feather-square me-1"></i>Clear All
                        </button>

                        {{-- Search (right-aligned) --}}
                        <div class="ms-auto" style="min-width:220px;max-width:280px">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="feather-search text-muted" style="font-size:13px"></i>
                                </span>
                                <input type="text" id="section-search"
                                       class="form-control border-start-0 bg-light"
                                       placeholder="Search sections…">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Section cards grid --}}
            @php
                $typeLabels = [
                    'task_list'         => 'Task List',
                    'quantity_sampling' => 'Quantity',
                    'cartons'           => 'Cartons',
                    'cover_photo'       => 'Cover',
                    'files_review'      => 'Files',
                    'defects'           => 'Defects',
                    'finish'            => 'Finish',
                    'images'            => 'Images',
                    'workmanship'       => 'Workmanship',
                    'aql'               => 'AQL',
                    'checklist'         => 'Checklist',
                    'container'         => 'Container',
                    'verification'      => 'Verification',
                    'review'            => 'Review',
                ];
                $typeColors = [
                    'images'            => 'purple',
                    'workmanship'       => 'primary',
                    'aql'               => 'success',
                    'checklist'         => 'info',
                    'container'         => 'warning',
                    'verification'      => 'warning',
                    'review'            => 'secondary',
                    'task_list'         => 'primary',
                    'quantity_sampling' => 'info',
                    'cartons'           => 'warning',
                    'cover_photo'       => 'purple',
                    'files_review'      => 'secondary',
                    'defects'           => 'danger',
                    'finish'            => 'success',
                ];
            @endphp

            <div class="row g-3" id="sections-grid">
                @foreach($sections as $loopIdx => $sec)
                @php
                    $assigned  = $inspectionType->sectionDefaults->firstWhere('inspection_section_id', $sec->id);
                    $isChecked = ! is_null($assigned);
                    $isGlobal  = ! $isChecked || is_null($assigned->category_id);
                    $color     = $typeColors[$sec->section_type] ?? 'secondary';
                    $sortVal   = $assigned?->sort_order ?? (($loopIdx + 1) * 10);
                @endphp

                <div class="col-md-6 col-lg-4 col-xl-3 section-col"
                     data-name="{{ strtolower($sec->name) }}"
                     data-type="{{ $sec->section_type }}">

                    <div class="card h-100 section-card border-2 {{ $isChecked ? 'border-primary shadow-sm' : 'border-light' }}"
                         id="card-{{ $sec->id }}"
                         style="{{ $isChecked ? '' : 'opacity:.8' }} transition:all .15s ease; cursor:pointer">

                        <div class="card-body p-3">

                            {{-- Checkbox + section header --}}
                            <div class="d-flex align-items-start gap-2 mb-0">
                                {{-- Big toggle checkbox --}}
                                <div class="flex-shrink-0 mt-1">
                                    <input type="checkbox"
                                           class="form-check-input section-toggle"
                                           id="toggle-{{ $sec->id }}"
                                           data-section-id="{{ $sec->id }}"
                                           style="width:20px;height:20px;cursor:pointer"
                                           {{ $isChecked ? 'checked' : '' }}>
                                </div>

                                {{-- Info --}}
                                <label for="toggle-{{ $sec->id }}"
                                       class="flex-grow-1 mb-0"
                                       style="cursor:pointer">
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <i class="{{ $sec->icon ?? 'feather-layers' }} text-{{ $color }}"
                                           style="font-size:14px;flex-shrink:0"></i>
                                        <span class="fw-semibold fs-13 lh-sm">{{ $sec->name }}</span>
                                        @if($isChecked && $assigned->is_required)
                                            <span class="badge bg-soft-danger text-danger fs-10 ms-auto">Required</span>
                                        @endif
                                    </div>
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }} fs-10">
                                        {{ $typeLabels[$sec->section_type] ?? $sec->section_type }}
                                    </span>
                                </label>
                            </div>

                            {{-- Inline config — visible only when checked --}}
                            <div class="section-config {{ $isChecked ? '' : 'd-none' }}" id="config-{{ $sec->id }}">
                                <hr class="my-2">

                                <div class="row g-2">
                                    {{-- Scope --}}
                                    <div class="col-12">
                                        <select class="form-select form-select-sm scope-select"
                                                id="scope-{{ $sec->id }}"
                                                data-section-id="{{ $sec->id }}">
                                            <option value="global"   {{ $isGlobal   ? 'selected' : '' }}>
                                                Global — All Categories
                                            </option>
                                            <option value="category" {{ !$isGlobal  ? 'selected' : '' }}>
                                                Category Specific
                                            </option>
                                        </select>
                                    </div>

                                    {{-- Category (hidden when global) --}}
                                    <div class="col-12 cat-row" id="cat-row-{{ $sec->id }}"
                                         style="{{ $isGlobal ? 'display:none' : '' }}">
                                        <select class="form-select form-select-sm"
                                                id="cat-{{ $sec->id }}">
                                            <option value="">— Any Category —</option>
                                            @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                    {{ ($assigned?->category_id === $cat->id) ? 'selected' : '' }}>
                                                {{ $cat->category_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Sort + Required --}}
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light"
                                                  style="font-size:11px;padding:4px 7px">#</span>
                                            <input type="number"
                                                   class="form-control form-control-sm"
                                                   id="sort-{{ $sec->id }}"
                                                   value="{{ $sortVal }}"
                                                   min="0"
                                                   placeholder="Sort"
                                                   title="Sort order">
                                        </div>
                                    </div>
                                    <div class="col-6 d-flex align-items-center">
                                        <div class="form-check form-switch mb-0">
                                            <input type="checkbox"
                                                   class="form-check-input req-toggle"
                                                   role="switch"
                                                   id="req-{{ $sec->id }}"
                                                   data-section-id="{{ $sec->id }}"
                                                   {{ $assigned?->is_required ? 'checked' : '' }}>
                                            <label class="form-check-label fs-12"
                                                   for="req-{{ $sec->id }}">Required</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>{{-- /card-body --}}
                    </div>{{-- /card --}}
                </div>{{-- /col --}}
                @endforeach
            </div>{{-- /row --}}

            {{-- No results message (shown by JS) --}}
            <div id="no-results" class="text-center py-5 text-muted d-none">
                <i class="feather-search fs-2 d-block mb-2 opacity-30"></i>
                <p class="mb-0">No sections match your search.</p>
            </div>

        </form>

    </div>{{-- /main-content --}}
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const form        = document.getElementById('sectionsForm');
    const grid        = document.getElementById('sections-grid');
    const countEl     = document.getElementById('selected-count');
    const noResults   = document.getElementById('no-results');
    const searchInput = document.getElementById('section-search');
    const selectAllBtn = document.getElementById('select-all-btn');
    const clearAllBtn  = document.getElementById('clear-all-btn');

    // ── Helpers ────────────────────────────────────────────────────────────────

    function getCheckedCount() {
        return form.querySelectorAll('.section-toggle:checked').length;
    }

    function updateCount() {
        if (countEl) countEl.textContent = getCheckedCount();
    }

    function styleCard(card, checked) {
        if (checked) {
            card.classList.add('border-primary', 'shadow-sm');
            card.classList.remove('border-light');
            card.style.opacity = '1';
        } else {
            card.classList.remove('border-primary', 'shadow-sm');
            card.classList.add('border-light');
            card.style.opacity = '.8';
        }
    }

    // ── Toggle logic ───────────────────────────────────────────────────────────

    form.querySelectorAll('.section-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const id     = this.dataset.sectionId;
            const config = document.getElementById('config-' + id);
            const card   = document.getElementById('card-' + id);

            config?.classList.toggle('d-none', !this.checked);
            styleCard(card, this.checked);
            updateCount();
        });
    });

    // Required toggle → update badge in header
    form.querySelectorAll('.req-toggle').forEach(tog => {
        tog.addEventListener('change', function () {
            const id   = this.dataset.sectionId;
            const card = document.getElementById('card-' + id);
            let badge  = card.querySelector('.badge.bg-soft-danger');
            if (this.checked) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge bg-soft-danger text-danger fs-10 ms-auto';
                    badge.textContent = 'Required';
                    card.querySelector('label .d-flex')?.appendChild(badge);
                }
            } else {
                badge?.remove();
            }
        });
    });

    // ── Scope → category dropdown ──────────────────────────────────────────────

    form.querySelectorAll('.scope-select').forEach(sel => {
        sel.addEventListener('change', function () {
            const id      = this.dataset.sectionId;
            const catRow  = document.getElementById('cat-row-' + id);
            const isGlobal = this.value === 'global';
            if (catRow) {
                catRow.style.display = isGlobal ? 'none' : '';
                if (isGlobal) {
                    catRow.querySelector('select').value = '';
                }
            }
        });
    });

    // ── Bulk selection ─────────────────────────────────────────────────────────

    selectAllBtn?.addEventListener('click', function () {
        grid.querySelectorAll('.section-col').forEach(col => {
            if (col.style.display === 'none') return; // skip filtered-out items
            const toggle = col.querySelector('.section-toggle');
            if (toggle && !toggle.checked) {
                toggle.checked = true;
                toggle.dispatchEvent(new Event('change'));
            }
        });
    });

    clearAllBtn?.addEventListener('click', function () {
        grid.querySelectorAll('.section-col').forEach(col => {
            if (col.style.display === 'none') return; // skip filtered-out items
            const toggle = col.querySelector('.section-toggle');
            if (toggle && toggle.checked) {
                toggle.checked = false;
                toggle.dispatchEvent(new Event('change'));
            }
        });
    });

    // ── Search ─────────────────────────────────────────────────────────────────

    function applyFilters() {
        const q = (searchInput?.value || '').toLowerCase().trim();
        let visCount = 0;

        grid.querySelectorAll('.section-col').forEach(col => {
            const name  = col.dataset.name || '';
            const show  = !q || name.includes(q);
            col.style.display = show ? '' : 'none';
            if (show) visCount++;
        });

        noResults?.classList.toggle('d-none', visCount > 0);
    }

    searchInput?.addEventListener('input', applyFilters);

    // ── Form submit: inject hidden rows ────────────────────────────────────────

    form.addEventListener('submit', function () {
        this.querySelectorAll('.injected-hidden').forEach(el => el.remove());

        const checked = [...this.querySelectorAll('.section-toggle:checked')];

        checked.forEach((toggle, i) => {
            const id    = toggle.dataset.sectionId;
            const scope = document.getElementById('scope-' + id)?.value  || 'global';
            const catId = document.getElementById('cat-' + id)?.value    || '';
            const sort  = document.getElementById('sort-' + id)?.value   || String((i + 1) * 10);
            const req   = document.getElementById('req-' + id)?.checked  ? '1' : '';

            const inject = (name, val) => {
                const inp    = document.createElement('input');
                inp.type     = 'hidden';
                inp.name     = name;
                inp.value    = val;
                inp.className = 'injected-hidden';
                form.appendChild(inp);
            };

            inject(`rows[${i}][section_id]`,  id);
            inject(`rows[${i}][category_id]`, scope === 'category' ? catId : '');
            inject(`rows[${i}][sort_order]`,  sort);
            inject(`rows[${i}][is_required]`, req);
        });
    });

    // ── Init ───────────────────────────────────────────────────────────────────
    updateCount();

})();
</script>
@endpush
