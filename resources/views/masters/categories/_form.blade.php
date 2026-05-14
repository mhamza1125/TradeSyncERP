<div class="row justify-content-center">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Category Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="category_name"
                           class="form-control @error('category_name') is-invalid @enderror"
                           placeholder="e.g. Textiles, Electronics..."
                           value="{{ old('category_name', $category->category_name ?? '') }}">
                    @error('category_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', $category->status ?? true) == true)>Active</option>
                        <option value="0" @selected(old('status', $category->status ?? true) == false)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
