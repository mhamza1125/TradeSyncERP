<!DOCTYPE html>
<html lang="en">
<head>
<title>Customer List</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Customer Directory</div>
                <div class="db-sub">{{ $customers->count() }} customer{{ $customers->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="width:24%">Brand</th>
            <th style="width:22%">Customer Name</th>
            <th style="width:19%">Contact Person</th>
            <th class="text-right" style="width:19%">Opening Bal.</th>
            <th style="width:12%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customers as $i => $customer)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ Illuminate\Support\Str::limit($customer->display_name, 26) }}</td>
            <td>{{ Illuminate\Support\Str::limit($customer->customer_name, 24) }}</td>
            <td>{{ Illuminate\Support\Str::limit($customer->contact_person ?? '—', 20) }}</td>
            <td class="text-right">{{ number_format($customer->opening_balance, 2) }} {{ $customer->currency?->currency_code ?? 'PKR' }}</td>
            <td class="text-center">
                @if($customer->status)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="no-data">No customers found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $customers->count() }} records
</div>

</body>
</html>
