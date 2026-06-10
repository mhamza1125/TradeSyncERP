{{-- AQL Sampling Section — ISO 2859-1 with Quantity Distribution --}}
@php
    $a = $aql; // InspectionRunAql model or null

    // Helper: -1.0 stored as sentinel for "Not Allowed"
    $dispCritical = ($a && $a->aql_critical == -1) ? 'not_allowed' : ($a?->aql_critical ?? 0.065);
    $dispMajor    = ($a && $a->aql_major    == -1) ? 'not_allowed' : ($a?->aql_major    ?? 2.5);
    $dispMinor    = ($a && $a->aql_minor    == -1) ? 'not_allowed' : ($a?->aql_minor    ?? 4.0);
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
                @foreach([0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5] as $lvl)
                    <option value="{{ $lvl }}" @selected(!is_string($dispCritical) && (float)$dispCritical === $lvl)>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-warning text-warning me-1">MA</span> Major AQL
            </label>
            <select id="aql_aql_major" name="aql[aql_major]" class="form-select form-select-sm" onchange="aqlRecalculate()">
                <option value="not_allowed" @selected($dispMajor === 'not_allowed')>Not Allowed</option>
                @foreach([0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5] as $lvl)
                    <option value="{{ $lvl }}" @selected(!is_string($dispMajor) && (float)$dispMajor === $lvl)>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-info text-info me-1">MI</span> Minor AQL
            </label>
            <select id="aql_aql_minor" name="aql[aql_minor]" class="form-select form-select-sm" onchange="aqlRecalculate()">
                <option value="not_allowed" @selected($dispMinor === 'not_allowed')>Not Allowed</option>
                @foreach([0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5] as $lvl)
                    <option value="{{ $lvl }}" @selected(!is_string($dispMinor) && (float)$dispMinor === $lvl)>{{ $lvl }}</option>
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
    <h6 class="fw-semibold mb-3">Defects Found During Inspection</h6>
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

    {{-- Notes --}}
    <div class="mb-0">
        <label class="form-label fw-semibold fs-12">AQL Notes</label>
        <textarea name="aql[notes]" rows="2"
                  class="form-control form-control-sm"
                  placeholder="Sampling observations, deviations, inspector notes…">{{ old('aql.notes', $a?->notes) }}</textarea>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     AQL Section JavaScript — ISO 2859-1 reference data + distribution
     ══════════════════════════════════════════════════════════════════════ --}}
