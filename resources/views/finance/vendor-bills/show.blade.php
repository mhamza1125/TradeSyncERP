@extends('index')

@section('title', 'Bill: {{ $vendorBill->bill_number }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Vendor Bills</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vendor-bills.index') }}">Vendor Bills</a></li>
                <li class="breadcrumb-item">{{ $vendorBill->bill_number }}</li>
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
                    <a href="{{ route('vendor-bills.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    <a href="javascript:void(0)" class="btn btn-icon btn-light-brand printBTN">
                        <i class="feather-printer"></i>
                    </a>
                    @can('vendor-bills.edit')
                    @if($vendorBill->status !== 'Paid')
                    <a href="{{ route('vendor-bills.edit', $vendorBill) }}" class="btn btn-light-brand">
                        <i class="feather-edit me-2"></i>Edit
                    </a>
                    @endif
                    @endcan
                    @can('vendor-bills.pay')
                    @if($vendorBill->status !== 'Paid')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payModal">
                        <i class="feather-dollar-sign me-2"></i>Mark as Paid
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content container-lg">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card invoice-container">
                    <div class="card-header">
                        <div>
                            <h2 class="fs-16 fw-700 text-truncate-1-line mb-0">Bill Preview</h2>
                            <span class="fs-12 text-muted">{{ $vendorBill->bill_number }}</span>
                        </div>
                        @php $statusColors = ['Paid'=>'success','Unpaid'=>'warning','Partial'=>'info','Overdue'=>'danger']; @endphp
                        <span class="badge bg-soft-{{ $statusColors[$vendorBill->status] ?? 'secondary' }} text-{{ $statusColors[$vendorBill->status] ?? 'secondary' }} fs-12">
                            {{ $vendorBill->status }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="px-4 pt-4">
                            <div class="d-sm-flex align-items-start justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-2">Billed From:</h6>
                                    <address class="text-muted lh-lg">
                                        <strong class="text-dark">{{ $vendorBill->vendor->vendor_name }}</strong><br>
                                        {{ $vendorBill->vendor->company_name }}<br>
                                        {{ $vendorBill->vendor->phone }}<br>
                                        {{ $vendorBill->vendor->email ?? '' }}
                                    </address>
                                </div>
                                <div class="lh-lg pt-3 pt-sm-0">
                                    <div><span class="fw-bold text-dark">Bill #:</span> <span class="text-primary fw-bold">{{ $vendorBill->bill_number }}</span></div>
                                    <div><span class="fw-bold text-dark">Bill Date:</span> <span class="text-muted">{{ \Carbon\Carbon::parse($vendorBill->bill_date)->format('d M Y') }}</span></div>
                                    @if($vendorBill->due_date)
                                    <div><span class="fw-bold text-dark">Due Date:</span> <span class="text-muted">{{ \Carbon\Carbon::parse($vendorBill->due_date)->format('d M Y') }}</span></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr class="border-dashed">

                        {{-- Line Items --}}
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vendorBill->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-dark fw-semibold">{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="3"></td>
                                        <td class="fw-semibold text-dark bg-gray-100 text-end">Grand Total</td>
                                        <td class="fw-bolder text-dark bg-gray-100 fs-16">{{ number_format($vendorBill->total_amount, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        @if($vendorBill->remarks)
                        <div class="px-4 pb-4">
                            <div class="alert alert-soft-warning-message p-3">
                                <strong>Notes:</strong> {{ $vendorBill->remarks }}
                            </div>
                        </div>
                        @endif

                        @if($vendorBill->transaction)
                        <hr class="border-dashed">
                        <div class="px-4 pb-4">
                            <h6 class="fw-bold mb-3">Payment Transaction:</h6>
                            <div class="row g-0 mb-2">
                                <div class="col-sm-4 text-muted">Paid On:</div>
                                <div class="col-sm-8 fw-semibold">{{ \Carbon\Carbon::parse($vendorBill->transaction->transaction_date)->format('d M Y') }}</div>
                            </div>
                            <div class="row g-0 mb-2">
                                <div class="col-sm-4 text-muted">Amount:</div>
                                <div class="col-sm-8 fw-semibold text-success">{{ number_format($vendorBill->transaction->amount, 2) }}</div>
                            </div>
                            @if($vendorBill->transaction->remarks)
                            <div class="row g-0">
                                <div class="col-sm-4 text-muted">Remarks:</div>
                                <div class="col-sm-8">{{ $vendorBill->transaction->remarks }}</div>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pay Modal --}}
@can('vendor-bills.pay')
@if($vendorBill->status !== 'Paid')
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('vendor-bills.pay', $vendorBill) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment — {{ $vendorBill->bill_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pay From Account <span class="text-danger">*</span></label>
                        <select name="account_id" class="form-select" required>
                            <option value="">— Select Account —</option>
                            @foreach(\App\Models\Account::where('status', true)->whereIn('account_type', ['Cash', 'Bank'])->orderBy('account_name')->get() as $acct)
                            <option value="{{ $acct->id }}">{{ $acct->account_name }} ({{ $acct->account_type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" value="{{ $vendorBill->total_amount }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-brand" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="feather-check me-1"></i> Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan
@endsection
