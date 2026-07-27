<!DOCTYPE html>
<html lang="en">
<head>
<title>Samples</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

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

<table class="data-table data-table-fixed">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:13%">Sample Code</th>
            <th style="width:24%">Product Name</th>
            <th style="width:19%">Customer</th>
            <th style="width:15%">Category</th>
            <th style="width:13%">Received</th>
            <th style="width:12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($samples as $i => $sample)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $sample->sample_code }}</td>
            <td>{{ Illuminate\Support\Str::limit($sample->product_name ?? '—', 22) }}</td>
            <td>{{ Illuminate\Support\Str::limit($sample->customer?->customer_name ?? '—', 18) }}</td>
            <td class="text-muted">{{ Illuminate\Support\Str::limit($sample->category?->category_name ?? '—', 14) }}</td>
            <td>{{ $sample->receive_date->format('d M Y') }}</td>
            <td class="text-center">
                @php
                    $cs = ($sample->open_movements_count ?? 0) > 0 ? 'In Testing' : 'Received';
                    $sc = $cs === 'In Testing' ? 'warning' : 'primary';
                @endphp
                <span class="badge badge-{{ $sc }}">{{ $cs }}</span>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="no-data">No samples found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $samples->count() }} records
</div>

</body>
</html>
