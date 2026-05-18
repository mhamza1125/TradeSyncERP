{{-- Reusable customer invoice form partial --}}
@php
    $suppliersJson       = $suppliers->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);
    $inspTypesJson       = $inspectionTypes->map(fn($t) => ['id' => $t->id, 'name' => $t->name]);
    $customersWithCurrency = $customers->mapWithKeys(fn($c) => [
        $c->id => optional($c->currency)->currency_code ?? ''
    ]);
@endphp

<div class="row">
    {{-- Main Invoice Info --}}
    <div class="col-xl-12">
        <div class="card invoice-container">
            <div class="card-header">
                <h5>Invoice Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-lg-5 mb-4">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="invoiceCustomerSelect" class="form-select @error('customer_id') is-invalid @enderror">
                            <option value="">— Select Customer —</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    data-currency="{{ optional($customer->currency)->currency_code }}"
                                    @selected(old('customer_id', $customerInvoice->customer_id ?? '') == $customer->id)>
                                    {{ $customer->customer_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 mb-4 d-flex align-items-end">
                        <div>
                            <label class="form-label text-muted fs-12">Customer Currency</label>
                            <div id="customerCurrencyDisplay" class="fw-bold fs-14 text-primary">—</div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" name="invoice_date" class="form-control @error('invoice_date') is-invalid @enderror"
                               value="{{ old('invoice_date', isset($customerInvoice) ? $customerInvoice->invoice_date?->toDateString() : now()->toDateString()) }}">
                        @error('invoice_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date', isset($customerInvoice) ? $customerInvoice->due_date?->toDateString() : '') }}">
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
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
                    <span class="fs-12 text-muted">Each row = one inspection/service charge</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="invoiceItemsTable">
                        <thead>
                            <tr class="single-item">
                                <th class="wd-40">#</th>
                                <th>Supplier</th>
                                <th>Inspection Type</th>
                                <th>PO / Invoice No</th>
                                <th class="wd-150">Date</th>
                                <th class="wd-150">Amount</th>
                                <th class="wd-40"></th>
                            </tr>
                        </thead>
                        <tbody id="invoiceItemsBody">
                            @if(isset($customerInvoice) && $customerInvoice->items->count())
                                @foreach($customerInvoice->items as $i => $item)
                                <tr class="invoice-item-row">
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <select name="items[{{ $i }}][supplier_id]" class="form-select form-select-sm">
                                            <option value="">— Select —</option>
                                            @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}" @selected(old("items.{$i}.supplier_id", $item->supplier_id) == $s->id)>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="items[{{ $i }}][inspection_type_id]" class="form-select form-select-sm">
                                            <option value="">— Select —</option>
                                            @foreach($inspectionTypes as $t)
                                            <option value="{{ $t->id }}" @selected(old("items.{$i}.inspection_type_id", $item->inspection_type_id) == $t->id)>{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="items[{{ $i }}][po_invoice_no]" class="form-control form-control-sm" placeholder="PO / Inv No." value="{{ old("items.{$i}.po_invoice_no", $item->po_invoice_no) }}"></td>
                                    <td><input type="date" name="items[{{ $i }}][item_date]" class="form-control form-control-sm" value="{{ old("items.{$i}.item_date", $item->item_date?->toDateString()) }}"></td>
                                    <td><input type="number" name="items[{{ $i }}][amount]" class="form-control form-control-sm item-amount" placeholder="0.00" step="0.01" value="{{ old("items.{$i}.amount", $item->amount) }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-light-brand remove-row"><i class="feather-trash-2"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                            <tr class="invoice-item-row">
                                <td>1</td>
                                <td>
                                    <select name="items[0][supplier_id]" class="form-select form-select-sm">
                                        <option value="">— Select —</option>
                                        @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][inspection_type_id]" class="form-select form-select-sm">
                                        <option value="">— Select —</option>
                                        @foreach($inspectionTypes as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="items[0][po_invoice_no]" class="form-control form-control-sm" placeholder="PO / Inv No."></td>
                                <td><input type="date" name="items[0][item_date]" class="form-control form-control-sm"></td>
                                <td><input type="number" name="items[0][amount]" class="form-control form-control-sm item-amount" placeholder="0.00" step="0.01"></td>
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
</div>

@push('scripts')
<script>
    // Customer → currency display
    const customerCurrencyMap = @json($customersWithCurrency);
    const customerSelect = document.getElementById('invoiceCustomerSelect');

    function updateCurrencyDisplay() {
        const id  = customerSelect.value;
        const cur = id ? (customerCurrencyMap[id] || '—') : '—';
        document.getElementById('customerCurrencyDisplay').textContent = cur || '—';
    }

    customerSelect?.addEventListener('change', updateCurrencyDisplay);
    updateCurrencyDisplay();

    // Invoice totals
    let invoiceRowIdx = {{ isset($customerInvoice) ? $customerInvoice->items->count() : 1 }};
    const suppliersData   = @json($suppliersJson);
    const inspTypesData   = @json($inspTypesJson);

    function calcInvoiceTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-amount').forEach(inp => {
            subtotal += parseFloat(inp.value) || 0;
        });
        const tax      = parseFloat(document.querySelector('[name="tax_amount"]')?.value) || 0;
        const discount = parseFloat(document.querySelector('[name="discount_amount"]')?.value) || 0;
        document.getElementById('grandTotal').textContent = (subtotal + tax - discount).toFixed(2);
    }

    document.getElementById('invoiceItemsBody').addEventListener('input', calcInvoiceTotals);
    document.querySelector('[name="tax_amount"]')?.addEventListener('input', calcInvoiceTotals);
    document.querySelector('[name="discount_amount"]')?.addEventListener('input', calcInvoiceTotals);
    calcInvoiceTotals();

    function buildSupplierSelect(idx) {
        let opts = `<option value="">— Select —</option>`;
        suppliersData.forEach(s => opts += `<option value="${s.id}">${s.name}</option>`);
        return `<select name="items[${idx}][supplier_id]" class="form-select form-select-sm">${opts}</select>`;
    }

    function buildInspTypeSelect(idx) {
        let opts = `<option value="">— Select —</option>`;
        inspTypesData.forEach(t => opts += `<option value="${t.id}">${t.name}</option>`);
        return `<select name="items[${idx}][inspection_type_id]" class="form-select form-select-sm">${opts}</select>`;
    }

    document.getElementById('addInvoiceRow').addEventListener('click', function () {
        const tbody    = document.getElementById('invoiceItemsBody');
        const rowCount = tbody.querySelectorAll('.invoice-item-row').length + 1;
        const tr       = document.createElement('tr');
        tr.className   = 'invoice-item-row';
        tr.innerHTML   = `
            <td>${rowCount}</td>
            <td>${buildSupplierSelect(invoiceRowIdx)}</td>
            <td>${buildInspTypeSelect(invoiceRowIdx)}</td>
            <td><input type="text" name="items[${invoiceRowIdx}][po_invoice_no]" class="form-control form-control-sm" placeholder="PO / Inv No."></td>
            <td><input type="date" name="items[${invoiceRowIdx}][item_date]" class="form-control form-control-sm"></td>
            <td><input type="number" name="items[${invoiceRowIdx}][amount]" class="form-control form-control-sm item-amount" placeholder="0.00" step="0.01"></td>
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
