@extends('index')

@section('title', 'Section Assignments — ' . $inspectionType->name . ' — TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Section Assignments</h5></div>
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
                    <i class="feather-save me-2"></i>Save Assignments
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Inspection Type context banner --}}
        <div class="card mb-4 border-0 bg-soft-primary">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-text avatar-md bg-primary text-white rounded-circle fw-bold flex-shrink-0">
                        <i class="feather-check-square"></i>
                    </div>
                    <div>
                        <div class="fw-semibold fs-15">{{ $inspectionType->name }}</div>
                        @if($inspectionType->description)
                        <div class="text-muted fs-12">{{ $inspectionType->description }}</div>
                        @endif
                    </div>
                    <div class="ms-auto fs-13 text-muted">
                        Configure which sections are automatically applied when a run of this type is created.
                        <br>
                        <strong>Global</strong> sections apply to all sample categories.
                        <strong>Category-specific</strong> sections apply only when the sample belongs to that category.
                    </div>
                </div>
            </div>
        </div>

        <form id="sectionsForm"
              action="{{ route('masters.inspection-types.sections.sync', $inspectionType) }}"
              method="POST">
            @csrf

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title mb-0">Assigned Sections</h5>
                        <small class="text-muted">Sections are applied in the order shown (Sort #). Drag to reorder.</small>
                    </div>
                    <button type="button" id="addRowBtn" class="btn btn-sm btn-light-brand">
                        <i class="feather-plus me-1"></i>Add Section
                    </button>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="sectionsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px"></th>
                                    <th>Section</th>
                                    <th style="width:220px">Scope</th>
                                    <th style="width:160px">Category</th>
                                    <th style="width:100px">Sort #</th>
                                    <th style="width:90px" class="text-center">Required</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="sectionsBody">
                                @php
                                    $existing = $inspectionType->sectionDefaults->values();
                                @endphp

                                @forelse($existing as $idx => $def)
                                @php
                                    $isGlobal = is_null($def->category_id);
                                @endphp
                                <tr class="section-row" data-idx="{{ $idx }}">
                                    <td class="drag-handle text-muted" style="cursor:grab">
                                        <i class="feather-menu"></i>
                                    </td>
                                    <td>
                                        <select name="rows[{{ $idx }}][section_id]"
                                                class="form-select form-select-sm section-select"
                                                required>
                                            <option value="">— Select Section —</option>
                                            @foreach($sections as $sec)
                                                <option value="{{ $sec->id }}"
                                                        data-type="{{ $sec->section_type }}"
                                                        @selected($def->inspection_section_id === $sec->id)>
                                                    {{ $sec->name }}
                                                    <span class="text-muted">({{ $sec->section_type }})</span>
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="rows[{{ $idx }}][scope]"
                                                class="form-select form-select-sm scope-select">
                                            <option value="global"    @selected($isGlobal)>Global — All Categories</option>
                                            <option value="category"  @selected(!$isGlobal)>Category Specific</option>
                                        </select>
                                    </td>
                                    <td class="category-cell" @if($isGlobal) style="opacity:.35;pointer-events:none" @endif>
                                        <select name="rows[{{ $idx }}][category_id]"
                                                class="form-select form-select-sm category-select"
                                                @disabled($isGlobal)>
                                            <option value="">— Any Category —</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}"
                                                        @selected($def->category_id === $cat->id)>
                                                    {{ $cat->category_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="rows[{{ $idx }}][sort_order]"
                                               class="form-control form-control-sm sort-input"
                                               value="{{ $def->sort_order }}" min="0" style="width:75px">
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                   name="rows[{{ $idx }}][is_required]" value="1"
                                                   @checked($def->is_required)>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger remove-row"
                                                title="Remove">
                                            <i class="feather-x"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="feather-layers" style="font-size:2rem;opacity:.3"></i>
                                        <p class="mt-2 mb-1">No sections assigned yet.</p>
                                        <small>Click <strong>Add Section</strong> to begin configuring this inspection type.</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($existing->count())
                <div class="card-footer text-muted fs-12">
                    <i class="feather-info me-1"></i>
                    {{ $existing->count() }} section(s) assigned —
                    {{ $existing->whereNull('category_id')->count() }} global,
                    {{ $existing->whereNotNull('category_id')->count() }} category-specific.
                </div>
                @endif
            </div>

        </form>

        {{-- Available Sections reference panel --}}
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="card-title mb-0 text-muted">Available Sections (Reference)</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($sections as $sec)
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
                        $color = $typeColors[$sec->section_type] ?? 'secondary';
                    @endphp
                    <div class="col-md-4 col-lg-3">
                        <div class="d-flex align-items-center gap-2 p-2 border rounded bg-light">
                            <i class="{{ $sec->icon ?? 'feather-layers' }} text-{{ $color }}" style="font-size:14px;flex-shrink:0"></i>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fs-12 fw-semibold text-truncate">{{ $sec->name }}</div>
                                <span class="badge bg-soft-{{ $color }} text-{{ $color }} fs-10">{{ $sec->section_type }}</span>
                            </div>
                            <button type="button"
                                    class="btn btn-xs btn-soft-primary quick-add-btn flex-shrink-0"
                                    data-section-id="{{ $sec->id }}"
                                    data-section-name="{{ $sec->name }}"
                                    title="Quick-add this section">
                                <i class="feather-plus" style="font-size:11px"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Row template (hidden) --}}
