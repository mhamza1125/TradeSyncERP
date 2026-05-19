@extends('index')

@section('title', 'Add Expense - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Expenses</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                <li class="breadcrumb-item">Add Expense</li>
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
                    <a href="{{ route('expenses.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="expenseForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Save Expense</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="expenseForm" action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-xl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Expense Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Expense Head <span class="text-danger">*</span></label>
                                    <select name="expense_head_id" class="form-select @error('expense_head_id') is-invalid @enderror">
                                        <option value="">— Select Expense Head —</option>
                                        @foreach($expenseHeads as $parent)
                                            @if($parent->children->count())
                                            <optgroup label="{{ $parent->expense_name }}">
                                                @foreach($parent->children as $child)
                                                <option value="{{ $child->id }}" @selected(old('expense_head_id') == $child->id)>
                                                    {{ $child->expense_name }}
                                                </option>
                                                @endforeach
                                            </optgroup>
                                            @else
                                            <option value="{{ $parent->id }}" @selected(old('expense_head_id') == $parent->id)>
                                                {{ $parent->expense_name }}
                                            </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('expense_head_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expense_date"
                                           class="form-control @error('expense_date') is-invalid @enderror"
                                           value="{{ old('expense_date', now()->toDateString()) }}">
                                    @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Pay From Account <span class="text-danger">*</span></label>
                                    <select name="account_id" class="form-select @error('account_id') is-invalid @enderror">
                                        <option value="">— Select Account —</option>
                                        @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('account_id') == $account->id)>
                                            {{ $account->account_name }} ({{ $account->account_type }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="amount"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           placeholder="0.00" value="{{ old('amount') }}">
                                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-12 mb-4">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" rows="3"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="Optional description or notes...">{{ old('description') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Attachment</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label">Receipt / Document</label>
                                <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">PDF, JPG, PNG up to 5MB</small>
                                @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
