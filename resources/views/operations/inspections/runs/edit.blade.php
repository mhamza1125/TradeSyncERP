@extends('index')

@section('title', 'Run #' . $run->run_number . ' — ' . $inspection->report_number . ' — TradeSyncERP')

@section('content')
<div class="nxl-content">

    {{-- ── Page header ──────────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Run #{{ $run->run_number }}
                    @php
                        $verdictColors = ['Pass' => 'success', 'Fail' => 'danger', 'Conditional' => 'warning', 'Pending' => 'secondary'];
                    @endphp
                    <span class="badge bg-soft-{{ $verdictColors[$run->verdict] ?? 'secondary' }} text-{{ $verdictColors[$run->verdict] ?? 'secondary' }} fs-11 ms-2">
                        {{ $run->verdict }}
                    </span>
                </h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.edit', $inspection) }}">{{ $inspection->report_number }}</a></li>
                <li class="breadcrumb-item">Run #{{ $run->run_number }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
                <button type="submit" form="runForm" class="btn btn-primary">
                    <i class="feather-save me-2"></i>Save All
                </button>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="runForm"
              action="{{ route('inspections.runs.update', [$inspection, $run]) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- ── Run header card ────────────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header"><h5 class="card-title mb-0">Run Details</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Inspection Type</label>
                            <select name="inspection_type_id" class="form-select">
                                <option value="">— None —</option>
                                @foreach($inspectionTypes as $t)
                                    <option value="{{ $t->id }}" @selected(old('inspection_type_id', $run->inspection_type_id) == $t->id)>{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Overall Verdict</label>
                            <select name="verdict" class="form-select">
                                @foreach(['Pending','Pass','Fail','Conditional'] as $v)
                                    <option value="{{ $v }}" @selected(old('verdict', $run->verdict) === $v)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold">Remarks</label>
                            <input type="text" name="remarks"
                                   class="form-control"
                                   value="{{ old('remarks', $run->remarks) }}"
                                   placeholder="Optional run note…">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Sections accordion ──────────────────────────────────────────── --}}
            @if($run->runSections->isEmpty())
            <div class="card mb-4">
                <div class="card-body text-center py-5 text-muted">
                    <i class="feather-layers" style="font-size:2rem;opacity:.3"></i>
                    <p class="mt-2 mb-0">No sections were enabled for this run.</p>
                    <small>Delete this run and recreate it to select sections.</small>
                </div>
            </div>
            @else
            <div class="accordion" id="sectionsAccordion">
                @foreach($run->runSections as $loopIdx => $runSection)
                @php
                    $sec     = $runSection->section;
                    $secSlug = $sec->slug;
                    $accId   = 'sec-' . $runSection->id;

                    $statusColors = ['pending' => 'secondary', 'complete' => 'success', 'na' => 'light text-muted'];
                    $statusLabels = ['pending' => 'Pending', 'complete' => 'Complete', 'na' => 'N/A'];

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

                    // Determine if this section should start open
                    $startOpen = $loopIdx === 0 || $runSection->status === 'pending';
                @endphp

                <div class="card mb-3 border-0 shadow-sm" id="card-{{ $accId }}">
                    {{-- Section header / toggle --}}
                    <div class="card-header p-0 border-0">
                        <button class="d-flex align-items-center gap-3 w-100 p-3 bg-transparent border-0 text-start"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#body-{{ $accId }}"
                                aria-expanded="{{ $startOpen ? 'true' : 'false' }}"
                                aria-controls="body-{{ $accId }}">
                            <i class="{{ $sec->icon }} text-{{ $color }}" style="font-size:18px;flex-shrink:0"></i>
                            <div class="flex-grow-1 text-start">
                                <div class="fw-semibold fs-14">{{ $sec->name }}</div>
                                <small class="text-muted fw-normal">{{ $sec->description }}</small>
                            </div>

                            {{-- Status badge --}}
                            <span class="badge bg-soft-{{ $statusColors[$runSection->status] ?? 'secondary' }} text-{{ explode(' ', $statusColors[$runSection->status] ?? 'secondary')[0] }} fs-11 flex-shrink-0">
                                {{ $statusLabels[$runSection->status] ?? 'Pending' }}
                            </span>

                            <i class="feather-chevron-down text-muted ms-2" style="flex-shrink:0"></i>
                        </button>
                    </div>

                    <div id="body-{{ $accId }}"
                         class="collapse {{ $startOpen ? 'show' : '' }}"
                         data-bs-parent="">
                        <div class="card-body border-top">

                            {{-- Section status + notes (all sections share this) --}}
                            <div class="row g-3 mb-4">
                                <div class="col-auto">
                                    <label class="form-label fw-semibold">Section Status</label>
                                    <select name="sections[{{ $runSection->id }}][status]" class="form-select form-select-sm" style="width:140px">
                                        <option value="pending"  @selected($runSection->status === 'pending')>Pending</option>
                                        <option value="complete" @selected($runSection->status === 'complete')>Complete</option>
                                        <option value="na"       @selected($runSection->status === 'na')>N/A</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label fw-semibold">Section Notes</label>
                                    <input type="text"
                                           name="sections[{{ $runSection->id }}][notes]"
                                           class="form-control form-control-sm"
                                           value="{{ old("sections.{$runSection->id}.notes", $runSection->notes) }}"
                                           placeholder="Optional notes for this section…">
                                </div>
                            </div>

                            {{-- ══ Delegate to section-type partial ════════════════ --}}
                            @switch($sec->section_type)

                                @case('workmanship')
                                    @include('operations.inspections.runs.sections._workmanship', [
                                        'runSection'  => $runSection,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                        'resultsMap'  => $resultsMap,
                                        'defects'     => $defects,
                                    ])
                                @break

                                @case('aql')
                                    @include('operations.inspections.runs.sections._aql_sampling', [
                                        'runSection' => $runSection,
                                        'aql'        => $run->aql,
                                        'aqlJsData'  => $aqlJsData,
                                    ])
                                @break

                                @case('images')
                                    @include('operations.inspections.runs.sections._product_screening', [
                                        'runSection' => $runSection,
                                    ])
                                @break

                                @case('container')
                                    @include('operations.inspections.runs.sections._container_details', [
                                        'runSection' => $runSection,
                                    ])
                                @break

                                @case('verification')
                                    @include('operations.inspections.runs.sections._verification', [
                                        'runSection' => $runSection,
                                    ])
                                @break

                                @case('review')
                                    @if($secSlug === 'corrective_action')
                                        @include('operations.inspections.runs.sections._corrective_action', ['runSection' => $runSection])
                                    @else
                                        @include('operations.inspections.runs.sections._final_review', ['runSection' => $runSection])
                                    @endif
                                @break

                                @default {{-- checklist --}}
                                    @include('operations.inspections.runs.sections._checklist', [
                                        'runSection' => $runSection,
                                        'defects'    => $defects,
                                    ])
                                @break

                            @endswitch

                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ── Result-row status → row highlight + defect column visibility ──────────
    function applyResultStatus(sel) {
        const row    = sel.closest('tr');
        const rowIdx = sel.dataset.row;
        const defect   = document.querySelector('.defect-select-' + rowIdx);
        const severity = document.querySelector('.severity-select-' + rowIdx);
        const status = sel.value;

        row.classList.remove('table-success', 'table-warning', 'table-danger');
        if      (status === 'Pass')     row.classList.add('table-success');
        else if (status === 'Fail')     row.classList.add('table-warning');
        else if (status === 'Rejected') row.classList.add('table-danger');

        const showDefect = ['Fail', 'Rejected'].includes(status);
        if (defect)   defect.closest('.defect-wrap')?.classList.toggle('d-none', !showDefect);
        if (severity) severity.closest('.severity-wrap')?.classList.toggle('d-none', !showDefect);
    }

    document.querySelectorAll('.result-status').forEach(sel => {
        sel.addEventListener('change', () => applyResultStatus(sel));
        applyResultStatus(sel);
    });

    // ── File input preview ────────────────────────────────────────────────────
    function attachFilePreviewer(input, previewEl) {
        if (!input || !previewEl) return;
        input.addEventListener('change', function () {
            previewEl.innerHTML = '';
            [...this.files].forEach(file => {
                const outer = document.createElement('div');
                outer.style.cssText = 'display:flex;flex-direction:column;align-items:center;';

                const wrap = document.createElement('div');
                wrap.style.cssText = 'width:56px;height:56px;position:relative;';

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.className = 'rounded border object-fit-cover w-100 h-100';
                    img.style.objectFit = 'cover';
                    const reader = new FileReader();
                    reader.onload = e => img.src = e.target.result;
                    reader.readAsDataURL(file);
                    wrap.appendChild(img);
                } else {
                    wrap.innerHTML = `<div class="d-flex align-items-center justify-content-center border rounded bg-light text-muted w-100 h-100"><i class="feather-file" style="font-size:18px"></i></div>`;
                }
                outer.appendChild(wrap);

                const label = document.createElement('small');
                label.className = 'text-muted mt-1';
                label.style.cssText = 'font-size:9px;max-width:56px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;';
                label.textContent = file.name;
                outer.appendChild(label);
                previewEl.appendChild(outer);
            });
        });
    }

    // Wire up all file inputs
    document.querySelectorAll('.file-input[data-preview]').forEach(input => {
        const preview = document.getElementById(input.dataset.preview);
        attachFilePreviewer(input, preview);
    });

    // ── Attachment delete ─────────────────────────────────────────────────────
    document.querySelectorAll('.delete-attachment').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Remove this attachment?')) return;
            const attId  = this.dataset.attId;
            const target = document.getElementById(this.dataset.target);

            fetch(`/attachments/${attId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.ok ? target?.remove() : alert('Could not delete attachment.'))
            .catch(() => alert('Network error.'));
        });
    });

    // ── AQL calculator ────────────────────────────────────────────────────────
    const aqlForm = document.getElementById('aql-calculator-form');
    if (aqlForm) {
        const calculateBtn = document.getElementById('aql-calculate-btn');
        calculateBtn?.addEventListener('click', function () {
            const lotSize  = document.getElementById('aql_lot_size')?.value;
            const level    = document.getElementById('aql_inspection_level')?.value;
            const aqlCrit  = document.getElementById('aql_aql_critical')?.value;
            const aqlMaj   = document.getElementById('aql_aql_major')?.value;
            const aqlMin   = document.getElementById('aql_aql_minor')?.value;

            if (!lotSize) { alert('Enter lot size first.'); return; }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Calculating…';

            fetch('{{ route("inspections.aql.calculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    lot_size: parseInt(lotSize),
                    inspection_level: level,
                    aql_critical: parseFloat(aqlCrit) || 0.065,
                    aql_major:    parseFloat(aqlMaj)  || 2.5,
                    aql_minor:    parseFloat(aqlMin)  || 4.0,
                }),
            })
            .then(r => r.json())
            .then(data => {
                const set = (id, val) => {
                    const el = document.getElementById(id);
                    if (el) el.value = val ?? '';
                };
                set('aql_code_letter',  data.code_letter);
                set('aql_sample_size',  data.sample_size);
                set('aql_ac_critical',  data.critical?.ac);
                set('aql_re_critical',  data.critical?.re);
                set('aql_ac_major',     data.major?.ac);
                set('aql_re_major',     data.major?.re);
                set('aql_ac_minor',     data.minor?.ac);
                set('aql_re_minor',     data.minor?.re);

                document.getElementById('aql-result-row')?.classList.remove('d-none');
            })
            .catch(() => alert('AQL calculation failed. Check your input.'))
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="feather-cpu me-1"></i>Calculate';
            });
        });

        // Auto-update verdict display when found counts change
        ['aql_found_critical','aql_found_major','aql_found_minor'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', updateAqlVerdict);
        });

        function updateAqlVerdict() {
            const foundCrit = parseInt(document.getElementById('aql_found_critical')?.value) || 0;
            const foundMaj  = parseInt(document.getElementById('aql_found_major')?.value)    || 0;
            const foundMin  = parseInt(document.getElementById('aql_found_minor')?.value)    || 0;
            const acCrit    = parseInt(document.getElementById('aql_ac_critical')?.value);
            const acMaj     = parseInt(document.getElementById('aql_ac_major')?.value);
            const acMin     = parseInt(document.getElementById('aql_ac_minor')?.value);

            const verdictEl = document.getElementById('aql_verdict_display');
            if (!verdictEl) return;

            if (foundCrit + foundMaj + foundMin === 0) {
                verdictEl.className = 'badge bg-soft-secondary text-secondary fs-13 px-3 py-2';
                verdictEl.textContent = 'Pending';
                return;
            }

            const fail =
                (!isNaN(acCrit) && foundCrit > acCrit) ||
                (!isNaN(acMaj)  && foundMaj  > acMaj)  ||
                (!isNaN(acMin)  && foundMin  > acMin);

            verdictEl.className = fail
                ? 'badge bg-soft-danger text-danger fs-13 px-3 py-2'
                : 'badge bg-soft-success text-success fs-13 px-3 py-2';
            verdictEl.textContent = fail ? 'FAIL' : 'PASS';
        }
    }
})();
</script>
@endpush
