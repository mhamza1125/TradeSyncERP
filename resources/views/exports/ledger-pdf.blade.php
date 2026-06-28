<!DOCTYPE html>
<html lang="en">
<head>
<title>{{ $title }} — Ledger</title>
@include('exports.partials._pdf-head')
<style>
    .credit { color: #155724; }
    .debit  { color: #721c24; }
    .running-balance { font-weight: bold; color: #1a3560; }
</style>
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => $title])

@include('exports.partials._pdf-company-footer', ['centerText' => $title])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">{{ $title }}</div>
                @if(!empty($account))
                <div class="db-sub">{{ $account->account_name }} ({{ $account->account_type }})</div>
                @elseif(!empty($customer))
                <div class="db-sub">{{ $customer->customer_name }}</div>
                @endif
            </td>
            <td class="db-right">
                @if(!empty($filters['from_date']) || !empty($filters['to_date']))
                <div class="db-code" style="font-size:8pt; font-weight:normal; color:#b0c4d8;">
                    @if(!empty($filters['from_date'])){{ \Carbon\Carbon::parse($filters['from_date'])->format('d M Y') }}@endif
                    @if(!empty($filters['from_date']) && !empty($filters['to_date'])) — @endif
                    @if(!empty($filters['to_date'])){{ \Carbon\Carbon::parse($filters['to_date'])->format('d M Y') }}@endif
                </div>
                @endif
                <div class="db-date" style="margin-top:4px;">{{ now()->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Account / Customer Summary --}}
@if(!empty($account))
<table class="two-col" style="margin-bottom:14px;">
    <tr>
        <td>
            <table class="info-grid">
                <tr><td class="info-label">Account Name</td><td class="info-value">{{ $account->account_name }}</td></tr>
                <tr><td class="info-label">Account Type</td><td class="info-value">{{ $account->account_type }}</td></tr>
                <tr><td class="info-label">Opening Balance</td><td class="info-value">{{ number_format($openingBalance, 2) }} PKR</td></tr>
            </table>
        </td>
        <td>
            <table class="info-grid">
                <tr><td class="info-label">Total Transactions</td><td class="info-value">{{ $transactions->count() }}</td></tr>
                <tr>
                    <td class="info-label">Closing Balance</td>
                    <td class="info-value running-balance" style="font-size:12pt;">{{ number_format($closingBalance, 2) }} PKR</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

@if(!empty($customer))
<table class="two-col" style="margin-bottom:14px;">
    <tr>
        <td>
            <table class="info-grid">
                <tr><td class="info-label">Customer</td><td class="info-value">{{ $customer->customer_name }}</td></tr>
                <tr><td class="info-label">Contact</td><td class="info-value">{{ $customer->contact_person ?? '—' }}</td></tr>
                <tr><td class="info-label">Phone</td><td class="info-value">{{ $customer->phone ?? '—' }}</td></tr>
                <tr><td class="info-label">Currency</td><td class="info-value">{{ $customer->currency?->currency_code ?? 'PKR' }}</td></tr>
            </table>
        </td>
        <td>
            <table class="info-grid">
                <tr><td class="info-label">Total Payments</td><td class="info-value">{{ $transactions->count() }}</td></tr>
                <tr>
                    <td class="info-label">Total PKR Received</td>
                    <td class="info-value credit" style="font-size:12pt;">{{ number_format($transactions->sum('actual_pkr_received'), 2) }}</td>
                </tr>
                <tr>
                    <td class="info-label">Total FC Received</td>
                    <td class="info-value">{{ number_format($transactions->sum('received_fc'), 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

{{-- Transactions / Payments Table --}}
@if(!empty($account))
<div class="info-section">
    <h3>Transaction Ledger</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:80px">Date</th>
                <th>Description / Reference</th>
                <th style="width:90px">Type</th>
                <th class="text-right" style="width:90px">Debit</th>
                <th class="text-right" style="width:90px">Credit</th>
                <th class="text-right" style="width:100px">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $runningBalance = $openingBalance; @endphp
            <tr>
                <td colspan="5" class="text-muted">Opening Balance</td>
                <td class="text-right running-balance">{{ number_format($runningBalance, 2) }}</td>
            </tr>
            @forelse($transactions as $txn)
            @php
                $isCredit = $txn->transaction_type === 'CustomerReceipt' || ($txn->transaction_type === 'JournalEntry' && $txn->debit_account_id == $account->id);
                $isDebit  = !$isCredit;
                if ($txn->transaction_type === 'JournalEntry') {
                    $runningBalance += $txn->debit_account_id == $account->id ? (float)$txn->amount : -(float)$txn->amount;
                } else {
                    $runningBalance += $txn->transaction_type === 'CustomerReceipt' ? (float)$txn->amount : -(float)$txn->amount;
                }
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($txn->transaction_date)->format('d M Y') }}</td>
                <td>
                    <span>{{ ucfirst(str_replace('_', ' ', $txn->transaction_type)) }}</span>
                    @if($txn->remarks)
                    <div class="text-muted" style="font-size:7pt;">{{ $txn->remarks }}</div>
                    @endif
                </td>
                <td class="text-muted" style="font-size:7.5pt;">{{ $txn->transaction_type }}</td>
                <td class="text-right {{ $isDebit ? 'debit' : '' }}">
                    {{ $isDebit ? number_format($txn->amount, 2) : '—' }}
                </td>
                <td class="text-right {{ $isCredit ? 'credit' : '' }}">
                    {{ $isCredit ? number_format($txn->amount, 2) : '—' }}
                </td>
                <td class="text-right running-balance">{{ number_format($runningBalance, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="no-data">No transactions in this period.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right">Closing Balance</td>
                <td class="text-right">{{ number_format($closingBalance, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@if(!empty($customer))
<div class="info-section">
    <h3>Payment History</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:80px">Date</th>
                <th style="width:120px">Invoice Ref</th>
                <th style="width:70px">Currency</th>
                <th class="text-right" style="width:100px">Invoiced (FC)</th>
                <th class="text-right" style="width:100px">Received (FC)</th>
                <th class="text-right" style="width:90px">Exch. Rate</th>
                <th class="text-right" style="width:110px">PKR Received</th>
                <th class="text-right" style="width:90px">PKR Gain/Loss</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $payment)
            <tr>
                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                <td>{{ $payment->invoice_reference ?? '—' }}</td>
                <td>{{ $payment->foreign_currency }}</td>
                <td class="text-right">{{ number_format($payment->invoiced_amount_fc, 2) }}</td>
                <td class="text-right">{{ number_format($payment->received_fc, 2) }}</td>
                <td class="text-right text-muted">{{ number_format($payment->exchange_rate, 4) }}</td>
                <td class="text-right credit fw-bold">{{ number_format($payment->actual_pkr_received, 2) }}</td>
                <td class="text-right {{ $payment->pkr_gain_loss > 0 ? 'credit' : ($payment->pkr_gain_loss < 0 ? 'debit' : '') }}">
                    {{ $payment->pkr_gain_loss != 0 ? ($payment->pkr_gain_loss > 0 ? '+' : '') . number_format($payment->pkr_gain_loss, 2) : '—' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="no-data">No payments found.</td></tr>
            @endforelse
        </tbody>
        @if($transactions->count())
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Total PKR Received</td>
                <td class="text-right">{{ number_format($transactions->sum('actual_pkr_received'), 2) }}</td>
                <td class="text-right">{{ number_format($transactions->sum('pkr_gain_loss'), 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
@endif

</body>
</html>
