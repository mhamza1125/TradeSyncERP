@extends('index')

@section('title', 'Employee: ' . $employee->employee_name . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Employees</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.employees.index') }}">Employees</a></li>
                <li class="breadcrumb-item">{{ $employee->employee_name }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('masters.employees.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <a href="{{ route('masters.employees.export-single-pdf', $employee) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i><span>Export PDF</span>
                    </a>
                    @can('employees.edit')
                    <a href="{{ route('masters.employees.edit', $employee) }}" class="btn btn-primary">
                        <i class="feather-edit me-2"></i><span>Edit</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-xxl-4 col-xl-5">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="mb-4 text-center">
                            <div class="wd-100 ht-100 mx-auto mb-3 avatar-text avatar-lg bg-soft-teal text-teal fs-2 rounded-circle d-flex align-items-center justify-content-center">
                                {{ strtoupper(substr($employee->employee_name, 0, 2)) }}
                            </div>
                            <a href="javascript:void(0);" class="fs-14 fw-bold d-block">{{ $employee->employee_name }}</a>
                            <span class="fs-12 fw-normal text-muted d-block">{{ $employee->designation }}</span>
                            <span class="fs-12 fw-normal text-muted d-block">{{ $employee->department }}</span>
                            @if($employee->status)
                                <span class="badge bg-soft-success text-success mt-2">Active</span>
                            @else
                                <span class="badge bg-soft-danger text-danger mt-2">Inactive</span>
                            @endif
                        </div>
                        <ul class="list-unstyled mb-4">
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-phone"></i>Phone</span>
                                <a href="tel:{{ $employee->phone }}">{{ $employee->phone }}</a>
                            </li>
                            <li class="hstack justify-content-between mb-3">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-calendar"></i>Joined</span>
                                <span>{{ \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') }}</span>
                            </li>
                            <li class="hstack justify-content-between mb-0">
                                <span class="text-muted fw-medium hstack gap-3"><i class="feather-dollar-sign"></i>Basic Salary</span>
                                <span class="fw-semibold">{{ number_format($employee->basic_salary, 2) }}</span>
                            </li>
                        </ul>
                        <div class="d-flex gap-2 pt-4">
                            @can('employees.delete')
                            <form action="{{ route('masters.employees.destroy', $employee) }}" method="POST"
                                  class="w-50" onsubmit="return confirm('Delete this employee?')">
                                @csrf @method('DELETE')
                                <button class="w-100 btn btn-light-brand" type="submit">
                                    <i class="feather-trash-2 me-2"></i>Delete
                                </button>
                            </form>
                            @endcan
                            @can('employees.edit')
                            <a href="{{ route('masters.employees.edit', $employee) }}" class="w-50 btn btn-primary">
                                <i class="feather-edit me-2"></i>Edit
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-8 col-xl-7">
                <div class="card border-top-0">
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs flex-wrap w-100 text-center customers-nav-tabs" role="tablist">
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="tab" data-bs-target="#empOverview" role="tab">Overview</a>
                            </li>
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#empPayments" role="tab">Payment History</a>
                            </li>
                            <li class="nav-item flex-fill border-top" role="presentation">
                                <a href="javascript:void(0);" class="nav-link" data-bs-toggle="tab" data-bs-target="#empActivity" role="tab">Activity Log</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade show active p-4" id="empOverview" role="tabpanel">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <h5 class="fw-bold mb-0">Employee Details:</h5>
                                @can('employees.edit')
                                <a href="{{ route('masters.employees.edit', $employee) }}" class="btn btn-sm btn-light-brand">Edit</a>
                                @endcan
                            </div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Employee Name:</div><div class="col-sm-7 fw-semibold">{{ $employee->employee_name }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Department:</div><div class="col-sm-7 fw-semibold">{{ $employee->department }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Designation:</div><div class="col-sm-7 fw-semibold">{{ $employee->designation }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Phone:</div><div class="col-sm-7 fw-semibold">{{ $employee->phone }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Joining Date:</div><div class="col-sm-7 fw-semibold">{{ \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') }}</div></div>
                            <div class="row g-0 mb-3"><div class="col-sm-5 text-muted">Basic Salary:</div><div class="col-sm-7 fw-semibold">{{ number_format($employee->basic_salary, 2) }}</div></div>
                            <div class="row g-0"><div class="col-sm-5 text-muted">Status:</div><div class="col-sm-7">
                                @if($employee->status)
                                    <span class="badge bg-soft-success text-success">Active</span>
                                @else
                                    <span class="badge bg-soft-danger text-danger">Inactive</span>
                                @endif
                            </div></div>
                        </div>
                        <div class="tab-pane fade" id="empPayments" role="tabpanel">
                            <h5 class="fw-bold mb-4 px-4 pt-4">Payment History:</h5>
                            @php
                                $salaryRows = $salaryHistory->map(fn ($line) => [
                                    'date'   => optional($line->salaryRun->payment_date ?? $line->salaryRun->created_at)->format('d M Y') ?? '—',
                                    'type'   => 'Salary',
                                    'ref'    => 'Month: ' . ($line->salaryRun->month ?? '—'),
                                    'amount' => $line->net_payable ?? 0,
                                ]);
                                $loanRows = $loanTransactions->map(fn ($txn) => [
                                    'date'   => $txn->transaction_date->format('d M Y'),
                                    'type'   => $txn->transaction_type ?? 'Loan',
                                    'ref'    => $txn->remarks ?? '—',
                                    'amount' => $txn->amount,
                                ]);
                                $paymentRows = $salaryRows->merge($loanRows)->sortByDesc('date');
                            @endphp
                            <div class="table-responsive px-4 pb-4">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Reference</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($paymentRows as $row)
                                        <tr>
                                            <td class="text-nowrap">{{ $row['date'] }}</td>
                                            <td>
                                                <span class="badge bg-soft-{{ $row['type'] === 'Salary' ? 'primary' : 'warning' }} text-{{ $row['type'] === 'Salary' ? 'primary' : 'warning' }}">
                                                    {{ $row['type'] }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">{{ $row['ref'] }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($row['amount'], 2) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="feather-inbox fs-1 d-block mb-2"></i>
                                                No payment records found.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if($paymentRows->count())
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="3" class="text-end">Total Paid:</td>
                                            <td class="text-end">{{ number_format($paymentRows->sum('amount'), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade p-4" id="empActivity" role="tabpanel">
                            <h5 class="fw-bold mb-4">Activity Log:</h5>
                            @php $activities = $employee->activities ?? collect(); @endphp
                            @forelse($activities as $activity)
                            <div class="d-flex gap-3 mb-4">
                                <div class="avatar-text avatar-sm bg-soft-teal text-teal flex-shrink-0"><i class="feather-activity"></i></div>
                                <div>
                                    <div class="fw-semibold">{{ $activity->description }}</div>
                                    <small class="text-muted">by {{ optional($activity->causer)->name ?? 'System' }} — {{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @empty
                            <p class="text-muted text-center py-4">No activity recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attachment Panel --}}
        @include('partials.attachment-panel', [
            'attachEntity'     => $employee,
            'attachEntityType' => 'employees',
            'attachLabel'      => 'Employee Documents',
        ])
    </div>
</div>
@endsection
