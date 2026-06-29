<!DOCTYPE html>
<html lang="en">
<head>
<title>Sample — {{ $sample->sample_code }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Sample Report</div>
                <div class="db-sub">{{ $sample->product_name ?? $sample->sample_code }}</div>
            </td>
            <td class="db-right">
                <div class="db-code">{{ $sample->sample_code }}</div>
                <div class="db-date">Received: {{ $sample->receive_date->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="two-col">
    <tr>
        <td>
            <div class="info-section">
                <h3>Sample Details</h3>
                <table class="info-grid">
                    <tr><td class="info-label">Sample Code</td><td class="info-value">{{ $sample->sample_code }}</td></tr>
                    <tr><td class="info-label">Product Name</td><td class="info-value">{{ $sample->product_name ?? '—' }}</td></tr>
                    <tr><td class="info-label">Article</td><td class="info-value">{{ $sample->article ?? '—' }}</td></tr>
                    <tr><td class="info-label">Reference</td><td class="info-value">{{ $sample->sample_reference ?? '—' }}</td></tr>
                    <tr><td class="info-label">Category</td><td class="info-value">{{ $sample->category->category_name ?? '—' }}</td></tr>
                    <tr>
                        <td class="info-label">Status</td>
                        <td class="info-value">
                            @php
                                $cs = $sample->computedStatus();
                                $sc = $cs === 'In Testing' ? 'warning' : 'primary';
                            @endphp
                            <span class="badge badge-{{ $sc }}">{{ $cs }}</span>
                        </td>
                    </tr>
                    <tr><td class="info-label">Receive Date</td><td class="info-value">{{ $sample->receive_date->format('d M Y') }}</td></tr>
                    <tr><td class="info-label">Alert Days</td><td class="info-value">{{ $sample->alert_days ?? '—' }} days</td></tr>
                </table>
            </div>
        </td>
        <td>
            <div class="info-section">
                <h3>Parties</h3>
                <table class="info-grid">
                    <tr><td class="info-label">Customer</td><td class="info-value">{{ $sample->customer->customer_name ?? '—' }}</td></tr>
                    <tr><td class="info-label">Supplier</td><td class="info-value">{{ $sample->supplier->name ?? '—' }}</td></tr>
                    <tr><td class="info-label">Source</td><td class="info-value">{{ $sample->source ?? '—' }}</td></tr>
                </table>
            </div>
            <div class="info-section">
                <h3>Storage</h3>
                <table class="info-grid">
                    <tr><td class="info-label">Location</td><td class="info-value">{{ $sample->physical_location ?? '—' }}</td></tr>
                    <tr><td class="info-label">Rack</td><td class="info-value">{{ $sample->rack ?? '—' }}</td></tr>
                    <tr><td class="info-label">Position</td><td class="info-value">{{ $sample->position ?? '—' }}</td></tr>
                </table>
            </div>
        </td>
    </tr>
</table>

@if($sample->variations->count())
<div class="info-section">
    <h3>Variations</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Color</th>
                <th>Size</th>
                <th class="text-right">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sample->variations as $i => $v)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $v->color->name ?? $v->color_id ?? '—' }}</td>
                <td>{{ $v->size->name ?? $v->size_id ?? '—' }}</td>
                <td class="text-right">{{ number_format($v->quantity) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Total Quantity</td>
                <td class="text-right">{{ number_format($sample->variations->sum('quantity')) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@if($sample->remarks)
<div class="info-section">
    <h3>Remarks</h3>
    <p style="font-size:8.5pt; color:#424242;">{{ $sample->remarks }}</p>
</div>
@endif

</body>
</html>
