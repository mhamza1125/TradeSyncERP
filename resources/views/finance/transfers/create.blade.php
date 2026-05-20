@extends('index')

@section('title', 'Transfer Funds - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Transfer Funds</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Finance</li>
                <li class="breadcrumb-item">Transfer Funds</li>
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
                    <a href="{{ route('ledger.cash') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="transferForm" class="btn btn-primary">
                        <i class="feather-send me-2"></i><span>Record Transfer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="transferForm" action="{{ route('transfers.store') }}" method="POST">
            @csrf
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <i class="feather-repeat me-2 text-primary"></i>Internal Fund Transfer
                            </h6>
                        </div>
                        <div class="card-body">

                            {{-- Info banner --}}
                            <div class="alert alert-soft-info d-flex align-items-center gap-2 mb-4" role="alert">
                                <i class="feather-info flex-shrink-0"></i>
                                <div class="small">Funds are moved from the <strong>Source</strong> account (credited) to the <strong>Destination</strong> account (debited). Both accounts will reflect the transfer immediately.</div>
                            </div>

                            <div class="row g-3">

                                {{-- Source Account --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="from_account_id">
                                        Source Account <span class="text-danger">*</span>
                                    </label>
                                    <select name="from_account_id" id="from_account_id"
                                        class="form-select @error('from_account_id') is-invalid @enderror" required>
                                        <option value="">— Select Source —</option>
                                        @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('from_account_id') == $account->id)>
                                            {{ $account->account_name }}
                                            ({{ $account->account_type }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('from_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Destination Account --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="to_account_id">
                                        Destination Account <span class="text-danger">*</span>
                                    </label>
                                    <select name="to_account_id" id="to_account_id"
                                        class="form-select @error('to_account_id') is-invalid @enderror" required>
                                        <option value="">— Select Destination —</option>
                                        @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('to_account_id') == $account->id)>
                                            {{ $account->account_name }}
                                            ({{ $account->account_type }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('to_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Arrow between accounts --}}
                                <div class="col-12 text-center my-n1">
                                    <span class="badge bg-soft-primary text-primary px-3 py-2 fs-6">
                                        <i class="feather-arrow-right me-1"></i>Transfer Direction
                                    </span>
                                </div>

                                {{-- Amount --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="amount">
                                        Amount <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">PKR</span>
                                        <input type="number" name="amount" id="amount" step="0.01" min="0.01"
                                            class="form-control @error('amount') is-invalid @enderror"
                                            value="{{ old('amount') }}" placeholder="0.00" required>
                                        @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Date --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" for="transaction_date">
                                        Transfer Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="transaction_date" id="transaction_date"
                                        class="form-control @error('transaction_date') is-invalid @enderror"
                                        value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                    @error('transaction_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Remarks --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold" for="remarks">Remarks</label>
                                    <textarea name="remarks" id="remarks" rows="3"
                                        class="form-control @error('remarks') is-invalid @enderror"
                                        placeholder="Optional notes about this transfer...">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end gap-2">
                            <a href="{{ route('ledger.cash') }}" class="btn btn-light-brand">Cancel</a>
                            <button type="submit" form="transferForm" class="btn btn-primary">
                                <i class="feather-send me-2"></i>Record Transfer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Prevent selecting the same account for source and destination
document.addEventListener('DOMContentLoaded', function () {
    const fromSelect = document.getElementById('from_account_id');
    const toSelect   = document.getElementById('to_account_id');

    function disableSameOption(changedSelect, otherSelect) {
        const selectedVal = changedSelect.value;
        Array.from(otherSelect.options).forEach(function (opt) {
            opt.disabled = opt.value !== '' && opt.value === selectedVal;
        });
        if (otherSelect.value === selectedVal) {
            otherSelect.value = '';
        }
    }

    fromSelect.addEventListener('change', function () {
        disableSameOption(fromSelect, toSelect);
    });
    toSelect.addEventListener('change', function () {
        disableSameOption(toSelect, fromSelect);
    });
});
</script>
@endsection
