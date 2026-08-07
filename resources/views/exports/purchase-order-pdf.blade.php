<!DOCTYPE html>
<html lang="en">
<head>
<title>Purchase Order — {{ $purchaseOrder->po_number }}</title>
@include('exports.partials._pdf-head')
<style>
    .totals-box { width: 55%; margin-left: auto; border-collapse: collapse; margin-top: 12px; }
    .totals-box td { padding: 4px 8px; font-size: 9pt; border-bottom: 1px solid #e9ecef; }
    .totals-box td:last-child { text-align: right; font-weight: bold; }
    .totals-box .grand-row td { font-size: 11pt; font-weight: bold; color: #1a3560; border-top: 2px solid #1a3560; border-bottom: none; background: #eef2ff; }
    .paid-row td { color: #155724; }
    .due-row td  { color: #721c24; }
</style>
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Purchase Order</div>
                <div class="db-sub">{{ $purchaseOrder->items->count() }} item{{ $purchaseOrder->items->count() !== 1 ? 's' : '' }}</div>
            </td>
            <td class="db-right">
                <div class="db-code">{{ $purchaseOrder->po_number }}</div>
                <div class="db-date">{{ $purchaseOrder->po_date->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Order Details --}}
<div class="info-section" style="margin-bottom:16px;">
    <h3>Order Details</h3>
    <table class="info-grid" style="width:50%;">
        <tr>
            <td class="info-label">PO Number</td>
            <td class="info-value">{{ $purchaseOrder->po_number }}</td>
        </tr>
        <tr>
            <td class="info-label">PO Date</td>
            <td class="info-value">{{ $purchaseOrder->po_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Status</td>
            <td class="info-value">
                @php $sc = match($purchaseOrder->status) { 'Paid'=>'success', 'Partially Paid'=>'warning', default=>'danger' }; @endphp
                <span class="badge badge-{{ $sc }}">{{ $purchaseOrder->status }}</span>
            </td>
        </tr>
    </table>
</div>

{{-- Line Items --}}
<div class="info-section">
    <h3>Items</h3>
    <table class="data-table data-table-fixed">
        <thead>
            <tr>
                <th style="width:6%">#</th>
                <th style="width:50%">Description</th>
                <th class="text-right" style="width:14%">Qty</th>
                <th class="text-right" style="width:15%">Unit Price</th>
                <th class="text-right" style="width:15%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrder->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ Illuminate\Support\Str::limit($item->description, 70) }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($item->total_amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="no-data">No items on this order.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Totals --}}
<table class="totals-box">
    <tr class="grand-row">
        <td>Grand Total</td>
        <td>{{ number_format($purchaseOrder->total_amount, 2) }}</td>
    </tr>
    @if($purchaseOrder->amount_paid > 0)
    <tr class="paid-row">
        <td>Amount Paid</td>
        <td>{{ number_format($purchaseOrder->amount_paid, 2) }}</td>
    </tr>
    @endif
    @if($purchaseOrder->amount_due > 0)
    <tr class="due-row">
        <td>Amount Due</td>
        <td>{{ number_format($purchaseOrder->amount_due, 2) }}</td>
    </tr>
    @endif
</table>

@if($purchaseOrder->expenses->count())
<div class="info-section" style="margin-top:16px;">
    <h3>Payment History</h3>
    <table class="data-table data-table-fixed">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:20%">Date</th>
                <th style="width:25%">Account</th>
                <th style="width:30%">Description</th>
                <th class="text-right" style="width:20%">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->expenses as $i => $payment)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($payment->expense_date)->format('d M Y') }}</td>
                <td>{{ $payment->account?->account_name ?? '—' }}</td>
                <td class="text-muted">{{ Illuminate\Support\Str::limit($payment->description ?? '—', 40) }}</td>
                <td class="text-right fw-bold">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($purchaseOrder->remarks)
<div class="info-section" style="margin-top:14px;">
    <h3>Remarks</h3>
    <p style="font-size:8.5pt; color:#424242;">{{ $purchaseOrder->remarks }}</p>
</div>
@endif

</body>
</html>
