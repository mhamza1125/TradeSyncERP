@extends('index')

@section('title', 'Roles & Permissions - TradeSyncERP')

@php
    $systemRoles = ['Admin', 'Lab Manager', 'Accountant', 'Employee'];
@endphp

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Roles & Permissions</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item">Roles</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back to Users</span>
                    </a>
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>New Role</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            @foreach($roles as $role)
            @php $isSystem = in_array($role->name, $systemRoles); @endphp
            <div class="col-xl-6 mb-4">
                <div class="card stretch stretch-full h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="card-title mb-0">{{ $role->name }}</h5>
                                @if($isSystem)
                                <span class="badge bg-secondary" style="font-size:10px;">System</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $role->permissions_count }} permission(s) &bull; {{ $role->users_count }} user(s)
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-primary">
                                <i class="feather-edit-2 me-1"></i> Edit Permissions
                            </a>
                            @if(!$isSystem)
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                  onsubmit="return confirm('Delete role \'{{ addslashes($role->name) }}\'? Users with this role will lose it.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger" title="Delete role">
                                    <i class="feather-trash-2"></i>
                                </button>
                            </form>
                            @else
                            <button class="btn btn-sm btn-light" disabled title="System roles cannot be deleted">
                                <i class="feather-lock"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $grouped = $role->permissions->sortBy('name')->groupBy(fn($p) => explode('.', $p->name)[0]);
                        @endphp
                        @if($grouped->isEmpty())
                            <p class="text-muted mb-0">No permissions assigned to this role.</p>
                        @else
                        <div class="row g-2">
                            @foreach($grouped as $module => $perms)
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-secondary text-white" style="min-width:110px;text-align:left;">{{ $module }}</span>
                                    @foreach($perms as $perm)
                                    <span class="badge bg-light text-muted" style="font-size:10px;">
                                        {{ explode('.', $perm->name)[1] ?? $perm->name }}
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
            @endforeach
        </div>
    </div>
</div>
@endsection
