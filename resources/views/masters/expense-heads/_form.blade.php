<div class="row justify-content-center">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Expense Head Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Parent Category</label>
                    <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                        <option value="">— None (this is a top-level category) —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('parent_id', $expenseHead->parent_id ?? '') == $cat->id)>
                            {{ $cat->expense_name }}
                        </option>
                        @endforeach
                    </select>
                    <div class="form-text">Leave blank to create a <strong>Category</strong>. Select a parent to create a <strong>Subcategory</strong>.</div>
                    @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Expense Head Name <span class="text-danger">*</span></label>
                    <input type="text" name="expense_name"
                           class="form-control @error('expense_name') is-invalid @enderror"
                           placeholder="e.g. Office Supplies, Utilities..."
                           value="{{ old('expense_name', $expenseHead->expense_name ?? '') }}">
                    @error('expense_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', $expenseHead->status ?? true) == true)>Active</option>
                        <option value="0" @selected(old('status', $expenseHead->status ?? true) == false)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
