@extends('index')

@section('title', 'Samples - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Samples</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Samples</li>
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
                    @can('samples.index')
                    <a href="{{ route('samples.export-list-pdf', request()->query()) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i><span>Export PDF</span>
                    </a>
                    @endcan
                    @can('samples.create')
                    <a href="{{ route('samples.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>New Sample</span>
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

    <div id="collapseFilters" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <form method="GET" action="{{ route('samples.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Sample code or product..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="customer_id" class="form-select">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach(['Received','In Testing','Completed','Returned'] as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="priority_level" class="form-select">
                            <option value="">All Priority</option>
                            @foreach(['Low','Medium','High','Urgent'] as $p)
                            <option value="{{ $p }}" @selected(request('priority_level') == $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('samples.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover" id="sampleList">
                                <thead>
                                    <tr>
                                        <th>Sample Code</th>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Category</th>
                                        <th>Receive Date</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th class="text-center">Total Qty</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($samples as $sample)
                                    @php
                                        $priorityColors = ['Low'=>'secondary','Medium'=>'info','High'=>'warning','Urgent'=>'danger'];
                                        $statusColors   = ['Received'=>'primary','In Testing'=>'warning','Completed'=>'success','Returned'=>'secondary'];
                                    @endphp
                                    <tr class="single-item">
                                        <td>
                                            <a href="{{ route('samples.show', $sample) }}" class="fw-bold text-primary">
                                                {{ $sample->sample_code }}
                                            </a>
                                        </td>
                                        <td>{{ $sample->product_name }}</td>
                                        <td>{{ $sample->customer->customer_name }}</td>
                                        <td>{{ $sample->category->category_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sample->receive_date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge bg-soft-{{ $priorityColors[$sample->priority_level] ?? 'secondary' }} text-{{ $priorityColors[$sample->priority_level] ?? 'secondary' }}">
                                                {{ $sample->priority_level }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-{{ $statusColors[$sample->status] ?? 'secondary' }} text-{{ $statusColors[$sample->status] ?? 'secondary' }}">
                                                {{ $sample->status }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold">
                                            {{ $sample->variations_sum_quantity ?? 0 }}
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('samples.index')
                                                <a href="{{ route('samples.show', $sample) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('samples.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('samples.edit', $sample) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('samples.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('samples.destroy', $sample) }}" method="POST"
                                                                  onsubmit="return confirm('Delete this sample?')">
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
                                            <i class="feather-package fs-1 d-block mb-2"></i>
                                            No samples found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($samples->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $samples->firstItem() }}–{{ $samples->lastItem() }} of {{ $samples->total() }}</small>
                        {{ $samples->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
