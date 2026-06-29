<!DOCTYPE html>
<html lang="en">
<head>
<title>Salary Run — {{ $salaryRun->month }}</title>
@include('exports.partials._pdf-head')
<style>
    @page { size: A4 landscape; margin: 40mm 12mm 28mm 12mm; }
    .pdf-header { top: -36mm; left: -12mm; right: -12mm; padding: 0 12mm; }
    .pdf-footer { bottom: -24mm; left: -12mm; right: -12mm; padding: 3px 12mm 0; }
    .salary-table thead th { font-size: 6.5pt; padding: 4px 5px; }
    .salary-table tbody td { font-size: 7pt; padding: 4px 5px; }
    .salary-table tfoot td { font-size: 7.5pt; padding: 5px; }
    .border-group { border-left: 1px solid #dee2e6; }
    .text-end { text-align: right; }
</style>
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

{{-- Salary Lines --}}
<div class="info-section">
    <h3>Employee Salary Details</h3>
    <table class="data-table salary-table">
        <thead>
            <tr>
                <th style="width:18px">#</th>
                <th style="width:80px">Employee</th>
                <th class="text-end" style="width:60px">Basic</th>
                <th class="text-end" style="width:50px">Bonus</th>
                <th class="text-end" style="width:62px">Allowances</th>
                <th class="text-end" style="width:55px">Deduction</th>
                <th class="text-end" style="width:55px">Advance</th>
                <th class="text-end border-group" style="width:50px">Leave Ded.</th>
                <th class="text-end" style="width:50px">Loan Ded.</th>
                <th class="text-end" style="width:50px">Late Ded.</th>
                <th class="text-end border-group fw-bold" style="width:72px">Net Pay</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salaryRun->lines as $i => $line)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="fw-bold">{{ optional($line->employee)->employee_name ?? 'Deleted' }}</td>
                <td class="text-end">{{ number_format($line->basic_salary, 0) }}</td>
                <td class="text-end">{{ number_format($line->bonus, 0) }}</td>
                <td class="text-end">
                    {{ number_format($line->allowances, 0) }}
                    @if($line->lineAllowances->count())
                    <div class="text-muted" style="font-size:6pt;">
                        @foreach($line->lineAllowances as $la)
                        {{ $la->allowanceType?->name ?? '—' }}: {{ number_format($la->amount, 0) }}<br>
                        @endforeach
                    </div>
                    @endif
                </td>
                <td class="text-end">{{ number_format($line->deduction, 0) }}</td>
                <td class="text-end">{{ number_format($line->advance, 0) }}</td>
                <td class="text-end border-group">{{ number_format($line->leave_deduction_amount, 0) }}</td>
                <td class="text-end">{{ number_format($line->loan_deduction, 0) }}</td>
                <td class="text-end">{{ number_format($line->late_deduction ?? 0, 0) }}</td>
                <td class="text-end border-group fw-bold" style="color:#1a3560;">{{ number_format($line->net_payable, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="fw-bold">Grand Total</td>
                <td class="text-end">{{ number_format($salaryRun->lines->sum('basic_salary'), 0) }}</td>
                <td class="text-end">{{ number_format($salaryRun->lines->sum('bonus'), 0) }}</td>
                <td class="text-end">{{ number_format($salaryRun->lines->sum('allowances'), 0) }}</td>
                <td class="text-end">{{ number_format($salaryRun->lines->sum('deduction'), 0) }}</td>
                <td class="text-end">{{ number_format($salaryRun->lines->sum('advance'), 0) }}</td>
                <td class="text-end">{{ number_format($salaryRun->lines->sum('leave_deduction_amount'), 0) }}</td>
                <td class="text-end">{{ number_format($salaryRun->lines->sum('loan_deduction'), 0) }}</td>
                <td class="text-end">{{ number_format($salaryRun->lines->sum('late_deduction'), 0) }}</td>
                <td class="text-end fw-bold" style="color:#1a3560; font-size:10pt;">{{ number_format($salaryRun->total_net_payable, 0) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>
