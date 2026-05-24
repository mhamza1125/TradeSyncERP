@extends('index')

@section('title', 'AQL Calculator - TradeSyncERP')

@push('styles')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">AQL Sampling Calculator</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Tools</li>
                <li class="breadcrumb-item">AQL Calculator</li>
            </ul>
        </div>
    </div>

    <div class="main-content pb-4" x-data="aqlCalc()" x-init="init()">

        {{-- ── Intro banner ──────────────────────────────────────────────────── --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="card border-0 bg-soft-primary">
                    <div class="card-body py-3 d-flex align-items-center gap-3">
                        <div class="avatar-text avatar-md bg-primary text-white rounded">
                            <i class="feather-cpu"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">ISO 2859-1 Acceptance Sampling Calculator</div>
                            <div class="fs-12 text-muted">Enter lot size and AQL levels to calculate the sampling plan for Critical, Major, and Minor defects.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            {{-- ── Input Panel ──────────────────────────────────────────────── --}}
            <div class="col-xl-4 col-lg-5">
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
                            <input type="number" class="form-control" x-model.number="lotSize"
                                   min="2" placeholder="e.g. 5000" @input="calculate()">
                            <div class="form-text fs-11" x-show="codeLetter">
                                Code Letter: <strong x-text="codeLetter"></strong> —
                                Sample Size: <strong x-text="sampleSize"></strong> units
                            </div>
                        </div>

                        {{-- Inspection Level --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-12">Inspection Level</label>
                            <div class="d-flex flex-wrap gap-2">
                                <template x-for="lvl in ['I','II','III','S1','S2','S3','S4']" :key="lvl">
                                    <button type="button"
                                            class="btn btn-sm"
                                            :class="level === lvl ? 'btn-primary' : 'btn-light'"
                                            @click="level = lvl; calculate()"
                                            x-text="lvl">
                                    </button>
                                </template>
                            </div>
                            <div class="form-text fs-11 mt-1">
                                General: I · II · III &nbsp;|&nbsp; Special: S1 – S4
                            </div>
                        </div>

                        <hr>

                        {{-- Critical AQL --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-12">
                                <span class="badge bg-soft-danger text-danger me-1">CR</span> Critical AQL
                            </label>
                            <select class="form-select form-select-sm" x-model="aqlCritical" @change="calculate()">
                                <option value="not_allowed">Not Allowed</option>
                                <template x-for="lvl in supportedAqls" :key="'cr-'+lvl">
                                    <option :value="lvl" x-text="lvl"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Major AQL --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-12">
                                <span class="badge bg-soft-warning text-warning me-1">MA</span> Major AQL
                            </label>
                            <select class="form-select form-select-sm" x-model="aqlMajor" @change="calculate()">
                                <option value="not_allowed">Not Allowed</option>
                                <template x-for="lvl in supportedAqls" :key="'ma-'+lvl">
                                    <option :value="lvl" x-text="lvl"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Minor AQL --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-12">
                                <span class="badge bg-soft-info text-info me-1">MI</span> Minor AQL
                            </label>
                            <select class="form-select form-select-sm" x-model="aqlMinor" @change="calculate()">
                                <option value="not_allowed">Not Allowed</option>
                                <template x-for="lvl in supportedAqls" :key="'mi-'+lvl">
                                    <option :value="lvl" x-text="lvl"></option>
                                </template>
                            </select>
                        </div>

                        <hr>

                        {{-- Defects found --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-12">Defects Found (for Verdict)</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <label class="form-label fs-11 text-danger mb-1">Critical</label>
                                    <input type="number" class="form-control form-control-sm text-center"
                                           x-model.number="foundCritical" min="0" @input="calcVerdict()">
                                </div>
                                <div class="col-4">
                                    <label class="form-label fs-11 text-warning mb-1">Major</label>
                                    <input type="number" class="form-control form-control-sm text-center"
                                           x-model.number="foundMajor" min="0" @input="calcVerdict()">
                                </div>
                                <div class="col-4">
                                    <label class="form-label fs-11 text-info mb-1">Minor</label>
                                    <input type="number" class="form-control form-control-sm text-center"
                                           x-model.number="foundMinor" min="0" @input="calcVerdict()">
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary w-100" @click="calculate()">
                            <i class="feather-cpu me-2"></i>Calculate Plan
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Results Panel ────────────────────────────────────────────── --}}
            <div class="col-xl-8 col-lg-7">

                {{-- Sampling Summary Card --}}
                <div class="card mb-4" x-show="calculated">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="feather-bar-chart-2 me-2 text-primary"></i>Sampling Plan</h5>
                        <div>
                            <span class="badge bg-soft-secondary text-secondary me-1">
                                Level: <strong x-text="level"></strong>
                            </span>
                            <span class="badge bg-soft-primary text-primary me-1">
                                Code: <strong x-text="codeLetter"></strong>
                            </span>
                            <span class="badge bg-soft-success text-success">
                                n = <strong x-text="sampleSize"></strong>
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
                                <tbody>
                                    {{-- Critical --}}
                                    <tr>
                                        <td>
                                            <span class="badge bg-soft-danger text-danger">Critical</span>
                                        </td>
                                        <td class="text-center" x-text="aqlCritical === 'not_allowed' ? 'Not Allowed' : aqlCritical"></td>
                                        <td class="text-center fw-semibold" x-text="crResult.ss ?? sampleSize"></td>
                                        <td class="text-center">
                                            <span x-show="aqlCritical === 'not_allowed'" class="badge bg-soft-danger text-danger">0</span>
                                            <span x-show="aqlCritical !== 'not_allowed'" x-text="crResult.ac !== null ? crResult.ac : '—'"></span>
                                        </td>
                                        <td class="text-center">
                                            <span x-show="aqlCritical === 'not_allowed'" class="badge bg-soft-danger text-danger">1</span>
                                            <span x-show="aqlCritical !== 'not_allowed'" x-text="crResult.re !== null ? crResult.re : '—'"></span>
                                        </td>
                                        <td class="text-center" x-text="foundCritical"></td>
                                        <td class="text-center">
                                            <span class="badge"
                                                  :class="getRowStatus('critical').class"
                                                  x-text="getRowStatus('critical').label"></span>
                                        </td>
                                    </tr>

                                    {{-- Major --}}
                                    <tr>
                                        <td>
                                            <span class="badge bg-soft-warning text-warning">Major</span>
                                        </td>
                                        <td class="text-center" x-text="aqlMajor === 'not_allowed' ? 'Not Allowed' : aqlMajor"></td>
                                        <td class="text-center fw-semibold" x-text="maResult.ss ?? sampleSize"></td>
                                        <td class="text-center">
                                            <span x-show="aqlMajor === 'not_allowed'" class="badge bg-soft-danger text-danger">0</span>
                                            <span x-show="aqlMajor !== 'not_allowed'" x-text="maResult.ac !== null ? maResult.ac : '—'"></span>
                                        </td>
                                        <td class="text-center">
                                            <span x-show="aqlMajor === 'not_allowed'" class="badge bg-soft-danger text-danger">1</span>
                                            <span x-show="aqlMajor !== 'not_allowed'" x-text="maResult.re !== null ? maResult.re : '—'"></span>
                                        </td>
                                        <td class="text-center" x-text="foundMajor"></td>
                                        <td class="text-center">
                                            <span class="badge"
                                                  :class="getRowStatus('major').class"
                                                  x-text="getRowStatus('major').label"></span>
                                        </td>
                                    </tr>

                                    {{-- Minor --}}
                                    <tr>
                                        <td>
                                            <span class="badge bg-soft-info text-info">Minor</span>
                                        </td>
                                        <td class="text-center" x-text="aqlMinor === 'not_allowed' ? 'Not Allowed' : aqlMinor"></td>
                                        <td class="text-center fw-semibold" x-text="miResult.ss ?? sampleSize"></td>
                                        <td class="text-center">
                                            <span x-show="aqlMinor === 'not_allowed'" class="badge bg-soft-danger text-danger">0</span>
                                            <span x-show="aqlMinor !== 'not_allowed'" x-text="miResult.ac !== null ? miResult.ac : '—'"></span>
                                        </td>
                                        <td class="text-center">
                                            <span x-show="aqlMinor === 'not_allowed'" class="badge bg-soft-danger text-danger">1</span>
                                            <span x-show="aqlMinor !== 'not_allowed'" x-text="miResult.re !== null ? miResult.re : '—'"></span>
                                        </td>
                                        <td class="text-center" x-text="foundMinor"></td>
                                        <td class="text-center">
                                            <span class="badge"
                                                  :class="getRowStatus('minor').class"
                                                  x-text="getRowStatus('minor').label"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Overall Verdict Card --}}
                <div class="card mb-4" x-show="calculated">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-lg rounded"
                                 :class="verdict === 'Pass' ? 'bg-soft-success text-success' : (verdict === 'Fail' ? 'bg-soft-danger text-danger' : 'bg-soft-secondary text-secondary')">
                                <i :class="verdict === 'Pass' ? 'feather-check-circle' : (verdict === 'Fail' ? 'feather-x-circle' : 'feather-clock')"></i>
                            </div>
                            <div>
                                <div class="fs-12 text-muted">Overall AQL Verdict</div>
                                <div class="fs-4 fw-bold"
                                     :class="verdict === 'Pass' ? 'text-success' : (verdict === 'Fail' ? 'text-danger' : 'text-secondary')"
                                     x-text="verdict">
                                </div>
                            </div>
                            <div class="ms-auto text-muted fs-12 text-end">
                                <div>Lot: <strong x-text="lotSize.toLocaleString()"></strong> units</div>
                                <div>Sample: <strong x-text="sampleSize"></strong> units</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Empty state --}}
                <div class="card" x-show="!calculated">
                    <div class="card-body text-center py-5 text-muted">
                        <i class="feather-cpu" style="font-size:3rem; opacity:.25;"></i>
                        <div class="mt-3">Enter a lot size and click <strong>Calculate Plan</strong> to see the sampling plan.</div>
                    </div>
                </div>

                {{-- Reference Table --}}
                <div class="card" x-show="calculated">
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
                                <tbody>
                                    @php
                                        $lotRows = [
                                            [2, 8], [9, 15], [16, 25], [26, 50], [51, 90],
                                            [91, 150], [151, 280], [281, 500], [501, 1200],
                                            [1201, 3200], [3201, 10000], [10001, 35000],
                                            [35001, 150000], [150001, 500000], [500001, null],
                                        ];
                                        $lotTable = $aqlJsData['lotSizeTable'];
                                        $sampleSizes = $aqlJsData['sampleSizes'];
                                    @endphp
                                    @foreach($lotTable as $row)
                                    @php
                                        $rangeLabel = number_format($row[0]) . ' – ' . ($row[1] >= PHP_INT_MAX ? '∞' : number_format($row[1]));
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $rangeLabel }}</td>
                                        @foreach([2,3,4,5,6,7,8] as $colIdx)
                                        <td class="text-center">
                                            {{ $row[$colIdx] }}
                                            <span class="text-muted">({{ $sampleSizes[$row[$colIdx]] ?? '?' }})</span>
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const AQL_DATA = @json($aqlJsData);

    function aqlCalc() {
        return {
            lotSize:       0,
            level:         'II',
            aqlCritical:   '0.065',
            aqlMajor:      '2.5',
            aqlMinor:      '4.0',
            foundCritical: 0,
            foundMajor:    0,
            foundMinor:    0,

            codeLetter: '',
            sampleSize: 0,
            crResult:   { ac: null, re: null, ss: null },
            maResult:   { ac: null, re: null, ss: null },
            miResult:   { ac: null, re: null, ss: null },
            verdict:    'Pending',
            calculated: false,

            supportedAqls: [],

            init() {
                this.supportedAqls = AQL_DATA.supportedAqls.map(v => String(v));
            },

            // Map level name to column index in lotSizeTable
            levelColIndex() {
                const map = { I:2, II:3, III:4, S1:5, S2:6, S3:7, S4:8 };
                return map[this.level] ?? 3;
            },

            resolveCodeLetter() {
                if (!this.lotSize || this.lotSize < 2) return null;
                const colIdx = this.levelColIndex();
                for (const row of AQL_DATA.lotSizeTable) {
                    if (this.lotSize >= row[0] && this.lotSize <= row[1]) {
                        return row[colIdx];
                    }
                }
                return null;
            },

            resolveAcRe(aqlKey, ss) {
                if (aqlKey === 'not_allowed') return { ac: 0, re: 1, ss: null };
                const numKey = parseFloat(aqlKey);
                const tableKey = Object.keys(AQL_DATA.aqlTable).find(k => Math.abs(parseFloat(k) - numKey) < 0.001);
                if (!tableKey) return { ac: null, re: null, ss: null };
                const row = AQL_DATA.aqlTable[tableKey];
                if (row[ss] !== null && row[ss] !== undefined) {
                    return { ac: row[ss][0], re: row[ss][1], ss: null };
                }
                // Arrow up: find next larger sample size
                const sizes = Object.keys(row).map(Number).sort((a, b) => a - b);
                for (const sz of sizes) {
                    if (sz > ss && row[sz] !== null) {
                        return { ac: row[sz][0], re: row[sz][1], ss: sz };
                    }
                }
                return { ac: null, re: null, ss: null };
            },

            calculate() {
                if (!this.lotSize || this.lotSize < 2) {
                    this.calculated = false;
                    return;
                }
                const code = this.resolveCodeLetter();
                if (!code) { this.calculated = false; return; }
                this.codeLetter = code;
                this.sampleSize = AQL_DATA.sampleSizes[code] ?? 0;
                const ss = Math.min(this.sampleSize, this.lotSize);

                this.crResult = this.resolveAcRe(this.aqlCritical, ss);
                this.maResult = this.resolveAcRe(this.aqlMajor,    ss);
                this.miResult = this.resolveAcRe(this.aqlMinor,    ss);
                this.calculated = true;
                this.calcVerdict();
            },

            calcVerdict() {
                if (!this.calculated) return;
                const f = this.foundCritical + this.foundMajor + this.foundMinor;
                if (f === 0) { this.verdict = 'Pending'; return; }

                const fail =
                    (this.aqlCritical === 'not_allowed' && this.foundCritical > 0) ||
                    (this.aqlCritical !== 'not_allowed' && this.crResult.ac !== null && this.foundCritical > this.crResult.ac) ||
                    (this.aqlMajor    === 'not_allowed' && this.foundMajor    > 0) ||
                    (this.aqlMajor    !== 'not_allowed' && this.maResult.ac !== null && this.foundMajor    > this.maResult.ac) ||
                    (this.aqlMinor    === 'not_allowed' && this.foundMinor    > 0) ||
                    (this.aqlMinor    !== 'not_allowed' && this.miResult.ac !== null && this.foundMinor    > this.miResult.ac);

                this.verdict = fail ? 'Fail' : 'Pass';
            },

            getRowStatus(type) {
                let found, ac, aqlKey;
                if (type === 'critical') { found = this.foundCritical; ac = this.crResult.ac; aqlKey = this.aqlCritical; }
                else if (type === 'major') { found = this.foundMajor;  ac = this.maResult.ac; aqlKey = this.aqlMajor; }
                else                       { found = this.foundMinor;  ac = this.miResult.ac; aqlKey = this.aqlMinor; }

                if (found === 0 && (aqlKey === 'not_allowed' || ac === null)) {
                    return { label: 'Pending', class: 'bg-soft-secondary text-secondary' };
                }
                if (aqlKey === 'not_allowed' && found > 0) {
                    return { label: 'Fail', class: 'bg-soft-danger text-danger' };
                }
                if (ac !== null) {
                    if (found > ac) return { label: 'Fail', class: 'bg-soft-danger text-danger' };
                    if (found === 0) return { label: 'Pending', class: 'bg-soft-secondary text-secondary' };
                    return { label: 'Pass', class: 'bg-soft-success text-success' };
                }
                return { label: 'Pending', class: 'bg-soft-secondary text-secondary' };
            },
        };
    }
</script>
@endpush
