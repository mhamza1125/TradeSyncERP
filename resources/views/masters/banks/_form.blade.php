<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Bank Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name"
                               class="form-control @error('bank_name') is-invalid @enderror"
                               placeholder="e.g. Habib Bank Limited"
                               value="{{ old('bank_name', $bank->bank_name ?? '') }}">
                        @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Branch Name</label>
                        <input type="text" name="branch_name"
                               class="form-control @error('branch_name') is-invalid @enderror"
                               placeholder="e.g. Main Branch, Karachi"
                               value="{{ old('branch_name', $bank->branch_name ?? '') }}">
                        @error('branch_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number"
                               class="form-control @error('account_number') is-invalid @enderror"
                               placeholder="Bank account number"
                               value="{{ old('account_number', $bank->account_number ?? '') }}">
                        @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 mb-4">
                        <label class="form-label">SWIFT Code</label>
                        <input type="text" name="swift_code"
                               class="form-control @error('swift_code') is-invalid @enderror"
                               placeholder="e.g. HABBPKKA"
                               value="{{ old('swift_code', $bank->swift_code ?? '') }}">
                        @error('swift_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="1" @selected(old('status', $bank->status ?? true) == true)>Active</option>
                            <option value="0" @selected(old('status', $bank->status ?? true) == false)>Inactive</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
