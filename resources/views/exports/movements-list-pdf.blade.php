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

<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px">#</th>
            <th style="width:90px">Issue Date</th>
            <th style="width:100px">Expected Return</th>
            <th>Assigned To</th>
            <th class="text-right" style="width:60px">Items</th>
            <th style="width:100px">Inspection Run</th>
            <th style="width:70px">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($movements as $i => $movement)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($movement->issue_date)->format('d M Y') }}</td>
            <td>{{ $movement->expected_return_date ? \Carbon\Carbon::parse($movement->expected_return_date)->format('d M Y') : '—' }}</td>
            <td>{{ $movement->employees->pluck('employee_name')->implode(', ') ?: '—' }}</td>
            <td class="text-right">{{ $movement->items->count() }}</td>
            <td class="text-muted" style="font-size:7.5pt;">
                {{ $movement->inspectionRun?->inspection?->report_number ?? '—' }}
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
