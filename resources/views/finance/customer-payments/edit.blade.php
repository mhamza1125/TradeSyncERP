@extends('index')

@section('title', 'Edit Payment - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Customer Payments</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer-payments.index') }}">Payments</a></li>
                <li class="breadcrumb-item">Edit Receipt #{{ $customerPayment->id }}</li>
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
                    <a href="{{ route('customer-payments.show', $customerPayment) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="paymentForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Update Payment</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="paymentForm" action="{{ route('customer-payments.update', $customerPayment) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-xl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Payment Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" id="customerSelect" class="form-select @error('customer_id') is-invalid @enderror">
                                        <option value="">— Select Customer —</option>
                                        @foreach($customers as $c)
                                        <option value="{{ $c->id }}"
                                            data-currency="{{ optional($c->currency)->currency_code }}"
                                            @selected(old('customer_id', $customerPayment->customer_id) == $c->id)>{{ $c->customer_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                                           value="{{ old('payment_date', \Carbon\Carbon::parse($customerPayment->payment_date)->toDateString()) }}">
                                    @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Invoice Reference</label>
                                    <input type="text" name="invoice_reference" class="form-control @error('invoice_reference') is-invalid @enderror"
                                           placeholder="Invoice # or reference" value="{{ old('invoice_reference', $customerPayment->invoice_reference) }}">
                                    @error('invoice_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Deposit Account <span class="text-danger">*</span></label>
                                    <select name="debit_account_id" class="form-select @error('debit_account_id') is-invalid @enderror">
                                        <option value="">— Select Account —</option>
                                        @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" @selected(old('debit_account_id', $customerPayment->account_id) == $account->id)>
                                            {{ $account->account_name }} ({{ $account->account_type }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('debit_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Foreign Currency Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label">Foreign Currency <span class="text-danger">*</span></label>
                                <input type="text" name="foreign_currency" id="foreignCurrency"
                                       class="form-control @error('foreign_currency') is-invalid @enderror"
                                       placeholder="USD, EUR, GBP..." value="{{ old('foreign_currency', $customerPayment->foreign_currency) }}">
                                @error('foreign_currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Invoiced Amount (FC) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="invoiced_amount_fc" id="invoicedAmountFc"
                                       class="form-control @error('invoiced_amount_fc') is-invalid @enderror"
                                       placeholder="0.00" value="{{ old('invoiced_amount_fc', $customerPayment->invoiced_amount_fc) }}">
                                @error('invoiced_amount_fc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Received (FC) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="received_fc" id="receivedFc"
                                       class="form-control @error('received_fc') is-invalid @enderror"
                                       placeholder="0.00" value="{{ old('received_fc', $customerPayment->received_fc) }}">
                                @error('received_fc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                                <input type="number" step="0.000001" name="exchange_rate" id="exchangeRate"
                                       class="form-control @error('exchange_rate') is-invalid @enderror"
                                       placeholder="e.g. 278.50" value="{{ old('exchange_rate', $customerPayment->exchange_rate) }}">
                                @error('exchange_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Actual PKR Received <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="actual_pkr_received" id="actualPkr"
                                       class="form-control @error('actual_pkr_received') is-invalid @enderror"
                                       placeholder="0.00" value="{{ old('actual_pkr_received', $customerPayment->actual_pkr_received) }}">
                                @error('actual_pkr_received')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                                          placeholder="Optional notes...">{{ old('remarks', $customerPayment->remarks) }}</textarea>
                                @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const customerSelect  = document.getElementById('customerSelect');
    const foreignCurrency = document.getElementById('foreignCurrency');
    const receivedFc      = document.getElementById('receivedFc');
    const exchangeRate    = document.getElementById('exchangeRate');
    const actualPkr       = document.getElementById('actualPkr');

    customerSelect.addEventListener('change', function () {
        const currency = this.options[this.selectedIndex].dataset.currency || '';
        foreignCurrency.value = currency;
    });

    function calcActualPkr() {
        const fc   = parseFloat(receivedFc.value);
        const rate = parseFloat(exchangeRate.value);
        if (fc > 0 && rate > 0) {
            actualPkr.value = (fc * rate).toFixed(2);
        }
    }

    function calcExchangeRate() {
        const fc  = parseFloat(receivedFc.value);
        const pkr = parseFloat(actualPkr.value);
        if (fc > 0 && pkr > 0) {
            exchangeRate.value = (pkr / fc).toFixed(6);
        }
    }

    receivedFc.addEventListener('input', calcActualPkr);
    exchangeRate.addEventListener('input', calcActualPkr);
    actualPkr.addEventListener('input', calcExchangeRate);
</script>
@endpush
