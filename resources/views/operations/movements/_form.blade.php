{{--
    Movement form partial — shared by create.blade.php and edit.blade.php.
    Callers keep the <form> tag (action, @csrf, @method('PUT'), hidden sample_id).

    Variables always present:  $sample
    Variables present on edit: $movement  (use isset($movement) to branch)
    Variables present on create only: $employees, $suppliers, $customers
--}}

<div class="row">

    {{-- ── Main content column ──────────────────────────────────────────────── --}}
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    {{ isset($movement) ? 'Return Information' : 'Movement Details' }}
                </h5>
            </div>
            <div class="card-body">

                @isset($movement)
                {{-- ── EDIT: read-only summary strip ──────────────────────── --}}
                <div class="alert alert-light border mb-4">
                    <div class="d-flex gap-4 flex-wrap">
                        <div><span class="text-muted fs-12">Sample</span><br><strong>{{ $sample->sample_code }}</strong></div>
                        <div>
                            <span class="text-muted fs-12">Assigned To</span><br>
                            <strong>
                                <span class="badge bg-soft-info text-info me-1">{{ $movement->assigned_to_type }}</span>
                                ID {{ $movement->assigned_to_id }}
                            </strong>
                        </div>
                        <div><span class="text-muted fs-12">Issue Date</span><br><strong>{{ $movement->issue_date->format('d M Y') }}</strong></div>
                        <div><span class="text-muted fs-12">Expected Return</span><br><strong>{{ $movement->expected_return_date?->format('d M Y') ?? '—' }}</strong></div>
                    </div>
                </div>

                {{-- ── EDIT: editable return fields ───────────────────────── --}}
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach(['Issued', 'Returned', 'Overdue'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $movement->status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Actual Return Date</label>
                        <input type="date" name="actual_return_date"
                               class="form-control @error('actual_return_date') is-invalid @enderror"
                               value="{{ old('actual_return_date', $movement->actual_return_date?->toDateString()) }}">
                        <div class="form-text">Fill this when the sample has been physically returned.</div>
                        @error('actual_return_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="3"
                                  class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Any notes about the return or current status…">{{ old('remarks', $movement->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @else
                {{-- ── CREATE: sample info strip ───────────────────────────── --}}
                <div class="alert alert-light border mb-4">
                    <div class="d-flex gap-4 flex-wrap">
                        <div><span class="text-muted fs-12">Sample</span><br><strong>{{ $sample->sample_code }}</strong></div>
                        <div><span class="text-muted fs-12">Product</span><br><strong>{{ $sample->product_name }}</strong></div>
                        <div><span class="text-muted fs-12">Customer</span><br><strong>{{ $sample->customer->customer_name }}</strong></div>
                    </div>
                </div>

                {{-- ── CREATE: full issue form ─────────────────────────────── --}}
                <div class="row">
                    {{-- Moved By type + dynamic selector --}}
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Moved By (Type) <span class="text-danger">*</span></label>
                        <select name="moved_by_type" id="movedByType"
                                class="form-select @error('moved_by_type') is-invalid @enderror" required>
                            <option value="Employee" @selected(old('moved_by_type', 'Employee') === 'Employee')>Employee</option>
                            <option value="User"     @selected(old('moved_by_type') === 'User')>System User (Self)</option>
                        </select>
                        @error('moved_by_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Moved By <span class="text-danger">*</span></label>
                        <div id="movedByEmployee">
                            <select name="moved_by_id" id="movedByIdEmployee"
                                    class="form-select @error('moved_by_id') is-invalid @enderror">
                                <option value="">— Select Employee —</option>
                                @foreach($employees as $e)
                                <option value="{{ $e->id }}" @selected(old('moved_by_id') == $e->id)>
                                    {{ $e->employee_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="movedByUser" style="display:none">
                            <input type="hidden" name="moved_by_id" id="movedByIdUser" value="{{ auth()->id() }}">
                            <input type="text" class="form-control"
                                   value="{{ auth()->user()->name ?? 'Current User' }}" disabled>
                            <small class="text-muted">Logged-in user will be recorded.</small>
                        </div>
                        @error('moved_by_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    {{-- Assigned To type + dynamic selector --}}
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Assign To (Type) <span class="text-danger">*</span></label>
                        <select name="assigned_to_type" id="assignedToType"
                                class="form-select @error('assigned_to_type') is-invalid @enderror" required>
                            <option value="">— Select Type —</option>
                            <option value="Employee" @selected(old('assigned_to_type') === 'Employee')>Employee</option>
                            <option value="Supplier" @selected(old('assigned_to_type') === 'Supplier')>Supplier / Factory</option>
                            <option value="Customer" @selected(old('assigned_to_type') === 'Customer')>Customer</option>
                            <option value="Storage"  @selected(old('assigned_to_type') === 'Storage')>Storage</option>
                        </select>
                        @error('assigned_to_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Assigned To <span class="text-danger">*</span></label>
                        <div id="atEmployee" class="at-panel" style="display:none">
                            <select name="assigned_to_id" class="form-select at-select">
                                <option value="">— Select Employee —</option>
                                @foreach($employees as $e)
                                <option value="{{ $e->id }}" @selected(old('assigned_to_id') == $e->id)>{{ $e->employee_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="atSupplier" class="at-panel" style="display:none">
                            <select name="assigned_to_id" class="form-select at-select">
                                <option value="">— Select Supplier —</option>
                                @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('assigned_to_id') == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="atCustomer" class="at-panel" style="display:none">
                            <select name="assigned_to_id" class="form-select at-select">
                                <option value="">— Select Customer —</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('assigned_to_id') == $c->id)>{{ $c->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="atStorage" class="at-panel" style="display:none">
                            <input type="number" name="assigned_to_id" class="form-control at-select"
                                   min="1" placeholder="Storage location ID (e.g. 1, 2, 3…)"
                                   value="{{ old('assigned_to_id') }}">
                            <small class="text-muted">Enter your storage bay / shelf number.</small>
                        </div>
                        <div id="atPlaceholder">
                            <input type="text" class="form-control" disabled placeholder="Select a type first">
                        </div>
                        @error('assigned_to_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    {{-- Dates --}}
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" name="issue_date"
                               class="form-control @error('issue_date') is-invalid @enderror"
                               value="{{ old('issue_date', now()->toDateString()) }}" required>
                        @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Expected Return Date</label>
                        <input type="date" name="expected_return_date"
                               class="form-control @error('expected_return_date') is-invalid @enderror"
                               value="{{ old('expected_return_date') }}">
                        @error('expected_return_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-lg-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="3"
                                  class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Optional notes about this movement…">{{ old('remarks') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                @endisset

            </div>
        </div>
    </div>

    {{-- ── Sidebar column ───────────────────────────────────────────────────── --}}
    <div class="col-xl-4">
        <div class="card">
            @isset($movement)
            {{-- EDIT sidebar: status guide --}}
            <div class="card-header"><h5 class="card-title">Status Guide</h5></div>
            <div class="card-body">
                <div class="alert alert-light border">
                    <small class="text-muted">
                        <strong class="text-primary">Issued</strong> — Sample is currently out.<br>
                        <strong class="text-success">Returned</strong> — Sample has been returned; set the actual return date.<br>
                        <strong class="text-danger">Overdue</strong> — Past expected return date with no return recorded.
                    </small>
                </div>
            </div>
            @else
            {{-- CREATE sidebar: alert days + assignment guide --}}
            <div class="card-header"><h5 class="card-title">Alert</h5></div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Alert Days Before Return</label>
                    <input type="number" name="alert_days" min="1"
                           class="form-control @error('alert_days') is-invalid @enderror"
                           placeholder="e.g. 3" value="{{ old('alert_days') }}">
                    <div class="form-text">Get alerted this many days before the expected return date.</div>
                    @error('alert_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="alert alert-light border">
                    <small class="text-muted">
                        <strong>Employee</strong> — internal staff handling the sample.<br>
                        <strong>Supplier / Factory</strong> — external supplier or processing factory.<br>
                        <strong>Customer</strong> — sample sent to the customer.<br>
                        <strong>Storage</strong> — moved to a storage bay/shelf.
                    </small>
                </div>
            </div>
            @endisset
        </div>
    </div>

</div>

{{-- Dynamic toggle JS — only needed on the create (issue) form --}}
@unless(isset($movement))
@push('scripts')
<script>
const movedByType   = document.getElementById('movedByType');
const movedByEmpDiv = document.getElementById('movedByEmployee');
const movedByUsrDiv = document.getElementById('movedByUser');
const movedByEmpSel = document.getElementById('movedByIdEmployee');

function toggleMovedBy() {
    const isUser = movedByType.value === 'User';
    movedByEmpDiv.style.display = isUser ? 'none' : 'block';
    movedByUsrDiv.style.display = isUser ? 'block' : 'none';
    movedByEmpSel.disabled = isUser;
}
movedByType.addEventListener('change', toggleMovedBy);
toggleMovedBy();

const assignedToType = document.getElementById('assignedToType');
const atPanels = {
    Employee: document.getElementById('atEmployee'),
    Supplier: document.getElementById('atSupplier'),
    Customer: document.getElementById('atCustomer'),
    Storage:  document.getElementById('atStorage'),
};
const atPlaceholder = document.getElementById('atPlaceholder');

function toggleAssignedTo() {
    const val = assignedToType.value;
    atPlaceholder.style.display = val ? 'none' : 'block';
    Object.entries(atPanels).forEach(([k, el]) => {
        const active = k === val;
        el.style.display = active ? 'block' : 'none';
        el.querySelectorAll('.at-select').forEach(i => { i.disabled = !active; });
    });
}
assignedToType.addEventListener('change', toggleAssignedTo);
toggleAssignedTo();
</script>
@endpush
@endunless
