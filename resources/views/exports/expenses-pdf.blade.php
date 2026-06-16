<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: sans-serif; font-size: 12px; color: #1a1a2e; }

        .page-header { padding: 20px 0 12px; border-bottom: 2px solid #1a1a2e; margin-bottom: 16px; }
        .page-header h1 { font-size: 20px; font-weight: 700; color: #1a1a2e; }
        .page-header .meta { font-size: 11px; color: #6c757d; margin-top: 4px; }

        .filters { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;
                   padding: 10px 14px; margin-bottom: 16px; }
        .filters h3 { font-size: 12px; font-weight: 600; margin-bottom: 6px; color: #495057; }
        .filters ul { list-style: none; display: flex; flex-wrap: wrap; gap: 6px 20px; }
        .filters li { font-size: 11px; color: #495057; }
        .filters li strong { color: #212529; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead th { background: #1a1a2e; color: #fff; font-size: 11px; font-weight: 600;
                   padding: 7px 10px; text-align: left; }
        thead th.text-right { text-align: right; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        tbody td { padding: 6px 10px; font-size: 11px; border-bottom: 1px solid #e9ecef;
                   vertical-align: top; }
        tbody td.text-right { text-align: right; }
        tbody td.amount { font-weight: 600; text-align: right; }
        tfoot td { padding: 8px 10px; font-size: 12px; font-weight: 700;
                   border-top: 2px solid #1a1a2e; background: #f0f4ff; }
        tfoot td.total-label { text-align: right; }
        tfoot td.total-amount { text-align: right; color: #1a1a2e; }

        .badge { display: inline-block; padding: 2px 7px; border-radius: 20px;
                 font-size: 10px; font-weight: 600; background: #e8eaf6; color: #3949ab; }
        .text-muted { color: #6c757d; }
        .no-data { text-align: center; padding: 30px; color: #6c757d; }
        .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #dee2e6;
                  font-size: 10px; color: #adb5bd; text-align: right; }
    </style>
</head>
<body>

    <div class="page-header">
        <h1>Expense Report</h1>
        <div class="meta">
            TradeSyncERP &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, H:i') }}
        </div>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3>Applied Filters</h3>
        <ul>
            @foreach($filters as $label => $value)
            <li><strong>{{ $label }}:</strong> {{ $value }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:90px">Date</th>
                <th style="width:130px">Expense Type</th>
                <th style="width:110px">Account</th>
                <th class="text-right" style="width:90px">Amount</th>
                <th>Description</th>
                <th style="width:100px">Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                <td>
                    <span class="badge">{{ $expense->expenseHead->expense_name }}</span>
                </td>
                <td>{{ $expense->account->account_name }}</td>
                <td class="amount">{{ number_format($expense->amount, 2) }}</td>
                <td class="text-muted">{{ $expense->description ?? '—' }}</td>
                <td>{{ $expense->transaction?->createdBy?->name ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="no-data">No expenses found matching the applied filters.</td>
            </tr>
            @endforelse
        </tbody>
        @if($expenses->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="3" class="total-label">Grand Total</td>
                <td class="total-amount">{{ number_format($total, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Total records: {{ $expenses->count() }}
    </div>

</body>
</html>
