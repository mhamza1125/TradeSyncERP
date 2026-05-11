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
                        <div class="px-4 pt-4 pb-3">
                            <div class="d-sm-flex align-items-start justify-content-between">
                                <div class="lh-lg">
                                    <div><span class="fw-bold text-dark">Pay Account:</span> <span class="text-muted">{{ optional($salaryRun->account)->account_name }}</span></div>
                                    <div><span class="fw-bold text-dark">Generated:</span> <span class="text-muted">{{ $salaryRun->created_at->format('d M Y') }}</span></div>
                                    @if($salaryRun->payment_date)
                                    <div><span class="fw-bold text-dark">Paid On:</span> <span class="text-muted">{{ $salaryRun->payment_date->format('d M Y') }}</span></div>
                                    @endif
                                </div>
                                <div class="text-sm-end pt-3 pt-sm-0">
                                    <div class="fs-12 text-muted">Total Net Payable</div>
                                    <div class="fs-28 fw-bolder text-dark">{{ number_format($salaryRun->total_net_payable, 2) }}</div>
                                    <div class="fs-12 text-muted">PKR</div>
                                </div>
                            </div>
                        </div>

                        <hr class="border-dashed">

                        {{-- Salary Lines Table --}}
                        <form id="linesForm" action="{{ route('salary.lines.update', $salaryRun) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Employee</th>
                                            <th>Basic Salary</th>
                                            <th>Bonus</th>
                                            <th>Deduction</th>
                                            <th>Advance</th>
                                            <th class="text-end fw-bold">Net Pay</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salaryRun->lines as $i => $line)
                                        <input type="hidden" name="lines[{{ $i }}][id]" value="{{ $line->id }}">
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td class="fw-semibold text-dark">{{ $line->employee->employee_name }}</td>
                                            <td>
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->basic_salary, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][basic_salary]" value="{{ $line->basic_salary }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][basic_salary]"
                                                           class="form-control form-control-sm line-field" style="min-width:110px"
                                                           value="{{ $line->basic_salary }}" data-field="basic_salary">
                                                @endif
                                            </td>
                                            <td>
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->bonus, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][bonus]" value="{{ $line->bonus }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][bonus]"
                                                           class="form-control form-control-sm line-field" style="min-width:90px"
                                                           value="{{ $line->bonus }}" data-field="bonus">
                                                @endif
                                            </td>
                                            <td>
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->deduction, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][deduction]" value="{{ $line->deduction }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][deduction]"
                                                           class="form-control form-control-sm line-field" style="min-width:90px"
                                                           value="{{ $line->deduction }}" data-field="deduction">
                                                @endif
                                            </td>
                                            <td>
                                                @if($salaryRun->isPaid())
                                                    {{ number_format($line->advance, 2) }}
                                                    <input type="hidden" name="lines[{{ $i }}][advance]" value="{{ $line->advance }}">
                                                @else
                                                    <input type="number" step="0.01" name="lines[{{ $i }}][advance]"
                                                           class="form-control form-control-sm line-field" style="min-width:90px"
                                                           value="{{ $line->advance }}" data-field="advance">
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold text-dark net-pay">
                                                {{ number_format($line->basic_salary + $line->bonus - $line->deduction - $line->advance, 2) }}
                                            </td>
                                            <td>
                                                @if($salaryRun->isPaid())
                                                    {{ $line->remarks ?? '—' }}
                                                    <input type="hidden" name="lines[{{ $i }}][remarks]" value="{{ $line->remarks }}">
                                                @else
                                                    <input type="text" name="lines[{{ $i }}][remarks]"
                                                           class="form-control form-control-sm" style="min-width:120px"
                                                           value="{{ $line->remarks }}" placeholder="Optional">
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                        <tr class="bg-gray-100">
                                            <td colspan="6" class="text-end fw-semibold text-dark">Grand Total</td>
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

{{-- Pay Modal --}}
@can('salary.pay')
@if(!$salaryRun->isPaid())
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('salary.pay', $salaryRun) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Payment — {{ $salaryRun->month }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="alert alert-soft-warning-message p-3">
                        Total to be paid: <strong>{{ number_format($salaryRun->total_net_payable, 2) }} PKR</strong><br>
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

@push('scripts')
<script>
document.querySelectorAll('.line-field').forEach(function(input) {
    input.addEventListener('input', function() {
        var row = this.closest('tr');
        var basic = parseFloat(row.querySelector('[data-field="basic_salary"]').value) || 0;
        var bonus = parseFloat(row.querySelector('[data-field="bonus"]').value) || 0;
        var deduction = parseFloat(row.querySelector('[data-field="deduction"]').value) || 0;
        var advance = parseFloat(row.querySelector('[data-field="advance"]').value) || 0;
        row.querySelector('.net-pay').textContent = (basic + bonus - deduction - advance).toFixed(2);

        var total = 0;
        document.querySelectorAll('.net-pay').forEach(function(cell) {
            total += parseFloat(cell.textContent) || 0;
        });
        document.getElementById('grandTotal').textContent = total.toFixed(2);
    });
});
</script>
@endpush
@endsection
