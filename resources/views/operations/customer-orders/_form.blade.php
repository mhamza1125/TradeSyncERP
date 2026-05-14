<div class="row">
    {{-- Main Order Info --}}
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Order Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customerSelect" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">— Select Customer —</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $customerOrder->customer_id ?? '') == $c->id)>
                                {{ $c->customer_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Brand <small class="text-muted">(filtered by customer)</small></label>
                        <select name="brand_id" id="brandSelect" class="form-select @error('brand_id') is-invalid @enderror">
                            <option value="">— Any Brand —</option>
                            @foreach($brands as $b)
                            <option value="{{ $b->id }}"
                                    data-customer="{{ $b->customer_id }}"
                                    @selected(old('brand_id', $customerOrder->brand_id ?? '') == $b->id)>
                                {{ $b->brand_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Order Date <span class="text-danger">*</span></label>
                        <input type="date" name="order_date"
                               class="form-control @error('order_date') is-invalid @enderror"
                               value="{{ old('order_date', isset($customerOrder) ? $customerOrder->order_date?->toDateString() : now()->toDateString()) }}">
                        @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Required By</label>
                        <input type="date" name="required_by"
                               class="form-control @error('required_by') is-invalid @enderror"
                               value="{{ old('required_by', isset($customerOrder) ? $customerOrder->required_by?->toDateString() : '') }}">
                        @error('required_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Optional notes about this order...">{{ old('remarks', $customerOrder->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Order Items --}}
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Requested Items</h5>
                    <button type="button" id="addOrderItem" class="btn btn-primary btn-sm">
                        <i class="feather-plus me-1"></i> Add Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="orderItemsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name <span class="text-danger">*</span></th>
                                <th class="wd-150">Qty <span class="text-danger">*</span></th>
                                <th class="wd-120">Unit</th>
                                <th>Description</th>
                                <th class="wd-50"></th>
                            </tr>
                        </thead>
                        <tbody id="orderItemsBody">
                            @if(isset($customerOrder) && $customerOrder->items->count())
                                @foreach($customerOrder->items as $i => $item)
                                <tr class="order-item-row">
                                    <td class="row-num">{{ $i + 1 }}</td>
                                    <td><input type="text" name="items[{{ $i }}][product_name]" class="form-control" placeholder="Product name" required value="{{ old("items.{$i}.product_name", $item->product_name) }}"></td>
                                    <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control" min="1" value="{{ old("items.{$i}.quantity", $item->quantity) }}" required></td>
                                    <td><input type="text" name="items[{{ $i }}][unit]" class="form-control" placeholder="e.g. meters" value="{{ old("items.{$i}.unit", $item->unit) }}"></td>
                                    <td><input type="text" name="items[{{ $i }}][description]" class="form-control" placeholder="Optional description" value="{{ old("items.{$i}.description", $item->description) }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-light-brand remove-order-row"><i class="feather-trash-2"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                            <tr class="order-item-row">
                                <td class="row-num">1</td>
                                <td><input type="text" name="items[0][product_name]" class="form-control" placeholder="Product name" required></td>
                                <td><input type="number" name="items[0][quantity]" class="form-control" min="1" value="1" required></td>
                                <td><input type="text" name="items[0][unit]" class="form-control" placeholder="e.g. meters"></td>
                                <td><input type="text" name="items[0][description]" class="form-control" placeholder="Optional description"></td>
                                <td></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Settings --}}
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Order Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach(['Draft','Confirmed','Processing','Dispatched','Cancelled'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $customerOrder->status ?? 'Draft') == $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="alert alert-light border">
                    <small class="text-muted">
                        <strong>Draft</strong> — Not yet confirmed by customer.<br>
                        <strong>Confirmed</strong> — Customer has confirmed this request.<br>
                        <strong>Processing</strong> — Samples are being prepared.<br>
                        <strong>Dispatched</strong> — Samples sent to customer.<br>
                        <strong>Cancelled</strong> — Order has been cancelled.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let orderItemIdx = {{ isset($customerOrder) ? max($customerOrder->items->count(), 1) : 1 }};

    // Filter brands by selected customer
    const customerSelect = document.getElementById('customerSelect');
    const brandSelect    = document.getElementById('brandSelect');
    const allBrandOpts   = Array.from(brandSelect.options).map(o => ({
        value: o.value, text: o.text, customer: o.dataset.customer, selected: o.selected
    }));

    function filterBrands() {
        const cid = customerSelect.value;
        const cur = brandSelect.value;
        brandSelect.innerHTML = '<option value="">— Any Brand —</option>';
        allBrandOpts.filter(o => o.value === '' || o.customer == cid).forEach(o => {
            if (o.value === '') return;
            const opt = document.createElement('option');
            opt.value = o.value;
            opt.text  = o.text;
            if (o.value == cur) opt.selected = true;
            brandSelect.appendChild(opt);
        });
    }

    customerSelect?.addEventListener('change', filterBrands);
    filterBrands();

    // Add/remove item rows
    document.getElementById('addOrderItem').addEventListener('click', function () {
        const tbody = document.getElementById('orderItemsBody');
        const tr = document.createElement('tr');
        tr.className = 'order-item-row';
        tr.innerHTML = `
            <td class="row-num">${tbody.querySelectorAll('.order-item-row').length + 1}</td>
            <td><input type="text" name="items[${orderItemIdx}][product_name]" class="form-control" placeholder="Product name" required></td>
            <td><input type="number" name="items[${orderItemIdx}][quantity]" class="form-control" min="1" value="1" required></td>
            <td><input type="text" name="items[${orderItemIdx}][unit]" class="form-control" placeholder="e.g. meters"></td>
            <td><input type="text" name="items[${orderItemIdx}][description]" class="form-control" placeholder="Optional description"></td>
            <td><button type="button" class="btn btn-sm btn-light-brand remove-order-row"><i class="feather-trash-2"></i></button></td>
        `;
        tbody.appendChild(tr);
        orderItemIdx++;
        renumberRows();
    });

    document.getElementById('orderItemsBody').addEventListener('click', function (e) {
        if (e.target.closest('.remove-order-row')) {
            const rows = document.querySelectorAll('.order-item-row');
            if (rows.length > 1) { e.target.closest('tr').remove(); renumberRows(); }
        }
    });

    function renumberRows() {
        document.querySelectorAll('.order-item-row .row-num').forEach((td, i) => td.textContent = i + 1);
    }
</script>
@endpush
