<!DOCTYPE html>
<html lang="en">
<head>
<title>Customer Invoices</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Customer Invoices'])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Customer Invoices'])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Customer Invoices</div>
                <div class="db-sub">{{ $invoices->count() }} invoice{{ $invoices->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="width:110px">Invoice No.</th>
            <th>Customer</th>
            <th style="width:90px">Invoice Date</th>
            <th style="width:90px">Due Date</th>
            <th class="text-right" style="width:110px">Total Amount</th>
            <th class="text-right" style="width:110px">Amount Due</th>
            <th style="width:70px">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoices as $i => $invoice)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $invoice->invoice_number }}</td>
            <td>{{ $invoice->customer->customer_name }}</td>
            <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
            <td>{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}</td>
            <td class="text-right">{{ number_format($invoice->total_amount, 2) }}</td>
            <td class="text-right">{{ number_format($invoice->amount_due, 2) }}</td>
            <td class="text-center">
                @php
                    $sc = match($invoice->status) {
                        'Paid' => 'success', 'Partial' => 'warning',
                        'Overdue' => 'danger', 'Sent' => 'info',
                        'Cancelled' => 'secondary', default => 'secondary',
                    };
                @endphp
                <span class="badge badge-{{ $sc }}">{{ $invoice->status }}</span>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="no-data">No invoices found.</td></tr>
        @endforelse
    </tbody>
    @if($invoices->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="5" class="text-right">Totals</td>
            <td class="text-right">{{ number_format($invoices->sum('total_amount'), 2) }}</td>
            <td class="text-right">{{ number_format($invoices->sum('amount_due'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $invoices->count() }} records
</div>

</body>
</html>
