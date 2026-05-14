@extends('index')

@section('title', 'Edit User – ' . $user->name . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Edit User</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item">{{ $user->name }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="userForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Update User</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="userForm" action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-xl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header"><h5 class="card-title">Account Details</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Leave blank to keep current password">
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control"
                                           placeholder="Repeat new password">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Permission Summary --}}
                    <div class="card stretch stretch-full mt-4">
                        <div class="card-header"><h5 class="card-title">Effective Permissions</h5></div>
                        <div class="card-body">
                            @php
                                $allPerms = $user->getAllPermissions()->pluck('name')->sort()->groupBy(fn($p) => explode('.', $p)[0]);
                            @endphp
                            @if($allPerms->isEmpty())
                                <p class="text-muted mb-0">No permissions assigned (no roles).</p>
                            @else
                                <div class="row g-2">
                                    @foreach($allPerms as $module => $perms)
                                    <div class="col-md-6">
                                        <div class="border rounded p-2">
                                            <div class="fw-semibold text-capitalize mb-1 fs-12">{{ $module }}</div>
                                            @foreach($perms as $perm)
                                            <span class="badge bg-light text-muted me-1 mb-1" style="font-size:10px;">
                                                {{ explode('.', $perm)[1] ?? $perm }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    {{-- Status --}}
                    <div class="card stretch stretch-full mb-4">
                        <div class="card-header"><h5 class="card-title">Status</h5></div>
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status" value="1"
                                       id="statusSwitch" @checked(old('status', $user->status))>
                                <label class="form-check-label" for="statusSwitch">Account Active</label>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Inactive users cannot log in.
                            </small>
                        </div>
                    </div>

                    {{-- Role Assignment --}}
                    <div class="card stretch stretch-full @error('roles') border-danger @enderror">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                Assign Role <span class="text-danger">*</span>
                            </h5>
                            <small class="text-muted">At least one role is required.</small>
                        </div>
                        <div class="card-body">
                            @error('roles')
                            <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
                            @enderror
                            @foreach($roles as $role)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox"
                                       name="roles[]" value="{{ $role->name }}"
                                       id="role_{{ $role->id }}"
                                       @checked(in_array($role->name, old('roles', $userRoles)))>
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                            @endforeach
                            <small class="text-muted d-block mt-2">
                                Permissions are inherited from the assigned roles.<br>
                                Edit role permissions via <a href="{{ route('admin.roles.index') }}">Manage Roles</a>.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
