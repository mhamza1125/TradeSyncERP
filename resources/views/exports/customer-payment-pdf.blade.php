<!DOCTYPE html>
<html lang="en">
<head>
<title>Payment Receipt #{{ $payment->id }}</title>
@include('exports.partials._pdf-head')
<style>
    .receipt-box {
        border: 1px solid #c8d6f0;
        border-radius: 4px;
        padding: 14px 16px;
        background: #f8faff;
        margin-bottom: 14px;
    }
    .amount-highlight {
        font-size: 18pt;
        font-weight: bold;
        color: #1a3560;
    }
    .gain { color: #155724; }
    .loss { color: #721c24; }
</style>
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Payment Receipt', 'reportRef' => 'RCP-'.$payment->id])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Receipt #'.$payment->id])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Payment Receipt</div>
                <div class="db-sub">{{ $payment->customer->customer_name }}</div>
            </td>
            <td class="db-right">
                <div class="db-code">#{{ $payment->id }}</div>
                <div class="db-date">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Received From + Receipt Meta --}}
<table class="two-col" style="margin-bottom:16px;">
    <tr>
        <td>
            <p style="font-size:7.5pt; text-transform:uppercase; color:#757575; margin-bottom:4px;">Received From:</p>
            <div style="font-size:12pt; font-weight:bold; color:#1a3560;">{{ $payment->customer->customer_name }}</div>
            @if($payment->customer->contact_person)
            <div style="font-size:8.5pt; color:#424242;">{{ $payment->customer->contact_person }}</div>
            @endif
            @if($payment->customer->phone)
            <div style="font-size:8.5pt; color:#757575;">{{ $payment->customer->phone }}</div>
            @endif
        </td>
        <td>
            <table class="info-grid">
                <tr>
                    <td class="info-label">Payment Date</td>
                    <td class="info-value">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                </tr>
                @if($payment->invoice_reference)
                <tr>
                    <td class="info-label">Invoice Reference</td>
                    <td class="info-value">{{ $payment->invoice_reference }}</td>
                </tr>
                @endif
                <tr>
                    <td class="info-label">Account</td>
                    <td class="info-value">{{ $payment->account->account_name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Status</td>
                    <td class="info-value"><span class="badge badge-success">Completed</span></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- FC + PKR Details --}}
<table class="two-col">
    <tr>
        <td>
            <div class="receipt-box">
                <h3 style="font-size:8pt; font-weight:bold; text-transform:uppercase; color:#546e7a; letter-spacing:0.4px; margin-bottom:10px;">Foreign Currency Details</h3>
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Currency</td>
                        <td class="info-value">{{ $payment->foreign_currency }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Invoiced Amount</td>
                        <td class="info-value">{{ number_format($payment->invoiced_amount_fc, 2) }} {{ $payment->foreign_currency }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Received (FC)</td>
                        <td class="info-value">{{ number_format($payment->received_fc, 2) }} {{ $payment->foreign_currency }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Deduction (FC)</td>
                        <td class="info-value {{ $payment->deduction_fc > 0 ? 'loss' : '' }}">
                            {{ number_format($payment->deduction_fc, 2) }}
                        </td>
                    </tr>
                    @if($payment->received_fc > 0)
                    @php
                        $fcWords = \App\Helpers\NumberToWords::convert(
                            (float) $payment->received_fc,
                            \App\Helpers\NumberToWords::currencyName($payment->foreign_currency ?? 'USD'),
                            \App\Helpers\NumberToWords::subunitName($payment->foreign_currency ?? 'USD')
                        );
                    @endphp
                    <tr>
                        <td colspan="2" class="fs-italic text-muted" style="font-size:7.5pt; padding-top:6px;">{{ $fcWords }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </td>
        <td>
            <div class="receipt-box">
                <h3 style="font-size:8pt; font-weight:bold; text-transform:uppercase; color:#546e7a; letter-spacing:0.4px; margin-bottom:10px;">PKR Details</h3>
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Exchange Rate</td>
                        <td class="info-value">{{ number_format($payment->exchange_rate, 4) }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Expected PKR</td>
                        <td class="info-value">{{ number_format($payment->expected_pkr, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Actual PKR Received</td>
                        <td class="info-value gain" style="font-size:11pt;">{{ number_format($payment->actual_pkr_received, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">PKR Gain / Loss</td>
                        <td class="info-value {{ $payment->pkr_gain_loss > 0 ? 'gain' : ($payment->pkr_gain_loss < 0 ? 'loss' : '') }}">
                            {{ $payment->pkr_gain_loss > 0 ? '+' : '' }}{{ number_format($payment->pkr_gain_loss, 2) }}
                        </td>
                    </tr>
                    @if($payment->actual_pkr_received > 0)
                    @php
                        $pkrWords = \App\Helpers\NumberToWords::convert(
                            (float) $payment->actual_pkr_received,
                            \App\Helpers\NumberToWords::currencyName('PKR'),
                            \App\Helpers\NumberToWords::subunitName('PKR')
                        );
                    @endphp
                    <tr>
                        <td colspan="2" class="fs-italic text-muted" style="font-size:7.5pt; padding-top:6px;">{{ $pkrWords }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </td>
    </tr>
</table>

@if($payment->remarks)
<div class="info-section" style="margin-top:14px;">
    <h3>Remarks</h3>
    <p style="font-size:8.5pt; color:#424242;">{{ $payment->remarks }}</p>
</div>
@endif

</body>
</html>
