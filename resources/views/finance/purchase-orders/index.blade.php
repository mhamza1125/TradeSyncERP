@extends('index')

@section('title', 'Purchase Orders - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Purchase Orders</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Purchase Orders</li>
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
                    @can('purchase-orders.index')
                    <a href="{{ route('purchase-orders.export-list-pdf', request()->query()) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i><span>Export PDF</span>
                    </a>
                    @endcan
                    @can('purchase-orders.create')
                    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Add Purchase Order</span>
                    </a>
                    @endcan
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <div id="collapseFilters" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('purchase-orders.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search PO number..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\PurchaseOrder::STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('purchase-orders.index') }}" class="btn btn-light-brand w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="purchaseOrderList">
                                <thead>
                                    <tr>
                                        <th>PO Number</th>
                                        <th>Date</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Due</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $statusColors = ['Unpaid'=>'danger','Partially Paid'=>'warning','Paid'=>'success']; @endphp
                                    @forelse($purchaseOrders as $po)
                                    <tr class="single-item">
                                        <td class="fw-semibold text-dark">{{ $po->po_number }}</td>
                                        <td>{{ $po->po_date->format('d M Y') }}</td>
                                        <td class="text-end">{{ number_format($po->total_amount, 2) }}</td>
                                        <td class="text-end text-success">{{ number_format($po->amount_paid, 2) }}</td>
                                        <td class="text-end {{ $po->amount_due > 0 ? 'text-danger' : '' }}">{{ number_format($po->amount_due, 2) }}</td>
                                        <td>
                                            <span class="badge bg-soft-{{ $statusColors[$po->status] ?? 'secondary' }} text-{{ $statusColors[$po->status] ?? 'secondary' }}">
                                                {{ $po->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('purchase-orders.index')
                                                <a href="{{ route('purchase-orders.show', $po) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('purchase-orders.edit')
                                                        @if($po->status !== 'Paid')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('purchase-orders.edit', $po) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endif
                                                        @endcan
                                                        @can('purchase-orders.delete')
                                                        @if($po->amount_paid <= 0)
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('purchase-orders.destroy', $po) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this purchase order?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Delete</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endif
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="feather-shopping-cart fs-1 d-block mb-2"></i>
                                            No purchase orders found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($purchaseOrders->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $purchaseOrders->firstItem() }}–{{ $purchaseOrders->lastItem() }} of {{ $purchaseOrders->total() }}</small>
                        {{ $purchaseOrders->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
