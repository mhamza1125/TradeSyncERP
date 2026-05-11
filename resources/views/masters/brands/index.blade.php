@extends('index')

@section('title', 'Brands - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Brands</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Brands</li>
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
                    @can('brands.create')
                    <a href="{{ route('masters.brands.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Add Brand</span>
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
            <form method="GET" action="{{ route('masters.brands.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search brand..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="customer_id" class="form-select">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" @selected(request('status') === '1')>Active</option>
                            <option value="0" @selected(request('status') === '0')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('masters.brands.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover" id="brandList">
                                <thead>
                                    <tr>
                                        <th>Brand Name</th>
                                        <th>Customer</th>
                                        <th>Remarks</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($brands as $brand)
                                    <tr class="single-item">
                                        <td class="fw-semibold text-dark">{{ $brand->brand_name }}</td>
                                        <td>{{ optional($brand->customer)->customer_name ?? '—' }}</td>
                                        <td class="text-muted text-truncate-1-line" style="max-width:200px">{{ $brand->remarks ?? '—' }}</td>
                                        <td>
                                            @if($brand->status)
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('brands.edit')
                                                <a href="{{ route('masters.brands.edit', $brand) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="feather feather-edit"></i>
                                                </a>
                                                @endcan
                                                @can('brands.delete')
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form action="{{ route('masters.brands.destroy', $brand) }}" method="POST"
                                                                  onsubmit="return confirm('Deactivate this brand?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Deactivate</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="feather-tag fs-1 d-block mb-2"></i>
                                            No brands found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($brands->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $brands->firstItem() }}–{{ $brands->lastItem() }} of {{ $brands->total() }}</small>
                        {{ $brands->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
