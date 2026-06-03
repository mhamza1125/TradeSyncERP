@extends('index')

@section('title', 'Currencies - TradeSyncERP')

@section('content')
<div class="nxl-content apps-container">
    <div class="nxl-content without-header nxl-full-content">
        <div class="main-content d-flex">
        <div class="content-area" data-scrollbar-target="#psScrollbarInit">
            <div class="content-area-header bg-white sticky-top">
                <div class="page-header-left d-flex align-items-center">
                    <a href="javascript:void(0);" class="app-sidebar-open-trigger me-2">
                        <i class="feather-align-left fs-24"></i>
                    </a>
                    <div class="page-header-title"><h5 class="m-b-10 mb-0">Currencies</h5></div>
                    <ul class="breadcrumb ms-3 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Currencies</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="d-flex align-items-center gap-2">
                        <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                            <i class="feather-filter"></i>
                        </a>
                        @can('currencies.create')
                        <a href="{{ route('masters.currencies.create') }}" class="btn btn-primary">
                            <i class="feather-plus me-2"></i><span>Add Currency</span>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <div id="collapseFilters" class="accordion-collapse collapse">
                <div class="accordion-body pb-2 px-3 pt-3 bg-white border-bottom">
                    <form method="GET" action="{{ route('masters.currencies.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search currency..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('masters.currencies.index') }}" class="btn btn-light-brand w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="content-area-body">
                @include('partials.flash-messages')

                <div class="card stretch stretch-full mb-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover" id="currencyList">
                                <thead>
                                    <tr>
                                        <th>Currency</th>
                                        <th>Code</th>
                                        <th>Symbol</th>
                                        <th>Exchange Rate</th>
                                        <th>Default</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($currencies as $currency)
                                    <tr class="single-item">
                                        <td class="fw-semibold text-dark">{{ $currency->currency_name }}</td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary">{{ $currency->currency_code }}</span>
                                        </td>
                                        <td>{{ $currency->symbol }}</td>
                                        <td>{{ number_format($currency->exchange_rate, 4) }}</td>
                                        <td>
                                            @if($currency->is_default)
                                                <span class="badge bg-soft-success text-success">Default</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($currency->status)
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('currencies.index')
                                                <a href="{{ route('masters.currencies.show', $currency) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('currencies.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('masters.currencies.edit', $currency) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('currencies.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('masters.currencies.destroy', $currency) }}" method="POST"
                                                                    onsubmit="return confirm('Deactivate this currency?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Deactivate</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endcan
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="feather-dollar-sign fs-1 d-block mb-2"></i>
                                            No currencies found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($currencies->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $currencies->firstItem() }}–{{ $currencies->lastItem() }} of {{ $currencies->total() }}</small>
                        {{ $currencies->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
