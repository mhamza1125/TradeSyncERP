<!DOCTYPE html>
<html lang="en">
<head>
<title>AQL Calculation — {{ $aqlCalculation->title }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">AQL Sampling Plan</div>
                <div class="db-sub">{{ $aqlCalculation->title }}</div>
            </td>
            <td class="db-right">
                <div class="db-code">#{{ $aqlCalculation->id }}</div>
                <div class="db-date">{{ $aqlCalculation->created_at->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="two-col">
    <tr>
        <td>
            <div class="info-section">
                <h3>Lot Details</h3>
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Lot Size</td>
                        <td class="info-value">{{ number_format($aqlCalculation->lot_size) }} units</td>
                    </tr>
                    <tr>
                        <td class="info-label">Inspection Level</td>
                        <td class="info-value">{{ $aqlCalculation->inspection_level }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Code Letter</td>
                        <td class="info-value">{{ $aqlCalculation->code_letter ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Base Sample (n)</td>
                        <td class="info-value">{{ $aqlCalculation->sample_size ?? '—' }}</td>
                    </tr>
                </table>
            </div>
        </td>
        <td>
            <div class="info-section">
                <h3>Verdict</h3>
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Overall Verdict</td>
                        <td class="info-value">
                            <span class="badge {{ $aqlCalculation->verdict === 'Pass' ? 'badge-success' : ($aqlCalculation->verdict === 'Fail' ? 'badge-danger' : 'badge-secondary') }}">
                                {{ $aqlCalculation->verdict }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Saved On</td>
                        <td class="info-value">{{ $aqlCalculation->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>

<div class="info-section">
    <h3>Sampling Plan</h3>
    <table class="data-table">
        <thead>
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
            @foreach([
                ['label' => 'Critical', 'badge' => 'danger', 'aql' => $aqlCalculation->aql_critical, 'ac' => $aqlCalculation->ac_critical, 're' => $aqlCalculation->re_critical, 'found' => $aqlCalculation->found_critical],
                ['label' => 'Major', 'badge' => 'warning', 'aql' => $aqlCalculation->aql_major, 'ac' => $aqlCalculation->ac_major, 're' => $aqlCalculation->re_major, 'found' => $aqlCalculation->found_major],
                ['label' => 'Minor', 'badge' => 'info', 'aql' => $aqlCalculation->aql_minor, 'ac' => $aqlCalculation->ac_minor, 're' => $aqlCalculation->re_minor, 'found' => $aqlCalculation->found_minor],
            ] as $row)
            <tr>
                <td><span class="badge badge-{{ $row['badge'] }}">{{ $row['label'] }}</span></td>
                <td class="text-center">{{ $row['aql'] === 'not_allowed' ? 'Not Allowed' : ($row['aql'] ?? '—') }}</td>
                <td class="text-center fw-bold">{{ $aqlCalculation->sample_size ?? '—' }}</td>
                <td class="text-center">{{ $row['ac'] ?? '—' }}</td>
                <td class="text-center">{{ $row['re'] ?? '—' }}</td>
                <td class="text-center">{{ $row['found'] }}</td>
                <td class="text-center">
                    @php
                        $status = 'Pending'; $badgeCls = 'badge-secondary';
                        if ($row['found'] > 0 && $row['ac'] !== null) {
                            if ($row['found'] > $row['ac']) { $status = 'Fail'; $badgeCls = 'badge-danger'; }
                            else { $status = 'Pass'; $badgeCls = 'badge-success'; }
                        }
                    @endphp
                    <span class="badge {{ $badgeCls }}">{{ $status }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($aqlCalculation->variations)
@php
    $variationTotalQty = collect($aqlCalculation->variations)->sum('qty');
    $variationTotalInspect = collect($aqlCalculation->variations)->sum('inspect_qty');
@endphp
<div class="info-section">
    <h3>Variations — Distributed across Base n = {{ $aqlCalculation->sample_size ?? '—' }}</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Color</th>
                <th>Size</th>
                <th class="text-center">Ordered Qty</th>
                <th class="text-center">Share %</th>
                <th class="text-center">Inspect Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aqlCalculation->variations as $row)
            <tr>
                <td>{{ $row['color'] ?: '—' }}</td>
                <td>{{ $row['size'] ?: '—' }}</td>
                <td class="text-center">{{ number_format($row['qty'] ?? 0) }}</td>
                <td class="text-center text-muted">{{ $variationTotalQty > 0 ? number_format((($row['qty'] ?? 0) / $variationTotalQty) * 100, 1).'%' : '—' }}</td>
                <td class="text-center fw-bold">{{ $row['inspect_qty'] ?? 0 }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="text-align:right; color:#888; font-size:7.5pt;">Total</td>
                <td class="text-center">{{ number_format($variationTotalQty) }}</td>
                <td class="text-center">100%</td>
                <td class="text-center">{{ number_format($variationTotalInspect) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@if($aqlCalculation->notes)
<div class="info-section">
    <h3>Notes</h3>
    <p style="font-size:8.5pt; color:#424242; padding:8px; background:#f8f9fa; border-radius:3px;">{{ $aqlCalculation->notes }}</p>
</div>
@endif

</body>
</html>
