<div class="row">
    <div class="col-xl-8">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Account Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name"
                               class="form-control @error('account_name') is-invalid @enderror"
                               placeholder="e.g. HBL Current Account"
                               value="{{ old('account_name', $account->account_name ?? '') }}">
                        @error('account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select name="account_type" class="form-select @error('account_type') is-invalid @enderror">
                            <option value="">— Select Type —</option>
                            @foreach(['Cash','Bank','Receivable','Payable','Equity','Expense','Revenue'] as $type)
                            <option value="{{ $type }}" @selected(old('account_type', $account->account_type ?? '') == $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('account_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Bank</label>
                        <select name="bank_id" class="form-select @error('bank_id') is-invalid @enderror">
                            <option value="">— No Bank (Cash) —</option>
                            @foreach($banks as $b)
                            <option value="{{ $b->id }}" @selected(old('bank_id', $account->bank_id ?? '') == $b->id)>
                                {{ $b->bank_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('bank_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Currency</label>
                        <select name="currency" class="form-select @error('currency') is-invalid @enderror">
                            <option value="">— Select Currency —</option>
                            @foreach($currencies as $c)
                            <option value="{{ $c->currency_code }}" @selected(old('currency', $account->currency ?? '') == $c->currency_code)>
                                {{ $c->currency_name }} ({{ $c->currency_code }})
                            </option>
                            @endforeach
                        </select>
                        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" step="0.01" name="opening_balance"
                               class="form-control @error('opening_balance') is-invalid @enderror"
                               placeholder="0.00" value="{{ old('opening_balance', $account->opening_balance ?? 0) }}">
                        @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', $account->status ?? true) == true)>Active</option>
                        <option value="0" @selected(old('status', $account->status ?? true) == false)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
