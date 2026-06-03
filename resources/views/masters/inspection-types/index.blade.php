@extends('index')

@section('title', 'Inspection Types - TradeSyncERP')

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
                    <div class="page-header-title"><h5 class="m-b-10 mb-0">Inspection Types</h5></div>
                    <ul class="breadcrumb ms-3 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Inspection Types</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="d-flex align-items-center gap-2">
                        <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                            <i class="feather-filter"></i>
                        </a>
                        @can('inspection-types.create')
                        <a href="{{ route('masters.inspection-types.create') }}" class="btn btn-primary">
                            <i class="feather-plus me-2"></i><span>Add Type</span>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <div id="collapseFilters" class="accordion-collapse collapse">
                <div class="accordion-body pb-2 px-3 pt-3 bg-white border-bottom">
                    <form method="GET" action="{{ route('masters.inspection-types.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
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
                                <a href="{{ route('masters.inspection-types.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Runs</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inspectionTypes as $type)
                                    <tr class="single-item">
                                        <td class="fw-semibold text-dark">{{ $type->name }}</td>
                                        <td class="text-muted text-truncate-1-line" style="max-width:250px">
                                            {{ $type->description ?? '—' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary">{{ $type->runs_count }} runs</span>
                                        </td>
                                        <td>
                                            @if($type->status)
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('inspection-types.index')
                                                <a href="{{ route('masters.inspection-types.show', $type) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('inspection-types.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('masters.inspection-types.edit', $type) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('inspection-types.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('masters.inspection-types.destroy', $type) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this inspection type?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Delete</span>
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
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="feather-clipboard fs-1 d-block mb-2"></i>
                                            No inspection types found.
                                            @can('inspection-types.create')
                                            <a href="{{ route('masters.inspection-types.create') }}">Add one now.</a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($inspectionTypes->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $inspectionTypes->firstItem() }}–{{ $inspectionTypes->lastItem() }} of {{ $inspectionTypes->total() }}</small>
                        {{ $inspectionTypes->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
