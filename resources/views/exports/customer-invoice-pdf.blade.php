<!DOCTYPE html>
<html lang="en">
<head>
<title>Invoice — {{ $invoice->invoice_number }}</title>
@include('exports.partials._pdf-head')
<style>
    .invoice-to { font-size: 9.5pt; }
    .invoice-to .name { font-size: 12pt; font-weight: bold; color: #1a3560; }
    .totals-box { width: 55%; margin-left: auto; border-collapse: collapse; margin-top: 12px; }
    .totals-box td { padding: 4px 8px; font-size: 9pt; border-bottom: 1px solid #e9ecef; }
    .totals-box td:last-child { text-align: right; font-weight: bold; }
    .totals-box .grand-row td { font-size: 11pt; font-weight: bold; color: #1a3560; border-top: 2px solid #1a3560; border-bottom: none; background: #eef2ff; }
    .paid-row td { color: #155724; }
    .due-row td  { color: #721c24; }
    .words-row td { font-size: 7.5pt; font-style: italic; color: #757575; border-bottom: none; }
</style>
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Tax Invoice</div>
                <div class="db-sub">TradeSyncERP &mdash; Quality Control &amp; ERP</div>
            </td>
            <td class="db-right">
                <div class="db-code">{{ $invoice->invoice_number }}</div>
                <div class="db-date">{{ $invoice->invoice_date->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Bill To + Invoice Meta --}}
<table class="two-col" style="margin-bottom:16px;">
    <tr>
        <td>
            <p style="font-size:7.5pt; text-transform:uppercase; color:#757575; margin-bottom:4px;">Bill To:</p>
            <div class="invoice-to">
                <div class="name">{{ $invoice->customer->customer_name }}</div>
                @if($invoice->customer->contact_person)
                <div>{{ $invoice->customer->contact_person }}</div>
                @endif
                @if($invoice->customer->phone)
                <div style="color:#757575;">{{ $invoice->customer->phone }}</div>
                @endif
                @if($invoice->customer->address)
                <div style="color:#757575;">{{ $invoice->customer->address }}</div>
                @endif
            </div>
        </td>
        <td>
            <table class="info-grid">
                <tr>
                    <td class="info-label">Invoice Number</td>
                    <td class="info-value">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td class="info-label">Invoice Date</td>
                    <td class="info-value">{{ $invoice->invoice_date->format('d M Y') }}</td>
                </tr>
                @if($invoice->due_date)
                <tr>
                    <td class="info-label">Due Date</td>
                    <td class="info-value" style="{{ $invoice->isOverdue() ? 'color:#721c24;' : '' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                    </td>
                </tr>
                @endif
                <tr>
                    <td class="info-label">Status</td>
                    <td class="info-value">
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
                @if($invoice->customer->currency)
                <tr>
                    <td class="info-label">Currency</td>
                    <td class="info-value">{{ $invoice->customer->currency->currency_code }}</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

{{-- Line Items --}}
<div class="info-section">
    <h3>Invoice Items</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Description / Supplier</th>
                <th style="width:120px">Service Type</th>
                <th style="width:90px">PO / Inv No.</th>
                <th style="width:80px">Date</th>
                <th class="text-right" style="width:110px">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    @if($item->supplier)
                    <span class="fw-bold">{{ $item->supplier->name }}</span>
                    @endif
                    @if($item->description)
                    <div class="text-muted" style="font-size:7.5pt;">{{ $item->description }}</div>
                    @endif
                </td>
                <td class="text-muted">{{ $item->inspectionType->name ?? '—' }}</td>
                <td class="text-muted">{{ $item->po_invoice_no ?? '—' }}</td>
                <td>{{ $item->item_date ? $item->item_date->format('d M Y') : '—' }}</td>
                <td class="text-right fw-bold">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="no-data">No line items.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Totals --}}
<table class="totals-box">
    <tr>
        <td>Subtotal</td>
        <td>{{ number_format($invoice->subtotal, 2) }}</td>
    </tr>
    @if($invoice->tax_amount > 0)
    <tr>
        <td>Tax</td>
        <td>{{ number_format($invoice->tax_amount, 2) }}</td>
    </tr>
    @endif
    @if($invoice->discount_amount > 0)
    <tr>
        <td>Discount</td>
        <td style="color:#721c24;">- {{ number_format($invoice->discount_amount, 2) }}</td>
    </tr>
    @endif
    <tr class="grand-row">
        <td>Total</td>
        <td>{{ number_format($invoice->total_amount, 2) }}</td>
    </tr>
    @php
        $currCode = $invoice->customer->currency?->currency_code ?? 'PKR';
        $words = \App\Helpers\NumberToWords::convert(
            (float) $invoice->total_amount,
            \App\Helpers\NumberToWords::currencyName($currCode),
            \App\Helpers\NumberToWords::subunitName($currCode)
        );
    @endphp
    <tr class="words-row">
        <td colspan="2">{{ $words }}</td>
    </tr>
    @if($invoice->amount_paid > 0)
    <tr class="paid-row">
        <td>Amount Paid</td>
        <td>{{ number_format($invoice->amount_paid, 2) }}</td>
    </tr>
    @endif
    @if($invoice->amount_due > 0)
    <tr class="due-row">
        <td>Amount Due</td>
        <td>{{ number_format($invoice->amount_due, 2) }}</td>
    </tr>
    @endif
</table>

@if($invoice->foreign_amount && $invoice->customer->currency)
<div class="info-section" style="margin-top:16px;">
    <h3>Foreign Currency Details</h3>
    <table class="info-grid" style="width:50%;">
        <tr>
            <td class="info-label">Currency</td>
            <td class="info-value">{{ $invoice->customer->currency->currency_code }}</td>
        </tr>
        <tr>
            <td class="info-label">Exchange Rate</td>
            <td class="info-value">{{ $invoice->exchange_rate }}</td>
        </tr>
        <tr>
            <td class="info-label">Foreign Amount</td>
            <td class="info-value">{{ number_format($invoice->foreign_amount, 2) }} {{ $invoice->customer->currency->currency_code }}</td>
        </tr>
    </table>
</div>
@endif

@if($invoice->remarks)
<div class="info-section" style="margin-top:14px;">
    <h3>Remarks</h3>
    <p style="font-size:8.5pt; color:#424242;">{{ $invoice->remarks }}</p>
</div>
@endif

</body>
</html>
