@extends('index')

@section('title', 'Dashboard - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Dashboard</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Dashboard</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <span class="text-muted fs-12">{{ now()->format('l, d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">

        {{-- ── Row 1: Key KPI cards ─────────────────────────────────────────── --}}
        <div class="row">

            {{-- Customers --}}
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded">
                                    <i class="feather-user-check"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Active Customers</div>
                                    <div class="fs-12 text-muted">All active accounts</div>
                                </div>
                            </div>
                        </div>
                        <div class="pt-4">
                            <h2 class="fs-4 fw-bold text-dark mb-1">{{ number_format($totalCustomers) }}</h2>
                            @can('customers.index')
                            <a href="{{ route('masters.customers.index') }}" class="fs-12 text-primary">View all →</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- Receivables --}}
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text avatar-lg bg-soft-success text-success border-soft-success rounded">
                                    <i class="feather-dollar-sign"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Outstanding Receivables</div>
                                    <div class="fs-12 text-muted">Total unpaid invoices</div>
                                </div>
                            </div>
                            @if($overdueInvoices > 0)
                            <div class="badge bg-soft-danger text-danger">
                                {{ $overdueInvoices }} overdue
                            </div>
                            @endif
                        </div>
                        <div class="pt-4">
                            <h2 class="fs-4 fw-bold text-dark mb-1">PKR {{ number_format($totalReceivable, 0) }}</h2>
                            <div class="fs-12 text-muted">{{ $invoicesThisMonth }} invoices this month</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Samples --}}
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text avatar-lg bg-soft-warning text-warning border-soft-warning rounded">
                                    <i class="feather-package"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Active Samples</div>
                                    <div class="fs-12 text-muted">In lab / awaiting testing</div>
                                </div>
                            </div>
                            @if($overdueSamples > 0)
                            <div class="badge bg-soft-danger text-danger">
                                {{ $overdueSamples }} overdue
                            </div>
                            @endif
                        </div>
                        <div class="pt-4">
                            <h2 class="fs-4 fw-bold text-dark mb-1">{{ number_format($activeSamples) }}</h2>
                            @can('samples.index')
                            <a href="{{ route('samples.index') }}" class="fs-12 text-primary">View samples →</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- Expenses this month --}}
            <div class="col-xxl-3 col-md-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text avatar-lg bg-soft-warning text-warning border-soft-warning rounded">
                                    <i class="feather-trending-down"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Expenses This Month</div>
                                    <div class="fs-12 text-muted">Total spent</div>
                                </div>
                            </div>
                        </div>
                        <div class="pt-4">
                            <h2 class="fs-4 fw-bold text-dark mb-1">PKR {{ number_format($expensesThisMonth, 0) }}</h2>
                            @can('expenses.index')
                            <a href="{{ route('expenses.index') }}" class="fs-12 text-primary">View expenses →</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Row 2: Finance + Operations summary ─────────────────────────── --}}
        <div class="row mt-2">

            {{-- Finance summary panel --}}
            <div class="col-xl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Finance Overview</h5>
                        <span class="text-muted fs-12">This month</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text rounded bg-soft-success text-success">
                                    <i class="feather-arrow-down-circle"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Payments Received</div>
                                    <div class="fs-12 text-muted">This month (PKR)</div>
                                </div>
                            </div>
                            <div class="fw-bold text-success">{{ number_format($paymentsThisMonth, 0) }}</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text rounded bg-soft-primary text-primary">
                                    <i class="feather-file-text"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Total Invoiced</div>
                                    <div class="fs-12 text-muted">All time (PKR)</div>
                                </div>
                            </div>
                            <div class="fw-bold text-dark">{{ number_format($totalInvoiced, 0) }}</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text rounded bg-soft-warning text-warning">
                                    <i class="feather-clock"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Outstanding</div>
                                    <div class="fs-12 text-muted">Unpaid invoices (PKR)</div>
                                </div>
                            </div>
                            <div class="fw-bold text-warning">{{ number_format($totalReceivable, 0) }}</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text rounded bg-soft-danger text-danger">
                                    <i class="feather-trending-down"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Expenses</div>
                                    <div class="fs-12 text-muted">This month (PKR)</div>
                                </div>
                            </div>
                            <div class="fw-bold text-danger">{{ number_format($expensesThisMonth, 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Operations alerts panel --}}
            <div class="col-xl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Operations Alerts</h5>
                        <span class="text-muted fs-12">Requires attention</span>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text rounded bg-soft-danger text-danger">
                                    <i class="feather-alert-triangle"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Overdue Samples</div>
                                    <div class="fs-12 text-muted">Past alert date</div>
                                </div>
                            </div>
                            <div class="fs-4 fw-bold {{ $overdueSamples > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $overdueSamples }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text rounded bg-soft-warning text-warning">
                                    <i class="feather-search"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Pending Inspections</div>
                                    <div class="fs-12 text-muted">Awaiting results</div>
                                </div>
                            </div>
                            <div class="fs-4 fw-bold {{ $pendingInspections > 0 ? 'text-warning' : 'text-success' }}">
                                {{ $pendingInspections }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text rounded bg-soft-warning text-warning">
                                    <i class="feather-repeat"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Overdue Movements</div>
                                    <div class="fs-12 text-muted">Not returned</div>
                                </div>
                            </div>
                            <div class="fs-4 fw-bold {{ $overdueMovements > 0 ? 'text-warning' : 'text-success' }}">
                                {{ $overdueMovements }}
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-text rounded bg-soft-primary text-primary">
                                    <i class="feather-clipboard"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Open Customer Orders</div>
                                    <div class="fs-12 text-muted">Draft / Confirmed / Processing</div>
                                </div>
                            </div>
                            <div class="fs-4 fw-bold text-primary">{{ $pendingOrders }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sample status breakdown --}}
            <div class="col-xl-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Samples by Status</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $statusMap = [
                                'Received'   => ['color' => 'primary',   'icon' => 'feather-inbox'],
                                'In Testing' => ['color' => 'warning',   'icon' => 'feather-activity'],
                                'Completed'  => ['color' => 'success',   'icon' => 'feather-check-circle'],
                                'Returned'   => ['color' => 'secondary', 'icon' => 'feather-corner-up-left'],
                            ];
                            $totalAllSamples = max($samplesByStatus->sum(), 1);
                        @endphp
                        @foreach($statusMap as $status => $cfg)
                        @php $count = $samplesByStatus[$status] ?? 0; @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fs-12 fw-semibold text-dark">
                                    <i class="{{ $cfg['icon'] }} me-1 text-{{ $cfg['color'] }}"></i>{{ $status }}
                                </span>
                                <span class="fs-12 text-muted">{{ $count }}</span>
                            </div>
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar bg-{{ $cfg['color'] }}"
                                     style="width: {{ round(($count / $totalAllSamples) * 100) }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Row 3: Recent orders + recent activity ───────────────────────── --}}
        <div class="row mt-2">

            {{-- Recent customer orders --}}
            @can('customer-orders.index')
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Customer Orders</h5>
                        <a href="{{ route('customer-orders.index') }}" class="btn btn-sm btn-light-brand">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentOrders as $order)
                                    @php
                                        $sc = ['Draft'=>'secondary','Confirmed'=>'primary','Processing'=>'warning','Dispatched'=>'success','Cancelled'=>'danger'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('customer-orders.show', $order) }}" class="fw-semibold text-primary">
                                                {{ $order->order_code }}
                                            </a>
                                        </td>
                                        <td class="text-muted">{{ $order->customer->customer_name }}</td>
                                        <td class="text-muted fs-12">{{ $order->order_date->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-soft-{{ $sc[$order->status] ?? 'secondary' }} text-{{ $sc[$order->status] ?? 'secondary' }}">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No customer orders yet.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            {{-- Recent Activity --}}
            <div class="{{ auth()->user()->can('customer-orders.index') ? 'col-xl-6' : 'col-xl-12' }}">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Subject</th>
                                        <th>By</th>
                                        <th>When</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentActivity as $activity)
                                    <tr>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary text-capitalize">
                                                {{ $activity->description }}
                                            </span>
                                        </td>
                                        <td class="text-muted fs-12">
                                            {{ class_basename($activity->subject_type ?? '') }}
                                        </td>
                                        <td class="text-muted fs-12">{{ $activity->causer?->name ?? 'System' }}</td>
                                        <td class="text-muted fs-12">{{ $activity->created_at->diffForHumans() }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No recent activity.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/dashboard-init.min.js') }}"></script>
@endpush
