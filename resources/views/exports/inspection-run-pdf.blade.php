<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Inspection Report &mdash; {{ $inspection->report_number }}</title>
{{-- "Page X of Y" is drawn by App\Services\Pdf\PdfPageNumberService (dompdf isPhpEnabled is off). --}}
<meta name="pdf-page-numbers" content="1">
<style>
@include('exports.partials._brand-pdf-styles')

/* Reset inside the body only: a bare * would also reset the page box and drop the @page margins. */
body, body * { box-sizing: border-box; margin: 0; padding: 0; }

/* Palette (brand tokens): primary #00699A, primary-dark #004E73, subtle #E1F4FC, wash #F1F8FC, tint border #B3E0F5.
   Fills are light tints with dark text (DESIGN.md §5: print and grayscale safe). */

/* ── Type & utilities ─────────────────────────────────────────────── */
.muted { color: #5B6470; }
.small { font-size: 8pt; color: #5B6470; }
.b { font-weight: bold; }
.c { text-align: center; }
.kicker { font-size: 7.5pt; font-weight: bold; letter-spacing: 0.6px; text-transform: uppercase; color: #00699A; }
.h2 { margin: 5mm 0 2mm; page-break-after: avoid; }
.h2-t { font-size: 10.5pt; font-weight: bold; color: #004E73; text-transform: uppercase; letter-spacing: 0.5px; }
.h2-s { font-size: 8pt; color: #5B6470; }
.h2-rule { border-bottom: 1.5pt solid #00699A; width: 14mm; margin-top: 1mm; }
.sub-h { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; color: #004E73; margin: 2.5mm 0 1.2mm; page-break-after: avoid; }
.note { margin-top: 1.5mm; padding: 1.8mm 2.5mm; background: #F1F8FC; border-left: 2pt solid #00699A; font-size: 9pt; color: #2B3137; }
.fineprint { margin-top: 1.2mm; font-size: 7pt; color: #5B6470; line-height: 1.4; }
.empty { padding: 1.5mm 0; color: #5B6470; font-size: 9pt; font-style: italic; }
.not-checked { margin-top: 1.2mm; font-size: 8pt; color: #5B6470; }
.not-checked strong { color: #2B3137; }

/* Tones: tint + dark text + border */
.tone-pass         { background: #E3F4EA; color: #17643A; border: 1.25pt solid #1D7A46; }
.tone-fail         { background: #FDE8E7; color: #A32019; border: 1.25pt solid #B42318; }
.tone-conditional  { background: #FFF1D6; color: #7A4A00; border: 1.25pt solid #9A5B00; }
.tone-reinspection { background: #EFE7FA; color: #5B2E91; border: 1.25pt solid #6B3FA0; }
.tone-pending      { background: #FFFFFF; color: #4A525C; border: 1.25pt solid #8A939E; }
.chip-info { background: #E1F4FC; color: #004E73; border-color: #B3E0F5; }

/* ── Cover band ───────────────────────────────────────────────────── */
.band { width: 100%; border-collapse: collapse; table-layout: auto; background: #E1F4FC; border-left: 4pt solid #00699A; }
.band td { vertical-align: middle; padding: 5mm 5mm; }
.band .band-dec { width: 62mm; padding-left: 0; }
.title { font-size: 18pt; font-weight: bold; color: #004E73; line-height: 1.15; margin-top: 1mm; }
.title-sub { font-size: 9.5pt; color: #2B3137; margin-top: 1.2mm; }
.prelim-chip { display: inline-block; margin-top: 2.5mm; padding: 1pt 6pt; border: 0.75pt solid #E2B866; background: #FFF1D6; color: #7A4A00; font-size: 7.5pt; font-weight: bold; border-radius: 7pt; }
.decision { padding: 3.5mm 3mm; text-align: center; border-radius: 3px; }
.decision-l { display: block; font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.6px; }
.decision-v { display: block; font-size: 20pt; font-weight: bold; margin-top: 0.8mm; line-height: 1.1; }
.decision-m { display: block; font-size: 8pt; margin-top: 1.2mm; line-height: 1.35; }

.facts-wrap { width: 100%; border-collapse: collapse; table-layout: auto; margin-top: 3mm; }
.facts-wrap > tbody > tr > td, .facts-wrap > tr > td { vertical-align: top; }
.facts { width: 100%; border-collapse: separate; border-spacing: 1.2mm; table-layout: fixed; margin: -1.2mm; }
.facts td { background: #F1F8FC; padding: 1.8mm 2.5mm; vertical-align: top; }
.fk { display: block; font-size: 6.5pt; font-weight: bold; color: #00699A; text-transform: uppercase; letter-spacing: 0.4px; }
.fv { display: block; font-size: 9pt; font-weight: bold; color: #171717; margin-top: 0.4mm; }
.hero-cell { width: 58mm; padding-left: 3mm; }
.hero { background: #F1F8FC; padding: 2mm; text-align: center; height: 50mm; }
.hero-cap { font-size: 7pt; color: #5B6470; margin-top: 0.8mm; text-align: center; }
.assess { margin-top: 2mm; font-size: 8pt; color: #2B3137; border-left: 2pt solid #8A939E; padding: 0.8mm 0 0.8mm 2.5mm; }
.assess strong { color: #171717; }

.kpis { width: 100%; border-collapse: separate; border-spacing: 1.5mm 0; table-layout: fixed; margin: 3.5mm -1.5mm 0; }
.kpis td { background: #F1F8FC; border-top: 2.5pt solid #00699A; padding: 2.2mm 3mm; vertical-align: top; }
.kpi-l { display: block; font-size: 6.5pt; font-weight: bold; color: #00699A; text-transform: uppercase; letter-spacing: 0.4px; }
.kpi-v { display: block; font-size: 14pt; font-weight: bold; color: #171717; margin-top: 0.4mm; }
.kpi-s { display: block; font-size: 7pt; color: #5B6470; }
.kpi-crit { color: #A32019; } .kpi-maj { color: #8F3B00; } .kpi-min { color: #1849A9; }

/* ── Tables ───────────────────────────────────────────────────────── */
.tbl { width: 100%; border-collapse: collapse; table-layout: fixed; }
.tbl thead { display: table-header-group; }
.tbl tr { page-break-inside: avoid; }
.tbl th {
    background: #E1F4FC; color: #004E73; padding: 1.5mm 2mm; font-size: 7pt; font-weight: bold;
    text-align: left; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 1pt solid #00699A;
}
.tbl th.c { text-align: center; }
.tbl td { padding: 1.4mm 2mm; font-size: 9pt; border-bottom: 0.5pt solid #E3E7EB; vertical-align: top; word-wrap: break-word; }
.tbl tbody tr:nth-child(even) td { background: #F8FAFC; }
.tbl tbody tr.row-fail td { background: #FFF4F3; }
.sum td.sum-area { font-weight: bold; color: #171717; }
.sum td.sum-detail { color: #5B6470; font-size: 8.5pt; }

.kv { width: 100%; border-collapse: collapse; table-layout: fixed; }
.kv tr { page-break-inside: avoid; }
.kv td { padding: 1.3mm 2mm 1.3mm 0; font-size: 9pt; border-bottom: 0.5pt solid #EEF1F4; vertical-align: top; word-wrap: break-word; }
.kv .kv-k { width: 20%; color: #5B6470; font-size: 8pt; }
.kv .kv-v { width: 30%; color: #171717; font-weight: bold; }

.two { width: 100%; border-collapse: collapse; table-layout: fixed; }
.two > tbody > tr > td, .two > tr > td { vertical-align: top; }

/* ── Article ──────────────────────────────────────────────────────── */
.art { width: 100%; border-collapse: collapse; table-layout: auto; background: #E1F4FC; border-left: 4pt solid #00699A; margin: 7mm 0 3mm; page-break-inside: avoid; page-break-after: avoid; }
.art td { vertical-align: middle; padding: 3mm 3.5mm; }
.art .art-photo { width: 27mm; padding-right: 0; text-align: center; }
.art .art-res { width: 38mm; padding-left: 0; }
.art-title { font-size: 12.5pt; font-weight: bold; color: #004E73; margin-top: 0.4mm; line-height: 1.2; }
.art-meta { font-size: 8.5pt; color: #2B3137; margin-top: 1mm; }
.art-stats { font-size: 8pt; color: #2B3137; margin-top: 0.6mm; }
.art-dec { padding: 2.2mm 1mm; border-radius: 3px; text-align: center; }
.art-dec .decision-v { font-size: 12.5pt; }
.group-h { margin-top: 6mm; padding-bottom: 1mm; border-bottom: 0.75pt solid #B3E0F5; page-break-after: avoid; }
.group-h-t { font-size: 8pt; font-weight: bold; color: #004E73; text-transform: uppercase; letter-spacing: 0.4px; }
.group-h-v { font-size: 8pt; color: #2B3137; }
.group-h + .art { margin-top: 2.5mm; }

.sec { margin-bottom: 3.5mm; }
.sec-h { width: 100%; border-collapse: collapse; table-layout: auto; background: #F1F8FC; border-left: 2.5pt solid #00699A; margin-bottom: 1.8mm; page-break-after: avoid; page-break-inside: avoid; }
.sec-h td { padding: 1.6mm 2.5mm; vertical-align: middle; }
.sec-no { font-size: 9pt; font-weight: bold; color: #00699A; }
.sec-name { font-size: 10pt; font-weight: bold; color: #171717; }
.sec-detail { font-size: 8pt; color: #5B6470; text-align: right; width: 62mm; }
.sec-chip { width: 28mm; text-align: right; }

.skipped { margin-top: 1mm; padding: 1.8mm 2.5mm; border: 0.75pt dashed #B3E0F5; font-size: 8pt; color: #5B6470; page-break-inside: avoid; }
.skipped strong { color: #004E73; }

/* AQL */
.aql-head { width: 100%; border-collapse: separate; border-spacing: 1.5mm 0; table-layout: fixed; margin: 0 -1.5mm 2mm; }
.aql-head td { background: #F8FAFC; border: 0.75pt solid #E3E7EB; padding: 1.8mm 2.5mm; vertical-align: top; }
.aql-head td.aql-res { text-align: center; }
.fig-l { display: block; font-size: 6.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; }
.aql-fig .fig-l { color: #00699A; }
.fig-v { display: block; font-size: 12.5pt; font-weight: bold; margin-top: 0.4mm; }
.aql-fig .fig-v { color: #171717; }
.fig-s { display: block; font-size: 7pt; color: #5B6470; }
.aql-table td { text-align: center; }
.aql-table td.aql-cat { text-align: left; font-weight: bold; }

/* Defects */
.sev-strip { width: 100%; border-collapse: separate; border-spacing: 1.5mm 0; table-layout: fixed; margin: 0 -1.5mm 2mm; }
.sev-strip td { padding: 0; }
.sev-box { padding: 1.6mm; text-align: center; border: 0.75pt solid #D5DAE0; }
.sev-n { display: block; font-size: 13pt; font-weight: bold; }
.sev-l { display: block; font-size: 6.5pt; text-transform: uppercase; letter-spacing: 0.5px; color: #2B3137; }
.sev-critical { background: #FDE8E7; border-color: #EE9C95; } .sev-critical .sev-n { color: #A32019; }
.sev-major    { background: #FFEBDC; border-color: #F0B17F; } .sev-major .sev-n { color: #8F3B00; }
.sev-minor    { background: #E5EEFC; border-color: #9DB8EE; } .sev-minor .sev-n { color: #1849A9; }
.sev-functional { background: #ECEFF2; border-color: #C3CAD2; } .sev-functional .sev-n { color: #4A525C; }
.defect-thumb { height: 12mm; width: auto; max-width: 18mm; border: 0.75pt solid #D5DAE0; margin: 0 1mm 1mm 0; }

/* Photos: fixed 3 columns, fitted without distortion */
.ph-group { font-size: 8pt; font-weight: bold; color: #2B3137; margin: 1.5mm 0 1mm; page-break-after: avoid; }
.ph-grid { width: 100%; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid; margin-bottom: 1.2mm; }
.ph-grid td { width: 33.33%; padding: 0 1mm; vertical-align: top; text-align: center; }
.ph-frame { height: 38mm; background: #F1F8FC; text-align: center; overflow: hidden; }
.ph-missing { padding-top: 16mm; font-size: 8pt; color: #5B6470; }
.ph-cap { font-size: 7pt; color: #2B3137; margin-top: 0.6mm; text-align: left; }

/* ── Final decision & sign-off ────────────────────────────────────── */
.final { margin-top: 8mm; page-break-inside: avoid; }
.final-band { width: 100%; border-collapse: collapse; table-layout: auto; background: #E1F4FC; border-left: 4pt solid #00699A; }
.final-band td { vertical-align: middle; padding: 4mm; }
.final-band .fb-dec { width: 58mm; padding-left: 0; }
.final-t { font-size: 13pt; font-weight: bold; color: #004E73; }
.final-s { font-size: 8.5pt; color: #2B3137; margin-top: 1mm; line-height: 1.4; }
.remarks { border-left: 2pt solid #00699A; background: #F8FAFC; padding: 1.5mm 3mm; margin-top: 2.5mm; page-break-inside: avoid; }
.remarks-l { font-size: 7pt; font-weight: bold; text-transform: uppercase; color: #00699A; letter-spacing: 0.3px; margin-bottom: 0.5mm; }
.remarks-t { font-size: 9pt; color: #171717; line-height: 1.45; }
.sig { width: 100%; border-collapse: separate; border-spacing: 5mm 0; table-layout: fixed; margin: 10mm -5mm 0; page-break-inside: avoid; }
.sig td { vertical-align: bottom; }
.sig-name { font-size: 9pt; font-weight: bold; color: #171717; height: 5mm; }
.sig-line { border-top: 0.75pt solid #8A939E; padding-top: 1mm; font-size: 7.5pt; color: #5B6470; }
.sig-date { font-size: 7.5pt; color: #5B6470; margin-top: 2.5mm; }
.disclaimer { margin-top: 5mm; padding-top: 2mm; border-top: 0.75pt solid #E3E7EB; font-size: 7pt; color: #5B6470; line-height: 1.45; page-break-inside: avoid; }
.disclaimer strong { color: #2B3137; }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════ DATA ══════════════════════════ --}}
@php
use App\Services\Inspection\InspectionReportService;
use App\Services\Inspection\InspectionVerdictService;
use Illuminate\Support\Str;

$report = new InspectionReportService($defects);

// Runs grouped by parent style/sample (the single-run export doesn't pass this in).
$runGroups = $runGroups ?? $runs->groupBy('sample_id');
$runs = $runs->values();

// The inspector's assessment (Overall Article Result) next to the run's decision (Q15 = a,
// docs/QC_RULES.md §3). The decision is the run verdict; the assessment never replaces it.
$assessmentOf = function ($r) {
    $ars = $r->runSections->first(fn ($x) => $x->section?->slug === 'overall_article_result');
    $choice = $ars?->data['overall_result'] ?? null;
    $asVerdict = InspectionVerdictService::fromAssessment($choice);
    if ($asVerdict === null) {
        return null;
    }
    $decision = $r->completed_at ? $r->verdict : null;

    return [
        'choice'   => $choice,
        'decision' => $decision,
        'differs'  => InspectionVerdictService::contradicts($asVerdict, $decision),
        'reason'   => trim((string) ($ars->data['assessment_reason'] ?? '')),
    ];
};

// Fit an embedded image into a box (mm) without distorting it, centred vertically.
$fitImg = function (?string $b64, float $boxW, float $boxH): string {
    $size = $b64 ? @getimagesizefromstring(base64_decode(substr($b64, strpos($b64, ',') + 1))) : false;
    if (! $size || ! $size[0] || ! $size[1]) {
        return 'max-width:'.$boxW.'mm; height:'.$boxH.'mm;';
    }
    $scale = min($boxW / $size[0], $boxH / $size[1]);
    $w = round($size[0] * $scale, 1);
    $h = round($size[1] * $scale, 1);

    return 'width:'.$w.'mm; height:'.$h.'mm; margin-top:'.round(($boxH - $h) / 2, 1).'mm;';
};

// One display map for every status (docs/QC_RULES.md §4); Conditional / Re-Inspection never show as PASS.
$overall        = $inspection->overall_status;
$overallDisplay = InspectionVerdictService::display($overall);
$overallTone    = $report->tone($overall);

$finishedRuns = $runs->filter(fn ($r) => $r->completed_at)->count();
$isFinal      = $finishedRuns === $runs->count();

// Cover facts
$coverClient = InspectionReportService::join($runs->map(fn ($r) => $r->sample?->customer?->display_name));
if ($coverClient === '—') { $coverClient = $report->generalInfo($runs, 'buyer_name'); }
$coverLocation = $report->generalInfo($runs, 'inspection_location');
$coverOrders = InspectionReportService::join($inspection->customerOrders->pluck('order_number')
    ->merge($runs->flatMap(fn ($r) => $r->runSections->filter(fn ($x) => $x->section?->section_type === 'general_info')->map(fn ($x) => $x->data['po_number'] ?? null))));
$coverStyles = $runGroups->count();
$facts = array_filter([
    'Client'             => $coverClient,
    'Supplier / Factory' => $report->generalInfo($runs, 'factory_name'),
    'Order reference'    => $coverOrders,
    'Inspection type'    => $inspection->inspectionType?->name ?? '—',
    'Inspection date'    => $inspection->inspection_date?->format('d F Y') ?? '—',
    'Location'           => $coverLocation !== '—' ? $coverLocation : null,
    'Inspector(s)'       => $inspection->inspectors->pluck('employee_name')->filter()->implode(', ') ?: $report->generalInfo($runs, 'inspector_name'),
    'Scope'              => $coverStyles.' '.Str::plural('style', $coverStyles).', '.$runs->count().' '.Str::plural('article', $runs->count()),
], fn ($v) => $v !== null);

// Cover photo: the first run with a cover or product photo
$heroImg = null; $heroRun = null;
foreach ($runs as $_r) { if ($heroImg = $report->coverImage($_r)) { $heroRun = $_r; break; } }
$heroB64 = $heroImg ? $imgBase64($heroImg->file_path) : null;
$factCols = $heroB64 ? 2 : 4;

// KPIs
$allCounts   = $report->defectCountsForRuns($runs);
$unitsSample = $runs->sum(fn ($r) => (int) ($r->aql?->sample_size ?? 0));
$unitsLot    = $runs->sum(fn ($r) => (int) ($r->aql?->lot_size ?? 0));
$aqlRuns     = $runs->filter(fn ($r) => $r->aql);
$aqlVerdicts = $aqlRuns->map(fn ($r) => $r->aql->verdict ?? 'Pending')->countBy();
$aqlText = $aqlVerdicts->isEmpty()
    ? 'Not used'
    : ($aqlVerdicts->count() === 1 ? strtoupper($aqlVerdicts->keys()->first()) : $aqlVerdicts->map(fn ($n, $v) => strtoupper($v).' '.$n)->implode(' · '));

$summaryAll     = collect($report->summaryRows($runs));
$summaryRows    = $summaryAll->where('recorded', true)->values();
$summaryMissing = $summaryAll->where('recorded', false)->pluck('name');
$findings       = $report->findings($runs);
$multi          = $runs->count() > 1;
@endphp

{{-- ══════════════════════════════════════════ RUNNING HEADER + FOOTER ═══ --}}
@php $companySetting = \App\Models\CompanySetting::current(); /* read once, shared by both partials */ @endphp
@include('exports.partials._brand-header', ['docTitle' => $isFinal ? 'Quality Inspection Report' : 'Preliminary Inspection Report', 'docNumber' => $inspection->report_number])
@include('exports.partials._brand-footer', ['footerNote' => 'Confidential — prepared for the client named in this report'])

{{-- ══════════════════════════════════════════ 1. EXECUTIVE SUMMARY ═══════ --}}
<table class="band">
    <tr>
        <td>
            <div class="kicker">{{ $inspection->inspectionType?->name ?? 'Quality Inspection' }}</div>
            <div class="title">Quality Inspection Report</div>
            <div class="title-sub">{{ $inspection->report_number }} &nbsp;·&nbsp; {{ $inspection->inspection_date?->format('d F Y') ?? 'Date not set' }}</div>
            @if(!$isFinal)
            <span class="prelim-chip">PRELIMINARY &nbsp;·&nbsp; {{ $finishedRuns }} of {{ $runs->count() }} {{ Str::plural('article', $runs->count()) }} finished</span>
            @endif
        </td>
        <td class="band-dec">
            <div class="decision tone-{{ $overallTone }}">
                <span class="decision-l">Overall result</span>
                <span class="decision-v">{{ $overallDisplay['label'] }}</span>
                <span class="decision-m">{{ $report->meaning($overall) }}</span>
            </div>
        </td>
    </tr>
</table>

<table class="facts-wrap">
    <tr>
        <td>
            <table class="facts">
                @foreach(array_chunk($facts, $factCols, true) as $factRow)
                <tr>
                    @foreach($factRow as $fLabel => $fValue)
                    <td><span class="fk">{{ $fLabel }}</span><span class="fv">{{ $fValue }}</span></td>
                    @endforeach
                    @for($p = count($factRow); $p < $factCols; $p++)<td style="background:none"></td>@endfor
                </tr>
                @endforeach
            </table>
        </td>
        @if($heroB64)
        <td class="hero-cell">
            <div class="hero"><img src="{{ $heroB64 }}" alt="" style="{{ $fitImg($heroB64, 52, 46) }}"></div>
            <div class="hero-cap">{{ $report->articleLabel($heroRun) }}</div>
        </td>
        @endif
    </tr>
</table>

@foreach($runs as $asRun)
    @php $asm = $assessmentOf($asRun); @endphp
    @if($asm)
    <div class="assess">
        <strong>Inspector's assessment &mdash; Run #{{ $asRun->run_number }}:</strong>
        {{ $asm['choice'] }}
        @if($asm['decision'] === null)
            (run not finished; the decision is calculated when it is finished)
        @elseif($asm['differs'])
            &mdash; differs from the decision ({{ InspectionVerdictService::display($asm['decision'])['label'] }}).
            Reason: {{ $asm['reason'] !== '' ? $asm['reason'] : 'no reason recorded' }}
        @else
            &mdash; agrees with the decision.
        @endif
    </div>
    @endif
@endforeach

<table class="kpis">
    <tr>
        <td>
            <span class="kpi-l">Articles</span>
            <span class="kpi-v">{{ $runs->count() }}</span>
            <span class="kpi-s">{{ $finishedRuns }} finished</span>
        </td>
        <td>
            <span class="kpi-l">Units sampled</span>
            <span class="kpi-v">{{ $unitsSample > 0 ? number_format($unitsSample) : '—' }}</span>
            <span class="kpi-s">{{ $unitsLot > 0 ? 'of '.number_format($unitsLot).' in the lot' : 'No sampling plan yet' }}</span>
        </td>
        <td>
            <span class="kpi-l">Defects found</span>
            <span class="kpi-v"><span class="kpi-crit">{{ $allCounts['critical'] }}</span> / <span class="kpi-maj">{{ $allCounts['major'] }}</span> / <span class="kpi-min">{{ $allCounts['minor'] }}</span></span>
            <span class="kpi-s">critical / major / minor</span>
        </td>
        <td>
            <span class="kpi-l">AQL result</span>
            <span class="kpi-v">{{ $aqlText }}</span>
            <span class="kpi-s">{{ $aqlRuns->isEmpty() ? 'No AQL sampling' : 'ISO 2859-1, normal' }}</span>
        </td>
    </tr>
</table>

{{-- Result summary --}}
@if($summaryAll->isNotEmpty())
<div class="h2"><span class="h2-t">Result summary</span> <span class="h2-s">&nbsp;by inspection area{{ $multi ? ', across all articles' : '' }}</span><div class="h2-rule"></div></div>
<table class="tbl sum">
    <thead>
        <tr>
            <th style="width:6%" class="c">#</th>
            <th style="width:38%">Inspection area</th>
            <th style="width:38%">Detail</th>
            <th style="width:18%" class="c">Result</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summaryRows as $sRow)
        <tr class="{{ $sRow['tone'] === 'fail' ? 'row-fail' : '' }}">
            <td class="c muted">{{ $loop->iteration }}</td>
            <td class="sum-area">{{ $sRow['name'] }}</td>
            <td class="sum-detail">{{ $sRow['detail'] }}</td>
            <td class="c"><span class="chip chip-{{ $sRow['tone'] }}">{{ $sRow['label'] }}</span></td>
        </tr>
        @endforeach
        @if($summaryRows->isEmpty())
        <tr><td colspan="4" class="muted">No inspection results have been recorded yet.</td></tr>
        @endif
    </tbody>
</table>
@if($summaryMissing->isNotEmpty())
<div class="not-checked"><strong>Not yet recorded:</strong> {{ $summaryMissing->implode(', ') }}.</div>
@endif
@endif

{{-- Articles (only when there is more than one; a single article is described by its own header) --}}
@if($multi)
<div class="h2"><span class="h2-t">Articles inspected</span><div class="h2-rule"></div></div>
<table class="tbl">
    <thead>
        <tr>
            <th style="width:6%" class="c">#</th>
            <th style="width:38%">Style / article</th>
            <th style="width:14%">Colour</th>
            <th style="width:11%" class="c">Sampled</th>
            <th style="width:13%" class="c">Defects C/Ma/Mi</th>
            <th style="width:18%" class="c">Result</th>
        </tr>
    </thead>
    <tbody>
        @foreach($runs as $i => $r)
        @php $rc = $report->defectCountsForRuns([$r]); @endphp
        <tr class="{{ $r->completed_at && $r->verdict === 'Fail' ? 'row-fail' : '' }}">
            <td class="c muted">{{ $i + 1 }}</td>
            <td><span class="b">{{ $r->sample?->sample_code ?? 'Run #'.$r->run_number }}</span>@if($r->sample?->product_name) <span class="small">&nbsp;{{ $r->sample->product_name }}</span>@endif</td>
            <td>{{ $r->sampleColor?->name ?? '—' }}</td>
            <td class="c">{{ $r->aql?->sample_size ? number_format($r->aql->sample_size) : '—' }}</td>
            <td class="c">{{ $rc['critical'] }} / {{ $rc['major'] }} / {{ $rc['minor'] }}</td>
            <td class="c"><span class="chip chip-{{ $r->completed_at ? $report->tone($r->verdict) : 'pending' }}">{{ $r->completed_at ? InspectionVerdictService::display($r->verdict)['label'] : 'IN PROGRESS' }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Key findings --}}
<div class="h2"><span class="h2-t">Key findings</span> <span class="h2-s">&nbsp;defects and failed checkpoints, most serious first</span><div class="h2-rule"></div></div>
@if(empty($findings))
    <div class="note" style="border-left-color:#1D7A46; background:#F2FAF5">
        {{ $isFinal ? 'No defects or failed checkpoints were recorded in this inspection.' : 'No defects or failed checkpoints have been recorded so far.' }}
    </div>
@else
<table class="tbl">
    <thead>
        <tr>
            <th style="width:6%" class="c">#</th>
            @if($multi)<th style="width:22%">Article</th>@endif
            <th style="width:18%">Area</th>
            <th style="width:{{ $multi ? 33 : 55 }}%">Finding</th>
            <th style="width:13%" class="c">Class</th>
            <th style="width:8%" class="c">Qty</th>
        </tr>
    </thead>
    <tbody>
        @foreach($findings as $f)
        <tr>
            <td class="c muted">{{ $loop->iteration }}</td>
            @if($multi)<td class="small">{{ $f['article'] }}</td>@endif
            <td class="small">{{ $f['area'] }}</td>
            <td>{{ $f['issue'] }}</td>
            <td class="c"><span class="chip chip-{{ $f['severity'] }}">{{ $f['severity'] === 'fail' ? 'FAIL' : ucfirst($f['severity']) }}</span></td>
            <td class="c b">{{ $f['qty'] ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ══════════════════════════════════════════ 2. ARTICLE DETAILS (flowing, no forced page breaks) ═ --}}
@foreach($runs as $runIndex => $run)
@php
    $artNo = $runIndex + 1;
    $isFirstOfGroup = $runIndex === 0 || $runs[$runIndex - 1]->sample_id !== $run->sample_id;
    $sections = $report->visibleSections($run);
    $shown = $sections->reject(fn ($rs) => $report->isEmpty($rs, $run))->values();
    $notRecorded = $sections->filter(fn ($rs) => $report->isEmpty($rs, $run))->map(fn ($rs) => $rs->section->name)->values();
    $runCounts = $report->defectCountsForRuns([$run]);
    $artImg = $report->coverImage($run);
    $artB64 = $artImg ? $imgBase64($artImg->file_path) : null;
    $runTone = $run->completed_at ? $report->tone($run->verdict) : 'pending';
    $runLabel = $run->completed_at ? InspectionVerdictService::display($run->verdict)['label'] : 'IN PROGRESS';
@endphp

@if($isFirstOfGroup)
    @php
        $styleGroup  = $runGroups->get($run->sample_id, collect([$run]));
        $groupPassed = $styleGroup->filter(fn ($r) => $r->completed_at && $r->verdict === 'Pass')->count();
        $groupTotal  = $styleGroup->count();
        // Same roll-up as the cover, so the two can never disagree (QC_RULES §4).
        $groupStatus = InspectionVerdictService::rollUp($styleGroup->map(fn ($r) => ['verdict' => $r->verdict, 'finished' => $r->completed_at !== null]));
    @endphp
    @if($groupTotal > 1)
    <div class="group-h">
        <div class="group-h-t">Style {{ $run->sample?->sample_code }}{{ $run->sample?->product_name ? ' — '.$run->sample->product_name : '' }}</div>
        <div class="group-h-v">{{ $groupPassed }} of {{ $groupTotal }} colors passed &bull; {{ InspectionVerdictService::display($groupStatus)['label'] }}</div>
    </div>
    @endif
@endif

<table class="art">
    <tr>
        @if($artB64)
        <td class="art-photo"><img src="{{ $artB64 }}" alt="" style="{{ $fitImg($artB64, 22, 22) }}"></td>
        @endif
        <td>
            <div class="kicker">Article {{ $artNo }} of {{ $runs->count() }} &nbsp;·&nbsp; Run #{{ $run->run_number }}</div>
            <div class="art-title">{{ $run->sample?->sample_code ?? 'Run #'.$run->run_number }}{{ $run->sample?->product_name ? ' — '.$run->sample->product_name : '' }}</div>
            @php
                $meta = array_filter([
                    $run->sampleColor ? 'Colour: '.$run->sampleColor->name : null,
                    $run->sample?->category ? 'Category: '.$run->sample->category->category_name : null,
                    $run->sample?->customer ? 'Client: '.$run->sample->customer->display_name : null,
                ]);
            @endphp
            @if($meta)<div class="art-meta">{{ implode('  ·  ', $meta) }}</div>@endif
            <div class="art-stats">
                {{ $run->aql?->sample_size ? number_format($run->aql->sample_size).' units sampled  ·  ' : '' }}Defects: {{ $report->countsText($runCounts) }}
                &nbsp;·&nbsp; {{ $run->completed_at ? 'Finished '.$run->completed_at->format('d M Y') : 'Not finished' }}
            </div>
        </td>
        <td class="art-res">
            <div class="art-dec tone-{{ $runTone }}">
                <span class="decision-l">Result</span>
                <span class="decision-v">{{ $runLabel }}</span>
            </div>
        </td>
    </tr>
</table>

@if($run->remarks)
<div class="note" style="margin:0 0 3mm"><strong>Remarks:</strong> {{ $run->remarks }}</div>
@endif

@forelse($shown as $secIdx => $rs)
@php
    $secNo  = $artNo.'.'.($secIdx + 1);
    $result = $report->sectionResult($rs, $run);
    $docs   = $rs->attachments->filter(fn ($a) => ! $a->isImage())->values();
@endphp
<div class="sec">
    <table class="sec-h">
        <tr>
            <td><span class="sec-no">{{ $secNo }}</span>&nbsp;&nbsp;<span class="sec-name">{{ $rs->section->name }}</span></td>
            <td class="sec-detail">{{ $result['detail'] }}</td>
            <td class="sec-chip"><span class="chip chip-{{ $result['tone'] }}">{{ $result['label'] }}</span></td>
        </tr>
    </table>

    @include('exports.inspection-report._section-body')

    @include('exports.inspection-report._photos', ['groups' => $report->photoGroups($rs), 'photoPrefix' => $secNo])

    @if($docs->isNotEmpty())
    <div class="sub-h">Attached documents</div>
    <table class="kv">
        @foreach($docs as $doc)
        <tr><td class="kv-k" style="width:40%">{{ $doc->title ?: $doc->file_name }}</td><td class="kv-v small" style="width:60%">{{ $doc->file_name }} ({{ $doc->humanFileSize() }})</td></tr>
        @endforeach
    </table>
    @endif

    @if($rs->notes)
    <div class="note"><strong>Inspector's note:</strong> {{ $rs->notes }}</div>
    @endif
</div>
@empty
<div class="empty">No inspection data has been recorded for this article yet.</div>
@endforelse

@if($notRecorded->isNotEmpty())
<div class="skipped"><strong>Not recorded for this article:</strong> {{ $notRecorded->implode(', ') }}.</div>
@endif

@endforeach

{{-- ══════════════════════════════════════════ 3. FINAL DECISION + SIGN-OFF ═ --}}
<div class="final">
    <table class="final-band">
        <tr>
            <td>
                <div class="kicker">{{ $inspection->report_number }}</div>
                <div class="final-t">Final Inspection Decision</div>
                <div class="final-s">
                    {{ $report->meaning($overall) }}
                    {{ 'Based on '.$runs->count().' '.Str::plural('article', $runs->count()).'; defects found: '.$report->countsText($allCounts)
                        .(empty($findings) ? '' : '; '.count($findings).' '.Str::plural('finding', count($findings)).' listed under Key findings').'.' }}
                </div>
            </td>
            <td class="fb-dec">
                <div class="decision tone-{{ $overallTone }}">
                    <span class="decision-l">Overall result</span>
                    <span class="decision-v">{{ $overallDisplay['label'] }}</span>
                </div>
            </td>
        </tr>
    </table>

    @foreach($runs as $asRun)
        @php $asm = $assessmentOf($asRun); @endphp
        @if($asm)
        <div class="remarks">
            <div class="remarks-l">Inspector's assessment &mdash; Run #{{ $asRun->run_number }}</div>
            <div class="remarks-t">
                {{ $asm['choice'] }}
                @if($asm['decision'] === null)
                    (run not finished; the decision is calculated when it is finished)
                @elseif($asm['differs'])
                    &mdash; differs from the decision ({{ InspectionVerdictService::display($asm['decision'])['label'] }}).
                    Reason: {{ $asm['reason'] !== '' ? $asm['reason'] : 'no reason recorded' }}
                @else
                    &mdash; agrees with the decision.
                @endif
            </div>
        </div>
        @endif
    @endforeach

    @if($inspection->remarks)
    <div class="remarks">
        <div class="remarks-l">Inspector's remarks</div>
        <div class="remarks-t">{{ $inspection->remarks }}</div>
    </div>
    @endif

    <table class="sig">
        <tr>
            <td>
                <div class="sig-name">{{ $inspection->inspectors->pluck('employee_name')->filter()->first() }}</div>
                <div class="sig-line">Inspected by</div>
                <div class="sig-date">Date: {{ $inspection->inspection_date?->format('d M Y') ?? '____________' }}</div>
            </td>
            <td>
                <div class="sig-name"></div>
                <div class="sig-line">Reviewed &amp; approved by (QC Manager)</div>
                <div class="sig-date">Date: ____________</div>
            </td>
            <td>
                <div class="sig-name"></div>
                <div class="sig-line">Acknowledged by (client)</div>
                <div class="sig-date">Date: ____________</div>
            </td>
        </tr>
    </table>

    <div class="disclaimer">
        <strong>About this report.</strong>
        The findings reflect the condition of the goods presented at the time and place of inspection,
        @if($aqlRuns->isNotEmpty())
        checked on a sample basis as stated in each article's AQL section (ISO 2859-1, single sampling, normal inspection).
        @else
        as checked by the inspector on the articles and checkpoints listed in this report.
        @endif
        The result applies only to the goods inspected and does not release the supplier from its contractual obligations.
        This report is confidential and intended for the client named above; it may not be reproduced except in full.
    </div>
</div>

</body>
</html>
