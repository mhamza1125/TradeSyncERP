@extends('index')

@section('title', 'Edit Sample Movement - TradeSyncERP')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
<style>
    .ts-wrapper.form-control { padding: 0; border: 0; }
    .ts-wrapper .ts-control { border-radius: 0.375rem; }
    .insp-table-wrap { max-height: 360px; overflow-y: auto; }
    .sample-group-header td { background: #f8f9fa; font-weight: 600; }
    .variation-qty { width: 80px; }
</style>
@endpush

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Edit Sample Movement</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('movements.index') }}">Sample Movements</a></li>
                <li class="breadcrumb-item"><a href="{{ route('movements.show', $movement) }}">Detail</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('movements.show', $movement) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i>Back
                    </a>
                    <button type="submit" form="editMovementForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i>Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="editMovementForm" action="{{ route('movements.update', $movement) }}" method="POST">
            @csrf @method('PUT')

            <div class="row">

                {{-- ── Main column ─────────────────────────────────────────────── --}}
                <div class="col-xl-8">

                    {{-- ── Sample Items ──────────────────────────────────────────── --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">Samples <span class="text-danger">*</span></h5>
                            <small class="text-muted">Search and add samples.</small>
                        </div>
                        <div class="card-body">
                            @error('items')
                            <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
                            @enderror

                            <div class="d-flex gap-2 mb-3">
                                <div class="flex-grow-1">
                                    <select id="sampleDropdown" placeholder="Search samples…"></select>
                                </div>
                                <button type="button" id="addSampleBtn" class="btn btn-light-brand">
                                    <i class="feather-plus me-1"></i>Add
                                </button>
                            </div>

                            <div id="samplesTableWrap" class="border rounded insp-table-wrap">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:36px">#</th>
                                            <th>Sample</th>
                                            <th style="width:90px">Color</th>
                                            <th style="width:75px">Size</th>
                                            <th class="text-center" style="width:100px">Move Qty</th>
                                            <th style="width:150px">Return Date</th>
                                            <th style="width:120px">Status</th>
                                            <th style="width:40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="samplesTableBody"></tbody>
                                </table>
                            </div>
                            <div id="noSamplesMsg" class="text-muted fs-12 mt-1" style="display:none">No samples added yet.</div>
                        </div>
                    </div>

                    {{-- ── Movement Details ─────────────────────────────────────── --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Movement Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                                    <input type="date" name="issue_date"
                                           class="form-control @error('issue_date') is-invalid @enderror"
                                           value="{{ old('issue_date', $movement->issue_date->toDateString()) }}" required>
                                    @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Expected Return Date</label>
                                    <input type="date" name="expected_return_date"
                                           class="form-control @error('expected_return_date') is-invalid @enderror"
                                           value="{{ old('expected_return_date', $movement->expected_return_date?->toDateString()) }}">
                                    @error('expected_return_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Overall Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach(['Issued', 'Returned', 'Overdue'] as $s)
                                        <option value="{{ $s }}" @selected(old('status', $movement->status) === $s)>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Actual Return Date</label>
                                    <input type="date" name="actual_return_date"
                                           class="form-control @error('actual_return_date') is-invalid @enderror"
                                           value="{{ old('actual_return_date', $movement->actual_return_date?->toDateString()) }}">
                                    @error('actual_return_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" rows="2"
                                              class="form-control @error('remarks') is-invalid @enderror"
                                              placeholder="Optional notes about this movement…">{{ old('remarks', $movement->remarks ?? '') }}</textarea>
                                    @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
                <div class="col-xl-4">

                    {{-- ── Assigned Employees ───────────────────────────────────── --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">Assigned Employees <span class="text-danger">*</span></h5>
                            <small class="text-muted">Add one by one.</small>
                        </div>
                        <div class="card-body">
                            @error('employee_ids')
                            <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
                            @enderror

                            <div class="d-flex gap-2 mb-3">
                                <div class="flex-grow-1">
                                    <select id="employeeDropdown" placeholder="Search employees…"></select>
                                </div>
                                <button type="button" id="addEmployeeBtn" class="btn btn-light-brand">
                                    <i class="feather-plus me-1"></i>Add
                                </button>
                            </div>

                            <div id="employeesTableWrap" class="border rounded insp-table-wrap" style="display:none">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:36px">#</th>
                                            <th>Employee</th>
                                            <th>Designation</th>
                                            <th style="width:40px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="employeesTableBody"></tbody>
                                </table>
                            </div>
                            <div id="noEmployeesMsg" class="text-muted fs-12 mt-1">No employees added yet.</div>
                            <div id="employeeHiddenInputs"></div>
                        </div>
                    </div>

                    {{-- ── Inspection Link ──────────────────────────────────────── --}}
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">Inspection Link</h5>
                            <small class="text-muted">(optional)</small>
                        </div>
                        <div class="card-body">
                            <select name="inspection_run_id" id="inspectionRunSelect"
                                    class="form-select @error('inspection_run_id') is-invalid @enderror">
                                <option value="">— Independent Movement —</option>
                                @foreach($inspectionRuns as $run)
                                <option value="{{ $run->id }}"
                                    @selected(old('inspection_run_id', $movement->inspection_run_id) == $run->id)>
                                    {{ $run->inspection->report_number ?? 'Inspection #'.$run->inspection_id }}
                                    — Run {{ $run->run_number ?? $run->id }}
                                </option>
                                @endforeach
                            </select>
                            @error('inspection_run_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- ── Alert Days ───────────────────────────────────────────── --}}
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="card-title mb-0">Alert</h5></div>
                        <div class="card-body">
                            <label class="form-label">Alert Days Before Return</label>
                            <input type="number" name="alert_days" min="1"
                                   class="form-control @error('alert_days') is-invalid @enderror"
                                   placeholder="e.g. 3" value="{{ old('alert_days', $movement->alert_days) }}">
                            <div class="form-text">Notify this many days before the expected return date.</div>
                            @error('alert_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- ── Status Guide ─────────────────────────────────────────── --}}
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Status Guide</h5></div>
                        <div class="card-body">
                            <div class="alert alert-light border mb-0">
                                <small class="text-muted">
                                    <strong class="text-primary">Issued</strong> — Sample(s) currently out.<br>
                                    <strong class="text-success">Returned</strong> — Returned; set the actual return date.<br>
                                    <strong class="text-danger">Overdue</strong> — Past expected return date, not yet returned.<br><br>
                                    Per-item status overrides the overall movement status in reporting.
                                </small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
@php
    $employeesJson = $employees->keyBy('id')->map(fn ($e) => [
        'name'        => $e->employee_name,
        'designation' => $e->designation ?? null,
    ]);

    // Existing items keyed by sample_id → variation_id → item info
    $existingItemsBySample = [];
    foreach ($movement->items as $item) {
        $sid = $item->sample_id;
        $vid = $item->sample_variation_id;
        if (!isset($existingItemsBySample[$sid])) {
            $existingItemsBySample[$sid] = [];
        }
        $existingItemsBySample[$sid][$vid ?? 'null'] = [
            'qty'                => $item->quantity,
            'actual_return_date' => $item->actual_return_date?->toDateString(),
            'status'             => $item->status,
            'remarks'            => $item->remarks,
        ];
    }
    $preselectedSampleIds   = $movement->items->pluck('sample_id')->unique()->values();
    $preselectedEmployeeIds = $movement->employees->pluck('id')->values();
@endphp
<script>
// ── Server data ───────────────────────────────────────────────────────────────
const SAMPLES_DATA         = @json($samplesJson);
const EMPLOYEES_DATA       = @json($employeesJson);
const INSPECTION_RUNS_DATA = @json($inspectionRunsJson);
const EXISTING_ITEMS       = @json($existingItemsBySample);
const PRESELECTED_SAMPLE_IDS   = @json($preselectedSampleIds);
const PRESELECTED_EMPLOYEE_IDS = @json($preselectedEmployeeIds);

// ── State ─────────────────────────────────────────────────────────────────────
let addedSamples   = new Set();
let addedEmployees = new Set();
let itemCounter    = 0;
let sampleCount    = 0;
let employeeCount  = 0;

// ── TomSelect: Samples ────────────────────────────────────────────────────────
const sampleSelect = new TomSelect('#sampleDropdown', {
    options: Object.entries(SAMPLES_DATA).map(([id, s]) => ({
        value: id,
        text:  `${s.code}${s.product ? ' — ' + s.product : ''}${s.customer ? ' (' + s.customer + ')' : ''}`,
    })),
    valueField:  'value',
    labelField:  'text',
    searchField: ['text'],
    placeholder: 'Search samples…',
    maxOptions:  null,
    create:      false,
});

// ── TomSelect: Employees ──────────────────────────────────────────────────────
const employeeSelect = new TomSelect('#employeeDropdown', {
    options: Object.entries(EMPLOYEES_DATA).map(([id, e]) => ({
        value: id,
        text:  e.name + (e.designation ? ' — ' + e.designation : ''),
    })),
    valueField:  'value',
    labelField:  'text',
    searchField: ['text'],
    placeholder: 'Search employees…',
    maxOptions:  null,
    create:      false,
});

// ── DOM refs ──────────────────────────────────────────────────────────────────
const samplesTableBody    = document.getElementById('samplesTableBody');
const samplesTableWrap    = document.getElementById('samplesTableWrap');
const noSamplesMsg        = document.getElementById('noSamplesMsg');
const employeesTableBody  = document.getElementById('employeesTableBody');
const employeesTableWrap  = document.getElementById('employeesTableWrap');
const noEmployeesMsg      = document.getElementById('noEmployeesMsg');
const employeeHiddenInputs = document.getElementById('employeeHiddenInputs');

// ── Add Sample ────────────────────────────────────────────────────────────────
function addSample(sampleId, existingData) {
    sampleId = String(sampleId);
    if (addedSamples.has(sampleId)) { sampleSelect.clear(); return; }
    const s = SAMPLES_DATA[sampleId];
    if (!s) return;

    addedSamples.add(sampleId);
    sampleCount++;

    const headerRow = document.createElement('tr');
    headerRow.className = 'sample-group-header';
    headerRow.dataset.sampleId = sampleId;
    headerRow.innerHTML = `
        <td class="text-muted"><span class="sample-num">${sampleCount}</span></td>
        <td colspan="7">
            <strong>${escHtml(s.code)}</strong>
            ${s.product ? ' — ' + escHtml(s.product) : ''}
            ${s.customer ? ' <span class="fw-normal text-muted">(' + escHtml(s.customer) + ')</span>' : ''}
        </td>`;
    samplesTableBody.appendChild(headerRow);

    // Remove button in header (separate row tail)
    const removeCell = headerRow.querySelector('td[colspan]');
    removeCell.innerHTML += `<span style="float:right">
        <button type="button" class="btn btn-sm btn-light-danger remove-sample-btn"
                data-sample-id="${sampleId}" title="Remove sample">
            <i class="feather-x"></i>
        </button>
    </span>`;

    if (s.variations && s.variations.length > 0) {
        s.variations.forEach(v => {
            const idx = itemCounter++;
            const existing = existingData && existingData[v.id] ? existingData[v.id] : (existingData && existingData['null'] ? null : null);
            const eItem    = existingData ? (existingData[v.id] || null) : null;
            const varRow   = document.createElement('tr');
            varRow.className = 'sample-var-row';
            varRow.dataset.sampleId = sampleId;
            varRow.innerHTML = `
                <input type="hidden" name="items[${idx}][sample_id]"   value="${sampleId}">
                <input type="hidden" name="items[${idx}][variation_id]" value="${v.id}">
                <td></td>
                <td class="ps-3 fs-12 text-muted">
                    <i class="feather-corner-down-right me-1"></i>${escHtml(v.color ?? '—')}
                </td>
                <td class="fs-12">${escHtml(v.size ?? '—')}</td>
                <td class="text-center">
                    <input type="number" name="items[${idx}][quantity]"
                           class="form-control form-control-sm variation-qty mx-auto"
                           min="0" max="${v.qty}" value="${eItem ? eItem.qty : 0}">
                </td>
                <td>
                    <input type="date" name="items[${idx}][actual_return_date]"
                           class="form-control form-control-sm"
                           value="${eItem && eItem.actual_return_date ? eItem.actual_return_date : ''}">
                </td>
                <td>
                    <select name="items[${idx}][item_status]" class="form-select form-select-sm">
                        <option value="">— Inherit —</option>
                        ${['Issued','Returned','Overdue'].map(st =>
                            `<option value="${st}" ${eItem && eItem.status === st ? 'selected' : ''}>${st}</option>`
                        ).join('')}
                    </select>
                </td>
                <td></td>`;
            samplesTableBody.appendChild(varRow);
        });
    } else {
        const idx    = itemCounter++;
        const eItem  = existingData ? (existingData['null'] || Object.values(existingData)[0] || null) : null;
        const singleRow = document.createElement('tr');
        singleRow.className = 'sample-var-row';
        singleRow.dataset.sampleId = sampleId;
        singleRow.innerHTML = `
            <input type="hidden" name="items[${idx}][sample_id]"   value="${sampleId}">
            <input type="hidden" name="items[${idx}][variation_id]" value="">
            <td></td>
            <td colspan="2" class="ps-3 text-muted fs-12 fst-italic">No variations defined</td>
            <td class="text-center">
                <input type="number" name="items[${idx}][quantity]"
                       class="form-control form-control-sm variation-qty mx-auto"
                       min="0" value="${eItem ? eItem.qty : 1}">
            </td>
            <td>
                <input type="date" name="items[${idx}][actual_return_date]"
                       class="form-control form-control-sm"
                       value="${eItem && eItem.actual_return_date ? eItem.actual_return_date : ''}">
            </td>
            <td>
                <select name="items[${idx}][item_status]" class="form-select form-select-sm">
                    <option value="">— Inherit —</option>
                    ${['Issued','Returned','Overdue'].map(st =>
                        `<option value="${st}" ${eItem && eItem.status === st ? 'selected' : ''}>${st}</option>`
                    ).join('')}
                </select>
            </td>
            <td></td>`;
        samplesTableBody.appendChild(singleRow);
    }

    samplesTableWrap.style.display = '';
    noSamplesMsg.style.display     = 'none';
    sampleSelect.clear();
}

function removeSample(sampleId) {
    sampleId = String(sampleId);
    addedSamples.delete(sampleId);
    samplesTableBody.querySelectorAll(`[data-sample-id="${sampleId}"]`).forEach(r => r.remove());
    renumberSampleGroups();
    if (addedSamples.size === 0) {
        samplesTableWrap.style.display = 'none';
        noSamplesMsg.style.display     = '';
    }
}

function renumberSampleGroups() {
    let n = 0;
    samplesTableBody.querySelectorAll('tr.sample-group-header').forEach(tr => {
        const el = tr.querySelector('.sample-num');
        if (el) el.textContent = ++n;
    });
    sampleCount = n;
}

document.getElementById('addSampleBtn').addEventListener('click', () => {
    const val = sampleSelect.getValue();
    if (val) addSample(val, null);
});

samplesTableBody.addEventListener('click', e => {
    const btn = e.target.closest('.remove-sample-btn');
    if (btn) removeSample(btn.dataset.sampleId);
});

// ── Add Employee ──────────────────────────────────────────────────────────────
function addEmployee(empId) {
    empId = String(empId);
    if (addedEmployees.has(empId)) { employeeSelect.clear(); return; }
    const emp = EMPLOYEES_DATA[empId];
    if (!emp) return;

    addedEmployees.add(empId);
    employeeCount++;

    const tr = document.createElement('tr');
    tr.dataset.employeeId = empId;
    tr.innerHTML = `
        <td class="text-muted"><span class="emp-num">${employeeCount}</span></td>
        <td class="fw-semibold">${escHtml(emp.name)}</td>
        <td class="text-muted fs-12">${escHtml(emp.designation ?? '—')}</td>
        <td>
            <button type="button" class="btn btn-sm btn-light-danger remove-employee-btn"
                    data-employee-id="${empId}" title="Remove">
                <i class="feather-x"></i>
            </button>
        </td>`;
    employeesTableBody.appendChild(tr);

    const input = document.createElement('input');
    input.type  = 'hidden';
    input.name  = 'employee_ids[]';
    input.value = empId;
    input.id    = `emp_hidden_${empId}`;
    employeeHiddenInputs.appendChild(input);

    employeesTableWrap.style.display = '';
    noEmployeesMsg.style.display     = 'none';
    employeeSelect.clear();
}

function removeEmployee(empId) {
    empId = String(empId);
    addedEmployees.delete(empId);
    employeesTableBody.querySelectorAll(`[data-employee-id="${empId}"]`).forEach(r => r.remove());
    const hidden = document.getElementById(`emp_hidden_${empId}`);
    if (hidden) hidden.remove();
    renumberRows(employeesTableBody, '.emp-num');
    if (addedEmployees.size === 0) {
        employeesTableWrap.style.display = 'none';
        noEmployeesMsg.style.display     = '';
    }
    employeeCount = addedEmployees.size;
}

document.getElementById('addEmployeeBtn').addEventListener('click', () => {
    const val = employeeSelect.getValue();
    if (val) addEmployee(val);
});

employeesTableBody.addEventListener('click', e => {
    const btn = e.target.closest('.remove-employee-btn');
    if (btn) removeEmployee(btn.dataset.employeeId);
});

// ── Inspection Run → auto-fill employees ──────────────────────────────────────
const inspRunSelect = document.getElementById('inspectionRunSelect');
if (inspRunSelect) {
    inspRunSelect.addEventListener('change', function () {
        const run = INSPECTION_RUNS_DATA[this.value];
        if (run) (run.employeeIds || []).forEach(id => addEmployee(String(id)));
    });
}

// ── Pre-populate from existing movement data ───────────────────────────────────
PRESELECTED_SAMPLE_IDS.forEach(id => {
    const sid = String(id);
    addSample(sid, EXISTING_ITEMS[sid] || null);
});
PRESELECTED_EMPLOYEE_IDS.forEach(id => addEmployee(String(id)));

// ── Helpers ───────────────────────────────────────────────────────────────────
function renumberRows(tbody, numSelector) {
    let n = 0;
    tbody.querySelectorAll('tr').forEach(tr => {
        const cell = tr.querySelector(numSelector);
        if (cell) cell.textContent = ++n;
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

@endsection
