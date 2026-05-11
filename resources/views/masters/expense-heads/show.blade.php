@extends('index')

@section('title', 'Expense Head - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Expense Heads</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.expense-heads.index') }}">Expense Heads</a></li>
                <li class="breadcrumb-item">{{ $expenseHead->expense_name }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('masters.expense-heads.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    @can('expense-heads.edit')
                    <a href="{{ route('masters.expense-heads.edit', $expenseHead) }}" class="btn btn-light-brand">
                        <i class="feather-edit me-2"></i>Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row justify-content-center">
            <div class="col-xl-5">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Expense Head Details</h5>
                        @if($expenseHead->status)
                            <span class="badge bg-soft-success text-success">Active</span>
                        @else
                            <span class="badge bg-soft-danger text-danger">Inactive</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Name</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ $expenseHead->expense_name }}</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Total Expenses</div>
                            <div class="col-sm-7">
                                <span class="badge bg-soft-primary text-primary">{{ $expenseHead->expenses->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
