@extends('index')

@section('title', 'Customers - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Customers</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Customers</li>
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
                    @can('customers.create')
                    <a href="{{ route('masters.customers.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Add Customer</span>
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

    <!-- [ filter collapse ] -->
    <div id="collapseFilters" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('masters.customers.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or phone..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" @selected(request('status') === '1')>Active</option>
                            <option value="0" @selected(request('status') === '0')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="feather-search me-1"></i> Search
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('masters.customers.index') }}" class="btn btn-light-brand w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="customerList">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Contact Person</th>
                                        <th>Phone</th>
                                        <th>Currency</th>
                                        <th>Opening Balance</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customers as $customer)
                                    <tr class="single-item">
                                        <td>
                                            <a href="{{ route('masters.customers.show', $customer) }}" class="hstack gap-3">
                                                <div class="avatar-text avatar-md bg-soft-primary text-primary">
                                                    {{ strtoupper(substr($customer->customer_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-semibold d-block text-truncate-1-line">{{ $customer->customer_name }}</span>
                                                    <small class="fs-12 fw-normal text-muted">{{ $customer->email }}</small>
                                                </div>
                                            </a>
                                        </td>
                                        <td>{{ $customer->contact_person }}</td>
                                        <td>{{ $customer->phone }}</td>
                                        <td>{{ $customer->default_currency }}</td>
                                        <td class="fw-semibold text-dark">
                                            {{ number_format($customer->opening_balance, 2) }} {{ $customer->opening_balance_currency }}
                                        </td>
                                        <td>
                                            @if($customer->status)
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('customers.index')
                                                <a href="{{ route('masters.customers.show', $customer) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('customers.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('masters.customers.edit', $customer) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('customers.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('masters.customers.destroy', $customer) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this customer?')">
                                                                @csrf
                                                                @method('DELETE')
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
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="feather-users fs-1 d-block mb-2"></i>
                                            No customers found.
                                            @can('customers.create')
                                            <a href="{{ route('masters.customers.create') }}">Add one now.</a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($customers->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }}</small>
                        {{ $customers->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>
@endsection
