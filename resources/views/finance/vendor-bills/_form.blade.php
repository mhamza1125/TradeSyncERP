{{-- Reusable vendor bill form partial --}}
<div class="row">
    {{-- Main Bill Info --}}
    <div class="col-xl-8">
        <div class="card invoice-container stretch stretch-full">
            <div class="card-header">
                <h5>Bill Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Vendor <span class="text-danger">*</span></label>
                        <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror">
                            <option value="">— Select Vendor —</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected(old('vendor_id', $vendorBill->vendor_id ?? '') == $vendor->id)>
                                    {{ $vendor->vendor_name }} ({{ $vendor->company_name }})
                                </option>
                            @endforeach
                        </select>
                        @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                        <input type="date" name="bill_date" class="form-control @error('bill_date') is-invalid @enderror"
                               value="{{ old('bill_date', isset($vendorBill) ? $vendorBill->bill_date : now()->toDateString()) }}">
                        @error('bill_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date', isset($vendorBill) ? $vendorBill->due_date : '') }}">
                        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(['Unpaid','Paid','Partial','Overdue'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $vendorBill->status ?? 'Unpaid') == $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Optional notes...">{{ old('remarks', $vendorBill->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="mb-4">
                    <h5 class="fw-bold">Bill Items:</h5>
                    <span class="fs-12 text-muted">Add items to this bill</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="billItemsTable">
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
                        <tbody id="billItemsBody">
                            @if(isset($vendorBill) && $vendorBill->items->count())
                                @foreach($vendorBill->items as $i => $item)
                                <tr class="bill-item-row">
                                    <td>{{ $i + 1 }}</td>
                                    <td><input type="text" name="items[{{ $i }}][description]" class="form-control" placeholder="Item description" value="{{ old("items.{$i}.description", $item->description) }}"></td>
                                    <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control item-qty" placeholder="1" min="1" step="1" value="{{ old("items.{$i}.quantity", $item->quantity) }}"></td>
                                    <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-control item-price" placeholder="0.00" step="0.01" value="{{ old("items.{$i}.unit_price", $item->unit_price) }}"></td>
                                    <td><input type="number" name="items[{{ $i }}][line_total]" class="form-control item-total" placeholder="0.00" readonly value="{{ old("items.{$i}.line_total", $item->line_total) }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-light-brand remove-row"><i class="feather-trash-2"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                            <tr class="bill-item-row">
                                <td>1</td>
                                <td><input type="text" name="items[0][description]" class="form-control" placeholder="Item description"></td>
                                <td><input type="number" name="items[0][quantity]" class="form-control item-qty" placeholder="1" min="1" step="1" value="1"></td>
                                <td><input type="number" name="items[0][unit_price]" class="form-control item-price" placeholder="0.00" step="0.01"></td>
                                <td><input type="number" name="items[0][line_total]" class="form-control item-total" placeholder="0.00" readonly></td>
                                <td></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <button type="button" id="addBillRow" class="btn btn-primary btn-sm">
                        <i class="feather-plus me-1"></i> Add Item
                    </button>
                    <div class="text-end">
                        <strong>Grand Total: <span id="grandTotal" class="text-primary">0.00</span></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Linked Inspections --}}
    @if(isset($inspections) && $inspections->count())
    <div class="col-xl-4">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Link Inspections</h5>
            </div>
            <div class="card-body" style="max-height:400px;overflow-y:auto">
                @foreach($inspections as $inspection)
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="inspection_ids[]"
                           value="{{ $inspection->id }}" id="insp_{{ $inspection->id }}"
                           @checked(isset($vendorBill) && $vendorBill->inspections->contains($inspection->id))>
                    <label class="form-check-label" for="insp_{{ $inspection->id }}">
                        <span class="d-block">{{ optional($inspection->sample)->sample_code ?? 'INS-'.$inspection->id }}</span>
                        <small class="text-muted">{{ $inspection->inspection_date }} — {{ $inspection->overall_status }}</small>
                    </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    let rowIndex = {{ isset($vendorBill) ? $vendorBill->items->count() : 1 }};

    function calcTotals() {
        let grand = 0;
        document.querySelectorAll('.bill-item-row').forEach((row, i) => {
            const qty   = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            const total = qty * price;
            const totalInput = row.querySelector('.item-total');
            if (totalInput) totalInput.value = total.toFixed(2);
            grand += total;
        });
        document.getElementById('grandTotal').textContent = grand.toFixed(2);
    }

    document.getElementById('billItemsBody').addEventListener('input', calcTotals);
    calcTotals();

    document.getElementById('addBillRow').addEventListener('click', function () {
        const tbody = document.getElementById('billItemsBody');
        const rowCount = tbody.querySelectorAll('.bill-item-row').length + 1;
        const tr = document.createElement('tr');
        tr.className = 'bill-item-row';
        tr.innerHTML = `
            <td>${rowCount}</td>
            <td><input type="text" name="items[${rowIndex}][description]" class="form-control" placeholder="Item description"></td>
            <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control item-qty" placeholder="1" min="1" step="1" value="1"></td>
            <td><input type="number" name="items[${rowIndex}][unit_price]" class="form-control item-price" placeholder="0.00" step="0.01"></td>
            <td><input type="number" name="items[${rowIndex}][line_total]" class="form-control item-total" placeholder="0.00" readonly></td>
            <td><button type="button" class="btn btn-sm btn-light-brand remove-row"><i class="feather-trash-2"></i></button></td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
    });

    document.getElementById('billItemsBody').addEventListener('click', function (e) {
        if (e.target.closest('.remove-row')) {
            const rows = document.querySelectorAll('.bill-item-row');
            if (rows.length > 1) { e.target.closest('tr').remove(); calcTotals(); }
        }
    });
</script>
@endpush
