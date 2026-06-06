@extends('index')

@section('title', $inspectionType->name . ' — TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">{{ $inspectionType->name }}</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('masters.inspection-types.index') }}">Inspection Types</a></li>
                <li class="breadcrumb-item">{{ $inspectionType->name }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('masters.inspection-types.index') }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
                @can('inspection-types.edit')
                <a href="{{ route('masters.inspection-types.sections', $inspectionType) }}" class="btn btn-light-brand">
                    <i class="feather-layers me-2"></i>Manage Sections
                </a>
                <a href="{{ route('masters.inspection-types.edit', $inspectionType) }}" class="btn btn-primary">
                    <i class="feather-edit me-2"></i>Edit
                </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">

            {{-- ── Left: Details + Sections ──────────────────────────────────── --}}
            <div class="col-xl-8">

                {{-- Details card --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Details</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Name</div>
                                <div class="fw-semibold">{{ $inspectionType->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted fs-12">Status</div>
                                @if($inspectionType->status)
                                    <span class="badge bg-soft-success text-success">Active</span>
                                @else
                                    <span class="badge bg-soft-danger text-danger">Inactive</span>
                                @endif
                            </div>
                            @if($inspectionType->description)
                            <div class="col-12">
                                <div class="text-muted fs-12">Description</div>
                                <div>{{ $inspectionType->description }}</div>
                            </div>
                            @endif
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Total Inspections</div>
                                <div class="fw-semibold">{{ $inspectionType->inspections->count() }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted fs-12">Total Sections Assigned</div>
                                <div class="fw-semibold">{{ $inspectionType->sectionDefaults->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sections assignment card --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Assigned Sections</h5>
                        @can('inspection-types.edit')
                        <a href="{{ route('masters.inspection-types.sections', $inspectionType) }}" class="btn btn-sm btn-primary">
                            <i class="feather-edit-3 me-1"></i>Manage
                        </a>
                        @endcan
                    </div>

                    @if($inspectionType->sectionDefaults->isEmpty())
                    <div class="card-body text-center py-5 text-muted">
                        <i class="feather-layers" style="font-size:2rem;opacity:.3"></i>
                        <p class="mt-2 mb-1">No sections configured for this inspection type.</p>
                        @can('inspection-types.edit')
                        <a href="{{ route('masters.inspection-types.sections', $inspectionType) }}" class="btn btn-sm btn-primary mt-2">
                            <i class="feather-plus me-1"></i>Configure Sections
                        </a>
                        @endcan
                    </div>
                    @else

                    @php
                        $globalSections   = $inspectionType->sectionDefaults->whereNull('category_id');
                        $specificSections = $inspectionType->sectionDefaults->whereNotNull('category_id')->groupBy('category_id');
                    @endphp

                    <div class="card-body">

                        @if($globalSections->count())
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-soft-success text-success fs-12">
                                    <i class="feather-globe me-1"></i>Global — All Categories
                                </span>
                                <small class="text-muted">{{ $globalSections->count() }} section(s)</small>
                            </div>
                            <div class="row g-2">
                                @foreach($globalSections->sortBy('sort_order') as $def)
                                @php
                                    $typeColors = ['images'=>'purple','workmanship'=>'primary','aql'=>'success','checklist'=>'info','container'=>'warning','verification'=>'warning','review'=>'secondary'];
                                    $color = $typeColors[$def->section?->section_type] ?? 'secondary';
                                @endphp
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 p-2 border rounded">
                                        <i class="{{ $def->section?->icon ?? 'feather-layers' }} text-{{ $color }}" style="font-size:14px;flex-shrink:0"></i>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fs-12 fw-semibold">{{ $def->section?->name ?? '—' }}</div>
                                            <span class="badge bg-soft-{{ $color }} text-{{ $color }} fs-10">{{ $def->section?->section_type }}</span>
                                        </div>
                                        @if($def->is_required)
                                        <span class="badge bg-soft-danger text-danger fs-10">Required</span>
                                        @endif
                                        <span class="text-muted fs-11">#{{ $def->sort_order }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @foreach($specificSections as $catId => $defs)
                        @php $catName = $defs->first()->category?->category_name ?? 'Unknown Category'; @endphp
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-soft-primary text-primary fs-12">
                                    <i class="feather-tag me-1"></i>{{ $catName }}
                                </span>
                                <small class="text-muted">{{ $defs->count() }} section(s)</small>
                            </div>
                            <div class="row g-2">
                                @foreach($defs->sortBy('sort_order') as $def)
                                @php
                                    $typeColors = ['images'=>'purple','workmanship'=>'primary','aql'=>'success','checklist'=>'info','container'=>'warning','verification'=>'warning','review'=>'secondary'];
                                    $color = $typeColors[$def->section?->section_type] ?? 'secondary';
                                @endphp
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 p-2 border rounded">
                                        <i class="{{ $def->section?->icon ?? 'feather-layers' }} text-{{ $color }}" style="font-size:14px;flex-shrink:0"></i>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fs-12 fw-semibold">{{ $def->section?->name ?? '—' }}</div>
                                            <span class="badge bg-soft-{{ $color }} text-{{ $color }} fs-10">{{ $def->section?->section_type }}</span>
                                        </div>
                                        @if($def->is_required)
                                        <span class="badge bg-soft-danger text-danger fs-10">Required</span>
                                        @endif
                                        <span class="text-muted fs-11">#{{ $def->sort_order }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                    </div>
                    @endif
                </div>

            </div>

            {{-- ── Right: Stats ───────────────────────────────────────────────── --}}
            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Summary</h5></div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Total Sections</span>
                                <strong>{{ $inspectionType->sectionDefaults->count() }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Global Sections</span>
                                <strong>{{ $inspectionType->sectionDefaults->whereNull('category_id')->count() }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Category-Specific</span>
                                <strong>{{ $inspectionType->sectionDefaults->whereNotNull('category_id')->count() }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span class="text-muted">Total Inspections</span>
                                <strong>{{ $inspectionType->inspections->count() }}</strong>
                            </li>
                        </ul>
                    </div>
                </div>

                @can('inspection-types.edit')
                <div class="card">
                    <div class="card-body text-center py-4">
                        <i class="feather-layers text-primary" style="font-size:2rem"></i>
                        <p class="mt-2 mb-3 text-muted fs-13">
                            Configure which inspection sections are auto-applied when a run is created for this type.
                        </p>
                        <a href="{{ route('masters.inspection-types.sections', $inspectionType) }}" class="btn btn-primary w-100">
                            <i class="feather-edit-3 me-2"></i>Manage Section Assignments
                        </a>
                    </div>
                </div>
                @endcan
            </div>

        </div>
    </div>
</div>
@endsection
