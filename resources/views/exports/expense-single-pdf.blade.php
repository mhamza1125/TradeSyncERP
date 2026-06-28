<!DOCTYPE html>
<html lang="en">
<head>
<title>Expense — #{{ $expense->id }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Expense Voucher', 'reportRef' => 'EXP-'.$expense->id])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Expense #'.$expense->id])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Expense Voucher</div>
                <div class="db-sub">{{ $expense->expenseHead->expense_name ?? '—' }}</div>
            </td>
            <td class="db-right">
                <div class="db-code">#{{ $expense->id }}</div>
                <div class="db-date">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="two-col">
    <tr>
        <td>
            <div class="info-section">
                <h3>Expense Details</h3>
                <table class="info-grid">
                    <tr>
                        <td class="info-label">Expense Date</td>
                        <td class="info-value">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Expense Type</td>
                        <td class="info-value">{{ $expense->expenseHead->expense_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Account</td>
                        <td class="info-value">{{ $expense->account->account_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Amount</td>
                        <td class="info-value" style="font-size:13pt; color:#1a3560;">{{ number_format($expense->amount, 2) }} PKR</td>
                    </tr>
                </table>
            </div>
        </td>
        <td>
            <div class="info-section">
                <h3>Transaction Details</h3>
                <table class="info-grid">
                    @if($expense->transaction)
                    <tr>
                        <td class="info-label">Transaction ID</td>
                        <td class="info-value">#{{ $expense->transaction->id }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Transaction Date</td>
                        <td class="info-value">{{ \Carbon\Carbon::parse($expense->transaction->transaction_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Recorded By</td>
                        <td class="info-value">{{ $expense->transaction->creator->name ?? '—' }}</td>
                    </tr>
                    @else
                    <tr><td colspan="2" class="text-muted">No transaction linked.</td></tr>
                    @endif
                </table>
            </div>
        </td>
    </tr>
</table>

@if($expense->description)
<div class="info-section">
    <h3>Description / Remarks</h3>
    <p style="font-size:8.5pt; color:#424242; padding:8px; background:#f8f9fa; border-radius:3px;">{{ $expense->description }}</p>
</div>
@endif

</body>
</html>
