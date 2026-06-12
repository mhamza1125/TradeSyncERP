<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Inspection Report — {{ $inspection->report_number }}</title>
<style>

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 8.5pt;
    color: #111827;
    line-height: 1.5;
}

@page {
    size: A4 portrait;
    margin: 20mm 14mm 22mm 14mm;
}

.page-break { page-break-after: always; }
.no-break   { page-break-inside: avoid; }

/* ── Fixed footer ──────────────────────────────────────────────────────── */
.pdf-footer {
    position: fixed;
    bottom: -18mm;
    left: -14mm; right: -14mm;
    border-top: 2px solid #1a3a5c;
    padding: 4px 14mm 0;
    font-size: 7pt;
    color: #6b7280;
    background: #ffffff;
}
.pdf-footer table { width: 100%; }
.pdf-footer .fn-center { text-align: center; color: #1a3a5c; font-weight: bold; font-size: 7.5pt; }
.pdf-footer .fn-right  { text-align: right; }
.pdf-footer .fn-right:after { content: "Page " counter(page) " of " counter(pages); }

/* ── Cover banner ──────────────────────────────────────────────────────── */
.cover-banner {
    background: #1a3a5c;
    color: #ffffff;
    padding: 14px 16px;
    margin-bottom: 0;
    border-radius: 3px 3px 0 0;
}
.cover-banner table { width: 100%; }
.cb-system-label {
    font-size: 7pt;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #93c5fd;
    margin-bottom: 2px;
}
.cb-company { font-size: 15pt; font-weight: bold; letter-spacing: 0.5px; }
.cb-tagline { font-size: 7.5pt; color: #bfdbfe; margin-top: 2px; }
.cb-repnum  { font-size: 10pt; font-weight: bold; text-align: right; }
.cb-date    { font-size: 8pt; color: #bfdbfe; text-align: right; margin-top: 2px; }

/* ── Cover title strip ─────────────────────────────────────────────────── */
.cover-title-strip {
    background: #f0f4f8;
    border-left: 4px solid #c8951a;
    border-bottom: 1px solid #d1d8e0;
    padding: 10px 16px;
    margin-bottom: 16px;
}
.cover-main-title {
    font-size: 20pt;
    font-weight: bold;
    color: #1a3a5c;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.cover-main-subtitle {
    font-size: 9.5pt;
    color: #4b5563;
    margin-top: 2px;
}

/* ── Info panels ───────────────────────────────────────────────────────── */
.info-panel {
    border: 1px solid #d1d8e0;
    border-top: 3px solid #1a3a5c;
    border-radius: 2px;
    margin-bottom: 12px;
}
.info-panel-header {
    background: #f8fafc;
    padding: 5px 12px;
    border-bottom: 1px solid #d1d8e0;
    font-size: 7pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #6b7280;
}
.info-panel-body { padding: 8px 12px; }

.cover-kv { width: 100%; border-collapse: collapse; }
.cover-kv td { padding: 3.5px 0; font-size: 8.5pt; border-bottom: 1px solid #f3f4f6; }
.cover-kv .ck { color: #6b7280; width: 44%; font-size: 8pt; }
.cover-kv .cv { color: #111827; font-weight: 500; }
.cover-kv tr:last-child td { border-bottom: none; }

/* ── Verdict panel ─────────────────────────────────────────────────────── */
.verdict-badge-lg {
    display: block;
    padding: 11px 0;
    font-size: 17pt;
    font-weight: bold;
    letter-spacing: 3px;
    border-radius: 3px;
    text-align: center;
    margin-bottom: 12px;
}
.vb-pass        { background: #dcfce7; color: #166534; border: 2px solid #16a34a; }
.vb-fail        { background: #fee2e2; color: #991b1b; border: 2px solid #dc2626; }
.vb-conditional { background: #fef3c7; color: #92400e; border: 2px solid #d97706; }
.vb-pending     { background: #f3f4f6; color: #4b5563; border: 2px solid #9ca3af; }

.defect-pill-row { width: 100%; border-collapse: collapse; }
.defect-pill-row td { text-align: center; padding: 3px 3px; }
.dp-box { border-radius: 3px; padding: 5px 4px; display: block; }
.dp-critical { background: #fee2e2; }
.dp-major    { background: #fef3c7; }
.dp-minor    { background: #dbeafe; }
.dp-num { font-size: 13pt; font-weight: bold; display: block; }
.dp-crit-num { color: #991b1b; }
.dp-maj-num  { color: #92400e; }
.dp-min-num  { color: #1e40af; }
.dp-lbl { font-size: 6.5pt; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; display: block; margin-top: 1px; }

/* ── Runs summary box ──────────────────────────────────────────────────── */
.runs-box { border: 1px solid #d1d8e0; border-radius: 2px; margin-top: 14px; }
.runs-box-hdr {
    background: #f0f4f8;
    padding: 5px 12px;
    border-bottom: 1px solid #d1d8e0;
    font-size: 7pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #374151;
}

/* ── Disclaimer ────────────────────────────────────────────────────────── */
.disclaimer {
    margin-top: 16px;
    padding: 8px 12px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-left: 3px solid #9ca3af;
    font-size: 7pt;
    color: #6b7280;
    border-radius: 0 2px 2px 0;
}

/* ── Run page header ───────────────────────────────────────────────────── */
.run-page-header {
    background: #1a3a5c;
    color: #ffffff;
    padding: 10px 14px;
    border-radius: 3px;
    margin-bottom: 14px;
}
.run-page-header table { width: 100%; }
.rph-label { font-size: 7pt; letter-spacing: 2px; text-transform: uppercase; color: #93c5fd; }
.rph-title { font-size: 13pt; font-weight: bold; margin-top: 1px; }
.rph-meta  { font-size: 7.5pt; color: #bfdbfe; margin-top: 3px; }
.rph-verdict-cell { text-align: right; vertical-align: middle; width: 140px; }
.rph-verdict-badge {
    display: inline-block;
    padding: 6px 14px;
    font-size: 10pt;
    font-weight: bold;
    border-radius: 3px;
    letter-spacing: 1px;
}

/* ── Section blocks ────────────────────────────────────────────────────── */
.section-block {
    margin-bottom: 11px;
    border: 1px solid #d1d8e0;
    border-left: 3px solid #1a3a5c;
    border-radius: 0 2px 2px 0;
    page-break-inside: avoid;
}
.section-hdr {
    background: #f8fafc;
    padding: 5px 10px;
    border-bottom: 1px solid #d1d8e0;
}
.section-hdr table { width: 100%; }
.sec-num {
    background: #1a3a5c;
    color: #ffffff;
    font-size: 7pt;
    font-weight: bold;
    padding: 1px 6px;
    border-radius: 2px;
    margin-right: 5px;
    display: inline-block;
}
.sec-name { font-size: 9.5pt; font-weight: bold; color: #1a3a5c; }

.sec-badge {
    display: inline-block;
    font-size: 7pt;
    font-weight: bold;
    padding: 1px 8px;
    border-radius: 10px;
}
.sb-complete { background: #dcfce7; color: #166534; }
.sb-pending  { background: #fef3c7; color: #92400e; }
.sb-na       { background: #f3f4f6; color: #4b5563; }

.section-body { padding: 10px 12px; }

.section-note {
    margin-top: 8px;
    padding: 6px 10px;
    background: #fffbeb;
    border-left: 3px solid #fbbf24;
    font-size: 8pt;
    color: #78350f;
    border-radius: 0 2px 2px 0;
}

.sub-heading {
    font-size: 7.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #374151;
    margin: 10px 0 5px;
    padding-bottom: 3px;
    border-bottom: 1px solid #e5e7eb;
}

/* ── Meta table ────────────────────────────────────────────────────────── */
.meta-table { width: 100%; border-collapse: collapse; }
.meta-table td {
    padding: 4px 8px;
    font-size: 8.5pt;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}
.meta-table .mk { width: 36%; font-weight: bold; color: #374151; white-space: nowrap; }
.meta-table .mv { color: #111827; }
.meta-table tr:last-child td { border-bottom: none; }

/* ── Data / checklist table ────────────────────────────────────────────── */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    background: #1a3a5c;
    color: #ffffff;
    padding: 5px 8px;
    font-size: 7.5pt;
    font-weight: bold;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.data-table td {
    padding: 4px 8px;
    font-size: 8.5pt;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: top;
}
.data-table tbody tr:nth-child(even) td { background: #f8fafc; }
.data-table tbody tr:last-child td { border-bottom: none; }

/* ── Result badges ─────────────────────────────────────────────────────── */
.rb-pass { color: #166534; font-weight: bold; background: #dcfce7; padding: 1px 7px; border-radius: 3px; font-size: 7.5pt; }
.rb-fail { color: #991b1b; font-weight: bold; background: #fee2e2; padding: 1px 7px; border-radius: 3px; font-size: 7.5pt; }
.rb-na   { color: #4b5563; background: #f3f4f6; padding: 1px 7px; border-radius: 3px; font-size: 7.5pt; }
.rb-def  { color: #4b5563; font-size: 7.5pt; }

/* ── AQL table ─────────────────────────────────────────────────────────── */
.aql-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
.aql-table th {
    background: #152c4a;
    color: #ffffff;
    padding: 5px 8px;
    font-size: 7.5pt;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.aql-table td {
    padding: 5px 8px;
    font-size: 8.5pt;
    border: 1px solid #d1d8e0;
    text-align: center;
}
.aql-table tbody tr:nth-child(even) td { background: #f8fafc; }
.aql-cat { font-weight: bold; text-align: left !important; }

.aql-verdict-block {
    margin-top: 10px;
    padding: 8px 14px;
    border-radius: 3px;
    font-size: 10.5pt;
    font-weight: bold;
    text-align: center;
}
.avb-pass    { background: #dcfce7; color: #166534; border: 1px solid #16a34a; }
.avb-fail    { background: #fee2e2; color: #991b1b; border: 1px solid #dc2626; }
.avb-pending { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d8e0; }

/* ── Defect table ──────────────────────────────────────────────────────── */
.defect-table { width: 100%; border-collapse: collapse; }
.defect-table th {
    background: #7f1d1d;
    color: #ffffff;
    padding: 5px 8px;
    font-size: 7.5pt;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.defect-table td {
    padding: 4px 8px;
    font-size: 8.5pt;
    border-bottom: 1px solid #fee2e2;
    vertical-align: top;
}
.defect-table tbody tr:nth-child(even) td { background: #fff7f7; }
.defect-table tbody tr:last-child td { border-bottom: none; }

.sev-critical { background: #fee2e2; color: #991b1b; font-weight: bold; padding: 1px 6px; border-radius: 3px; font-size: 7.5pt; }
.sev-major    { background: #fef3c7; color: #92400e; font-weight: bold; padding: 1px 6px; border-radius: 3px; font-size: 7.5pt; }
.sev-minor    { background: #dbeafe; color: #1e40af; font-weight: bold; padding: 1px 6px; border-radius: 3px; font-size: 7.5pt; }
.sev-functional { background: #f3f4f6; color: #374151; font-weight: bold; padding: 1px 6px; border-radius: 3px; font-size: 7.5pt; }

/* ── Image gallery ─────────────────────────────────────────────────────── */
.img-gallery-table { width: 100%; border-collapse: collapse; }
.img-gallery-table td { padding: 4px; vertical-align: top; text-align: center; }
.img-thumb {
    width: 112px;
    height: 112px;
    object-fit: cover;
    border: 2px solid #d1d8e0;
    border-radius: 2px;
    display: block;
    margin: 0 auto 4px;
}
.img-label { font-size: 7pt; color: #6b7280; word-break: break-word; }

/* ── Verdict review box ────────────────────────────────────────────────── */
.review-verdict-box {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 3px;
    font-size: 10pt;
    font-weight: bold;
}

/* ── Empty state ───────────────────────────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 18px;
    color: #6b7280;
    font-size: 8pt;
    font-style: italic;
}

</style>
</head>
<body>

{{-- ═══════════════════════════════════════════ FIXED FOOTER ═══════════════ --}}
<div class="pdf-footer">
    <table>
        <tr>
            <td style="color:#6b7280">Confidential &mdash; For Authorized Recipients Only &mdash; TradeSyncERP</td>
            <td class="fn-center">{{ $inspection->report_number }}</td>
            <td class="fn-right"></td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════ COVER PAGE ════════════════ --}}

@php
    $coverVerdictClass = match($inspection->overall_status) {
        'Pass'             => 'vb-pass',
        'Fail'             => 'vb-fail',
        'Conditional Pass' => 'vb-conditional',
        default            => 'vb-pending',
    };

    // Aggregate defect counts across all runs
    $defCritical = 0; $defMajor = 0; $defMinor = 0;
    foreach ($runs as $_r) {
        foreach ($_r->runSections as $_rs) {
            if ($_rs->section && (in_array($_rs->section->section_type, ['defects']) || $_rs->section->slug === 'defect_recording')) {
                $sels = collect($_rs->data['selections'] ?? [])->filter(fn($s) => !empty($s['selected']));
                $defCritical += $sels->where('severity', 'critical')->count();
                $defMajor    += $sels->where('severity', 'major')->count();
                $defMinor    += $sels->where('severity', 'minor')->count();
            }
        }
    }
@endphp

{{-- Cover Banner --}}
<div class="cover-banner">
    <table>
        <tr>
            <td style="vertical-align: bottom">
                <div class="cb-system-label">Quality Assurance &amp; Inspection Management</div>
                <div class="cb-company">TradeSyncERP</div>
                <div class="cb-tagline">Inspection &amp; Quality Control System</div>
            </td>
            <td style="vertical-align: bottom">
                <div class="cb-repnum">{{ $inspection->report_number }}</div>
                <div class="cb-date">{{ $inspection->inspection_date?->format('d F Y') ?? now()->format('d F Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Title Strip --}}
<div class="cover-title-strip">
    <div class="cover-main-title">Inspection Report</div>
    <div class="cover-main-subtitle">{{ $inspection->inspectionType?->name ?? 'Quality Inspection' }}</div>
</div>

{{-- Two-column info layout --}}
<table style="width:100%; border-collapse:collapse">
    <tr>
        <td style="width:58%; padding-right:10px; vertical-align:top">
            <div class="info-panel">
                <div class="info-panel-header">Report Information</div>
                <div class="info-panel-body">
                    <table class="cover-kv">
                        <tr><td class="ck">Report Number</td><td class="cv">{{ $inspection->report_number }}</td></tr>
                        <tr><td class="ck">Inspection Type</td><td class="cv">{{ $inspection->inspectionType?->name ?? '—' }}</td></tr>
                        <tr><td class="ck">Inspection Date</td><td class="cv">{{ $inspection->inspection_date?->format('d F Y') ?? '—' }}</td></tr>
                        <tr><td class="ck">Inspector(s)</td><td class="cv">{{ $inspection->inspectors->pluck('employee_name')->implode(', ') ?: '—' }}</td></tr>
                        <tr><td class="ck">Customer Orders</td><td class="cv">{{ $inspection->customerOrders->count() > 0 ? $inspection->customerOrders->count().' order(s)' : '—' }}</td></tr>
                        <tr><td class="ck">Total Runs</td><td class="cv">{{ $runs->count() }}</td></tr>
                        <tr><td class="ck">Date Generated</td><td class="cv">{{ now()->format('d F Y, H:i') }}</td></tr>
                        @if($inspection->remarks)
                        <tr><td class="ck">Remarks</td><td class="cv">{{ $inspection->remarks }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </td>
        <td style="width:42%; vertical-align:top">
            <div class="info-panel">
                <div class="info-panel-header">Overall Verdict</div>
                <div class="info-panel-body">
                    <div class="verdict-badge-lg {{ $coverVerdictClass }}">
                        {{ strtoupper($inspection->overall_status ?? 'PENDING') }}
                    </div>
                    <div style="font-size:7.5pt; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#6b7280; margin-bottom:6px">Defect Summary</div>
                    <table class="defect-pill-row">
                        <tr>
                            <td><div class="dp-box dp-critical"><span class="dp-num dp-crit-num">{{ $defCritical }}</span><span class="dp-lbl">Critical</span></div></td>
                            <td><div class="dp-box dp-major"><span class="dp-num dp-maj-num">{{ $defMajor }}</span><span class="dp-lbl">Major</span></div></td>
                            <td><div class="dp-box dp-minor"><span class="dp-num dp-min-num">{{ $defMinor }}</span><span class="dp-lbl">Minor</span></div></td>
                        </tr>
                    </table>
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- Runs Summary --}}
<div class="runs-box no-break">
    <div class="runs-box-hdr">Inspection Runs Overview</div>
    <table class="data-table" style="border:none">
        <thead>
            <tr>
                <th style="width:50px; text-align:center">Run #</th>
                <th>Sample Code</th>
                <th>Product / Description</th>
                <th>Customer</th>
                <th style="width:100px; text-align:center">Verdict</th>
                <th style="width:90px">Completed</th>
            </tr>
        </thead>
        <tbody>
            @foreach($runs as $r)
            @php
                $rvc = match($r->verdict) {
                    'Pass'        => 'rb-pass',
                    'Fail'        => 'rb-fail',
                    'Conditional' => 'rb-na',
                    default       => 'rb-def',
                };
            @endphp
            <tr>
                <td style="text-align:center; font-weight:bold; color:#1a3a5c">{{ $r->run_number }}</td>
                <td style="font-weight:500">{{ $r->sample?->sample_code ?? '—' }}</td>
                <td>{{ $r->sample?->product_name ?? '—' }}</td>
                <td style="color:#4b5563">{{ $r->sample?->customer?->customer_name ?? '—' }}</td>
                <td style="text-align:center"><span class="{{ $rvc }}">{{ $r->verdict ?? 'Pending' }}</span></td>
                <td style="font-size:8pt; color:#6b7280">{{ $r->completed_at?->format('d M Y') ?? 'In Progress' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="disclaimer">
    <strong style="color:#374151">Confidentiality Notice:</strong>
    This inspection report is intended solely for the use of the named recipient and contains confidential quality control information.
    Any unauthorized review, disclosure, copying, distribution, or use of this report is strictly prohibited.
    Generated by TradeSyncERP &mdash; {{ now()->format('d F Y \a\t H:i') }}.
</div>

{{-- ═══════════════════════════════════════════ RUNS DETAIL ════════════════ --}}

@php
$hiddenSlugs = [
    'corrective_action', 'inspection_conclusion', 'finish_inspection',
    'textile_sample_conformity', 'denim_textile_defects',
    'cover_photo', 'workmanship_check',
];
@endphp

@foreach($runs as $runIndex => $run)

<div class="page-break"></div>

@php
    $runVerdictClass = match($run->verdict) {
        'Pass'        => 'vb-pass',
        'Fail'        => 'vb-fail',
        'Conditional' => 'vb-conditional',
        default       => 'vb-pending',
    };
    $visibleSections = $run->runSections->filter(
        fn($rs) => $rs->section && !in_array($rs->section->slug, $hiddenSlugs)
    )->values();
    $secIdx = 0;
@endphp

{{-- Run Header --}}
<div class="run-page-header">
    <table>
        <tr>
            <td style="vertical-align:middle">
                <div class="rph-label">Inspection Run &mdash; {{ $inspection->report_number }}</div>
                <div class="rph-title">
                    Run #{{ $run->run_number }}
                    @if($run->sample?->product_name) &mdash; {{ $run->sample->product_name }} @endif
                </div>
                <div class="rph-meta">
                    @if($run->sample)
                        Sample: {{ $run->sample->sample_code }}
                        @if($run->sample->customer) &nbsp;&bull;&nbsp; Customer: {{ $run->sample->customer->customer_name }} @endif
                        @if($run->sample->category) &nbsp;&bull;&nbsp; Category: {{ $run->sample->category->category_name }} @endif
                    @endif
                    @if($run->completed_at) &nbsp;&bull;&nbsp; Completed: {{ $run->completed_at->format('d M Y H:i') }} @endif
                </div>
            </td>
            <td class="rph-verdict-cell">
                <div style="font-size:7pt; text-transform:uppercase; letter-spacing:1.5px; color:#93c5fd; margin-bottom:4px">Verdict</div>
                <div class="rph-verdict-badge {{ $runVerdictClass }}">{{ $run->verdict ?? 'Pending' }}</div>
            </td>
        </tr>
    </table>
</div>

@if($run->remarks)
<div class="section-note" style="margin-bottom:12px">
    <strong>Run Remarks:</strong> {{ $run->remarks }}
</div>
@endif

{{-- ─────────────────────────── SECTIONS ────────────────────────────────── --}}
@foreach($visibleSections as $rs)
@php
    $secIdx++;
    $sec     = $rs->section;
    $secType = $sec->section_type;
    $slug    = $sec->slug;
    $data    = $rs->data ?? [];

    $statusLabel = match($rs->status) { 'complete' => 'Complete', 'na' => 'N/A', default => 'Pending' };
    $statusClass = match($rs->status) { 'complete' => 'sb-complete', 'na' => 'sb-na', default => 'sb-pending' };

    $images = $rs->attachments->filter(fn($a) => $a->isImage())->values();
    $docs   = $rs->attachments->filter(fn($a) => !$a->isImage())->values();
@endphp

<div class="section-block no-break">
    <div class="section-hdr">
        <table>
            <tr>
                <td style="vertical-align:middle">
                    <span class="sec-num">{{ $secIdx }}</span>
                    <span class="sec-name">{{ $sec->name }}</span>
                </td>
                <td style="text-align:right; vertical-align:middle; width:90px">
                    <span class="sec-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
            </tr>
        </table>
    </div>
    <div class="section-body">

        {{-- ════════════ GENERAL INFO ════════════ --}}
        @if($secType === 'general_info')
        @php
            $genFields = array_filter([
                'Buyer / Client'      => $data['buyer_name'] ?? null,
                'Factory / Supplier'  => $data['factory_name'] ?? null,
                'PO / Order Number'   => $data['po_number'] ?? null,
                'Style / Article No.' => $data['style_article_no'] ?? null,
                'Product Description' => $data['product_description'] ?? null,
                'Order Quantity'      => $data['order_quantity'] ?? null,
                'Inspection Date'     => $data['inspection_date'] ?? null,
                'Inspector Name'      => $data['inspector_name'] ?? null,
                'Inspection Location' => $data['inspection_location'] ?? null,
            ], fn($v) => $v !== null && $v !== '');
        @endphp
        @if(!empty($genFields))
        <table style="width:100%; border-collapse:collapse">
            <tr>
                <td style="width:50%; padding-right:8px; vertical-align:top">
                    <table class="meta-table">
                        @foreach(array_slice($genFields, 0, (int)ceil(count($genFields)/2)) as $label => $value)
                        <tr><td class="mk">{{ $label }}</td><td class="mv">{{ $value }}</td></tr>
                        @endforeach
                    </table>
                </td>
                <td style="width:50%; vertical-align:top">
                    <table class="meta-table">
                        @foreach(array_slice($genFields, (int)ceil(count($genFields)/2)) as $label => $value)
                        <tr><td class="mk">{{ $label }}</td><td class="mv">{{ $value }}</td></tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
        @endif

        {{-- ════════════ CHECKLIST ════════════ --}}
        @elseif($secType === 'checklist')
        @php
            $items = $data['items'] ?? [];
            $extra = collect($data)->except('items')->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
        @endphp
        @if($extra->isNotEmpty())
        <table class="meta-table" style="margin-bottom:10px">
            @foreach($extra as $k => $v)
            <tr><td class="mk">{{ ucwords(str_replace('_', ' ', $k)) }}</td><td class="mv">{{ $v }}</td></tr>
            @endforeach
        </table>
        @endif
        @if(!empty($items))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:42%">Checkpoint</th>
                    <th style="width:14%; text-align:center">Result</th>
                    <th>Remarks</th>
                    @if($images->isNotEmpty())<th style="width:65px; text-align:center">Photos</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                @php
                    $rClass = match(strtolower($item['result'] ?? '')) {
                        'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def'
                    };
                    $itemImgs = $images->filter(fn($a) => $a->task_key === 'item_'.$idx)->values();
                @endphp
                <tr>
                    <td style="text-align:center; color:#9ca3af; font-size:8pt">{{ $idx+1 }}</td>
                    <td>{{ $item['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $rClass }}">{{ $item['result'] ?? '—' }}</span></td>
                    <td style="color:#4b5563; font-size:8pt">{{ $item['remarks'] ?? '' }}</td>
                    @if($images->isNotEmpty())
                    <td style="text-align:center">
                        @foreach($itemImgs->take(2) as $img)
                        @php $b64 = $imgBase64($img->file_path); @endphp
                        @if($b64)<img src="{{ $b64 }}" style="width:36px;height:36px;object-fit:cover;border:1px solid #d1d8e0;margin:1px;border-radius:1px">@endif
                        @endforeach
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ DEFECT RECORDING ════════════ --}}
        @elseif($secType === 'defects' || $slug === 'defect_recording')
        @php
            $selections = collect($data['selections'] ?? [])->filter(
                fn($s) => !empty($s['selected']) && !empty($s['defect_id'])
            )->values();
            $dCrit = $selections->where('severity', 'critical')->count();
            $dMaj  = $selections->where('severity', 'major')->count();
            $dMin  = $selections->where('severity', 'minor')->count();
        @endphp
        @if($selections->isEmpty())
        <div class="empty-state">No defects recorded for this inspection run.</div>
        @else
        {{-- Defect stats bar --}}
        <table style="width:100%; margin-bottom:10px; border-collapse:collapse">
            <tr>
                <td style="width:33%; padding:0 4px 0 0">
                    <div style="background:#fee2e2; border-radius:3px; padding:6px 8px; text-align:center">
                        <div style="font-size:16pt; font-weight:bold; color:#991b1b">{{ $dCrit }}</div>
                        <div style="font-size:6.5pt; color:#b91c1c; text-transform:uppercase; letter-spacing:1px">Critical</div>
                    </div>
                </td>
                <td style="width:33%; padding:0 4px">
                    <div style="background:#fef3c7; border-radius:3px; padding:6px 8px; text-align:center">
                        <div style="font-size:16pt; font-weight:bold; color:#92400e">{{ $dMaj }}</div>
                        <div style="font-size:6.5pt; color:#b45309; text-transform:uppercase; letter-spacing:1px">Major</div>
                    </div>
                </td>
                <td style="width:33%; padding:0 0 0 4px">
                    <div style="background:#dbeafe; border-radius:3px; padding:6px 8px; text-align:center">
                        <div style="font-size:16pt; font-weight:bold; color:#1e40af">{{ $dMin }}</div>
                        <div style="font-size:6.5pt; color:#2563eb; text-transform:uppercase; letter-spacing:1px">Minor</div>
                    </div>
                </td>
            </tr>
        </table>
        <table class="defect-table">
            <thead>
                <tr>
                    <th style="width:28px; text-align:center">#</th>
                    <th style="width:36%">Defect Description</th>
                    <th style="width:88px; text-align:center">Severity</th>
                    <th style="width:48px; text-align:center">Qty</th>
                    <th>Comment / Location</th>
                    <th style="width:80px; text-align:center">Photos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($selections as $i => $sel)
                @php
                    $sev = $sel['severity'] ?? 'minor';
                    $defImgs = $images->filter(fn($a) => $a->task_key === 'defect_'.$sel['defect_id'])->values();
                @endphp
                <tr>
                    <td style="text-align:center; color:#9ca3af; font-size:8pt">{{ $i+1 }}</td>
                    <td><strong>{{ $sel['defect_name'] ?? ('Defect #'.$sel['defect_id']) }}</strong></td>
                    <td style="text-align:center"><span class="sev-{{ $sev }}">{{ ucfirst($sev) }}</span></td>
                    <td style="text-align:center; font-weight:bold">{{ $sel['quantity'] ?? 1 }}</td>
                    <td style="color:#4b5563; font-size:8pt">{{ $sel['comment'] ?? '' }}</td>
                    <td style="text-align:center">
                        @foreach($defImgs->take(3) as $img)
                        @php $b64 = $imgBase64($img->file_path); @endphp
                        @if($b64)<img src="{{ $b64 }}" style="width:26px;height:26px;object-fit:cover;border:1px solid #fca5a5;margin:1px;border-radius:1px">@endif
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ AQL SAMPLING ════════════ --}}
        @elseif($secType === 'aql')
        @php $aql = $run->aql; @endphp
        @if($aql)
        <table style="width:100%; margin-bottom:10px; border-collapse:collapse">
            <tr>
                <td style="width:50%; padding-right:8px; vertical-align:top">
                    <table class="meta-table">
                        <tr><td class="mk">Lot Size</td><td class="mv">{{ number_format($aql->lot_size ?? 0) }} units</td></tr>
                        <tr><td class="mk">Inspection Level</td><td class="mv">{{ $aql->inspection_level ?? '—' }}</td></tr>
                    </table>
                </td>
                <td style="width:50%; vertical-align:top">
                    <table class="meta-table">
                        <tr><td class="mk">Code Letter</td><td class="mv">{{ $aql->code_letter ?? '—' }}</td></tr>
                        <tr><td class="mk">Sample Size</td><td class="mv">{{ $aql->sample_size ?? '—' }} units</td></tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="aql-table">
            <thead>
                <tr>
                    <th style="text-align:left; width:22%">Defect Category</th>
                    <th>AQL Level</th>
                    <th>Accept (Ac)</th>
                    <th>Reject (Re)</th>
                    <th>Found</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['critical' => 'Critical', 'major' => 'Major', 'minor' => 'Minor'] as $key => $label)
                @php
                    $aqlLevel = $aql->{"aql_{$key}"};
                    $ac       = $aql->{"ac_{$key}"};
                    $re       = $aql->{"re_{$key}"};
                    $found    = $aql->{"found_{$key}"} ?? 0;
                    $rowRes   = ($ac !== null && $found > $ac) ? 'FAIL' : (($found > 0 || $ac !== null) ? 'PASS' : '—');
                    $rowCls   = $rowRes === 'FAIL' ? 'rb-fail' : ($rowRes === 'PASS' ? 'rb-pass' : '');
                @endphp
                <tr>
                    <td class="aql-cat">{{ $label }}</td>
                    <td>{{ $aqlLevel !== null ? $aqlLevel.'%' : '—' }}</td>
                    <td>{{ $ac ?? '—' }}</td>
                    <td>{{ $re ?? '—' }}</td>
                    <td style="font-weight:bold">{{ $found }}</td>
                    <td><span class="{{ $rowCls }}">{{ $rowRes }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @php
            $avbClass = match(strtolower($aql->verdict ?? '')) {
                'pass' => 'avb-pass', 'fail' => 'avb-fail', default => 'avb-pending'
            };
        @endphp
        <div class="aql-verdict-block {{ $avbClass }}">
            AQL Sampling Verdict: {{ strtoupper($aql->verdict ?? 'Pending') }}
        </div>
        @if($aql->notes)
        <div class="section-note" style="margin-top:8px">{{ $aql->notes }}</div>
        @endif
        @else
        <div class="empty-state">No AQL sampling data recorded.</div>
        @endif

        {{-- ════════════ IMAGES / PRODUCT SCREENING ════════════ --}}
        @elseif($secType === 'images')
        @if($images->isEmpty())
        <div class="empty-state">No product images uploaded.</div>
        @else
        @foreach($images->chunk(4) as $chunk)
        <table class="img-gallery-table" style="margin-bottom:6px">
            <tr>
                @foreach($chunk as $img)
                @php $b64 = $imgBase64($img->file_path); @endphp
                <td style="width:25%">
                    @if($b64)
                        <img src="{{ $b64 }}" class="img-thumb">
                        <div class="img-label">{{ $img->title ?: $img->file_name }}</div>
                    @else
                        <div style="width:112px;height:112px;border:2px dashed #d1d8e0;display:block;margin:0 auto;background:#f9fafb;text-align:center;font-size:7pt;color:#9ca3af;padding-top:48px">No Image</div>
                    @endif
                </td>
                @endforeach
                @for($p = $chunk->count(); $p < 4; $p++)<td></td>@endfor
            </tr>
        </table>
        @endforeach
        @endif
        @if(!empty($data['notes']))
        <div class="section-note">{{ $data['notes'] }}</div>
        @endif

        {{-- ════════════ CONTAINER DETAILS ════════════ --}}
        @elseif($secType === 'container')
        @php
            $cf = array_filter([
                'Container Number'    => $data['container_number'] ?? null,
                'Container Type'      => $data['container_type'] ?? null,
                'Seal Number'         => $data['seal_number'] ?? null,
                'Loading Date'        => $data['loading_date'] ?? null,
                'Loading Port'        => $data['loading_port'] ?? null,
                'Discharge Port'      => $data['discharge_port'] ?? null,
                'Total Cartons'       => $data['total_cartons_loaded'] ?? null,
                'Total Quantity'      => $data['total_qty_loaded'] ?? null,
                'Container Condition' => $data['container_condition'] ?? null,
            ], fn($v) => $v !== null && $v !== '');
            $cfHalf = (int) ceil(count($cf) / 2);
        @endphp
        <table style="width:100%; border-collapse:collapse">
            <tr>
                <td style="width:50%; padding-right:8px; vertical-align:top">
                    <table class="meta-table">
                        @foreach(array_slice($cf, 0, $cfHalf) as $label => $value)
                        <tr><td class="mk">{{ $label }}</td><td class="mv">{{ $value }}</td></tr>
                        @endforeach
                    </table>
                </td>
                <td style="width:50%; vertical-align:top">
                    <table class="meta-table">
                        @foreach(array_slice($cf, $cfHalf) as $label => $value)
                        <tr><td class="mk">{{ $label }}</td><td class="mv">{{ $value }}</td></tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
        @if($images->isNotEmpty())
        <div class="sub-heading">Container Photos</div>
        <table class="img-gallery-table">
            <tr>
                @foreach($images->take(4) as $img)
                @php $b64 = $imgBase64($img->file_path); @endphp
                <td style="width:25%">
                    @if($b64)<img src="{{ $b64 }}" class="img-thumb"><div class="img-label">{{ $img->title ?: $img->file_name }}</div>@endif
                </td>
                @endforeach
                @for($p = min($images->count(), 4); $p < 4; $p++)<td></td>@endfor
            </tr>
        </table>
        @endif

        {{-- ════════════ FINAL REVIEW / CONCLUSION ════════════ --}}
        @elseif($secType === 'review' || $secType === 'conclusion' || $slug === 'final_review')
        @php
            $rf = array_filter([
                'Overall QC Verdict' => $data['overall_verdict'] ?? null,
                'Inspector Name'     => $data['inspector_name'] ?? null,
                'Follow-up Date'     => $data['follow_up_date'] ?? null,
                'Conclusion'         => $data['conclusion'] ?? null,
                'Summary'            => $data['summary'] ?? null,
                'Notes / Remarks'    => $data['notes'] ?? null,
            ], fn($v) => $v !== null && $v !== '');
        @endphp
        @if(!empty($rf))
        <table class="meta-table">
            @foreach($rf as $label => $value)
            <tr>
                <td class="mk">{{ $label }}</td>
                <td class="mv">
                    @if($label === 'Overall QC Verdict')
                    @php $vc = match($value) {
                        'Pass'                   => 'vb-pass',
                        'Fail'                   => 'vb-fail',
                        'Conditional Pass'       => 'vb-conditional',
                        'Re-Inspection Required' => 'vb-conditional',
                        default                  => 'vb-pending',
                    }; @endphp
                    <span class="review-verdict-box {{ $vc }}">{{ $value }}</span>
                    @else
                        {{ $value }}
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        @endif

        {{-- ════════════ TASK LIST ════════════ --}}
        @elseif($secType === 'task_list')
        @php
            $taskDefs = $sec->default_data['tasks'] ?? [];
            $taskData = $data['tasks'] ?? [];
        @endphp
        @if(!empty($taskDefs))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:40%">Task / Checkpoint</th>
                    <th style="width:14%; text-align:center">Result</th>
                    <th>Notes</th>
                    <th style="width:65px; text-align:center">Photos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($taskDefs as $tIdx => $taskDef)
                @php
                    $tKey   = $taskDef['key'] ?? '';
                    $tEntry = $taskData[$tKey] ?? [];
                    $tRes   = $tEntry['selected'] ?? '—';
                    $tNotes = $tEntry['notes'] ?? ($tEntry['comments'] ?? '');
                    $tImgs  = $images->filter(fn($a) => $a->task_key === $tKey)->values();
                    $tCls   = match(strtolower((string)$tRes)) {
                        'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def'
                    };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9ca3af; font-size:8pt">{{ $tIdx+1 }}</td>
                    <td>{{ $taskDef['label'] ?? $tKey }}</td>
                    <td style="text-align:center"><span class="{{ $tCls }}">{{ $tRes ?: '—' }}</span></td>
                    <td style="color:#4b5563; font-size:8pt">{{ $tNotes }}</td>
                    <td style="text-align:center">
                        @foreach($tImgs->take(2) as $img)
                        @php $b64 = $imgBase64($img->file_path); @endphp
                        @if($b64)<img src="{{ $b64 }}" style="width:32px;height:32px;object-fit:cover;border:1px solid #d1d8e0;margin:1px;border-radius:1px">@endif
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ CARTON DIMENSIONS & WEIGHT ════════════ --}}
        @elseif($secType === 'cartons' || $slug === 'carton_dimensions_weight')
        @php
            $cstd = array_filter([
                'Length (cm)'        => $data['length'] ?? null,
                'Width (cm)'         => $data['width'] ?? null,
                'Height (cm)'        => $data['height'] ?? null,
                'Gross Weight (kg)'  => $data['gross_weight'] ?? null,
                'Net Weight (kg)'    => $data['net_weight'] ?? null,
                'CBM'                => $data['cbm'] ?? null,
                'Cartons per Pallet' => $data['cartons_per_pallet'] ?? null,
            ], fn($v) => $v !== null && $v !== '');
            $cstdHalf  = (int) ceil(count($cstd) / 2);
            $cartonRows = $data['items'] ?? [];
        @endphp
        @if(!empty($cstd))
        <div class="sub-heading" style="margin-top:0">Standard Specifications</div>
        <table style="width:100%; margin-bottom:12px; border-collapse:collapse">
            <tr>
                <td style="width:50%; padding-right:8px; vertical-align:top">
                    <table class="meta-table">
                        @foreach(array_slice($cstd, 0, $cstdHalf) as $label => $value)
                        <tr><td class="mk">{{ $label }}</td><td class="mv">{{ $value }}</td></tr>
                        @endforeach
                    </table>
                </td>
                <td style="width:50%; vertical-align:top">
                    <table class="meta-table">
                        @foreach(array_slice($cstd, $cstdHalf) as $label => $value)
                        <tr><td class="mk">{{ $label }}</td><td class="mv">{{ $value }}</td></tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
        @endif
        @if(!empty($cartonRows))
        <div class="sub-heading">Measured Cartons</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Carton No.</th>
                    <th style="text-align:center">Length</th>
                    <th style="text-align:center">Width</th>
                    <th style="text-align:center">Height</th>
                    <th style="text-align:center">Gross Wt.</th>
                    <th style="text-align:center">Net Wt.</th>
                    <th style="text-align:center">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cartonRows as $ci)
                @php
                    $ciRes = $ci['result'] ?? '—';
                    $ciCls = match(strtolower((string)$ciRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="font-weight:bold">{{ $ci['carton_no'] ?? ($loop->index+1) }}</td>
                    <td style="text-align:center">{{ $ci['length'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $ci['width'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $ci['height'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $ci['gross_weight'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $ci['net_weight'] ?? '—' }}</td>
                    <td style="text-align:center"><span class="{{ $ciCls }}">{{ $ciRes }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ VERIFICATION ════════════ --}}
        @elseif($secType === 'verification')
        @php
            $verItems = $data['items'] ?? [];
            $verExtra = collect($data)->except('items')->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
        @endphp
        @if($verExtra->isNotEmpty())
        <table class="meta-table" style="margin-bottom:10px">
            @foreach($verExtra as $k => $v)
            <tr><td class="mk">{{ ucwords(str_replace('_', ' ', $k)) }}</td><td class="mv">{{ $v }}</td></tr>
            @endforeach
        </table>
        @endif
        @if(!empty($verItems))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:50%">Verification Item</th>
                    <th style="width:18%; text-align:center">Result</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($verItems as $vIdx => $vi)
                @php
                    $viRes = $vi['result'] ?? '—';
                    $viCls = match(strtolower((string)$viRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9ca3af; font-size:8pt">{{ $vIdx+1 }}</td>
                    <td>{{ $vi['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $viCls }}">{{ $viRes }}</span></td>
                    <td style="color:#4b5563; font-size:8pt">{{ $vi['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ ARTICLE RESULTS ════════════ --}}
        @elseif($secType === 'article_results')
        @php
            $artItems = $data['items'] ?? [];
            $artExtra = collect($data)->except('items')->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
        @endphp
        @if($artExtra->isNotEmpty())
        <table class="meta-table" style="margin-bottom:10px">
            @foreach($artExtra as $k => $v)
            <tr><td class="mk">{{ ucwords(str_replace('_', ' ', $k)) }}</td><td class="mv">{{ $v }}</td></tr>
            @endforeach
        </table>
        @endif
        @if(!empty($artItems))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:45%">Parameter</th>
                    <th style="width:18%; text-align:center">Result</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($artItems as $aIdx => $ai)
                @php
                    $aiRes = $ai['result'] ?? '—';
                    $aiCls = match(strtolower((string)$aiRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9ca3af; font-size:8pt">{{ $aIdx+1 }}</td>
                    <td>{{ $ai['label'] ?? $ai['parameter'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $aiCls }}">{{ $aiRes }}</span></td>
                    <td style="color:#4b5563; font-size:8pt">{{ $ai['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ MEASUREMENT CHECK ════════════ --}}
        @elseif($slug === 'measurement_check')
        @php $measurements = $data['measurements'] ?? []; @endphp
        @if(!empty($measurements))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:34%">Measurement Point</th>
                    <th style="width:14%; text-align:center">Spec</th>
                    <th style="width:14%; text-align:center">Tolerance</th>
                    <th style="width:14%; text-align:center">Measured</th>
                    <th style="width:12%; text-align:center">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($measurements as $mIdx => $m)
                @php
                    $mRes = $m['result'] ?? '—';
                    $mCls = match(strtolower((string)$mRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9ca3af; font-size:8pt">{{ $mIdx+1 }}</td>
                    <td>{{ $m['point'] ?? '' }}</td>
                    <td style="text-align:center">{{ $m['spec'] ?? '—' }}</td>
                    <td style="text-align:center; color:#6b7280">{{ $m['tolerance'] ?? '—' }}</td>
                    <td style="text-align:center; font-weight:bold">{{ $m['measured'] ?? '—' }}</td>
                    <td style="text-align:center"><span class="{{ $mCls }}">{{ $mRes }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @if(!empty($data['notes']))
        <div class="section-note" style="margin-top:8px">{{ $data['notes'] }}</div>
        @endif

        {{-- ════════════ GENERIC FALLBACK ════════════ --}}
        @else
        @php
            $fbData  = collect($data)->except('items')->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
            $fbItems = $data['items'] ?? [];
        @endphp
        @if($fbData->isNotEmpty())
        <table class="meta-table" style="margin-bottom:10px">
            @foreach($fbData as $k => $v)
            <tr><td class="mk">{{ ucwords(str_replace('_', ' ', $k)) }}</td><td class="mv">{{ $v }}</td></tr>
            @endforeach
        </table>
        @endif
        @if(!empty($fbItems))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:50%">Item</th>
                    <th style="width:18%; text-align:center">Result</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fbItems as $fIdx => $fi)
                @php
                    $fiRes = $fi['result'] ?? '—';
                    $fiCls = match(strtolower((string)$fiRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9ca3af; font-size:8pt">{{ $fIdx+1 }}</td>
                    <td>{{ $fi['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $fiCls }}">{{ $fiRes }}</span></td>
                    <td style="color:#4b5563; font-size:8pt">{{ $fi['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @if($images->isEmpty() && $fbData->isEmpty() && empty($fbItems))
        <div class="empty-state">No data recorded for this section.</div>
        @endif
        @endif

        {{-- ── General section-level images (non-task-keyed) ─────────────── --}}
        @php $generalImages = $images->filter(fn($a) => empty($a->task_key))->values(); @endphp
        @if($generalImages->isNotEmpty() && $secType !== 'images')
        <div class="sub-heading">Attached Photos</div>
        <table class="img-gallery-table">
            <tr>
                @foreach($generalImages->take(4) as $img)
                @php $b64 = $imgBase64($img->file_path); @endphp
                <td style="width:25%">
                    @if($b64)
                        <img src="{{ $b64 }}" class="img-thumb">
                        <div class="img-label">{{ $img->title ?: $img->file_name }}</div>
                    @endif
                </td>
                @endforeach
                @for($p = min($generalImages->count(), 4); $p < 4; $p++)<td></td>@endfor
            </tr>
        </table>
        @if($generalImages->count() > 4)
        <div style="font-size:7.5pt; color:#6b7280; margin-top:3px">
            + {{ $generalImages->count() - 4 }} more photo(s) not shown
        </div>
        @endif
        @endif

        {{-- ── Documents ──────────────────────────────────────────────────── --}}
        @if($docs->isNotEmpty())
        <div class="sub-heading">Attached Documents</div>
        <table class="meta-table" style="margin-top:4px">
            @foreach($docs as $doc)
            <tr>
                <td class="mk">{{ $doc->title ?: $doc->file_name }}</td>
                <td class="mv" style="color:#6b7280; font-size:8pt">{{ $doc->file_name }} ({{ $doc->humanFileSize() }})</td>
            </tr>
            @endforeach
        </table>
        @endif

        {{-- ── Section notes ──────────────────────────────────────────────── --}}
        @if($rs->notes)
        <div class="section-note" style="margin-top:8px">
            <strong>Notes:</strong> {{ $rs->notes }}
        </div>
        @endif

    </div>{{-- /.section-body --}}
</div>{{-- /.section-block --}}

@endforeach {{-- sections --}}

@endforeach {{-- runs --}}

</body>
</html>
