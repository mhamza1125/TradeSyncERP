@extends('index')

@section('title', 'User Management - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">User Management</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Users</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light-brand">
                        <i class="feather-shield me-2"></i><span>Manage Roles</span>
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>New User</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Filters --}}
        <div class="card stretch stretch-full mb-4">
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search name or email…"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select">
                            <option value="">— All Roles —</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" @selected(request('role') == $role->name)>
                                {{ $role->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>
                    @if(request()->hasAny(['search','role']))
                    <div class="col-md-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light w-100">Clear</a>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="card stretch stretch-full">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roles</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td class="ps-3 text-muted">{{ $users->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <span class="fw-semibold">{{ $user->name }}</span>
                                        @if($user->id === auth()->id())
                                        <span class="badge bg-light text-muted" style="font-size:10px;">You</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-muted">{{ $user->email }}</td>
                                <td>
                                    @forelse($user->roles as $role)
                                    <span class="badge bg-primary-light text-primary me-1">{{ $role->name }}</span>
                                    @empty
                                    <span class="text-muted fs-12">No role</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($user->status)
                                    <span class="badge bg-success-light text-success">Active</span>
                                    @else
                                    <span class="badge bg-danger-light text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-12">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="text-end pe-3">
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
                                <td colspan="7" class="text-center py-4 text-muted">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
