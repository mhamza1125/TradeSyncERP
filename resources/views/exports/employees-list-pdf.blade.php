<!DOCTYPE html>
<html lang="en">
<head>
<title>Employee Directory</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Employee Directory</div>
                <div class="db-sub">{{ $employees->count() }} employee{{ $employees->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="min-width:130px">Employee Name</th>
            <th style="width:100px">Department</th>
            <th style="width:110px">Designation</th>
            <th style="width:100px">Phone</th>
            <th style="width:80px">Joining Date</th>
            <th class="text-right" style="width:90px">Basic Salary</th>
            <th style="width:55px; text-align:center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($employees as $i => $emp)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ $emp->employee_name }}</td>
            <td>{{ $emp->department ?? '—' }}</td>
            <td>{{ $emp->designation ?? $emp->job_title ?? '—' }}</td>
            <td>{{ $emp->phone ?? '—' }}</td>
            <td>{{ $emp->joining_date ? $emp->joining_date->format('d M Y') : '—' }}</td>
            <td class="text-right">{{ number_format($emp->basic_salary, 0) }}</td>
            <td class="text-center">
                @if($emp->status)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="no-data">No employees found.</td></tr>
        @endforelse
    </tbody>
    @if($employees->count())
    <tfoot>
        <tr>
            <td colspan="6" class="text-right">Total Basic Salary</td>
            <td class="text-right">{{ number_format($employees->sum('basic_salary'), 0) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

<div style="margin-top:10px; font-size:7.5pt; color:#9e9e9e; text-align:right;">
    Total: {{ $employees->count() }} records
</div>

</body>
</html>
