@extends('index')

@section('title', 'Inspections - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Inspections</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Inspections</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            @can('inspections.create')
            <a href="{{ route('inspections.create') }}" class="btn btn-primary">
                <i class="feather-clipboard me-2"></i><span>New Inspection</span>
            </a>
            @endcan
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Filters --}}
        <form method="GET" class="card mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-3">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Report number…" value="{{ request('search') }}">
                    </div>
                    <div class="col-lg-2">
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-lg-2">
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-lg-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            @foreach(['Pending','Pass','Fail'] as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-auto">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <a href="{{ route('inspections.index') }}" class="btn btn-sm btn-light ms-1">Reset</a>
                    </div>
                </div>
            </div>
        </form>

        <div class="card stretch stretch-full">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Report #</th>
                                <th>Date</th>
                                <th>Samples</th>
                                <th>Orders</th>
                                <th>Inspectors</th>
                                <th>Runs</th>
                                <th>Overall</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inspections as $ins)
                            @php $ic = ['Pass'=>'success','Fail'=>'danger','Pending'=>'warning']; @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('inspections.show', $ins) }}" class="fw-bold text-primary">
                                        {{ $ins->report_number }}
                                    </a>
                                </td>
                                <td>{{ $ins->inspection_date->format('d M Y') }}</td>
                                <td>
                                    @foreach($ins->samples->take(2) as $s)
                                    <span class="badge bg-soft-secondary text-secondary me-1">{{ $s->sample_code }}</span>
                                    @endforeach
                                    @if($ins->samples->count() > 2)
                                    <span class="text-muted fs-12">+{{ $ins->samples->count() - 2 }}</span>
                                    @endif
                                </td>
                                <td>{{ $ins->customerOrders->count() ?: '—' }}</td>
                                <td>
                                    @foreach($ins->inspectors->take(2) as $e)
                                    <span class="badge bg-soft-info text-info me-1">{{ $e->employee_name }}</span>
                                    @endforeach
                                    @if($ins->inspectors->count() > 2)
                                    <span class="text-muted fs-12">+{{ $ins->inspectors->count() - 2 }}</span>
                                    @endif
                                </td>
                                <td>{{ $ins->runs->count() }}</td>
                                <td>
                                    <span class="badge bg-soft-{{ $ic[$ins->overall_status] ?? 'secondary' }} text-{{ $ic[$ins->overall_status] ?? 'secondary' }}">
                                        {{ $ins->overall_status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="hstack gap-2 justify-content-end">
                                        <a href="{{ route('inspections.show', $ins) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                            <i class="feather feather-eye"></i>
                                        </a>
                                        <div class="dropdown">
                                            <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                <i class="feather feather-more-horizontal"></i>
                                            </a>
                                            <ul class="dropdown-menu">
                                                @can('inspections.edit')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('inspections.edit', $ins) }}">
                                                        <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                    </a>
                                                </li>
                                                @endcan
                                                @can('inspections.delete')
                                                <li class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('inspections.destroy', $ins) }}" method="POST"
                                                          onsubmit="return confirm('Delete inspection {{ $ins->report_number }}?')">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="feather feather-trash-2 me-3"></i><span>Delete</span>
                                                        </button>
                                                    </form>
                                                </li>
                                                @endcan
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="feather-clipboard fs-1 d-block mb-2"></i>
                                    No inspections found.
                                    @can('inspections.create')
                                    <div class="mt-2">
                                        <a href="{{ route('inspections.create') }}" class="btn btn-sm btn-primary">
                                            <i class="feather-plus me-1"></i> Create First Inspection
                                        </a>
                                    </div>
                                    @endcan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($inspections->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing {{ $inspections->firstItem() }}–{{ $inspections->lastItem() }} of {{ $inspections->total() }}</small>
                {{ $inspections->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
