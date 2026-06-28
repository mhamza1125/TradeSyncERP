<!DOCTYPE html>
<html lang="en">
<head>
<title>Samples</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Samples'])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Samples'])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Sample Register</div>
                <div class="db-sub">{{ $samples->count() }} sample{{ $samples->count() !== 1 ? 's' : '' }} listed</div>
            </td>
            <td class="db-right">
                <div class="db-date">{{ now()->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px">#</th>
            <th style="width:100px">Sample Code</th>
            <th>Product Name</th>
            <th style="width:120px">Customer</th>
            <th style="width:100px">Category</th>
            <th style="width:90px">Received</th>
            <th style="width:60px">Priority</th>
            <th style="width:70px">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($samples as $i => $sample)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $sample->sample_code }}</td>
            <td>{{ $sample->product_name ?? '—' }}</td>
            <td>{{ $sample->customer?->customer_name ?? '—' }}</td>
            <td class="text-muted">{{ $sample->category?->category_name ?? '—' }}</td>
            <td>{{ $sample->receive_date->format('d M Y') }}</td>
            <td class="text-center">
                @php
                    $pc = match($sample->priority_level) {
                        'Urgent' => 'danger', 'High' => 'warning',
                        'Medium' => 'info', default => 'secondary',
                    };
                @endphp
                <span class="badge badge-{{ $pc }}">{{ $sample->priority_level }}</span>
            </td>
            <td class="text-center">
                @php
                    $sc = match($sample->status) {
                        'Completed' => 'success', 'In Testing' => 'info',
                        'Returned' => 'secondary', default => 'warning',
                    };
                @endphp
                <span class="badge badge-{{ $sc }}">{{ $sample->status }}</span>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="no-data">No samples found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $samples->count() }} records
</div>

</body>
</html>
