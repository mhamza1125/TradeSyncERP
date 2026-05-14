{{-- Reusable form partial for customer create/edit --}}
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror"
                               placeholder="Full business name" value="{{ old('customer_name', $customer->customer_name ?? '') }}">
                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Contact Person <span class="text-danger">*</span></label>
                        <input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror"
                               placeholder="Primary contact name" value="{{ old('contact_person', $customer->contact_person ?? '') }}">
                        @error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               placeholder="+92 300 0000000" value="{{ old('phone', $customer->phone ?? '') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               placeholder="customer@example.com" value="{{ old('email', $customer->email ?? '') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-4">
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror"
                                  placeholder="Full postal address">{{ old('address', $customer->address ?? '') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Financial Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Currency</label>
                    <select name="currency_id" id="customerCurrencySelect" class="form-select @error('currency_id') is-invalid @enderror">
                        <option value="">— Select Currency —</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}"
                                data-code="{{ $currency->currency_code }}"
                                @selected(old('currency_id', $customer->currency_id ?? '') == $currency->id)>
                                {{ $currency->currency_code }} — {{ $currency->currency_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">The customer's ledger and opening balance will use this currency.</small>
                    @error('currency_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Opening Balance</label>
                    <div class="input-group">
                        <span class="input-group-text" id="obCurrencyLabel">
                            {{ isset($customer) && $customer->currency ? $customer->currency->currency_code : 'PKR' }}
                        </span>
                        <input type="number" step="0.01" name="opening_balance" class="form-control @error('opening_balance') is-invalid @enderror"
                               placeholder="0.00" value="{{ old('opening_balance', $customer->opening_balance ?? 0) }}">
                    </div>
                    <small class="text-muted">Amount the customer owes you (receivable). Enter a negative value if you owe them.</small>
                    @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', $customer->status ?? 1) == 1)>Active</option>
                        <option value="0" @selected(old('status', $customer->status ?? 1) == 0)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('customerCurrencySelect')?.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const code = selected.dataset.code || '—';
        document.getElementById('obCurrencyLabel').textContent = code;
    });
</script>
@endpush
