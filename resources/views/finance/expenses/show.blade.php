@extends('index')

@section('title', 'Expense Detail - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Expenses</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                <li class="breadcrumb-item">Expense #{{ $expense->id }}</li>
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
                    <a href="{{ route('expenses.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-icon btn-light-brand printBTN">
                        <i class="feather-printer"></i>
                    </a>
                    @can('expenses.delete')
                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                          onsubmit="return confirm('Delete this expense?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-light-brand" type="submit">
                            <i class="feather-trash-2 me-2"></i>Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content container-lg">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card invoice-container">
                    <div class="card-header">
                        <div>
                            <h2 class="fs-16 fw-700 mb-0">Expense Voucher</h2>
                            <span class="fs-12 text-muted">#{{ $expense->id }}</span>
                        </div>
                        <span class="badge bg-soft-danger text-danger fs-12">
                            <i class="feather-trending-down me-1"></i>Expense
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="px-4 pt-4 pb-3">
                            <div class="d-sm-flex align-items-start justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-2">Expense Head:</h6>
                                    <span class="badge bg-soft-primary text-primary fs-14 px-3 py-2">
                                        {{ $expense->expenseHead->expense_name }}
                                    </span>
                                </div>
                                <div class="lh-lg pt-3 pt-sm-0">
                                    <div><span class="fw-bold text-dark">Date:</span> <span class="text-muted">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</span></div>
                                    <div><span class="fw-bold text-dark">Account:</span> <span class="text-muted">{{ $expense->account->account_name }}</span></div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-dashed">

                        <div class="px-4 py-4">
                            <div class="row g-4">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-soft-danger rounded">
                                        <div class="text-muted fs-12 mb-1">Amount</div>
                                        <div class="fs-24 fw-bolder text-dark">{{ number_format($expense->amount, 2) }}</div>
                                        <div class="text-muted fs-12">PKR</div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded">
                                        <div class="text-muted fs-12 mb-1">Paid From</div>
                                        <div class="fs-16 fw-bold text-dark">{{ $expense->account->account_name }}</div>
                                        <div class="text-muted fs-12">{{ $expense->account->account_type }}</div>
                                    </div>
                                </div>
                            </div>

                            @if($expense->description)
                            <div class="mt-4">
                                <h6 class="fw-bold mb-2">Description / Notes:</h6>
                                <p class="text-muted mb-0">{{ $expense->description }}</p>
                            </div>
                            @endif

                            @if($expense->attachment)
                            <div class="mt-4">
                                <h6 class="fw-bold mb-2">Attachment:</h6>
                                <a href="{{ Storage::url($expense->attachment) }}" target="_blank" class="btn btn-light-brand btn-sm">
                                    <i class="feather-paperclip me-2"></i>View Receipt
                                </a>
                            </div>
                            @endif
                        </div>

                        @if($expense->transaction)
                        <hr class="border-dashed">
                        <div class="px-4 pb-4">
                            <h6 class="fw-bold mb-3">Transaction Reference:</h6>
                            <div class="row g-0 mb-2">
                                <div class="col-sm-4 text-muted">Transaction Date:</div>
                                <div class="col-sm-8 fw-semibold">{{ \Carbon\Carbon::parse($expense->transaction->transaction_date)->format('d M Y') }}</div>
                            </div>
                            <div class="row g-0 mb-2">
                                <div class="col-sm-4 text-muted">Debit Account:</div>
                                <div class="col-sm-8 fw-semibold">{{ optional($expense->transaction->debitAccount)->account_name }}</div>
                            </div>
                            <div class="row g-0">
                                <div class="col-sm-4 text-muted">Credit Account:</div>
                                <div class="col-sm-8 fw-semibold">{{ optional($expense->transaction->creditAccount)->account_name }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
