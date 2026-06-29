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

<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px">#</th>
            <th>Bank Name</th>
            <th style="width:160px">Branch</th>
            <th style="width:160px">Address</th>
            <th style="width:110px">Phone</th>
        </tr>
    </thead>
    <tbody>
        @forelse($banks as $i => $bank)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $bank->bank_name }}</td>
            <td class="text-muted">{{ $bank->branch_name ?? '—' }}</td>
            <td class="text-muted">{{ $bank->address ?? '—' }}</td>
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
