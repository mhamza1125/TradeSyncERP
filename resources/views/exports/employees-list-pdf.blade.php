<!DOCTYPE html>
<html lang="en">
<head>
<title>Employee Record Directory</title>
@include('exports.partials._pdf-head')
</head>
<body>

@include('exports.partials._pdf-company-header')

@include('exports.partials._pdf-company-footer')

<div class="doc-banner">
    <table>
        <tr>
            <td>
                <div class="db-title">Employee Record Directory</div>
                <div class="db-sub">{{ $employees->count() }} employee{{ $employees->count() !== 1 ? 's' : '' }} listed</div>
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
            <th style="width:4%">#</th>
            <th style="width:16%">Employee Name</th>
            <th style="width:12%">Department</th>
            <th style="width:13%">Designation</th>
            <th style="width:11%">Type</th>
            <th style="width:12%">Phone</th>
            <th style="width:11%">Joining Date</th>
            <th class="text-right" style="width:13%">Basic Salary</th>
            <th style="width:12%; text-align:center;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($employees as $i => $emp)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="fw-bold">{{ Illuminate\Support\Str::limit($emp->employee_name, 20) }}</td>
            <td>{{ Illuminate\Support\Str::limit($emp->department ?? '—', 15) }}</td>
            <td>{{ Illuminate\Support\Str::limit($emp->designation ?? '—', 17) }}</td>
            <td>{{ $emp->employee_type ? ucfirst(strtolower($emp->employee_type)) : '—' }}</td>
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
        <tr><td colspan="9" class="no-data">No employee records found.</td></tr>
        @endforelse
    </tbody>
    @if($employees->count())
    <tfoot>
        <tr>
            <td colspan="7" class="text-right">Total Basic Salary</td>
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
