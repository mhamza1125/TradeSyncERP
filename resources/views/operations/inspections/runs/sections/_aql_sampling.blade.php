{{-- AQL Sampling Section — ISO 2859-1 with Quantity Distribution --}}
@php
    $a = $aql; // InspectionRunAql model or null

    // Helper: -1.0 stored as sentinel for "Not Allowed"
    $dispCritical = ($a && $a->aql_critical == -1) ? 'not_allowed' : ($a?->aql_critical ?? 0.065);
    $dispMajor    = ($a && $a->aql_major    == -1) ? 'not_allowed' : ($a?->aql_major    ?? 2.5);
    $dispMinor    = ($a && $a->aql_minor    == -1) ? 'not_allowed' : ($a?->aql_minor    ?? 4.0);

    // Per-size checked/error breakdown — one row per declared size for this
    // run's sample+color (from sample_variations), pre-filled with any
    // already-saved counts. Purely additive input detail; does not drive
    // lot_size/sample_size/found_* above.
    $savedBreakdown = collect($a?->sizeBreakdowns ?? [])->keyBy('size_label');
    $breakdownSizes = collect($sizeOptions ?? [])->merge($savedBreakdown->keys())->unique()->values();
    $sizeBreakdownRows = $breakdownSizes->map(fn ($label) => [
        'size_label'  => $label,
        'order_qty'   => $savedBreakdown->get($label)?->order_qty ?? 0,
        'checked_qty' => $savedBreakdown->get($label)?->checked_qty ?? 0,
        'error_qty'   => $savedBreakdown->get($label)?->error_qty ?? 0,
    ])->values();
@endphp

