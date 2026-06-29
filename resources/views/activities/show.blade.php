@extends('index')

@section('title', 'Activity Detail - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Activity Detail</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('activities.index') }}">Activities</a></li>
                <li class="breadcrumb-item">Detail</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('activities.index') }}" class="btn btn-light-brand">
                <i class="feather-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="main-content">
        @php
            $eventColors = ['created' => 'success', 'updated' => 'primary', 'deleted' => 'danger'];
            $ec          = $eventColors[$activity->description] ?? 'secondary';
            $modelLabel  = \App\Http\Controllers\ActivityController::modelLabel($activity->subject_type ?? '');
            $identifier  = \App\Http\Controllers\ActivityController::subjectIdentifier($activity);
        @endphp

        <div class="row">
            {{-- Activity Metadata --}}
            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Activity Info</h5></div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted fs-12">Event</span>
                                <span class="badge bg-soft-{{ $ec }} text-{{ $ec }} text-capitalize">
                                    {{ $activity->description }}
                                </span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                <span class="text-muted fs-12">Subject</span>
                                <div class="text-end">
                                    <div class="fw-semibold">{{ $modelLabel }}</div>
                                    <small class="text-primary">{{ $identifier }}</small>
                                </div>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted fs-12">By</span>
                                <div class="d-flex align-items-center gap-2">
                                    @if($activity->causer)
                                    <div class="avatar-text avatar-xs bg-soft-primary text-primary rounded-circle fw-bold">
                                        {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $activity->causer->name }}</span>
                                    @else
                                    <span class="text-muted">System</span>
                                    @endif
                                </div>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                <span class="text-muted fs-12">Date &amp; Time</span>
                                <div class="text-end">
                                    <div class="fw-semibold">{{ $activity->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $activity->created_at->format('H:i:s') }}</small>
                                </div>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted fs-12">Relative</span>
                                <span class="text-muted">{{ $activity->created_at->diffForHumans() }}</span>
                            </li>
                            @if($activity->properties->get('ip'))
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted fs-12">IP Address</span>
                                <span>{{ $activity->properties->get('ip') }}</span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Changes --}}
            <div class="col-xl-8">
                @if($activity->description === 'updated' && !empty($old) && !empty($attributes))
                    {{-- Side-by-side comparison for updates --}}
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Changes</h5></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:30%">Field</th>
                                            <th style="width:35%">Before</th>
                                            <th style="width:35%">After</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attributes as $key => $newValue)
                                        @php
                                            $oldValue  = $old[$key] ?? null;
                                            $fieldLabel = \App\Http\Controllers\ActivityController::columnLabel($key);
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold fs-13 text-muted">{{ $fieldLabel }}</td>
                                            <td class="fs-12">
                                                @if($oldValue !== null)
                                                    <span class="text-danger">{{ is_array($oldValue) ? json_encode($oldValue) : $oldValue }}</span>
                                                @else
                                                    <span class="text-muted fst-italic">—</span>
                                                @endif
                                            </td>
                                            <td class="fs-12">
                                                @if($newValue !== null)
                                                    <span class="text-success">{{ is_array($newValue) ? json_encode($newValue) : $newValue }}</span>
                                                @else
                                                    <span class="text-muted fst-italic">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                @elseif(!empty($attributes))
                    {{-- Created / Deleted: show values as a single column --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                {{ $activity->description === 'created' ? 'Created Values' : 'Deleted Values' }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:40%">Field</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attributes as $key => $value)
                                        <tr>
                                            <td class="fw-semibold fs-13 text-muted">
                                                {{ \App\Http\Controllers\ActivityController::columnLabel($key) }}
                                            </td>
                                            <td class="fs-12">{{ is_array($value) ? json_encode($value) : ($value ?? '—') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                @elseif(!empty($old))
                    {{-- Deleted: show old values --}}
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Deleted Values</h5></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:40%">Field</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($old as $key => $value)
                                        <tr>
                                            <td class="fw-semibold fs-13 text-muted">
                                                {{ \App\Http\Controllers\ActivityController::columnLabel($key) }}
                                            </td>
                                            <td class="fs-12">{{ is_array($value) ? json_encode($value) : ($value ?? '—') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                @else
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">
                            <i class="feather-info fs-2 d-block mb-2"></i>
                            No property changes recorded for this activity.
                        </div>
                    </div>
                @endif

                @if($activity->properties->count() > 0)
                {{-- Raw properties JSON for debugging --}}
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Raw Properties</h5>
                        <button class="btn btn-sm btn-light-brand" type="button" data-bs-toggle="collapse" data-bs-target="#rawJson">
                            Toggle
                        </button>
                    </div>
                    <div class="collapse" id="rawJson">
                        <div class="card-body p-0">
                            <pre class="mb-0 p-3" style="font-size:11px; background:#f8f9fa; max-height:300px; overflow:auto;">{{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
