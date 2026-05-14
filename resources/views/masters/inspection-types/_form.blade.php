<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Inspection Type</h5></div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           placeholder="e.g. PPI, Final Quality Check" value="{{ old('name', $inspectionType->name ?? '') }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                              placeholder="Optional description…">{{ old('description', $inspectionType->description ?? '') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Settings</h5></div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', $inspectionType->status ?? 1) == 1)>Active</option>
                        <option value="0" @selected(old('status', $inspectionType->status ?? 1) == 0)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
