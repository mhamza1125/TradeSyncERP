<!DOCTYPE html>
<html lang="en">
<head>
<title>Supplier Directory</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Supplier Directory'])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Supplier Directory'])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Supplier Directory</div>
                <div class="db-sub">{{ $suppliers->count() }} supplier{{ $suppliers->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="min-width:140px">Supplier Name</th>
            <th style="width:110px">Phone</th>
            <th style="width:140px">Email</th>
            <th style="width:90px">City</th>
            <th style="width:80px">Country</th>
            <th style="width:60px; text-align:center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($suppliers as $i => $supplier)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $supplier->name }}</td>
            <td>{{ $supplier->phone ?? '—' }}</td>
            <td class="text-muted" style="font-size:7.5pt;">{{ $supplier->email ?? '—' }}</td>
            <td>{{ $supplier->city ?? '—' }}</td>
            <td>{{ $supplier->country ?? '—' }}</td>
            <td class="text-center">
                @if($supplier->status)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="no-data">No suppliers found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $suppliers->count() }} records
</div>

</body>
</html>
