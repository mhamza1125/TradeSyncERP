<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Account Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <input type="hidden" name="account_type" id="accountTypeInput"
                           value="{{ old('account_type', $account->account_type ?? (($account->bank_id ?? null) ? 'Bank' : 'Cash')) }}">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Account Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_name"
                               class="form-control @error('account_name') is-invalid @enderror"
                               placeholder="e.g. HBL Current Account"
                               value="{{ old('account_name', $account->account_name ?? '') }}">
                        @error('account_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Bank <small class="text-muted">(leave blank for Cash account)</small></label>
                        <select name="bank_id" id="bankSelect" class="form-select @error('bank_id') is-invalid @enderror">
                            <option value="">— Cash Account —</option>
                            @foreach($banks as $b)
                            <option value="{{ $b->id }}" @selected(old('bank_id', $account->bank_id ?? '') == $b->id)>
                                {{ $b->bank_name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Selecting a bank marks this as a Bank Account; leaving blank marks it as Cash.</small>
                        @error('bank_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4" id="accountNumberRow" style="{{ old('bank_id', $account->bank_id ?? '') ? '' : 'display:none;' }}">
                        <label class="form-label">Account Number <span class="text-danger" id="accountNumberRequired">*</span></label>
                        <input type="text" name="account_number"
                               class="form-control @error('account_number') is-invalid @enderror"
                               placeholder="e.g. 0123-456789-01"
                               value="{{ old('account_number', $account->account_number ?? '') }}">
                        <small class="text-muted">Required for bank accounts. Multiple accounts can share the same name but differ by number.</small>
                        @error('account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="alert alert-light border mb-3" id="accountTypeBadge">
                        <i class="feather-info me-2 text-primary"></i>
                        <span id="accountTypeText">
                            {{ old('bank_id', $account->bank_id ?? '') ? 'Bank Account' : 'Cash Account' }}
                        </span>
                    </div>
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

@push('scripts')
<script>
    const bankSelect        = document.getElementById('bankSelect');
    const accountNumberRow  = document.getElementById('accountNumberRow');
    const accountTypeText   = document.getElementById('accountTypeText');
    const accountTypeInput  = document.getElementById('accountTypeInput');
    const acctNumInput      = accountNumberRow?.querySelector('input');

    function toggleAccountNumber() {
        const isBank = bankSelect.value !== '';
        accountNumberRow.style.display = isBank ? '' : 'none';
        accountTypeText.textContent    = isBank ? 'Bank Account' : 'Cash Account';
        if (accountTypeInput) accountTypeInput.value = isBank ? 'Bank' : 'Cash';
        if (acctNumInput) acctNumInput.required = isBank;
    }

    bankSelect?.addEventListener('change', toggleAccountNumber);
    toggleAccountNumber();
</script>
@endpush
