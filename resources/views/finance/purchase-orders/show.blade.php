@extends('index')

@section('title', $purchaseOrder->po_number . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">{{ $purchaseOrder->po_number }}</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}">Purchase Orders</a></li>
                <li class="breadcrumb-item">{{ $purchaseOrder->po_number }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i> Back
                    </a>
                    <a href="{{ route('purchase-orders.export-pdf', $purchaseOrder) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i> Export PDF
                    </a>
                    @can('purchase-orders.edit')
                    @if($purchaseOrder->status !== 'Paid')
                    <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn btn-light-brand">
                        <i class="feather-edit-2 me-2"></i> Edit
                    </a>
                    @endif
                    @endcan
                    @can('purchase-orders.pay')
                    @if($purchaseOrder->status !== 'Paid')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#payPoModal">
                        <i class="feather-credit-card me-2"></i> Record Payment
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        @php
            $statusColors = ['Unpaid'=>'danger','Partially Paid'=>'warning','Paid'=>'success'];
            $sc = $statusColors[$purchaseOrder->status] ?? 'secondary';
        @endphp

        <div class="row">
            <div class="col-xl-8">
                {{-- Order Information --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Purchase Order Information</h5>
                        <span class="badge bg-soft-{{ $sc }} text-{{ $sc }}">{{ $purchaseOrder->status }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted fs-12">PO Number</div>
                                <div class="fw-semibold">{{ $purchaseOrder->po_number }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-12">PO Date</div>
                                <div class="fw-semibold">{{ $purchaseOrder->po_date->format('d M Y') }}</div>
                            </div>
                            @if($purchaseOrder->remarks)
                            <div class="col-12">
                                <div class="text-muted fs-12">Remarks</div>
                                <div>{{ $purchaseOrder->remarks }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Items ({{ $purchaseOrder->items->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($item->total_amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Grand Total</td>
                                        <td class="text-end fw-bold">{{ number_format($purchaseOrder->total_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end text-success">Amount Paid</td>
                                        <td class="text-end text-success">{{ number_format($purchaseOrder->amount_paid, 2) }}</td>
                                    </tr>
                                    <tr class="{{ $purchaseOrder->amount_due > 0 ? 'text-danger' : 'text-success' }}">
                                        <td colspan="4" class="text-end fw-bold">Amount Due</td>
                                        <td class="text-end fw-bold">{{ number_format($purchaseOrder->amount_due, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Payment History --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center gap-2">
                        <h5 class="card-title mb-0">Payment History</h5>
                        <span class="badge bg-soft-secondary text-secondary ms-auto">{{ $purchaseOrder->expenses->count() }} payment(s)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Account</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchaseOrder->expenses as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->expense_date)->format('d M Y') }}</td>
                                        <td>{{ $payment->account?->account_name ?? '—' }}</td>
                                        <td class="text-muted">{{ $payment->description ?? '—' }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($payment->amount, 2) }}</td>
                                        <td class="text-end">
                                            @can('expenses.index')
                                            <a href="{{ route('expenses.show', $payment) }}" class="avatar-text avatar-sm" data-bs-toggle="tooltip" title="View Expense">
                                                <i class="feather feather-eye"></i>
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No payments recorded yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                {{-- Payment Summary --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Payment Summary</h5></div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="text-muted fs-12">Amount Due</div>
                            <div class="fs-28 fw-bolder {{ $purchaseOrder->amount_due > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($purchaseOrder->amount_due, 2) }}
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted fs-12">Total</span>
                                <strong>{{ number_format($purchaseOrder->total_amount, 2) }}</strong>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted fs-12">Paid</span>
                                <strong class="text-success">{{ number_format($purchaseOrder->amount_paid, 2) }}</strong>
                            </li>
                        </ul>
                        @can('purchase-orders.pay')
                        @if($purchaseOrder->status !== 'Paid')
                        <button type="button" class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#payPoModal">
                            <i class="feather-credit-card me-2"></i> Record Payment
                        </button>
                        @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- Attachment Panel --}}
        @include('partials.attachment-panel', [
            'attachEntity'     => $purchaseOrder,
            'attachEntityType' => 'purchase-orders',
            'attachLabel'      => 'Purchase Order Attachments',
        ])
    </div>
</div>
@endsection

{{-- Record Payment Modal --}}
@can('purchase-orders.pay')
@if($purchaseOrder->status !== 'Paid')
@push('modals')
<div class="modal fade" id="payPoModal" tabindex="-1" role="dialog" aria-labelledby="payPoModalLabel" aria-modal="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('purchase-orders.pay', $purchaseOrder) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="payPoModalLabel">Record Payment — {{ $purchaseOrder->po_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" max="{{ $purchaseOrder->amount_due }}" name="amount"
                               class="form-control" value="{{ $purchaseOrder->amount_due }}" required>
                        <div class="form-text">Remaining balance: {{ number_format($purchaseOrder->amount_due, 2) }}. Partial payments are allowed.</div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control" placeholder="Optional notes about this payment..."></textarea>
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
@endpush
@endif
@endcan
