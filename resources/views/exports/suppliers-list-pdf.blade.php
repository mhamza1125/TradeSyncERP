<!DOCTYPE html>
<html lang="en">
<head>
<title>Supplier Directory</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

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

<table class="data-table data-table-fixed">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:35%">Supplier Name</th>
            <th style="width:24%">City</th>
            <th style="width:22%">Country</th>
            <th style="width:15%; text-align:center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($suppliers as $i => $supplier)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ Illuminate\Support\Str::limit($supplier->name, 30) }}</td>
            <td>{{ Illuminate\Support\Str::limit($supplier->city ?? '—', 20) }}</td>
            <td>{{ Illuminate\Support\Str::limit($supplier->country ?? '—', 18) }}</td>
            <td class="text-center">
                @if($supplier->status)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="no-data">No suppliers found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $suppliers->count() }} records
</div>

</body>
</html>
