<!DOCTYPE html>
<html lang="en">
<head>
<title>Employee Profile — {{ $employee->employee_name }}</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Employee Profile</div>
                <div class="db-sub">{{ $employee->department ?? '' }}{{ $employee->department && $employee->designation ? ' · ' : '' }}{{ $employee->designation ?? '' }}</div>
            </td>
            <td class="db-right">
                @if($employee->status)
                    <span style="background:#d4edda; color:#155724; padding:3px 8px; border-radius:3px; font-size:8pt; font-weight:bold;">Active</span>
                @else
                    <span style="background:#f8d7da; color:#721c24; padding:3px 8px; border-radius:3px; font-size:8pt; font-weight:bold;">Inactive</span>
                @endif
                @if($employee->joining_date)
                <div class="db-date" style="margin-top:6px;">Joined: {{ $employee->joining_date->format('d M Y') }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>

<table class="two-col">
    <tr>
        <td>
            <div class="info-section">
                <h3>Personal Information</h3>
                <table class="info-grid">
                    <tr><td class="info-label">Full Name</td><td class="info-value">{{ $employee->employee_name }}</td></tr>
                    @if($employee->father_name)
                    <tr><td class="info-label">Father's Name</td><td class="info-value">{{ $employee->father_name }}</td></tr>
                    @endif
                    <tr><td class="info-label">Gender</td><td class="info-value">{{ $employee->gender ?? '—' }}</td></tr>
                    <tr><td class="info-label">Marital Status</td><td class="info-value">{{ $employee->marital_status ?? '—' }}</td></tr>
                    @if($employee->dob)
                    <tr><td class="info-label">Date of Birth</td><td class="info-value">{{ $employee->dob->format('d M Y') }}</td></tr>
                    @endif
                    <tr><td class="info-label">NIC</td><td class="info-value">{{ $employee->nic ?? '—' }}</td></tr>
                    <tr><td class="info-label">Phone</td><td class="info-value">{{ $employee->phone ?? '—' }}</td></tr>
                    @if($employee->emergency_contact)
                    <tr><td class="info-label">Emergency Contact</td><td class="info-value">{{ $employee->emergency_contact }}</td></tr>
                    @endif
                    <tr><td class="info-label">Address</td><td class="info-value">{{ $employee->address ?? '—' }}{{ $employee->city ? ', '.$employee->city : '' }}{{ $employee->country ? ', '.$employee->country : '' }}</td></tr>
                </table>
            </div>
        </td>
        <td>
            <div class="info-section">
                <h3>Employment Details</h3>
                <table class="info-grid">
                    <tr><td class="info-label">Department</td><td class="info-value">{{ $employee->department ?? '—' }}</td></tr>
                    <tr><td class="info-label">Designation</td><td class="info-value">{{ $employee->designation ?? '—' }}</td></tr>
                    <tr><td class="info-label">Employee Type</td><td class="info-value">{{ $employee->employee_type ? ucfirst(strtolower($employee->employee_type)) : '—' }}</td></tr>
                    @if($employee->joining_date)
                    <tr><td class="info-label">Joining Date</td><td class="info-value">{{ $employee->joining_date->format('d M Y') }}</td></tr>
                    @endif
                    <tr><td class="info-label">Basic Salary</td><td class="info-value" style="font-size:11pt; color:#1a3560;">{{ number_format($employee->basic_salary, 2) }} PKR</td></tr>
                    @if($employee->remarks)
                    <tr><td class="info-label">Remarks</td><td class="info-value">{{ $employee->remarks }}</td></tr>
                    @endif
                </table>
            </div>
        </td>
    </tr>
</table>

@if($salaryHistory->count())
<div class="info-section">
    <h3>Recent Salary History</h3>
    <table class="data-table data-table-fixed">
        <thead>
            <tr>
                <th style="width:16%">Period</th>
                <th class="text-right" style="width:17%">Basic Salary</th>
                <th class="text-right" style="width:17%">Allowances</th>
                <th class="text-right" style="width:17%">Deductions</th>
                <th class="text-right" style="width:17%">Net Pay</th>
                <th style="width:16%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salaryHistory->take(12) as $line)
            <tr>
                <td>{{ optional($line->salaryRun)->month ?? '—' }}</td>
                <td class="text-right">{{ number_format($line->basic_salary, 0) }}</td>
                <td class="text-right">{{ number_format($line->allowances, 0) }}</td>
                <td class="text-right">{{ number_format(($line->deduction + $line->advance + $line->leave_deduction_amount + $line->loan_deduction + ($line->late_deduction ?? 0)), 0) }}</td>
                <td class="text-right fw-bold">{{ number_format($line->net_payable, 0) }}</td>
                <td>{{ optional($line->salaryRun)->status ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

</body>
</html>
