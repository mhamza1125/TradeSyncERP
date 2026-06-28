<!DOCTYPE html>
<html lang="en">
<head>
<title>Customer Order — {{ $order->order_code }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Customer Order', 'reportRef' => $order->order_code])

@include('exports.partials._pdf-company-footer', ['centerText' => $order->order_code])

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
                    @if($order->delivery_date)
                    <tr>
                        <td class="info-label">Delivery Date</td>
                        <td class="info-value">{{ $order->delivery_date->format('d M Y') }}</td>
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
                <th class="text-right" style="width:110px">Unit Price</th>
                <th class="text-right" style="width:120px">Total</th>
                <th style="width:120px">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($order->items as $i => $item)
            @php
                $lineTotal = ($item->unit_price ?? 0) * ($item->quantity ?? 0);
                $grandTotal += $lineTotal;
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->productCategory->category_name ?? '—' }}</td>
                <td class="text-right">{{ number_format($item->quantity ?? 0) }}</td>
                <td class="text-right">{{ $item->unit_price ? number_format($item->unit_price, 2) : '—' }}</td>
                <td class="text-right">{{ $lineTotal > 0 ? number_format($lineTotal, 2) : '—' }}</td>
                <td class="text-muted">{{ $item->remarks ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="no-data">No items on this order.</td></tr>
            @endforelse
        </tbody>
        @if($order->items->count() && $grandTotal > 0)
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Grand Total</td>
                <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
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