<template id="rowTemplate">
    <tr class="section-row" data-idx="__IDX__">
        <td class="drag-handle text-muted" style="cursor:grab">
            <i class="feather-menu"></i>
        </td>
        <td>
            <select name="rows[__IDX__][section_id]"
                    class="form-select form-select-sm section-select"
                    required>
                <option value="">— Select Section —</option>
                @foreach($sections as $sec)
                <option value="{{ $sec->id }}" data-type="{{ $sec->section_type }}">
                    {{ $sec->name }} ({{ $sec->section_type }})
                </option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="rows[__IDX__][scope]" class="form-select form-select-sm scope-select">
                <option value="global">Global — All Categories</option>
                <option value="category">Category Specific</option>
            </select>
        </td>
        <td class="category-cell" style="opacity:.35;pointer-events:none">
            <select name="rows[__IDX__][category_id]"
                    class="form-select form-select-sm category-select" disabled>
                <option value="">— Any Category —</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="rows[__IDX__][sort_order]"
                   class="form-control form-control-sm sort-input"
                   value="__SORT__" min="0" style="width:75px">
        </td>
        <td class="text-center">
            <div class="form-check form-switch d-flex justify-content-center mb-0">
                <input class="form-check-input" type="checkbox" role="switch"
                       name="rows[__IDX__][is_required]" value="1" checked>
            </div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-icon btn-light-danger remove-row" title="Remove">
                <i class="feather-x"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
(function () {
    const body     = document.getElementById('sectionsBody');
    const tmpl     = document.getElementById('rowTemplate');
    const addBtn   = document.getElementById('addRowBtn');
    const emptyRow = document.getElementById('emptyRow');

    let rowIdx = {{ $inspectionType->sectionDefaults->count() }};

    function removeEmptyRow() {
        document.getElementById('emptyRow')?.remove();
    }

    function reindexRows() {
        body.querySelectorAll('tr.section-row').forEach((tr, i) => {
            tr.dataset.idx = i;
            tr.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/rows\[\d+\]/, `rows[${i}]`);
            });
        });
        rowIdx = body.querySelectorAll('tr.section-row').length;
    }

    function attachRowEvents(tr) {
        // Scope toggle
        const scopeSel   = tr.querySelector('.scope-select');
        const catCell    = tr.querySelector('.category-cell');
        const catSel     = tr.querySelector('.category-select');

        scopeSel.addEventListener('change', function () {
            const isGlobal = this.value === 'global';
            catCell.style.opacity         = isGlobal ? '.35' : '1';
            catCell.style.pointerEvents   = isGlobal ? 'none' : 'auto';
            catSel.disabled               = isGlobal;
            if (isGlobal) catSel.value    = '';
        });

        // Remove
        tr.querySelector('.remove-row').addEventListener('click', function () {
            tr.remove();
            reindexRows();
            if (!body.querySelector('tr.section-row')) {
                body.innerHTML = `<tr id="emptyRow"><td colspan="7" class="text-center py-5 text-muted">
                    <i class="feather-layers" style="font-size:2rem;opacity:.3"></i>
                    <p class="mt-2 mb-1">No sections assigned.</p>
                    <small>Click <strong>Add Section</strong> to add one.</small></td></tr>`;
            }
        });
    }

    function addRow(sectionId, sortOrder) {
        removeEmptyRow();
        const sort = sortOrder ?? (body.querySelectorAll('tr.section-row').length + 1) * 10;
        const html = tmpl.innerHTML
            .replaceAll('__IDX__',  rowIdx)
            .replaceAll('__SORT__', sort);

        const tmp = document.createElement('tbody');
        tmp.innerHTML = html;
        const tr = tmp.querySelector('tr');
        body.appendChild(tr);

        if (sectionId) {
            const sel = tr.querySelector('.section-select');
            sel.value = sectionId;
        }

        attachRowEvents(tr);
        rowIdx++;
    }

    // Wire existing rows
    body.querySelectorAll('tr.section-row').forEach(tr => attachRowEvents(tr));

    addBtn.addEventListener('click', () => addRow(null, null));

    // Quick-add buttons from reference panel
    document.querySelectorAll('.quick-add-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            addRow(this.dataset.sectionId, null);
        });
    });

    // Auto-sort on sort input change
    body.addEventListener('change', function (e) {
        if (!e.target.classList.contains('sort-input')) return;
    });
})();
</script>
@endpush
