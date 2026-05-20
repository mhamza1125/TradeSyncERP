{{-- Reusable form partial for salary run create/edit --}}
<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Salary Run Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Month <span class="text-danger">*</span></label>
                        <input type="month" name="month"
                               class="form-control @error('month') is-invalid @enderror"
                               value="{{ old('month', $salaryRun->month ?? now()->format('Y-m')) }}">
                        @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Select the month to generate salary for all active employees.</small>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Pay From Account <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select @error('account_id') is-invalid @enderror">
                            <option value="">— Select Account —</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}" @selected(old('account_id', $salaryRun->account_id ?? '') == $account->id)>
                                {{ $account->account_name }} ({{ $account->account_type }})
                            </option>
                            @endforeach
                        </select>
                        @error('account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Working Days</label>
                        <input type="number" name="working_days" min="0" max="31"
                               class="form-control @error('working_days') is-invalid @enderror"
                               value="{{ old('working_days', $salaryRun->working_days ?? '') }}" placeholder="e.g. 26">
                        @error('working_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Off Days <small class="text-muted">(holidays)</small></label>
                        <input type="number" name="off_days" min="0" max="31"
                               class="form-control @error('off_days') is-invalid @enderror"
                               value="{{ old('off_days', $salaryRun->off_days ?? '') }}" placeholder="e.g. 2">
                        @error('off_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Non-weekend public holidays.</small>
                    </div>
                    <div class="col-lg-4 mb-4 d-flex align-items-end">
                        <div class="w-100">
                            <label class="form-label text-muted">Total Days</label>
                            <div id="totalDaysDisplay" class="form-control bg-light text-center fw-semibold">—</div>
                        </div>
                    </div>
                    <div class="col-lg-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="2"
                                  class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Optional notes for this batch...">{{ old('remarks', $salaryRun->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @unless(isset($salaryRun))
                <div class="alert alert-soft-info p-3">
                    <div class="d-flex gap-3">
                        <i class="feather-info fs-20 text-info"></i>
                        <div>
                            <strong>Note:</strong> This will generate salary lines for all active employees
                            using their current basic salary. You can adjust bonuses, deductions, allowances,
                            and advances after the run is created.
                        </div>
                    </div>
                </div>
                @endunless
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
</script>
@endpush
