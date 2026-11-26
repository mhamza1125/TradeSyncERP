{{-- Reusable form partial for salary run create/edit --}}
<div class="row justify-content-center ts-dense">
    <div class="col-xl-8 col-xxl-7">
        <div class="card">
            <div class="card-header py-3">
                <h5 class="card-title mb-0">Salary Run Details</h5>
            </div>
            <div class="card-body py-3">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label" for="salaryMonth">Month <span class="text-danger">*</span></label>
                        <input id="salaryMonth" type="month" name="month"
                               class="form-control form-control-sm @error('month') is-invalid @enderror"
                               value="{{ old('month', $salaryRun->month ?? now()->format('Y-m')) }}">
                        @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-7">
                        <label class="form-label" for="salaryAccount">Pay from account <span class="text-danger">*</span></label>
                        <select id="salaryAccount" name="account_id" class="form-select form-select-sm @error('account_id') is-invalid @enderror">
                            <option value="">— Select account —</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('account_id', $salaryRun->account_id ?? '') == $account->id)>
                                {{ $account->account_name }} ({{ $account->account_type }})
                            </option>
                            @endforeach
                        </select>
                        @error('account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label" for="salaryWorking">Working days</label>
                        <input id="salaryWorking" type="number" name="working_days" min="0" max="31"
                               class="form-control form-control-sm @error('working_days') is-invalid @enderror"
                               value="{{ old('working_days', $salaryRun->working_days ?? '') }}" placeholder="e.g. 26">
                        @error('working_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="salaryOff">Off days</label>
                        <input id="salaryOff" type="number" name="off_days" min="0" max="31"
                               class="form-control form-control-sm @error('off_days') is-invalid @enderror"
                               value="{{ old('off_days', $salaryRun->off_days ?? '') }}" placeholder="e.g. 2">
                        @error('off_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="salaryRemarks">Remarks</label>
                        <input id="salaryRemarks" type="text" name="remarks"
                               class="form-control form-control-sm @error('remarks') is-invalid @enderror"
                               value="{{ old('remarks', $salaryRun->remarks ?? '') }}" placeholder="Optional notes for this batch">
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mt-1">
                        <div class="form-text mt-0">
                            Off days are non-weekend public holidays. Total days: <strong id="totalDaysDisplay" class="text-dark">—</strong>
                        </div>
                    </div>
                </div>

                @unless(isset($salaryRun))
                <div class="alert alert-soft-info d-flex gap-2 align-items-start py-2 px-3 mt-3 mb-0 fs-12">
                    <i class="feather-info text-info mt-1"></i>
                    <div>
                        Generates a salary line for every active employee from their current basic salary.
                        Bonuses, deductions, allowances and advances can be adjusted after the run is created.
                    </div>
                </div>
                @endunless
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const workingDaysInput = document.querySelector('[name="working_days"]');
        const offDaysInput     = document.querySelector('[name="off_days"]');
        const totalDisplay     = document.getElementById('totalDaysDisplay');

        function updateTotal() {
            const w = parseInt(workingDaysInput.value) || 0;
            const o = parseInt(offDaysInput.value) || 0;
            totalDisplay.textContent = (w + o) > 0 ? (w + o) + ' days' : '—';
        }

        workingDaysInput.addEventListener('input', updateTotal);
        offDaysInput.addEventListener('input', updateTotal);
        updateTotal();
    })();
</script>
@endpush
