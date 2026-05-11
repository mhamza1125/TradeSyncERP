@extends('index')

@section('title', 'Add Expense Head - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Expense Heads</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.expense-heads.index') }}">Expense Heads</a></li>
                <li class="breadcrumb-item">Add Expense Head</li>
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
                    <a href="{{ route('masters.expense-heads.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="expenseHeadForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Save</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="expenseHeadForm" action="{{ route('masters.expense-heads.store') }}" method="POST">
            @csrf
            @include('masters.expense-heads._form')
        </form>
    </div>
</div>
@endsection
