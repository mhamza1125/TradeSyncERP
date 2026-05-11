@extends('index')

@section('title', 'Employees - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Employees</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Employees</li>
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
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                        <i class="feather-filter"></i>
                    </a>
                    @can('employees.create')
                    <a href="{{ route('masters.employees.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Add Employee</span>
                    </a>
                    @endcan
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <div id="collapseFilters" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('masters.employees.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or department..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" @selected(request('status') === '1')>Active</option>
                            <option value="0" @selected(request('status') === '0')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search me-1"></i> Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('masters.employees.index') }}" class="btn btn-light-brand w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="employeeList">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Phone</th>
                                        <th>Joining Date</th>
                                        <th>Basic Salary</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employees as $employee)
                                    <tr class="single-item">
                                        <td>
                                            <a href="{{ route('masters.employees.show', $employee) }}" class="hstack gap-3">
                                                <div class="avatar-text avatar-md bg-soft-teal text-teal">
                                                    {{ strtoupper(substr($employee->employee_name, 0, 1)) }}
                                                </div>
                                                <span class="fw-semibold">{{ $employee->employee_name }}</span>
                                            </a>
                                        </td>
                                        <td>{{ $employee->department }}</td>
                                        <td>{{ $employee->designation }}</td>
                                        <td>{{ $employee->phone }}</td>
                                        <td>{{ \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') }}</td>
                                        <td class="fw-semibold text-dark">{{ number_format($employee->basic_salary, 2) }}</td>
                                        <td>
                                            @if($employee->status)
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('employees.index')
                                                <a href="{{ route('masters.employees.show', $employee) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('employees.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('masters.employees.edit', $employee) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('employees.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('masters.employees.destroy', $employee) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this employee?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Delete</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="feather-briefcase fs-1 d-block mb-2"></i>
                                            No employees found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($employees->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}</small>
                        {{ $employees->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
