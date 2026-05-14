@extends('index')

@section('title', 'New User - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">New User</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item">New</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="userForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Create User</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="userForm" action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-xl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header"><h5 class="card-title">Account Details</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           placeholder="John Doe" value="{{ old('name') }}" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           placeholder="user@example.com" value="{{ old('email') }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Min. 8 characters" required>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control"
                                           placeholder="Repeat password" required>
                                </div>
                            </div>
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
                                       id="statusSwitch" @checked(old('status', true))>
                                <label class="form-check-label" for="statusSwitch">Account Active</label>
                            </div>
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
                                       @checked(in_array($role->name, old('roles', [])))>
                                <label class="form-check-label" for="role_{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
