@extends('index')

@section('title', 'New Role - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">New Role</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item">New</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="roleForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Create Role</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="roleForm" action="{{ route('admin.roles.store') }}" method="POST">
            @csrf

            {{-- Role name --}}
            <div class="card stretch stretch-full mb-4">
                <div class="card-header"><h5 class="card-title">Role Details</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. Quality Inspector"
                                   value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Must be unique. Cannot match a system role name.</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Permissions grid --}}
            <div class="alert alert-light border mb-4">
                Assign permissions for this role now, or leave all unchecked and edit later.
            </div>

            <div class="row">
                @foreach($permissions as $module => $modulePerms)
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card stretch stretch-full h-100">
                        <div class="card-header d-flex justify-content-between align-items-center py-2">
                            <h6 class="card-title mb-0 text-capitalize">{{ $module }}</h6>
                            <button type="button" class="btn btn-xs btn-light toggle-module"
                                    data-module="{{ $module }}" style="font-size:11px;padding:2px 8px;">
                                Toggle All
                            </button>
                        </div>
                        <div class="card-body py-2">
                            @foreach($modulePerms as $perm)
                            <div class="form-check mb-1">
                                <input class="form-check-input perm-check perm-module-{{ $module }}"
                                       type="checkbox"
                                       name="permissions[]"
                                       value="{{ $perm->name }}"
                                       id="perm_{{ $perm->id }}"
                                       @checked(in_array($perm->name, $rolePermissions))>
                                <label class="form-check-label fs-12" for="perm_{{ $perm->id }}">
                                    {{ explode('.', $perm->name)[1] ?? $perm->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.toggle-module').forEach(btn => {
        btn.addEventListener('click', function () {
            const module = this.dataset.module;
            const checkboxes = document.querySelectorAll('.perm-module-' + module);
            const allChecked = [...checkboxes].every(c => c.checked);
            checkboxes.forEach(c => c.checked = !allChecked);
        });
    });
</script>
@endpush
