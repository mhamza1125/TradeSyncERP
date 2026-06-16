{{-- Reusable customer-payment form partial --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/tom-select/tom-select.bootstrap5.min.css') }}">
@endpush

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
                                @selected(old('customer_id', $fromInvoice->customer_id ?? $customerPayment->customer_id ?? '') == $c->id)>{{ $c->customer_name }}</option>
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
                        <select name="invoice_reference" id="invoiceRefSelect"
                                class="form-select @error('invoice_reference') is-invalid @enderror">
                            <option value="">— Select Invoice (optional) —</option>
                        </select>
                        @error('invoice_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="invoiceSummary" class="mt-2 d-none">
                            <div class="d-flex gap-3 small">
                                <span class="text-muted">Invoice Total: <strong id="invTotal"></strong></span>
                                <span class="text-muted">Paid: <strong id="invPaid"></strong></span>
                                <span class="text-success">Outstanding: <strong id="invDue"></strong></span>
                            </div>
                        </div>
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
                               placeholder="0.00" value="{{ old('invoiced_amount_fc', $fromInvoice->amount_due ?? $customerPayment->invoiced_amount_fc ?? '') }}">
                        @error('invoiced_amount_fc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Received (FC) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="received_fc" id="receivedFc"
                               class="form-control @error('received_fc') is-invalid @enderror"
                               placeholder="0.00" value="{{ old('received_fc', $fromInvoice->amount_due ?? $customerPayment->received_fc ?? '') }}">
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
<script src="{{ asset('assets/vendor/tom-select/tom-select.complete.min.js') }}"></script>
<script>
(function () {
    const INVOICE_URL       = @json(route('customer-invoices.by-customer'));
    const PREFILL_INV_REF   = @json(old('invoice_reference', $fromInvoice->invoice_number ?? $customerPayment->invoice_reference ?? null));
    const PREFILL_CUSTOMER  = @json(old('customer_id', $fromInvoice->customer_id ?? $customerPayment->customer_id ?? null));

    const customerSelect   = document.getElementById('customerSelect');
    const foreignCurrency  = document.getElementById('foreignCurrency');
    const currencyDisplay  = document.getElementById('currencyDisplay');
    const receivedFc       = document.getElementById('receivedFc');
    const exchangeRate     = document.getElementById('exchangeRate');
    const actualPkr        = document.getElementById('actualPkr');
    const invoicedAmountFc = document.getElementById('invoicedAmountFc');
    const invoiceSummary   = document.getElementById('invoiceSummary');
    const invTotal         = document.getElementById('invTotal');
    const invPaid          = document.getElementById('invPaid');
    const invDue           = document.getElementById('invDue');

    // ── Tom Select for invoice dropdown ───────────────────────────────────────
    const invoiceTs = new TomSelect('#invoiceRefSelect', {
        valueField:  'value',
        labelField:  'text',
        searchField: ['text'],
        placeholder: '— Select Invoice (optional) —',
        maxOptions:  null,
        create:      false,
        onChange(val) {
            if (!val) {
                invoiceSummary.classList.add('d-none');
                return;
            }
            const opt = invoiceTs.options[val];
            if (opt && opt.total !== undefined) {
                invoicedAmountFc.value = parseFloat(opt.due).toFixed(2);
                receivedFc.value       = parseFloat(opt.due).toFixed(2);
                invTotal.textContent   = parseFloat(opt.total).toFixed(2);
                invPaid.textContent    = parseFloat(opt.paid).toFixed(2);
                invDue.textContent     = parseFloat(opt.due).toFixed(2);
                invoiceSummary.classList.remove('d-none');
                calcActualPkr();
            }
        },
    });

    // ── Load invoices for a customer via AJAX ─────────────────────────────────
    function loadInvoices(customerId, preselectRef) {
        if (!customerId) {
            invoiceTs.clearOptions();
            invoiceTs.clear();
            invoiceSummary.classList.add('d-none');
            return;
        }

        fetch(INVOICE_URL + '?customer_id=' + customerId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(invoices => {
            invoiceTs.clearOptions();
            invoiceTs.clear(true);

            const opts = invoices.map(inv => ({
                value: inv.invoice_number,
                text:  inv.invoice_number + ' — Due: ' + parseFloat(inv.amount_due).toFixed(2)
                       + ' (' + inv.status + ')',
                total: inv.total_amount,
                paid:  inv.amount_paid,
                due:   inv.amount_due,
            }));

            invoiceTs.addOptions(opts);

            if (preselectRef) {
                // If there's no matching option (e.g., old fully-paid invoice), add a placeholder
                const exists = opts.some(o => o.value === preselectRef);
                if (!exists) {
                    invoiceTs.addOption({ value: preselectRef, text: preselectRef });
                }
                invoiceTs.setValue(preselectRef, true);

                // Show summary if the option carries data
                const opt = invoiceTs.options[preselectRef];
                if (opt && opt.total !== undefined) {
                    invTotal.textContent = parseFloat(opt.total).toFixed(2);
                    invPaid.textContent  = parseFloat(opt.paid).toFixed(2);
                    invDue.textContent   = parseFloat(opt.due).toFixed(2);
                    invoiceSummary.classList.remove('d-none');
                }
            }
        })
        .catch(() => {});
    }

    // ── Currency auto-set ─────────────────────────────────────────────────────
    function setCurrency(code) {
        foreignCurrency.value       = code || '';
        currencyDisplay.textContent = code || '—';
    }

    customerSelect.addEventListener('change', function () {
        setCurrency(this.options[this.selectedIndex].dataset.currency || '');
        loadInvoices(this.value, null);
    });

    if (customerSelect.value && !foreignCurrency.value) {
        setCurrency(customerSelect.options[customerSelect.selectedIndex].dataset.currency || '');
    }

    // ── PKR calculation ───────────────────────────────────────────────────────
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

    // ── On page load: if customer is pre-selected, fetch invoices ─────────────
    if (PREFILL_CUSTOMER) {
        loadInvoices(PREFILL_CUSTOMER, PREFILL_INV_REF);
    }
})();
</script>
@endpush
