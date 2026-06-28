<!DOCTYPE html>
<html lang="en">
<head>
<title>Sizes</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Sizes'])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Sizes'])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Size Register</div>
                <div class="db-sub">{{ $sizes->count() }} size{{ $sizes->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="width:40px">#</th>
            <th>Size Name</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sizes as $i => $size)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $size->name }}</td>
        </tr>
        @empty
        <tr><td colspan="2" class="no-data">No sizes found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $sizes->count() }} records
</div>

</body>
</html>
