<!DOCTYPE html>
<html lang="en">
<head>
<title>Customer List</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Customer Directory'])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Customer Directory'])

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

<table class="data-table">
    <thead>
        <tr>
            <th style="width:30px">#</th>
            <th style="min-width:140px">Customer Name</th>
            <th style="width:120px">Contact Person</th>
            <th style="width:110px">Phone</th>
            <th style="width:120px">Email</th>
            <th style="width:60px">Currency</th>
            <th class="text-right" style="width:100px">Opening Bal.</th>
            <th style="width:60px text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customers as $i => $customer)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $customer->customer_name }}</td>
            <td>{{ $customer->contact_person ?? '—' }}</td>
            <td>{{ $customer->phone ?? '—' }}</td>
            <td class="text-muted" style="font-size:7.5pt;">{{ $customer->email ?? '—' }}</td>
            <td class="text-center">{{ $customer->currency?->currency_code ?? 'PKR' }}</td>
            <td class="text-right">{{ number_format($customer->opening_balance, 2) }}</td>
            <td class="text-center">
                @if($customer->status)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="no-data">No customers found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $customers->count() }} records
</div>

</body>
</html>
