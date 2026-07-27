<!DOCTYPE html>
<html lang="en">
<head>
<title>Sample Movements</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Sample Movements</div>
                <div class="db-sub">{{ $movements->count() }} movement{{ $movements->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="width:12%">Issue Date</th>
            <th style="width:14%">Expected Return</th>
            <th style="width:26%">Assigned To</th>
            <th class="text-right" style="width:8%">Items</th>
            <th style="width:20%">Inspection Run</th>
            <th style="width:16%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($movements as $i => $movement)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($movement->issue_date)->format('d M Y') }}</td>
            <td>{{ $movement->expected_return_date ? \Carbon\Carbon::parse($movement->expected_return_date)->format('d M Y') : '—' }}</td>
            <td>{{ Illuminate\Support\Str::limit($movement->employees->pluck('employee_name')->implode(', ') ?: '—', 28) }}</td>
            <td class="text-right">{{ $movement->items->count() }}</td>
            <td class="text-muted" style="font-size:7.5pt;">
                {{ Illuminate\Support\Str::limit($movement->inspectionRun?->inspection?->report_number ?? '—', 18) }}
            </td>
            <td class="text-center">
                @php
                    $sc = match($movement->status) {
                        'Returned' => 'success', 'Overdue' => 'danger', default => 'primary',
                    };
                @endphp
                <span class="badge badge-{{ $sc }}">{{ $movement->status }}</span>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="no-data">No movements found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $movements->count() }} records
</div>

</body>
</html>
