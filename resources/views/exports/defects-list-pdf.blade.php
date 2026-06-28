<!DOCTYPE html>
<html lang="en">
<head>
<title>Defects</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Defect Catalogue'])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Defect Catalogue'])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Defect Catalogue</div>
                <div class="db-sub">{{ $defects->count() }} defect{{ $defects->count() !== 1 ? 's' : '' }} listed</div>
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
            <th>Defect Name</th>
            <th style="width:100px">Severity</th>
            <th style="width:60px">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($defects as $i => $defect)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $defect->defect_name }}</td>
            <td class="text-center">
                @php
                    $sc = match($defect->severity) {
                        'critical' => 'danger', 'major' => 'warning',
                        'minor' => 'info', default => 'secondary',
                    };
                @endphp
                <span class="badge badge-{{ $sc }}">{{ ucfirst($defect->severity) }}</span>
            </td>
            <td class="text-center">
                <span class="badge {{ $defect->status ? 'badge-success' : 'badge-danger' }}">
                    {{ $defect->status ? 'Active' : 'Inactive' }}
                </span>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="no-data">No defects found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $defects->count() }} records
</div>

</body>
</html>
