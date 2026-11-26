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

/* Reset inside the body only: a bare * would also reset the page box and drop the @page margins (the old header/footer never printed). */
body, body * { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Cover ─────────────────────────────────────────────────────────── */
.cover-title-block { margin: 1mm 0 5mm; }
.cover-kicker {
    font-size: 7.5pt; font-weight: bold; letter-spacing: 0.6px; text-transform: uppercase; color: #00699A;
}
.cover-main-title { font-size: 19pt; font-weight: bold; color: #171717; margin-top: 1mm; }
.cover-main-subtitle { font-size: 9.5pt; color: #5B6470; margin-top: 1mm; }
.cover-accent { width: 22mm; border-bottom: 2pt solid #01AEEF; margin-top: 3mm; }

.cover-grid { width: 100%; border-collapse: collapse; }
.cover-grid td.cg-left  { width: 58%; padding-right: 4mm; vertical-align: top; }
.cover-grid td.cg-right { width: 42%; vertical-align: top; }

.info-panel { border: 0.75pt solid #D5DAE0; border-top: 2pt solid #00699A; margin-bottom: 4mm; }
.info-panel-header {
    background: #F4F6F8; padding: 5px 10px; border-bottom: 0.75pt solid #D5DAE0;
    font-size: 7.5pt; font-weight: bold; color: #2B3137; text-transform: uppercase; letter-spacing: 0.4px;
}
.info-panel-body { padding: 8px 10px; }

.cover-kv { width: 100%; border-collapse: collapse; }
.cover-kv td { padding: 3.5px 0; font-size: 9pt; border-bottom: 0.5pt solid #EEF1F4; vertical-align: top; }
.cover-kv .ck { color: #5B6470; width: 40%; font-size: 8.5pt; }
.cover-kv .cv { color: #171717; font-weight: bold; }
.cover-kv tr:last-child td { border-bottom: none; }

/* The one decision (text + colour; tint + border so it survives grayscale) */
.decision-label { font-size: 7.5pt; font-weight: bold; color: #5B6470; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.verdict-badge-lg {
    display: block; padding: 10px 0; font-size: 18pt; font-weight: bold; text-align: center; margin-bottom: 8px;
    border-radius: 3px;
}
.vb-pass         { background: #E3F4EA; color: #17643A; border: 1.5pt solid #1D7A46; }
.vb-fail         { background: #FDE8E7; color: #A32019; border: 1.5pt solid #B42318; }
.vb-conditional  { background: #FFF1D6; color: #7A4A00; border: 1.5pt solid #9A5B00; }
.vb-reinspection { background: #EFE7FA; color: #5B2E91; border: 1.5pt solid #6B3FA0; }
.vb-pending      { background: #ECEFF2; color: #4A525C; border: 1.5pt solid #8A939E; }

.assessment-line { font-size: 8.5pt; color: #2B3137; border-left: 2pt solid #8A939E; padding: 3px 0 3px 7px; margin-bottom: 4px; }
.assessment-line strong { color: #171717; }

/* Summary strip under the cover */
.summary-strip { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 5mm; }
.summary-strip td { border: 0.75pt solid #D5DAE0; padding: 6px 8px; vertical-align: top; background: #FFFFFF; }
.ss-label { display: block; font-size: 7pt; font-weight: bold; color: #5B6470; text-transform: uppercase; letter-spacing: 0.4px; }
.ss-value { display: block; font-size: 12pt; font-weight: bold; color: #171717; margin-top: 1px; }
.ss-sub   { display: block; font-size: 8pt; color: #2B3137; margin-top: 1px; }

.defect-pill-row { width: 100%; border-collapse: collapse; }
.defect-pill-row td { text-align: center; padding: 2px; }
.dp-box { padding: 5px 3px; display: block; border: 0.75pt solid #D5DAE0; }
.dp-critical { background: #FDE8E7; border-color: #EE9C95; }
.dp-major    { background: #FFEBDC; border-color: #F0B17F; }
.dp-minor    { background: #E5EEFC; border-color: #9DB8EE; }
.dp-num { font-size: 13pt; font-weight: bold; display: block; }
.dp-crit-num { color: #A32019; }
.dp-maj-num  { color: #8F3B00; }
.dp-min-num  { color: #1849A9; }
.dp-lbl { font-size: 7pt; text-transform: uppercase; color: #2B3137; display: block; margin-top: 1px; }

/* ── Section summary table ─────────────────────────────────────────── */
.summary-dashboard { border: 0.75pt solid #D5DAE0; border-top: 2pt solid #00699A; margin-bottom: 5mm; }
.summary-dashboard-hdr { background: #F4F6F8; padding: 5px 10px; border-bottom: 0.75pt solid #D5DAE0; }
.summary-dashboard-hdr-text { font-size: 7.5pt; font-weight: bold; color: #2B3137; text-transform: uppercase; letter-spacing: 0.4px; }
.summary-table { width: 100%; border-collapse: collapse; }
.summary-table th {
    background: #FFFFFF; padding: 5px 10px; font-size: 7pt; font-weight: bold; text-transform: uppercase;
    color: #5B6470; border-bottom: 0.75pt solid #D5DAE0; text-align: left;
}
.summary-table td { padding: 5px 10px; font-size: 9pt; border-bottom: 0.5pt solid #EEF1F4; vertical-align: middle; }
.summary-table tr { page-break-inside: avoid; }
.summary-table tbody tr:last-child td { border-bottom: none; }
.summary-table .st-idx     { width: 26px; text-align: center; color: #5B6470; font-size: 8pt; }
.summary-table .st-section { font-weight: bold; color: #171717; }
.summary-table .st-detail  { color: #5B6470; font-size: 8.5pt; }
.summary-table .st-status  { text-align: right; width: 90px; }

/* Status pills (summary, section headers) */
.badge { display: inline-block; padding: 1.5pt 6pt; border-radius: 7pt; font-size: 7.5pt; font-weight: bold; border: 0.75pt solid #C3CAD2; }
.badge-pass    { background: #E3F4EA; color: #17643A; border-color: #8CCBA4; }
.badge-fail    { background: #FDE8E7; color: #A32019; border-color: #EE9C95; }
.badge-na      { background: #ECEFF2; color: #4A525C; border-color: #C3CAD2; }
.badge-pending { background: #FFF1D6; color: #7A4A00; border-color: #E2B866; }

/* ── Runs overview ─────────────────────────────────────────────────── */
.runs-box { border: 0.75pt solid #D5DAE0; margin-top: 4mm; }
.runs-box-hdr {
    background: #F4F6F8; padding: 5px 10px; border-bottom: 0.75pt solid #D5DAE0;
    font-size: 7.5pt; font-weight: bold; color: #2B3137; text-transform: uppercase; letter-spacing: 0.4px;
}

.disclaimer {
    margin-top: 5mm; padding: 6px 10px; border: 0.75pt solid #D5DAE0; border-left: 2pt solid #8A939E;
    font-size: 7.5pt; color: #5B6470;
}

/* ── Style group + run header ──────────────────────────────────────── */
.style-group-header { border-bottom: 1.5pt solid #171717; padding-bottom: 4px; margin-bottom: 4mm; page-break-after: avoid; }
.sgh-title  { font-size: 11pt; font-weight: bold; color: #171717; text-transform: uppercase; }
.sgh-rollup { font-size: 8.5pt; color: #5B6470; margin-top: 2px; }

.run-page-header {
    background: #F4F6F8; border: 0.75pt solid #D5DAE0; border-left: 3pt solid #00699A;
    padding: 8px 12px; margin-bottom: 4mm; page-break-after: avoid;
}
.run-page-header table { width: 100%; border-collapse: collapse; }
.rph-label { font-size: 7.5pt; text-transform: uppercase; color: #00699A; font-weight: bold; letter-spacing: 0.4px; }
.rph-title { font-size: 13pt; font-weight: bold; color: #171717; margin-top: 1px; }
.rph-meta  { font-size: 8.5pt; color: #2B3137; margin-top: 3px; }
.rph-verdict-cell  { text-align: right; vertical-align: middle; width: 150px; }
.rph-verdict-label { font-size: 7pt; text-transform: uppercase; color: #5B6470; font-weight: bold; margin-bottom: 4px; }
.rph-verdict-badge { display: inline-block; padding: 5px 14px; font-size: 10.5pt; font-weight: bold; border-radius: 3px; }

/* ── Section blocks ────────────────────────────────────────────────── */
.section-block { margin-bottom: 4mm; border: 0.75pt solid #D5DAE0; border-left: 3pt solid #00699A; }
.section-block-empty { margin-bottom: 2mm; border: 0.75pt solid #E3E7EB; border-left: 3pt solid #C3CAD2; page-break-inside: avoid; }
.section-hdr { background: #F4F6F8; padding: 5px 10px; border-bottom: 0.75pt solid #D5DAE0; page-break-after: avoid; page-break-inside: avoid; }
.section-hdr table { width: 100%; border-collapse: collapse; }
.sec-num {
    background: #00699A; color: #FFFFFF; font-size: 7.5pt; font-weight: bold;
    padding: 1px 6px; border-radius: 2px; margin-right: 5px; display: inline-block;
}
.sec-name { font-size: 10pt; font-weight: bold; color: #171717; }
.section-body { padding: 8px 10px; }

.section-note {
    margin-top: 6px; padding: 5px 8px; border: 0.75pt solid #E2B866; border-left: 2pt solid #9A5B00;
    background: #FFFAEF; font-size: 9pt; color: #2B3137;
}
.sub-heading {
    font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; color: #2B3137;
    margin: 8px 0 4px; padding-bottom: 2px; border-bottom: 0.75pt solid #D5DAE0; page-break-after: avoid;
}

/* ── Label / value rows ────────────────────────────────────────────── */
.meta-table { width: 100%; border-collapse: collapse; }
.meta-table td { padding: 3.5px 6px; font-size: 9pt; border-bottom: 0.5pt solid #EEF1F4; vertical-align: top; }
.meta-table tr { page-break-inside: avoid; }
.meta-table .mk { width: 36%; font-weight: bold; color: #5B6470; }
.meta-table .mv { color: #171717; }
.meta-table tr:last-child td { border-bottom: none; }

/* ── Data tables (header repeats on each page; rows never split) ──── */
.data-table, .aql-table, .defect-table { width: 100%; border-collapse: collapse; }
.data-table thead, .aql-table thead, .defect-table thead { display: table-header-group; }
.data-table tr, .aql-table tr, .defect-table tr { page-break-inside: avoid; }
.data-table th, .aql-table th, .defect-table th {
    background: #EEF1F4; color: #2B3137; padding: 5px 7px; font-size: 7.5pt; font-weight: bold;
    text-align: left; text-transform: uppercase; letter-spacing: 0.2px; border-bottom: 1.25pt solid #00699A;
}
.data-table td, .defect-table td { padding: 4px 7px; font-size: 9pt; border-bottom: 0.5pt solid #E3E7EB; vertical-align: top; }
.data-table tbody tr:nth-child(even) td, .defect-table tbody tr:nth-child(even) td { background: #FAFBFC; }

.rb-pass { color: #17643A; font-weight: bold; background: #E3F4EA; border: 0.75pt solid #8CCBA4; padding: 1px 6px; border-radius: 7pt; font-size: 7.5pt; }
.rb-fail { color: #A32019; font-weight: bold; background: #FDE8E7; border: 0.75pt solid #EE9C95; padding: 1px 6px; border-radius: 7pt; font-size: 7.5pt; }
.rb-na   { color: #4A525C; background: #ECEFF2; border: 0.75pt solid #C3CAD2; padding: 1px 6px; border-radius: 7pt; font-size: 7.5pt; }
.rb-def  { color: #5B6470; font-size: 8.5pt; }

/* ── AQL sampling basis ────────────────────────────────────────────── */
.aql-table th { text-align: center; }
.aql-table td { padding: 4px 7px; font-size: 9pt; border: 0.5pt solid #D5DAE0; text-align: center; }
.aql-cat { font-weight: bold; text-align: left !important; }
.aql-verdict-block { margin-top: 6px; padding: 6px 10px; font-size: 10.5pt; font-weight: bold; text-align: center; }
.avb-pass    { background: #E3F4EA; color: #17643A; border: 1pt solid #1D7A46; }
.avb-fail    { background: #FDE8E7; color: #A32019; border: 1pt solid #B42318; }
.avb-pending { background: #ECEFF2; color: #4A525C; border: 1pt solid #8A939E; }

/* ── Defects ───────────────────────────────────────────────────────── */
.defect-table { table-layout: fixed; }
.defect-table td { word-wrap: break-word; }
.sev-critical, .sev-major, .sev-minor, .sev-functional {
    font-weight: bold; padding: 1px 6px; border-radius: 7pt; font-size: 7.5pt; border: 0.75pt solid #C3CAD2;
}
.sev-critical   { background: #FDE8E7; color: #A32019; border-color: #EE9C95; }
.sev-major      { background: #FFEBDC; color: #8F3B00; border-color: #F0B17F; }
.sev-minor      { background: #E5EEFC; color: #1849A9; border-color: #9DB8EE; }
.sev-functional { background: #ECEFF2; color: #4A525C; }
.defect-thumb { height: 34px; width: auto; max-width: 48px; border: 0.75pt solid #D5DAE0; margin: 0 2px 2px 0; }

/* ── Photo grid: fixed 3 columns, image size constrained ───────────── */
.img-gallery-table { width: 100%; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid; }
.img-gallery-table td { padding: 4px; vertical-align: top; text-align: center; width: 33.33%; }
.img-thumb {
    height: 150px; width: auto; max-width: 100%;
    border: 0.75pt solid #D5DAE0; display: block; margin: 0 auto 3px; background: #FFFFFF;
}
.img-caption { font-size: 7.5pt; color: #2B3137; text-align: center; word-wrap: break-word; }
.img-label { font-size: 7.5pt; color: #5B6470; word-break: break-word; margin-top: 2px; }
.img-label strong { color: #171717; }
.img-group-heading { font-size: 8pt; font-weight: bold; color: #2B3137; margin: 6px 0 3px; page-break-after: avoid; }
.img-missing {
    height: 150px; border: 0.75pt dashed #8A939E; display: block; margin: 0 auto; background: #F4F6F8;
    text-align: center; font-size: 8pt; color: #5B6470; padding-top: 65px;
}

.review-verdict-box { display: inline-block; padding: 2px 10px; border-radius: 7pt; font-size: 9.5pt; font-weight: bold; }
.empty-state { text-align: center; padding: 12px; color: #5B6470; font-size: 9pt; font-style: italic; }

/* ── Final decision card ───────────────────────────────────────────── */
.final-card { border: 1pt solid #00699A; margin-top: 6mm; page-break-inside: avoid; }
.final-card-hdr { background: #F4F6F8; padding: 8px 12px; border-bottom: 0.75pt solid #D5DAE0; }
.final-card-hdr-title { font-size: 10.5pt; font-weight: bold; color: #171717; }
.final-card-hdr-sub { font-size: 8pt; color: #5B6470; margin-top: 1px; }
.final-card-body { padding: 10px 12px; }
.final-verdict-badge { display: block; padding: 10px 0; font-size: 20pt; font-weight: bold; text-align: center; margin-bottom: 8px; border-radius: 3px; }
.final-remarks-box { border: 0.75pt solid #D5DAE0; border-left: 2pt solid #00699A; padding: 6px 10px; margin-bottom: 6px; }
.final-remarks-label { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; color: #5B6470; margin-bottom: 3px; }
.final-remarks-text { font-size: 9pt; color: #171717; line-height: 1.5; }
.final-meta-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
.final-meta-table td { padding: 3.5px 0; font-size: 9pt; border-bottom: 0.5pt solid #EEF1F4; }
.final-meta-table .fmk { color: #5B6470; width: 22%; font-size: 8.5pt; }
.final-meta-table .fmv { color: #171717; font-weight: bold; }
.final-meta-table tr:last-child td { border-bottom: none; }

.sig-row { width: 100%; border-collapse: collapse; margin-top: 12mm; }
.sig-row td { text-align: center; padding: 0 8px; vertical-align: bottom; width: 33%; }
.sig-line { border-top: 0.75pt solid #8A939E; padding-top: 4px; font-size: 8pt; color: #5B6470; }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════ GLOBAL PHP SETUP ════════════ --}}
@php
// Slugs treated as "not a section" — must stay identical to the $hiddenSlugs list in
// resources/views/operations/inspections/runs/edit.blade.php, otherwise stale/orphaned
// InspectionRunSection rows for these slugs leak into the report as phantom "pending" sections.
$hiddenSlugs = [
    'corrective_action', 'inspection_conclusion', 'finish_inspection',
    'textile_sample_conformity', 'denim_textile_defects',
    'cover_photo', 'workmanship_check',
];

// Real section_type values seeded in InspectionSectionSeeder — used to order the report
$sectionTypePriority = [
    'general_info'       => 1,
    'images'             => 2,
    'quantity_sampling'  => 3,
    'cartons'            => 4,
    'cover_photo'        => 5,
    'checklist'          => 6,
    'checkpoint'         => 7,
    'files_review'       => 8,
    'verification'       => 9,
    'article_results'    => 10,
    'aql'                => 11,
    'defects'            => 12,
    'workmanship'        => 13,
    'production_stages'  => 14,
    'container'          => 15,
    'review'             => 16,
    'conclusion'         => 17,
    'finish'             => 18,
];

// The inspector's assessment (Overall Article Result) next to the run's decision (Q15 = a,
// docs/QC_RULES.md §3). The decision is the run verdict; the assessment never replaces it.
$assessmentOf = function ($r) {
    $ars = $r->runSections->first(fn ($x) => $x->section?->slug === 'overall_article_result');
    $choice = $ars?->data['overall_result'] ?? null;
    $asVerdict = \App\Services\Inspection\InspectionVerdictService::fromAssessment($choice);
    if ($asVerdict === null) {
        return null;
    }
    $decision = $r->completed_at ? $r->verdict : null;

    return [
        'choice'   => $choice,
        'decision' => $decision,
        'differs'  => \App\Services\Inspection\InspectionVerdictService::contradicts($asVerdict, $decision),
        'reason'   => trim((string) ($ars->data['assessment_reason'] ?? '')),
    ];
};

// Resolve a defect's display name + severity, since only defect_id is stored on the row
$defectInfo = function ($defectId) use ($defects) {
    $def = $defects->get((int) $defectId);
    return [
        'name'     => $def->defect_name ?? ('Defect #' . $defectId),
        'severity' => $def->severity ?? 'minor',
    ];
};

// Verdict tone for the decision boxes (colour only; the label always comes from display()).
$verdictTone = fn ($status) => match ($status) {
    'Pass' => 'pass', 'Fail' => 'fail', 'Conditional' => 'conditional', 'Re-Inspection' => 'reinspection', default => 'pending',
};

// Cover details, taken from values this report already prints (runs overview, run headers,
// General Information). Several runs with different values list each value once.
$coverJoin = fn ($values) => collect($values)->filter(fn ($v) => $v !== null && $v !== '')->unique()->implode(', ') ?: '—';
$coverCustomer = $coverJoin($runs->map(fn ($r) => $r->sample?->customer?->display_name));
$coverFactory  = $coverJoin($runs->flatMap(fn ($r) => $r->runSections
    ->filter(fn ($x) => $x->section?->section_type === 'general_info')
    ->map(fn ($x) => $x->data['factory_name'] ?? null)));
$coverSample   = $coverJoin($runs->map(fn ($r) => $r->sample ? trim($r->sample->sample_code.($r->sample->product_name ? ' — '.$r->sample->product_name : '')) : null));
$coverColour   = $coverJoin($runs->map(fn ($r) => $r->sampleColor?->name));

// Runs grouped by parent style/sample (single-run exports don't pass this in —
// fall back to grouping the one run we have so the template logic stays uniform).
$runGroups = $runGroups ?? $runs->groupBy('sample_id');

// One display map for every status (docs/QC_RULES.md); Conditional / Re-Inspection never show as PASS.
$overallDisplay    = \App\Services\Inspection\InspectionVerdictService::display($inspection->overall_status);
$coverVerdictClass = $overallDisplay['pdf'];
$headerBadgeClass  = match($inspection->overall_status) {
    'Pass'  => 'badge-pass',
    'Fail'  => 'badge-fail',
    default => 'badge-pending',
};

// Aggregate defect counts across all runs (severity is a snapshot on each
// inspection_run_defects row — falls back to the Defect master for any
// backfilled row that predates severity being captured per-entry)
$defCritical = 0; $defMajor = 0; $defMinor = 0;
foreach ($runs as $_r) {
    foreach ($_r->runSections as $_rs) {
        if ($_rs->section && $_rs->section->section_type === 'defects') {
            foreach ($_rs->defects as $_row) {
                $sev = $_row->severity ?? $defectInfo($_row->defect_id)['severity'];
                if ($sev === 'critical') $defCritical++;
                elseif ($sev === 'major') $defMajor++;
                elseif ($sev === 'minor') $defMinor++;
            }
        }
    }
}

// Detects whether a run-section carries no meaningful data (matched against real section_type values)
$isSectionEmpty = function($rs, $currentRun): bool {
    $data = $rs->data ?? [];
    $type = $rs->section->section_type;
    $slug = $rs->section->slug;
    $imgs = $rs->attachments; // any attachment (image or doc) counts as recorded evidence
    $hasVal = fn($v) => $v !== null && $v !== '';

    return match(true) {
        $type === 'general_info'
            => empty(array_filter([
                $data['buyer_name'] ?? null, $data['factory_name'] ?? null,
                $data['po_number'] ?? null, $data['product_description'] ?? null,
                $data['order_quantity'] ?? null,
            ], $hasVal)),

        $type === 'images'
            => $imgs->isEmpty() && empty($data['notes']),

        $type === 'workmanship'
            => collect($data['items'] ?? [])->every(fn($i) => empty($i['status'])),

        $type === 'defects'
            => $rs->defects->isEmpty(),

        $type === 'aql'
            => !$currentRun->aql
               || (($currentRun->aql->found_critical ?? 0) == 0 && ($currentRun->aql->found_major ?? 0) == 0
                   && ($currentRun->aql->found_minor ?? 0) == 0 && ($currentRun->aql->lot_size ?? 0) == 0),

        $type === 'checklist'
            => !empty($data['items'])
                ? collect($data['items'])->every(fn($i) => empty($i['result']))
                : empty(array_filter(collect($data)->except('items')->all(), $hasVal)) && $imgs->isEmpty(),

        $type === 'container'
            => empty(array_filter([
                $data['container_number'] ?? null, $data['seal_number'] ?? null,
                $data['loading_date'] ?? null, $data['notes'] ?? null,
            ], $hasVal)) && $imgs->isEmpty(),

        $type === 'verification'
            => empty(array_filter(collect($data)->except('items')->all(), $hasVal))
               && (empty($data['items']) || collect($data['items'])->every(fn($i) => empty($i['result'])))
               && $imgs->isEmpty(),

        $type === 'review' && $slug === 'overall_carton_condition'
            => empty($data['overall_condition']) && empty($data['remarks']) && $imgs->isEmpty(),

        $type === 'review' && $slug === 'corrective_action'
            => collect($data['items'] ?? [])->every(fn($i) => empty($i['defect_description']) && empty($i['corrective_action'])),

        $type === 'review' && $slug === 'overall_article_result'
            => empty($data['overall_result']) && empty($data['remarks']),

        $type === 'review'
            => empty(array_filter([$data['overall_verdict'] ?? null, $data['qc_remarks'] ?? null], $hasVal)),

        $type === 'article_results'
            => collect($data['articles'] ?? [])->every(fn($a) => empty($a['result']) && empty($a['article_no'])),

        $type === 'conclusion'
            => empty(array_filter([$data['presented_styles'] ?? null, $data['unpresented_styles'] ?? null, $data['note'] ?? null], $hasVal)),

        $type === 'finish'
            => empty($data['comments']) && !$currentRun->completed_at,

        $type === 'quantity_sampling'
            => empty(array_filter([$data['product_quantity'] ?? null, $data['packed_goods_qty'] ?? null, $data['packed_cartons_qty'] ?? null], $hasVal)),

        $type === 'cartons'
            => collect($data['cartons'] ?? [])->every(fn($c) => empty(array_filter($c, $hasVal))),

        $type === 'cover_photo'
            => $imgs->isEmpty(),

        $type === 'files_review'
            => $imgs->isEmpty() && empty($data['notes']) && empty($data['acknowledged']),

        $type === 'checkpoint'
            => collect($data['tasks'] ?? [])->every(fn($t) => empty($t['selected'] ?? null)),

        $type === 'production_stages'
            => collect($data['selections'] ?? [])->filter(fn($s) => !empty($s['stage']))->isEmpty(),

        default => collect($data)->filter(fn($v) => $v !== null && $v !== '' && !is_array($v))->isEmpty()
                   && empty($data['items']),
    };
};

// Shared "hide empty column" helper for section data tables (Production Status, Carton
// Dimensions & Weight, …): a column is only worth printing if at least one row has a value
// for it — an all-blank column just widens the table without showing anything useful.
$colHasData = fn($rows, $key) => collect($rows)->contains(
    fn($row) => trim((string) ($row[$key] ?? '')) !== ''
);

// Build Section Summary Dashboard data
$summaryRows = [];
foreach ($runs as $sumRun) {
    foreach ($sumRun->runSections as $sumRs) {
        // Sections the inspector explicitly skipped are excluded from the export entirely —
        // they never contribute to the roll-up dashboard either.
        if (!$sumRs->section || in_array($sumRs->section->slug, $hiddenSlugs) || $sumRs->status === 'na') continue;
        $sName = $sumRs->section->name;
        $sType = $sumRs->section->section_type;
        $sSlug = $sumRs->section->slug;

        if (!isset($summaryRows[$sName])) {
            $summaryRows[$sName] = [
                'type'        => $sType,
                'slug'        => $sSlug,
                'sortPriority'=> $sumRs->section->sort_order ?? ($sectionTypePriority[$sType] ?? 99),
                'statuses'    => [],
                'passCount'   => 0,
                'failCount'   => 0,
                'naCount'     => 0,
                'totalCount'  => 0,
                'customDetail'=> '',
            ];
        }
        $summaryRows[$sName]['statuses'][] = $sumRs->status;

        if (in_array($sType, ['checklist', 'verification']) && !empty($sumRs->data['items'])) {
            foreach ($sumRs->data['items'] as $si) {
                $sr = strtolower((string)($si['result'] ?? ''));
                if ($sr === '') continue;
                $summaryRows[$sName]['totalCount']++;
                if ($sr === 'pass') $summaryRows[$sName]['passCount']++;
                elseif ($sr === 'fail') $summaryRows[$sName]['failCount']++;
                elseif (in_array($sr, ['n/a', 'na'])) $summaryRows[$sName]['naCount']++;
            }
        } elseif ($sType === 'checkpoint') {
            $taskDefs = $sumRs->section->default_data['tasks'] ?? [];
            foreach ($taskDefs as $td) {
                $sr = strtolower((string)($sumRs->data['tasks'][$td['key']]['selected'] ?? ''));
                if ($sr === '') continue;
                $summaryRows[$sName]['totalCount']++;
                if ($sr === 'pass') $summaryRows[$sName]['passCount']++;
                elseif ($sr === 'fail') $summaryRows[$sName]['failCount']++;
                elseif (in_array($sr, ['n/a', 'na'])) $summaryRows[$sName]['naCount']++;
            }
        } elseif ($sType === 'aql') {
            $aqlV = strtolower($sumRun->aql?->verdict ?? '');
            if ($aqlV === 'pass') $summaryRows[$sName]['passCount']++;
            elseif ($aqlV === 'fail') $summaryRows[$sName]['failCount']++;
            $summaryRows[$sName]['customDetail'] = 'Verdict: '.($sumRun->aql?->verdict ?? 'Pending');
        } elseif ($sType === 'defects') {
            $dRows  = $sumRs->defects;
            $dCritD = $dRows->filter(fn($r) => ($r->severity ?? $defectInfo($r->defect_id)['severity']) === 'critical')->count();
            $dMajD  = $dRows->filter(fn($r) => ($r->severity ?? $defectInfo($r->defect_id)['severity']) === 'major')->count();
            $dMinD  = $dRows->filter(fn($r) => ($r->severity ?? $defectInfo($r->defect_id)['severity']) === 'minor')->count();
            $dTotal = $dCritD + $dMajD + $dMinD;
            $summaryRows[$sName]['customDetail'] = $dTotal > 0
                ? $dCritD.' Crit, '.$dMajD.' Major, '.$dMinD.' Minor'
                : 'None recorded';
        } elseif ($sSlug === 'overall_article_result') {
            $summaryRows[$sName]['assessment'] = true;
            $summaryRows[$sName]['customDetail'] = "Inspector's assessment: ".($sumRs->data['overall_result'] ?? '—');
        } elseif ($sType === 'article_results') {
            foreach ($sumRs->data['articles'] ?? [] as $ar) {
                $sr = strtolower((string)($ar['result'] ?? ''));
                if ($sr === '') continue;
                $summaryRows[$sName]['totalCount']++;
                if ($sr === 'pass') $summaryRows[$sName]['passCount']++;
                elseif ($sr === 'fail') $summaryRows[$sName]['failCount']++;
                elseif (in_array($sr, ['n/a', 'na'])) $summaryRows[$sName]['naCount']++;
            }
        }
    }
}

// Sort by section type priority (then by first-seen order within same priority)
uasort($summaryRows, fn($a, $b) => $a['sortPriority'] <=> $b['sortPriority']);

// Summary strip: sections complete (same sections the report lists), AQL result per run.
$stripSections = $runs->flatMap(fn ($r) => $r->runSections->filter(
    fn ($x) => $x->section && !in_array($x->section->slug, $hiddenSlugs) && $x->status !== 'na'
));
$stripDone  = $stripSections->where('status', 'complete')->count();
$stripTotal = $stripSections->count();
$stripAql   = $runs->filter(fn ($r) => $r->aql)->map(fn ($r) => $r->aql->verdict ?? 'Pending')->countBy();
$stripAqlText = $stripAql->isEmpty()
    ? 'Not used'
    : ($stripAql->count() === 1 && $runs->filter(fn ($r) => $r->aql)->count() === 1
        ? strtoupper($stripAql->keys()->first())
        : $stripAql->map(fn ($n, $v) => strtoupper($v).' '.$n)->implode(' · '));
@endphp

{{-- ══════════════════════════════════════════ RUNNING HEADER + FOOTER ═══ --}}
@php $companySetting = \App\Models\CompanySetting::current(); /* read once, shared by both partials */ @endphp
@include('exports.partials._brand-header', ['docTitle' => 'Quality Inspection Report', 'docNumber' => $inspection->report_number])
@include('exports.partials._brand-footer', ['footerNote' => 'Confidential — for authorized recipients only'])

{{-- ══════════════════════════════════════════ COVER PAGE ═════════════════ --}}

{{-- Title block --}}
<div class="cover-title-block">
    <div class="cover-kicker">{{ $inspection->inspectionType?->name ?? 'Quality Inspection' }}</div>
    <div class="cover-main-title">Quality Inspection Report</div>
    <div class="cover-main-subtitle">{{ $inspection->report_number }} &mdash; Generated {{ now()->format('d F Y, H:i') }}</div>
    <div class="cover-accent"></div>
</div>

{{-- Details + the one decision --}}
<table class="cover-grid">
    <tr>
        <td class="cg-left">
            <div class="info-panel">
                <div class="info-panel-header">Report Information</div>
                <div class="info-panel-body">
                    <table class="cover-kv">
                        <tr><td class="ck">Report Number</td><td class="cv">{{ $inspection->report_number }}</td></tr>
                        <tr><td class="ck">Inspection Date</td><td class="cv">{{ $inspection->inspection_date?->format('d F Y') ?? '—' }}</td></tr>
                        <tr><td class="ck">Customer</td><td class="cv">{{ $coverCustomer }}</td></tr>
                        <tr><td class="ck">Supplier / Factory</td><td class="cv">{{ $coverFactory }}</td></tr>
                        <tr><td class="ck">Style / Sample</td><td class="cv">{{ $coverSample }}</td></tr>
                        <tr><td class="ck">Colour</td><td class="cv">{{ $coverColour }}</td></tr>
                        <tr><td class="ck">Inspection Type</td><td class="cv">{{ $inspection->inspectionType?->name ?? '—' }}</td></tr>
                        <tr><td class="ck">Inspector(s)</td><td class="cv">{{ $inspection->inspectors->pluck('employee_name')->implode(', ') ?: '—' }}</td></tr>
                        <tr><td class="ck">Customer Orders</td><td class="cv">{{ $inspection->customerOrders->count() > 0 ? $inspection->customerOrders->count().' order(s)' : '—' }}</td></tr>
                        <tr><td class="ck">Total Runs</td><td class="cv">{{ $runs->count() }}</td></tr>
                        <tr><td class="ck">Report Generated</td><td class="cv">{{ now()->format('d F Y, H:i') }}</td></tr>
                        @if($inspection->remarks)
                        <tr><td class="ck">Remarks</td><td class="cv">{{ $inspection->remarks }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </td>
        <td class="cg-right">
            <div class="info-panel">
                <div class="info-panel-header">Decision</div>
                <div class="info-panel-body">
                    <div class="verdict-badge-lg vb-{{ $verdictTone($inspection->overall_status) }}">
                        {{ $overallDisplay['label'] }}
                    </div>
                    @foreach($runs as $asRun)
                        @php $asm = $assessmentOf($asRun); @endphp
                        @if($asm)
                        <div class="assessment-line">
                            <strong>Inspector's assessment &mdash; Run #{{ $asRun->run_number }}:</strong>
                            {{ $asm['choice'] }}
                            @if($asm['decision'] === null)
                                (run not finished; the decision is calculated when it is finished)
                            @elseif($asm['differs'])
                                &mdash; differs from the decision ({{ \App\Services\Inspection\InspectionVerdictService::display($asm['decision'])['label'] }}).
                                Reason: {{ $asm['reason'] !== '' ? $asm['reason'] : 'no reason recorded' }}
                            @else
                                &mdash; agrees with the decision.
                            @endif
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- Summary strip --}}
<table class="summary-strip">
    <tr>
        <td style="width:26%">
            <span class="ss-label">Sections complete</span>
            <span class="ss-value">{{ $stripDone }} of {{ $stripTotal }}</span>
        </td>
        <td style="width:48%">
            <span class="ss-label">Defects by severity</span>
            <table class="defect-pill-row">
                <tr>
                    <td><div class="dp-box dp-critical"><span class="dp-num dp-crit-num">{{ $defCritical }}</span><span class="dp-lbl">Critical</span></div></td>
                    <td><div class="dp-box dp-major"><span class="dp-num dp-maj-num">{{ $defMajor }}</span><span class="dp-lbl">Major</span></div></td>
                    <td><div class="dp-box dp-minor"><span class="dp-num dp-min-num">{{ $defMinor }}</span><span class="dp-lbl">Minor</span></div></td>
                </tr>
            </table>
        </td>
        <td style="width:26%">
            <span class="ss-label">AQL result</span>
            <span class="ss-value">{{ $stripAqlText }}</span>
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
            } elseif (!empty($sRow['assessment'])) {
                $sBadge = 'badge-na'; $sLabel = 'RECORDED';
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
                    $sDetailStr .= ' → '.$sLabel;
                }
            } else {
                $sDetailStr = '';
            }
        @endphp
        <tr>
            <td class="st-idx">{{ $loop->iteration }}</td>
            <td class="st-section">{{ $sRowName }}</td>
            <td class="st-detail">{{ $sDetailStr }}</td>
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
                    'Pass'                         => 'rb-pass',
                    'Fail'                         => 'rb-fail',
                    'Conditional', 'Re-Inspection' => 'rb-na',
                    default                        => 'rb-def',
                };
            @endphp
            <tr>
                <td style="text-align:center; font-weight:bold; color:#00699A">{{ $r->run_number }}</td>
                <td style="font-weight:500">{{ $r->sample?->sample_code ?? '—' }}</td>
                <td>{{ $r->sample?->product_name ?? '—' }}</td>
                <td style="color:#5B6470">{{ $r->sample?->customer?->display_name ?? '—' }}</td>
                <td style="text-align:center"><span class="{{ $rvc }}">{{ $r->verdict ?? 'Pending' }}</span></td>
                <td style="font-size:8pt; color:#5B6470">{{ $r->completed_at?->format('d M Y') ?? 'In Progress' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="disclaimer">
    <strong style="color:#5B6470">Confidentiality Notice:</strong>
    This inspection report is intended solely for the use of the named recipient and contains confidential quality control information.
    Any unauthorized review, disclosure, copying, distribution, or use of this report is strictly prohibited.
    Generated by TradeSyncERP &mdash; {{ now()->format('d F Y \a\t H:i') }}.
</div>

{{-- ══════════════════════════════════════════ RUNS DETAIL ════════════════ --}}

@foreach($runs as $runIndex => $run)

@php
    $isFirstRunOfGroup = $runIndex === 0 || $runs[$runIndex - 1]->sample_id !== $run->sample_id;
@endphp

@if($isFirstRunOfGroup)
    @php
        $styleGroup   = $runGroups->get($run->sample_id, collect([$run]));
        $groupPassed  = $styleGroup->filter(fn($r) => $r->completed_at && $r->verdict === 'Pass')->count();
        $groupTotal   = $styleGroup->count();
        // Same roll-up as the cover, so the two can never disagree.
        $groupStatus  = \App\Services\Inspection\InspectionVerdictService::rollUp($styleGroup->map(fn($r) => ['verdict' => $r->verdict, 'finished' => $r->completed_at !== null]));
    @endphp
    <div class="page-break"></div>
    <div class="style-group-header">
        <div class="sgh-title">
            {{ $run->sample?->sample_code }}
            @if($run->sample?->product_name) &mdash; {{ $run->sample->product_name }} @endif
        </div>
        @if($groupTotal > 1)
        <div class="sgh-rollup">{{ $groupPassed }} of {{ $groupTotal }} colors passed &bull; {{ \App\Services\Inspection\InspectionVerdictService::display($groupStatus)['label'] }}</div>
        @endif
    </div>
@else
    <div class="page-break"></div>
@endif

@php
    $runVerdictClass = 'vb-'.$verdictTone($run->verdict);
    $visibleSections = $run->runSections->filter(
        // Sections the inspector explicitly skipped ('na' status) are left out of the export
        // entirely — no header, table, checkpoints, images, or remarks for them.
        fn($rs) => $rs->section && !in_array($rs->section->slug, $hiddenSlugs) && $rs->status !== 'na'
    )->sortBy(fn($rs) =>
        $rs->section->sort_order ?? ($sectionTypePriority[$rs->section->section_type] ?? 99)
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
                        @if($run->sampleColor) &nbsp;&bull;&nbsp; Color: {{ $run->sampleColor->name }} @endif
                        @if($run->sample->customer) &nbsp;&bull;&nbsp; Customer: {{ $run->sample->customer->display_name }} @endif
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

    $images  = $rs->attachments->filter(fn($a) => $a->isImage())->values();
    $docs    = $rs->attachments->filter(fn($a) => !$a->isImage())->values();
    $isEmpty = $isSectionEmpty($rs, $run);

    // Photos are collected here as the section is rendered (with a row label, where one applies)
    // and rendered once, after the data table, instead of as a cramped in-table column.
    $galleryItems     = [];
    $consumedImageIds = [];
    // $label is only set for images tied to a specific row (defect name, checkpoint label,
    // carton type, production stage, …). General / reference gallery photos pass null and
    // are rendered with no caption at all — never fall back to the raw filename.
    // Marks images as accounted for without displaying them — used for checkpoint/checklist rows
    // whose result is N/A, so their photos don't fall through to the unlabeled leftover gallery.
    $suppressGallery = function ($imgs) use (&$consumedImageIds) {
        foreach ($imgs as $img) {
            $consumedImageIds[] = $img->id;
        }
    };
    $addGallery = function ($imgs, $label = null) use (&$galleryItems, &$consumedImageIds) {
        foreach ($imgs as $img) {
            $galleryItems[]     = ['img' => $img, 'label' => $label ?: null];
            $consumedImageIds[] = $img->id;
        }
    };
    $imgsByTask = fn(string $taskKey) => $images->where('task_key', $taskKey)->values();
@endphp

@if($isEmpty)
{{-- Compact collapsed state for sections with no data --}}
<div class="section-block-empty">
    <table style="width:100%; border-collapse:collapse">
        <tr>
            <td style="padding:7px 12px; vertical-align:middle">
                <span class="sec-num" style="background:#5B6470">{{ $secIdx }}</span>
                <span style="font-size:9pt; font-weight:bold; color:#5B6470">{{ $sec->name }}</span>
            </td>
            <td style="padding:7px 12px; text-align:right; width:120px; vertical-align:middle">
                <span class="badge badge-na" style="color:#5B6470">No data recorded</span>
            </td>
        </tr>
    </table>
</div>
@else
<div class="section-block">
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

        {{-- ════════════ PRODUCT SCREENING / IMAGES ════════════ --}}
        @elseif($secType === 'images')
        @php $addGallery($images, null); @endphp
        @if($images->isEmpty())
        <div class="empty-state">No product images uploaded.</div>
        @endif
        @if(!empty($data['notes']))
        <div class="section-note">{{ $data['notes'] }}</div>
        @endif

        {{-- ════════════ WORKMANSHIP CHECK (legacy) ════════════ --}}
        @elseif($secType === 'workmanship')
        @php $wItems = $data['items'] ?? []; @endphp
        @if(!empty($wItems))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:60%">Item</th>
                    <th style="width:18%; text-align:center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wItems as $wIdx => $wi)
                @php
                    $wRes = $wi['status'] ?? '—';
                    $wCls = match(strtolower((string)$wRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $wIdx+1 }}</td>
                    <td>{{ $wi['name'] ?? '' }}
                        @if(!empty($wi['description']))<div style="color:#5B6470; font-size:7.5pt">{{ $wi['description'] }}</div>@endif
                    </td>
                    <td style="text-align:center"><span class="{{ $wCls }}">{{ $wRes }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ DEFECT RECORDING ════════════ --}}
        @elseif($secType === 'defects')
        @php
            $defectRows = $rs->defects;
            $dCrit = 0; $dMaj = 0; $dMin = 0;
            foreach ($defectRows as $_row) {
                $sev = $_row->severity ?? $defectInfo($_row->defect_id)['severity'];
                if ($sev === 'critical') $dCrit++;
                elseif ($sev === 'major') $dMaj++;
                elseif ($sev === 'minor') $dMin++;
            }
        @endphp
        @if($defectRows->isEmpty())
        <div class="empty-state">No defects recorded for this inspection run.</div>
        @else
        <table style="width:100%; margin-bottom:10px; border-collapse:collapse">
            <tr>
                <td style="width:33%; padding:0 4px 0 0">
                    <div style="background:#FDE8E7; border-radius:4px; padding:7px 8px; text-align:center">
                        <div style="font-size:16pt; font-weight:bold; color:#A32019">{{ $dCrit }}</div>
                        <div style="font-size:6.5pt; color:#A32019; text-transform:uppercase; letter-spacing:1px">Critical</div>
                    </div>
                </td>
                <td style="width:33%; padding:0 4px">
                    <div style="background:#FFEBDC; border-radius:4px; padding:7px 8px; text-align:center">
                        <div style="font-size:16pt; font-weight:bold; color:#8F3B00">{{ $dMaj }}</div>
                        <div style="font-size:6.5pt; color:#8F3B00; text-transform:uppercase; letter-spacing:1px">Major</div>
                    </div>
                </td>
                <td style="width:33%; padding:0 0 0 4px">
                    <div style="background:#E5EEFC; border-radius:4px; padding:7px 8px; text-align:center">
                        <div style="font-size:16pt; font-weight:bold; color:#00699A">{{ $dMin }}</div>
                        <div style="font-size:6.5pt; color:#00699A; text-transform:uppercase; letter-spacing:1px">Minor</div>
                    </div>
                </td>
            </tr>
        </table>
        <table class="defect-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:19%">Defect Description</th>
                    <th style="width:11%; text-align:center">Severity</th>
                    <th style="width:6%; text-align:center">Size</th>
                    <th style="width:6%; text-align:center">Qty</th>
                    <th style="width:8%; text-align:center">Carton</th>
                    <th style="width:9%; text-align:center">Status</th>
                    <th style="width:9%; text-align:center">Disp.</th>
                    <th style="width:13%">Notes</th>
                    <th style="width:15%">Photos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($defectRows as $i => $row)
                @php
                    $defInfo = $defectInfo($row->defect_id);
                    $sev     = $row->severity ?? $defInfo['severity'];
                    // This row's photos are shown as thumbnails in the row (not again in the gallery).
                    $rowPhotos = $imgsByTask('defect_row_'.$row->id);
                    $suppressGallery($rowPhotos);
                @endphp
                <tr>
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $i+1 }}</td>
                    <td><strong>{{ $defInfo['name'] }}</strong></td>
                    <td style="text-align:center"><span class="sev-{{ $sev }}">{{ ucfirst($sev) }}</span></td>
                    <td style="text-align:center">{{ $row->size ?? '—' }}</td>
                    <td style="text-align:center; font-weight:bold">{{ $row->qty ?? 1 }}</td>
                    <td style="text-align:center">{{ $row->carton_no ?? '—' }}</td>
                    <td style="text-align:center">{{ ucfirst($row->status ?? 'open') }}</td>
                    <td style="text-align:center">{{ $row->disposition_code ?? '—' }}</td>
                    <td style="color:#2B3137">{{ $row->notes ?? '' }}</td>
                    <td>
                        @forelse($rowPhotos as $rowPhoto)
                            @php $rowB64 = $imgBase64($rowPhoto->file_path); @endphp
                            @if($rowB64)<img src="{{ $rowB64 }}" class="defect-thumb" alt="">@else<span class="rb-def">Image unavailable</span>@endif
                        @empty
                            <span class="rb-def">&mdash;</span>
                        @endforelse
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
        {{-- Sampling basis: the plan actually applied per severity (QC_RULES §1) --}}
        <div class="section-note" style="margin-bottom:6px">
            <strong>Sampling basis:</strong> {{ $aql->standard_ref ?? \App\Services\Inspection\AqlCalculationService::STANDARD_REF }};
            normal inspection, general/special level {{ $aql->inspection_level ?? '—' }}; switching rules not applied.
            @if($aql->sample_size && $aql->lot_size && $aql->sample_size >= $aql->lot_size) 100% inspection (sample equals lot). @endif
            Found counts {{ $aql->counts_source === 'defect_rows' ? 'counted from the recorded defects' : 'entered by the inspector' }}.
        </div>
        <table class="aql-table">
            <thead>
                <tr>
                    <th style="text-align:left; width:22%">Defect Category</th>
                    <th>AQL Level</th>
                    <th>Code</th>
                    <th>Sample (n)</th>
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
                    // No Ac (no plan for this severity) can never print PASS (docs/QC_RULES.md).
                    $rowRes   = $ac === null ? '—' : ($found > $ac ? 'FAIL' : 'PASS');
                    $rowCls   = $rowRes === 'FAIL' ? 'rb-fail' : ($rowRes === 'PASS' ? 'rb-pass' : '');
                @endphp
                <tr>
                    <td class="aql-cat">{{ $label }}</td>
                    <td>{{ $aqlLevel === null ? '—' : ((float) $aqlLevel === -1.0 ? 'Not allowed' : $aqlLevel.'%') }}</td>
                    <td>{{ $aql->{"code_letter_{$key}"} ?? '—' }}</td>
                    <td>{{ $aql->{"sample_size_{$key}"} ?? '—' }}</td>
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
        @if(!empty($aql->variations))
        <div class="sub-heading">Quantity Distribution by Variation</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Color</th>
                    <th>Size</th>
                    <th style="text-align:center">Order Qty</th>
                    <th style="text-align:center">Inspect Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aql->variations as $var)
                <tr>
                    <td>{{ $var['color'] ?? '—' }}</td>
                    <td>{{ $var['size'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $var['order_qty'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $var['inspect_qty'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @if($aql->sizeBreakdowns->isNotEmpty())
        <div class="sub-heading">Per-Size Checked / Error Breakdown</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Size</th>
                    <th style="text-align:center">Order Qty</th>
                    <th style="text-align:center">Checked Qty</th>
                    <th style="text-align:center">Error Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aql->sizeBreakdowns as $sb)
                <tr>
                    <td>{{ $sb->size_label }}</td>
                    <td style="text-align:center">{{ $sb->order_qty }}</td>
                    <td style="text-align:center">{{ $sb->checked_qty }}</td>
                    <td style="text-align:center">{{ $sb->error_qty }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @else
        <div class="empty-state">No AQL sampling data recorded.</div>
        @endif

        {{-- ════════════ CHECKLIST (covers carton_verification, packaging_check, labels_check,
             order_quantity_vs_packing_list, loading_schedule_and_timing, inner_conditions_of_container,
             pre_production_checklist, factory_readiness, raw_material_check, variations_sample,
             barcode_testing, variations_techpack, carton_dimensions_weight) ════════════ --}}
        @elseif($secType === 'checklist')
        @php
            $items   = $data['items'] ?? [];
            $cartons = $data['cartons'] ?? [];
            $skipKeys = ['items', 'cartons', 'dim_unit', 'weight_unit', 'barcode_status', 'tech_pack_reference', 'result'];
            $extra = collect($data)->except($skipKeys)->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
        @endphp
        @if($extra->isNotEmpty())
        <table class="meta-table" style="margin-bottom:10px">
            @foreach($extra as $k => $v)
            <tr><td class="mk">{{ ucwords(str_replace('_', ' ', $k)) }}</td><td class="mv">{{ $v }}</td></tr>
            @endforeach
        </table>
        @endif

        @if($slug === 'barcode_testing')
        @php
            $bStatus = $data['barcode_status'] ?? null;
            $bCls = match($bStatus) { 'functional' => 'rb-pass', 'non-functional' => 'rb-fail', 'partial' => 'rb-na', default => 'rb-def' };
            $addGallery($imgsByTask('barcode_photos'), null);
        @endphp
        @if($bStatus)
        <table class="meta-table">
            <tr><td class="mk">Barcode Status</td><td class="mv"><span class="{{ $bCls }}">{{ ucfirst($bStatus) }}</span></td></tr>
        </table>
        @endif

        @elseif($slug === 'variations_techpack')
        @php $addGallery($imgsByTask('techpack_photos'), null); @endphp

        @elseif($slug === 'carton_dimensions_weight')
        @php
            $dimUnit    = $data['dim_unit'] ?? 'cm';
            $weightUnit = $data['weight_unit'] ?? 'kg';
            // Columns that are frequently left blank (e.g. Net Weight) are hidden entirely
            // when none of the rows carry a value, instead of printing an empty column.
            $showLength = $colHasData($cartons, 'length');
            $showWidth  = $colHasData($cartons, 'width');
            $showHeight = $colHasData($cartons, 'height');
            $showGross  = $colHasData($cartons, 'gross_weight');
            $showNet    = $colHasData($cartons, 'net_weight');
            $showRemarks = $colHasData($cartons, 'remarks');
        @endphp
        @if(!empty($cartons))
        <table class="data-table">
            <thead>
                <tr>
                    <th>Carton Type / Variant</th>
                    @if($showLength)<th style="text-align:center">Length ({{ $dimUnit }})</th>@endif
                    @if($showWidth)<th style="text-align:center">Width ({{ $dimUnit }})</th>@endif
                    @if($showHeight)<th style="text-align:center">Height ({{ $dimUnit }})</th>@endif
                    @if($showGross)<th style="text-align:center">Gross Wt. ({{ $weightUnit }})</th>@endif
                    @if($showNet)<th style="text-align:center">Net Wt. ({{ $weightUnit }})</th>@endif
                    @if($showRemarks)<th>Remarks</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($cartons as $ci => $carton)
                @php
                    $cLabel = $carton['carton_type'] ?: ('Carton ' . ($ci + 1));
                    $addGallery($imgsByTask('carton_dim_'.$ci), $cLabel);
                @endphp
                <tr>
                    <td style="font-weight:bold">{{ $cLabel }}</td>
                    @if($showLength)<td style="text-align:center">{{ $carton['length'] ?? '—' }}</td>@endif
                    @if($showWidth)<td style="text-align:center">{{ $carton['width'] ?? '—' }}</td>@endif
                    @if($showHeight)<td style="text-align:center">{{ $carton['height'] ?? '—' }}</td>@endif
                    @if($showGross)<td style="text-align:center">{{ $carton['gross_weight'] ?? '—' }}</td>@endif
                    @if($showNet)<td style="text-align:center">{{ $carton['net_weight'] ?? '—' }}</td>@endif
                    @if($showRemarks)<td style="color:#5B6470; font-size:8pt">{{ $carton['remarks'] ?? '' }}</td>@endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @elseif(!empty($items))
        @php $showItemRemarks = $colHasData($items, 'remarks'); @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="{{ $showItemRemarks ? 'width:46%' : '' }}">Checkpoint</th>
                    <th style="width:14%; text-align:center">Result</th>
                    @if($showItemRemarks)<th>Remarks</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                @php
                    $rClass = match(strtolower($item['result'] ?? '')) {
                        'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def'
                    };
                    // N/A checkpoints don't warrant photo evidence — hide their images in the export.
                    if (in_array(strtolower($item['result'] ?? ''), ['n/a', 'na'], true)) {
                        $suppressGallery($imgsByTask('checkpoint_'.$idx));
                    } else {
                        $addGallery($imgsByTask('checkpoint_'.$idx), $item['label'] ?? null);
                    }
                @endphp
                <tr>
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $idx+1 }}</td>
                    <td>{{ $item['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $rClass }}">{{ !empty($item['result']) ? $item['result'] : '—' }}</span></td>
                    @if($showItemRemarks)<td style="color:#5B6470; font-size:8pt">{{ $item['remarks'] ?? '' }}</td>@endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ CHECKPOINT (Packing Check SI, Functional Test, etc.) ════════════ --}}
        @elseif($secType === 'checkpoint')
        @php
            $taskDefs = $sec->default_data['tasks'] ?? [];
            $taskData = $data['tasks'] ?? [];
        @endphp
        @if(!empty($taskDefs))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:56%">Checkpoint</th>
                    <th style="width:18%; text-align:center">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($taskDefs as $tIdx => $taskDef)
                @php
                    $tKey = $taskDef['key'] ?? '';
                    $tRes = $taskData[$tKey]['selected'] ?? '—';
                    $tCls = match(strtolower((string)$tRes)) {
                        'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def'
                    };
                    // N/A checkpoints don't warrant photo evidence — hide their images in the export.
                    if (in_array(strtolower((string)$tRes), ['n/a', 'na'], true)) {
                        $suppressGallery($imgsByTask($tKey));
                    } else {
                        $addGallery($imgsByTask($tKey), $taskDef['label'] ?? $tKey);
                    }
                @endphp
                <tr>
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $tIdx+1 }}</td>
                    <td>{{ $taskDef['label'] ?? $tKey }}</td>
                    <td style="text-align:center"><span class="{{ $tCls }}">{{ $tRes ?: '—' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ PRODUCTION STATUS ════════════ --}}
        @elseif($secType === 'production_stages')
        @php
            $psSelections = collect($data['selections'] ?? [])->filter(fn($s) => !empty($s['stage']))->values();
            // % Complete / Qty / Notes are optional per stage — hide a column entirely when
            // every stage in this run left it blank, instead of printing an empty column.
            $showPct     = $colHasData($psSelections, 'percentage');
            $showQty     = $colHasData($psSelections, 'quantity');
            $showComment = $colHasData($psSelections, 'comment');
        @endphp
        @if($psSelections->isEmpty())
        <div class="empty-state">No production stages recorded.</div>
        @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th>Production Stage</th>
                    @if($showPct)<th style="width:14%; text-align:center">% Complete</th>@endif
                    @if($showQty)<th style="width:16%; text-align:center">Qty / Output</th>@endif
                    @if($showComment)<th>Status Notes</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($psSelections as $psIdx => $ps)
                @php
                    $stageKey = 'ps_' . preg_replace('/[^a-z0-9_]/', '', strtolower(str_replace(' ', '_', $ps['stage'])));
                    $addGallery($imgsByTask($stageKey), $ps['stage']);
                @endphp
                <tr>
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $psIdx+1 }}</td>
                    <td>{{ $ps['stage'] }}</td>
                    @if($showPct)<td style="text-align:center">{{ $ps['percentage'] !== '' && $ps['percentage'] !== null ? $ps['percentage'].'%' : '—' }}</td>@endif
                    @if($showQty)<td style="text-align:center">{{ $ps['quantity'] ?? '—' }}</td>@endif
                    @if($showComment)<td style="color:#5B6470; font-size:8pt">{{ $ps['comment'] ?? '' }}</td>@endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @if(!empty($data['notes']))
        <div class="section-note" style="margin-top:8px">{{ $data['notes'] }}</div>
        @endif

        {{-- ════════════ CONTAINER DETAILS ════════════ --}}
        @elseif($secType === 'container')
        @php
            $cf = array_filter([
                'Container Number'    => $data['container_number'] ?? null,
                'Container Type'      => $data['container_type'] ?? null,
                'Seal Number'         => $data['seal_number'] ?? null,
                'Loading Date'        => $data['loading_date'] ?? null,
                'Loading Start Time'  => $data['loading_start_time'] ?? null,
                'Loading End Time'    => $data['loading_end_time'] ?? null,
                'Loading Port'        => $data['loading_port'] ?? null,
                'Discharge Port'      => $data['discharge_port'] ?? null,
                'Total Cartons Loaded' => $data['total_cartons_loaded'] ?? null,
                'Total Quantity Loaded' => $data['total_quantity_loaded'] ?? null,
            ], fn($v) => $v !== null && $v !== '');
            $cfHalf = (int) ceil(count($cf) / 2);
            $addGallery($imgsByTask('container_photos'), null);
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
        @if(!empty($data['notes']))
        <div class="section-note" style="margin-top:8px">{{ $data['notes'] }}</div>
        @endif

        {{-- ════════════ VERIFICATION (seal_verification, shipment_verification) ════════════ --}}
        @elseif($secType === 'verification')
        @php
            $verItems = $data['items'] ?? [];
            $verExtra = collect($data)->except('items')->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
            $addGallery($imgsByTask('verification_photos'), null);
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
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $vIdx+1 }}</td>
                    <td>{{ $vi['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $viCls }}">{{ $viRes }}</span></td>
                    <td style="color:#5B6470; font-size:8pt">{{ $vi['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ REVIEW (overall_carton_condition / corrective_action / overall_article_result / final_review) ════════════ --}}
        @elseif($secType === 'review')
        @if($slug === 'overall_carton_condition')
        @php $addGallery($imgsByTask('carton_condition'), null); @endphp
        <table class="meta-table">
            <tr><td class="mk">Overall Carton Condition</td><td class="mv">{{ $data['overall_condition'] ?? '—' }}</td></tr>
        </table>
        @if(!empty($data['remarks']))
        <div class="section-note" style="margin-top:8px">{{ $data['remarks'] }}</div>
        @endif

        @elseif($slug === 'corrective_action')
        @php $capItems = $data['items'] ?? []; @endphp
        @if(!empty($capItems))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th style="width:20%">Defect / Non-Conformance</th>
                    <th style="width:16%">Root Cause</th>
                    <th style="width:18%">Corrective Action</th>
                    <th style="width:12%">Responsible</th>
                    <th style="width:10%; text-align:center">Target Date</th>
                    <th style="width:10%; text-align:center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($capItems as $capIdx => $cap)
                @php
                    $capCls = match($cap['status'] ?? 'Open') {
                        'Closed' => 'rb-pass', 'Rejected' => 'rb-fail', default => 'rb-na',
                    };
                @endphp
                <tr>
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $capIdx+1 }}</td>
                    <td>{{ $cap['defect_description'] ?? '' }}</td>
                    <td style="color:#5B6470; font-size:8pt">{{ $cap['root_cause'] ?? '' }}</td>
                    <td style="color:#5B6470; font-size:8pt">{{ $cap['corrective_action'] ?? '' }}</td>
                    <td>{{ $cap['responsible_party'] ?? '' }}</td>
                    <td style="text-align:center; font-size:8pt">{{ $cap['target_date'] ?? '—' }}</td>
                    <td style="text-align:center"><span class="{{ $capCls }}">{{ $cap['status'] ?? 'Open' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @elseif($slug === 'overall_article_result')
        @php $asm = $assessmentOf($run); @endphp
        <table class="meta-table">
            <tr>
                <td class="mk">Inspector's assessment</td>
                <td class="mv">{{ $data['overall_result'] ?? '—' }} <span style="color:#5B6470">(recorded by the inspector; the decision is calculated from the AQL result and the defects)</span></td>
            </tr>
            @if($asm && $asm['differs'])
            <tr>
                <td class="mk">Reason the assessment differs</td>
                <td class="mv">{{ $asm['reason'] !== '' ? $asm['reason'] : 'no reason recorded' }}</td>
            </tr>
            @endif
            @if(!empty($data['follow_up_date']))
            <tr><td class="mk">Follow-up Date</td><td class="mv">{{ $data['follow_up_date'] }}</td></tr>
            @endif
        </table>
        @if(!empty($data['remarks']))
        <div class="section-note" style="margin-top:8px">{{ $data['remarks'] }}</div>
        @endif

        @else
        {{-- final_review or any other legacy review slug --}}
        @php
            $rf = array_filter([
                'Overall QC Verdict' => $data['overall_verdict'] ?? null,
                'Inspector Name'     => $data['inspector_name'] ?? null,
                'Follow-up Date'     => $data['follow_up_date'] ?? null,
                'Action Required'    => $data['action_required'] ?? null,
                'QC Remarks'         => $data['qc_remarks'] ?? null,
            ], fn($v) => $v !== null && $v !== '');
        @endphp
        @if(!empty($rf))
        <table class="meta-table">
            @foreach($rf as $label => $value)
            <tr><td class="mk">{{ $label }}</td><td class="mv">{{ $value }}</td></tr>
            @endforeach
        </table>
        @endif
        @endif

        {{-- ════════════ ARTICLE RESULTS TABLE ════════════ --}}
        @elseif($secType === 'article_results')
        @php
            $articles = collect($data['articles'] ?? [])->filter(fn($a) => !empty($a['article_no']) || !empty($a['result']))->values();
            // Size is optional per article — hide the column entirely when every article in
            // this run left it blank, instead of printing an empty column (same rule as
            // Production Status's % Complete / Qty / Notes columns above).
            $showSize = $colHasData($articles, 'size');
        @endphp
        @if($articles->isEmpty())
        <div class="empty-state">No article results recorded.</div>
        @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th>Article / Style No.</th>
                    <th>Color</th>
                    @if($showSize)<th>Size</th>@endif
                    <th style="text-align:center">Qty Ordered</th>
                    <th style="text-align:center">Qty Inspected</th>
                    <th style="text-align:center">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($articles as $aIdx => $ai)
                @php
                    $aiRes = $ai['result'] ?? '—';
                    $aiCls = match(strtolower((string)$aiRes)) { 'pass' => 'rb-pass', 'fail' => 'rb-fail', 'n/a','na' => 'rb-na', default => 'rb-def' };
                @endphp
                <tr>
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $aIdx+1 }}</td>
                    <td>{{ $ai['article_no'] ?? '' }}</td>
                    <td>{{ $ai['color'] ?? '' }}</td>
                    @if($showSize)<td>{{ $ai['size'] ?? '' }}</td>@endif
                    <td style="text-align:center">{{ $ai['qty_ordered'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $ai['qty_inspected'] ?? '—' }}</td>
                    <td style="text-align:center"><span class="{{ $aiCls }}">{{ $aiRes }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ CONCLUSION ════════════ --}}
        @elseif($secType === 'conclusion')
        @php
            $cnFields = array_filter([
                'Presented Styles'   => $data['presented_styles'] ?? null,
                'Unpresented Styles' => $data['unpresented_styles'] ?? null,
                'Note'               => $data['note'] ?? null,
            ], fn($v) => $v !== null && $v !== '');
        @endphp
        @if(!empty($cnFields))
        <table class="meta-table">
            @foreach($cnFields as $label => $value)
            <tr><td class="mk">{{ $label }}</td><td class="mv">{{ $value }}</td></tr>
            @endforeach
        </table>
        @endif

        {{-- ════════════ FINISH INSPECTION ════════════ --}}
        @elseif($secType === 'finish')
        <table class="meta-table">
            <tr>
                <td class="mk">Status</td>
                <td class="mv">{{ $run->completed_at ? 'Finished — ' . $run->completed_at->format('d M Y H:i') : 'Not yet finished' }}</td>
            </tr>
            @if(!empty($data['comments']))
            <tr><td class="mk">Final Comments</td><td class="mv">{{ $data['comments'] }}</td></tr>
            @endif
        </table>

        {{-- ════════════ QUANTITY & SAMPLING COUNT ════════════ --}}
        @elseif($secType === 'quantity_sampling')
        @php
            $qsFields = array_filter([
                'Product Quantity'      => $data['product_quantity'] ?? null,
                'Packed Goods Quantity' => $data['packed_goods_qty'] ?? null,
                'Packed Cartons Quantity' => $data['packed_cartons_qty'] ?? null,
            ], fn($v) => $v !== null && $v !== '');
        @endphp
        @if(!empty($qsFields))
        <table class="meta-table">
            @foreach($qsFields as $label => $value)
            <tr><td class="mk">{{ $label }}</td><td class="mv">{{ $value }}</td></tr>
            @endforeach
        </table>
        @endif

        {{-- ════════════ SELECTED CARTONS SI ════════════ --}}
        @elseif($secType === 'cartons')
        @php $selCartons = collect($data['cartons'] ?? [])->filter(fn($c) => array_filter($c))->values(); @endphp
        @if($selCartons->isEmpty())
        <div class="empty-state">No cartons recorded.</div>
        @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%; text-align:center">#</th>
                    <th>Box #</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th style="text-align:center">Qty Inspected</th>
                </tr>
            </thead>
            <tbody>
                @foreach($selCartons as $cIdx => $carton)
                <tr>
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $cIdx+1 }}</td>
                    <td>{{ $carton['box_number'] ?? '' }}</td>
                    <td>{{ $carton['size'] ?? '' }}</td>
                    <td>{{ $carton['color'] ?? '' }}</td>
                    <td style="text-align:center">{{ $carton['qty_inspected'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════ COVER PHOTO ════════════ --}}
        @elseif($secType === 'cover_photo')
        @php $addGallery($images, 'Cover Photo'); @endphp
        @if($images->isEmpty())
        <div class="empty-state">No cover photo uploaded.</div>
        @endif

        {{-- ════════════ FILES TO REVIEW ════════════ --}}
        @elseif($secType === 'files_review')
        <table class="meta-table" style="margin-bottom:10px">
            <tr><td class="mk">Reviewed by Inspector</td><td class="mv">{{ !empty($data['acknowledged']) ? 'Yes' : 'No' }}</td></tr>
        </table>
        @if(!empty($data['notes']))
        <div class="section-note" style="margin-bottom:8px">{{ $data['notes'] }}</div>
        @endif
        @php $addGallery($images, null); @endphp

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
                    <td style="text-align:center; color:#5B6470; font-size:8pt">{{ $fIdx+1 }}</td>
                    <td>{{ $fi['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $fiCls }}">{{ $fiRes }}</span></td>
                    <td style="color:#5B6470; font-size:8pt">{{ $fi['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @if($images->isEmpty() && $fbData->isEmpty() && empty($fbItems))
        <div class="empty-state">No data recorded for this section.</div>
        @endif
        @endif

        {{-- ── Photo gallery: every recorded photo for this section, labeled by the row it
             belongs to where applicable. Any image not explicitly consumed above (a safety net
             against task_key mismatches) is still shown here, unlabeled. ────────────────────── --}}
        @php
            $leftoverImages = $images->reject(fn($img) => in_array($img->id, $consumedImageIds))->values();
            $addGallery($leftoverImages, null);
        @endphp
        @if(!empty($galleryItems))
        <div class="sub-heading">Photos</div>
        @php
            // Group images by their row label (production stage, checkpoint description, carton
            // type, …) so the label is shown once as a group heading instead of being repeated
            // under every single image. Images with no row label (general/reference photos) are
            // grouped together with no heading at all.
            $galleryGroups = collect($galleryItems)->groupBy(fn($gi) => $gi['label'] ?? '');
        @endphp
        @php $photoNo = 0; @endphp
        @foreach($galleryGroups as $galLabel => $galGroup)
            @if($galLabel !== '')
            <div class="img-group-heading">{{ $galLabel }}</div>
            @endif
            @foreach($galGroup->chunk(3) as $galChunk)
            <table class="img-gallery-table" style="margin-bottom:4px">
                <tr>
                    @foreach($galChunk as $galItem)
                    @php $b64 = $imgBase64($galItem['img']->file_path); $photoNo++; @endphp
                    <td>
                        @if($b64)
                            <img src="{{ $b64 }}" class="img-thumb" alt="">
                        @else
                            <div class="img-missing">Image unavailable</div>
                        @endif
                        <div class="img-caption">{{ $galLabel !== '' ? $galLabel.' — ' : '' }}Photo {{ $photoNo }}</div>
                    </td>
                    @endforeach
                    @for($p = count($galChunk); $p < 3; $p++)<td></td>@endfor
                </tr>
            </table>
            @endforeach
        @endforeach
        @endif

        {{-- ── Documents ────────────────────────────────────────────────── --}}
        @if($docs->isNotEmpty())
        <div class="sub-heading">Attached Documents</div>
        <table class="meta-table" style="margin-top:4px">
            @foreach($docs as $doc)
            <tr>
                <td class="mk">{{ $doc->title ?: $doc->file_name }}</td>
                <td class="mv" style="color:#5B6470; font-size:8pt">{{ $doc->file_name }} ({{ $doc->humanFileSize() }})</td>
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
@endif{{-- empty/populated --}}

@endforeach {{-- sections --}}

@endforeach {{-- runs --}}

{{-- ══════════════════════════════════════════ FINAL DECISION CARD ════════ --}}
@php
    $finalVerdictClass = 'vb-'.$verdictTone($inspection->overall_status);
    $finalVerdictIcon = match($inspection->overall_status) {
        'Pass'  => '✓  PASS',
        'Fail'  => '✗  FAIL',
        'Conditional', 'Re-Inspection' => '~  '.$overallDisplay['label'],
        default => $overallDisplay['label'],
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

        @foreach($runs as $asRun)
            @php $asm = $assessmentOf($asRun); @endphp
            @if($asm)
            <div class="final-remarks-box">
                <div class="final-remarks-label">Inspector's assessment &mdash; Run #{{ $asRun->run_number }}</div>
                <div class="final-remarks-text">
                    {{ $asm['choice'] }}
                    @if($asm['decision'] === null)
                        (run not finished; the decision is calculated when it is finished)
                    @elseif($asm['differs'])
                        &mdash; differs from the decision ({{ \App\Services\Inspection\InspectionVerdictService::display($asm['decision'])['label'] }}).
                        Reason: {{ $asm['reason'] !== '' ? $asm['reason'] : 'no reason recorded' }}
                    @else
                        &mdash; agrees with the decision.
                    @endif
                </div>
            </div>
            @endif
        @endforeach

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
                <td class="fmv">{{ $inspection->inspection_date?->format('d F Y') ?? '—' }}</td>
            </tr>
            <tr>
                <td class="fmk">Inspector(s)</td>
                <td class="fmv">{{ $inspection->inspectors->pluck('employee_name')->implode(', ') ?: '—' }}</td>
                <td class="fmk">Total Runs</td>
                <td class="fmv">{{ $runs->count() }}</td>
            </tr>
            <tr>
                <td class="fmk">Defects Found</td>
                <td class="fmv">
                    @if($defCritical + $defMajor + $defMinor > 0)
                        <span style="color:#A32019">{{ $defCritical }} Critical</span>
                        &nbsp;&bull;&nbsp;
                        <span style="color:#8F3B00">{{ $defMajor }} Major</span>
                        &nbsp;&bull;&nbsp;
                        <span style="color:#00699A">{{ $defMinor }} Minor</span>
                    @else
                        <span style="color:#17643A">None recorded</span>
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
