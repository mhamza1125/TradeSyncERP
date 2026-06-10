<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Inspection Report — {{ $inspection->report_number }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: sans-serif;
        font-size: 9pt;
        color: #1a1a1a;
        line-height: 1.4;
    }

    /* ── Page setup ───────────────────────────────── */
    @page {
        margin: 15mm 12mm 18mm 12mm;
    }

    /* ── Page break ───────────────────────────────── */
    .page-break { page-break-after: always; }
    .no-break   { page-break-inside: avoid; }

    /* ── Header ───────────────────────────────────── */
    .report-header {
        border-bottom: 2px solid #1a3a5c;
        padding-bottom: 8px;
        margin-bottom: 12px;
    }
    .report-header table { width: 100%; }
    .report-title {
        font-size: 15pt;
        font-weight: bold;
        color: #1a3a5c;
    }
    .report-subtitle {
        font-size: 8pt;
        color: #555;
        margin-top: 2px;
    }
    .report-number {
        font-size: 10pt;
        font-weight: bold;
        color: #1a3a5c;
        text-align: right;
    }
    .report-date {
        font-size: 8pt;
        color: #555;
        text-align: right;
    }

    /* ── Page footer ──────────────────────────────── */
    .pdf-footer {
        position: fixed;
        bottom: -12mm;
        left: 0; right: 0;
        font-size: 7pt;
        color: #999;
        border-top: 1px solid #ddd;
        padding-top: 3px;
    }
    .pdf-footer table { width: 100%; }

    /* ── Section blocks ───────────────────────────── */
    .section-block {
        margin-bottom: 14px;
        border: 1px solid #d0d7df;
        border-radius: 3px;
        page-break-inside: avoid;
    }
    .section-header {
        background: #f0f4f8;
        padding: 5px 9px;
        border-bottom: 1px solid #d0d7df;
    }
    .section-title {
        font-size: 9.5pt;
        font-weight: bold;
        color: #1a3a5c;
        display: inline;
    }
    .section-status {
        float: right;
        font-size: 7.5pt;
        padding: 1px 6px;
        border-radius: 3px;
        font-weight: bold;
    }
    .status-complete  { background: #d4edda; color: #155724; }
    .status-pending   { background: #fff3cd; color: #856404; }
    .status-na        { background: #e2e3e5; color: #383d41; }
    .section-body  { padding: 8px 9px; }
    .section-notes {
        margin-top: 6px;
        padding: 5px 7px;
        background: #fafbfc;
        border: 1px solid #e8ecf0;
        border-radius: 2px;
        font-style: italic;
        font-size: 8pt;
        color: #555;
    }

    /* ── Meta / KV table ──────────────────────────── */
    .meta-table { width: 100%; border-collapse: collapse; }
    .meta-table td {
        padding: 3px 6px;
        font-size: 8.5pt;
        border-bottom: 1px solid #eef1f4;
        vertical-align: top;
    }
    .meta-table .kv-key {
        width: 34%;
        font-weight: bold;
        color: #444;
        white-space: nowrap;
    }
    .meta-table .kv-val { color: #1a1a1a; }

    /* ── Checklist table ──────────────────────────── */
    .check-table { width: 100%; border-collapse: collapse; }
    .check-table th {
        background: #eef2f7;
        padding: 4px 6px;
        text-align: left;
        font-size: 8pt;
        border: 1px solid #d4dce6;
    }
    .check-table td {
        padding: 3px 6px;
        font-size: 8pt;
        border: 1px solid #e0e6ed;
        vertical-align: top;
    }
    .check-table tr:nth-child(even) td { background: #f8fafc; }

    .result-pass { color: #155724; font-weight: bold; }
    .result-fail { color: #721c24; font-weight: bold; }
    .result-na   { color: #6c757d; }

    /* ── AQL table ────────────────────────────────── */
    .aql-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    .aql-table th {
        background: #1a3a5c;
        color: #fff;
        padding: 4px 6px;
        font-size: 8pt;
        text-align: center;
    }
    .aql-table td {
        padding: 3px 6px;
        font-size: 8pt;
        border: 1px solid #d4dce6;
        text-align: center;
    }
    .aql-verdict-pass { background: #d4edda; color: #155724; font-weight: bold; font-size: 10pt; text-align: center; padding: 4px; }
    .aql-verdict-fail { background: #f8d7da; color: #721c24; font-weight: bold; font-size: 10pt; text-align: center; padding: 4px; }
    .aql-verdict-pending { background: #fff3cd; color: #856404; font-weight: bold; font-size: 10pt; text-align: center; padding: 4px; }

    /* ── Image gallery ────────────────────────────── */
    .img-gallery { margin-top: 4px; }
    .img-gallery table { width: 100%; }
    .img-gallery td { padding: 3px; vertical-align: top; text-align: center; }
    .img-thumb {
        width: 85px;
        height: 85px;
        object-fit: cover;
        border: 1px solid #ccc;
        border-radius: 2px;
        display: block;
        margin: 0 auto 3px auto;
    }
    .img-label { font-size: 7pt; color: #666; word-break: break-all; }

    /* ── Defect table ─────────────────────────────── */
    .defect-table { width: 100%; border-collapse: collapse; }
    .defect-table th {
        background: #dc3545;
        color: #fff;
        padding: 4px 6px;
        font-size: 8pt;
        text-align: left;
    }
    .defect-table td {
        padding: 3px 6px;
        font-size: 8pt;
        border: 1px solid #f0d0d3;
        vertical-align: top;
    }
    .defect-table tr:nth-child(even) td { background: #fdf5f5; }
    .sev-critical { background: #f8d7da; color: #721c24; font-weight: bold; padding: 1px 4px; border-radius: 2px; font-size: 7.5pt; }
    .sev-major    { background: #fff3cd; color: #856404; font-weight: bold; padding: 1px 4px; border-radius: 2px; font-size: 7.5pt; }
    .sev-minor    { background: #d1ecf1; color: #0c5460; font-weight: bold; padding: 1px 4px; border-radius: 2px; font-size: 7.5pt; }
    .sev-functional { background: #e2e3e5; color: #383d41; font-weight: bold; padding: 1px 4px; border-radius: 2px; font-size: 7.5pt; }

    /* ── Run separator ─────────────────────────────── */
    .run-header-block {
        background: #1a3a5c;
        color: #fff;
        padding: 8px 12px;
        margin-bottom: 12px;
        border-radius: 3px;
    }
    .run-title { font-size: 12pt; font-weight: bold; }
    .run-meta  { font-size: 8pt; margin-top: 3px; opacity: 0.85; }
    .run-verdict {
        float: right;
        font-size: 10pt;
        font-weight: bold;
        padding: 3px 10px;
        border-radius: 3px;
        margin-top: 3px;
    }
    .verdict-pass        { background: #d4edda; color: #155724; }
    .verdict-fail        { background: #f8d7da; color: #721c24; }
    .verdict-conditional { background: #fff3cd; color: #856404; }
    .verdict-pending     { background: #e2e3e5; color: #383d41; }

    /* ── Cover / Summary ──────────────────────────── */
    .cover-block {
        border: 2px solid #1a3a5c;
        padding: 18px;
        margin-bottom: 18px;
        border-radius: 4px;
    }
    .cover-title {
        font-size: 18pt;
        font-weight: bold;
        color: #1a3a5c;
        margin-bottom: 4px;
    }
    .cover-divider {
        border: none;
        border-top: 2px solid #1a3a5c;
        margin: 12px 0;
    }

    /* ── Verdict box ──────────────────────────────── */
    .verdict-box {
        padding: 8px 14px;
        border-radius: 4px;
        font-size: 12pt;
        font-weight: bold;
        text-align: center;
        margin-top: 8px;
        display: inline-block;
    }

    .info-grid-2 { width: 100%; }
    .info-grid-2 td { width: 50%; vertical-align: top; padding-right: 8px; }
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- FIXED FOOTER (appears on every page)                                       --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="pdf-footer">
    <table>
        <tr>
            <td>TradeSyncERP &mdash; Confidential Inspection Report</td>
            <td style="text-align:center">{{ $inspection->report_number }}</td>
            <td style="text-align:right">Generated: {{ now()->format('d M Y H:i') }}</td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- REPORT HEADER                                                               --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="report-header">
    <table>
        <tr>
            <td style="vertical-align:top">
                <div class="report-title">Inspection Report</div>
                <div class="report-subtitle">{{ $inspection->inspectionType?->name ?? 'Quality Inspection' }}</div>
            </td>
            <td style="vertical-align:top; text-align:right">
                <div class="report-number">{{ $inspection->report_number }}</div>
                <div class="report-date">{{ $inspection->inspection_date?->format('d M Y') ?? now()->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- COVER BLOCK — Inspection summary                                            --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
@php
    $overallColor = match($inspection->overall_status) {
        'Pass'    => 'verdict-pass',
        'Fail'    => 'verdict-fail',
        default   => 'verdict-pending',
    };
@endphp

<div class="cover-block no-break">
    <div class="cover-title">{{ $inspection->inspectionType?->name ?? 'Inspection' }}</div>

    <hr class="cover-divider">

    <table class="info-grid-2">
        <tr>
            <td>
                <table class="meta-table">
                    <tr>
                        <td class="kv-key">Report Number</td>
                        <td class="kv-val">{{ $inspection->report_number }}</td>
                    </tr>
                    <tr>
                        <td class="kv-key">Inspection Type</td>
                        <td class="kv-val">{{ $inspection->inspectionType?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="kv-key">Inspection Date</td>
                        <td class="kv-val">{{ $inspection->inspection_date?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="kv-key">Overall Status</td>
                        <td class="kv-val"><strong>{{ $inspection->overall_status ?? 'Pending' }}</strong></td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="meta-table">
                    <tr>
                        <td class="kv-key">Total Runs</td>
                        <td class="kv-val">{{ $runs->count() }}</td>
                    </tr>
                    <tr>
                        <td class="kv-key">Inspectors</td>
                        <td class="kv-val">{{ $inspection->inspectors->pluck('employee_name')->implode(', ') ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="kv-key">Customer Orders</td>
                        <td class="kv-val">{{ $inspection->customerOrders->count() > 0 ? $inspection->customerOrders->count() . ' order(s)' : '—' }}</td>
                    </tr>
                    @if($inspection->remarks)
                    <tr>
                        <td class="kv-key">Remarks</td>
                        <td class="kv-val">{{ $inspection->remarks }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- Runs summary table --}}
    @if($runs->count() > 1)
    <div style="margin-top: 12px;">
        <strong style="font-size:9pt">Inspection Runs Summary</strong>
        <table class="check-table" style="margin-top:5px">
            <thead>
                <tr>
                    <th style="width:40px">Run #</th>
                    <th>Sample</th>
                    <th>Product</th>
                    <th style="width:80px; text-align:center">Verdict</th>
                    <th style="width:90px">Completed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($runs as $r)
                <tr>
                    <td style="text-align:center">{{ $r->run_number }}</td>
                    <td>{{ $r->sample?->sample_code ?? '—' }}</td>
                    <td>{{ $r->sample?->product_name ?? '—' }}</td>
                    <td style="text-align:center">
                        @php $vc = match($r->verdict) { 'Pass' => 'result-pass', 'Fail' => 'result-fail', default => 'result-na' }; @endphp
                        <span class="{{ $vc }}">{{ $r->verdict }}</span>
                    </td>
                    <td>{{ $r->completed_at?->format('d M Y') ?? 'In Progress' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- RUNS DETAIL                                                                 --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

@php
$hiddenSlugs = [
    'corrective_action', 'inspection_conclusion', 'finish_inspection',
    'textile_sample_conformity', 'denim_textile_defects',
    'cover_photo', 'workmanship_check',
];
@endphp

@foreach($runs as $runIndex => $run)

@if($runIndex > 0 || $runs->count() > 1)
<div class="page-break"></div>
@endif

{{-- Run header --}}
@php
    $vClass = match($run->verdict) {
        'Pass'        => 'verdict-pass',
        'Fail'        => 'verdict-fail',
        'Conditional' => 'verdict-conditional',
        default       => 'verdict-pending',
    };
@endphp

<div class="run-header-block">
    <span class="run-verdict {{ $vClass }}">{{ $run->verdict }}</span>
    <div class="run-title">Run #{{ $run->run_number }}</div>
    <div class="run-meta">
        @if($run->sample)
            Sample: {{ $run->sample->sample_code }}
            @if($run->sample->product_name) &mdash; {{ $run->sample->product_name }} @endif
            @if($run->sample->customer) &nbsp;|&nbsp; Customer: {{ $run->sample->customer->customer_name }} @endif
            @if($run->sample->category) &nbsp;|&nbsp; Category: {{ $run->sample->category->category_name }} @endif
        @endif
        @if($run->completed_at)
            &nbsp;|&nbsp; Completed: {{ $run->completed_at->format('d M Y H:i') }}
        @endif
    </div>
</div>

@if($run->remarks)
<div class="section-notes" style="margin-bottom:10px">
    <strong>Run Remarks:</strong> {{ $run->remarks }}
</div>
@endif

{{-- ── Sections ──────────────────────────────────────────────────────────── --}}
@php
    $visibleSections = $run->runSections->filter(
        fn($rs) => $rs->section && !in_array($rs->section->slug, $hiddenSlugs)
    )->values();
@endphp

@foreach($visibleSections as $rs)
@php
    $sec     = $rs->section;
    $secType = $sec->section_type;
    $slug    = $sec->slug;
    $data    = $rs->data ?? [];

    $statusLabel = match($rs->status) {
        'complete' => 'Complete',
        'na'       => 'N/A',
        default    => 'Pending',
    };
    $statusClass = match($rs->status) {
        'complete' => 'status-complete',
        'na'       => 'status-na',
        default    => 'status-pending',
    };

    $images = $rs->attachments->filter(fn($a) => $a->isImage())->values();
    $docs   = $rs->attachments->filter(fn($a) => !$a->isImage())->values();
@endphp

<div class="section-block no-break">
    <div class="section-header">
        <span class="section-status {{ $statusClass }}">{{ $statusLabel }}</span>
        <span class="section-title">{{ $sec->name }}</span>
    </div>
    <div class="section-body">

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- GENERAL INFO                                                      --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @if($secType === 'general_info')
        @php
            $genFields = [
                'Buyer / Client'       => $data['buyer_name'] ?? null,
                'Factory / Supplier'   => $data['factory_name'] ?? null,
                'PO / Order Number'    => $data['po_number'] ?? null,
                'Style / Article No.'  => $data['style_article_no'] ?? null,
                'Product Description'  => $data['product_description'] ?? null,
                'Order Quantity'       => $data['order_quantity'] ?? null,
                'Inspection Date'      => $data['inspection_date'] ?? null,
                'Inspector Name'       => $data['inspector_name'] ?? null,
                'Inspection Location'  => $data['inspection_location'] ?? null,
            ];
            $genFields = array_filter($genFields, fn($v) => $v !== null && $v !== '');
        @endphp
        @if(!empty($genFields))
        <table class="meta-table">
            @foreach($genFields as $label => $value)
            <tr>
                <td class="kv-key">{{ $label }}</td>
                <td class="kv-val">{{ $value }}</td>
            </tr>
            @endforeach
        </table>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- CHECKLIST (packing check, carton verification, packaging, etc.)  --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'checklist')
        @php
            $items = $data['items'] ?? [];
            // Extra top-level fields (carton verification totals, etc.) — skip arrays
            $extra = collect($data)->except('items')->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
        @endphp
        @if($extra->isNotEmpty())
        <table class="meta-table" style="margin-bottom:8px">
            @foreach($extra as $k => $v)
            <tr>
                <td class="kv-key">{{ ucwords(str_replace('_', ' ', $k)) }}</td>
                <td class="kv-val">{{ $v }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        @if(!empty($items))
        <table class="check-table">
            <thead>
                <tr>
                    <th style="width:40%">Checkpoint</th>
                    <th style="width:15%; text-align:center">Result</th>
                    <th>Remarks</th>
                    @if($images->isNotEmpty()) <th style="width:80px">Photos</th> @endif
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                @php
                    $resultClass = match(strtolower($item['result'] ?? '')) {
                        'pass'         => 'result-pass',
                        'fail'         => 'result-fail',
                        'n/a', 'na'    => 'result-na',
                        default        => '',
                    };
                    $resultLabel = $item['result'] ?? '—';
                    // Per-item images (task_key = item_{idx})
                    $itemKey = 'item_' . $idx;
                    $itemImgs = $images->filter(fn($a) => $a->task_key === $itemKey)->values();
                @endphp
                <tr>
                    <td>{{ $item['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $resultClass }}">{{ $resultLabel }}</span></td>
                    <td>{{ $item['remarks'] ?? '' }}</td>
                    @if($images->isNotEmpty())
                    <td>
                        @foreach($itemImgs->take(2) as $img)
                        @php $b64 = $imgBase64($img->file_path); @endphp
                        @if($b64)
                            <img src="{{ $b64 }}" style="width:40px;height:40px;object-fit:cover;border:1px solid #ccc;margin:1px">
                        @endif
                        @endforeach
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- DEFECT RECORDING                                                  --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'defects' || $slug === 'defect_recording')
        @php
            $selections = collect($data['selections'] ?? [])->filter(
                fn($s) => !empty($s['selected']) && !empty($s['defect_id'])
            )->values();
        @endphp
        @if($selections->isEmpty())
            <em style="color:#888;font-size:8pt">No defects recorded.</em>
        @else
        <table class="defect-table">
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Defect</th>
                    <th style="width:80px; text-align:center">Severity</th>
                    <th style="width:70px; text-align:center">Qty</th>
                    <th>Comment</th>
                    <th style="width:100px">Photos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($selections as $i => $sel)
                @php
                    $sev = $sel['severity'] ?? 'minor';
                    $sevClass = 'sev-' . $sev;
                    $defectImages = $images->filter(fn($a) => $a->task_key === 'defect_' . $sel['defect_id'])->values();
                @endphp
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td><strong>{{ $sel['defect_name'] ?? ('Defect #' . $sel['defect_id']) }}</strong></td>
                    <td style="text-align:center"><span class="{{ $sevClass }}">{{ ucfirst($sev) }}</span></td>
                    <td style="text-align:center">{{ $sel['quantity'] ?? 1 }}</td>
                    <td>{{ $sel['comment'] ?? '' }}</td>
                    <td>
                        @foreach($defectImages->take(3) as $img)
                        @php $b64 = $imgBase64($img->file_path); @endphp
                        @if($b64)
                            <img src="{{ $b64 }}" style="width:30px;height:30px;object-fit:cover;border:1px solid #ccc;margin:1px">
                        @endif
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- AQL SAMPLING                                                      --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'aql')
        @php $aql = $run->aql; @endphp
        @if($aql)
        <table class="meta-table" style="margin-bottom:8px">
            <tr>
                <td class="kv-key">Lot Size</td>
                <td class="kv-val">{{ number_format($aql->lot_size ?? 0) }}</td>
                <td class="kv-key">Inspection Level</td>
                <td class="kv-val">{{ $aql->inspection_level ?? '—' }}</td>
            </tr>
            <tr>
                <td class="kv-key">Code Letter</td>
                <td class="kv-val">{{ $aql->code_letter ?? '—' }}</td>
                <td class="kv-key">Sample Size</td>
                <td class="kv-val">{{ $aql->sample_size ?? '—' }}</td>
            </tr>
        </table>
        <table class="aql-table">
            <thead>
                <tr>
                    <th>Category</th>
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
                    $aqlLevel  = $aql->{"aql_{$key}"};
                    $ac        = $aql->{"ac_{$key}"};
                    $re        = $aql->{"re_{$key}"};
                    $found     = $aql->{"found_{$key}"} ?? 0;
                    $rowResult = ($ac !== null && $found > $ac) ? 'FAIL' : (($found > 0 || $ac !== null) ? 'PASS' : '—');
                    $rowClass  = $rowResult === 'FAIL' ? 'result-fail' : ($rowResult === 'PASS' ? 'result-pass' : '');
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ $aqlLevel !== null ? $aqlLevel . '%' : '—' }}</td>
                    <td>{{ $ac ?? '—' }}</td>
                    <td>{{ $re ?? '—' }}</td>
                    <td>{{ $found }}</td>
                    <td><span class="{{ $rowClass }}">{{ $rowResult }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aql-verdict-{{ strtolower($aql->verdict ?? 'pending') }}" style="margin-top:6px">
            AQL Verdict: {{ $aql->verdict ?? 'Pending' }}
        </div>
        @if($aql->notes)
            <div class="section-notes" style="margin-top:6px">{{ $aql->notes }}</div>
        @endif
        @else
            <em style="color:#888;font-size:8pt">No AQL data recorded.</em>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- IMAGES / PRODUCT SCREENING                                        --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'images')
        @if($images->isEmpty())
            <em style="color:#888;font-size:8pt">No images uploaded.</em>
        @else
        @php
            $imgChunks = $images->chunk(4);
        @endphp
        @foreach($imgChunks as $chunk)
        <table class="img-gallery" style="margin-bottom:4px">
            <tr>
                @foreach($chunk as $img)
                @php $b64 = $imgBase64($img->file_path); @endphp
                <td style="width:25%">
                    @if($b64)
                        <img src="{{ $b64 }}" class="img-thumb">
                        <div class="img-label">{{ $img->title ?: $img->file_name }}</div>
                    @else
                        <div style="width:85px;height:85px;border:1px dashed #ccc;text-align:center;line-height:85px;color:#999;font-size:7pt">No image</div>
                    @endif
                </td>
                @endforeach
                @for($p = $chunk->count(); $p < 4; $p++)<td></td>@endfor
            </tr>
        </table>
        @endforeach
        @endif
        @if(!empty($data['notes']))
            <div class="section-notes">{{ $data['notes'] }}</div>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- CONTAINER DETAILS                                                 --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'container')
        @php
            $containerFields = [
                'Container Number'   => $data['container_number'] ?? null,
                'Container Type'     => $data['container_type'] ?? null,
                'Seal Number'        => $data['seal_number'] ?? null,
                'Loading Date'       => $data['loading_date'] ?? null,
                'Loading Port'       => $data['loading_port'] ?? null,
                'Discharge Port'     => $data['discharge_port'] ?? null,
                'Total Cartons'      => $data['total_cartons_loaded'] ?? null,
                'Total Quantity'     => $data['total_qty_loaded'] ?? null,
                'Container Condition'=> $data['container_condition'] ?? null,
            ];
            $containerFields = array_filter($containerFields, fn($v) => $v !== null && $v !== '');
        @endphp
        <table class="meta-table">
            @foreach($containerFields as $label => $value)
            <tr>
                <td class="kv-key">{{ $label }}</td>
                <td class="kv-val">{{ $value }}</td>
            </tr>
            @endforeach
        </table>
        @if($images->isNotEmpty())
        <div style="margin-top:8px">
            <strong style="font-size:8pt">Container Photos</strong>
            <table class="img-gallery" style="margin-top:4px">
                <tr>
                    @foreach($images->take(4) as $img)
                    @php $b64 = $imgBase64($img->file_path); @endphp
                    <td style="width:25%">
                        @if($b64)
                            <img src="{{ $b64 }}" class="img-thumb">
                        @endif
                    </td>
                    @endforeach
                    @for($p = min($images->count(), 4); $p < 4; $p++)<td></td>@endfor
                </tr>
            </table>
        </div>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- FINAL REVIEW / CONCLUSION                                         --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'review' || $secType === 'conclusion' || $slug === 'final_review')
        @php
            $reviewFields = [
                'Overall QC Verdict' => $data['overall_verdict'] ?? null,
                'Inspector Name'     => $data['inspector_name'] ?? null,
                'Follow-up Date'     => $data['follow_up_date'] ?? null,
                'Notes / Remarks'    => $data['notes'] ?? null,
                'Conclusion'         => $data['conclusion'] ?? null,
                'Summary'            => $data['summary'] ?? null,
            ];
            $reviewFields = array_filter($reviewFields, fn($v) => $v !== null && $v !== '');
        @endphp
        @if(!empty($reviewFields))
        <table class="meta-table">
            @foreach($reviewFields as $label => $value)
            <tr>
                <td class="kv-key">{{ $label }}</td>
                <td class="kv-val">
                    @if($label === 'Overall QC Verdict')
                        @php
                            $vc = match($value) {
                                'Pass'                   => 'verdict-pass',
                                'Fail'                   => 'verdict-fail',
                                'Conditional Pass'       => 'verdict-conditional',
                                'Re-Inspection Required' => 'verdict-conditional',
                                default                  => 'verdict-pending',
                            };
                        @endphp
                        <span class="verdict-box {{ $vc }}" style="font-size:9pt;padding:2px 8px">{{ $value }}</span>
                    @else
                        {{ $value }}
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- TASK LIST                                                         --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'task_list')
        @php
            $taskDefs  = $sec->default_data['tasks'] ?? [];
            $taskData  = $data['tasks'] ?? [];
        @endphp
        @if(!empty($taskDefs))
        <table class="check-table">
            <thead>
                <tr>
                    <th style="width:35%">Task</th>
                    <th style="width:15%; text-align:center">Result</th>
                    <th>Notes</th>
                    <th style="width:80px">Photos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($taskDefs as $taskDef)
                @php
                    $tKey    = $taskDef['key'] ?? '';
                    $tEntry  = $taskData[$tKey] ?? [];
                    $tResult = $tEntry['selected'] ?? '—';
                    $tNotes  = $tEntry['notes'] ?? ($tEntry['comments'] ?? '');
                    $tImgs   = $images->filter(fn($a) => $a->task_key === $tKey)->values();
                    $resultClass = match(strtolower((string)$tResult)) {
                        'pass' => 'result-pass', 'fail' => 'result-fail', 'n/a', 'na' => 'result-na', default => '',
                    };
                @endphp
                <tr>
                    <td>{{ $taskDef['label'] ?? $tKey }}</td>
                    <td style="text-align:center"><span class="{{ $resultClass }}">{{ $tResult ?: '—' }}</span></td>
                    <td>{{ $tNotes }}</td>
                    <td>
                        @foreach($tImgs->take(2) as $img)
                        @php $b64 = $imgBase64($img->file_path); @endphp
                        @if($b64)
                            <img src="{{ $b64 }}" style="width:35px;height:35px;object-fit:cover;border:1px solid #ccc;margin:1px">
                        @endif
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- CARTON DIMENSIONS & WEIGHT                                        --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'cartons' || $slug === 'carton_dimensions_weight')
        @php
            $cartonFields = [
                'Length (cm)'        => $data['length'] ?? null,
                'Width (cm)'         => $data['width'] ?? null,
                'Height (cm)'        => $data['height'] ?? null,
                'Gross Weight (kg)'  => $data['gross_weight'] ?? null,
                'Net Weight (kg)'    => $data['net_weight'] ?? null,
                'CBM'                => $data['cbm'] ?? null,
                'Cartons per Pallet' => $data['cartons_per_pallet'] ?? null,
            ];
            $cartonFields = array_filter($cartonFields, fn($v) => $v !== null && $v !== '');
            $cartonItems  = $data['items'] ?? [];
        @endphp
        @if(!empty($cartonFields))
        <table class="meta-table" style="margin-bottom:8px">
            @foreach($cartonFields as $label => $value)
            <tr>
                <td class="kv-key">{{ $label }}</td>
                <td class="kv-val">{{ $value }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        @if(!empty($cartonItems))
        <table class="check-table">
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
                @foreach($cartonItems as $ci)
                @php
                    $ciRes   = $ci['result'] ?? '—';
                    $ciClass = match(strtolower((string)$ciRes)) {
                        'pass' => 'result-pass', 'fail' => 'result-fail', default => ''
                    };
                @endphp
                <tr>
                    <td>{{ $ci['carton_no'] ?? ($loop->index + 1) }}</td>
                    <td style="text-align:center">{{ $ci['length'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $ci['width'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $ci['height'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $ci['gross_weight'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $ci['net_weight'] ?? '—' }}</td>
                    <td style="text-align:center"><span class="{{ $ciClass }}">{{ $ciRes }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- VERIFICATION sections                                             --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'verification')
        @php
            $verItems = $data['items'] ?? [];
            $verExtra = collect($data)->except('items')->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
        @endphp
        @if($verExtra->isNotEmpty())
        <table class="meta-table" style="margin-bottom:8px">
            @foreach($verExtra as $k => $v)
            <tr>
                <td class="kv-key">{{ ucwords(str_replace('_', ' ', $k)) }}</td>
                <td class="kv-val">{{ $v }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        @if(!empty($verItems))
        <table class="check-table">
            <thead>
                <tr>
                    <th style="width:50%">Item</th>
                    <th style="width:20%; text-align:center">Result</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($verItems as $vi)
                @php
                    $viRes   = $vi['result'] ?? '—';
                    $viClass = match(strtolower((string)$viRes)) {
                        'pass' => 'result-pass', 'fail' => 'result-fail', 'n/a', 'na' => 'result-na', default => ''
                    };
                @endphp
                <tr>
                    <td>{{ $vi['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $viClass }}">{{ $viRes }}</span></td>
                    <td>{{ $vi['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- ARTICLE RESULTS                                                   --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($secType === 'article_results')
        @php
            $artItems = $data['items'] ?? [];
            $artExtra = collect($data)->except('items')->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
        @endphp
        @if($artExtra->isNotEmpty())
        <table class="meta-table" style="margin-bottom:8px">
            @foreach($artExtra as $k => $v)
            <tr>
                <td class="kv-key">{{ ucwords(str_replace('_', ' ', $k)) }}</td>
                <td class="kv-val">{{ $v }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        @if(!empty($artItems))
        <table class="check-table">
            <thead>
                <tr>
                    <th style="width:40%">Parameter</th>
                    <th style="width:20%; text-align:center">Result</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($artItems as $ai)
                @php
                    $aiRes   = $ai['result'] ?? '—';
                    $aiClass = match(strtolower((string)$aiRes)) {
                        'pass' => 'result-pass', 'fail' => 'result-fail', 'n/a', 'na' => 'result-na', default => ''
                    };
                @endphp
                <tr>
                    <td>{{ $ai['label'] ?? $ai['parameter'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $aiClass }}">{{ $aiRes }}</span></td>
                    <td>{{ $ai['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- MEASUREMENT CHECK                                                 --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @elseif($slug === 'measurement_check')
        @php $measurements = $data['measurements'] ?? []; @endphp
        @if(!empty($measurements))
        <table class="check-table">
            <thead>
                <tr>
                    <th style="width:35%">Measurement Point</th>
                    <th style="width:15%; text-align:center">Spec</th>
                    <th style="width:15%; text-align:center">Tolerance</th>
                    <th style="width:15%; text-align:center">Measured</th>
                    <th style="width:10%; text-align:center">Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($measurements as $m)
                @php
                    $mRes   = $m['result'] ?? '—';
                    $mClass = match(strtolower((string)$mRes)) { 'pass' => 'result-pass', 'fail' => 'result-fail', default => '' };
                @endphp
                <tr>
                    <td>{{ $m['point'] ?? '' }}</td>
                    <td style="text-align:center">{{ $m['spec'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $m['tolerance'] ?? '—' }}</td>
                    <td style="text-align:center">{{ $m['measured'] ?? '—' }}</td>
                    <td style="text-align:center"><span class="{{ $mClass }}">{{ $mRes }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @if(!empty($data['notes']))
            <div class="section-notes" style="margin-top:6px">{{ $data['notes'] }}</div>
        @endif

        {{-- ════════════════════════════════════════════════════════════════ --}}
        {{-- FINISH / GENERIC KEY-VALUE FALLBACK                              --}}
        {{-- ════════════════════════════════════════════════════════════════ --}}
        @else
        @php
            $fallbackData = collect($data)->except('items')->filter(fn($v) => $v !== null && $v !== '' && !is_array($v));
            $fallbackItems = $data['items'] ?? [];
        @endphp
        @if($fallbackData->isNotEmpty())
        <table class="meta-table" style="margin-bottom:8px">
            @foreach($fallbackData as $k => $v)
            <tr>
                <td class="kv-key">{{ ucwords(str_replace('_', ' ', $k)) }}</td>
                <td class="kv-val">{{ $v }}</td>
            </tr>
            @endforeach
        </table>
        @endif
        @if(!empty($fallbackItems))
        <table class="check-table">
            <thead>
                <tr>
                    <th style="width:50%">Item</th>
                    <th style="width:20%; text-align:center">Result</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fallbackItems as $fi)
                @php
                    $fiRes   = $fi['result'] ?? '—';
                    $fiClass = match(strtolower((string)$fiRes)) {
                        'pass' => 'result-pass', 'fail' => 'result-fail', 'n/a', 'na' => 'result-na', default => ''
                    };
                @endphp
                <tr>
                    <td>{{ $fi['label'] ?? '' }}</td>
                    <td style="text-align:center"><span class="{{ $fiClass }}">{{ $fiRes }}</span></td>
                    <td>{{ $fi['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @if($images->isEmpty() && $fallbackData->isEmpty() && empty($fallbackItems))
            <em style="color:#888;font-size:8pt">No data recorded for this section.</em>
        @endif
        @endif

        {{-- ── General images gallery (non-task images at section level) ─── --}}
        @php
            $generalImages = $images->filter(fn($a) => empty($a->task_key))->values();
        @endphp
        @if($generalImages->isNotEmpty() && !in_array($secType, ['images']))
        <div style="margin-top:8px">
            <strong style="font-size:8pt">Attached Photos</strong>
            <table style="width:100%;margin-top:4px">
                <tr>
                    @foreach($generalImages->take(4) as $img)
                    @php $b64 = $imgBase64($img->file_path); @endphp
                    <td style="width:25%;padding:2px;text-align:center;vertical-align:top">
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
                <div style="font-size:7.5pt;color:#666;margin-top:3px">+ {{ $generalImages->count() - 4 }} more photo(s)</div>
            @endif
        </div>
        @endif

        {{-- ── Documents ──────────────────────────────────────────────────── --}}
        @if($docs->isNotEmpty())
        <div style="margin-top:6px">
            <strong style="font-size:8pt">Attached Documents</strong>
            <table class="meta-table" style="margin-top:3px">
                @foreach($docs as $doc)
                <tr>
                    <td class="kv-key">{{ $doc->title ?: $doc->file_name }}</td>
                    <td class="kv-val" style="color:#666">{{ $doc->file_name }} ({{ $doc->humanFileSize() }})</td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif

        {{-- ── Section notes ───────────────────────────────────────────────── --}}
        @if($rs->notes)
        <div class="section-notes" style="margin-top:6px">
            <strong>Notes:</strong> {{ $rs->notes }}
        </div>
        @endif

    </div>{{-- end .section-body --}}
</div>{{-- end .section-block --}}

@endforeach {{-- end sections loop --}}

@endforeach {{-- end runs loop --}}

</body>
</html>
