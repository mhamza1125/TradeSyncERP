<!DOCTYPE html>
<html lang="en">
<head>
<title>Expense Heads</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Expense Heads'])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Expense Heads'])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Expense Heads</div>
                <div class="db-sub">{{ $expenseHeads->count() }} record{{ $expenseHeads->count() !== 1 ? 's' : '' }} listed</div>
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
            <th>Expense Head Name</th>
            <th style="width:160px">Parent Category</th>
            <th style="width:70px">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($expenseHeads as $i => $head)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="{{ $head->parent_id ? '' : 'fw-bold' }}">
                {{ $head->parent_id ? '↳ ' : '' }}{{ $head->expense_name }}
            </td>
            <td class="text-muted">{{ $head->parent?->expense_name ?? '—' }}</td>
            <td class="text-center">
                <span class="badge {{ $head->status ? 'badge-success' : 'badge-danger' }}">
                    {{ $head->status ? 'Active' : 'Inactive' }}
                </span>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="no-data">No expense heads found.</td></tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $expenseHeads->count() }} records
</div>

</body>
</html>
