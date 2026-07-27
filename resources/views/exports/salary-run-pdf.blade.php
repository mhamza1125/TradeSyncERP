<!DOCTYPE html>
<html lang="en">
<head>
<title>Salary Run — {{ $salaryRun->month }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Salary Statement</div>
                <div class="db-sub">{{ $salaryRun->month }} &mdash; Processed by {{ optional($salaryRun->processedBy)->name }}</div>
            </td>
            <td class="db-right">
                @php $statusColor = $salaryRun->isPaid() ? '#d4edda' : '#fff3cd'; $statusText = $salaryRun->isPaid() ? '#155724' : '#856404'; @endphp
                <span style="background:{{ $statusColor }}; color:{{ $statusText }}; padding:3px 8px; border-radius:3px; font-size:8pt; font-weight:bold;">
                    {{ $salaryRun->status }}
                </span>
                <div class="db-date" style="margin-top:6px;">Generated: {{ $salaryRun->created_at->format('d M Y') }}</div>
                @if($salaryRun->payment_date)
                <div class="db-date">Paid: {{ $salaryRun->payment_date->format('d M Y') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

{{-- Run Summary --}}
<table class="two-col" style="margin-bottom:14px;">
    <tr>
        <td>
            <table class="info-grid">
                <tr><td class="info-label">Period</td><td class="info-value">{{ $salaryRun->month }}</td></tr>
                <tr><td class="info-label">Working Days</td><td class="info-value">{{ $salaryRun->working_days ?? '—' }}</td></tr>
                <tr><td class="info-label">Off Days</td><td class="info-value">{{ $salaryRun->off_days ?? '—' }}</td></tr>
                @if($salaryRun->remarks)
                <tr><td class="info-label">Remarks</td><td class="info-value">{{ $salaryRun->remarks }}</td></tr>
                @endif
            </table>
        </td>
        <td>
            <table class="info-grid">
                <tr><td class="info-label">Pay Account</td><td class="info-value">{{ optional($salaryRun->account)->account_name ?? '—' }}</td></tr>
                <tr><td class="info-label">Employees</td><td class="info-value">{{ $salaryRun->lines->count() }}</td></tr>
                <tr>
                    <td class="info-label">Total Net Payable</td>
                    <td class="info-value" style="font-size:12pt; color:#1a3560;">{{ number_format($salaryRun->total_net_payable, 2) }} PKR</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- Salary Lines: one stacked card per employee — a wide 11-field row never fits a
     printable page cleanly, so each record gets a header + a 4-column key/value grid. --}}
<div class="info-section">
    <h3>Employee Salary Details</h3>

    @foreach($salaryRun->lines as $i => $line)
    <div class="record-card no-break">
        <div class="record-card-header">
            <table>
                <tr>
                    <td class="rch-title">{{ $i + 1 }}. {{ optional($line->employee)->employee_name ?? 'Deleted Employee' }}</td>
                    <td class="rch-value">Net Pay: {{ number_format($line->net_payable, 0) }} PKR</td>
                </tr>
            </table>
        </div>
        <table class="kv-grid">
            <tr>
                <td><span class="kv-label">Basic</span><span class="kv-value">{{ number_format($line->basic_salary, 0) }}</span></td>
                <td><span class="kv-label">Bonus</span><span class="kv-value">{{ number_format($line->bonus, 0) }}</span></td>
                <td><span class="kv-label">Allowances</span><span class="kv-value">{{ number_format($line->allowances, 0) }}</span></td>
                <td><span class="kv-label">Deduction</span><span class="kv-value">{{ number_format($line->deduction, 0) }}</span></td>
            </tr>
            <tr>
                <td><span class="kv-label">Advance</span><span class="kv-value">{{ number_format($line->advance, 0) }}</span></td>
                <td><span class="kv-label">Leave Ded.</span><span class="kv-value">{{ number_format($line->leave_deduction_amount, 0) }}</span></td>
                <td><span class="kv-label">Loan Ded.</span><span class="kv-value">{{ number_format($line->loan_deduction, 0) }}</span></td>
                <td><span class="kv-label">Late Ded.</span><span class="kv-value">{{ number_format($line->late_deduction ?? 0, 0) }}</span></td>
            </tr>
        </table>
        @if($line->lineAllowances->count())
        <div style="padding:0 10px 6px; font-size:6.5pt; color:#757575;">
            Allowance breakdown:
            @foreach($line->lineAllowances as $la)
            {{ $la->allowanceType?->name ?? '—' }}: {{ number_format($la->amount, 0) }}{{ !$loop->last ? ', ' : '' }}
            @endforeach
        </div>
        @endif
    </div>
    @endforeach

    <div class="summary-box no-break">
        <table>
            <tr>
                <td>Total Employees</td>
                <td class="text-right">{{ $salaryRun->lines->count() }}</td>
            </tr>
            <tr>
                <td>Total Basic</td>
                <td class="text-right">{{ number_format($salaryRun->lines->sum('basic_salary'), 0) }}</td>
            </tr>
            <tr>
                <td>Total Bonus</td>
                <td class="text-right">{{ number_format($salaryRun->lines->sum('bonus'), 0) }}</td>
            </tr>
            <tr>
                <td>Total Allowances</td>
                <td class="text-right">{{ number_format($salaryRun->lines->sum('allowances'), 0) }}</td>
            </tr>
            <tr>
                <td>Total Deductions (advance, leave, loan, late)</td>
                <td class="text-right">{{ number_format($salaryRun->lines->sum('deduction') + $salaryRun->lines->sum('advance') + $salaryRun->lines->sum('leave_deduction_amount') + $salaryRun->lines->sum('loan_deduction') + $salaryRun->lines->sum('late_deduction'), 0) }}</td>
            </tr>
            <tr class="summary-row-total">
                <td>Grand Total Net Payable</td>
                <td class="text-right">{{ number_format($salaryRun->total_net_payable, 0) }} PKR</td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