<div id="aql-calculator-form">

    {{-- ── Quantity Distribution / Variations ───────────────────────────── --}}
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-semibold mb-0">
                <i class="feather-grid me-2 text-success"></i>Quantity Distribution by Variation
            </h6>
            <button type="button" id="aql-add-variation-btn" class="btn btn-sm btn-light-primary">
                <i class="feather-plus me-1"></i>Add Variation
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0 align-middle" id="aql-variations-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:28%">Color</th>
                        <th style="width:20%">Size</th>
                        <th style="width:20%" class="text-center">Ordered Qty</th>
                        <th style="width:18%" class="text-center">Inspect Qty</th>
                        <th style="width:8%" class="text-center"></th>
                    </tr>
                </thead>
                <tbody id="aql-variations-tbody">
                    {{-- Rows rendered by JS --}}
                </tbody>
                <tfoot>
                    <tr class="table-light fw-semibold">
                        <td colspan="2" class="text-end text-muted fs-12">Total</td>
                        <td class="text-center" id="aql-total-order-qty">—</td>
                        <td class="text-center" id="aql-total-inspect-qty">—</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <small class="text-muted fs-11">
            <i class="feather-info me-1"></i>
            Inspect quantities are automatically distributed proportionally based on the AQL sample size below.
        </small>
    </div>

    {{-- ── Sampling plan inputs ───────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                Lot Size <span class="text-muted fs-11">(auto from variations)</span>
            </label>
            <input type="number" id="aql_lot_size" name="aql[lot_size]"
                   class="form-control form-control-sm"
                   value="{{ old('aql.lot_size', $a?->lot_size) }}"
                   placeholder="e.g. 5000" min="1">
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">Inspection Level</label>
            <select id="aql_inspection_level" name="aql[inspection_level]" class="form-select form-select-sm" onchange="aqlRecalculate()">
                @foreach(['I','II','III','S1','S2','S3','S4'] as $lvl)
                    <option value="{{ $lvl }}" @selected(old('aql.inspection_level', $a?->inspection_level ?? 'II') === $lvl)>
                        {{ $lvl }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-danger text-danger me-1">CR</span> Critical AQL
            </label>
            <select id="aql_aql_critical" name="aql[aql_critical]" class="form-select form-select-sm" onchange="aqlRecalculate()">
                <option value="not_allowed" @selected($dispCritical === 'not_allowed')>Not Allowed</option>
                @foreach(\App\Services\Inspection\AqlCalculationService::AQL_LEVELS as $lvl)
                    <option value="{{ $lvl }}" @selected($dispCritical !== 'not_allowed' && \App\Services\Inspection\AqlCalculationService::aqlKey($dispCritical) === $lvl)>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-warning text-warning me-1">MA</span> Major AQL
            </label>
            <select id="aql_aql_major" name="aql[aql_major]" class="form-select form-select-sm" onchange="aqlRecalculate()">
                <option value="not_allowed" @selected($dispMajor === 'not_allowed')>Not Allowed</option>
                @foreach(\App\Services\Inspection\AqlCalculationService::AQL_LEVELS as $lvl)
                    <option value="{{ $lvl }}" @selected($dispMajor !== 'not_allowed' && \App\Services\Inspection\AqlCalculationService::aqlKey($dispMajor) === $lvl)>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-info text-info me-1">MI</span> Minor AQL
            </label>
            <select id="aql_aql_minor" name="aql[aql_minor]" class="form-select form-select-sm" onchange="aqlRecalculate()">
                <option value="not_allowed" @selected($dispMinor === 'not_allowed')>Not Allowed</option>
                @foreach(\App\Services\Inspection\AqlCalculationService::AQL_LEVELS as $lvl)
                    <option value="{{ $lvl }}" @selected($dispMinor !== 'not_allowed' && \App\Services\Inspection\AqlCalculationService::aqlKey($dispMinor) === $lvl)>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4 d-flex align-items-end">
            <button type="button" id="aql-calculate-btn" class="btn btn-sm btn-light-primary w-100">
                <i class="feather-cpu me-1"></i>Calculate
            </button>
        </div>
    </div>

    {{-- ── Calculated plan result ──────────────────────────────────────────── --}}
    <div id="aql-result-row" class="{{ $a && $a->sample_size > 0 ? '' : 'd-none' }}">
        <div class="card border-0 bg-soft-success bg-opacity-10 mb-4">
            <div class="card-body py-3">
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-auto">
                        <span class="fs-12 text-muted">Code Letter</span>
                        <div class="fw-bold fs-16 text-primary" id="aql_code_letter_display">{{ $a?->code_letter ?? '—' }}</div>
                        <input type="hidden" id="aql_code_letter_val" value="{{ $a?->code_letter }}">
                    </div>
                    <div class="col-auto">
                        <span class="fs-12 text-muted">Sample Size</span>
                        <div class="fw-bold fs-16 text-success" id="aql_sample_size_display">{{ $a?->sample_size ?? '—' }}</div>
                        <input type="number" id="aql_sample_size" name="aql[sample_size]"
                               class="d-none"
                               value="{{ old('aql.sample_size', $a?->sample_size) }}" readonly tabindex="-1">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Defect Type</th>
                                <th class="text-center">AQL Level</th>
                                <th class="text-center">Sample Size</th>
                                <th class="text-center">Accept (Ac)</th>
                                <th class="text-center">Reject (Re)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-soft-danger text-danger">Critical</span></td>
                                <td class="text-center fs-12" id="aql_cr_aql_display">{{ $a && $a->aql_critical == -1 ? 'Not Allowed' : ($a?->aql_critical ?? '—') }}</td>
                                <td class="text-center fw-semibold" id="aql_cr_ss_display">{{ $a?->sample_size ?? '—' }}</td>
                                <td class="text-center">
                                    <input type="number" id="aql_ac_critical" name="aql[ac_critical]"
                                           class="form-control form-control-sm text-center"
                                           value="{{ old('aql.ac_critical', $a?->ac_critical) }}"
                                           style="max-width:70px;margin:auto">
                                </td>
                                <td class="text-center">
                                    <input type="number" id="aql_re_critical" name="aql[re_critical]"
                                           class="form-control form-control-sm text-center"
                                           value="{{ old('aql.re_critical', $a?->re_critical) }}"
                                           style="max-width:70px;margin:auto">
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-soft-warning text-warning">Major</span></td>
                                <td class="text-center fs-12" id="aql_ma_aql_display">{{ $a && $a->aql_major == -1 ? 'Not Allowed' : ($a?->aql_major ?? '—') }}</td>
                                <td class="text-center fw-semibold" id="aql_ma_ss_display">{{ $a?->sample_size ?? '—' }}</td>
                                <td class="text-center">
                                    <input type="number" id="aql_ac_major" name="aql[ac_major]"
                                           class="form-control form-control-sm text-center"
                                           value="{{ old('aql.ac_major', $a?->ac_major) }}"
                                           style="max-width:70px;margin:auto">
                                </td>
                                <td class="text-center">
                                    <input type="number" id="aql_re_major" name="aql[re_major]"
                                           class="form-control form-control-sm text-center"
                                           value="{{ old('aql.re_major', $a?->re_major) }}"
                                           style="max-width:70px;margin:auto">
                                </td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-soft-info text-info">Minor</span></td>
                                <td class="text-center fs-12" id="aql_mi_aql_display">{{ $a && $a->aql_minor == -1 ? 'Not Allowed' : ($a?->aql_minor ?? '—') }}</td>
                                <td class="text-center fw-semibold" id="aql_mi_ss_display">{{ $a?->sample_size ?? '—' }}</td>
                                <td class="text-center">
                                    <input type="number" id="aql_ac_minor" name="aql[ac_minor]"
                                           class="form-control form-control-sm text-center"
                                           value="{{ old('aql.ac_minor', $a?->ac_minor) }}"
                                           style="max-width:70px;margin:auto">
                                </td>
                                <td class="text-center">
                                    <input type="number" id="aql_re_minor" name="aql[re_minor]"
                                           class="form-control form-control-sm text-center"
                                           value="{{ old('aql.re_minor', $a?->re_minor) }}"
                                           style="max-width:70px;margin:auto">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Defect counts found ─────────────────────────────────────────────── --}}
    <hr class="my-4">
    <h6 class="fw-semibold mb-1">Defects Found During Inspection</h6>
    <p class="text-muted fs-11 mb-3">
        <i class="feather-info me-1"></i>
        @if(($a?->counts_source ?? 'manual') === 'defect_rows')
            Counted automatically from the recorded defects (critical, major, minor); edit the defect rows to change them.
        @else
            Once defects are recorded in the defect section, these counts are taken from those rows instead.
        @endif
    </p>
    <div class="row g-3 align-items-end mb-4">
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-danger text-danger me-1">CR</span> Critical Found
            </label>
            <input type="number" id="aql_found_critical" name="aql[found_critical]"
                   class="form-control form-control-sm"
                   value="{{ old('aql.found_critical', $a?->found_critical ?? 0) }}"
                   min="0">
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-warning text-warning me-1">MA</span> Major Found
            </label>
            <input type="number" id="aql_found_major" name="aql[found_major]"
                   class="form-control form-control-sm"
                   value="{{ old('aql.found_major', $a?->found_major ?? 0) }}"
                   min="0">
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-info text-info me-1">MI</span> Minor Found
            </label>
            <input type="number" id="aql_found_minor" name="aql[found_minor]"
                   class="form-control form-control-sm"
                   value="{{ old('aql.found_minor', $a?->found_minor ?? 0) }}"
                   min="0">
        </div>
        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold fs-12">AQL Verdict</label>
            <div>
                @php
                    $verdictClass = match($a?->verdict) {
                        'Pass' => 'bg-soft-success text-success',
                        'Fail' => 'bg-soft-danger text-danger',
                        default => 'bg-soft-secondary text-secondary',
                    };
                @endphp
                <span id="aql_verdict_display"
                      class="badge {{ $verdictClass }} fs-13 px-3 py-2">
                    {{ $a?->verdict ?? 'Pending' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── Per-size checked / error breakdown ──────────────────────────────── --}}
    <hr class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-semibold mb-0">
            <i class="feather-list me-2 text-primary"></i>Per-Size Checked / Error Breakdown
        </h6>
        <button type="button" id="aql-add-size-row-btn" class="btn btn-sm btn-light-primary">
            <i class="feather-plus me-1"></i>Add Size
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0 align-middle" id="aql-size-breakdown-table">
            <thead class="table-light">
                <tr>
                    <th style="width:34%">Size</th>
                    <th style="width:20%" class="text-center">Order Qty</th>
                    <th style="width:20%" class="text-center">Checked Qty</th>
                    <th style="width:18%" class="text-center">Error Qty</th>
                    <th style="width:8%" class="text-center"></th>
                </tr>
            </thead>
            <tbody id="aql-size-breakdown-tbody">
                {{-- Rows rendered by JS --}}
            </tbody>
            <tfoot>
                <tr class="table-light fw-semibold">
                    <td class="text-end text-muted fs-12">Total</td>
                    <td class="text-center" id="aql-sb-total-order">—</td>
                    <td class="text-center" id="aql-sb-total-checked">—</td>
                    <td class="text-center" id="aql-sb-total-error">—</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <small class="text-muted fs-11">
        <i class="feather-info me-1"></i>
        For coverage/defect-rate reporting only — doesn't change the sample size or Ac/Re numbers above.
    </small>

</div>

{{-- ══════════════════════════════════════════════════════════════════════
     AQL Section JavaScript — ISO 2859-1 reference data + distribution
     ══════════════════════════════════════════════════════════════════════ --}}
<script src="{{ asset('assets/js/aql-plan.js') }}"></script>
<script>
(function() {
    'use strict';

    // All sampling numbers come from AqlCalculationService::tableForJs() — no tables here.
    const AQL = window.AqlPlan(@json($aqlJsData));

    // ── Master data from DB ──────────────────────────────────────────────────
    const AQL_COLORS = @json(($colors ?? collect())->pluck('name'));
    const AQL_SIZES  = @json(($sizes  ?? collect())->pluck('name'));

    function buildAqlColorOptions(selected) {
        return '<option value="">— Color —</option>' +
            AQL_COLORS.map(c => `<option value="${escHtml(c)}"${selected === c ? ' selected' : ''}>${escHtml(c)}</option>`).join('');
    }

    function buildAqlSizeOptions(selected) {
        return '<option value="">— Size —</option>' +
            AQL_SIZES.map(s => `<option value="${escHtml(s)}"${selected === s ? ' selected' : ''}>${escHtml(s)}</option>`).join('');
    }

    // ── State ────────────────────────────────────────────────────────────────
    let aqlVariations = @json($a?->variations ?? []);
    if (!Array.isArray(aqlVariations)) aqlVariations = [];
    // Ensure each row has required fields
    aqlVariations = aqlVariations.map(v => ({
        color: v.color || '',
        size:  v.size  || '',
        order_qty: parseInt(v.order_qty) || 0,
        inspect_qty: parseInt(v.inspect_qty) || 0,
    }));

    // ── Helpers ──────────────────────────────────────────────────────────────
    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function distributeProportionally(totalQty, sampleSize, variations) {
        if (totalQty <= 0 || variations.length === 0) return variations.map(() => 0);
        const raw = variations.map(v => (v.order_qty / totalQty) * sampleSize);
        const floored = raw.map(r => Math.floor(r));
        let remainder = sampleSize - floored.reduce((a, b) => a + b, 0);
        const fractionals = raw.map((r, i) => ({ i, frac: r - Math.floor(r) }));
        fractionals.sort((a, b) => b.frac - a.frac);
        for (let j = 0; j < remainder && j < fractionals.length; j++) {
            floored[fractionals[j].i]++;
        }
        return floored;
    }

    // ── Render variations table ──────────────────────────────────────────────
    function aqlRenderVariations() {
        const tbody = document.getElementById('aql-variations-tbody');
        if (!tbody) return;

        if (aqlVariations.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3 fs-12">No variations added. Click <strong>Add Variation</strong> to start.</td></tr>';
        } else {
            tbody.innerHTML = aqlVariations.map((v, i) => `
                <tr>
                    <td>
                        <select class="form-select form-select-sm"
                                name="aql[variations][${i}][color]"
                                onchange="aqlUpdateVariation(${i},'color',this.value)">
                            ${buildAqlColorOptions(v.color)}
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-sm"
                                name="aql[variations][${i}][size]"
                                onchange="aqlUpdateVariation(${i},'size',this.value)">
                            ${buildAqlSizeOptions(v.size)}
                        </select>
                    </td>
                    <td><input type="number" class="form-control form-control-sm text-center"
                               name="aql[variations][${i}][order_qty]"
                               value="${v.order_qty}"
                               min="0"
                               oninput="aqlUpdateVariation(${i},'order_qty',parseInt(this.value)||0)"></td>
                    <td class="text-center">
                        <span class="fw-semibold text-success" id="aql-inspect-qty-${i}">${v.inspect_qty}</span>
                        <input type="hidden" name="aql[variations][${i}][inspect_qty]" id="aql-inspect-qty-hidden-${i}" value="${v.inspect_qty}">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-icon btn-light-danger"
                                onclick="aqlRemoveVariation(${i})" title="Remove">
                            <i class="feather-x"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    }

    // ── Recalculate everything ───────────────────────────────────────────────
    function aqlRecalculate() {
        const level   = document.getElementById('aql_inspection_level')?.value || 'II';
        const crAql   = document.getElementById('aql_aql_critical')?.value || 'not_allowed';
        const maAql   = document.getElementById('aql_aql_major')?.value    || '2.5';
        const miAql   = document.getElementById('aql_aql_minor')?.value    || '4.0';

        const totalOrderQty = aqlVariations.reduce((s, v) => s + (parseInt(v.order_qty) || 0), 0);

        // Display totals
        const totalOrderEl = document.getElementById('aql-total-order-qty');
        if (totalOrderEl) totalOrderEl.textContent = totalOrderQty > 0 ? totalOrderQty.toLocaleString() : '—';

        // Auto-populate lot_size from variations
        const lotSizeEl = document.getElementById('aql_lot_size');
        if (lotSizeEl && totalOrderQty > 0) {
            lotSizeEl.value = totalOrderQty;
        }

        if (totalOrderQty < 2 && aqlVariations.length > 0) return;

        const effectiveLotSize = totalOrderQty > 0
            ? totalOrderQty
            : (parseInt(lotSizeEl?.value) || 0);

        if (effectiveLotSize < 2) return;

        // Units to inspect: enough for every severity's plan (an arrow can move a plan to a larger sample).
        const baseSample = AQL.sampleSize(effectiveLotSize, level, [crAql, maAql, miAql]);
        if (!baseSample) return;

        // Distribute
        if (aqlVariations.length > 0 && totalOrderQty > 0) {
            const dist = distributeProportionally(totalOrderQty, baseSample, aqlVariations);
            dist.forEach((qty, i) => {
                aqlVariations[i].inspect_qty = qty;
                const span  = document.getElementById(`aql-inspect-qty-${i}`);
                const input = document.getElementById(`aql-inspect-qty-hidden-${i}`);
                if (span)  span.textContent = qty;
                if (input) input.value = qty;
            });
            const totalInspect = dist.reduce((a, b) => a + b, 0);
            const totalInspectEl = document.getElementById('aql-total-inspect-qty');
            if (totalInspectEl) totalInspectEl.textContent = totalInspect.toLocaleString();
        }

        // Update sample_size display
        const ssDisplay = document.getElementById('aql_sample_size_display');
        const ssInput   = document.getElementById('aql_sample_size');
        if (ssDisplay) ssDisplay.textContent = baseSample;
        if (ssInput)   ssInput.value = baseSample;

        // Code letter from Table I
        const codeLetter = AQL.codeLetter(effectiveLotSize, level) || '';
        const clDisplay = document.getElementById('aql_code_letter_display');
        const clVal     = document.getElementById('aql_code_letter_val');
        if (clDisplay) clDisplay.textContent = codeLetter || '—';
        if (clVal)     clVal.value = codeLetter;

        // Get AcRe for each type
        const crRes = AQL.plan(crAql, effectiveLotSize, level);
        const maRes = AQL.plan(maAql, effectiveLotSize, level);
        const miRes = AQL.plan(miAql, effectiveLotSize, level);

        function setAcRe(prefix, res, aqlKey) {
            const acEl = document.getElementById(`aql_ac_${prefix}`);
            const reEl = document.getElementById(`aql_re_${prefix}`);
            const ssEl = document.getElementById(`aql_${prefix}_ss_display`);
            const aqEl = document.getElementById(`aql_${prefix}_aql_display`);
            if (acEl && res) acEl.value = res.ac;
            if (reEl && res) reEl.value = res.re;
            if (ssEl && res) ssEl.textContent = res.ss ?? baseSample;
            if (aqEl) aqEl.textContent = aqlKey === 'not_allowed' ? 'Not Allowed' : aqlKey;
        }

        setAcRe('critical', crRes, crAql);
        setAcRe('major',    maRes, maAql);
        setAcRe('minor',    miRes, miAql);

        document.getElementById('aql-result-row')?.classList.remove('d-none');
        updateAqlVerdictLocal();
    }

    // ── Update verdict display ───────────────────────────────────────────────
    function updateAqlVerdictLocal() {
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
        verdictEl.textContent = fail ? 'Fail' : 'Pass';
    }

    // ── Public API (called by inline handlers and runs/edit.blade.php) ──────
    window.aqlUpdateVariation = function(idx, field, value) {
        if (!aqlVariations[idx]) return;
        aqlVariations[idx][field] = value;
        aqlRecalculate();
    };

    window.aqlRemoveVariation = function(idx) {
        aqlVariations.splice(idx, 1);
        aqlRenderVariations();
        aqlRecalculate();
    };

    window.aqlRecalculate = aqlRecalculate;

    // ── Wire up Add Variation button ─────────────────────────────────────────
    document.getElementById('aql-add-variation-btn')?.addEventListener('click', function() {
        aqlVariations.push({ color: '', size: '', order_qty: 0, inspect_qty: 0 });
        aqlRenderVariations();
        aqlRecalculate();
    });

    // ── Per-size checked/error breakdown ─────────────────────────────────────
    let aqlSizeRows = @json($sizeBreakdownRows ?? []);
    if (!Array.isArray(aqlSizeRows)) aqlSizeRows = [];
    aqlSizeRows = aqlSizeRows.map(r => ({
        size_label:  r.size_label || '',
        order_qty:   parseInt(r.order_qty)   || 0,
        checked_qty: parseInt(r.checked_qty) || 0,
        error_qty:   parseInt(r.error_qty)   || 0,
    }));

    function aqlRenderSizeBreakdown() {
        const tbody = document.getElementById('aql-size-breakdown-tbody');
        if (!tbody) return;

        if (aqlSizeRows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3 fs-12">No sizes yet. Click <strong>Add Size</strong> to start.</td></tr>';
        } else {
            tbody.innerHTML = aqlSizeRows.map((r, i) => `
                <tr>
                    <td><input type="text" class="form-control form-control-sm"
                               name="aql[size_breakdown][${i}][size_label]"
                               value="${escHtml(r.size_label)}" placeholder="e.g. M"
                               oninput="aqlUpdateSizeRow(${i},'size_label',this.value)"></td>
                    <td><input type="number" class="form-control form-control-sm text-center"
                               name="aql[size_breakdown][${i}][order_qty]"
                               value="${r.order_qty}" min="0"
                               oninput="aqlUpdateSizeRow(${i},'order_qty',parseInt(this.value)||0)"></td>
                    <td><input type="number" class="form-control form-control-sm text-center"
                               name="aql[size_breakdown][${i}][checked_qty]"
                               value="${r.checked_qty}" min="0"
                               oninput="aqlUpdateSizeRow(${i},'checked_qty',parseInt(this.value)||0)"></td>
                    <td><input type="number" class="form-control form-control-sm text-center"
                               name="aql[size_breakdown][${i}][error_qty]"
                               value="${r.error_qty}" min="0"
                               oninput="aqlUpdateSizeRow(${i},'error_qty',parseInt(this.value)||0)"></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-icon btn-light-danger"
                                onclick="aqlRemoveSizeRow(${i})" title="Remove">
                            <i class="feather-x"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        aqlRecalculateSizeTotals();
    }

    function aqlRecalculateSizeTotals() {
        const totals = aqlSizeRows.reduce((acc, r) => {
            acc.order   += r.order_qty;
            acc.checked += r.checked_qty;
            acc.error   += r.error_qty;
            return acc;
        }, { order: 0, checked: 0, error: 0 });

        const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val > 0 ? val.toLocaleString() : '—'; };
        set('aql-sb-total-order', totals.order);
        set('aql-sb-total-checked', totals.checked);
        set('aql-sb-total-error', totals.error);
    }

    window.aqlUpdateSizeRow = function(idx, field, value) {
        if (!aqlSizeRows[idx]) return;
        aqlSizeRows[idx][field] = value;
        aqlRecalculateSizeTotals();
    };

    window.aqlRemoveSizeRow = function(idx) {
        aqlSizeRows.splice(idx, 1);
        aqlRenderSizeBreakdown();
    };

    document.getElementById('aql-add-size-row-btn')?.addEventListener('click', function() {
        aqlSizeRows.push({ size_label: '', order_qty: 0, checked_qty: 0, error_qty: 0 });
        aqlRenderSizeBreakdown();
    });

    aqlRenderSizeBreakdown();

    // ── Wire up lot_size manual change ───────────────────────────────────────
    document.getElementById('aql_lot_size')?.addEventListener('input', function() {
        if (aqlVariations.length === 0) aqlRecalculate();
    });

    // ── Wire up found defect inputs ──────────────────────────────────────────
    ['aql_found_critical','aql_found_major','aql_found_minor'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updateAqlVerdictLocal);
    });

    // ── Wire up calculate button (AJAX save + recalculate) ───────────────────
    const calculateBtn = document.getElementById('aql-calculate-btn');
    if (calculateBtn) {
        calculateBtn.addEventListener('click', function() {
            // First run local calculation
            aqlRecalculate();

            // Then AJAX for server-side confirmation
            const lotSize = document.getElementById('aql_lot_size')?.value;
            const level   = document.getElementById('aql_inspection_level')?.value;
            const aqlCrit = document.getElementById('aql_aql_critical')?.value;
            const aqlMaj  = document.getElementById('aql_aql_major')?.value;
            const aqlMin  = document.getElementById('aql_aql_minor')?.value;

            if (!lotSize || parseInt(lotSize) < 2) {
                if (aqlVariations.length === 0) alert('Enter lot size or add at least one variation.');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Calculating…';

            fetch('{{ route("inspections.aql.calculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    lot_size:         parseInt(lotSize),
                    inspection_level: level,
                    aql_critical:     (aqlCrit === 'not_allowed') ? null : (parseFloat(aqlCrit) || 0.065),
                    aql_major:        (aqlMaj  === 'not_allowed') ? null : (parseFloat(aqlMaj)  || 2.5),
                    aql_minor:        (aqlMin  === 'not_allowed') ? null : (parseFloat(aqlMin)  || 4.0),
                }),
            })
            .then(r => r.json())
            .then(data => {
                const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
                set('aql_ac_critical', data.critical?.ac);
                set('aql_re_critical', data.critical?.re);
                set('aql_ac_major',    data.major?.ac);
                set('aql_re_major',    data.major?.re);
                set('aql_ac_minor',    data.minor?.ac);
                set('aql_re_minor',    data.minor?.re);
                document.getElementById('aql-result-row')?.classList.remove('d-none');
                updateAqlVerdictLocal();
            })
            .catch(() => {})
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="feather-cpu me-1"></i>Calculate';
            });
        });
    }

    // ── Initial render ───────────────────────────────────────────────────────
    aqlRenderVariations();
    if (aqlVariations.length > 0 || (document.getElementById('aql_lot_size')?.value)) {
        aqlRecalculate();
    }

})();
</script>
