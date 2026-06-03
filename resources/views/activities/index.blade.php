@extends('index')

@section('title', 'Recent Activities - TradeSyncERP')

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
                    <div class="page-header-title"><h5 class="m-b-10 mb-0">Recent Activities</h5></div>
                    <ul class="breadcrumb ms-3 mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Activities</li>
                    </ul>
                </div>
                <div class="page-header-right ms-auto">
                    <div class="d-flex align-items-center gap-2">
                        <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                            <i class="feather-filter"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div id="collapseFilters" class="accordion-collapse collapse">
                <div class="accordion-body pb-2 px-3 pt-3 bg-white border-bottom">
                    <form method="GET" action="{{ route('activities.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search action or subject..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('activities.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                                        <th>Action</th>
                                        <th>Subject</th>
                                        <th>By</th>
                                        <th>Changes</th>
                                        <th>When</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activities as $activity)
                                    <tr>
                                        <td>
                                            @php
                                                $actionColors = [
                                                    'created' => 'success',
                                                    'updated' => 'primary',
                                                    'deleted' => 'danger',
                                                ];
                                                $ac = $actionColors[$activity->description] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-soft-{{ $ac }} text-{{ $ac }} text-capitalize">
                                                {{ $activity->description }}
                                            </span>
                                        </td>
                                        <td class="text-dark fw-semibold fs-13">
                                            {{ class_basename($activity->subject_type ?? '') }}
                                            @if($activity->subject_id)
                                                <span class="text-muted fw-normal">#{{ $activity->subject_id }}</span>
                                            @endif
                                        </td>
                                        <td class="text-muted fs-12">
                                            <div class="d-flex align-items-center gap-2">
                                                @if($activity->causer)
                                                <div class="avatar-text avatar-xs bg-soft-primary text-primary rounded-circle fw-bold">
                                                    {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                                                </div>
                                                @endif
                                                {{ $activity->causer?->name ?? 'System' }}
                                            </div>
                                        </td>
                                        <td class="fs-12 text-muted" style="max-width:280px;">
                                            @if($activity->properties && $activity->properties->count())
                                                @php
                                                    $changed = $activity->properties->get('attributes', []);
                                                    $keys    = array_keys($changed ?? []);
                                                @endphp
                                                @if(count($keys))
                                                    <span class="text-dark">{{ implode(', ', array_slice($keys, 0, 4)) }}</span>
                                                    @if(count($keys) > 4)
                                                        <span class="text-muted"> +{{ count($keys) - 4 }} more</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-muted fs-12" style="white-space:nowrap;">
                                            <span data-bs-toggle="tooltip" title="{{ $activity->created_at->format('d M Y H:i') }}">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="feather-activity fs-1 d-block mb-2"></i>
                                            No activity records found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($activities->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $activities->firstItem() }}–{{ $activities->lastItem() }} of {{ $activities->total() }} records</small>
                        {{ $activities->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
