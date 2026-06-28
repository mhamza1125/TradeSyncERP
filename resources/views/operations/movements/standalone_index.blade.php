@extends('index')

@section('title', 'Sample Movements - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Sample Movements</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Sample Movements</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseFilters">
                        <i class="feather-filter"></i>
                    </a>
                    @can('sample-movements.index')
                    <a href="{{ route('movements.export-list-pdf', request()->query()) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i><span>Export PDF</span>
                    </a>
                    @endcan
                    @can('sample-movements.create')
                    <a href="{{ route('movements.create') }}" class="btn btn-primary">
                        <i class="feather-send me-2"></i><span>Record Movement</span>
                    </a>
                    @endcan
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <div id="collapseFilters" class="accordion-collapse collapse page-header-collapse{{ request('search') || request('status') ? ' show' : '' }}">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('movements.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                               placeholder="Sample code or product name…" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach(['Issued','Returned','Overdue'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('movements.index') }}" class="btn btn-light-brand w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Samples</th>
                                        <th>Issue Date</th>
                                        <th>Assigned Employees</th>
                                        <th>Expected Return</th>
                                        <th>Actual Return</th>
                                        <th>Inspection Run</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movements as $m)
                                    @php $mc = ['Issued'=>'primary','Returned'=>'success','Overdue'=>'danger']; @endphp
                                    <tr>
                                        <td class="text-muted">{{ $movements->firstItem() + $loop->index }}</td>
                                        <td>
                                            @php $itemCount = $m->items->count(); @endphp
                                            <span class="fw-semibold">{{ $itemCount }} line{{ $itemCount !== 1 ? 's' : '' }}</span>
                                            @if($m->items->count())
                                            <div class="text-muted fs-12">
                                                {{ $m->items->take(2)->map(fn($i) => $i->sample?->sample_code ?? '?')->unique()->join(', ') }}
                                                @if($itemCount > 2)<span>+{{ $itemCount - 2 }} more</span>@endif
                                            </div>
                                            @endif
                                        </td>
                                        <td>{{ $m->issue_date->format('d M Y') }}</td>
                                        <td>
                                            @forelse($m->employees->take(2) as $e)
                                            <span class="badge bg-soft-secondary text-secondary fs-11">{{ $e->employee_name }}</span>
                                            @empty
                                            <span class="text-muted fs-12">—</span>
                                            @endforelse
                                            @if($m->employees->count() > 2)
                                            <span class="text-muted fs-11">+{{ $m->employees->count() - 2 }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $m->expected_return_date?->format('d M Y') ?? '—' }}</td>
                                        <td>{{ $m->actual_return_date?->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            @if($m->inspectionRun)
                                            <a href="{{ route('inspections.show', $m->inspectionRun->inspection) }}"
                                               class="text-primary fs-12">
                                                {{ $m->inspectionRun->inspection->report_number ?? 'Run #'.$m->inspection_run_id }}
                                            </a>
                                            @else
                                            <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-{{ $mc[$m->status] ?? 'secondary' }} text-{{ $mc[$m->status] ?? 'secondary' }}">
                                                {{ $m->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('sample-movements.index')
                                                <a href="{{ route('movements.show', $m) }}"
                                                   class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md"
                                                       data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('sample-movements.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('movements.edit', $m) }}">
                                                                <i class="feather feather-edit-3 me-3"></i>Update Return
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('sample-movements.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('movements.destroy', $m) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this movement event?')">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger" type="submit">
                                                                    <i class="feather feather-trash-2 me-3"></i>Delete
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
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="feather-send fs-1 d-block mb-2"></i>
                                            No movements recorded yet.
                                            @can('sample-movements.create')
                                            <div class="mt-2">
                                                <a href="{{ route('movements.create') }}" class="btn btn-sm btn-primary">
                                                    <i class="feather-plus me-1"></i> Record First Movement
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
                    @if($movements->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Showing {{ $movements->firstItem() }}–{{ $movements->lastItem() }} of {{ $movements->total() }}
                        </small>
                        {{ $movements->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
