<!DOCTYPE html>
<html lang="en">
<head>
<title>Banks</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Banks</div>
                <div class="db-sub">{{ $banks->count() }} bank{{ $banks->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="width:26%">Bank Name</th>
            <th style="width:22%">Branch</th>
            <th style="width:28%">Address</th>
            <th style="width:20%">Phone</th>
        </tr>
    </thead>
    <tbody>
        @forelse($banks as $i => $bank)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ Illuminate\Support\Str::limit($bank->bank_name, 24) }}</td>
            <td class="text-muted">{{ Illuminate\Support\Str::limit($bank->branch_name ?? '—', 20) }}</td>
            <td class="text-muted">{{ Illuminate\Support\Str::limit($bank->address ?? '—', 26) }}</td>
            <td class="text-muted">{{ $bank->phone ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="no-data">No banks found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $banks->count() }} records
</div>

</body>
</html>
