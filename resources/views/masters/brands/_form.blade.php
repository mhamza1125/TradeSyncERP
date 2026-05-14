<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Brand Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="brand_name"
                               class="form-control @error('brand_name') is-invalid @enderror"
                               placeholder="Brand name" value="{{ old('brand_name', $brand->brand_name ?? '') }}">
                        @error('brand_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                            <option value="">— Select Customer —</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $brand->customer_id ?? '') == $c->id)>
                                {{ $c->customer_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="3"
                                  class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Optional remarks...">{{ old('remarks', $brand->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', $brand->status ?? true) == true)>Active</option>
                        <option value="0" @selected(old('status', $brand->status ?? true) == false)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
