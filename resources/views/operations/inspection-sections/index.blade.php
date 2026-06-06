@extends('index')

@section('title', 'Inspection Sections - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Inspection Sections</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Inspection Sections</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="javascript:void(0);" class="btn btn-icon btn-light-brand"
                   data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                    <i class="feather-filter"></i>
                </a>
                @can('inspection-sections.create')
                <a href="{{ route('inspection-sections.create') }}" class="btn btn-primary">
                    <i class="feather-plus me-2"></i>New Section
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Filters --}}
        <div id="collapseFilters" class="accordion-collapse collapse mb-3 {{ request()->hasAny(['search','type','status']) ? 'show' : '' }}">
            <div class="card">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('inspection-sections.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fs-12">Search</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="Section name or slug…" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fs-12">Section Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach($sectionTypes as $t)
                                        <option value="{{ $t }}" @selected(request('type') === $t)>
                                            {{ ucfirst($t) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-12">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="1" @selected(request('status') === '1')>Active</option>
                                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-auto d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('inspection-sections.index') }}" class="btn btn-light-brand">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width:50px">#</th>
                                <th>Name / Slug</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-center">Order</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $typeColors = [
                                    'images'       => 'purple',
                                    'workmanship'  => 'primary',
                                    'aql'          => 'success',
                                    'checklist'    => 'info',
                                    'container'    => 'warning',
                                    'verification' => 'warning',
                                    'review'       => 'secondary',
                                ];
                            @endphp
                            @forelse($sections as $i => $section)
                            @php $color = $typeColors[$section->section_type] ?? 'secondary'; @endphp
                            <tr>
                                <td class="ps-4 text-muted">{{ $sections->firstItem() + $i }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="{{ $section->icon ?? 'feather-layers' }} text-{{ $color }}" style="font-size:16px;flex-shrink:0"></i>
                                        <div>
                                            <div class="fw-semibold fs-13">{{ $section->name }}</div>
                                            <code class="fs-11 text-muted">{{ $section->slug }}</code>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-soft-{{ $color }} text-{{ $color }} text-uppercase fs-10">
                                        {{ $section->section_type }}
                                    </span>
                                </td>
                                <td class="text-muted fs-12" style="max-width:280px">
                                    {{ Str::limit($section->description, 80) ?? '—' }}
                                </td>
                                <td class="text-center text-muted">{{ $section->sort_order }}</td>
                                <td class="text-center">
                                    @if($section->is_active)
                                        <span class="badge bg-soft-success text-success">Active</span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-2 justify-content-end">
                                        @can('inspection-sections.edit')
                                        <a href="{{ route('inspection-sections.edit', $section) }}"
                                           class="btn btn-sm btn-light-brand">
                                            <i class="feather-edit-3 me-1"></i>Edit
                                        </a>
                                        @endcan
                                        @can('inspection-sections.delete')
                                        <form action="{{ route('inspection-sections.destroy', $section) }}"
                                              method="POST"
                                              onsubmit="return confirm('Delete section {{ addslashes($section->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light-danger">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="feather-layers fs-1 d-block mb-2 opacity-50"></i>
                                    <p class="mb-1">No inspection sections found.</p>
                                    @can('inspection-sections.create')
                                    <a href="{{ route('inspection-sections.create') }}" class="btn btn-sm btn-primary mt-2">
                                        <i class="feather-plus me-1"></i>Create First Section
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($sections->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Showing {{ $sections->firstItem() }}–{{ $sections->lastItem() }} of {{ $sections->total() }}
                </small>
                {{ $sections->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