<script>
(function() {
    'use strict';

    // ── Reference AQL data (ISO 2859-1) ─────────────────────────────────────
    const AQL_LOT_RANGES = [
        { Min:2,      Max:8,                  I:2,   II:2,   III:3,   S1:2,  S2:2,  S3:2,  S4:2   },
        { Min:9,      Max:15,                 I:2,   II:3,   III:5,   S1:2,  S2:2,  S3:2,  S4:2   },
        { Min:16,     Max:25,                 I:3,   II:5,   III:8,   S1:2,  S2:2,  S3:3,  S4:3   },
        { Min:26,     Max:50,                 I:5,   II:8,   III:13,  S1:2,  S2:3,  S3:3,  S4:5   },
        { Min:51,     Max:90,                 I:5,   II:13,  III:20,  S1:3,  S2:3,  S3:5,  S4:5   },
        { Min:91,     Max:150,                I:8,   II:20,  III:32,  S1:3,  S2:3,  S3:5,  S4:8   },
        { Min:151,    Max:280,                I:13,  II:32,  III:50,  S1:3,  S2:5,  S3:8,  S4:13  },
        { Min:281,    Max:500,                I:20,  II:50,  III:80,  S1:3,  S2:5,  S3:8,  S4:13  },
        { Min:501,    Max:1200,               I:32,  II:80,  III:125, S1:5,  S2:5,  S3:13, S4:20  },
        { Min:1201,   Max:3200,               I:50,  II:125, III:200, S1:5,  S2:8,  S3:13, S4:32  },
        { Min:3201,   Max:10000,              I:80,  II:200, III:315, S1:5,  S2:8,  S3:20, S4:32  },
        { Min:10001,  Max:35000,              I:125, II:315, III:500, S1:5,  S2:8,  S3:20, S4:50  },
        { Min:35001,  Max:150000,             I:200, II:500, III:800, S1:8,  S2:13, S3:32, S4:80  },
        { Min:150001, Max:500000,             I:315, II:800, III:1250,S1:8,  S2:13, S3:32, S4:80  },
        { Min:500001, Max:Number.MAX_SAFE_INTEGER, I:500, II:1250, III:2000, S1:8, S2:13, S3:50, S4:125 },
    ];

    // code letter → sample size
    const AQL_SAMPLE_SIZES = {
        'A':2,'B':3,'C':5,'D':8,'E':13,'F':20,'G':32,'H':50,
        'J':80,'K':125,'L':200,'M':315,'N':500,'P':800,'Q':1250,'R':2000
    };

    // AQL level → sample-size → {Ac, Re, Ss?}
    const AQL_NUMBERS = {
        "not_allowed": { 2:{Ac:0,Re:1},3:{Ac:0,Re:1},5:{Ac:0,Re:1},8:{Ac:0,Re:1},13:{Ac:0,Re:1},20:{Ac:0,Re:1},32:{Ac:0,Re:1},50:{Ac:0,Re:1},80:{Ac:0,Re:1},125:{Ac:0,Re:1},200:{Ac:0,Re:1},315:{Ac:0,Re:1},500:{Ac:0,Re:1},800:{Ac:0,Re:1},1250:{Ac:0,Re:1},2000:{Ac:0,Re:1} },
        "0.065": { 2:{Ac:0,Re:1,Ss:200},3:{Ac:0,Re:1,Ss:200},5:{Ac:0,Re:1,Ss:200},8:{Ac:0,Re:1,Ss:200},13:{Ac:0,Re:1,Ss:200},20:{Ac:0,Re:1,Ss:200},32:{Ac:0,Re:1,Ss:200},50:{Ac:0,Re:1,Ss:200},80:{Ac:0,Re:1},125:{Ac:0,Re:1},200:{Ac:0,Re:1},315:{Ac:0,Re:1,Ss:200},500:{Ac:1,Re:2,Ss:800},800:{Ac:1,Re:2},1250:{Ac:2,Re:3},2000:{Ac:3,Re:4} },
        "0.10":  { 2:{Ac:0,Re:1,Ss:125},3:{Ac:0,Re:1,Ss:125},5:{Ac:0,Re:1,Ss:125},8:{Ac:0,Re:1,Ss:125},13:{Ac:0,Re:1,Ss:125},20:{Ac:0,Re:1,Ss:125},32:{Ac:0,Re:1,Ss:125},50:{Ac:0,Re:1,Ss:125},80:{Ac:0,Re:1,Ss:125},125:{Ac:0,Re:1},200:{Ac:0,Re:1,Ss:125},315:{Ac:1,Re:2,Ss:500},500:{Ac:1,Re:2},800:{Ac:2,Re:3},1250:{Ac:3,Re:4},2000:{Ac:5,Re:6} },
        "0.15":  { 2:{Ac:0,Re:1,Ss:80},3:{Ac:0,Re:1,Ss:80},5:{Ac:0,Re:1,Ss:80},8:{Ac:0,Re:1,Ss:80},13:{Ac:0,Re:1,Ss:80},20:{Ac:0,Re:1,Ss:80},32:{Ac:0,Re:1,Ss:80},50:{Ac:0,Re:1,Ss:80},80:{Ac:0,Re:1},125:{Ac:0,Re:1,Ss:80},200:{Ac:1,Re:2,Ss:315},315:{Ac:1,Re:2},500:{Ac:2,Re:3},800:{Ac:3,Re:4},1250:{Ac:5,Re:6},2000:{Ac:7,Re:8} },
        "0.25":  { 2:{Ac:0,Re:1,Ss:50},3:{Ac:0,Re:1,Ss:50},5:{Ac:0,Re:1,Ss:50},8:{Ac:0,Re:1,Ss:50},13:{Ac:0,Re:1,Ss:50},20:{Ac:0,Re:1,Ss:50},32:{Ac:0,Re:1,Ss:50},50:{Ac:0,Re:1},80:{Ac:0,Re:1,Ss:50},125:{Ac:1,Re:2,Ss:200},200:{Ac:1,Re:2},315:{Ac:2,Re:3},500:{Ac:3,Re:4},800:{Ac:5,Re:6},1250:{Ac:7,Re:8},2000:{Ac:10,Re:11} },
        "0.40":  { 2:{Ac:0,Re:1,Ss:32},3:{Ac:0,Re:1,Ss:32},5:{Ac:0,Re:1,Ss:32},8:{Ac:0,Re:1,Ss:32},13:{Ac:0,Re:1,Ss:32},20:{Ac:0,Re:1,Ss:32},32:{Ac:0,Re:1},50:{Ac:0,Re:1,Ss:32},80:{Ac:1,Re:2,Ss:125},125:{Ac:1,Re:2},200:{Ac:2,Re:3},315:{Ac:3,Re:4},500:{Ac:5,Re:6},800:{Ac:7,Re:8},1250:{Ac:10,Re:11},2000:{Ac:14,Re:15} },
        "0.65":  { 2:{Ac:0,Re:1,Ss:20},3:{Ac:0,Re:1,Ss:20},5:{Ac:0,Re:1,Ss:20},8:{Ac:0,Re:1,Ss:20},13:{Ac:0,Re:1,Ss:20},20:{Ac:0,Re:1},32:{Ac:0,Re:1,Ss:20},50:{Ac:1,Re:2,Ss:80},80:{Ac:1,Re:2},125:{Ac:2,Re:3},200:{Ac:3,Re:4},315:{Ac:5,Re:6},500:{Ac:7,Re:8},800:{Ac:10,Re:11},1250:{Ac:14,Re:15},2000:{Ac:21,Re:22} },
        "1.0":   { 2:{Ac:0,Re:1,Ss:13},3:{Ac:0,Re:1,Ss:13},5:{Ac:0,Re:1,Ss:13},8:{Ac:0,Re:1,Ss:13},13:{Ac:0,Re:1},20:{Ac:0,Re:1,Ss:13},32:{Ac:1,Re:2,Ss:50},50:{Ac:1,Re:2},80:{Ac:2,Re:3},125:{Ac:3,Re:4},200:{Ac:5,Re:6},315:{Ac:7,Re:8},500:{Ac:10,Re:11},800:{Ac:14,Re:15},1250:{Ac:21,Re:22},2000:{Ac:21,Re:22,Ss:1250} },
        "1.5":   { 2:{Ac:0,Re:1,Ss:8},3:{Ac:0,Re:1,Ss:8},5:{Ac:0,Re:1,Ss:8},8:{Ac:0,Re:1},13:{Ac:0,Re:1,Ss:8},20:{Ac:1,Re:2,Ss:32},32:{Ac:1,Re:2},50:{Ac:2,Re:3},80:{Ac:3,Re:4},125:{Ac:5,Re:6},200:{Ac:7,Re:8},315:{Ac:10,Re:11},500:{Ac:14,Re:15},800:{Ac:21,Re:22},1250:{Ac:21,Re:22,Ss:800},2000:{Ac:21,Re:22,Ss:800} },
        "2.5":   { 2:{Ac:0,Re:1,Ss:5},3:{Ac:0,Re:1,Ss:5},5:{Ac:0,Re:1},8:{Ac:0,Re:1,Ss:5},13:{Ac:1,Re:2,Ss:20},20:{Ac:1,Re:2},32:{Ac:2,Re:3},50:{Ac:3,Re:4},80:{Ac:5,Re:6},125:{Ac:7,Re:8},200:{Ac:10,Re:11},315:{Ac:14,Re:15},500:{Ac:21,Re:22},800:{Ac:21,Re:22,Ss:500},1250:{Ac:21,Re:22,Ss:500},2000:{Ac:21,Re:22,Ss:500} },
        "4.0":   { 2:{Ac:0,Re:1,Ss:3},3:{Ac:0,Re:1},5:{Ac:0,Re:1,Ss:3},8:{Ac:1,Re:2,Ss:13},13:{Ac:1,Re:2},20:{Ac:2,Re:3},32:{Ac:3,Re:4},50:{Ac:5,Re:6},80:{Ac:7,Re:8},125:{Ac:10,Re:11},200:{Ac:14,Re:15},315:{Ac:21,Re:22},500:{Ac:21,Re:22,Ss:315},800:{Ac:21,Re:22,Ss:315},1250:{Ac:21,Re:22,Ss:315},2000:{Ac:21,Re:22,Ss:315} },
        "6.5":   { 2:{Ac:0,Re:1},3:{Ac:0,Re:1,Ss:2},5:{Ac:1,Re:2,Ss:8},8:{Ac:1,Re:2},13:{Ac:2,Re:3},20:{Ac:3,Re:4},32:{Ac:5,Re:6},50:{Ac:7,Re:8},80:{Ac:10,Re:11},125:{Ac:14,Re:15},200:{Ac:21,Re:22},315:{Ac:21,Re:22,Ss:200},500:{Ac:21,Re:22,Ss:200},800:{Ac:21,Re:22,Ss:200},1250:{Ac:21,Re:22,Ss:200},2000:{Ac:21,Re:22,Ss:200} }
    };

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

    function getBaseSample(totalQty, level) {
        const row = AQL_LOT_RANGES.find(r => totalQty >= r.Min && totalQty <= r.Max);
        if (!row) return null;
        const ss = row[level];
        return ss ? Math.min(ss, totalQty) : null;
    }

    function getAcRe(aqlKey, baseSample, totalQty) {
        const numKey = String(aqlKey);
        const tbl = AQL_NUMBERS[numKey];
        if (!tbl) return null;
        const entry = tbl[baseSample];
        if (!entry) return null;
        const effectiveSs = entry.Ss ? Math.min(entry.Ss, totalQty) : baseSample;
        return { ac: entry.Ac, re: entry.Re, ss: effectiveSs };
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
                    <td><input type="text" class="form-control form-control-sm"
                               name="aql[variations][${i}][color]"
                               value="${escHtml(v.color)}"
                               onchange="aqlUpdateVariation(${i},'color',this.value)"
                               placeholder="e.g. Red"></td>
                    <td><input type="text" class="form-control form-control-sm"
                               name="aql[variations][${i}][size]"
                               value="${escHtml(v.size)}"
                               onchange="aqlUpdateVariation(${i},'size',this.value)"
                               placeholder="e.g. M"></td>
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

        const baseSample = getBaseSample(effectiveLotSize, level);
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

        // Resolve code letter for display
        const row = AQL_LOT_RANGES.find(r => effectiveLotSize >= r.Min && effectiveLotSize <= r.Max);
        // Reverse-lookup code from sample size (approximate, from level)
        const reverseSS = row ? row[level] : null;
        const codeLetter = reverseSS ? (Object.entries(AQL_SAMPLE_SIZES).find(([,v]) => v === reverseSS)?.[0] || '') : '';
        const clDisplay = document.getElementById('aql_code_letter_display');
        const clVal     = document.getElementById('aql_code_letter_val');
        if (clDisplay) clDisplay.textContent = codeLetter || '—';
        if (clVal)     clVal.value = codeLetter;

        // Get AcRe for each type
        const crRes = getAcRe(crAql, baseSample, effectiveLotSize);
        const maRes = getAcRe(maAql, baseSample, effectiveLotSize);
        const miRes = getAcRe(miAql, baseSample, effectiveLotSize);

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
