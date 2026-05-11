@extends('index')

@section('title', 'Generate Salary Run - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Salary Runs</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('salary.index') }}">Salary Runs</a></li>
                <li class="breadcrumb-item">Generate</li>
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
                    <a href="{{ route('salary.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="salaryForm" class="btn btn-primary">
                        <i class="feather-play me-2"></i><span>Generate Run</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="salaryForm" action="{{ route('salary.store') }}" method="POST">
            @csrf
            <div class="row justify-content-center">
                <div class="col-xl-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Salary Run Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label">Month <span class="text-danger">*</span></label>
                                <input type="month" name="month"
                                       class="form-control @error('month') is-invalid @enderror"
                                       value="{{ old('month', now()->format('Y-m')) }}">
                                @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Select the month to generate salary for all active employees.</small>
                            </div>
                            <div class="mb-4">
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

                            <div class="alert alert-soft-info p-3">
                                <div class="d-flex gap-3">
                                    <i class="feather-info fs-20 text-info"></i>
                                    <div>
                                        <strong>Note:</strong> This will generate salary lines for all active employees
                                        using their current basic salary. You can adjust bonuses, deductions, and advances
                                        after the run is created.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
