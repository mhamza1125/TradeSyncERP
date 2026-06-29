<!DOCTYPE html>
<html lang="en">
<head>
<title>Colors</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Color Register</div>
                <div class="db-sub">{{ $colors->count() }} color{{ $colors->count() !== 1 ? 's' : '' }} listed</div>
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
            <th>Color Name</th>
        </tr>
    </thead>
    <tbody>
        @forelse($colors as $i => $color)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $color->name }}</td>
        </tr>
        @empty
        <tr><td colspan="2" class="no-data">No colors found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $colors->count() }} records
</div>

</body>
</html>
