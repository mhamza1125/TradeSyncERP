@extends('index')

@section('title', 'Movements – ' . $sample->sample_code . ' - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Sample Movements</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.index') }}">Samples</a></li>
                <li class="breadcrumb-item"><a href="{{ route('samples.show', $sample) }}">{{ $sample->sample_code }}</a></li>
                <li class="breadcrumb-item">Movements</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('samples.show', $sample) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back to Sample</span>
                    </a>
                    @can('sample-movements.create')
                    <a href="{{ route('samples.movements.create', $sample) }}" class="btn btn-primary">
                        <i class="feather-send me-2"></i><span>Issue Movement</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        {{-- Sample summary strip --}}
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div>
                        <div class="text-muted fs-12">Sample</div>
                        <div class="fw-semibold">{{ $sample->sample_code }}</div>
                    </div>
                    <div>
                        <div class="text-muted fs-12">Product</div>
                        <div class="fw-semibold">{{ $sample->product_name }}</div>
                    </div>
                    <div>
                        <div class="text-muted fs-12">Customer</div>
                        <div class="fw-semibold">{{ $sample->customer->customer_name }}</div>
                    </div>
                    <div>
                        <div class="text-muted fs-12">Status</div>
                        @php $sc = ['Received'=>'primary','In Testing'=>'warning','Completed'=>'success','Returned'=>'secondary']; @endphp
                        <span class="badge bg-soft-{{ $sc[$sample->status] ?? 'secondary' }} text-{{ $sc[$sample->status] ?? 'secondary' }}">
                            {{ $sample->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Issue Date</th>
                                        <th>Moved By</th>
                                        <th>Assigned To</th>
                                        <th>Expected Return</th>
                                        <th>Actual Return</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movements as $m)
                                    @php $mc = ['Issued'=>'primary','Returned'=>'success','Overdue'=>'danger']; @endphp
                                    <tr>
                                        <td>{{ $movements->firstItem() + $loop->index }}</td>
                                        <td>{{ $m->issue_date->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-soft-secondary text-secondary">{{ $m->moved_by_type }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-info text-info">{{ $m->assigned_to_type }}</span>
                                        </td>
                                        <td>{{ $m->expected_return_date?->format('d M Y') ?? '—' }}</td>
                                        <td>{{ $m->actual_return_date?->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-soft-{{ $mc[$m->status] ?? 'secondary' }} text-{{ $mc[$m->status] ?? 'secondary' }}">
                                                {{ $m->status }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ Str::limit($m->remarks, 40) ?? '—' }}</td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('sample-movements.index')
                                                <a href="{{ route('movements.show', $m) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('sample-movements.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('movements.edit', $m) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Update Return</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('sample-movements.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('movements.destroy', $m) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this movement record?')">
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
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="feather-send fs-1 d-block mb-2"></i>
                                            No movements recorded for this sample.
                                            @can('sample-movements.create')
                                            <div class="mt-2">
                                                <a href="{{ route('samples.movements.create', $sample) }}" class="btn btn-sm btn-primary">
                                                    <i class="feather-plus me-1"></i> Issue First Movement
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
                        <small class="text-muted">Showing {{ $movements->firstItem() }}–{{ $movements->lastItem() }} of {{ $movements->total() }}</small>
                        {{ $movements->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
