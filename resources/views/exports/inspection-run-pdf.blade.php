<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Inspection Report &mdash; {{ $inspection->report_number }}</title>
<style>

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 8.5pt;
    color: #212121;
    line-height: 1.55;
}

@page {
    size: A4 portrait;
    margin: 20mm 15mm 22mm 15mm;
}

.page-break { page-break-after: always; }
.no-break   { page-break-inside: avoid; }

/* ── Fixed footer ──────────────────────────────────────────────────── */
.pdf-footer {
    position: fixed;
    bottom: -18mm;
    left: -15mm; right: -15mm;
    border-top: 2px solid #1A3560;
    padding: 4px 15mm 0;
    font-size: 6.5pt;
    color: #9E9E9E;
    background: #ffffff;
}
.pdf-footer table { width: 100%; }
.pdf-footer .fn-left   { color: #9E9E9E; }
.pdf-footer .fn-center { text-align: center; color: #1565C0; font-weight: bold; font-size: 7pt; }
.pdf-footer .fn-right  { text-align: right; }
.pdf-footer .fn-right:after { content: "Page " counter(page) " of " counter(pages); }

/* ── Cover banner ──────────────────────────────────────────────────── */
.cover-banner {
    background: #0D2B4E;
    color: #ffffff;
    padding: 18px 18px;
    border-radius: 4px 4px 0 0;
    margin-bottom: 0;
}
.cover-banner table { width: 100%; }
.cb-system-label {
    font-size: 6.5pt;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #7FB3D3;
    margin-bottom: 3px;
}
.cb-company { font-size: 16pt; font-weight: bold; }
.cb-tagline { font-size: 7.5pt; color: #B0C4D8; margin-top: 3px; }
.cb-right   { text-align: right; vertical-align: bottom; }
.cb-repnum  { font-size: 11pt; font-weight: bold; }
.cb-date    { font-size: 7.5pt; color: #B0C4D8; margin-top: 3px; }
.cb-type    { font-size: 6.5pt; color: #7FB3D3; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 3px; }

/* ── Cover title strip ─────────────────────────────────────────────── */
.cover-title-strip {
    background: #F5F7FA;
    border-left: 5px solid #C8951A;
    border-bottom: 1px solid #DEE2E6;
    padding: 11px 16px;
    margin-bottom: 16px;
}
.cover-main-title {
    font-size: 18pt;
    font-weight: bold;
    color: #0D2B4E;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}
.cover-main-subtitle {
    font-size: 9pt;
    color: #546E7A;
    margin-top: 3px;
}

/* ── Info panels ───────────────────────────────────────────────────── */
.info-panel {
    border: 1px solid #DEE2E6;
    border-top: 3px solid #1565C0;
    border-radius: 3px;
    margin-bottom: 12px;
}
.info-panel-header {
    background: #F5F7FA;
    padding: 6px 12px;
    border-bottom: 1px solid #DEE2E6;
    font-size: 6.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #546E7A;
}
.info-panel-body { padding: 10px 12px; }

.cover-kv { width: 100%; border-collapse: collapse; }
.cover-kv td { padding: 4px 0; font-size: 8.5pt; border-bottom: 1px solid #F5F5F5; vertical-align: top; }
.cover-kv .ck { color: #9E9E9E; width: 44%; font-size: 8pt; }
.cover-kv .cv { color: #212121; font-weight: 500; }
.cover-kv tr:last-child td { border-bottom: none; }

/* ── Overall Verdict badge ─────────────────────────────────────────── */
.verdict-badge-lg {
    display: block;
    padding: 12px 0;
    font-size: 18pt;
    font-weight: bold;
    letter-spacing: 3px;
    border-radius: 4px;
    text-align: center;
    margin-bottom: 14px;
}
.vb-pass        { background: #E8F5E9; color: #2E7D32; border: 2px solid #2E7D32; }
.vb-fail        { background: #FFEBEE; color: #C62828; border: 2px solid #C62828; }
.vb-conditional { background: #FFF3E0; color: #E65100; border: 2px solid #E65100; }
.vb-pending     { background: #F5F5F5; color: #757575; border: 2px solid #BDBDBD; }

.defect-pill-row { width: 100%; border-collapse: collapse; }
.defect-pill-row td { text-align: center; padding: 3px; }
.dp-box { border-radius: 4px; padding: 7px 4px; display: block; }
.dp-critical { background: #FFEBEE; }
.dp-major    { background: #FFF8E1; }
.dp-minor    { background: #E3F2FD; }
.dp-num { font-size: 14pt; font-weight: bold; display: block; }
.dp-crit-num { color: #C62828; }
.dp-maj-num  { color: #E65100; }
.dp-min-num  { color: #1565C0; }
.dp-lbl { font-size: 6.5pt; text-transform: uppercase; letter-spacing: 1px; color: #9E9E9E; display: block; margin-top: 2px; }

/* ── Section Summary Dashboard ─────────────────────────────────────── */
.summary-dashboard {
    border: 1px solid #DEE2E6;
    border-top: 3px solid #1565C0;
    border-radius: 3px;
    margin-bottom: 14px;
}
.summary-dashboard-hdr {
    background: #1565C0;
    padding: 7px 14px;
}
.summary-dashboard-hdr-text {
    font-size: 7pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #ffffff;
}
.summary-table { width: 100%; border-collapse: collapse; }
.summary-table th {
    background: #F5F7FA;
    padding: 6px 12px;
    font-size: 6.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #546E7A;
    border-bottom: 1px solid #DEE2E6;
    text-align: left;
}
.summary-table td {
    padding: 6px 12px;
    font-size: 8pt;
    border-bottom: 1px solid #F5F5F5;
    vertical-align: middle;
}
.summary-table tbody tr:last-child td { border-bottom: none; }
.summary-table .st-idx     { width: 28px; text-align: center; color: #9E9E9E; font-size: 7.5pt; }
.summary-table .st-section { font-weight: 500; color: #212121; }
.summary-table .st-detail  { color: #9E9E9E; font-size: 7.5pt; }
.summary-table .st-status  { text-align: right; width: 90px; }

/* ── Status badges (pill) ──────────────────────────────────────────── */
.badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 7pt;
    font-weight: bold;
    letter-spacing: 0.3px;
}
.badge-pass    { background: #E8F5E9; color: #2E7D32; }
.badge-fail    { background: #FFEBEE; color: #C62828; }
.badge-na      { background: #F5F5F5; color: #757575; }
.badge-pending { background: #FFF8E1; color: #E65100; }

/* ── Runs overview box ─────────────────────────────────────────────── */
.runs-box { border: 1px solid #DEE2E6; border-radius: 3px; margin-top: 14px; }
.runs-box-hdr {
    background: #F5F7FA;
    padding: 6px 14px;
    border-bottom: 1px solid #DEE2E6;
    font-size: 6.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #546E7A;
}

/* ── Disclaimer ────────────────────────────────────────────────────── */
.disclaimer {
    margin-top: 16px;
    padding: 9px 14px;
    background: #F5F7FA;
    border: 1px solid #DEE2E6;
    border-left: 3px solid #BDBDBD;
    font-size: 7pt;
    color: #9E9E9E;
    border-radius: 0 3px 3px 0;
}

/* ── Run page header ───────────────────────────────────────────────── */
.run-page-header {
    background: #0D2B4E;
    color: #ffffff;
    padding: 12px 16px;
    border-radius: 4px;
    margin-bottom: 14px;
}
.run-page-header table { width: 100%; }
.rph-label { font-size: 6.5pt; letter-spacing: 2px; text-transform: uppercase; color: #7FB3D3; }
.rph-title { font-size: 13pt; font-weight: bold; margin-top: 2px; }
.rph-meta  { font-size: 7.5pt; color: #B0C4D8; margin-top: 4px; }
.rph-verdict-cell { text-align: right; vertical-align: middle; width: 150px; }
.rph-verdict-label { font-size: 6.5pt; text-transform: uppercase; letter-spacing: 1.5px; color: #7FB3D3; margin-bottom: 5px; }
.rph-verdict-badge {
    display: inline-block;
    padding: 7px 18px;
    font-size: 10pt;
    font-weight: bold;
    border-radius: 4px;
    letter-spacing: 1px;
}

/* ── Section blocks ────────────────────────────────────────────────── */
.section-block {
    margin-bottom: 12px;
    border: 1px solid #DEE2E6;
    border-left: 4px solid #1565C0;
    border-radius: 0 3px 3px 0;
    page-break-inside: avoid;
}
.section-hdr {
    background: #F5F7FA;
    padding: 6px 12px;
    border-bottom: 1px solid #DEE2E6;
}
.section-hdr table { width: 100%; }
.sec-num {
    background: #1565C0;
    color: #ffffff;
    font-size: 6.5pt;
    font-weight: bold;
    padding: 2px 7px;
    border-radius: 3px;
    margin-right: 6px;
    display: inline-block;
}
.sec-name { font-size: 9.5pt; font-weight: bold; color: #0D2B4E; }

.section-body { padding: 10px 14px; }

.section-note {
    margin-top: 8px;
    padding: 7px 10px;
    background: #FFFDE7;
    border-left: 3px solid #F9A825;
    font-size: 8pt;
    color: #5D4037;
    border-radius: 0 3px 3px 0;
}

.sub-heading {
    font-size: 7pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #546E7A;
    margin: 10px 0 6px;
    padding-bottom: 3px;
    border-bottom: 1px solid #EEEEEE;
}

/* ── Meta table ────────────────────────────────────────────────────── */
.meta-table { width: 100%; border-collapse: collapse; }
.meta-table td {
    padding: 4px 8px;
    font-size: 8.5pt;
    border-bottom: 1px solid #F5F5F5;
    vertical-align: top;
}
.meta-table .mk { width: 36%; font-weight: 600; color: #546E7A; white-space: nowrap; }
.meta-table .mv { color: #212121; }
.meta-table tr:last-child td { border-bottom: none; }

/* ── Data / checklist table ────────────────────────────────────────── */
.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    background: #1A3560;
    color: #ffffff;
    padding: 6px 9px;
    font-size: 7pt;
    font-weight: bold;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.data-table td {
    padding: 5px 9px;
    font-size: 8.5pt;
    border-bottom: 1px solid #F0F0F0;
    vertical-align: top;
}
.data-table tbody tr:nth-child(even) td { background: #FAFAFA; }
.data-table tbody tr:last-child td { border-bottom: none; }

/* ── Result badges (inline / table) ───────────────────────────────── */
.rb-pass { color: #2E7D32; font-weight: bold; background: #E8F5E9; padding: 2px 8px; border-radius: 10px; font-size: 7pt; }
.rb-fail { color: #C62828; font-weight: bold; background: #FFEBEE; padding: 2px 8px; border-radius: 10px; font-size: 7pt; }
.rb-na   { color: #757575; background: #F5F5F5; padding: 2px 8px; border-radius: 10px; font-size: 7pt; }
.rb-def  { color: #9E9E9E; font-size: 7.5pt; }

/* ── AQL table ─────────────────────────────────────────────────────── */
.aql-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
.aql-table th {
    background: #0D2B4E;
    color: #ffffff;
    padding: 6px 9px;
    font-size: 7pt;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.aql-table td {
    padding: 5px 9px;
    font-size: 8.5pt;
    border: 1px solid #DEE2E6;
    text-align: center;
}
.aql-table tbody tr:nth-child(even) td { background: #FAFAFA; }
.aql-cat { font-weight: bold; text-align: left !important; }

.aql-verdict-block {
    margin-top: 10px;
    padding: 9px 14px;
    border-radius: 4px;
    font-size: 10.5pt;
    font-weight: bold;
    text-align: center;
}
.avb-pass    { background: #E8F5E9; color: #2E7D32; border: 1px solid #A5D6A7; }
.avb-fail    { background: #FFEBEE; color: #C62828; border: 1px solid #EF9A9A; }
.avb-pending { background: #F5F5F5; color: #757575; border: 1px solid #E0E0E0; }

/* ── Defect table ──────────────────────────────────────────────────── */
.defect-table { width: 100%; border-collapse: collapse; }
.defect-table th {
    background: #7F1D1D;
    color: #ffffff;
    padding: 6px 9px;
    font-size: 7pt;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.defect-table td {
    padding: 5px 9px;
    font-size: 8.5pt;
    border-bottom: 1px solid #FFCDD2;
    vertical-align: top;
}
.defect-table tbody tr:nth-child(even) td { background: #FFF9F9; }
.defect-table tbody tr:last-child td { border-bottom: none; }

.sev-critical  { background: #FFEBEE; color: #C62828; font-weight: bold; padding: 2px 7px; border-radius: 10px; font-size: 7pt; }
.sev-major     { background: #FFF8E1; color: #E65100; font-weight: bold; padding: 2px 7px; border-radius: 10px; font-size: 7pt; }
.sev-minor     { background: #E3F2FD; color: #1565C0; font-weight: bold; padding: 2px 7px; border-radius: 10px; font-size: 7pt; }
.sev-functional{ background: #F5F5F5; color: #546E7A; font-weight: bold; padding: 2px 7px; border-radius: 10px; font-size: 7pt; }

/* ── Image gallery — 3 columns ─────────────────────────────────────── */
.img-gallery-table { width: 100%; border-collapse: collapse; }
.img-gallery-table td { padding: 5px; vertical-align: top; text-align: center; }
.img-thumb {
    width: 145px;
    height: 145px;
    object-fit: cover;
    border: 2px solid #DEE2E6;
    border-radius: 3px;
    display: block;
    margin: 0 auto 5px;
}
.img-label { font-size: 7pt; color: #9E9E9E; word-break: break-word; }

/* ── Review verdict box ────────────────────────────────────────────── */
.review-verdict-box {
    display: inline-block;
    padding: 3px 14px;
    border-radius: 10px;
    font-size: 9.5pt;
    font-weight: bold;
}

/* ── Empty state ───────────────────────────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 20px;
    color: #9E9E9E;
    font-size: 8pt;
    font-style: italic;
}

/* ── Final Decision Card ───────────────────────────────────────────── */
.final-card {
    border: 2px solid #1565C0;
    border-radius: 4px;
    margin-top: 20px;
    page-break-inside: avoid;
}
.final-card-hdr {
    background: #0D2B4E;
    color: #ffffff;
    padding: 12px 18px;
    border-radius: 3px 3px 0 0;
}
.final-card-hdr-title {
    font-size: 9pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
}
.final-card-hdr-sub {
    font-size: 7pt;
    color: #7FB3D3;
    margin-top: 2px;
    letter-spacing: 1px;
}
.final-card-body { padding: 18px; }

.final-verdict-badge {
    display: block;
    padding: 14px 0;
    font-size: 22pt;
    font-weight: bold;
    letter-spacing: 4px;
    border-radius: 4px;
    text-align: center;
    margin-bottom: 16px;
}

.final-remarks-box {
    border: 1px solid #DEE2E6;
    border-left: 4px solid #1565C0;
    border-radius: 0 3px 3px 0;
    padding: 10px 14px;
    background: #F5F7FA;
    margin-bottom: 14px;
}
.final-remarks-label {
    font-size: 6.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #9E9E9E;
    margin-bottom: 5px;
}
.final-remarks-text {
    font-size: 9pt;
    color: #212121;
    line-height: 1.6;
}
.final-meta-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.final-meta-table td { padding: 4px 0; font-size: 8.5pt; border-bottom: 1px solid #F5F5F5; }
.final-meta-table .fmk { color: #9E9E9E; width: 30%; font-size: 8pt; }
.final-meta-table .fmv { color: #212121; font-weight: 500; }
.final-meta-table tr:last-child td { border-bottom: none; }

.sig-row { width: 100%; border-collapse: collapse; margin-top: 22px; }
.sig-row td { text-align: center; padding: 0 10px; vertical-align: bottom; width: 33%; }
.sig-line { border-top: 1px solid #BDBDBD; padding-top: 5px; margin-top: 30px; font-size: 7.5pt; color: #9E9E9E; }

</style>
</head>
<body>

{{-- ══════════════════════════════════════════ GLOBAL PHP SETUP ════════════ --}}
@php
$hiddenSlugs = [
    'corrective_action', 'inspection_conclusion', 'finish_inspection',
    'textile_sample_conformity', 'denim_textile_defects',
    'cover_photo', 'workmanship_check',
];

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

// Build Section Summary Dashboard data
$summaryRows = [];
foreach ($runs as $sumRun) {
    foreach ($sumRun->runSections as $sumRs) {
        if (!$sumRs->section || in_array($sumRs->section->slug, $hiddenSlugs)) continue;
        $sName = $sumRs->section->name;
        $sType = $sumRs->section->section_type;
        $sSlug = $sumRs->section->slug;

        if (!isset($summaryRows[$sName])) {
            $summaryRows[$sName] = [
                'type'        => $sType,
                'slug'        => $sSlug,
                'statuses'    => [],
                'passCount'   => 0,
                'failCount'   => 0,
                'naCount'     => 0,
                'totalCount'  => 0,
                'customDetail'=> '',
            ];
        }
        $summaryRows[$sName]['statuses'][] = $sumRs->status;

        if (in_array($sType, ['checklist', 'task_list', 'verification', 'article_results'])) {
            foreach ($sumRs->data['items'] ?? [] as $si) {
                $sr = strtolower((string)($si['result'] ?? $si['selected'] ?? ''));
                $summaryRows[$sName]['totalCount']++;
                if ($sr === 'pass') $summaryRows[$sName]['passCount']++;
                elseif ($sr === 'fail') $summaryRows[$sName]['failCount']++;
                elseif (in_array($sr, ['n/a', 'na'])) $summaryRows[$sName]['naCount']++;
            }
        } elseif ($sSlug === 'measurement_check') {
            foreach ($sumRs->data['measurements'] ?? [] as $sm) {
                $smr = strtolower((string)($sm['result'] ?? ''));
                $summaryRows[$sName]['totalCount']++;
                if ($smr === 'pass') $summaryRows[$sName]['passCount']++;
                elseif ($smr === 'fail') $summaryRows[$sName]['failCount']++;
            }
        } elseif ($sType === 'aql') {
            $aqlV = strtolower($sumRun->aql?->verdict ?? '');
            if ($aqlV === 'pass') $summaryRows[$sName]['passCount']++;
            elseif ($aqlV === 'fail') $summaryRows[$sName]['failCount']++;
            $summaryRows[$sName]['customDetail'] = 'Verdict: '.($sumRun->aql?->verdict ?? 'Pending');
        } elseif (in_array($sType, ['defects']) || $sSlug === 'defect_recording') {
            $dSels  = collect($sumRs->data['selections'] ?? [])->filter(fn($s) => !empty($s['selected']));
            $dCritD = $dSels->where('severity', 'critical')->count();
            $dMajD  = $dSels->where('severity', 'major')->count();
            $dMinD  = $dSels->where('severity', 'minor')->count();
            $dTotal = $dCritD + $dMajD + $dMinD;
            $summaryRows[$sName]['customDetail'] = $dTotal > 0
                ? $dCritD.' Crit, '.$dMajD.' Major, '.$dMinD.' Minor'
                : 'None recorded';
        }
    }
}
@endphp

{{-- ══════════════════════════════════════════ FIXED FOOTER ════════════════ --}}
<div class="pdf-footer">
    <table>
        <tr>
            <td class="fn-left">Confidential &mdash; For Authorized Recipients Only &mdash; TradeSyncERP</td>
            <td class="fn-center">{{ $inspection->report_number }}</td>
            <td class="fn-right"></td>
        </tr>
    </table>
</div>

{{-- ══════════════════════════════════════════ COVER PAGE ═════════════════ --}}

{{-- Cover Banner --}}
<div class="cover-banner">
    <table>
        <tr>
            <td style="vertical-align: bottom">
                <div class="cb-system-label">Quality Assurance &amp; Inspection Management</div>
                <div class="cb-company">TradeSyncERP</div>
                <div class="cb-tagline">Inspection &amp; Quality Control System</div>
            </td>
            <td class="cb-right">
                <div class="cb-type">{{ $inspection->inspectionType?->name ?? 'Inspection Report' }}</div>
                <div class="cb-repnum">{{ $inspection->report_number }}</div>
                <div class="cb-date">{{ $inspection->inspection_date?->format('d F Y') ?? now()->format('d F Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Title Strip --}}
<div class="cover-title-strip">
    <div class="cover-main-title">Quality Inspection Report</div>
    <div class="cover-main-subtitle">{{ $inspection->inspectionType?->name ?? 'Quality Inspection' }} &mdash; Generated {{ now()->format('d F Y, H:i') }}</div>
</div>

{{-- Two-column: Report Info + Overall Verdict --}}
<table style="width:100%; border-collapse:collapse">
    <tr>
        <td style="width:58%; padding-right:10px; vertical-align:top">
            <div class="info-panel">
                <div class="info-panel-header">Report Information</div>
                <div class="info-panel-body">
                    <table class="cover-kv">
                        <tr><td class="ck">Report Number</td><td class="cv">{{ $inspection->report_number }}</td></tr>
                        <tr><td class="ck">Inspection Type</td><td class="cv">{{ $inspection->inspectionType?->name ?? '&mdash;' }}</td></tr>
                        <tr><td class="ck">Inspection Date</td><td class="cv">{{ $inspection->inspection_date?->format('d F Y') ?? '&mdash;' }}</td></tr>
                        <tr><td class="ck">Inspector(s)</td><td class="cv">{{ $inspection->inspectors->pluck('employee_name')->implode(', ') ?: '&mdash;' }}</td></tr>
                        <tr><td class="ck">Customer Orders</td><td class="cv">{{ $inspection->customerOrders->count() > 0 ? $inspection->customerOrders->count().' order(s)' : '&mdash;' }}</td></tr>
                        <tr><td class="ck">Total Runs</td><td class="cv">{{ $runs->count() }}</td></tr>
                        <tr><td class="ck">Report Generated</td><td class="cv">{{ now()->format('d F Y, H:i') }}</td></tr>
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
                    <div style="font-size:6.5pt; font-weight:bold; text-transform:uppercase; letter-spacing:1px; color:#9E9E9E; margin-bottom:6px">Defect Summary</div>
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

{{-- ── Section Summary Dashboard ──────────────────────────────────────── --}}
@if(!empty($summaryRows))
<div class="summary-dashboard no-break">
    <div class="summary-dashboard-hdr">
        <div class="summary-dashboard-hdr-text">Inspection Sections &mdash; Summary Overview</div>
    </div>
    <table class="summary-table">
        <thead>
            <tr>
                <th class="st-idx">#</th>
                <th class="st-section">Section</th>
                <th class="st-detail">Detail</th>
                <th class="st-status">Status</th>
            </tr>
        </thead>
        <tbody>
        @foreach($summaryRows as $sRowName => $sRow)
        @php
            $allStatuses = $sRow['statuses'];
            $allNa   = count(array_filter($allStatuses, fn($s) => $s === 'na')) === count($allStatuses);
            $allDone = count(array_filter($allStatuses, fn($s) => in_array($s, ['complete', 'na']))) === count($allStatuses);

            if ($allNa) {
                $sBadge = 'badge-na'; $sLabel = 'N/A';
            } elseif (!$allDone) {
                $sBadge = 'badge-pending'; $sLabel = 'PENDING';
            } elseif ($sRow['failCount'] > 0) {
                $sBadge = 'badge-fail'; $sLabel = 'FAIL';
            } else {
                $sBadge = 'badge-pass'; $sLabel = 'PASS';
            }

            if ($sRow['customDetail']) {
                $sDetailStr = $sRow['customDetail'];
            } elseif ($sRow['totalCount'] > 0) {
                $sParts = [];
                if ($sRow['passCount'] > 0) $sParts[] = $sRow['passCount'].' Passed';
                if ($sRow['failCount'] > 0) $sParts[] = $sRow['failCount'].' Failed';
                if ($sRow['naCount']   > 0) $sParts[] = $sRow['naCount'].' N/A';
                $sDetailStr = implode(', ', $sParts);
                if (!empty($sDetailStr) && in_array($sBadge, ['badge-pass','badge-fail'])) {
                    $sDetailStr .= ' &rarr; '.$sLabel;
                }
            } else {
                $sDetailStr = '';
            }
        @endphp
        <tr>
            <td class="st-idx">{{ $loop->iteration }}</td>
            <td class="st-section">{{ $sRowName }}</td>
            <td class="st-detail">{!! $sDetailStr !!}</td>
            <td class="st-status"><span class="badge {{ $sBadge }}">{{ $sLabel }}</span></td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Runs Overview ──────────────────────────────────────────────────── --}}
<div class="runs-box no-break">
    <div class="runs-box-hdr">Inspection Runs Overview</div>
    <table class="data-table" style="border:none">
        <thead>
            <tr>
                <th style="width:46px; text-align:center">Run #</th>
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
                <td style="text-align:center; font-weight:bold; color:#1565C0">{{ $r->run_number }}</td>
                <td style="font-weight:500">{{ $r->sample?->sample_code ?? '&mdash;' }}</td>
                <td>{{ $r->sample?->product_name ?? '&mdash;' }}</td>
                <td style="color:#546E7A">{{ $r->sample?->customer?->customer_name ?? '&mdash;' }}</td>
                <td style="text-align:center"><span class="{{ $rvc }}">{{ $r->verdict ?? 'Pending' }}</span></td>
                <td style="font-size:8pt; color:#9E9E9E">{{ $r->completed_at?->format('d M Y') ?? 'In Progress' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="disclaimer">
    <strong style="color:#546E7A">Confidentiality Notice:</strong>
    This inspection report is intended solely for the use of the named recipient and contains confidential quality control information.
    Any unauthorized review, disclosure, copying, distribution, or use of this report is strictly prohibited.
    Generated by TradeSyncERP &mdash; {{ now()->format('d F Y \a\t H:i') }}.
</div>

{{-- ══════════════════════════════════════════ RUNS DETAIL ════════════════ --}}

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
                <div class="rph-verdict-label">Verdict</div>
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

{{-- ─────────────────────────── SECTIONS ────────────────────────────── --}}
@foreach($visibleSections as $rs)
@php
    $secIdx++;
    $sec     = $rs->section;
    $secType = $sec->section_type;
    $slug    = $sec->slug;
    $data    = $rs->data ?? [];

    $statusLabel = match($rs->status) { 'complete' => 'Complete', 'na' => 'N/A', default => 'Pending' };
    $statusClass = match($rs->status) { 'complete' => 'badge-pass', 'na' => 'badge-na', default => 'badge-pending' };

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
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
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
                    <td style="text-align:center; color:#9E9E9E; font-size:8pt">{{ $idx+1 }}</td>
                    <td>{{ $item['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $rClass }}">{{ $item['result'] ?? '&mdash;' }}</span></td>
                    <td style="color:#546E7A; font-size:8pt">{{ $item['remarks'] ?? '' }}</td>
                    @if($images->isNotEmpty())
                    <td style="text-align:center">
                        @foreach($itemImgs->take(2) as $img)
                        @php $b64 = $imgBase64($img->file_path); @endphp
                        @if($b64)<img src="{{ $b64 }}" style="width:36px;height:36px;object-fit:cover;border:1px solid #DEE2E6;margin:1px;border-radius:2px">@endif
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
        <table style="width:100%; margin-bottom:10px; border-collapse:collapse">
            <tr>
                <td style="width:33%; padding:0 4px 0 0">
                    <div style="background:#FFEBEE; border-radius:4px; padding:7px 8px; text-align:center">
                        <div style="font-size:16pt; font-weight:bold; color:#C62828">{{ $dCrit }}</div>
                        <div style="font-size:6.5pt; color:#C62828; text-transform:uppercase; letter-spacing:1px">Critical</div>
                    </div>
                </td>
                <td style="width:33%; padding:0 4px">
                    <div style="background:#FFF8E1; border-radius:4px; padding:7px 8px; text-align:center">
                        <div style="font-size:16pt; font-weight:bold; color:#E65100">{{ $dMaj }}</div>
                        <div style="font-size:6.5pt; color:#E65100; text-transform:uppercase; letter-spacing:1px">Major</div>
                    </div>
                </td>
                <td style="width:33%; padding:0 0 0 4px">
                    <div style="background:#E3F2FD; border-radius:4px; padding:7px 8px; text-align:center">
                        <div style="font-size:16pt; font-weight:bold; color:#1565C0">{{ $dMin }}</div>
                        <div style="font-size:6.5pt; color:#1565C0; text-transform:uppercase; letter-spacing:1px">Minor</div>
                    </div>
                </td>
            </tr>
        </table>
        <table class="defect-table">
            <thead>
                <tr>
                    <th style="width:28px; text-align:center">#</th>
                    <th style="width:36%">Defect Description</th>
                    <th style="width:90px; text-align:center">Severity</th>
                    <th style="width:48px; text-align:center">Qty</th>
                    <th>Comment / Location</th>
                    <th style="width:80px; text-align:center">Photos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($selections as $i => $sel)
                @php
                    $sev     = $sel['severity'] ?? 'minor';
                    $defImgs = $images->filter(fn($a) => $a->task_key === 'defect_'.$sel['defect_id'])->values();
                @endphp
                <tr>
                    <td style="text-align:center; color:#9E9E9E; font-size:8pt">{{ $i+1 }}</td>
                    <td><strong>{{ $sel['defect_name'] ?? ('Defect #'.$sel['defect_id']) }}</strong></td>
                    <td style="text-align:center"><span class="sev-{{ $sev }}">{{ ucfirst($sev) }}</span></td>
                    <td style="text-align:center; font-weight:bold">{{ $sel['quantity'] ?? 1 }}</td>
                    <td style="color:#546E7A; font-size:8pt">{{ $sel['comment'] ?? '' }}</td>
                    <td style="text-align:center">
                        @foreach($defImgs->take(3) as $img)
                        @php $b64 = $imgBase64($img->file_path); @endphp
                        @if($b64)<img src="{{ $b64 }}" style="width:26px;height:26px;object-fit:cover;border:1px solid #FFCDD2;margin:1px;border-radius:2px">@endif
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
                        <tr><td class="mk">Inspection Level</td><td class="mv">{{ $aql->inspection_level ?? '&mdash;' }}</td></tr>
                    </table>
                </td>
                <td style="width:50%; vertical-align:top">
                    <table class="meta-table">
                        <tr><td class="mk">Code Letter</td><td class="mv">{{ $aql->code_letter ?? '&mdash;' }}</td></tr>
                        <tr><td class="mk">Sample Size</td><td class="mv">{{ $aql->sample_size ?? '&mdash;' }} units</td></tr>
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
                    $rowRes   = ($ac !== null && $found > $ac) ? 'FAIL' : (($found > 0 || $ac !== null) ? 'PASS' : '&mdash;');
                    $rowCls   = $rowRes === 'FAIL' ? 'rb-fail' : ($rowRes === 'PASS' ? 'rb-pass' : '');
                @endphp
                <tr>
                    <td class="aql-cat">{{ $label }}</td>
                    <td>{{ $aqlLevel !== null ? $aqlLevel.'%' : '&mdash;' }}</td>
                    <td>{{ $ac ?? '&mdash;' }}</td>
                    <td>{{ $re ?? '&mdash;' }}</td>
                    <td style="font-weight:bold">{{ $found }}</td>
                    <td><span class="{{ $rowCls }}">{!! $rowRes !!}</span></td>
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
        @foreach($images->chunk(3) as $chunk)
        <table class="img-gallery-table" style="margin-bottom:6px">
            <tr>
                @foreach($chunk as $img)
                @php $b64 = $imgBase64($img->file_path); @endphp
                <td style="width:33.33%">
                    @if($b64)
                        <img src="{{ $b64 }}" class="img-thumb">
                        <div class="img-label">{{ $img->title ?: $img->file_name }}</div>
                    @else
                        <div style="width:145px;height:145px;border:2px dashed #DEE2E6;display:block;margin:0 auto;background:#F9FAFB;text-align:center;font-size:7pt;color:#9E9E9E;padding-top:60px">No Image</div>
                    @endif
                </td>
                @endforeach
                @for($p = $chunk->count(); $p < 3; $p++)<td style="width:33.33%"></td>@endfor
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
                @foreach($images->take(3) as $img)
                @php $b64 = $imgBase64($img->file_path); @endphp
                <td style="width:33.33%">
                    @if($b64)<img src="{{ $b64 }}" class="img-thumb"><div class="img-label">{{ $img->title ?: $img->file_name }}</div>@endif
                </td>
                @endforeach
                @for($p = min($images->count(), 3); $p < 3; $p++)<td style="width:33.33%"></td>@endfor
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
                    $tRes   = $tEntry['selected'] ?? '&mdash;';
                    $tNotes = $tEntry['notes'] ?? ($tEntry['comments'] ?? '');
                    $tImgs  = $images->filter(fn($a) => $a->task_key === $tKey)->values();
                    $tCls   = match(strtolower((string)$tRes)) {
                        'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def'
                    };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9E9E9E; font-size:8pt">{{ $tIdx+1 }}</td>
                    <td>{{ $taskDef['label'] ?? $tKey }}</td>
                    <td style="text-align:center"><span class="{{ $tCls }}">{!! $tRes ?: '&mdash;' !!}</span></td>
                    <td style="color:#546E7A; font-size:8pt">{{ $tNotes }}</td>
                    <td style="text-align:center">
                        @foreach($tImgs->take(2) as $img)
                        @php $b64 = $imgBase64($img->file_path); @endphp
                        @if($b64)<img src="{{ $b64 }}" style="width:32px;height:32px;object-fit:cover;border:1px solid #DEE2E6;margin:1px;border-radius:2px">@endif
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
            $cstdHalf   = (int) ceil(count($cstd) / 2);
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
                    $ciRes = $ci['result'] ?? '&mdash;';
                    $ciCls = match(strtolower((string)$ciRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="font-weight:bold">{{ $ci['carton_no'] ?? ($loop->index+1) }}</td>
                    <td style="text-align:center">{{ $ci['length'] ?? '&mdash;' }}</td>
                    <td style="text-align:center">{{ $ci['width'] ?? '&mdash;' }}</td>
                    <td style="text-align:center">{{ $ci['height'] ?? '&mdash;' }}</td>
                    <td style="text-align:center">{{ $ci['gross_weight'] ?? '&mdash;' }}</td>
                    <td style="text-align:center">{{ $ci['net_weight'] ?? '&mdash;' }}</td>
                    <td style="text-align:center"><span class="{{ $ciCls }}">{!! $ciRes !!}</span></td>
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
                    $viRes = $vi['result'] ?? '&mdash;';
                    $viCls = match(strtolower((string)$viRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9E9E9E; font-size:8pt">{{ $vIdx+1 }}</td>
                    <td>{{ $vi['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $viCls }}">{!! $viRes !!}</span></td>
                    <td style="color:#546E7A; font-size:8pt">{{ $vi['remarks'] ?? '' }}</td>
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
                    $aiRes = $ai['result'] ?? '&mdash;';
                    $aiCls = match(strtolower((string)$aiRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9E9E9E; font-size:8pt">{{ $aIdx+1 }}</td>
                    <td>{{ $ai['label'] ?? $ai['parameter'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $aiCls }}">{!! $aiRes !!}</span></td>
                    <td style="color:#546E7A; font-size:8pt">{{ $ai['remarks'] ?? '' }}</td>
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
                    $mRes = $m['result'] ?? '&mdash;';
                    $mCls = match(strtolower((string)$mRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9E9E9E; font-size:8pt">{{ $mIdx+1 }}</td>
                    <td>{{ $m['point'] ?? '' }}</td>
                    <td style="text-align:center">{{ $m['spec'] ?? '&mdash;' }}</td>
                    <td style="text-align:center; color:#9E9E9E">{{ $m['tolerance'] ?? '&mdash;' }}</td>
                    <td style="text-align:center; font-weight:bold">{{ $m['measured'] ?? '&mdash;' }}</td>
                    <td style="text-align:center"><span class="{{ $mCls }}">{!! $mRes !!}</span></td>
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
                    $fiRes = $fi['result'] ?? '&mdash;';
                    $fiCls = match(strtolower((string)$fiRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#9E9E9E; font-size:8pt">{{ $fIdx+1 }}</td>
                    <td>{{ $fi['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $fiCls }}">{!! $fiRes !!}</span></td>
                    <td style="color:#546E7A; font-size:8pt">{{ $fi['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @if($images->isEmpty() && $fbData->isEmpty() && empty($fbItems))
        <div class="empty-state">No data recorded for this section.</div>
        @endif
        @endif

        {{-- ── General section-level images (non-task-keyed) ────────────── --}}
        @php $generalImages = $images->filter(fn($a) => empty($a->task_key))->values(); @endphp
        @if($generalImages->isNotEmpty() && $secType !== 'images')
        <div class="sub-heading">Attached Photos</div>
        @foreach($generalImages->take(6)->chunk(3) as $imgChunk)
        <table class="img-gallery-table" style="margin-bottom:4px">
            <tr>
                @foreach($imgChunk as $img)
                @php $b64 = $imgBase64($img->file_path); @endphp
                <td style="width:33.33%">
                    @if($b64)
                        <img src="{{ $b64 }}" class="img-thumb">
                        <div class="img-label">{{ $img->title ?: $img->file_name }}</div>
                    @endif
                </td>
                @endforeach
                @for($p = $imgChunk->count(); $p < 3; $p++)<td style="width:33.33%"></td>@endfor
            </tr>
        </table>
        @endforeach
        @if($generalImages->count() > 6)
        <div style="font-size:7.5pt; color:#9E9E9E; margin-top:3px">
            + {{ $generalImages->count() - 6 }} more photo(s) not shown
        </div>
        @endif
        @endif

        {{-- ── Documents ────────────────────────────────────────────────── --}}
        @if($docs->isNotEmpty())
        <div class="sub-heading">Attached Documents</div>
        <table class="meta-table" style="margin-top:4px">
            @foreach($docs as $doc)
            <tr>
                <td class="mk">{{ $doc->title ?: $doc->file_name }}</td>
                <td class="mv" style="color:#9E9E9E; font-size:8pt">{{ $doc->file_name }} ({{ $doc->humanFileSize() }})</td>
            </tr>
            @endforeach
        </table>
        @endif

        {{-- ── Section notes ────────────────────────────────────────────── --}}
        @if($rs->notes)
        <div class="section-note" style="margin-top:8px">
            <strong>Notes:</strong> {{ $rs->notes }}
        </div>
        @endif

    </div>{{-- /.section-body --}}
</div>{{-- /.section-block --}}

@endforeach {{-- sections --}}

@endforeach {{-- runs --}}

{{-- ══════════════════════════════════════════ FINAL DECISION CARD ════════ --}}
@php
    $finalVerdictClass = match($inspection->overall_status) {
        'Pass'             => 'vb-pass',
        'Fail'             => 'vb-fail',
        'Conditional Pass' => 'vb-conditional',
        default            => 'vb-pending',
    };
    $finalVerdictIcon = match($inspection->overall_status) {
        'Pass'             => '✓  PASS',
        'Fail'             => '✗  FAIL',
        'Conditional Pass' => '~  CONDITIONAL PASS',
        default            => 'PENDING',
    };
@endphp

<div class="final-card no-break">
    <div class="final-card-hdr">
        <div class="final-card-hdr-title">Final Inspection Decision</div>
        <div class="final-card-hdr-sub">Official verdict based on all inspection runs and findings</div>
    </div>
    <div class="final-card-body">

        <div class="final-verdict-badge {{ $finalVerdictClass }}">
            {{ $finalVerdictIcon }}
        </div>

        @if($inspection->remarks)
        <div class="final-remarks-box">
            <div class="final-remarks-label">Inspector Remarks</div>
            <div class="final-remarks-text">{{ $inspection->remarks }}</div>
        </div>
        @endif

        <table class="final-meta-table">
            <tr>
                <td class="fmk">Report Number</td>
                <td class="fmv">{{ $inspection->report_number }}</td>
                <td class="fmk">Inspection Date</td>
                <td class="fmv">{{ $inspection->inspection_date?->format('d F Y') ?? '&mdash;' }}</td>
            </tr>
            <tr>
                <td class="fmk">Inspector(s)</td>
                <td class="fmv">{{ $inspection->inspectors->pluck('employee_name')->implode(', ') ?: '&mdash;' }}</td>
                <td class="fmk">Total Runs</td>
                <td class="fmv">{{ $runs->count() }}</td>
            </tr>
            <tr>
                <td class="fmk">Defects Found</td>
                <td class="fmv">
                    @if($defCritical + $defMajor + $defMinor > 0)
                        <span style="color:#C62828">{{ $defCritical }} Critical</span>
                        &nbsp;&bull;&nbsp;
                        <span style="color:#E65100">{{ $defMajor }} Major</span>
                        &nbsp;&bull;&nbsp;
                        <span style="color:#1565C0">{{ $defMinor }} Minor</span>
                    @else
                        <span style="color:#2E7D32">None recorded</span>
                    @endif
                </td>
                <td class="fmk">Report Generated</td>
                <td class="fmv">{{ now()->format('d F Y, H:i') }}</td>
            </tr>
        </table>

        <table class="sig-row">
            <tr>
                <td>
                    <div class="sig-line">Lead Inspector Signature</div>
                </td>
                <td>
                    <div class="sig-line">QC Manager Approval</div>
                </td>
                <td>
                    <div class="sig-line">Client / Buyer Representative</div>
                </td>
            </tr>
        </table>

    </div>
</div>

</body>
</html>
