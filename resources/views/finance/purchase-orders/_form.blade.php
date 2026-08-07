{{-- Reusable purchase order form partial --}}
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Purchase Order Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">PO Date <span class="text-danger">*</span></label>
                        <input type="date" name="po_date" class="form-control @error('po_date') is-invalid @enderror"
                               value="{{ old('po_date', isset($purchaseOrder) ? $purchaseOrder->po_date?->toDateString() : now()->toDateString()) }}">
                        @error('po_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @if(isset($purchaseOrder))
                    <div class="col-lg-4 mb-4">
                        <label class="form-label text-muted fs-12">PO Number</label>
                        <div class="fw-bold fs-14">{{ $purchaseOrder->po_number }}</div>
                    </div>
                    @endif
                    <div class="col-12 mb-4">
                        <label class="form-label">Remarks / Notes</label>
                        <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Optional notes...">{{ old('remarks', $purchaseOrder->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Items</h5>
                    <button type="button" id="addPoItem" class="btn btn-primary btn-sm">
                        <i class="feather-plus me-1"></i> Add Item
                    </button>
                </div>
                @error('items')
                <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
                @enderror
                <div class="table-responsive">
                    <table class="table table-bordered" id="poItemsTable">
                        <thead>
                            <tr class="single-item">
                                <th class="wd-40">#</th>
                                <th>Item / Description <span class="text-danger">*</span></th>
                                <th class="wd-100">Qty <span class="text-danger">*</span></th>
                                <th class="wd-150">Unit Price <span class="text-danger">*</span></th>
                                <th class="wd-150 text-end">Total</th>
                                <th class="wd-40"></th>
                            </tr>
                        </thead>
                        <tbody id="poItemsBody">
                            @if(isset($purchaseOrder) && $purchaseOrder->items->count())
                                @foreach($purchaseOrder->items as $i => $item)
                                <tr class="po-item-row">
                                    <td class="row-num">{{ $i + 1 }}</td>
                                    <td><input type="text" name="items[{{ $i }}][description]" class="form-control form-control-sm" value="{{ old("items.{$i}.description", $item->description) }}" required></td>
                                    <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm item-qty" step="0.01" min="0.01" value="{{ old("items.{$i}.quantity", $item->quantity) }}" required></td>
                                    <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-control form-control-sm item-price" step="0.01" min="0" value="{{ old("items.{$i}.unit_price", $item->unit_price) }}" required></td>
                                    <td class="text-end item-total">{{ number_format($item->total_amount, 2) }}</td>
                                    <td><button type="button" class="btn btn-sm btn-light-brand remove-po-row"><i class="feather-trash-2"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                            <tr class="po-item-row">
                                <td class="row-num">1</td>
                                <td><input type="text" name="items[0][description]" class="form-control form-control-sm" required></td>
                                <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty" step="0.01" min="0.01" value="1" required></td>
                                <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm item-price" step="0.01" min="0" value="0" required></td>
                                <td class="text-end item-total">0.00</td>
                                <td></td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Grand Total</td>
                                <td class="text-end fw-bold" id="poGrandTotal">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let poItemIdx = {{ isset($purchaseOrder) ? max($purchaseOrder->items->count(), 1) : 1 }};

    function calcPoTotals() {
        let grandTotal = 0;
        document.querySelectorAll('#poItemsBody .po-item-row').forEach(row => {
            const qty   = parseFloat(row.querySelector('.item-qty')?.value)   || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            const total = qty * price;
            row.querySelector('.item-total').textContent = total.toFixed(2);
            grandTotal += total;
        });
        document.getElementById('poGrandTotal').textContent = grandTotal.toFixed(2);
    }

    document.getElementById('poItemsBody').addEventListener('input', calcPoTotals);
    calcPoTotals();

    document.getElementById('addPoItem').addEventListener('click', function () {
        const tbody = document.getElementById('poItemsBody');
        const tr = document.createElement('tr');
        tr.className = 'po-item-row';
        tr.innerHTML = `
            <td class="row-num">${tbody.querySelectorAll('.po-item-row').length + 1}</td>
            <td><input type="text" name="items[${poItemIdx}][description]" class="form-control form-control-sm" required></td>
            <td><input type="number" name="items[${poItemIdx}][quantity]" class="form-control form-control-sm item-qty" step="0.01" min="0.01" value="1" required></td>
            <td><input type="number" name="items[${poItemIdx}][unit_price]" class="form-control form-control-sm item-price" step="0.01" min="0" value="0" required></td>
            <td class="text-end item-total">0.00</td>
            <td><button type="button" class="btn btn-sm btn-light-brand remove-po-row"><i class="feather-trash-2"></i></button></td>
        `;
        tbody.appendChild(tr);
        poItemIdx++;
        renumberPoRows();
    });

    document.getElementById('poItemsBody').addEventListener('click', function (e) {
        if (e.target.closest('.remove-po-row')) {
            const rows = document.querySelectorAll('.po-item-row');
            if (rows.length > 1) { e.target.closest('tr').remove(); renumberPoRows(); calcPoTotals(); }
        }
    });

    function renumberPoRows() {
        document.querySelectorAll('.po-item-row .row-num').forEach((td, i) => td.textContent = i + 1);
    }
</script>
@endpush
