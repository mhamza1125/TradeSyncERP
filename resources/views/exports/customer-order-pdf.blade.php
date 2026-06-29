<!DOCTYPE html>
<html lang="en">
<head>
<title>Customer Order — {{ $order->order_code }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

{{-- Document banner --}}
<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Customer Order</div>
                <div class="db-sub">{{ $order->customer->customer_name }}</div>
            </td>
            <td class="db-right">
                <div class="db-code">{{ $order->order_code }}</div>
                <div class="db-date">{{ $order->order_date->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Order & Customer Details --}}
<table class="two-col">
    <tr>
        <td>
            <div class="info-section">
                <h3>Order Details</h3>
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Order Code</td>
                        <td class="info-value">{{ $order->order_code }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Order Date</td>
                        <td class="info-value">{{ $order->order_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Status</td>
                        <td class="info-value">
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
                    </tr>
                    @if($order->required_by)
                    <tr>
                        <td class="info-label">Required Date</td>
                        <td class="info-value">{{ $order->required_by->format('d M Y') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </td>
        <td>
            <div class="info-section">
                <h3>Customer Details</h3>
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Customer</td>
                        <td class="info-value">{{ $order->customer->customer_name }}</td>
                    </tr>
                    @if($order->customer->contact_person)
                    <tr>
                        <td class="info-label">Contact Person</td>
                        <td class="info-value">{{ $order->customer->contact_person }}</td>
                    </tr>
                    @endif
                    @if($order->customer->phone)
                    <tr>
                        <td class="info-label">Phone</td>
                        <td class="info-value">{{ $order->customer->phone }}</td>
                    </tr>
                    @endif
                    @if($order->customer->email)
                    <tr>
                        <td class="info-label">Email</td>
                        <td class="info-value">{{ $order->customer->email }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- Order Items --}}
<div class="info-section">
    <h3>Order Items</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Product Category</th>
                <th class="text-right" style="width:100px">Quantity</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->productCategory->category_name ?? '—' }}</td>
                <td class="text-right">{{ number_format($item->quantity ?? 0) }}</td>
                <td class="text-muted">{{ $item->description ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="no-data">No items on this order.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($order->remarks)
<div class="info-section">
    <h3>Remarks</h3>
    <p style="font-size:8.5pt; color:#424242;">{{ $order->remarks }}</p>
</div>
@endif

</body>
</html>
