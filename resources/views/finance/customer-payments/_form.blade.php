{{-- Reusable customer-payment form partial --}}
<div class="row justify-content-center">
    <div class="col-xl-12">
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
                                @selected(old('customer_id', $customerPayment->customer_id ?? '') == $c->id)>{{ $c->customer_name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror"
                               value="{{ old('payment_date', isset($customerPayment) ? \Carbon\Carbon::parse($customerPayment->payment_date)->toDateString() : now()->toDateString()) }}">
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Invoice Reference</label>
                        <input type="text" name="invoice_reference" class="form-control @error('invoice_reference') is-invalid @enderror"
                               placeholder="Invoice # or reference" value="{{ old('invoice_reference', $customerPayment->invoice_reference ?? '') }}">
                        @error('invoice_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Deposit Account <span class="text-danger">*</span></label>
                        <select name="debit_account_id" class="form-select @error('debit_account_id') is-invalid @enderror">
                            <option value="">— Select Account —</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('debit_account_id', $customerPayment->account_id ?? '') == $account->id)>
                                {{ $account->account_name }} ({{ $account->account_type }})
                            </option>
                            @endforeach
                        </select>
                        @error('debit_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr>
                <h6 class="fw-semibold mb-3">Foreign Currency Details</h6>

                {{-- Currency auto-populated from customer selection; stored as hidden field --}}
                <input type="hidden" name="foreign_currency" id="foreignCurrency"
                       value="{{ old('foreign_currency', $customerPayment->foreign_currency ?? '') }}">
                <div class="mb-4">
                    <label class="form-label">Currency</label>
                    <div class="d-flex align-items-center gap-2">
                        <span id="currencyDisplay" class="badge bg-soft-primary text-primary fs-14 px-3 py-2">
                            {{ old('foreign_currency', $customerPayment->foreign_currency ?? '') ?: '—' }}
                        </span>
                        <small class="text-muted">Auto-set from the selected customer's default currency.</small>
                    </div>
                    @error('foreign_currency')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Invoiced Amount (FC) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="invoiced_amount_fc" id="invoicedAmountFc"
                               class="form-control @error('invoiced_amount_fc') is-invalid @enderror"
                               placeholder="0.00" value="{{ old('invoiced_amount_fc', $customerPayment->invoiced_amount_fc ?? '') }}">
                        @error('invoiced_amount_fc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Received (FC) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="received_fc" id="receivedFc"
                               class="form-control @error('received_fc') is-invalid @enderror"
                               placeholder="0.00" value="{{ old('received_fc', $customerPayment->received_fc ?? '') }}">
                        @error('received_fc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                        <input type="number" step="0.000001" name="exchange_rate" id="exchangeRate"
                               class="form-control @error('exchange_rate') is-invalid @enderror"
                               placeholder="e.g. 278.50" value="{{ old('exchange_rate', $customerPayment->exchange_rate ?? '') }}">
                        @error('exchange_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Actual PKR Received <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="actual_pkr_received" id="actualPkr"
                               class="form-control @error('actual_pkr_received') is-invalid @enderror"
                               placeholder="0.00" value="{{ old('actual_pkr_received', $customerPayment->actual_pkr_received ?? '') }}">
                        @error('actual_pkr_received')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                              placeholder="Optional notes...">{{ old('remarks', $customerPayment->remarks ?? '') }}</textarea>
                    @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const customerSelect  = document.getElementById('customerSelect');
    const foreignCurrency = document.getElementById('foreignCurrency');
    const currencyDisplay = document.getElementById('currencyDisplay');
    const receivedFc      = document.getElementById('receivedFc');
    const exchangeRate    = document.getElementById('exchangeRate');
    const actualPkr       = document.getElementById('actualPkr');

    function setCurrency(code) {
        foreignCurrency.value       = code || '';
        currencyDisplay.textContent = code || '—';
    }

    customerSelect.addEventListener('change', function () {
        setCurrency(this.options[this.selectedIndex].dataset.currency || '');
    });

    // On edit, the hidden field already has the stored value; only override from
    // the customer dropdown when the field is blank (e.g. fresh create form).
    if (customerSelect.value && !foreignCurrency.value) {
        setCurrency(customerSelect.options[customerSelect.selectedIndex].dataset.currency || '');
    }

    function calcActualPkr() {
        const fc   = parseFloat(receivedFc.value);
        const rate = parseFloat(exchangeRate.value);
        if (fc > 0 && rate > 0) actualPkr.value = (fc * rate).toFixed(2);
    }

    function calcExchangeRate() {
        const fc  = parseFloat(receivedFc.value);
        const pkr = parseFloat(actualPkr.value);
        if (fc > 0 && pkr > 0) exchangeRate.value = (pkr / fc).toFixed(6);
    }

    receivedFc.addEventListener('input', calcActualPkr);
    exchangeRate.addEventListener('input', calcActualPkr);
    actualPkr.addEventListener('input', calcExchangeRate);
})();
</script>
@endpush
