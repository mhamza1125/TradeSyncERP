@extends('index')

@section('title', 'AQL Calculator - TradeSyncERP')

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

    <div class="main-content pb-4">

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
                            <div class="fs-12 text-muted">Calculate sampling plans and distribute inspection quantities across color/size variations.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Tabs ──────────────────────────────────────────────────────────── --}}
        <ul class="nav nav-tabs mb-4" id="aqlTabs">
            <li class="nav-item">
                <a class="nav-link active" id="tab-calculator" data-bs-toggle="tab" href="#panel-calculator">
                    <i class="feather-cpu me-1"></i>AQL Calculator
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab-distribution" data-bs-toggle="tab" href="#panel-distribution">
                    <i class="feather-grid me-1"></i>Quantity Distribution
                </a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ══════════════════════════════════════════════════════════
                 TAB 1 — AQL Calculator
                 ══════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade show active" id="panel-calculator">
                <div class="row">

                    {{-- ── Input Panel ────────────────────────────────────────── --}}
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
                                    <input type="number" class="form-control" id="calc-lot-size"
                                           min="2" placeholder="e.g. 5000" oninput="calcRun()">
                                    <div class="form-text fs-11" id="calc-code-info" style="display:none">
                                        Code Letter: <strong id="calc-code-letter"></strong> —
                                        Base Sample: <strong id="calc-base-sample"></strong> units
                                    </div>
                                </div>

                                {{-- Inspection Level --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-12">Inspection Level</label>
                                    <div class="d-flex flex-wrap gap-2" id="calc-level-btns">
                                        @foreach(['I','II','III','S1','S2','S3','S4'] as $lvl)
                                        <button type="button"
                                                class="btn btn-sm calc-level-btn {{ $lvl === 'II' ? 'btn-primary' : 'btn-light' }}"
                                                data-level="{{ $lvl }}"
                                                onclick="calcSetLevel('{{ $lvl }}')"
                                                x-text="'{{ $lvl }}'">{{ $lvl }}</button>
                                        @endforeach
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
                                    <select class="form-select form-select-sm" id="calc-aql-critical" onchange="calcRun()">
                                        <option value="not_allowed">Not Allowed</option>
                                        <option value="0.065">0.065</option>
                                        <option value="0.10">0.10</option>
                                        <option value="0.15">0.15</option>
                                        <option value="0.25">0.25</option>
                                        <option value="0.40">0.40</option>
                                        <option value="0.65">0.65</option>
                                        <option value="1.0">1.0</option>
                                        <option value="1.5">1.5</option>
                                        <option value="2.5" selected>2.5</option>
                                        <option value="4.0">4.0</option>
                                        <option value="6.5">6.5</option>
                                    </select>
                                </div>

                                {{-- Major AQL --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-12">
                                        <span class="badge bg-soft-warning text-warning me-1">MA</span> Major AQL
                                    </label>
                                    <select class="form-select form-select-sm" id="calc-aql-major" onchange="calcRun()">
                                        <option value="not_allowed">Not Allowed</option>
                                        <option value="0.065">0.065</option>
                                        <option value="0.10">0.10</option>
                                        <option value="0.15">0.15</option>
                                        <option value="0.25">0.25</option>
                                        <option value="0.40">0.40</option>
                                        <option value="0.65">0.65</option>
                                        <option value="1.0">1.0</option>
                                        <option value="1.5">1.5</option>
                                        <option value="2.5" selected>2.5</option>
                                        <option value="4.0">4.0</option>
                                        <option value="6.5">6.5</option>
                                    </select>
                                </div>

                                {{-- Minor AQL --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-12">
                                        <span class="badge bg-soft-info text-info me-1">MI</span> Minor AQL
                                    </label>
                                    <select class="form-select form-select-sm" id="calc-aql-minor" onchange="calcRun()">
                                        <option value="not_allowed">Not Allowed</option>
                                        <option value="0.065">0.065</option>
                                        <option value="0.10">0.10</option>
                                        <option value="0.15">0.15</option>
                                        <option value="0.25">0.25</option>
                                        <option value="0.40">0.40</option>
                                        <option value="0.65">0.65</option>
                                        <option value="1.0">1.0</option>
                                        <option value="1.5">1.5</option>
                                        <option value="2.5" selected>2.5</option>
                                        <option value="4.0">4.0</option>
                                        <option value="6.5">6.5</option>
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
                                                   id="calc-found-critical" min="0" value="0" oninput="calcRun()">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label fs-11 text-warning mb-1">Major</label>
                                            <input type="number" class="form-control form-control-sm text-center"
                                                   id="calc-found-major" min="0" value="0" oninput="calcRun()">
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label fs-11 text-info mb-1">Minor</label>
                                            <input type="number" class="form-control form-control-sm text-center"
                                                   id="calc-found-minor" min="0" value="0" oninput="calcRun()">
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-primary w-100" onclick="calcRun()">
                                    <i class="feather-cpu me-2"></i>Calculate Plan
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ── Results Panel ─────────────────────────────────────── --}}
                    <div class="col-xl-8 col-lg-7">

                        {{-- Sampling Plan --}}
                        <div class="card mb-4" id="calc-result-card" style="display:none">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="feather-bar-chart-2 me-2 text-primary"></i>Sampling Plan</h5>
                                <div class="d-flex align-items-center gap-2">
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
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportCalcPDF()">
                                        <i class="feather-download me-1"></i>Export PDF
                                    </button>
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
                                        <tbody id="calc-result-tbody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Overall Verdict --}}
                        <div class="card mb-4" id="calc-verdict-card" style="display:none">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-text avatar-lg rounded" id="calc-verdict-icon">
                                        <i class="feather-clock"></i>
                                    </div>
                                    <div>
                                        <div class="fs-12 text-muted">Overall AQL Verdict</div>
                                        <div class="fs-4 fw-bold" id="calc-verdict-text">Pending</div>
                                    </div>
                                    <div class="ms-auto text-muted fs-12 text-end">
                                        <div>Lot: <strong id="calc-verdict-lot">—</strong> units</div>
                                        <div>Sample: <strong id="calc-verdict-sample">—</strong> units</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Empty state --}}
                        <div class="card" id="calc-empty-card">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="feather-cpu" style="font-size:3rem; opacity:.25;"></i>
                                <div class="mt-3">Enter a lot size and click <strong>Calculate Plan</strong> to see the sampling plan.</div>
                            </div>
                        </div>

                        {{-- Reference Table --}}
                        <div class="card" id="calc-ref-card" style="display:none">
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
                    </div>
                </div>
            </div>{{-- /panel-calculator --}}


            {{-- ══════════════════════════════════════════════════════════
                 TAB 2 — Quantity Distribution
                 ══════════════════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="panel-distribution">
                <div class="row">

                    {{-- Inputs --}}
                    <div class="col-xl-4 col-lg-5">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title"><i class="feather-sliders me-2 text-primary"></i>AQL Settings</h5>
                            </div>
                            <div class="card-body">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-12">Inspection Level</label>
                                    <div class="d-flex flex-wrap gap-2" id="dist-level-btns">
                                        @foreach(['I','II','III','S1','S2','S3','S4'] as $lvl)
                                        <button type="button"
                                                class="btn btn-sm dist-level-btn {{ $lvl === 'II' ? 'btn-primary' : 'btn-light' }}"
                                                data-level="{{ $lvl }}"
                                                onclick="distSetLevel('{{ $lvl }}')">{{ $lvl }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-12"><span class="badge bg-soft-danger text-danger me-1">CR</span> Critical AQL</label>
                                    <select class="form-select form-select-sm" id="dist-aql-critical" onchange="distRecalc()">
                                        <option value="not_allowed">Not Allowed</option>
                                        <option value="0.065">0.065</option><option value="0.10">0.10</option>
                                        <option value="0.15">0.15</option><option value="0.25">0.25</option>
                                        <option value="0.40">0.40</option><option value="0.65">0.65</option>
                                        <option value="1.0">1.0</option><option value="1.5">1.5</option>
                                        <option value="2.5" selected>2.5</option>
                                        <option value="4.0">4.0</option><option value="6.5">6.5</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-12"><span class="badge bg-soft-warning text-warning me-1">MA</span> Major AQL</label>
                                    <select class="form-select form-select-sm" id="dist-aql-major" onchange="distRecalc()">
                                        <option value="not_allowed">Not Allowed</option>
                                        <option value="0.065">0.065</option><option value="0.10">0.10</option>
                                        <option value="0.15">0.15</option><option value="0.25">0.25</option>
                                        <option value="0.40">0.40</option><option value="0.65">0.65</option>
                                        <option value="1.0">1.0</option><option value="1.5">1.5</option>
                                        <option value="2.5" selected>2.5</option>
                                        <option value="4.0">4.0</option><option value="6.5">6.5</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold fs-12"><span class="badge bg-soft-info text-info me-1">MI</span> Minor AQL</label>
                                    <select class="form-select form-select-sm" id="dist-aql-minor" onchange="distRecalc()">
                                        <option value="not_allowed">Not Allowed</option>
                                        <option value="0.065">0.065</option><option value="0.10">0.10</option>
                                        <option value="0.15">0.15</option><option value="0.25">0.25</option>
                                        <option value="0.40">0.40</option><option value="0.65">0.65</option>
                                        <option value="1.0">1.0</option><option value="1.5">1.5</option>
                                        <option value="2.5" selected>2.5</option>
                                        <option value="4.0">4.0</option><option value="6.5">6.5</option>
                                    </select>
                                </div>

                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="feather-list me-2 text-primary"></i>Variations</h5>
                                <button type="button" class="btn btn-sm btn-light-primary" onclick="distAddRow()">
                                    <i class="feather-plus me-1"></i>Add Variation
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Color</th>
                                                <th>Size</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-center" style="width:36px"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="dist-rows-tbody">
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light fw-semibold">
                                                <td colspan="2" class="text-end text-muted fs-12">Total</td>
                                                <td class="text-center" id="dist-total-qty">—</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Distribution Results --}}
                    <div class="col-xl-8 col-lg-7">

                        <div class="card mb-4" id="dist-empty-card">
                            <div class="card-body text-center py-5 text-muted">
                                <i class="feather-grid" style="font-size:3rem; opacity:.25;"></i>
                                <div class="mt-3">Add variations with quantities to see the inspection distribution.</div>
                            </div>
                        </div>

                        <div id="dist-result-section" style="display:none">

                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="exportDistPDF()">
                                    <i class="feather-download me-1"></i>Export PDF
                                </button>
                            </div>

                            {{-- Summary --}}
                            <div class="row g-3 mb-4">
                                <div class="col-4">
                                    <div class="card text-center border-0 bg-soft-secondary">
                                        <div class="card-body py-3">
                                            <div class="fs-11 text-muted mb-1">Total Order Qty</div>
                                            <div class="fw-bold fs-4" id="dist-res-total-qty">0</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card text-center border-0 bg-soft-primary">
                                        <div class="card-body py-3">
                                            <div class="fs-11 text-muted mb-1">Inspection Sample</div>
                                            <div class="fw-bold fs-4 text-primary" id="dist-res-sample">0</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card text-center border-0 bg-soft-info">
                                        <div class="card-body py-3">
                                            <div class="fs-11 text-muted mb-1">Code Letter</div>
                                            <div class="fw-bold fs-4 text-info" id="dist-res-code">—</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- AQL Plan --}}
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="feather-bar-chart-2 me-2 text-primary"></i>AQL Sampling Plan</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Type</th>
                                                <th class="text-center">AQL</th>
                                                <th class="text-center">Sample Size</th>
                                                <th class="text-center">Accept (Ac)</th>
                                                <th class="text-center">Reject (Re)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dist-aql-plan-tbody"></tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Distribution Table --}}
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="feather-grid me-2 text-success"></i>Inspection Distribution by Variation</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Color</th>
                                                    <th>Size</th>
                                                    <th class="text-center">Ordered Qty</th>
                                                    <th class="text-center">Share %</th>
                                                    <th class="text-center">Inspect Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody id="dist-table-tbody"></tbody>
                                            <tfoot>
                                                <tr class="table-light fw-semibold">
                                                    <td colspan="2" class="text-end text-muted fs-12">Total</td>
                                                    <td class="text-center" id="dist-foot-total-qty">—</td>
                                                    <td class="text-center">100%</td>
                                                    <td class="text-center" id="dist-foot-total-inspect">—</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>{{-- /panel-distribution --}}

        </div>{{-- /tab-content --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    // ── Master data from DB ──────────────────────────────────────────────────
    const COLORS = @json($colors->pluck('name'));
    const SIZES  = @json($sizes->pluck('name'));

    // ── ISO 2859-1 Reference Data ────────────────────────────────────────────
    const LOT_RANGES = [
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

    const SS_TO_CODE = {2:'A',3:'B',5:'C',8:'D',13:'E',20:'F',32:'G',50:'H',80:'J',125:'K',200:'L',315:'M',500:'N',800:'P',1250:'Q',2000:'R'};

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

    // ── Shared helpers ───────────────────────────────────────────────────────
    function getBaseSample(qty, level) {
        const row = LOT_RANGES.find(r => qty >= r.Min && qty <= r.Max);
        return row ? Math.min(row[level], qty) : null;
    }

    function getAcRe(aqlKey, baseSample, totalQty) {
        const tbl = AQL_NUMBERS[String(aqlKey)];
        if (!tbl) return null;
        const entry = tbl[baseSample];
        if (!entry) return null;
        const ss = entry.Ss ? Math.min(entry.Ss, totalQty) : baseSample;
        return { ac: entry.Ac, re: entry.Re, ss };
    }

    function escHtml(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function distributeProportionally(totalQty, sampleSize, qtys) {
        if (totalQty <= 0 || qtys.length === 0) return qtys.map(() => 0);
        const raw = qtys.map(q => (q / totalQty) * sampleSize);
        const floored = raw.map(Math.floor);
        let rem = sampleSize - floored.reduce((a, b) => a + b, 0);
        const fracs = raw.map((r, i) => ({ i, frac: r - Math.floor(r) }));
        fracs.sort((a, b) => b.frac - a.frac);
        for (let j = 0; j < rem && j < fracs.length; j++) floored[fracs[j].i]++;
        return floored;
    }

    // ════════════════════════════════════════════════════════════════
    // TAB 1 — AQL Calculator
    // ════════════════════════════════════════════════════════════════
    let calcLevel = 'II';
    let calcCalculated = false;
    let calcLotSize = 0, calcBaseSample = 0;
    let calcCrRes = null, calcMaRes = null, calcMiRes = null;

    window.calcSetLevel = function(lvl) {
        calcLevel = lvl;
        document.querySelectorAll('.calc-level-btn').forEach(b => {
            b.className = 'btn btn-sm calc-level-btn ' + (b.dataset.level === lvl ? 'btn-primary' : 'btn-light');
        });
        calcRun();
    };

    window.calcRun = function() {
        const lot = parseInt(document.getElementById('calc-lot-size')?.value) || 0;
        if (lot < 2) { calcCalculated = false; calcShowEmpty(); return; }

        const bs = getBaseSample(lot, calcLevel);
        if (!bs) { calcCalculated = false; calcShowEmpty(); return; }

        calcLotSize   = lot;
        calcBaseSample = bs;

        const crAql = document.getElementById('calc-aql-critical').value;
        const maAql = document.getElementById('calc-aql-major').value;
        const miAql = document.getElementById('calc-aql-minor').value;

        calcCrRes = getAcRe(crAql, bs, lot);
        calcMaRes = getAcRe(maAql, bs, lot);
        calcMiRes = getAcRe(miAql, bs, lot);

        // Code letter reverse lookup
        const codeLetter = SS_TO_CODE[bs] || '—';

        // Update badges
        document.getElementById('calc-badge-level').textContent = calcLevel;
        document.getElementById('calc-badge-code').textContent  = codeLetter;
        document.getElementById('calc-badge-ss').textContent    = bs;

        // Code info below lot input
        document.getElementById('calc-code-letter').textContent = codeLetter;
        document.getElementById('calc-base-sample').textContent = bs;
        document.getElementById('calc-code-info').style.display = '';

        // Build results table
        const tbody = document.getElementById('calc-result-tbody');
        const rows = [
            { label:'Critical', badge:'danger',  aql: crAql, res: calcCrRes, foundId:'calc-found-critical' },
            { label:'Major',    badge:'warning', aql: maAql, res: calcMaRes, foundId:'calc-found-major' },
            { label:'Minor',    badge:'info',    aql: miAql, res: calcMiRes, foundId:'calc-found-minor' },
        ];
        tbody.innerHTML = rows.map(r => {
            const found = parseInt(document.getElementById(r.foundId)?.value) || 0;
            const ac    = r.res?.ac ?? (r.aql === 'not_allowed' ? 0 : null);
            const re    = r.res?.re ?? (r.aql === 'not_allowed' ? 1 : null);
            const ss    = r.res?.ss ?? bs;
            const aqlLabel = r.aql === 'not_allowed' ? 'Not Allowed' : r.aql;
            let status = { label:'Pending', cls:'bg-soft-secondary text-secondary' };
            if (r.aql === 'not_allowed' && found > 0)        status = { label:'Fail',    cls:'bg-soft-danger text-danger' };
            else if (ac !== null && found > ac)               status = { label:'Fail',    cls:'bg-soft-danger text-danger' };
            else if (ac !== null && found > 0 && found <= ac) status = { label:'Pass',    cls:'bg-soft-success text-success' };
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

        calcCalculated = true;
        calcVerdict();
        calcShowResults();
        calcBuildRefTable();
    };

    window.calcVerdict = function() {
        if (!calcCalculated) return;
        const foundCrit = parseInt(document.getElementById('calc-found-critical')?.value) || 0;
        const foundMaj  = parseInt(document.getElementById('calc-found-major')?.value)    || 0;
        const foundMin  = parseInt(document.getElementById('calc-found-minor')?.value)    || 0;

        const crAql = document.getElementById('calc-aql-critical').value;
        const maAql = document.getElementById('calc-aql-major').value;
        const miAql = document.getElementById('calc-aql-minor').value;

        if (foundCrit + foundMaj + foundMin === 0) {
            calcSetVerdictUI('Pending');
            return;
        }

        const fail =
            (crAql === 'not_allowed' && foundCrit > 0) ||
            (calcCrRes && calcCrRes.ac !== null && foundCrit > calcCrRes.ac) ||
            (maAql === 'not_allowed' && foundMaj  > 0) ||
            (calcMaRes && calcMaRes.ac !== null && foundMaj  > calcMaRes.ac) ||
            (miAql === 'not_allowed' && foundMin  > 0) ||
            (calcMiRes && calcMiRes.ac !== null && foundMin  > calcMiRes.ac);

        calcSetVerdictUI(fail ? 'Fail' : 'Pass');
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
    }

    function calcBuildRefTable() {
        const tbody = document.getElementById('calc-ref-tbody');
        if (!tbody || tbody.children.length > 0) return; // built once
        tbody.innerHTML = LOT_RANGES.map(r => {
            const maxLabel = r.Max >= Number.MAX_SAFE_INTEGER ? '∞' : r.Max.toLocaleString();
            const highlight = calcLotSize >= r.Min && calcLotSize <= r.Max ? 'table-warning' : '';
            return `<tr class="${highlight}">
                <td class="fw-semibold">${r.Min.toLocaleString()} – ${maxLabel}</td>
                <td class="text-center">${r.I} <span class="text-muted">(${r.I})</span></td>
                <td class="text-center">${r.II} <span class="text-muted">(${r.II})</span></td>
                <td class="text-center">${r.III} <span class="text-muted">(${r.III})</span></td>
                <td class="text-center">${r.S1}</td>
                <td class="text-center">${r.S2}</td>
                <td class="text-center">${r.S3}</td>
                <td class="text-center">${r.S4}</td>
            </tr>`;
        }).join('');
    }

    // ════════════════════════════════════════════════════════════════
    // TAB 2 — Distribution
    // ════════════════════════════════════════════════════════════════
    let distLevel = 'II';
    let distRows = [
        { color:'', size:'', qty:0 },
        { color:'', size:'', qty:0 },
        { color:'', size:'', qty:0 },
    ];

    window.distSetLevel = function(lvl) {
        distLevel = lvl;
        document.querySelectorAll('.dist-level-btn').forEach(b => {
            b.className = 'btn btn-sm dist-level-btn ' + (b.dataset.level === lvl ? 'btn-primary' : 'btn-light');
        });
        distRecalc();
    };

    window.distAddRow = function() {
        distRows.push({ color:'', size:'', qty:0 });
        distRenderRows();
        distRecalc();
    };

    window.distRemoveRow = function(i) {
        distRows.splice(i, 1);
        distRenderRows();
        distRecalc();
    };

    window.distUpdateRow = function(i, field, val) {
        distRows[i][field] = field === 'qty' ? (parseInt(val) || 0) : val;
        distRecalc();
    };

    function buildColorOptions(selected) {
        return '<option value="">— Color —</option>' +
            COLORS.map(c => `<option value="${escHtml(c)}"${selected === c ? ' selected' : ''}>${escHtml(c)}</option>`).join('');
    }

    function buildSizeOptions(selected) {
        return '<option value="">— Size —</option>' +
            SIZES.map(s => `<option value="${escHtml(s)}"${selected === s ? ' selected' : ''}>${escHtml(s)}</option>`).join('');
    }

    function distRenderRows() {
        const tbody = document.getElementById('dist-rows-tbody');
        if (!tbody) return;
        tbody.innerHTML = distRows.map((r, i) => `
            <tr>
                <td>
                    <select class="form-select form-select-sm" onchange="distUpdateRow(${i},'color',this.value)">
                        ${buildColorOptions(r.color)}
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm" onchange="distUpdateRow(${i},'size',this.value)">
                        ${buildSizeOptions(r.size)}
                    </select>
                </td>
                <td><input type="number" class="form-control form-control-sm text-center" value="${r.qty}"
                           min="0" oninput="distUpdateRow(${i},'qty',this.value)"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="distRemoveRow(${i})">
                        <i class="feather-x"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    window.distRecalc = function() {
        const totalQty = distRows.reduce((s, r) => s + r.qty, 0);

        document.getElementById('dist-total-qty').textContent = totalQty > 0 ? totalQty.toLocaleString() : '—';
        document.getElementById('dist-res-total-qty').textContent = totalQty.toLocaleString();

        const crAql = document.getElementById('dist-aql-critical').value;
        const maAql = document.getElementById('dist-aql-major').value;
        const miAql = document.getElementById('dist-aql-minor').value;

        if (totalQty < 2 || distRows.every(r => r.qty === 0)) {
            document.getElementById('dist-empty-card').style.display   = '';
            document.getElementById('dist-result-section').style.display = 'none';
            return;
        }

        const bs = getBaseSample(totalQty, distLevel);
        if (!bs) return;

        const codeLetter = SS_TO_CODE[bs] || '—';
        document.getElementById('dist-res-sample').textContent = bs;
        document.getElementById('dist-res-code').textContent   = codeLetter;

        // AQL plan
        const crRes = getAcRe(crAql, bs, totalQty);
        const maRes = getAcRe(maAql, bs, totalQty);
        const miRes = getAcRe(miAql, bs, totalQty);

        const planTbody = document.getElementById('dist-aql-plan-tbody');
        const planRows  = [
            { label:'Critical', badge:'danger',  aql:crAql, res:crRes },
            { label:'Major',    badge:'warning', aql:maAql, res:maRes },
            { label:'Minor',    badge:'info',    aql:miAql, res:miRes },
        ];
        planTbody.innerHTML = planRows.map(r => {
            const ac = r.res?.ac ?? (r.aql === 'not_allowed' ? 0 : '—');
            const re = r.res?.re ?? (r.aql === 'not_allowed' ? 1 : '—');
            const ss = r.res?.ss ?? bs;
            return `<tr>
                <td><span class="badge bg-soft-${r.badge} text-${r.badge}">${r.label}</span></td>
                <td class="text-center">${r.aql === 'not_allowed' ? 'Not Allowed' : r.aql}</td>
                <td class="text-center fw-semibold">${ss}</td>
                <td class="text-center">${ac}</td>
                <td class="text-center">${re}</td>
            </tr>`;
        }).join('');

        // Distribution
        const qtys = distRows.map(r => r.qty);
        const dist = distributeProportionally(totalQty, bs, qtys);

        const distTbody = document.getElementById('dist-table-tbody');
        distTbody.innerHTML = distRows.map((r, i) => {
            const share = totalQty > 0 ? ((r.qty / totalQty) * 100).toFixed(1) : '0.0';
            const color = r.color || `<span class="text-muted">—</span>`;
            const size  = r.size  || `<span class="text-muted">—</span>`;
            return `<tr>
                <td>${r.color ? escHtml(r.color) : '<span class="text-muted">—</span>'}</td>
                <td>${r.size  ? escHtml(r.size)  : '<span class="text-muted">—</span>'}</td>
                <td class="text-center">${r.qty.toLocaleString()}</td>
                <td class="text-center text-muted">${share}%</td>
                <td class="text-center fw-semibold text-success">${dist[i]}</td>
            </tr>`;
        }).join('');

        const totalInspect = dist.reduce((a, b) => a + b, 0);
        document.getElementById('dist-foot-total-qty').textContent     = totalQty.toLocaleString();
        document.getElementById('dist-foot-total-inspect').textContent = totalInspect.toLocaleString();

        document.getElementById('dist-empty-card').style.display    = 'none';
        document.getElementById('dist-result-section').style.display = '';
    };

    // ── PDF Export helpers ───────────────────────────────────────────────────
    function pdfStyles() {
        return `
            *{box-sizing:border-box;margin:0;padding:0}
            body{font-family:Arial,sans-serif;color:#333;padding:36px;font-size:13px}
            h1{font-size:20px;color:#0d6efd;margin-bottom:4px}
            .subtitle{color:#888;font-size:12px;margin-bottom:22px}
            .meta-row{display:flex;gap:14px;margin-bottom:22px}
            .meta-box{flex:1;background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;padding:10px;text-align:center}
            .meta-label{font-size:11px;color:#888;margin-bottom:4px}
            .meta-value{font-size:18px;font-weight:700;color:#0d6efd}
            h2{font-size:14px;color:#444;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #dee2e6}
            table{width:100%;border-collapse:collapse;margin-bottom:20px}
            th{background:#f8f9fa;border:1px solid #dee2e6;padding:8px 10px;text-align:left;font-size:12px}
            td{border:1px solid #dee2e6;padding:8px 10px;font-size:12px}
            .badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px}
            .verdict-box{border-radius:8px;padding:18px;text-align:center;margin-bottom:20px}
            .verdict-label{font-size:11px;color:#666;margin-bottom:6px}
            .verdict-value{font-size:26px;font-weight:700}
            .footer{margin-top:28px;font-size:11px;color:#bbb;text-align:center;border-top:1px solid #eee;padding-top:10px}
            .print-btn{display:block;text-align:center;margin-top:18px}
            .print-btn button{background:#0d6efd;color:white;border:none;padding:9px 22px;border-radius:5px;font-size:13px;cursor:pointer}
            @media print{.print-btn{display:none}}
        `;
    }

    function openPrintWindow(html, title) {
        const w = window.open('', '_blank', 'width=900,height=700');
        w.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + title + '</title><style>' + pdfStyles() + '</style></head><body>' + html + '<div class="print-btn"><button onclick="window.print()">Print / Save as PDF</button></div><script>window.onload=function(){window.print();}<\/script></body></html>');
        w.document.close();
    }

    window.exportCalcPDF = function() {
        if (!calcCalculated) return;

        const lotSize  = parseInt(document.getElementById('calc-lot-size').value) || 0;
        const level    = document.getElementById('calc-badge-level').textContent;
        const code     = document.getElementById('calc-badge-code').textContent;
        const ss       = document.getElementById('calc-badge-ss').textContent;
        const verdict  = document.getElementById('calc-verdict-text').textContent;
        const vSample  = document.getElementById('calc-verdict-sample').textContent;

        const vColor = verdict === 'Pass' ? '#198754' : verdict === 'Fail' ? '#dc3545' : '#6c757d';
        const vBg    = verdict === 'Pass' ? '#d1e7dd' : verdict === 'Fail' ? '#f8d7da' : '#e9ecef';

        let tableRows = '';
        document.querySelectorAll('#calc-result-tbody tr').forEach(tr => {
            const cells = tr.querySelectorAll('td');
            const type    = cells[0].textContent.trim();
            const aql     = cells[1].textContent.trim();
            const nss     = cells[2].textContent.trim();
            const ac      = cells[3].textContent.trim();
            const re      = cells[4].textContent.trim();
            const found   = cells[5].textContent.trim();
            const status  = cells[6].textContent.trim();
            const tBg     = type === 'Critical' ? '#f8d7da' : type === 'Major' ? '#fff3cd' : '#cff4fc';
            const tColor  = type === 'Critical' ? '#842029' : type === 'Major' ? '#664d03' : '#055160';
            const sBg     = status === 'Pass' ? '#d1e7dd' : status === 'Fail' ? '#f8d7da' : '#e9ecef';
            const sColor  = status === 'Pass' ? '#0f5132' : status === 'Fail' ? '#842029' : '#41464b';
            tableRows += `<tr>
                <td><span class="badge" style="background:${tBg};color:${tColor}">${type}</span></td>
                <td style="text-align:center">${aql}</td>
                <td style="text-align:center;font-weight:600">${nss}</td>
                <td style="text-align:center">${ac}</td>
                <td style="text-align:center">${re}</td>
                <td style="text-align:center">${found}</td>
                <td style="text-align:center"><span class="badge" style="background:${sBg};color:${sColor}">${status}</span></td>
            </tr>`;
        });

        const html = `
            <h1>AQL Sampling Plan</h1>
            <div class="subtitle">ISO 2859-1 Acceptance Sampling &nbsp;|&nbsp; ${new Date().toLocaleDateString()}</div>
            <div class="meta-row">
                <div class="meta-box"><div class="meta-label">Lot Size</div><div class="meta-value">${lotSize.toLocaleString()}</div></div>
                <div class="meta-box"><div class="meta-label">Inspection Level</div><div class="meta-value">${level}</div></div>
                <div class="meta-box"><div class="meta-label">Code Letter</div><div class="meta-value">${code}</div></div>
                <div class="meta-box"><div class="meta-label">Base Sample (n)</div><div class="meta-value">${ss}</div></div>
            </div>
            <h2>Sampling Plan</h2>
            <table>
                <thead><tr>
                    <th>Defect Type</th>
                    <th style="text-align:center">AQL Level</th>
                    <th style="text-align:center">Sample Size</th>
                    <th style="text-align:center">Accept (Ac)</th>
                    <th style="text-align:center">Reject (Re)</th>
                    <th style="text-align:center">Found</th>
                    <th style="text-align:center">Status</th>
                </tr></thead>
                <tbody>${tableRows}</tbody>
            </table>
            <div class="verdict-box" style="background:${vBg};border:1px solid ${vColor}55">
                <div class="verdict-label">Overall AQL Verdict &nbsp;·&nbsp; Sample: ${vSample} units &nbsp;·&nbsp; Lot: ${lotSize.toLocaleString()} units</div>
                <div class="verdict-value" style="color:${vColor}">${verdict}</div>
            </div>
            <div class="footer">TradeSyncERP &nbsp;·&nbsp; AQL Sampling Plan &nbsp;·&nbsp; ISO 2859-1</div>`;

        openPrintWindow(html, 'AQL Sampling Plan – Lot ' + lotSize.toLocaleString());
    };

    window.exportDistPDF = function() {
        const totalQty    = document.getElementById('dist-res-total-qty').textContent;
        const sampleSize  = document.getElementById('dist-res-sample').textContent;
        const codeLetter  = document.getElementById('dist-res-code').textContent;
        const level       = distLevel;

        let planRows = '';
        document.querySelectorAll('#dist-aql-plan-tbody tr').forEach(tr => {
            const cells  = tr.querySelectorAll('td');
            const type   = cells[0].textContent.trim();
            const aql    = cells[1].textContent.trim();
            const nss    = cells[2].textContent.trim();
            const ac     = cells[3].textContent.trim();
            const re     = cells[4].textContent.trim();
            const tBg    = type === 'Critical' ? '#f8d7da' : type === 'Major' ? '#fff3cd' : '#cff4fc';
            const tColor = type === 'Critical' ? '#842029' : type === 'Major' ? '#664d03' : '#055160';
            planRows += `<tr>
                <td><span class="badge" style="background:${tBg};color:${tColor}">${type}</span></td>
                <td style="text-align:center">${aql}</td>
                <td style="text-align:center;font-weight:600">${nss}</td>
                <td style="text-align:center">${ac}</td>
                <td style="text-align:center">${re}</td>
            </tr>`;
        });

        let distRows_html = '';
        document.querySelectorAll('#dist-table-tbody tr').forEach(tr => {
            const cells  = tr.querySelectorAll('td');
            const color  = cells[0].textContent.trim() || '—';
            const size   = cells[1].textContent.trim() || '—';
            const qty    = cells[2].textContent.trim();
            const share  = cells[3].textContent.trim();
            const insp   = cells[4].textContent.trim();
            distRows_html += `<tr>
                <td>${color}</td>
                <td>${size}</td>
                <td style="text-align:center">${qty}</td>
                <td style="text-align:center;color:#888">${share}</td>
                <td style="text-align:center;font-weight:600;color:#198754">${insp}</td>
            </tr>`;
        });

        const totalInspect = document.getElementById('dist-foot-total-inspect').textContent;

        const html = `
            <h1>AQL Quantity Distribution Report</h1>
            <div class="subtitle">ISO 2859-1 Acceptance Sampling &nbsp;|&nbsp; ${new Date().toLocaleDateString()}</div>
            <div class="meta-row">
                <div class="meta-box"><div class="meta-label">Total Order Qty</div><div class="meta-value">${totalQty}</div></div>
                <div class="meta-box"><div class="meta-label">Inspection Level</div><div class="meta-value">${level}</div></div>
                <div class="meta-box"><div class="meta-label">Code Letter</div><div class="meta-value">${codeLetter}</div></div>
                <div class="meta-box"><div class="meta-label">Total Sample (n)</div><div class="meta-value">${sampleSize}</div></div>
            </div>
            <h2>AQL Sampling Plan</h2>
            <table>
                <thead><tr>
                    <th>Type</th>
                    <th style="text-align:center">AQL</th>
                    <th style="text-align:center">Sample Size</th>
                    <th style="text-align:center">Accept (Ac)</th>
                    <th style="text-align:center">Reject (Re)</th>
                </tr></thead>
                <tbody>${planRows}</tbody>
            </table>
            <h2>Inspection Distribution by Variation</h2>
            <table>
                <thead><tr>
                    <th>Color</th><th>Size</th>
                    <th style="text-align:center">Ordered Qty</th>
                    <th style="text-align:center">Share %</th>
                    <th style="text-align:center">Inspect Qty</th>
                </tr></thead>
                <tbody>${distRows_html}</tbody>
                <tfoot><tr style="background:#f8f9fa;font-weight:600">
                    <td colspan="2" style="text-align:right;color:#888;font-size:11px">Total</td>
                    <td style="text-align:center">${totalQty}</td>
                    <td style="text-align:center">100%</td>
                    <td style="text-align:center;color:#198754">${totalInspect}</td>
                </tr></tfoot>
            </table>
            <div class="footer">TradeSyncERP &nbsp;·&nbsp; AQL Quantity Distribution &nbsp;·&nbsp; ISO 2859-1</div>`;

        openPrintWindow(html, 'AQL Distribution Report');
    };

    // ── Init ─────────────────────────────────────────────────────────────────
    distRenderRows();
    distRecalc();
    calcShowEmpty();

})();
</script>
@endpush
