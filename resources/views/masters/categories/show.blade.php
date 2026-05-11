@extends('index')

@section('title', 'Category: {{ $category->category_name }} - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Product Categories</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.categories.index') }}">Categories</a></li>
                <li class="breadcrumb-item">{{ $category->category_name }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('masters.categories.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    @can('categories.edit')
                    <a href="{{ route('masters.categories.edit', $category) }}" class="btn btn-light-brand">
                        <i class="feather-edit me-2"></i>Edit
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-xl-5">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Category Details</h5>
                        @if($category->status)
                            <span class="badge bg-soft-success text-success">Active</span>
                        @else
                            <span class="badge bg-soft-danger text-danger">Inactive</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Category Name</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ $category->category_name }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Testing Parameters</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Parameter Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($category->testingParameters as $param)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $param->parameter_name }}</td>
                                        <td class="text-muted">{{ $param->description ?? '—' }}</td>
                                        <td>
                                            @if($param->status)
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No parameters yet.</td>
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
