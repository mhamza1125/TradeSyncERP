@extends('index')

@section('title', 'User Management - TradeSyncERP')

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
                    <div class="page-header-title"><h5 class="m-b-10 mb-0">User Management</h5></div>
                    <ul class="breadcrumb ms-3 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Users</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="d-flex align-items-center gap-2">
                        <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                            <i class="feather-filter"></i>
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-light-brand">
                            <i class="feather-shield me-2"></i><span>Manage Roles</span>
                        </a>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                            <i class="feather-plus me-2"></i><span>New User</span>
                        </a>
                    </div>
                </div>
            </div>

            <div id="collapseFilters" class="accordion-collapse collapse">
                <div class="accordion-body pb-2 px-3 pt-3 bg-white border-bottom">
                    <form method="GET" action="{{ route('admin.users.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search name or email..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->name }}" @selected(request('role') == $role->name)>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                    <tr class="single-item">
                                        <td class="text-muted">{{ $users->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-text avatar-sm bg-soft-primary text-primary rounded-circle fw-bold">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <span class="fw-semibold text-dark">{{ $user->name }}</span>
                                                @if($user->id === auth()->id())
                                                <span class="badge bg-soft-secondary text-secondary fs-10">You</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-muted">{{ $user->email }}</td>
                                        <td>
                                            @forelse($user->roles as $role)
                                            <span class="badge bg-soft-primary text-primary me-1">{{ $role->name }}</span>
                                            @empty
                                            <span class="text-muted fs-12">No role</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if($user->status)
                                            <span class="badge bg-soft-success text-success">Active</span>
                                            @else
                                            <span class="badge bg-soft-danger text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted fs-12">{{ $user->created_at->format('d M Y') }}</td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @if($user->id !== auth()->id())
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                                  onsubmit="return confirm('Delete user {{ addslashes($user->name) }}?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i><span>Delete</span>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="feather-users fs-1 d-block mb-2"></i>
                                            No users found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($users->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</small>
                        {{ $users->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
