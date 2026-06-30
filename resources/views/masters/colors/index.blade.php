@extends('index')

@section('title', 'Colors - TradeSyncERP')

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
                    <div class="page-header-title"><h5 class="m-b-10 mb-0">Colors</h5></div>
                    <ul class="breadcrumb ms-3 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Colors</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="d-flex align-items-center gap-2">
                        <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                            <i class="feather-filter"></i>
                        </a>
                        @can('colors.index')
                        <a href="{{ route('masters.colors.export-pdf', request()->query()) }}" class="btn btn-light-brand" target="_blank">
                            <i class="feather-download me-2"></i><span>Export PDF</span>
                        </a>
                        @endcan
                        @can('colors.create')
                        <a href="{{ route('masters.colors.create') }}" class="btn btn-primary">
                            <i class="feather-plus me-2"></i><span>Add Color</span>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <div id="collapseFilters" class="accordion-collapse collapse">
                <div class="accordion-body pb-2 px-3 pt-3 bg-white border-bottom">
                    <form method="GET" action="{{ route('masters.colors.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search color name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('masters.colors.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Color Name</th>
                                        <th>Used in Variations</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($colors as $color)
                                    <tr>
                                        <td class="fw-semibold text-dark">
                                            <span class="d-inline-block me-2 rounded-circle border" style="width:14px;height:14px;background:#eee;"></span>
                                            {{ $color->name }}
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary">
                                                {{ $color->variations_count }} variations
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('colors.edit')
                                                <a href="{{ route('masters.colors.edit', $color) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="feather feather-edit-3"></i>
                                                </a>
                                                @endcan
                                                @can('colors.delete')
                                                <form action="{{ route('masters.colors.destroy', $color) }}" method="POST"
                                                      onsubmit="return confirm('Delete this color?')">
                                                    @csrf @method('DELETE')
                                                    <button class="avatar-text avatar-md text-danger" type="submit" data-bs-toggle="tooltip" title="Delete">
                                                        <i class="feather feather-trash-2"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="feather-droplet fs-1 d-block mb-2"></i>
                                            No colors found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($colors->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $colors->firstItem() }}–{{ $colors->lastItem() }} of {{ $colors->total() }}</small>
                        {{ $colors->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
