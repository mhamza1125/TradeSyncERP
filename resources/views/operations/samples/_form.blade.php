{{-- Reusable sample form partial --}}
<div class="row">
    <div class="col-xl-8">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Sample Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customerSelect" class="form-select @error('customer_id') is-invalid @enderror">
                            <option value="">— Select Customer —</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $sample->customer_id ?? '') == $customer->id)>
                                    {{ $customer->customer_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Brand <span class="text-danger">*</span></label>
                        <select name="brand_id" id="brandSelect" class="form-select @error('brand_id') is-invalid @enderror">
                            <option value="">— Select Brand —</option>
                            @if(isset($brands))
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected(old('brand_id', $sample->brand_id ?? '') == $brand->id)>
                                        {{ $brand->brand_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('brand_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Product Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">— Select Category —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $sample->category_id ?? '') == $category->id)>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control @error('product_name') is-invalid @enderror"
                               placeholder="Product name" value="{{ old('product_name', $sample->product_name ?? '') }}">
                        @error('product_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Shipment Reference</label>
                        <input type="text" name="shipment_reference" class="form-control @error('shipment_reference') is-invalid @enderror"
                               placeholder="AWB / BL / Reference no." value="{{ old('shipment_reference', $sample->shipment_reference ?? '') }}">
                        @error('shipment_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Receive Date <span class="text-danger">*</span></label>
                        <input type="date" name="receive_date" class="form-control @error('receive_date') is-invalid @enderror"
                               value="{{ old('receive_date', isset($sample) ? $sample->receive_date : '') }}">
                        @error('receive_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                               placeholder="1" value="{{ old('quantity', $sample->quantity ?? '') }}">
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Additional notes...">{{ old('remarks', $sample->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Priority & Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                    <select name="priority_level" class="form-select @error('priority_level') is-invalid @enderror">
                        @foreach(['Low','Medium','High','Urgent'] as $p)
                        <option value="{{ $p }}" @selected(old('priority_level', $sample->priority_level ?? 'Medium') == $p)>{{ $p }}</option>
                        @endforeach
                    </select>
                    @error('priority_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Alert Days</label>
                    <input type="number" min="1" name="alert_days" class="form-control @error('alert_days') is-invalid @enderror"
                           placeholder="7" value="{{ old('alert_days', $sample->alert_days ?? 7) }}">
                    <small class="text-muted">Alert when due in this many days.</small>
                    @error('alert_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach(['Received','In Testing','Completed','Returned'] as $s)
                        <option value="{{ $s }}" @selected(old('status', $sample->status ?? 'Received') == $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('customerSelect')?.addEventListener('change', function () {
        const customerId = this.value;
        const brandSelect = document.getElementById('brandSelect');
        brandSelect.innerHTML = '<option value="">— Loading... —</option>';
        if (!customerId) { brandSelect.innerHTML = '<option value="">— Select Brand —</option>'; return; }
        fetch(`/api/customers/${customerId}/brands`)
            .then(r => r.json())
            .then(brands => {
                brandSelect.innerHTML = '<option value="">— Select Brand —</option>';
                brands.forEach(b => brandSelect.add(new Option(b.brand_name, b.id)));
            });
    });
</script>
@endpush
