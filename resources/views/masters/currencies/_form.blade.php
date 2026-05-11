<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Currency Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Currency Name <span class="text-danger">*</span></label>
                        <input type="text" name="currency_name"
                               class="form-control @error('currency_name') is-invalid @enderror"
                               placeholder="e.g. US Dollar" value="{{ old('currency_name', $currency->currency_name ?? '') }}">
                        @error('currency_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 mb-4">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="currency_code"
                               class="form-control @error('currency_code') is-invalid @enderror"
                               placeholder="USD" maxlength="10" value="{{ old('currency_code', $currency->currency_code ?? '') }}">
                        @error('currency_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 mb-4">
                        <label class="form-label">Symbol</label>
                        <input type="text" name="symbol"
                               class="form-control @error('symbol') is-invalid @enderror"
                               placeholder="$" maxlength="5" value="{{ old('symbol', $currency->symbol ?? '') }}">
                        @error('symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Exchange Rate (to PKR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.000001" name="exchange_rate"
                               class="form-control @error('exchange_rate') is-invalid @enderror"
                               placeholder="e.g. 278.50" value="{{ old('exchange_rate', $currency->exchange_rate ?? '') }}">
                        @error('exchange_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 mb-4">
                        <label class="form-label">Default Currency</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1"
                                   @checked(old('is_default', $currency->is_default ?? false))>
                            <label class="form-check-label">Set as default</label>
                        </div>
                        @error('is_default')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-3 mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="1" @selected(old('status', $currency->status ?? true) == true)>Active</option>
                            <option value="0" @selected(old('status', $currency->status ?? true) == false)>Inactive</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
