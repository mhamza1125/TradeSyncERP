<div class="row justify-content-center">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Allowance Type Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="e.g. Petrol Allowance, Mobile Package..."
                           value="{{ old('name', $allowanceType->name ?? '') }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input type="text" name="description"
                           class="form-control @error('description') is-invalid @enderror"
                           placeholder="Brief description..."
                           value="{{ old('description', $allowanceType->description ?? '') }}">
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                        <option value="1" @selected(old('is_active', $allowanceType->is_active ?? true) == true)>Active</option>
                        <option value="0" @selected(old('is_active', $allowanceType->is_active ?? true) == false)>Inactive</option>
                    </select>
                    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
