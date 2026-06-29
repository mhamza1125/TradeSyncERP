<!DOCTYPE html>
<html lang="en">
<head>
<title>Customer Orders</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Customer Orders</div>
                <div class="db-sub">{{ $orders->count() }} order{{ $orders->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="width:110px">Order Code</th>
            <th>Customer</th>
            <th style="width:90px">Order Date</th>
            <th style="width:90px">Required Date</th>
            <th style="width:70px text-center">Status</th>
            <th class="text-right" style="width:90px">Items</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $i => $order)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $order->order_code }}</td>
            <td>{{ $order->customer->customer_name }}</td>
            <td>{{ $order->order_date->format('d M Y') }}</td>
            <td>{{ $order->required_by ? $order->required_by->format('d M Y') : '—' }}</td>
            <td class="text-center">
                @php
                    $sc = match($order->status) {
                        'Confirmed','Processing' => 'primary',
                        'Dispatched' => 'success',
                        'Cancelled' => 'danger',
                        default => 'secondary',
                    };
                @endphp
                <span class="badge badge-{{ $sc }}">{{ $order->status }}</span>
            </td>
            <td class="text-right">{{ $order->items->count() }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="no-data">No orders found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $orders->count() }} records
</div>

</body>
</html>
