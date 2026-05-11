@extends('index')

@section('title', 'Vendor Bills - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Vendor Bills</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Vendor Bills</li>
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
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseStats">
                        <i class="feather-bar-chart"></i>
                    </a>
                    @can('vendor-bills.create')
                    <a href="{{ route('vendor-bills.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>New Bill</span>
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

    {{-- Stats --}}
    <div id="collapseStats" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <div class="row">
                @foreach(['Unpaid'=>'warning','Paid'=>'success','Partial'=>'info','Overdue'=>'danger'] as $status => $color)
                <div class="col-xxl-3 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <a href="{{ route('vendor-bills.index', ['status' => $status]) }}" class="fw-bold d-block">
                                    <span class="d-block">{{ $status }}</span>
                                    <span class="fs-20 fw-bold d-block">{{ $bills->where('status', $status)->count() }}</span>
                                </a>
                                <div class="badge bg-soft-{{ $color }} text-{{ $color }}">Bills</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div id="collapseFilters" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('vendor-bills.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="vendor_id" class="form-select">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" @selected(request('vendor_id') == $vendor->id)>{{ $vendor->vendor_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach(['Unpaid','Paid','Partial','Overdue'] as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To date">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('vendor-bills.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover" id="billList">
                                <thead>
                                    <tr>
                                        <th>Bill #</th>
                                        <th>Vendor</th>
                                        <th>Bill Date</th>
                                        <th>Due Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bills as $bill)
                                    @php $statusColors = ['Paid'=>'success','Unpaid'=>'warning','Partial'=>'info','Overdue'=>'danger']; @endphp
                                    <tr class="single-item">
                                        <td>
                                            <a href="{{ route('vendor-bills.show', $bill) }}" class="fw-bold text-primary">
                                                {{ $bill->bill_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="hstack gap-3">
                                                <div class="avatar-text avatar-md bg-soft-warning text-warning">
                                                    {{ strtoupper(substr($bill->vendor->vendor_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="text-truncate-1-line">{{ $bill->vendor->vendor_name }}</span>
                                                    <small class="fs-12 fw-normal text-muted d-block">{{ $bill->vendor->company_name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($bill->bill_date)->format('d M Y') }}</td>
                                        <td>
                                            @if($bill->due_date)
                                                @php $overdue = \Carbon\Carbon::parse($bill->due_date)->isPast() && $bill->status !== 'Paid'; @endphp
                                                <span class="{{ $overdue ? 'text-danger fw-semibold' : '' }}">
                                                    {{ \Carbon\Carbon::parse($bill->due_date)->format('d M Y') }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="fw-bold text-dark">{{ number_format($bill->total_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-soft-{{ $statusColors[$bill->status] ?? 'secondary' }} text-{{ $statusColors[$bill->status] ?? 'secondary' }}">
                                                {{ $bill->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('vendor-bills.index')
                                                <a href="{{ route('vendor-bills.show', $bill) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('vendor-bills.edit')
                                                        @if($bill->status !== 'Paid')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('vendor-bills.edit', $bill) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endif
                                                        @endcan
                                                        @can('vendor-bills.delete')
                                                        @if($bill->status !== 'Paid')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('vendor-bills.destroy', $bill) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this bill?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Delete</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endif
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="feather-file-text fs-1 d-block mb-2"></i>
                                            No bills found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($bills->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $bills->firstItem() }}–{{ $bills->lastItem() }} of {{ $bills->total() }}</small>
                        {{ $bills->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
