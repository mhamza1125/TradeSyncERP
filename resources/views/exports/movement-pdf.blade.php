<!DOCTYPE html>
<html lang="en">
<head>
<title>Movement #{{ $movement->id }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

@php
    $sc = match($movement->status) { 'Returned' => 'success', 'Overdue' => 'danger', default => 'primary' };
    $totalSamples = $movement->items->pluck('sample_id')->unique()->count();
    $totalQty     = $movement->items->sum('quantity');
    $returnedCount = $movement->items->filter(fn ($i) => $i->effectiveStatus() === 'Returned')->count();
    $overdueCount  = $movement->items->filter(fn ($i) => $i->effectiveStatus() === 'Overdue')->count();
    $issuedCount   = $movement->items->count() - $returnedCount - $overdueCount;
@endphp

{{-- Document banner --}}
<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Sample Movement</div>
                <div class="db-sub">{{ $totalSamples }} sample{{ $totalSamples !== 1 ? 's' : '' }} &middot; {{ $movement->employees->pluck('employee_name')->implode(', ') ?: 'No assignees' }}</div>
            </td>
            <td class="db-right">
                <div class="db-code">MVT-{{ $movement->id }}</div>
                <div class="db-date">{{ \Carbon\Carbon::parse($movement->issue_date)->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Quick summary --}}
<div class="summary-box" style="margin-top:0; margin-bottom:16px;">
    <table>
        <tr>
            <td style="width:25%;">
                <span class="kv-label" style="display:block; text-transform:uppercase; color:#8a97a6; font-size:6.5pt; letter-spacing:0.3px;">Samples</span>
                <span style="font-size:12pt; font-weight:bold; color:#1a3560;">{{ $totalSamples }}</span>
            </td>
            <td style="width:25%;">
                <span class="kv-label" style="display:block; text-transform:uppercase; color:#8a97a6; font-size:6.5pt; letter-spacing:0.3px;">Total Quantity</span>
                <span style="font-size:12pt; font-weight:bold; color:#1a3560;">{{ number_format($totalQty) }}</span>
            </td>
            <td style="width:25%;">
                <span class="kv-label" style="display:block; text-transform:uppercase; color:#8a97a6; font-size:6.5pt; letter-spacing:0.3px;">Overall Status</span>
                <span class="badge badge-{{ $sc }}" style="font-size:8.5pt;">{{ $movement->status }}</span>
            </td>
            <td style="width:25%;">
                <span class="kv-label" style="display:block; text-transform:uppercase; color:#8a97a6; font-size:6.5pt; letter-spacing:0.3px;">Item Breakdown</span>
                <span style="font-size:8pt; color:#424242;">
                    {{ $issuedCount }} Issued &middot; {{ $returnedCount }} Returned{{ $overdueCount ? ' · '.$overdueCount.' Overdue' : '' }}
                </span>
            </td>
        </tr>
    </table>
</div>

{{-- Movement details --}}
<table class="two-col" style="margin-bottom:16px;">
    <tr>
        <td>
            <div class="info-section">
                <h3>Movement Details</h3>
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Issue Date</td>
                        <td class="info-value">{{ \Carbon\Carbon::parse($movement->issue_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Expected Return</td>
                        <td class="info-value">{{ $movement->expected_return_date ? \Carbon\Carbon::parse($movement->expected_return_date)->format('d M Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Actual Return</td>
                        <td class="info-value">{{ $movement->actual_return_date ? \Carbon\Carbon::parse($movement->actual_return_date)->format('d M Y') : '—' }}</td>
                    </tr>
                    @if($movement->alert_days)
                    <tr>
                        <td class="info-label">Alert Days</td>
                        <td class="info-value">{{ $movement->alert_days }} days</td>
                    </tr>
                    @endif
                    @if($movement->order_number)
                    <tr>
                        <td class="info-label">Order Number</td>
                        <td class="info-value">{{ $movement->order_number }}</td>
                    </tr>
                    @endif
                    @if($movement->inspectionType)
                    <tr>
                        <td class="info-label">Inspection Type</td>
                        <td class="info-value">{{ $movement->inspectionType->name }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="info-label">Status</td>
                        <td class="info-value">
                            <span class="badge badge-{{ $sc }}">{{ $movement->status }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
        <td>
            <div class="info-section">
                <h3>Assigned To</h3>
                @forelse($movement->employees as $emp)
                <span class="badge badge-secondary" style="margin:0 4px 4px 0;">{{ $emp->employee_name }}</span>
                @empty
                <div class="text-muted" style="font-size:8.5pt;">No assignees recorded.</div>
                @endforelse

                <h3 style="margin-top:12px;">Recipient</h3>
                @if($movement->recipient_type && $movement->recipient_name)
                <div style="font-size:8.5pt;">
                    <span class="badge badge-info">{{ $movement->recipient_type }}</span>
                    <span style="font-weight:bold;">{{ $movement->recipient_name }}</span>
                </div>
                @else
                <div class="text-muted" style="font-size:8.5pt;">Not specified.</div>
                @endif

                <h3 style="margin-top:12px;">Linked Inspection Run</h3>
                @if($movement->inspectionRun)
                <div style="font-size:8.5pt; font-weight:bold; color:#212121;">{{ $movement->inspectionRun?->inspection?->report_number ?? 'Run #'.$movement->inspection_run_id }}</div>
                <div class="text-muted" style="font-size:7.5pt;">Run #{{ $movement->inspectionRun->run_number ?? $movement->inspectionRun->id }}</div>
                @else
                <div class="text-muted" style="font-size:8.5pt;">Not linked to an inspection run.</div>
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- Items --}}
<div class="info-section">
    <h3>Sample Items</h3>
    <table class="data-table data-table-fixed">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:12%">Sample Code</th>
                <th style="width:22%">Product Name</th>
                <th style="width:16%">Customer</th>
                <th style="width:13%">Variation</th>
                <th class="text-right" style="width:6%">Qty</th>
                <th style="width:13%">Status</th>
                <th style="width:14%">Return Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movement->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="fw-bold">{{ $item->sample?->sample_code ?? 'Removed' }}</td>
                <td>{{ Illuminate\Support\Str::limit($item->sample?->product_name ?? '—', 22) }}</td>
                <td class="text-muted">{{ Illuminate\Support\Str::limit($item->sample?->customer?->display_name ?? '—', 18) }}</td>
                <td class="text-muted">
                    {{ collect([$item->variation?->color?->name, $item->variation?->size?->name])->filter()->implode(' / ') ?: '—' }}
                </td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-center">
                    @php $is = $item->effectiveStatus(); $isc = match($is) { 'Returned'=>'success','Overdue'=>'danger', default=>'primary' }; @endphp
                    <span class="badge badge-{{ $isc }}">{{ $is }}</span>
                </td>
                <td>{{ $item->actual_return_date ? \Carbon\Carbon::parse($item->actual_return_date)->format('d M Y') : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" class="no-data">No items recorded.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($movement->remarks)
<div class="info-section" style="margin-top:14px;">
    <h3>Remarks</h3>
    <p style="font-size:8.5pt; color:#424242;">{{ $movement->remarks }}</p>
</div>
@endif

</body>
</html>
