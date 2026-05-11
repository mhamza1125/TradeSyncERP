@extends('index')

@section('title', 'Banks - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Banks</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Banks</li>
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
                    @can('banks.create')
                    <a href="{{ route('masters.banks.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Add Bank</span>
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
            <form method="GET" action="{{ route('masters.banks.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search bank..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" @selected(request('status') === '1')>Active</option>
                            <option value="0" @selected(request('status') === '0')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('masters.banks.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover" id="bankList">
                                <thead>
                                    <tr>
                                        <th>Bank Name</th>
                                        <th>Branch</th>
                                        <th>Account Number</th>
                                        <th>SWIFT Code</th>
                                        <th>Accounts</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($banks as $bank)
                                    <tr class="single-item">
                                        <td class="fw-semibold text-dark">{{ $bank->bank_name }}</td>
                                        <td>{{ $bank->branch_name ?? '—' }}</td>
                                        <td>{{ $bank->account_number ?? '—' }}</td>
                                        <td>{{ $bank->swift_code ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary">{{ $bank->accounts_count }} accounts</span>
                                        </td>
                                        <td>
                                            @if($bank->status)
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('banks.edit')
                                                <a href="{{ route('masters.banks.edit', $bank) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="feather feather-edit"></i>
                                                </a>
                                                @endcan
                                                @can('banks.delete')
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form action="{{ route('masters.banks.destroy', $bank) }}" method="POST"
                                                                  onsubmit="return confirm('Deactivate this bank?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Deactivate</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="feather-credit-card fs-1 d-block mb-2"></i>
                                            No banks found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($banks->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $banks->firstItem() }}–{{ $banks->lastItem() }} of {{ $banks->total() }}</small>
                        {{ $banks->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
