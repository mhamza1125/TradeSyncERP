<!DOCTYPE html>
<html lang="en">
<head>
<title>Customer Profile — {{ $customer->display_name }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Customer Profile</div>
                <div class="db-sub">{{ $customer->display_name }}</div>
            </td>
            <td class="db-right">
                @if($customer->status)
                    <span style="background:#d4edda; color:#155724; padding:3px 8px; border-radius:3px; font-size:8pt; font-weight:bold;">Active</span>
                @else
                    <span style="background:#f8d7da; color:#721c24; padding:3px 8px; border-radius:3px; font-size:8pt; font-weight:bold;">Inactive</span>
                @endif
                <div class="db-date" style="margin-top:6px;">Customer since {{ $customer->created_at->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Headline figures --}}
<table class="stat-row">
    <tr>
        <td style="width:25%;">
            <div class="stat-tile">
                <span class="stat-label">Currency</span>
                <span class="stat-value">{{ $customer->currency?->currency_code ?? 'PKR' }}</span>
            </div>
        </td>
        <td style="width:25%;">
            <div class="stat-tile">
                <span class="stat-label">Opening Balance</span>
                <span class="stat-value stat-value-sm">{{ number_format($customer->opening_balance, 2) }}</span>
            </div>
        </td>
        <td style="width:25%;">
            <div class="stat-tile">
                <span class="stat-label">Payments Recorded</span>
                <span class="stat-value">{{ $customer->payments->count() }}</span>
            </div>
        </td>
        <td style="width:25%;">
            <div class="stat-tile">
                <span class="stat-label">Total PKR Received</span>
                <span class="stat-value stat-value-sm" style="color:#155724;">{{ number_format($customer->payments->sum('actual_pkr_received'), 2) }}</span>
            </div>
        </td>
    </tr>
</table>

{{-- Company details + address, side by side --}}
<table style="width:100%; border-collapse:collapse;">
    <tr>
        <td style="width:50%; vertical-align:top; padding-right:8px;">
            <div class="info-panel">
                <div class="info-panel-header">Company Details</div>
                <div class="info-panel-body">
                    <table class="info-grid">
                        <tr><td class="info-label">Brand / Trading Name</td><td class="info-value">{{ $customer->brand ?? '—' }}</td></tr>
                        <tr><td class="info-label">Legal Customer Name</td><td class="info-value">{{ $customer->customer_name }}</td></tr>
                        <tr><td class="info-label">Contact Person</td><td class="info-value">{{ $customer->contact_person ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </td>
        <td style="width:50%; vertical-align:top; padding-left:8px;">
            <div class="info-panel">
                <div class="info-panel-header">Address &amp; Account</div>
                <div class="info-panel-body">
                    <table class="info-grid">
                        <tr><td class="info-label">Address</td><td class="info-value">{{ $customer->address ?? '—' }}</td></tr>
                        <tr><td class="info-label">Account Status</td><td class="info-value">{{ $customer->status ? 'Active' : 'Inactive' }}</td></tr>
                        <tr><td class="info-label">Customer Since</td><td class="info-value">{{ $customer->created_at->format('d M Y') }}</td></tr>
                    </table>
                </div>
            </div>
        </td>
    </tr>
</table>

@if($customer->payments->count())
<div class="info-panel">
    <div class="info-panel-header">Recent Payments</div>
    <div class="info-panel-body" style="padding:0;">
        <table class="data-table data-table-fixed">
            <thead>
                <tr>
                    <th style="width:13%">Date</th>
                    <th style="width:20%">Invoice Ref</th>
                    <th style="width:11%">Currency</th>
                    <th class="text-right" style="width:16%">Received (FC)</th>
                    <th class="text-right" style="width:18%">PKR Received</th>
                    <th class="text-right" style="width:22%">Exchange Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customer->payments as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                    <td>{{ Illuminate\Support\Str::limit($payment->invoice_reference ?? '—', 20) }}</td>
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
</div>
@endif

</body>
</html>
