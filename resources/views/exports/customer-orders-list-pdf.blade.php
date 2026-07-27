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

<table class="data-table data-table-fixed">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:12%">Order Code</th>
            <th style="width:12%">Order Number</th>
            <th style="width:20%">Brand</th>
            <th style="width:11%">Order Date</th>
            <th style="width:11%">ETD</th>
            <th style="width:14%; text-align:center;">Status</th>
            <th class="text-right" style="width:16%">Items</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $i => $order)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $order->order_code }}</td>
            <td>{{ $order->order_number ?? '—' }}</td>
            <td>{{ Illuminate\Support\Str::limit($order->customer?->display_name ?? '—', 20) }}</td>
            <td>{{ $order->order_date->format('d M Y') }}</td>
            <td>{{ $order->required_by ? $order->required_by->format('d M Y') : '—' }}</td>
            <td class="text-center">
                @php
                    $sc = match($order->status) {
                        'Done' => 'success',
                        default => 'secondary',
                    };
                @endphp
                <span class="badge badge-{{ $sc }}">{{ $order->status }}</span>
            </td>
            <td class="text-right">{{ $order->items->count() }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="no-data">No orders found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $orders->count() }} records
</div>

</body>
</html>
