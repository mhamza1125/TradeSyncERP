{{-- Reusable customer invoice form partial --}}
<div class="row">
    {{-- Main Invoice Info --}}
    <div class="col-xl-12">
        <div class="card invoice-container">
            <div class="card-header">
                <h5>Invoice Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                            <option value="">— Select Customer —</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    @selected(old('customer_id', $customerInvoice->customer_id ?? '') == $customer->id)>
                                    {{ $customer->customer_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                               value="{{ old('invoice_date', isset($customerInvoice) ? $customerInvoice->invoice_date : now()->toDateString()) }}">
                        @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date', isset($customerInvoice) ? $customerInvoice->due_date : '') }}">
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(['Draft','Sent','Partial','Paid','Overdue','Cancelled'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $customerInvoice->status ?? 'Draft') == $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Optional notes...">{{ old('remarks', $customerInvoice->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="mb-4">
                    <h5 class="fw-bold">Invoice Items:</h5>
                    <span class="fs-12 text-muted">Add items to this invoice</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="invoiceItemsTable">
                        <thead>
                            <tr class="single-item">
                                <th class="wd-50">#</th>
                                <th>Description</th>
                                <th class="wd-120">Qty</th>
                                <th class="wd-150">Unit Price</th>
                                <th class="wd-150">Line Total</th>
                                <th class="wd-50"></th>
                            </tr>
                        </thead>
                        <tbody id="invoiceItemsBody">
                            @if(isset($customerInvoice) && $customerInvoice->items->count())
                                @foreach($customerInvoice->items as $i => $item)
                                <tr class="invoice-item-row">
                                    <td>{{ $i + 1 }}</td>
                                    <td><input type="text" name="items[{{ $i }}][description]" class="form-control" placeholder="Item description" value="{{ old("items.{$i}.description", $item->description) }}"></td>
                                    <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control item-qty" placeholder="1" min="0.01" step="0.01" value="{{ old("items.{$i}.quantity", $item->quantity) }}"></td>
                                    <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-control item-price" placeholder="0.00" step="0.01" value="{{ old("items.{$i}.unit_price", $item->unit_price) }}"></td>
                                    <td><input type="number" name="items[{{ $i }}][line_total]" class="form-control item-total" placeholder="0.00" readonly value="{{ old("items.{$i}.line_total", $item->line_total) }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-light-brand remove-row"><i class="feather-trash-2"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                            <tr class="invoice-item-row">
                                <td>1</td>
                                <td><input type="text" name="items[0][description]" class="form-control" placeholder="Item description"></td>
                                <td><input type="number" name="items[0][quantity]" class="form-control item-qty" placeholder="1" min="0.01" step="0.01" value="1"></td>
                                <td><input type="number" name="items[0][unit_price]" class="form-control item-price" placeholder="0.00" step="0.01"></td>
                                <td><input type="number" name="items[0][line_total]" class="form-control item-total" placeholder="0.00" readonly></td>
                                <td></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-start mt-3">
                    <button type="button" id="addInvoiceRow" class="btn btn-primary btn-sm">
                        <i class="feather-plus me-1"></i> Add Item
                    </button>
                    <div class="text-end">
                        <div class="mb-1">
                            <span class="text-muted me-2">Tax:</span>
                            <input type="number" step="0.01" name="tax_amount" class="form-control form-control-sm d-inline-block"
                                   style="width:120px;" placeholder="0.00"
                                   value="{{ old('tax_amount', $customerInvoice->tax_amount ?? 0) }}">
                        </div>
                        <div class="mb-1">
                            <span class="text-muted me-2">Discount:</span>
                            <input type="number" step="0.01" name="discount_amount" class="form-control form-control-sm d-inline-block"
                                   style="width:120px;" placeholder="0.00"
                                   value="{{ old('discount_amount', $customerInvoice->discount_amount ?? 0) }}">
                        </div>
                        <strong>Grand Total: <span id="grandTotal" class="text-primary">0.00</span></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Foreign Currency Section --}}
    <div class="col-xl-6 mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Foreign Currency Details</h5>
                <p class="text-muted fs-12 mb-0">Fill in if this invoice was issued in a foreign currency.</p>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Invoice Currency</label>
                    <select name="foreign_currency_id" class="form-select @error('foreign_currency_id') is-invalid @enderror">
                        <option value="">— Local Currency (no conversion) —</option>
                        @foreach($currencies as $cur)
                        <option value="{{ $cur->id }}"
                            @selected(old('foreign_currency_id', $customerInvoice->foreign_currency_id ?? '') == $cur->id)>
                            {{ $cur->currency_code }} — {{ $cur->currency_name }}
                        </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Select the currency in which this invoice was issued.</small>
                    @error('foreign_currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Exchange Rate</label>
                    <input type="number" step="0.000001" name="exchange_rate"
                           class="form-control @error('exchange_rate') is-invalid @enderror"
                           placeholder="e.g. 278.50"
                           value="{{ old('exchange_rate', $customerInvoice->exchange_rate ?? '') }}">
                    <small class="text-muted">Enter the exchange rate applicable at invoice time (1 foreign unit = ? local).</small>
                    @error('exchange_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Foreign Amount</label>
                    <input type="number" step="0.01" name="foreign_amount"
                           class="form-control @error('foreign_amount') is-invalid @enderror"
                           placeholder="0.00"
                           value="{{ old('foreign_amount', $customerInvoice->foreign_amount ?? '') }}">
                    <small class="text-muted">Specify the converted local amount if different from the calculated total.</small>
                    @error('foreign_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let invoiceRowIdx = {{ isset($customerInvoice) ? $customerInvoice->items->count() : 1 }};

    function calcInvoiceTotals() {
        let subtotal = 0;
        document.querySelectorAll('.invoice-item-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            const total = qty * price;
            const totalInput = row.querySelector('.item-total');
            if (totalInput) totalInput.value = total.toFixed(2);
            subtotal += total;
        });
        const tax      = parseFloat(document.querySelector('[name="tax_amount"]')?.value) || 0;
        const discount = parseFloat(document.querySelector('[name="discount_amount"]')?.value) || 0;
        document.getElementById('grandTotal').textContent = (subtotal + tax - discount).toFixed(2);
    }

    document.getElementById('invoiceItemsBody').addEventListener('input', calcInvoiceTotals);
    document.querySelector('[name="tax_amount"]')?.addEventListener('input', calcInvoiceTotals);
    document.querySelector('[name="discount_amount"]')?.addEventListener('input', calcInvoiceTotals);
    calcInvoiceTotals();

    document.getElementById('addInvoiceRow').addEventListener('click', function () {
        const tbody    = document.getElementById('invoiceItemsBody');
        const rowCount = tbody.querySelectorAll('.invoice-item-row').length + 1;
        const tr       = document.createElement('tr');
        tr.className   = 'invoice-item-row';
        tr.innerHTML   = `
            <td>${rowCount}</td>
            <td><input type="text" name="items[${invoiceRowIdx}][description]" class="form-control" placeholder="Item description"></td>
            <td><input type="number" name="items[${invoiceRowIdx}][quantity]" class="form-control item-qty" placeholder="1" min="0.01" step="0.01" value="1"></td>
            <td><input type="number" name="items[${invoiceRowIdx}][unit_price]" class="form-control item-price" placeholder="0.00" step="0.01"></td>
            <td><input type="number" name="items[${invoiceRowIdx}][line_total]" class="form-control item-total" placeholder="0.00" readonly></td>
            <td><button type="button" class="btn btn-sm btn-light-brand remove-row"><i class="feather-trash-2"></i></button></td>
        `;
        tbody.appendChild(tr);
        invoiceRowIdx++;
    });

    document.getElementById('invoiceItemsBody').addEventListener('click', function (e) {
        if (e.target.closest('.remove-row')) {
            const rows = document.querySelectorAll('.invoice-item-row');
            if (rows.length > 1) { e.target.closest('tr').remove(); calcInvoiceTotals(); }
        }
    });
</script>
@endpush
