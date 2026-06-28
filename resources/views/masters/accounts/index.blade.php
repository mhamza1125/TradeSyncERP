@extends('index')

@section('title', 'Accounts - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Accounts</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Accounts</li>
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
                    @can('accounts.index')
                    <a href="{{ route('masters.accounts.export-pdf', request()->query()) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i><span>Export PDF</span>
                    </a>
                    @endcan
                    @can('accounts.create')
                    <a href="{{ route('masters.accounts.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Add Account</span>
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
            <form method="GET" action="{{ route('masters.accounts.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search account..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="account_type" class="form-select">
                            <option value="">All Types</option>
                            @foreach(['Cash','Bank','Receivable','Payable','Equity','Expense','Revenue'] as $type)
                            <option value="{{ $type }}" @selected(request('account_type') == $type)>{{ $type }}</option>
                            @endforeach
                        </select>
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
                        <a href="{{ route('masters.accounts.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover" id="accountList">
                                <thead>
                                    <tr>
                                        <th>Account Name</th>
                                        <th>Type</th>
                                        <th>Bank</th>
                                        <th>Currency</th>
                                        <th>Opening Balance</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accounts as $account)
                                    @php
                                        $typeColors = ['Cash'=>'success','Bank'=>'primary','Receivable'=>'info','Payable'=>'warning','Expense'=>'danger','Revenue'=>'success','Equity'=>'secondary'];
                                        $typeColor = $typeColors[$account->account_type] ?? 'secondary';
                                    @endphp
                                    <tr class="single-item">
                                        <td class="fw-semibold text-dark">{{ $account->account_name }}</td>
                                        <td>
                                            <span class="badge bg-soft-{{ $typeColor }} text-{{ $typeColor }}">
                                                {{ $account->account_type }}
                                            </span>
                                        </td>
                                        <td>{{ optional($account->bank)->bank_name ?? '—' }}</td>
                                        <td>{{ $account->currency ?? '—' }}</td>
                                        <td class="fw-semibold">{{ number_format($account->opening_balance, 2) }}</td>
                                        <td>
                                            @if($account->status)
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('accounts.index')
                                                <a href="{{ route('masters.accounts.show', $account) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('accounts.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('masters.accounts.edit', $account) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('accounts.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('masters.accounts.destroy', $account) }}" method="POST"
                                                                  onsubmit="return confirm('Deactivate this account?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Deactivate</span>
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
                                            <i class="feather-briefcase fs-1 d-block mb-2"></i>
                                            No accounts found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($accounts->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $accounts->firstItem() }}–{{ $accounts->lastItem() }} of {{ $accounts->total() }}</small>
                        {{ $accounts->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
