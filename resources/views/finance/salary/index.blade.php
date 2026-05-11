@extends('index')

@section('title', 'Salary Runs - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Salary Runs</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Salary Runs</li>
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
                    @can('salary.create')
                    <a href="{{ route('salary.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Generate Salary Run</span>
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

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="salaryList">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Account</th>
                                        <th>Total Net Payable</th>
                                        <th>Status</th>
                                        <th>Payment Date</th>
                                        <th>Processed By</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($runs as $run)
                                    @php
                                        $statusColors = ['Draft'=>'warning','Paid'=>'success'];
                                        $statusColor = $statusColors[$run->status] ?? 'secondary';
                                    @endphp
                                    <tr class="single-item">
                                        <td class="fw-semibold text-dark">{{ $run->month }}</td>
                                        <td>{{ optional($run->account)->account_name ?? '—' }}</td>
                                        <td class="fw-bold text-dark">{{ number_format($run->total_net_payable, 2) }}</td>
                                        <td>
                                            <span class="badge bg-soft-{{ $statusColor }} text-{{ $statusColor }}">
                                                {{ $run->status }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $run->payment_date ? $run->payment_date->format('d M Y') : '—' }}
                                        </td>
                                        <td>{{ optional($run->processedBy)->name ?? '—' }}</td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('salary.index')
                                                <a href="{{ route('salary.show', $run) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="feather-users fs-1 d-block mb-2"></i>
                                            No salary runs found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($runs->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $runs->firstItem() }}–{{ $runs->lastItem() }} of {{ $runs->total() }}</small>
                        {{ $runs->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
