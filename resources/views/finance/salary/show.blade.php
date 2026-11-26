@extends('index')

@section('title', 'Salary Run - TradeSyncERP')

@section('content')
@php
    $paid = $salaryRun->isPaid();
    $statusColors = ['Draft' => 'warning', 'Paid' => 'success'];
    $canEdit = auth()->user()->can('salary.edit') && ! $paid;
    $allowanceTypesJson = json_encode($allowanceTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name]));
@endphp
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Salary Runs</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('salary.index') }}">Salary Runs</a></li>
                <li class="breadcrumb-item">{{ $salaryRun->month }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('salary.index') }}" class="btn btn-icon btn-light-brand" aria-label="Back to salary runs">
                        <i class="feather-arrow-left"></i>
                    </a>
                    <a href="{{ route('salary.export-pdf', $salaryRun) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i>Export PDF
                    </a>
                    @can('salary.delete')
                    @if(!$paid)
                    <form action="{{ route('salary.destroy', $salaryRun) }}" method="POST" class="d-inline-flex"
                          onsubmit="return confirm('Delete the draft salary run for {{ $salaryRun->month }}? Its lines are deleted too.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-light-brand text-danger">
                            <i class="feather-trash-2 me-2"></i>Delete
                        </button>
                    </form>
                    @endif
                    @endcan
                    @can('salary.pay')
                    @if(!$paid)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payModal">
                        <i class="feather-dollar-sign me-2"></i>Mark as Paid
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content ts-dense">
        @include('partials.flash-messages')

        <div class="card">
            {{-- One form holds the run details and every line, so one Save submits everything --}}
            <form id="linesForm" action="{{ route('salary.lines.update', $salaryRun) }}" method="POST">
                @csrf @method('PUT')

                {{-- ── Header: title, status, run details, live total ── --}}
                <div class="card-header py-3">
                    <div>
                        <h2 class="fs-16 fw-bold mb-0 d-flex align-items-center gap-2">
                            Salary Run — {{ $salaryRun->month }}
                            <span class="badge bg-soft-{{ $statusColors[$salaryRun->status] ?? 'secondary' }} text-{{ $statusColors[$salaryRun->status] ?? 'secondary' }} fs-11">{{ $salaryRun->status }}</span>
                        </h2>
                        <span class="fs-12 text-muted">
                            {{ $salaryRun->lines->count() }} employees · generated {{ $salaryRun->created_at->format('d M Y') }}
                            @if($salaryRun->processedBy) by {{ $salaryRun->processedBy->name }} @endif
                            @if($paid && $salaryRun->payment_date) · paid {{ $salaryRun->payment_date->format('d M Y') }} @endif
                        </span>
                    </div>
                    @if($canEdit)
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="feather-save me-1"></i>Save Changes
                    </button>
                    @endif
                </div>

                <div class="card-body py-3">
                    <div class="row g-2 align-items-end">
                        @if(!$paid)
                        <div class="col-sm-6 col-lg-3">
                            <label class="form-label" for="runAccount">Pay account</label>
                            <select id="runAccount" name="account_id" class="form-select form-select-sm">
                                <option value="">— Select account —</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" @selected($salaryRun->account_id == $acc->id)>{{ $acc->account_name }} ({{ $acc->account_type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-sm-3 col-lg-1">
                            <label class="form-label" for="runWorking">Working days</label>
                            <input id="runWorking" type="number" name="working_days" min="0" max="31" class="form-control form-control-sm" value="{{ $salaryRun->working_days }}" placeholder="—">
                        </div>
                        <div class="col-6 col-sm-3 col-lg-1">
                            <label class="form-label" for="runOff">Off days</label>
                            <input id="runOff" type="number" name="off_days" min="0" max="31" class="form-control form-control-sm" value="{{ $salaryRun->off_days }}" placeholder="—">
                        </div>
                        <div class="col-sm-12 col-lg-4">
                            <label class="form-label" for="runRemarks">Remarks</label>
                            <input id="runRemarks" type="text" name="remarks" class="form-control form-control-sm" value="{{ $salaryRun->remarks }}" placeholder="Batch notes…">
                        </div>
                        @else
                        <div class="col-lg-9">
                            <div class="d-flex flex-wrap gap-4 fs-13">
                                <div><div class="fs-11 text-muted text-uppercase fw-bold">Pay account</div>{{ optional($salaryRun->account)->account_name ?? '—' }}</div>
                                <div><div class="fs-11 text-muted text-uppercase fw-bold">Days</div>{{ $salaryRun->working_days ?? '—' }} working{{ $salaryRun->off_days ? ' + '.$salaryRun->off_days.' off' : '' }}</div>
                                @if($salaryRun->transaction)
                                <div><div class="fs-11 text-muted text-uppercase fw-bold">Payment</div>{{ \Carbon\Carbon::parse($salaryRun->transaction->transaction_date)->format('d M Y') }} · <span class="text-success fw-semibold">{{ number_format($salaryRun->transaction->amount, 2) }}</span></div>
                                @endif
                                @if($salaryRun->remarks)
                                <div><div class="fs-11 text-muted text-uppercase fw-bold">Remarks</div>{{ $salaryRun->remarks }}</div>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div class="col-lg-3 {{ $paid ? '' : 'ms-lg-auto' }}">
                            <div class="rounded border px-3 py-2 text-lg-end" style="background: var(--ts-primary-subtle); border-color: var(--ts-primary-border) !important;">
                                <div class="fs-11 text-uppercase fw-bold" style="color: var(--ts-on-primary-subtle)">Total net payable</div>
                                <div class="fs-20 fw-bolder text-dark lh-sm"><span id="headerTotal">{{ number_format($salaryRun->total_net_payable, 2) }}</span> <span class="fs-12 fw-semibold text-muted">PKR</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Salary lines ── --}}
                <div class="table-responsive border-top">
                    <table class="table table-hover ts-sheet align-middle">
                        <thead>
                            <tr>
                                <th rowspan="2" class="ts-sheet-pin" style="min-width:160px">Employee</th>
                                <th rowspan="2" class="text-end" style="min-width:100px">Basic</th>
                                <th rowspan="2" class="text-end" style="min-width:85px">Bonus</th>
                                <th rowspan="2" style="min-width:{{ $paid ? 150 : 250 }}px">Allowances</th>
                                <th rowspan="2" class="text-end" style="min-width:85px">Deduction</th>
                                <th rowspan="2" class="text-end" style="min-width:85px">Advance</th>
                                <th colspan="3" class="text-center border-start border-end">Leave</th>
                                <th colspan="2" class="text-center border-start border-end">Loan</th>
                                <th colspan="3" class="text-center border-start border-end">
                                    Late arrival
                                    <i class="feather-help-circle ms-1"
                                       data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-html="true" tabindex="0"
                                       title="Late arrival deduction"
                                       data-bs-content="<strong>Formula:</strong><br>Daily rate = Basic ÷ Working days<br>Hourly rate = Daily rate ÷ 8<br>Deduction = (Hours + Mins÷60) × Hourly rate<br><br><em>Example:</em> 30,000 ÷ 26 ÷ 8 = 144.23/hr<br>3h 30m late → 3.5 × 144.23 = <strong>504.81</strong><br><br><small>Auto-calculated; can be overridden.</small>"
                                       style="cursor:pointer;"></i>
                                </th>
                                <th rowspan="2" class="text-end" style="min-width:105px">Net pay</th>
                                <th rowspan="2" style="min-width:140px">Remarks</th>
                            </tr>
                            <tr>
                                <th class="text-center border-start" style="min-width:58px">Days</th>
                                <th class="text-center" style="min-width:58px">Deduct.</th>
                                <th class="text-end border-end" style="min-width:90px">Amount</th>
                                <th class="text-end border-start" style="min-width:95px">Balance</th>
                                <th class="text-end border-end" style="min-width:90px">This month</th>
                                <th class="text-center border-start" style="min-width:52px">Hrs</th>
                                <th class="text-center" style="min-width:52px">Mins</th>
                                <th class="text-end border-end" style="min-width:90px">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaryRun->lines as $i => $line)
                            @php $who = optional($line->employee)->employee_name ?? 'employee '.($i + 1); @endphp
                            <tr>
                                <td class="ts-sheet-pin fw-semibold text-dark">
                                    <input type="hidden" name="lines[{{ $i }}][id]" value="{{ $line->id }}">
                                    <span class="text-muted fw-normal me-1">{{ $i + 1 }}.</span>{{ optional($line->employee)->employee_name ?? '—' }}
                                    @if(!$line->employee)
                                    <span class="badge bg-soft-danger text-danger fs-10 ms-1" title="Employee record deleted">Deleted</span>
                                    @endif
                                </td>

                                <td class="text-end">@include('finance.salary._line-cell', ['field' => 'basic_salary', 'value' => $line->basic_salary, 'label' => "Basic salary for $who"])</td>
                                <td class="text-end">@include('finance.salary._line-cell', ['field' => 'bonus', 'value' => $line->bonus, 'label' => "Bonus for $who"])</td>

                                {{-- Allowances (breakdown) --}}
                                <td>
                                    @if($paid)
                                        @foreach($line->lineAllowances as $la)
                                        <div class="fs-12 text-muted d-flex justify-content-between">
                                            <span>{{ $la->allowanceType?->name ?? 'Other' }}</span>
                                            <span class="text-dark ms-2">{{ number_format($la->amount, 2) }}</span>
                                        </div>
                                        @endforeach
                                        <div class="fw-semibold text-end text-dark {{ $line->lineAllowances->count() ? 'border-top mt-1 pt-1' : '' }}">{{ number_format($line->allowances, 2) }}</div>
                                        <input type="hidden" name="lines[{{ $i }}][allowances]" value="{{ $line->allowances }}">
                                    @else
                                        <div class="allowance-rows" id="allowance-rows-{{ $i }}">
                                            @php $rows = $line->lineAllowances->count() ? $line->lineAllowances : collect([null]); @endphp
                                            @foreach($rows as $ai => $la)
                                            <div class="d-flex gap-1 mb-1 allowance-row">
                                                <select name="lines[{{ $i }}][line_allowances][{{ $ai }}][allowance_type_id]" class="form-select" data-native
                                                        aria-label="Allowance type for {{ $who }}" style="min-width:120px">
                                                    <option value="">— Type —</option>
                                                    @foreach($allowanceTypes as $at)
                                                    <option value="{{ $at->id }}" @selected($la && $la->allowance_type_id == $at->id)>{{ $at->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="number" step="0.01" min="0"
                                                       name="lines[{{ $i }}][line_allowances][{{ $ai }}][amount]"
                                                       class="form-control allowance-amount text-end" style="width:84px"
                                                       value="{{ $la->amount ?? 0 }}" data-line="{{ $i }}" aria-label="Allowance amount for {{ $who }}">
                                                <button type="button" class="btn btn-xs btn-light remove-allowance-row px-2" title="Remove" aria-label="Remove allowance">×</button>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <button type="button" class="btn btn-xs btn-light-brand add-allowance-row px-2 py-0"
                                                    data-line="{{ $i }}" data-types="{{ $allowanceTypesJson }}">
                                                <i class="feather-plus fs-10 me-1"></i><span class="fs-11">Add</span>
                                            </button>
                                            <span class="fs-12 fw-semibold text-dark line-allowances-total" data-line="{{ $i }}">{{ number_format($line->allowances, 2) }}</span>
                                            <input type="hidden" name="lines[{{ $i }}][allowances]" class="allowances-hidden-total"
                                                   data-line="{{ $i }}" value="{{ $line->allowances }}" data-field="allowances">
                                        </div>
                                    @endif
                                </td>

                                <td class="text-end">@include('finance.salary._line-cell', ['field' => 'deduction', 'value' => $line->deduction, 'label' => "Deduction for $who"])</td>
                                <td class="text-end">@include('finance.salary._line-cell', ['field' => 'advance', 'value' => $line->advance, 'label' => "Salary advance for $who"])</td>

                                <td class="text-center border-start">@include('finance.salary._line-cell', ['field' => 'leave_days', 'value' => $line->leave_days, 'int' => true, 'align' => 'center', 'label' => "Leave days for $who"])</td>
                                <td class="text-center">@include('finance.salary._line-cell', ['field' => 'deductible_leaves', 'value' => $line->deductible_leaves, 'int' => true, 'align' => 'center', 'label' => "Deductible leaves for $who"])</td>
                                <td class="text-end border-end">@include('finance.salary._line-cell', ['field' => 'leave_deduction_amount', 'value' => $line->leave_deduction_amount, 'label' => "Leave deduction for $who"])</td>

                                <td class="text-end border-start">@include('finance.salary._line-cell', ['field' => 'loan_balance', 'value' => $line->loan_balance, 'label' => "Loan balance for $who", 'attrs' => ['placeholder' => 'Balance']])</td>
                                <td class="text-end border-end">@include('finance.salary._line-cell', ['field' => 'loan_deduction', 'value' => $line->loan_deduction, 'label' => "Loan deduction this month for $who"])</td>

                                <td class="text-center border-start">@include('finance.salary._line-cell', ['field' => 'late_hours', 'value' => $line->late_hours ?? 0, 'int' => true, 'align' => 'center', 'label' => "Late hours for $who",
                                    'attrs' => ['data-basic' => $line->basic_salary, 'data-working-days' => $salaryRun->working_days ?: 26]])</td>
                                <td class="text-center">@include('finance.salary._line-cell', ['field' => 'late_minutes', 'value' => $line->late_minutes ?? 0, 'int' => true, 'align' => 'center', 'label' => "Late minutes for $who", 'attrs' => ['max' => 59]])</td>
                                <td class="text-end border-end">@include('finance.salary._line-cell', ['field' => 'late_deduction', 'value' => $line->late_deduction ?? 0, 'label' => "Late deduction for $who",
                                    'extraClass' => 'late-deduction-field', 'attrs' => ['title' => 'Auto-calculated; override here if needed']])</td>

                                <td class="text-end fw-bold text-dark net-pay">{{ number_format($line->net_payable, 2) }}</td>

                                <td>
                                    @if($paid)
                                        <span class="text-muted">{{ $line->remarks ?? '—' }}</span>
                                        <input type="hidden" name="lines[{{ $i }}][remarks]" value="{{ $line->remarks }}">
                                    @else
                                        <input type="text" name="lines[{{ $i }}][remarks]" class="form-control" value="{{ $line->remarks }}" placeholder="Optional" aria-label="Remarks for {{ $who }}">
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                            <tr class="ts-sheet-total fw-semibold">
                                <td class="ts-sheet-pin text-dark">Grand total</td>
                                <td colspan="13"></td>
                                <td class="text-end fw-bolder text-dark fs-14" id="grandTotal">{{ number_format($salaryRun->total_net_payable, 2) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if($canEdit)
                <div class="card-footer d-flex align-items-center justify-content-between py-2">
                    <span class="fs-12 text-muted">Net pay updates as you type; nothing is saved until you click Save.</span>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="feather-save me-1"></i>Save Changes
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

{{-- Pay Modal --}}
@push('modals')
@can('salary.pay')
@if(!$salaryRun->isPaid())
<div class="modal fade" id="payModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('salary.pay', $salaryRun) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="payModalLabel">Confirm Payment — {{ $salaryRun->month }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="alert alert-soft-warning-message p-3">
                        Total to be paid: <strong id="modalTotal">{{ number_format($salaryRun->total_net_payable, 2) }}</strong> PKR<br>
                        From: <strong>{{ optional($salaryRun->account)->account_name }}</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-brand" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="feather-check me-1"></i>Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan
@endpush

@push('scripts')
<script>
(function () {
    var headerTotal = document.getElementById('headerTotal');
    var grandTotal  = document.getElementById('grandTotal');
    var modalTotal  = document.getElementById('modalTotal');
    var num = function (el) { return el ? (parseFloat(el.value) || 0) : 0; };
    var money = function (n) { return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    var parseMoney = function (s) { return parseFloat(String(s).replace(/,/g, '')) || 0; };

    function calcLateDeduction(row) {
        var hoursInput = row.querySelector('[data-field="late_hours"]');
        if (!hoursInput) return;
        var basic      = num(row.querySelector('[data-field="basic_salary"]'));
        var workDays   = parseFloat(hoursInput.dataset.workingDays) || 26;
        var totalHours = num(hoursInput) + num(row.querySelector('[data-field="late_minutes"]')) / 60;
        var calculated = Math.round(totalHours * (basic / workDays / 8) * 100) / 100;
        var lateField  = row.querySelector('.late-deduction-field');
        if (lateField) lateField.value = calculated.toFixed(2);
    }

    function recalcRow(row) {
        var f = function (name) { return num(row.querySelector('[data-field="' + name + '"]')); };
        var allowances = num(row.querySelector('.allowances-hidden-total') || row.querySelector('[data-field="allowances"]'));
        return (f('basic_salary') + f('bonus') + allowances)
             - (f('deduction') + f('advance') + f('leave_deduction_amount') + f('loan_deduction') + f('late_deduction'));
    }

    function refreshRow(row) {
        var netEl = row.querySelector('.net-pay');
        if (netEl) netEl.textContent = money(recalcRow(row));
        syncTotals();
    }

    function syncTotals() {
        var total = 0;
        document.querySelectorAll('.net-pay').forEach(function (cell) { total += parseMoney(cell.textContent); });
        var formatted = money(total);
        if (grandTotal)  grandTotal.textContent  = formatted;
        if (headerTotal) headerTotal.textContent = formatted;
        if (modalTotal)  modalTotal.textContent  = formatted;
    }

    document.querySelectorAll('.line-field').forEach(function (input) {
        input.addEventListener('input', function () {
            var row = this.closest('tr');
            if (this.dataset.field === 'late_hours' || this.dataset.field === 'late_minutes' || this.dataset.field === 'basic_salary') {
                calcLateDeduction(row);
            }
            refreshRow(row);
        });
    });

    // ── Allowance breakdown rows ─────────────────────────────────────────────
    function syncAllowanceTotal(lineIdx) {
        var total = 0;
        document.querySelectorAll('.allowance-amount[data-line="' + lineIdx + '"]').forEach(function (inp) { total += num(inp); });
        var hidden  = document.querySelector('.allowances-hidden-total[data-line="' + lineIdx + '"]');
        var display = document.querySelector('.line-allowances-total[data-line="' + lineIdx + '"]');
        if (hidden)  hidden.value = total.toFixed(2);
        if (display) display.textContent = money(total);
        if (hidden && hidden.closest('tr')) refreshRow(hidden.closest('tr'));
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('allowance-amount')) syncAllowanceTotal(e.target.dataset.line);
    });

    document.addEventListener('click', function (e) {
        var addBtn = e.target.closest('.add-allowance-row');
        if (addBtn) {
            var lineIdx   = addBtn.dataset.line;
            var types     = JSON.parse(addBtn.dataset.types || '[]');
            var container = document.getElementById('allowance-rows-' + lineIdx);
            // Next free index (rows can be removed, so the count alone may collide)
            var next = 0;
            container.querySelectorAll('select').forEach(function (s) {
                var m = s.name.match(/\[line_allowances\]\[(\d+)\]/);
                if (m) next = Math.max(next, parseInt(m[1], 10) + 1);
            });
            var row = document.createElement('div');
            row.className = 'd-flex gap-1 mb-1 allowance-row';
            var select = document.createElement('select');
            select.name = 'lines[' + lineIdx + '][line_allowances][' + next + '][allowance_type_id]';
            select.className = 'form-select';
            select.setAttribute('data-native', '');
            select.setAttribute('aria-label', 'Allowance type');
            select.style.minWidth = '120px';
            select.add(new Option('— Type —', ''));
            types.forEach(function (t) { select.add(new Option(t.name, t.id)); });
            var amount = document.createElement('input');
            amount.type = 'number'; amount.step = '0.01'; amount.min = '0'; amount.value = '0';
            amount.name = 'lines[' + lineIdx + '][line_allowances][' + next + '][amount]';
            amount.className = 'form-control allowance-amount text-end';
            amount.style.width = '84px';
            amount.dataset.line = lineIdx;
            amount.setAttribute('aria-label', 'Allowance amount');
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-xs btn-light remove-allowance-row px-2';
            remove.title = 'Remove';
            remove.setAttribute('aria-label', 'Remove allowance');
            remove.textContent = '×';
            row.append(select, amount, remove);
            container.appendChild(row);
            select.focus();
        }

        var removeBtn = e.target.closest('.remove-allowance-row');
        if (removeBtn) {
            var aRow = removeBtn.closest('.allowance-row');
            var box  = aRow.closest('[id^="allowance-rows-"]');
            var idx  = box ? box.id.replace('allowance-rows-', '') : null;
            if (box && box.querySelectorAll('.allowance-row').length > 1) {
                aRow.remove();
            } else {
                aRow.querySelectorAll('input[type="number"]').forEach(function (i) { i.value = '0'; });
                aRow.querySelectorAll('select').forEach(function (s) { s.value = ''; });
            }
            if (idx) syncAllowanceTotal(idx);
        }
    });

    // An allowance amount needs a type (the server drops untyped rows). Check before posting,
    // so a refused save doesn't reload the page and lose the unsaved edits.
    var linesForm = document.getElementById('linesForm');
    if (linesForm) {
        linesForm.addEventListener('submit', function (e) {
            var firstBad = null;
            linesForm.querySelectorAll('.allowance-row').forEach(function (aRow) {
                var select = aRow.querySelector('select');
                var bad = select && !select.value && num(aRow.querySelector('.allowance-amount')) > 0;
                if (select) select.classList.toggle('is-invalid', !!bad);
                if (bad && !firstBad) firstBad = select;
            });
            if (firstBad) {
                e.preventDefault();
                firstBad.focus();
                alert('Choose a type for every allowance with an amount, or set its amount to 0. The rows are marked in red.');
            }
        });
        linesForm.addEventListener('change', function (e) {
            if (e.target.matches('.allowance-row select') && e.target.value) e.target.classList.remove('is-invalid');
        });
    }

    // Bootstrap popover for the late-arrival help icon
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) { new bootstrap.Popover(el); });

    // The pay modal shows the live (possibly unsaved) total
    var payModalEl = document.getElementById('payModal');
    if (payModalEl) {
        payModalEl.addEventListener('show.bs.modal', function () {
            if (modalTotal && headerTotal) modalTotal.textContent = headerTotal.textContent;
        });
    }
})();
</script>
@endpush
