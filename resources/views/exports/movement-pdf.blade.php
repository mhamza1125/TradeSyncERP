<!DOCTYPE html>
<html lang="en">
<head>
<title>Movement #{{ $movement->id }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Sample Movement</div>
                <div class="db-sub">{{ $movement->employees->pluck('employee_name')->implode(', ') ?: 'No assignees' }}</div>
            </td>
            <td class="db-right">
                <div class="db-code">MVT-{{ $movement->id }}</div>
                <div class="db-date">{{ \Carbon\Carbon::parse($movement->issue_date)->format('d M Y') }}</div>
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
                    @if($movement->actual_return_date)
                    <tr>
                        <td class="info-label">Actual Return</td>
                        <td class="info-value">{{ \Carbon\Carbon::parse($movement->actual_return_date)->format('d M Y') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="info-label">Status</td>
                        <td class="info-value">
                            @php $sc = match($movement->status) { 'Returned'=>'success','Overdue'=>'danger', default=>'primary' }; @endphp
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
                <div style="font-size:8.5pt; font-weight:bold; margin-bottom:2px;">{{ $emp->employee_name }}</div>
                @empty
                <div class="text-muted" style="font-size:8.5pt;">No assignees recorded.</div>
                @endforelse
                @if($movement->inspectionRun)
                <div style="margin-top:8px;">
                    <div style="font-size:7.5pt; text-transform:uppercase; color:#757575; margin-bottom:2px;">Inspection Run</div>
                    <div style="font-size:8.5pt;">{{ $movement->inspectionRun?->inspection?->report_number ?? 'Run #'.$movement->inspection_run_id }}</div>
                </div>
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- Items --}}
<div class="info-section">
    <h3>Sample Items</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th style="width:100px">Sample Code</th>
                <th>Product Name</th>
                <th style="width:90px">Customer</th>
                <th style="width:70px">Color</th>
                <th style="width:60px">Size</th>
                <th class="text-right" style="width:55px">Qty</th>
                <th style="width:90px">Status</th>
                <th style="width:90px">Return Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movement->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="fw-bold">{{ $item->sample->sample_code }}</td>
                <td>{{ $item->sample->product_name ?? '—' }}</td>
                <td class="text-muted">{{ $item->sample->customer?->customer_name ?? '—' }}</td>
                <td class="text-muted">{{ $item->variation?->color?->name ?? '—' }}</td>
                <td class="text-muted">{{ $item->variation?->size?->name ?? '—' }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-center">
                    @php $is = $item->effectiveStatus(); $isc = match($is) { 'Returned'=>'success','Overdue'=>'danger', default=>'primary' }; @endphp
                    <span class="badge badge-{{ $isc }}">{{ $is }}</span>
                </td>
                <td>{{ $item->actual_return_date ? \Carbon\Carbon::parse($item->actual_return_date)->format('d M Y') : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="no-data">No items recorded.</td></tr>
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
