<div class="row">
    {{-- Main Order Info — full width --}}
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Order Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">— Select Customer —</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $customerOrder->customer_id ?? '') == $c->id)>
                                {{ $c->customer_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Order Date <span class="text-danger">*</span></label>
                        <input type="date" name="order_date"
                               class="form-control @error('order_date') is-invalid @enderror"
                               value="{{ old('order_date', isset($customerOrder) ? $customerOrder->order_date?->toDateString() : now()->toDateString()) }}">
                        @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(['Draft','Confirmed','Processing','Dispatched','Cancelled'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $customerOrder->status ?? 'Draft') == $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">ETD</label>
                        <input type="date" name="required_by"
                               class="form-control @error('required_by') is-invalid @enderror"
                               value="{{ old('required_by', isset($customerOrder) ? $customerOrder->required_by?->toDateString() : '') }}">
                        <small class="text-muted">Estimated Time of Departure / Delivery</small>
                        @error('required_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-8 mb-4">
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
                                <th>Product Category <span class="text-danger">*</span></th>
                                <th class="wd-120">Qty <span class="text-danger">*</span></th>
                                <th>Description</th>
                                <th class="wd-50"></th>
                            </tr>
                        </thead>
                        <tbody id="orderItemsBody">
                            @php
                                $categoriesJson = $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->category_name]);
                            @endphp
                            @if(isset($customerOrder) && $customerOrder->items->count())
                                @foreach($customerOrder->items as $i => $item)
                                <tr class="order-item-row">
                                    <td class="row-num">{{ $i + 1 }}</td>
                                    <td>
                                        <select name="items[{{ $i }}][product_category_id]" class="form-select form-select-sm">
                                            <option value="">— Select —</option>
                                            @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected(old("items.{$i}.product_category_id", $item->product_category_id) == $cat->id)>
                                                {{ $cat->category_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm" min="1" value="{{ old("items.{$i}.quantity", $item->quantity) }}" required></td>
                                    <td><input type="text" name="items[{{ $i }}][description]" class="form-control form-control-sm" placeholder="Optional" value="{{ old("items.{$i}.description", $item->description) }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-light-brand remove-order-row"><i class="feather-trash-2"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                            <tr class="order-item-row">
                                <td class="row-num">1</td>
                                <td>
                                    <select name="items[0][product_category_id]" class="form-select form-select-sm">
                                        <option value="">— Select —</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm" min="1" value="1" required></td>
                                <td><input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="Optional"></td>
                                <td></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let orderItemIdx = {{ isset($customerOrder) ? max($customerOrder->items->count(), 1) : 1 }};
    const categoriesData = @json($categoriesJson ?? []);

    function buildCategorySelect(name, selectedId = '') {
        let opts = `<option value="">— Select —</option>`;
        categoriesData.forEach(c => {
            opts += `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.name}</option>`;
        });
        return `<select name="${name}" class="form-select form-select-sm">${opts}</select>`;
    }

    document.getElementById('addOrderItem').addEventListener('click', function () {
        const tbody = document.getElementById('orderItemsBody');
        const tr = document.createElement('tr');
        tr.className = 'order-item-row';
        tr.innerHTML = `
            <td class="row-num">${tbody.querySelectorAll('.order-item-row').length + 1}</td>
            <td>${buildCategorySelect('items[' + orderItemIdx + '][product_category_id]')}</td>
            <td><input type="number" name="items[${orderItemIdx}][quantity]" class="form-control form-control-sm" min="1" value="1" required></td>
            <td><input type="text" name="items[${orderItemIdx}][description]" class="form-control form-control-sm" placeholder="Optional"></td>
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
