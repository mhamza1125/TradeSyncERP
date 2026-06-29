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
                                       placeholder="Search subject or changes..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="event" class="form-select">
                                    <option value="">All Events</option>
                                    <option value="created"  @selected(request('event') === 'created')>Created</option>
                                    <option value="updated"  @selected(request('event') === 'updated')>Updated</option>
                                    <option value="deleted"  @selected(request('event') === 'deleted')>Deleted</option>
                                </select>
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
                                        <th style="width:90px">Event</th>
                                        <th>Subject</th>
                                        <th style="width:280px">Changes</th>
                                        <th style="width:160px">By</th>
                                        <th style="width:120px">When</th>
                                        <th style="width:50px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activities as $activity)
                                    @php
                                        $eventColors = [
                                            'created' => 'success',
                                            'updated' => 'primary',
                                            'deleted' => 'danger',
                                        ];
                                        $ec         = $eventColors[$activity->description] ?? 'secondary';
                                        $modelLabel = \App\Http\Controllers\ActivityController::modelLabel($activity->subject_type ?? '');
                                        $identifier = \App\Http\Controllers\ActivityController::subjectIdentifier($activity);
                                        $summary    = \App\Http\Controllers\ActivityController::changeSummary($activity);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-soft-{{ $ec }} text-{{ $ec }} text-capitalize">
                                                {{ $activity->description }}
                                            </span>
                                        </td>
                                        <td class="text-dark fw-semibold fs-13">
                                            {{ $modelLabel }}
                                            <span class="text-primary">{{ $identifier }}</span>
                                        </td>
                                        <td class="fs-12 text-muted">
                                            @if($summary)
                                                {{ $summary }}
                                            @else
                                                <span class="text-muted">—</span>
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
                                        <td class="text-muted fs-12" style="white-space:nowrap;">
                                            <span data-bs-toggle="tooltip" title="{{ $activity->created_at->format('d M Y H:i') }}">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('activities.show', $activity) }}" class="avatar-text avatar-sm" data-bs-toggle="tooltip" title="View Details">
                                                <i class="feather feather-eye fs-14"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
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
