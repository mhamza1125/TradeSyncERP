@extends('index')

@section('title', 'My Profile - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">My Profile</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Profile</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            {{-- Profile Information --}}
            <div class="col-xl-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Profile Information</h5>
                        <p class="text-muted fs-12 mb-0">Update your account name and email address.</p>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            <div class="mb-4">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input id="name" name="name" type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input id="email" name="email" type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required autocomplete="username">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="alert alert-warning mt-2 py-2 fs-12">
                                        Your email is unverified.
                                        <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm p-0 text-warning">
                                                Re-send verification email
                                            </button>
                                        </form>
                                        @if (session('status') === 'verification-link-sent')
                                            <span class="text-success ms-2">Verification link sent!</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-save me-2"></i>Save Changes
                                </button>
                                @if (session('status') === 'profile-updated')
                                    <span class="text-success fs-12">
                                        <i class="feather-check me-1"></i>Saved.
                                    </span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Update Password --}}
            <div class="col-xl-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Update Password</h5>
                        <p class="text-muted fs-12 mb-0">Use a long, random password to keep your account secure.</p>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')

                            <div class="mb-4">
                                <label for="update_password_current_password" class="form-label">Current Password</label>
                                <input id="update_password_current_password" name="current_password" type="password"
                                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                                       autocomplete="current-password">
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="update_password_password" class="form-label">New Password</label>
                                <input id="update_password_password" name="password" type="password"
                                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                                       autocomplete="new-password">
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="update_password_password_confirmation" class="form-label">Confirm New Password</label>
                                <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                                       class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                                       autocomplete="new-password">
                                @error('password_confirmation', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-lock me-2"></i>Update Password
                                </button>
                                @if (session('status') === 'password-updated')
                                    <span class="text-success fs-12">
                                        <i class="feather-check me-1"></i>Password updated.
                                    </span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Delete Account --}}
            <div class="col-xl-6 mb-4">
                <div class="card stretch stretch-full border border-danger">
                    <div class="card-header">
                        <h5 class="card-title text-danger">Delete Account</h5>
                        <p class="text-muted fs-12 mb-0">
                            Once your account is deleted, all data will be permanently removed.
                            Please download any data you wish to keep before proceeding.
                        </p>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="feather-trash-2 me-2"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Account Modal --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p class="text-muted">
                        Are you sure you want to delete your account? This action is irreversible.
                        Please enter your password to confirm.
                    </p>
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Password</label>
                        <input id="delete_password" name="password" type="password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               placeholder="Enter your password">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="feather-trash-2 me-2"></i>Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
