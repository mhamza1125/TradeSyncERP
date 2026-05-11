@extends('index')

@section('title', 'Vendor Ledger — {{ $vendor->vendor_name }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Vendor Ledger</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Reports</li>
                <li class="breadcrumb-item">{{ $vendor->vendor_name }}</li>
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
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                        <i class="feather-filter"></i>
                    </a>
                    <button onclick="window.print()" class="btn btn-light-brand">
                        <i class="feather-printer me-2"></i>Print
                    </button>
                    <a href="{{ route('masters.vendors.show', $vendor) }}" class="btn btn-light-brand">
                        <i class="feather-briefcase me-2"></i>Vendor Profile
                    </a>
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <div id="collapseFilters" class="accordion-collapse collapse {{ request()->hasAny(['from_date','to_date']) ? 'show' : '' }} page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('ledger.vendor', $vendor) }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search me-1"></i>Apply</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('ledger.vendor', $vendor) }}" class="btn btn-light-brand w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Vendor Summary --}}
        <div class="row mb-3">
            <div class="col-md-5">
                <div class="card border-0 bg-soft-warning">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-lg bg-warning text-white rounded">
                                {{ strtoupper(substr($vendor->vendor_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold fs-6">{{ $vendor->vendor_name }}</div>
                                @if($vendor->email)
                                <div class="text-muted small"><i class="feather-mail me-1"></i>{{ $vendor->email }}</div>
                                @endif
                                @if($vendor->phone)
                                <div class="text-muted small"><i class="feather-phone me-1"></i>{{ $vendor->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @php
                $totalBilled = $bills->sum('total_amount');
                $paidCount   = $bills->where('status', 'Paid')->count();
                $pendingCount = $bills->whereIn('status', ['Unpaid', 'Partial'])->count();
            @endphp
            <div class="col-md-7">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="card border-0 text-center py-3">
                            <div class="text-muted small mb-1">Total Billed (Page)</div>
                            <div class="fw-bold text-danger fs-6">{{ number_format($totalBilled, 2) }}</div>
                            <div class="text-muted small">PKR</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 text-center py-3">
                            <div class="text-muted small mb-1">Paid Bills</div>
                            <div class="fw-bold text-success fs-6">{{ $paidCount }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 text-center py-3">
                            <div class="text-muted small mb-1">Pending Bills</div>
                            <div class="fw-bold text-warning fs-6">{{ $pendingCount }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="vendorLedger">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:110px">Bill Date</th>
                                        <th>Bill No.</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bills as $bill)
                                    <tr>
                                        <td class="text-nowrap">{{ $bill->bill_date->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('vendor-bills.show', $bill) }}" class="fw-semibold">
                                                {{ $bill->bill_number }}
                                            </a>
                                        </td>
                                        <td class="text-nowrap {{ $bill->due_date && $bill->due_date->isPast() && $bill->status !== 'Paid' ? 'text-danger fw-semibold' : 'text-muted' }}">
                                            {{ $bill->due_date ? $bill->due_date->format('d M Y') : '—' }}
                                        </td>
                                        <td class="text-end fw-bold">{{ number_format($bill->total_amount, 2) }}</td>
                                        <td>
                                            @php
                                                $badge = match($bill->status) {
                                                    'Paid'    => 'bg-soft-success text-success',
                                                    'Partial' => 'bg-soft-warning text-warning',
                                                    default   => 'bg-soft-danger text-danger',
                                                };
                                            @endphp
                                            <span class="badge {{ $badge }}">{{ $bill->status }}</span>
                                        </td>
                                        <td class="text-muted small">{{ $bill->remarks ?? '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="feather-inbox fs-1 d-block mb-2"></i>
                                            No bills found for the selected period.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($bills->count() > 0)
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="3" class="text-end">Page Total:</td>
                                        <td class="text-end text-danger">{{ number_format($totalBilled, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                    @if($bills->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $bills->firstItem() }}–{{ $bills->lastItem() }} of {{ $bills->total() }} bills</small>
                        {{ $bills->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
