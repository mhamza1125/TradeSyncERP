{{-- Reusable vendor form partial --}}
<div class="row">
    <div class="col-xl-8">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Vendor Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="vendor_name" class="form-control @error('vendor_name') is-invalid @enderror"
                               placeholder="Vendor / Supplier name" value="{{ old('vendor_name', $vendor->vendor_name ?? '') }}">
                        @error('vendor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                               placeholder="Registered company name" value="{{ old('company_name', $vendor->company_name ?? '') }}">
                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               placeholder="+92 300 0000000" value="{{ old('phone', $vendor->phone ?? '') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               placeholder="vendor@example.com" value="{{ old('email', $vendor->email ?? '') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-4">
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror"
                                  placeholder="Full postal address">{{ old('address', $vendor->address ?? '') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Financial Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Payment Terms</label>
                    <input type="text" name="payment_terms" class="form-control @error('payment_terms') is-invalid @enderror"
                           placeholder="e.g. Net 30, Immediate" value="{{ old('payment_terms', $vendor->payment_terms ?? '') }}">
                    @error('payment_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Opening Balance</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control @error('opening_balance') is-invalid @enderror"
                           placeholder="0.00" value="{{ old('opening_balance', $vendor->opening_balance ?? 0) }}">
                    @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', $vendor->status ?? 1) == 1)>Active</option>
                        <option value="0" @selected(old('status', $vendor->status ?? 1) == 0)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
