<!DOCTYPE html>
<html lang="en">
<head>
<title>Salary Runs</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header', ['reportTitle' => 'Salary Runs'])

@include('exports.partials._pdf-company-footer', ['centerText' => 'Salary Runs'])

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Salary Runs</div>
                <div class="db-sub">{{ $salaryRuns->count() }} run{{ $salaryRuns->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="width:90px">Period</th>
            <th style="width:80px">Working Days</th>
            <th style="width:110px">Pay Account</th>
            <th class="text-right" style="width:70px">Employees</th>
            <th class="text-right" style="width:130px">Total Net Payable</th>
            <th style="width:90px">Payment Date</th>
            <th style="width:60px">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($salaryRuns as $i => $run)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $run->month }}</td>
            <td class="text-center">{{ $run->working_days ?? '—' }}</td>
            <td class="text-muted">{{ $run->account?->account_name ?? '—' }}</td>
            <td class="text-right">{{ $run->lines_count ?? $run->lines->count() }}</td>
            <td class="text-right fw-bold">{{ number_format($run->total_net_payable, 2) }}</td>
            <td>{{ $run->payment_date ? \Carbon\Carbon::parse($run->payment_date)->format('d M Y') : '—' }}</td>
            <td class="text-center">
                <span class="badge {{ $run->isPaid() ? 'badge-success' : 'badge-warning' }}">{{ $run->status }}</span>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="no-data">No salary runs found.</td></tr>
        @endforelse
    </tbody>
    @if($salaryRuns->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="5" class="text-right">Grand Total</td>
            <td class="text-right">{{ number_format($salaryRuns->sum('total_net_payable'), 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
    @endif
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $salaryRuns->count() }} records
</div>

</body>
</html>
