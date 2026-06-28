<!DOCTYPE html>
<html lang="en">
<head>
<title>Customer Profile — {{ $customer->customer_name }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Customer Profile', 'reportSubtitle' => $customer->customer_name])

@include('exports.partials._pdf-company-footer', ['centerText' => $customer->customer_name])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Customer Profile</div>
                <div class="db-sub">{{ $customer->customer_name }}</div>
            </td>
            <td class="db-right">
                @if($customer->status)
                    <span style="background:#d4edda; color:#155724; padding:3px 8px; border-radius:3px; font-size:8pt; font-weight:bold;">Active</span>
                @else
                    <span style="background:#f8d7da; color:#721c24; padding:3px 8px; border-radius:3px; font-size:8pt; font-weight:bold;">Inactive</span>
                @endif
                <div class="db-date" style="margin-top:6px;">Since {{ $customer->created_at->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="two-col">
    <tr>
        <td>
            <div class="info-section">
                <h3>Contact Information</h3>
                <table class="info-grid">
                    <tr><td class="info-label">Customer Name</td><td class="info-value">{{ $customer->customer_name }}</td></tr>
                    <tr><td class="info-label">Contact Person</td><td class="info-value">{{ $customer->contact_person ?? '—' }}</td></tr>
                    <tr><td class="info-label">Phone</td><td class="info-value">{{ $customer->phone ?? '—' }}</td></tr>
                    <tr><td class="info-label">Email</td><td class="info-value">{{ $customer->email ?? '—' }}</td></tr>
                    <tr><td class="info-label">Address</td><td class="info-value">{{ $customer->address ?? '—' }}</td></tr>
                    @if($customer->brand)
                    <tr><td class="info-label">Brand</td><td class="info-value">{{ $customer->brand }}</td></tr>
                    @endif
                </table>
            </div>
        </td>
        <td>
            <div class="info-section">
                <h3>Financial Summary</h3>
                <table class="info-grid">
                    <tr><td class="info-label">Currency</td><td class="info-value">{{ $customer->currency?->currency_code ?? 'PKR' }}</td></tr>
                    <tr><td class="info-label">Opening Balance</td><td class="info-value">{{ number_format($customer->opening_balance, 2) }} {{ $customer->currency?->currency_code ?? 'PKR' }}</td></tr>
                    <tr><td class="info-label">Total Payments</td><td class="info-value">{{ $customer->payments->count() }}</td></tr>
                    <tr><td class="info-label">Total PKR Received</td><td class="info-value" style="color:#155724;">{{ number_format($customer->payments->sum('actual_pkr_received'), 2) }}</td></tr>
                </table>
            </div>
        </td>
    </tr>
</table>

@if($customer->payments->count())
<div class="info-section">
    <h3>Recent Payments</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:90px">Date</th>
                <th style="width:120px">Invoice Ref</th>
                <th style="width:70px">Currency</th>
                <th class="text-right" style="width:100px">Received (FC)</th>
                <th class="text-right" style="width:110px">PKR Received</th>
                <th class="text-right" style="width:90px">Exchange Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customer->payments as $payment)
            <tr>
                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                <td>{{ $payment->invoice_reference ?? '—' }}</td>
                <td>{{ $payment->foreign_currency }}</td>
                <td class="text-right">{{ number_format($payment->received_fc, 2) }}</td>
                <td class="text-right fw-bold">{{ number_format($payment->actual_pkr_received, 2) }}</td>
                <td class="text-right text-muted">{{ number_format($payment->exchange_rate, 4) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Total PKR Received</td>
                <td class="text-right">{{ number_format($customer->payments->sum('actual_pkr_received'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

</body>
</html>
