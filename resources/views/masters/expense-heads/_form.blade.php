<div class="row justify-content-center">
    <div class="col-xl-6">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Expense Head Details</h5>
            </div>
            <div class="card-body">
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
