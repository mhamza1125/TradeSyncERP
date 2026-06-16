@extends('index')

@section('title', 'Expenses - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Expenses</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Expenses</li>
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
                    @can('expenses.index')
                    <a href="{{ route('expenses.export-pdf', request()->query()) }}" class="btn btn-light-brand" title="Export PDF">
                        <i class="feather-download me-2"></i><span>Export PDF</span>
                    </a>
                    @endcan
                    @can('expenses.create')
                    <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Add Expense</span>
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
            <form method="GET" action="{{ route('expenses.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="expense_head_id" class="form-select">
                            <option value="">All Heads</option>
                            @foreach($expenseHeads as $head)
                            <option value="{{ $head->id }}" @selected(request('expense_head_id') == $head->id)>{{ $head->expense_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="account_id" class="form-select">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}" @selected(request('account_id') == $account->id)>{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('expenses.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover" id="expenseList">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Expense Head</th>
                                        <th>Account</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $expense)
                                    <tr class="single-item">
                                        <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary">
                                                {{ $expense->expenseHead->expense_name }}
                                            </span>
                                        </td>
                                        <td>{{ $expense->account->account_name }}</td>
                                        <td class="fw-bold text-dark">{{ number_format($expense->amount, 2) }}</td>
                                        <td class="text-muted text-truncate-1-line" style="max-width:200px">{{ $expense->description ?? '—' }}</td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('expenses.index')
                                                <a href="{{ route('expenses.show', $expense) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                @can('expenses.delete')
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this expense?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Delete</span>
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
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="feather-trending-down fs-1 d-block mb-2"></i>
                                            No expenses found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($expenses->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $expenses->firstItem() }}–{{ $expenses->lastItem() }} of {{ $expenses->total() }}</small>
                        {{ $expenses->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
