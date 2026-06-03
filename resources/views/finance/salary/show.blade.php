@extends('index')

@section('title', 'Salary Run - TradeSyncERP')

@section('content')
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
                    <a href="{{ route('salary.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-icon btn-light-brand printBTN">
                        <i class="feather-printer"></i>
                    </a>
                    @can('salary.pay')
                    @if(!$salaryRun->isPaid())
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payModal">
                        <i class="feather-dollar-sign me-2"></i>Mark as Paid
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card invoice-container">
                    <div class="card-header">
                        <div>
                            <h2 class="fs-16 fw-700 mb-0">Salary Run — {{ $salaryRun->month }}</h2>
                            <span class="fs-12 text-muted">Processed by {{ optional($salaryRun->processedBy)->name }}</span>
                        </div>
                        @php $statusColors = ['Draft'=>'warning','Paid'=>'success']; @endphp
                        <span class="badge bg-soft-{{ $statusColors[$salaryRun->status] ?? 'secondary' }} text-{{ $statusColors[$salaryRun->status] ?? 'secondary' }} fs-12">
                            {{ $salaryRun->status }}
                        </span>
                    </div>

                    <div class="card-body p-0">

                        {{-- Single form wraps header fields + lines so one Save submits everything --}}
                        <form id="linesForm" action="{{ route('salary.lines.update', $salaryRun) }}" method="POST">
                            @csrf @method('PUT')

                            {{-- ── Header meta section ── --}}
                            <div class="px-4 pt-4 pb-3">
                                <div class="d-sm-flex align-items-start justify-content-between gap-4">

                                    {{-- Left: run details (editable when Draft) --}}
                                    <div class="flex-grow-1">
                                        @if(!$salaryRun->isPaid())
                                        <div class="row g-3 mb-1">
                                            <div class="col-sm-6 col-lg-4">
                                                <label class="form-label fw-bold text-dark mb-1">Pay Account</label>
                                                <select name="account_id" class="form-select form-select-sm">
                                                    <option value="">— Select Account —</option>
                                                    @foreach($accounts as $acc)
                                                    <option value="{{ $acc->id }}" @selected($salaryRun->account_id == $acc->id)>
                                                        {{ $acc->account_name }} ({{ $acc->account_type }})
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-3 col-lg-2">
                                                <label class="form-label fw-bold text-dark mb-1">Working Days</label>
                                                <input type="number" name="working_days" min="0" max="31"
                                                       class="form-control form-control-sm"
                                                       value="{{ $salaryRun->working_days }}" placeholder="—">
                                            </div>
                                            <div class="col-sm-3 col-lg-2">
                                                <label class="form-label fw-bold text-dark mb-1">Off Days</label>
                                                <input type="number" name="off_days" min="0" max="31"
                                                       class="form-control form-control-sm"
                                                       value="{{ $salaryRun->off_days }}" placeholder="—">
                                            </div>
                                            <div class="col-sm-12 col-lg-4">
                                                <label class="form-label fw-bold text-dark mb-1">Remarks</label>
                                                <input type="text" name="remarks"
                                                       class="form-control form-control-sm"
                                                       value="{{ $salaryRun->remarks }}" placeholder="Batch notes…">
                                            </div>
                                        </div>
                                        <div class="fs-12 text-muted mt-1">
                                            Generated: {{ $salaryRun->created_at->format('d M Y') }}
                                        </div>
                                        @else
                                        {{-- Paid: read-only display --}}
                                        <div class="lh-lg">
                                            <div><span class="fw-bold text-dark">Pay Account:</span> <span class="text-muted">{{ optional($salaryRun->account)->account_name }}</span></div>
                                            <div><span class="fw-bold text-dark">Generated:</span> <span class="text-muted">{{ $salaryRun->created_at->format('d M Y') }}</span></div>
                                            @if($salaryRun->working_days || $salaryRun->off_days)
                                            <div>
                                                <span class="fw-bold text-dark">Days:</span>
                                                <span class="text-muted">
                                                    {{ $salaryRun->working_days ?? '—' }} working
                                                    {{ $salaryRun->off_days ? '+ '.$salaryRun->off_days.' off' : '' }}
                                                </span>
                                            </div>
                                            @endif
                                            @if($salaryRun->payment_date)
                                            <div><span class="fw-bold text-dark">Paid On:</span> <span class="text-muted">{{ $salaryRun->payment_date->format('d M Y') }}</span></div>
                                            @endif
                                            @if($salaryRun->remarks)
                                            <div><span class="fw-bold text-dark">Remarks:</span> <span class="text-muted">{{ $salaryRun->remarks }}</span></div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Right: live total --}}
                                    <div class="text-sm-end pt-3 pt-sm-0 flex-shrink-0">
                                        <div class="fs-12 text-muted">Total Net Payable</div>
                                        <div id="headerTotal" class="fs-28 fw-bolder text-dark">{{ number_format($salaryRun->total_net_payable, 2) }}</div>
                                        <div class="fs-12 text-muted">PKR</div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-dashed">

                            {{-- ── Salary Lines Table ── --}}
                            <div class="table-responsive">
                                <table class="table salary-sheet-table">
                                    <thead>
                                        <tr class="table-light">
                                            <th rowspan="2" class="align-middle" style="min-width:30px">#</th>
                                            <th rowspan="2" class="align-middle" style="min-width:140px">Employee</th>
                                            <th rowspan="2" class="align-middle text-end" style="min-width:110px">Basic Salary</th>
                                            <th rowspan="2" class="align-middle text-end" style="min-width:90px">Bonus</th>
                                            <th rowspan="2" class="align-middle text-end" style="min-width:100px">Allowances</th>
                                            <th rowspan="2" class="align-middle text-end" style="min-width:90px">Deduction</th>
                                            <th rowspan="2" class="align-middle text-end" style="min-width:110px">Salary Advance</th>
                                            <th colspan="3" class="text-center border-start border-end" style="min-width:280px">Leave</th>
                                            <th colspan="2" class="text-center border-start border-end" style="min-width:200px">Loan</th>
                                            <th colspan="3" class="text-center border-start border-end" style="min-width:280px">Late Arrival</th>
                                            <th rowspan="2" class="align-middle text-end fw-bold" style="min-width:110px">Net Pay</th>
                                            <th rowspan="2" class="align-middle" style="min-width:120px">Remarks</th>
                                        </tr>
                                        <tr class="table-light fs-12">
                                            <th class="text-center border-start">Days</th>
                                            <th class="text-center">Deductible</th>
                                            <th class="text-end border-end" style="min-width:100px">Deduction Amt</th>
                                            <th class="text-end border-start" style="min-width:110px">Balance</th>
                                            <th class="text-end border-end" style="min-width:90px">This Month</th>
                                            <th class="text-center border-start" style="min-width:70px">Hours</th>
                                            <th class="text-center" style="min-width:70px">Mins</th>
                                            <th class="text-end border-end" style="min-width:110px">Deduction</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salaryRun->lines as $i => $line)
                                        <input type="hidden" name="lines[{{ $i }}][id]" value="{{ $line->id }}">
                                        <tr>
                                            <td class="align-middle">{{ $i + 1 }}</td>
                                            <td class="fw-semibold text-dark align-middle">{{ $line->employee->employee_name }}</td>

                                            {{-- Basic Salary --}}
                                            <td class="align-middle text-end">
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->basic_salary, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][basic_salary]" value="{{ $line->basic_salary }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][basic_salary]"
                                                           class="form-control form-control-sm line-field text-end"
                                                           value="{{ $line->basic_salary }}" data-field="basic_salary">
                                                @endif
                                            </td>

                                            {{-- Bonus --}}
                                            <td class="align-middle text-end">
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->bonus, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][bonus]" value="{{ $line->bonus }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][bonus]"
                                                           class="form-control form-control-sm line-field text-end"
                                                           value="{{ $line->bonus }}" data-field="bonus">
                                                @endif
                                            </td>

                                            {{-- Allowances (breakdown) --}}
                                            <td class="align-middle" style="min-width:180px;">
                                                @if($salaryRun->isPaid())
                                                    {{-- Paid: show breakdown read-only --}}
                                                    @if($line->lineAllowances->count())
                                                        @foreach($line->lineAllowances as $la)
                                                        <div class="fs-12 text-muted d-flex justify-content-between">
                                                            <span>{{ $la->allowanceType?->name ?? 'Other' }}</span>
                                                            <span class="fw-semibold text-dark ms-2">{{ number_format($la->amount, 2) }}</span>
                                                        </div>
                                                        @endforeach
                                                        <div class="border-top mt-1 pt-1 fs-12 fw-bold text-end text-dark">
                                                            {{ number_format($line->allowances, 2) }}
                                                        </div>
                                                    @else
                                                        {{ number_format($line->allowances, 2) }}
                                                    @endif
                                                    <input type="hidden" name="lines[{{ $i }}][allowances]" value="{{ $line->allowances }}">
                                                @else
                                                    {{-- Draft: editable allowance rows --}}
                                                    <div class="allowance-rows" id="allowance-rows-{{ $i }}">
                                                        @forelse($line->lineAllowances as $ai => $la)
                                                        <div class="d-flex gap-1 mb-1 allowance-row">
                                                            <select name="lines[{{ $i }}][line_allowances][{{ $ai }}][allowance_type_id]"
                                                                    class="form-select form-select-sm" style="width:130px;">
                                                                <option value="">— Type —</option>
                                                                @foreach($allowanceTypes as $at)
                                                                <option value="{{ $at->id }}" @selected($la->allowance_type_id == $at->id)>{{ $at->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type="number" step="0.01" min="0"
                                                                   name="lines[{{ $i }}][line_allowances][{{ $ai }}][amount]"
                                                                   class="form-control form-control-sm allowance-amount text-end"
                                                                   value="{{ $la->amount }}"
                                                                   style="width:80px;"
                                                                   data-line="{{ $i }}">
                                                            <button type="button" class="btn btn-xs btn-light remove-allowance-row" title="Remove">×</button>
                                                        </div>
                                                        @empty
                                                        <div class="d-flex gap-1 mb-1 allowance-row">
                                                            <select name="lines[{{ $i }}][line_allowances][0][allowance_type_id]"
                                                                    class="form-select form-select-sm" style="width:130px;">
                                                                <option value="">— Type —</option>
                                                                @foreach($allowanceTypes as $at)
                                                                <option value="{{ $at->id }}">{{ $at->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type="number" step="0.01" min="0"
                                                                   name="lines[{{ $i }}][line_allowances][0][amount]"
                                                                   class="form-control form-control-sm allowance-amount text-end"
                                                                   value="0"
                                                                   style="width:80px;"
                                                                   data-line="{{ $i }}">
                                                            <button type="button" class="btn btn-xs btn-light remove-allowance-row" title="Remove">×</button>
                                                        </div>
                                                        @endforelse
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-between mt-1">
                                                        <button type="button"
                                                                class="btn btn-xs btn-light-brand add-allowance-row"
                                                                data-line="{{ $i }}"
                                                                data-types="{{ json_encode($allowanceTypes->map(fn($t) => ['id'=>$t->id,'name'=>$t->name])) }}">
                                                            <i class="feather-plus" style="font-size:10px;"></i>
                                                        </button>
                                                        <span class="fs-12 fw-semibold text-dark line-allowances-total" data-line="{{ $i }}">
                                                            {{ number_format($line->allowances, 2) }}
                                                        </span>
                                                        <input type="hidden" name="lines[{{ $i }}][allowances]"
                                                               class="allowances-hidden-total"
                                                               data-line="{{ $i }}"
                                                               value="{{ $line->allowances }}"
                                                               data-field="allowances">
                                                    </div>
                                                @endif
                                            </td>

                                            {{-- Deduction --}}
                                            <td class="align-middle text-end">
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->deduction, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][deduction]" value="{{ $line->deduction }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][deduction]"
                                                           class="form-control form-control-sm line-field text-end"
                                                           value="{{ $line->deduction }}" data-field="deduction">
                                                @endif
                                            </td>

                                            {{-- Salary Advance --}}
                                            <td class="align-middle text-end">
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->advance, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][advance]" value="{{ $line->advance }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][advance]"
                                                           class="form-control form-control-sm line-field text-end"
                                                           value="{{ $line->advance }}" data-field="advance">
                                                @endif
                                            </td>

                                            {{-- Leave: Days --}}
                                            <td class="align-middle text-center border-start">
                                                @if($salaryRun->isPaid())
                                                    {{ $line->leave_days }}
                                                    <input type="hidden" name="lines[{{ $i }}][leave_days]" value="{{ $line->leave_days }}">
                                                @else
                                                    <input type="number" min="0" name="lines[{{ $i }}][leave_days]"
                                                           class="form-control form-control-sm line-field text-center"
                                                           value="{{ $line->leave_days }}" data-field="leave_days">
                                                @endif
                                            </td>

                                            {{-- Leave: Deductible --}}
                                            <td class="align-middle text-center">
                                                @if($salaryRun->isPaid())
                                                    {{ $line->deductible_leaves }}
                                                    <input type="hidden" name="lines[{{ $i }}][deductible_leaves]" value="{{ $line->deductible_leaves }}">
                                                @else
                                                    <input type="number" min="0" name="lines[{{ $i }}][deductible_leaves]"
                                                           class="form-control form-control-sm line-field text-center"
                                                           value="{{ $line->deductible_leaves }}" data-field="deductible_leaves">
                                                @endif
                                            </td>

                                            {{-- Leave: Deduction Amount --}}
                                            <td class="align-middle text-end border-end">
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->leave_deduction_amount, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][leave_deduction_amount]" value="{{ $line->leave_deduction_amount }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][leave_deduction_amount]"
                                                           class="form-control form-control-sm line-field text-end"
                                                           value="{{ $line->leave_deduction_amount }}" data-field="leave_deduction_amount">
                                                @endif
                                            </td>

                                            {{-- Loan: Balance --}}
                                            <td class="align-middle text-end border-start">
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->loan_balance, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][loan_balance]" value="{{ $line->loan_balance }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][loan_balance]"
                                                           class="form-control form-control-sm line-field text-end"
                                                           value="{{ $line->loan_balance }}" data-field="loan_balance"
                                                           placeholder="Pending balance">
                                                @endif
                                            </td>

                                            {{-- Loan: This Month --}}
                                            <td class="align-middle text-end border-end">
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->loan_deduction, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][loan_deduction]" value="{{ $line->loan_deduction }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][loan_deduction]"
                                                           class="form-control form-control-sm line-field text-end"
                                                           value="{{ $line->loan_deduction }}" data-field="loan_deduction">
                                                @endif
                                            </td>

                                            {{-- Late: Hours --}}
                                            <td class="align-middle text-center border-start">
                                                @if($salaryRun->isPaid())
                                                    {{ $line->late_hours ?? 0 }}
                                                    <input type="hidden" name="lines[{{ $i }}][late_hours]" value="{{ $line->late_hours ?? 0 }}">
                                                @else
                                                    <input type="number" min="0" name="lines[{{ $i }}][late_hours]"
                                                           class="form-control form-control-sm line-field text-center"
                                                           value="{{ $line->late_hours ?? 0 }}" data-field="late_hours"
                                                           data-basic="{{ $line->basic_salary }}"
                                                           data-working-days="{{ $salaryRun->working_days ?: 26 }}"
                                                           placeholder="0">
                                                @endif
                                            </td>

                                            {{-- Late: Minutes --}}
                                            <td class="align-middle text-center">
                                                @if($salaryRun->isPaid())
                                                    {{ $line->late_minutes ?? 0 }}
                                                    <input type="hidden" name="lines[{{ $i }}][late_minutes]" value="{{ $line->late_minutes ?? 0 }}">
                                                @else
                                                    <input type="number" min="0" max="59" name="lines[{{ $i }}][late_minutes]"
                                                           class="form-control form-control-sm line-field text-center"
                                                           value="{{ $line->late_minutes ?? 0 }}" data-field="late_minutes"
                                                           placeholder="0">
                                                @endif
                                            </td>

                                            {{-- Late: Deduction (auto-calculated, editable) --}}
                                            <td class="align-middle text-end border-end">
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->late_deduction ?? 0, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][late_deduction]" value="{{ $line->late_deduction ?? 0 }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][late_deduction]"
                                                           class="form-control form-control-sm line-field text-end late-deduction-field"
                                                           value="{{ $line->late_deduction ?? 0 }}" data-field="late_deduction"
                                                           title="Auto-calculated; override here if needed">
                                                @endif
                                            </td>

                                            {{-- Net Pay --}}
                                            <td class="align-middle text-end fw-bold text-dark net-pay">
                                                {{ number_format($line->net_payable, 2) }}
                                            </td>

                                            {{-- Remarks --}}
                                            <td class="align-middle">
                                                @if($salaryRun->isPaid())
                                                    {{ $line->remarks ?? '—' }}
                                                    <input type="hidden" name="lines[{{ $i }}][remarks]" value="{{ $line->remarks }}">
                                                @else
                                                    <input type="text" name="lines[{{ $i }}][remarks]"
                                                           class="form-control form-control-sm"
                                                           value="{{ $line->remarks }}" placeholder="Optional">
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach

                                        <tr class="bg-gray-100 fw-semibold">
                                            <td colspan="15" class="text-end text-dark">Grand Total</td>
                                            <td class="text-end fw-bolder text-dark fs-16" id="grandTotal">
                                                {{ number_format($salaryRun->total_net_payable, 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            @can('salary.edit')
                            @if(!$salaryRun->isPaid())
                            <div class="px-4 pb-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-save me-2"></i>Save Changes
                                </button>
                            </div>
                            @endif
                            @endcan

                        </form>

                        @if($salaryRun->transaction)
                        <hr class="border-dashed">
                        <div class="px-4 pb-4">
                            <h6 class="fw-bold mb-3">Payment Transaction:</h6>
                            <div class="row g-0 mb-2">
                                <div class="col-sm-4 text-muted">Paid On:</div>
                                <div class="col-sm-8 fw-semibold">{{ \Carbon\Carbon::parse($salaryRun->transaction->transaction_date)->format('d M Y') }}</div>
                            </div>
                            <div class="row g-0">
                                <div class="col-sm-4 text-muted">Amount:</div>
                                <div class="col-sm-8 fw-semibold text-success">{{ number_format($salaryRun->transaction->amount, 2) }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
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

    function calcLateDeduction(row) {
        var hoursInput = row.querySelector('[data-field="late_hours"]');
        if (!hoursInput) return;
        var basic       = parseFloat(row.querySelector('[data-field="basic_salary"]')?.value) || 0;
        var workDays    = parseFloat(hoursInput.dataset.workingDays) || 26;
        var lateHours   = parseFloat(hoursInput.value)                               || 0;
        var lateMins    = parseFloat(row.querySelector('[data-field="late_minutes"]')?.value) || 0;
        var totalHours  = lateHours + (lateMins / 60);
        var hourlyRate  = (basic / workDays) / 8;
        var calculated  = Math.round(totalHours * hourlyRate * 100) / 100;
        var lateField   = row.querySelector('.late-deduction-field');
        if (lateField) lateField.value = calculated.toFixed(2);
    }

    function recalcRow(row) {
        var basic       = parseFloat(row.querySelector('[data-field="basic_salary"]')?.value)            || 0;
        var bonus       = parseFloat(row.querySelector('[data-field="bonus"]')?.value)                   || 0;
        var allowances  = parseFloat(row.querySelector('.allowances-hidden-total')?.value || row.querySelector('[data-field="allowances"]')?.value) || 0;
        var deduction   = parseFloat(row.querySelector('[data-field="deduction"]')?.value)               || 0;
        var advance     = parseFloat(row.querySelector('[data-field="advance"]')?.value)                 || 0;
        var leaveDeduct = parseFloat(row.querySelector('[data-field="leave_deduction_amount"]')?.value)  || 0;
        var loanDeduct  = parseFloat(row.querySelector('[data-field="loan_deduction"]')?.value)          || 0;
        var lateDeduct  = parseFloat(row.querySelector('[data-field="late_deduction"]')?.value)          || 0;
        return (basic + bonus + allowances) - (deduction + advance + leaveDeduct + loanDeduct + lateDeduct);
    }

    function syncTotals() {
        var total = 0;
        document.querySelectorAll('.net-pay').forEach(function (cell) {
            total += parseFloat(cell.textContent) || 0;
        });
        var formatted = total.toFixed(2);
        if (grandTotal)  grandTotal.textContent  = formatted;
        if (headerTotal) headerTotal.textContent  = formatted;
        if (modalTotal)  modalTotal.textContent   = formatted;
    }

    document.querySelectorAll('.line-field').forEach(function (input) {
        input.addEventListener('input', function () {
            var row   = this.closest('tr');
            var field = this.dataset.field;
            if (field === 'late_hours' || field === 'late_minutes') {
                calcLateDeduction(row);
            }
            var net = recalcRow(row);
            row.querySelector('.net-pay').textContent = net.toFixed(2);
            syncTotals();
        });
    });

    // ── Allowance breakdown row management ───────────────────────────────────
    function syncAllowanceTotal(lineIdx) {
        var total = 0;
        document.querySelectorAll('.allowance-amount[data-line="' + lineIdx + '"]').forEach(function (inp) {
            total += parseFloat(inp.value) || 0;
        });
        var hiddenTotal = document.querySelector('.allowances-hidden-total[data-line="' + lineIdx + '"]');
        var displayTotal = document.querySelector('.line-allowances-total[data-line="' + lineIdx + '"]');
        if (hiddenTotal) hiddenTotal.value = total.toFixed(2);
        if (displayTotal) displayTotal.textContent = total.toFixed(2);

        // Trigger net-pay recalc
        var anyInput = document.querySelector('.allowances-hidden-total[data-line="' + lineIdx + '"]');
        if (anyInput) {
            var row = anyInput.closest('tr');
            if (row) {
                var net = recalcRow(row);
                var netEl = row.querySelector('.net-pay');
                if (netEl) netEl.textContent = net.toFixed(2);
                syncTotals();
            }
        }
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('allowance-amount')) {
            syncAllowanceTotal(e.target.dataset.line);
        }
    });

    document.addEventListener('click', function (e) {
        // Add allowance row
        var addBtn = e.target.closest('.add-allowance-row');
        if (addBtn) {
            var lineIdx  = addBtn.dataset.line;
            var types    = JSON.parse(addBtn.dataset.types || '[]');
            var container = document.getElementById('allowance-rows-' + lineIdx);
            var rowCount  = container.querySelectorAll('.allowance-row').length;
            var opts = '<option value="">— Type —</option>' +
                types.map(function (t) { return '<option value="' + t.id + '">' + t.name + '</option>'; }).join('');
            var html = '<div class="d-flex gap-1 mb-1 allowance-row">' +
                '<select name="lines[' + lineIdx + '][line_allowances][' + rowCount + '][allowance_type_id]" class="form-select form-select-sm" style="width:130px;">' + opts + '</select>' +
                '<input type="number" step="0.01" min="0" name="lines[' + lineIdx + '][line_allowances][' + rowCount + '][amount]" class="form-control form-control-sm allowance-amount text-end" value="0" style="width:80px;" data-line="' + lineIdx + '">' +
                '<button type="button" class="btn btn-xs btn-light remove-allowance-row" title="Remove">×</button>' +
                '</div>';
            container.insertAdjacentHTML('beforeend', html);
        }

        // Remove allowance row
        var removeBtn = e.target.closest('.remove-allowance-row');
        if (removeBtn) {
            var row = removeBtn.closest('.allowance-row');
            var container = row.closest('[id^="allowance-rows-"]');
            var lineIdx = container ? container.id.replace('allowance-rows-', '') : null;
            if (container && container.querySelectorAll('.allowance-row').length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('input[type="number"]').forEach(function (i) { i.value = '0'; });
            }
            if (lineIdx) syncAllowanceTotal(lineIdx);
        }
    });

    // Keep modal amount up to date when it opens (reflects any unsaved live edits)
    var payModalEl = document.getElementById('payModal');
    if (payModalEl) {
        payModalEl.addEventListener('show.bs.modal', function () {
            if (modalTotal && headerTotal) {
                modalTotal.textContent = headerTotal.textContent;
            }
        });
    }
})();
</script>
@endpush
