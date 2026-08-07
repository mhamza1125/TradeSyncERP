<!DOCTYPE html>
<html lang="en">
<head>
<title>Purchase Orders</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Purchase Orders</div>
                <div class="db-sub">{{ $purchaseOrders->count() }} order{{ $purchaseOrders->count() !== 1 ? 's' : '' }} listed</div>
            </td>
            <td class="db-right">
                <div class="db-date">{{ now()->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

<table class="data-table data-table-fixed">
    <thead>
        <tr>
            <th style="width:6%">#</th>
            <th style="width:22%">PO Number</th>
            <th style="width:16%">Date</th>
            <th class="text-right" style="width:18%">Total</th>
            <th class="text-right" style="width:18%">Paid</th>
            <th style="width:20%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($purchaseOrders as $i => $po)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $po->po_number }}</td>
            <td>{{ $po->po_date->format('d M Y') }}</td>
            <td class="text-right">{{ number_format($po->total_amount, 2) }}</td>
            <td class="text-right">{{ number_format($po->amount_paid, 2) }}</td>
            <td class="text-center">
                @php $sc = match($po->status) { 'Paid'=>'success', 'Partially Paid'=>'warning', default=>'danger' }; @endphp
                <span class="badge badge-{{ $sc }}">{{ $po->status }}</span>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="no-data">No purchase orders found.</td></tr>
        @endforelse
    </tbody>
    @if($purchaseOrders->count())
    <tfoot>
        <tr>
            <td colspan="3" class="text-right">Totals</td>
            <td class="text-right">{{ number_format($purchaseOrders->sum('total_amount'), 2) }}</td>
            <td class="text-right">{{ number_format($purchaseOrders->sum('amount_paid'), 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

</body>
</html>
