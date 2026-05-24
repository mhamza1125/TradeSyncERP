{{--
    Shared inspection form partial (create & edit).
    Variables: $employees, $samples (mapped [{id,text}]), $customerOrders (mapped [{id,text}])
    Optional: $inspection (edit only)
--}}
@php
    $savedSampleIds  = isset($inspection) ? $inspection->samples->pluck('id')->toArray() : [];
    $savedOrderIds   = isset($inspection) ? $inspection->customerOrders->pluck('id')->toArray() : [];
    $savedInspectors = isset($inspection) ? $inspection->inspectors->pluck('id')->toArray() : [];

    // Build lookup maps for JS pre-population
    $samplesMap    = collect($samples)->keyBy('id');
    $employeesMap  = $employees->keyBy('id')->map(fn($e) => [
        'name'        => $e->employee_name,
        'designation' => $e->designation ?? null,
    ]);
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
<style>
    .ts-wrapper.form-control { padding: 0; border: 0; }
    .ts-wrapper .ts-control { border-radius: 0.375rem; }
    .insp-table-wrap { max-height: 320px; overflow-y: auto; }
</style>
@endpush

{{-- ── Inspection Details ────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            Inspection Details
            @isset($inspection)
                <span class="text-muted fw-normal fs-14">— {{ $inspection->report_number }}</span>
            @endisset
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-4">
                <label class="form-label">Inspection Date <span class="text-danger">*</span></label>
                <input type="date" name="inspection_date"
                       class="form-control @error('inspection_date') is-invalid @enderror"
                       value="{{ old('inspection_date', isset($inspection) ? $inspection->inspection_date->toDateString() : now()->toDateString()) }}"
                       required>
                @error('inspection_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-4">
                <label class="form-label">Overall Status</label>
                <select name="overall_status" class="form-select @error('overall_status') is-invalid @enderror">
                    @foreach(['Pending','Pass','Fail'] as $s)
                        <option value="{{ $s }}" @selected(old('overall_status', $inspection->overall_status ?? 'Pending') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                @error('overall_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" rows="2" class="form-control"
                          placeholder="Overall inspection notes…">{{ old('remarks', $inspection->remarks ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- ── Samples Being Tested ──────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Samples Being Tested</h5>
        <small class="text-muted">Search and add samples one by one.</small>
    </div>
    <div class="card-body">
        @error('sample_ids')<div class="alert alert-danger py-2 mb-3">{{ $message }}</div>@enderror

        <div class="d-flex gap-2 mb-3">
            <div class="flex-grow-1">
                <select id="sampleAddDropdown" placeholder="Search samples…"></select>
            </div>
            <button type="button" id="addSampleBtn" class="btn btn-light-brand">
                <i class="feather-plus me-1"></i>Add
            </button>
        </div>

        <div id="samplesTableWrap" class="border rounded insp-table-wrap" style="{{ count(old('sample_ids', $savedSampleIds)) ? '' : 'display:none' }}">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Sample Code</th>
                        <th>Details</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="samplesTableBody"></tbody>
            </table>
        </div>
        <div id="noSamplesMsg" class="text-muted fs-12 mt-1"
             style="{{ count(old('sample_ids', $savedSampleIds)) ? 'display:none' : '' }}">
            No samples added yet.
        </div>
        <div id="sampleHiddenInputs"></div>
    </div>
</div>

{{-- ── Linked Customer Orders ────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Linked Customer Orders</h5>
        <small class="text-muted">Search and select customer orders related to this inspection.</small>
    </div>
    <div class="card-body">
        <select id="orderSelect" name="customer_order_ids[]" multiple placeholder="Search orders…"
                class="@error('customer_order_ids') is-invalid @enderror">
            @foreach($customerOrders as $o)
                <option value="{{ $o['id'] }}" @selected(in_array($o['id'], old('customer_order_ids', $savedOrderIds)))>
                    {{ $o['text'] }}
                </option>
            @endforeach
        </select>
        @error('customer_order_ids')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
    </div>
</div>

{{-- ── Assigned Inspectors ──────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Assigned Inspectors</h5>
        <small class="text-muted">Search and add inspectors one by one.</small>
    </div>
    <div class="card-body">
        @error('inspector_ids')<div class="alert alert-danger py-2 mb-3">{{ $message }}</div>@enderror

        <div class="d-flex gap-2 mb-3">
            <div class="flex-grow-1">
                <select id="inspectorAddDropdown" placeholder="Search employees…"></select>
            </div>
            <button type="button" id="addInspectorBtn" class="btn btn-light-brand">
                <i class="feather-plus me-1"></i>Add
            </button>
        </div>

        <div id="inspectorsTableWrap" class="border rounded insp-table-wrap" style="{{ count(old('inspector_ids', $savedInspectors)) ? '' : 'display:none' }}">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Inspector</th>
                        <th>Designation</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="inspectorsTableBody"></tbody>
            </table>
        </div>
        <div id="noInspectorsMsg" class="text-muted fs-12 mt-1"
             style="{{ count(old('inspector_ids', $savedInspectors)) ? 'display:none' : '' }}">
            No inspectors added yet.
        </div>
        <div id="inspectorHiddenInputs"></div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
// ── Data from server ──────────────────────────────────────────────────────────
const SAMPLES_MAP   = @json($samplesMap);
const EMPLOYEES_MAP = @json($employeesMap);

// Pre-saved (edit mode) or old input
const SAVED_SAMPLE_IDS    = @json(array_values(old('sample_ids', $savedSampleIds)));
const SAVED_INSPECTOR_IDS = @json(array_values(old('inspector_ids', $savedInspectors)));

// ── State ─────────────────────────────────────────────────────────────────────
let addedSamples    = new Set();
let addedInspectors = new Set();
let sampleRow       = 0;
let inspectorRow    = 0;

// ── TomSelect: Sample search dropdown ─────────────────────────────────────────
const sampleOptions = Object.entries(SAMPLES_MAP).map(([id, s]) => ({
    value: String(id),
    text:  s.text,
}));

const sampleDropdown = new TomSelect('#sampleAddDropdown', {
    options:     sampleOptions,
    valueField:  'value',
    labelField:  'text',
    searchField: ['text'],
    placeholder: 'Search samples…',
    maxOptions:  null,
    create:      false,
});

// ── TomSelect: Inspector search dropdown ──────────────────────────────────────
const inspectorOptions = Object.entries(EMPLOYEES_MAP).map(([id, e]) => ({
    value: String(id),
    text:  e.name + (e.designation ? ' (' + e.designation + ')' : ''),
}));

const inspectorDropdown = new TomSelect('#inspectorAddDropdown', {
    options:     inspectorOptions,
    valueField:  'value',
    labelField:  'text',
    searchField: ['text'],
    placeholder: 'Search employees…',
    maxOptions:  null,
    create:      false,
});

// ── TomSelect: Customer Orders ────────────────────────────────────────────────
new TomSelect('#orderSelect', {
    plugins: ['remove_button', 'checkbox_options'],
    maxOptions: null,
    placeholder: 'Search orders…',
});

// ── DOM refs ──────────────────────────────────────────────────────────────────
const samplesTableBody   = document.getElementById('samplesTableBody');
const samplesTableWrap   = document.getElementById('samplesTableWrap');
const noSamplesMsg       = document.getElementById('noSamplesMsg');
const sampleHiddenInputs = document.getElementById('sampleHiddenInputs');

const inspectorsTableBody   = document.getElementById('inspectorsTableBody');
const inspectorsTableWrap   = document.getElementById('inspectorsTableWrap');
const noInspectorsMsg       = document.getElementById('noInspectorsMsg');
const inspectorHiddenInputs = document.getElementById('inspectorHiddenInputs');

// ── Add Sample ────────────────────────────────────────────────────────────────
function addSample(id) {
    id = String(id);
    if (addedSamples.has(id)) { sampleDropdown.clear(); return; }
    const s = SAMPLES_MAP[id];
    if (!s) return;

    addedSamples.add(id);
    sampleRow++;

    const parts = s.text.split(' — ');
    const code  = parts[0] ?? s.text;
    const rest  = parts.slice(1).join(' — ');

    const tr = document.createElement('tr');
    tr.dataset.sampleId = id;
    tr.innerHTML = `
        <td class="text-muted">${sampleRow}</td>
        <td class="fw-semibold">${escHtml(code)}</td>
        <td class="text-muted fs-12">${escHtml(rest)}</td>
        <td>
            <button type="button" class="btn btn-sm btn-light-danger remove-sample-btn"
                    data-id="${id}" title="Remove">
                <i class="feather-x"></i>
            </button>
        </td>`;
    samplesTableBody.appendChild(tr);

    const input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'sample_ids[]';
    input.value = id;
    input.id    = `sample_hidden_${id}`;
    sampleHiddenInputs.appendChild(input);

    samplesTableWrap.style.display = '';
    noSamplesMsg.style.display     = 'none';
    sampleDropdown.clear();
}

function removeSample(id) {
    id = String(id);
    addedSamples.delete(id);
    samplesTableBody.querySelectorAll(`[data-sample-id="${id}"]`).forEach(r => r.remove());
    const h = document.getElementById(`sample_hidden_${id}`);
    if (h) h.remove();
    renumberRows(samplesTableBody);
    if (addedSamples.size === 0) {
        samplesTableWrap.style.display = 'none';
        noSamplesMsg.style.display     = '';
    }
    sampleRow = addedSamples.size;
}

// ── Add Inspector ─────────────────────────────────────────────────────────────
function addInspector(id) {
    id = String(id);
    if (addedInspectors.has(id)) { inspectorDropdown.clear(); return; }
    const e = EMPLOYEES_MAP[id];
    if (!e) return;

    addedInspectors.add(id);
    inspectorRow++;

    const tr = document.createElement('tr');
    tr.dataset.inspectorId = id;
    tr.innerHTML = `
        <td class="text-muted">${inspectorRow}</td>
        <td class="fw-semibold">${escHtml(e.name)}</td>
        <td class="text-muted fs-12">${escHtml(e.designation ?? '—')}</td>
        <td>
            <button type="button" class="btn btn-sm btn-light-danger remove-inspector-btn"
                    data-id="${id}" title="Remove">
                <i class="feather-x"></i>
            </button>
        </td>`;
    inspectorsTableBody.appendChild(tr);

    const input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'inspector_ids[]';
    input.value = id;
    input.id    = `inspector_hidden_${id}`;
    inspectorHiddenInputs.appendChild(input);

    inspectorsTableWrap.style.display = '';
    noInspectorsMsg.style.display     = 'none';
    inspectorDropdown.clear();
}

function removeInspector(id) {
    id = String(id);
    addedInspectors.delete(id);
    inspectorsTableBody.querySelectorAll(`[data-inspector-id="${id}"]`).forEach(r => r.remove());
    const h = document.getElementById(`inspector_hidden_${id}`);
    if (h) h.remove();
    renumberRows(inspectorsTableBody);
    if (addedInspectors.size === 0) {
        inspectorsTableWrap.style.display = 'none';
        noInspectorsMsg.style.display     = '';
    }
    inspectorRow = addedInspectors.size;
}

// ── Event Listeners ───────────────────────────────────────────────────────────
document.getElementById('addSampleBtn').addEventListener('click', () => {
    const val = sampleDropdown.getValue();
    if (val) addSample(val);
});
samplesTableBody.addEventListener('click', e => {
    const btn = e.target.closest('.remove-sample-btn');
    if (btn) removeSample(btn.dataset.id);
});

document.getElementById('addInspectorBtn').addEventListener('click', () => {
    const val = inspectorDropdown.getValue();
    if (val) addInspector(val);
});
inspectorsTableBody.addEventListener('click', e => {
    const btn = e.target.closest('.remove-inspector-btn');
    if (btn) removeInspector(btn.dataset.id);
});

// ── Pre-populate saved / old values ──────────────────────────────────────────
SAVED_SAMPLE_IDS.forEach(id => addSample(String(id)));
SAVED_INSPECTOR_IDS.forEach(id => addInspector(String(id)));

// ── Helpers ───────────────────────────────────────────────────────────────────
function renumberRows(tbody) {
    tbody.querySelectorAll('tr').forEach((tr, i) => {
        const numCell = tr.querySelector('td:first-child');
        if (numCell) numCell.textContent = i + 1;
    });
}

function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
@endpush
