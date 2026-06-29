<!DOCTYPE html>
<html lang="en">
<head>
<title>Customer Payments</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Customer Payments</div>
                <div class="db-sub">{{ $payments->count() }} payment{{ $payments->count() !== 1 ? 's' : '' }} listed</div>
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
            <th>Customer</th>
            <th style="width:90px">Payment Date</th>
            <th style="width:60px">Currency</th>
            <th class="text-right" style="width:100px">Received (FC)</th>
            <th class="text-right" style="width:110px">PKR Received</th>
            <th class="text-right" style="width:100px">Gain / Loss</th>
            <th style="width:120px">Invoice Ref.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payments as $i => $payment)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $payment->customer->customer_name }}</td>
            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
            <td class="text-center">{{ $payment->foreign_currency }}</td>
            <td class="text-right">{{ number_format($payment->received_fc, 2) }}</td>
            <td class="text-right fw-bold">{{ number_format($payment->actual_pkr_received, 2) }}</td>
            <td class="text-right {{ $payment->pkr_gain_loss > 0 ? '' : ($payment->pkr_gain_loss < 0 ? '' : '') }}"
                style="{{ $payment->pkr_gain_loss > 0 ? 'color:#155724;' : ($payment->pkr_gain_loss < 0 ? 'color:#721c24;' : '') }}">
                {{ $payment->pkr_gain_loss > 0 ? '+' : '' }}{{ number_format($payment->pkr_gain_loss, 2) }}
            </td>
            <td class="text-muted">{{ $payment->invoice_reference ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="no-data">No payments found.</td></tr>
        @endforelse
    </tbody>
    @if($payments->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="5" class="text-right">Totals</td>
            <td class="text-right">{{ number_format($payments->sum('actual_pkr_received'), 2) }}</td>
            <td class="text-right">{{ number_format($payments->sum('pkr_gain_loss'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $payments->count() }} records
</div>

</body>
</html>
