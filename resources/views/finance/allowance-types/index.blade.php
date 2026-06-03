@extends('index')

@section('title', 'Allowance Types - TradeSyncERP')

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
                    <div class="page-header-title"><h5 class="m-b-10 mb-0">Allowance Types</h5></div>
                    <ul class="breadcrumb ms-3 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Allowance Types</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <a href="{{ route('allowance-types.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>Add Type</span>
                    </a>
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
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allowanceTypes as $type)
                                    <tr>
                                        <td class="fw-semibold text-dark">{{ $type->name }}</td>
                                        <td class="text-muted">{{ $type->description ?? '—' }}</td>
                                        <td>
                                            @if($type->is_active)
                                                <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('allowance-types.edit', $type) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="Edit">
                                                    <i class="feather feather-edit-3"></i>
                                                </a>
                                                <form action="{{ route('allowance-types.destroy', $type) }}" method="POST"
                                                      onsubmit="return confirm('Deactivate this allowance type?')">
                                                    @csrf @method('DELETE')
                                                    <button class="avatar-text avatar-md text-danger" type="submit" data-bs-toggle="tooltip" title="Deactivate">
                                                        <i class="feather feather-trash-2"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="feather-gift fs-1 d-block mb-2"></i>
                                            No allowance types found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($allowanceTypes->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $allowanceTypes->firstItem() }}–{{ $allowanceTypes->lastItem() }} of {{ $allowanceTypes->total() }}</small>
                        {{ $allowanceTypes->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
