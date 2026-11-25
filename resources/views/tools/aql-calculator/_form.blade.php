@php
    $isEdit = isset($aqlCalculation);
    $existingVariations = old('variations', $aqlCalculation->variations ?? []);
    // Pre-shaped into a plain array (not a raw expression) so the @json()
    // directive below only ever sees a single variable — @json() splits its
    // argument on top-level commas to find json_encode() options/depth, so
    // passing it an inline expression containing commas (array literals,
    // closure args, ...) corrupts the compiled PHP.
    $variationRowsForJs = collect($existingVariations)
        ->map(fn ($row) => [
            'color' => $row['color'] ?? '',
            'size' => $row['size'] ?? '',
            'qty' => $row['qty'] ?? 0,
        ])
        ->values()
        ->all();
@endphp

<div class="row">
    <div class="col-xl-4 col-lg-5">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="feather-tag me-2 text-primary"></i>Record Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-12">Title / Reference <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           placeholder="e.g. PO-2044 – Lot 5000"
                           value="{{ old('title', $aqlCalculation->title ?? '') }}">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="feather-sliders me-2 text-primary"></i>Sampling Parameters</h5>
            </div>
            <div class="card-body">

                {{-- Lot Size --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-12">
                        Lot Size <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="lot_size" class="form-control @error('lot_size') is-invalid @enderror" id="calc-lot-size"
                           min="2" placeholder="e.g. 5000" value="{{ old('lot_size', $aqlCalculation->lot_size ?? '') }}" oninput="calcRun()">
                    @error('lot_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text fs-11" id="calc-code-info" style="display:none">
                        Code Letter: <strong id="calc-code-letter"></strong> —
                        Base Sample: <strong id="calc-base-sample"></strong> units
                    </div>
                </div>

                {{-- Inspection Level --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-12">Inspection Level</label>
                    <input type="hidden" name="inspection_level" id="calc-level-input" value="{{ old('inspection_level', $aqlCalculation->inspection_level ?? 'II') }}">
                    <div class="d-flex flex-wrap gap-2" id="calc-level-btns">
                        @foreach(['I','II','III','S1','S2','S3','S4'] as $lvl)
                        <button type="button"
                                class="btn btn-sm calc-level-btn {{ old('inspection_level', $aqlCalculation->inspection_level ?? 'II') === $lvl ? 'btn-primary' : 'btn-light' }}"
                                data-level="{{ $lvl }}"
                                onclick="calcSetLevel('{{ $lvl }}')">{{ $lvl }}</button>
                        @endforeach
                    </div>
                    <div class="form-text fs-11 mt-1">
                        General: I · II · III &nbsp;|&nbsp; Special: S1 – S4
                    </div>
                    @error('inspection_level')<div class="text-danger fs-11 mt-1">{{ $message }}</div>@enderror
                </div>

                <hr>

                @php
                    $aqlOptions = ['not_allowed' => 'Not Allowed', '0.065' => '0.065', '0.10' => '0.10', '0.15' => '0.15', '0.25' => '0.25', '0.40' => '0.40', '0.65' => '0.65', '1.0' => '1.0', '1.5' => '1.5', '2.5' => '2.5', '4.0' => '4.0', '6.5' => '6.5'];
                @endphp

                {{-- Critical AQL --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-12">
                        <span class="badge bg-soft-danger text-danger me-1">CR</span> Critical AQL
                    </label>
                    <select class="form-select form-select-sm @error('aql_critical') is-invalid @enderror" name="aql_critical" id="calc-aql-critical" onchange="calcRun()">
                        @foreach($aqlOptions as $val => $label)
                        <option value="{{ $val }}" @selected(old('aql_critical', $aqlCalculation->aql_critical ?? '2.5') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('aql_critical')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Major AQL --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-12">
                        <span class="badge bg-soft-warning text-warning me-1">MA</span> Major AQL
                    </label>
                    <select class="form-select form-select-sm @error('aql_major') is-invalid @enderror" name="aql_major" id="calc-aql-major" onchange="calcRun()">
                        @foreach($aqlOptions as $val => $label)
                        <option value="{{ $val }}" @selected(old('aql_major', $aqlCalculation->aql_major ?? '2.5') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('aql_major')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Minor AQL --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-12">
                        <span class="badge bg-soft-info text-info me-1">MI</span> Minor AQL
                    </label>
                    <select class="form-select form-select-sm @error('aql_minor') is-invalid @enderror" name="aql_minor" id="calc-aql-minor" onchange="calcRun()">
                        @foreach($aqlOptions as $val => $label)
                        <option value="{{ $val }}" @selected(old('aql_minor', $aqlCalculation->aql_minor ?? '2.5') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('aql_minor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <hr>

                {{-- Defects found --}}
                <div class="mb-2">
                    <label class="form-label fw-semibold fs-12">Defects Found (for Verdict)</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label fs-11 text-danger mb-1">Critical</label>
                            <input type="number" name="found_critical" class="form-control form-control-sm text-center"
                                   id="calc-found-critical" min="0" value="{{ old('found_critical', $aqlCalculation->found_critical ?? 0) }}" oninput="calcRun()">
                        </div>
                        <div class="col-4">
                            <label class="form-label fs-11 text-warning mb-1">Major</label>
                            <input type="number" name="found_major" class="form-control form-control-sm text-center"
                                   id="calc-found-major" min="0" value="{{ old('found_major', $aqlCalculation->found_major ?? 0) }}" oninput="calcRun()">
                        </div>
                        <div class="col-4">
                            <label class="form-label fs-11 text-info mb-1">Minor</label>
                            <input type="number" name="found_minor" class="form-control form-control-sm text-center"
                                   id="calc-found-minor" min="0" value="{{ old('found_minor', $aqlCalculation->found_minor ?? 0) }}" oninput="calcRun()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="feather-file-text me-2 text-primary"></i>Notes</h5>
            </div>
            <div class="card-body">
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                          placeholder="Optional remarks about this calculation...">{{ old('notes', $aqlCalculation->notes ?? '') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ── Live Preview + Variations ─────────────────────────────────────── --}}
    <div class="col-xl-8 col-lg-7">

        <div class="card mb-4" id="calc-result-card" style="display:none">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="feather-bar-chart-2 me-2 text-primary"></i>Sampling Plan Preview</h5>
                <div>
                    <span class="badge bg-soft-secondary text-secondary me-1">
                        Level: <strong id="calc-badge-level">II</strong>
                    </span>
                    <span class="badge bg-soft-primary text-primary me-1">
                        Code: <strong id="calc-badge-code">—</strong>
                    </span>
                    <span class="badge bg-soft-success text-success">
                        Base n = <strong id="calc-badge-ss">—</strong>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Defect Type</th>
                                <th class="text-center">AQL Level</th>
                                <th class="text-center">Sample Size</th>
                                <th class="text-center">Accept (Ac)</th>
                                <th class="text-center">Reject (Re)</th>
                                <th class="text-center">Found</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="calc-result-tbody"></tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer fs-11 text-muted">
                <i class="feather-info me-1"></i>Preview only — the final plan is recalculated on save.
            </div>
        </div>

        <div class="card mb-4" id="calc-verdict-card" style="display:none">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-text avatar-lg rounded" id="calc-verdict-icon">
                        <i class="feather-clock"></i>
                    </div>
                    <div>
                        <div class="fs-12 text-muted">Overall AQL Verdict (Preview)</div>
                        <div class="fs-4 fw-bold" id="calc-verdict-text">Pending</div>
                    </div>
                    <div class="ms-auto text-muted fs-12 text-end">
                        <div>Lot: <strong id="calc-verdict-lot">—</strong> units</div>
                        <div>Sample: <strong id="calc-verdict-sample">—</strong> units</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" id="calc-empty-card">
            <div class="card-body text-center py-5 text-muted">
                <i class="feather-cpu" style="font-size:3rem; opacity:.25;"></i>
                <div class="mt-3">Enter a lot size to preview the sampling plan.</div>
            </div>
        </div>

        {{-- ISO 2859-1 Reference — Lot Size Ranges --}}
        <div class="card mb-4 mt-4" id="calc-ref-card" style="display:none">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="feather-info me-2 text-muted"></i>ISO 2859-1 Reference — Lot Size Ranges</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0 fs-12">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Lot Range</th>
                                <th class="text-center">I</th>
                                <th class="text-center">II</th>
                                <th class="text-center">III</th>
                                <th class="text-center">S1</th>
                                <th class="text-center">S2</th>
                                <th class="text-center">S3</th>
                                <th class="text-center">S4</th>
                            </tr>
                        </thead>
                        <tbody id="calc-ref-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Variations / Quantity Distribution --}}
        <div class="card mb-4 mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="feather-grid me-2 text-primary"></i>Variations <span class="text-muted fw-normal fs-12">(optional)</span></h5>
                <button type="button" class="btn btn-sm btn-light-primary" onclick="varAddRow()">
                    <i class="feather-plus me-1"></i>Add Variation
                </button>
            </div>

            <div class="card-body p-0" id="var-summary-row" style="display:none">
                <div class="row g-0 text-center border-bottom">
                    <div class="col-4 py-3 border-end">
                        <div class="fs-11 text-muted mb-1">Total Qty</div>
                        <div class="fw-bold fs-5" id="var-summary-total-qty">0</div>
                    </div>
                    <div class="col-4 py-3 border-end">
                        <div class="fs-11 text-muted mb-1">Inspection Sample</div>
                        <div class="fw-bold fs-5 text-primary" id="var-summary-sample">—</div>
                    </div>
                    <div class="col-4 py-3">
                        <div class="fs-11 text-muted mb-1">Code Letter</div>
                        <div class="fw-bold fs-5 text-info" id="var-summary-code">—</div>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Color</th>
                                <th>Size</th>
                                <th class="text-center" style="width:110px">Qty</th>
                                <th class="text-center" style="width:90px">Share %</th>
                                <th class="text-center" style="width:100px">Inspect Qty</th>
                                <th class="text-center" style="width:36px"></th>
                            </tr>
                        </thead>
                        <tbody id="var-rows-tbody"></tbody>
                        <tfoot>
                            <tr class="table-light fw-semibold">
                                <td colspan="2" class="text-end text-muted fs-12">Total</td>
                                <td class="text-center" id="var-foot-total-qty">—</td>
                                <td class="text-center">100%</td>
                                <td class="text-center" id="var-foot-total-inspect">—</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer fs-11 text-muted">
                <i class="feather-info me-1"></i>The sample size shown above (based on the Lot Size entered) is distributed proportionally across these rows. Leave empty to skip.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/aql-plan.js') }}"></script>
<script>
(function() {
    'use strict';

    const COLORS = @json($colors->pluck('name'));
    const SIZES  = @json($sizes->pluck('name'));

    // All sampling numbers come from AqlCalculationService::tableForJs() — no tables here.
    const AQL = window.AqlPlan(@json($aqlJsData));

    function escHtml(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function distributeProportionally(sampleSize, qtys) {
        const totalQty = qtys.reduce((a, b) => a + b, 0);
        if (totalQty <= 0 || qtys.length === 0) return qtys.map(() => 0);
        const raw = qtys.map(q => (q / totalQty) * sampleSize);
        const floored = raw.map(Math.floor);
        let rem = sampleSize - floored.reduce((a, b) => a + b, 0);
        const fracs = raw.map((r, i) => ({ i, frac: r - Math.floor(r) }));
        fracs.sort((a, b) => b.frac - a.frac);
        for (let j = 0; j < rem && j < fracs.length; j++) floored[fracs[j].i]++;
        return floored;
    }

    // ── Sampling plan preview (Tab 1 logic, read-only) ──────────────────────
    let calcLevel = document.getElementById('calc-level-input').value || 'II';
    let calcLotSize = 0, calcBaseSample = 0;

    window.calcSetLevel = function(lvl) {
        calcLevel = lvl;
        document.getElementById('calc-level-input').value = lvl;
        document.querySelectorAll('.calc-level-btn').forEach(b => {
            b.className = 'btn btn-sm calc-level-btn ' + (b.dataset.level === lvl ? 'btn-primary' : 'btn-light');
        });
        calcRun();
    };

    window.calcRun = function() {
        const lot = parseInt(document.getElementById('calc-lot-size')?.value) || 0;
        if (lot < 2) { calcShowEmpty(); return; }

        const crAql = document.getElementById('calc-aql-critical').value;
        const maAql = document.getElementById('calc-aql-major').value;
        const miAql = document.getElementById('calc-aql-minor').value;

        // Units to inspect: enough for every severity's plan (same rule as the server).
        const bs = AQL.sampleSize(lot, calcLevel, [crAql, maAql, miAql]);
        if (!bs) { calcShowEmpty(); return; }

        calcLotSize = lot;
        calcBaseSample = bs;

        const crRes = AQL.plan(crAql, lot, calcLevel);
        const maRes = AQL.plan(maAql, lot, calcLevel);
        const miRes = AQL.plan(miAql, lot, calcLevel);

        const codeLetter = AQL.codeLetter(lot, calcLevel) || '—';

        document.getElementById('calc-badge-level').textContent = calcLevel;
        document.getElementById('calc-badge-code').textContent  = codeLetter;
        document.getElementById('calc-badge-ss').textContent    = bs;
        document.getElementById('calc-code-letter').textContent = codeLetter;
        document.getElementById('calc-base-sample').textContent = bs;
        document.getElementById('calc-code-info').style.display = '';

        const tbody = document.getElementById('calc-result-tbody');
        const rows = [
            { label:'Critical', badge:'danger',  aql: crAql, res: crRes, foundId:'calc-found-critical' },
            { label:'Major',    badge:'warning', aql: maAql, res: maRes, foundId:'calc-found-major' },
            { label:'Minor',    badge:'info',    aql: miAql, res: miRes, foundId:'calc-found-minor' },
        ];
        let anyFound = 0, fail = false;
        tbody.innerHTML = rows.map(r => {
            const found = parseInt(document.getElementById(r.foundId)?.value) || 0;
            anyFound += found;
            const ac = r.res?.ac ?? (r.aql === 'not_allowed' ? 0 : null);
            const re = r.res?.re ?? (r.aql === 'not_allowed' ? 1 : null);
            const ss = r.res?.ss ?? bs;
            const aqlLabel = r.aql === 'not_allowed' ? 'Not Allowed' : r.aql;
            let status = { label:'Pending', cls:'bg-soft-secondary text-secondary' };
            if (ac !== null && found > ac) { status = { label:'Fail', cls:'bg-soft-danger text-danger' }; fail = true; }
            else if (ac !== null && found > 0 && found <= ac) status = { label:'Pass', cls:'bg-soft-success text-success' };
            return `<tr>
                <td><span class="badge bg-soft-${r.badge} text-${r.badge}">${r.label}</span></td>
                <td class="text-center">${escHtml(aqlLabel)}</td>
                <td class="text-center fw-semibold">${ss}</td>
                <td class="text-center">${ac !== null ? ac : '—'}</td>
                <td class="text-center">${re !== null ? re : '—'}</td>
                <td class="text-center">${found}</td>
                <td class="text-center"><span class="badge ${status.cls}">${status.label}</span></td>
            </tr>`;
        }).join('');

        const verdict = anyFound === 0 ? 'Pending' : (fail ? 'Fail' : 'Pass');
        calcSetVerdictUI(verdict);
        calcShowResults();
        calcBuildRefTable();
        varRecalcDistribution();
    };

    function calcSetVerdictUI(v) {
        const icon = document.getElementById('calc-verdict-icon');
        const text = document.getElementById('calc-verdict-text');
        const lot  = document.getElementById('calc-verdict-lot');
        const ss   = document.getElementById('calc-verdict-sample');
        if (lot) lot.textContent = calcLotSize.toLocaleString();
        if (ss)  ss.textContent  = calcBaseSample;
        const map = {
            Pending:{ cls:'bg-soft-secondary text-secondary', ico:'feather-clock', tcls:'text-secondary' },
            Pass:   { cls:'bg-soft-success text-success',     ico:'feather-check-circle', tcls:'text-success' },
            Fail:   { cls:'bg-soft-danger text-danger',       ico:'feather-x-circle', tcls:'text-danger' },
        };
        const m = map[v] || map.Pending;
        if (icon) { icon.className = 'avatar-text avatar-lg rounded ' + m.cls; icon.innerHTML = `<i class="${m.ico}"></i>`; }
        if (text) { text.className = 'fs-4 fw-bold ' + m.tcls; text.textContent = v; }
    }

    function calcShowResults() {
        document.getElementById('calc-result-card').style.display  = '';
        document.getElementById('calc-verdict-card').style.display = '';
        document.getElementById('calc-empty-card').style.display   = 'none';
        document.getElementById('calc-ref-card').style.display     = '';
    }

    function calcShowEmpty() {
        document.getElementById('calc-result-card').style.display  = 'none';
        document.getElementById('calc-verdict-card').style.display = 'none';
        document.getElementById('calc-empty-card').style.display   = '';
        document.getElementById('calc-ref-card').style.display     = 'none';
        document.getElementById('calc-code-info').style.display    = 'none';
        calcLotSize = 0;
        calcBaseSample = 0;
        varRecalcDistribution();
    }

    function calcBuildRefTable() {
        const tbody = document.getElementById('calc-ref-tbody');
        if (!tbody) return;
        tbody.innerHTML = AQL.lotSizeTable.map(r => {
            const maxLabel = r.max === null ? '∞' : r.max.toLocaleString();
            const highlight = calcLotSize >= r.min && (r.max === null || calcLotSize <= r.max) ? 'table-warning' : '';
            const n = lvl => AQL.sampleSizes[r.codes[lvl]];
            return `<tr class="${highlight}">
                <td class="fw-semibold">${r.min.toLocaleString()} – ${maxLabel}</td>
                <td class="text-center">${n('I')}</td>
                <td class="text-center">${n('II')}</td>
                <td class="text-center">${n('III')}</td>
                <td class="text-center">${n('S1')}</td>
                <td class="text-center">${n('S2')}</td>
                <td class="text-center">${n('S3')}</td>
                <td class="text-center">${n('S4')}</td>
            </tr>`;
        }).join('');
    }

    // ── Variations rows ──────────────────────────────────────────────────
    let varRows = @json($variationRowsForJs);
    if (varRows.length === 0) varRows = [{ color:'', size:'', qty:0 }];

    function buildColorOptions(selected) {
        return '<option value="">— Color —</option>' +
            COLORS.map(c => `<option value="${escHtml(c)}"${selected === c ? ' selected' : ''}>${escHtml(c)}</option>`).join('');
    }

    function buildSizeOptions(selected) {
        return '<option value="">— Size —</option>' +
            SIZES.map(s => `<option value="${escHtml(s)}"${selected === s ? ' selected' : ''}>${escHtml(s)}</option>`).join('');
    }

    // Rebuilds the whole rows table (structure changes: add/remove/init only —
    // never call this from a keystroke handler, it would blow away input focus).
    function varRenderRows() {
        const tbody = document.getElementById('var-rows-tbody');
        if (!tbody) return;

        tbody.innerHTML = varRows.map((r, i) => `
            <tr>
                <td>
                    <select class="form-select form-select-sm" name="variations[${i}][color]" onchange="varUpdateRow(${i},'color',this.value)">
                        ${buildColorOptions(r.color)}
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm" name="variations[${i}][size]" onchange="varUpdateRow(${i},'size',this.value)">
                        ${buildSizeOptions(r.size)}
                    </select>
                </td>
                <td><input type="number" class="form-control form-control-sm text-center" name="variations[${i}][qty]" value="${r.qty}"
                           min="0" oninput="varUpdateRow(${i},'qty',this.value)"></td>
                <td class="text-center text-muted" id="var-share-${i}">—</td>
                <td class="text-center fw-semibold text-success" id="var-inspect-${i}">—</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="varRemoveRow(${i})">
                        <i class="feather-x"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        varRecalcDistribution();
    }

    // Updates only the computed columns (share %, inspect qty, footer,
    // summary) in place — safe to call on every keystroke since it never
    // touches the color/size/qty input elements themselves.
    function varRecalcDistribution() {
        const qtys = varRows.map(r => r.qty || 0);
        const totalQty = qtys.reduce((a, b) => a + b, 0);
        const sampleSize = calcBaseSample || 0;
        const inspectQtys = (totalQty > 0 && sampleSize > 0) ? distributeProportionally(sampleSize, qtys) : qtys.map(() => null);

        varRows.forEach((r, i) => {
            const share = totalQty > 0 ? ((r.qty / totalQty) * 100).toFixed(1) + '%' : '—';
            const shareEl = document.getElementById(`var-share-${i}`);
            const inspectEl = document.getElementById(`var-inspect-${i}`);
            if (shareEl) shareEl.textContent = share;
            if (inspectEl) inspectEl.textContent = inspectQtys[i] !== null ? inspectQtys[i] : '—';
        });

        const totalInspect = (totalQty > 0 && sampleSize > 0) ? inspectQtys.reduce((a, b) => a + b, 0) : null;
        document.getElementById('var-foot-total-qty').textContent = totalQty > 0 ? totalQty.toLocaleString() : '—';
        document.getElementById('var-foot-total-inspect').textContent = totalInspect !== null ? totalInspect : '—';

        const summaryRow = document.getElementById('var-summary-row');
        if (totalQty > 0) {
            summaryRow.style.display = '';
            document.getElementById('var-summary-total-qty').textContent = totalQty.toLocaleString();
            document.getElementById('var-summary-sample').textContent = sampleSize > 0 ? sampleSize : '—';
            document.getElementById('var-summary-code').textContent = sampleSize > 0 ? (AQL.codeLetter(calcLotSize, calcLevel) || '—') : '—';
        } else {
            summaryRow.style.display = 'none';
        }
    }

    window.varAddRow = function() {
        varRows.push({ color:'', size:'', qty:0 });
        varRenderRows();
    };

    window.varRemoveRow = function(i) {
        varRows.splice(i, 1);
        if (varRows.length === 0) varRows.push({ color:'', size:'', qty:0 });
        varRenderRows();
    };

    window.varUpdateRow = function(i, field, val) {
        varRows[i][field] = field === 'qty' ? (parseInt(val) || 0) : val;
        varRecalcDistribution();
    };

    // ── Init ─────────────────────────────────────────────────────────────
    varRenderRows();
    calcRun();

})();
</script>
@endpush
