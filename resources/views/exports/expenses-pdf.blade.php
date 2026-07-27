<!DOCTYPE html>
<html lang="en">
<head>
<title>Expense Report</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Expense Report</div>
                <div class="db-sub">{{ $expenses->count() }} record{{ $expenses->count() !== 1 ? 's' : '' }}</div>
            </td>
            <td class="db-right">
                <div class="db-date">{{ now()->format('d M Y') }}</div>
            </td>
        </tr>
    </table>
</div>

@if(!empty($filters))
<div class="info-section">
    <h3>Applied Filters</h3>
    <table class="info-grid" style="width:60%;">
        @foreach($filters as $label => $value)
        <tr>
            <td class="info-label">{{ $label }}</td>
            <td class="info-value">{{ $value }}</td>
        </tr>
        @endforeach
    </table>
</div>
@endif

<table class="data-table data-table-fixed">
    <thead>
        <tr>
            <th style="width:4%">#</th>
            <th style="width:10%">Date</th>
            <th style="width:17%">Expense Type</th>
            <th style="width:15%">Account</th>
            <th class="text-right" style="width:12%">Amount</th>
            <th style="width:30%">Description</th>
            <th style="width:12%">Created By</th>
        </tr>
    </thead>
    <tbody>
        @forelse($expenses as $i => $expense)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
            <td class="fw-bold">{{ Illuminate\Support\Str::limit($expense->expenseHead->expense_name ?? '—', 20) }}</td>
            <td>{{ Illuminate\Support\Str::limit($expense->account->account_name ?? '—', 18) }}</td>
            <td class="text-right fw-bold">{{ number_format($expense->amount, 2) }}</td>
            <td class="text-muted">{{ Illuminate\Support\Str::limit($expense->description ?? '—', 55) }}</td>
            <td>{{ Illuminate\Support\Str::limit($expense->transaction?->creator?->name ?? '—', 16) }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="no-data">No expenses found.</td></tr>
        @endforelse
    </tbody>
    @if($expenses->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="4" class="text-right">Grand Total</td>
            <td class="text-right">{{ number_format($total, 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
    @endif
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $expenses->count() }} records
</div>

</body>
</html>
