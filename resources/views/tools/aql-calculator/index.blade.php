@extends('index')

@section('title', 'AQL Calculator - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">AQL Calculator</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">AQL Calculator</li>
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
                    @can('aql-calculator.create')
                    <a href="{{ route('tools.aql-calculator.create') }}" class="btn btn-primary">
                        <i class="feather-plus me-2"></i><span>New Calculation</span>
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
            <form method="GET" action="{{ route('tools.aql-calculator.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by title..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="inspection_level" class="form-select">
                            <option value="">All Levels</option>
                            @foreach(['I','II','III','S1','S2','S3','S4'] as $lvl)
                            <option value="{{ $lvl }}" @selected(request('inspection_level') === $lvl)>{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="verdict" class="form-select">
                            <option value="">All Verdicts</option>
                            <option value="Pending" @selected(request('verdict') === 'Pending')>Pending</option>
                            <option value="Pass" @selected(request('verdict') === 'Pass')>Pass</option>
                            <option value="Fail" @selected(request('verdict') === 'Fail')>Fail</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100"><i class="feather-search"></i></button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('tools.aql-calculator.index') }}" class="btn btn-light-brand w-100">Reset</a>
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
                            <table class="table table-hover" id="aqlCalculationList">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th class="text-center">Lot Size</th>
                                        <th class="text-center">Level</th>
                                        <th class="text-center">Sample Size</th>
                                        <th class="text-center">Verdict</th>
                                        <th>Saved</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($aqlCalculations as $calc)
                                    <tr class="single-item">
                                        <td class="fw-semibold text-dark">{{ $calc->title }}</td>
                                        <td class="text-center">{{ number_format($calc->lot_size) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-soft-secondary text-secondary">{{ $calc->inspection_level }}</span>
                                        </td>
                                        <td class="text-center">{{ $calc->sample_size ?? '—' }}</td>
                                        <td class="text-center">
                                            @if($calc->verdict === 'Pass')
                                            <span class="badge bg-soft-success text-success">Pass</span>
                                            @elseif($calc->verdict === 'Fail')
                                            <span class="badge bg-soft-danger text-danger">Fail</span>
                                            @else
                                            <span class="badge bg-soft-secondary text-secondary">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $calc->created_at->format('d M Y') }}</td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                @can('aql-calculator.index')
                                                <a href="{{ route('tools.aql-calculator.show', $calc) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                                @endcan
                                                <div class="dropdown">
                                                    <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                        <i class="feather feather-more-horizontal"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @can('aql-calculator.edit')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('tools.aql-calculator.edit', $calc) }}">
                                                                <i class="feather feather-edit-3 me-3"></i><span>Edit</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('aql-calculator.index')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('tools.aql-calculator.export-pdf', $calc) }}" target="_blank">
                                                                <i class="feather feather-download me-3"></i><span>Export PDF</span>
                                                            </a>
                                                        </li>
                                                        @endcan
                                                        @can('aql-calculator.delete')
                                                        <li class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('tools.aql-calculator.destroy', $calc) }}" method="POST"
                                                                    onsubmit="return confirm('Delete this AQL calculation? This cannot be undone.')">
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
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="feather-cpu fs-1 d-block mb-2"></i>
                                            No saved AQL calculations found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($aqlCalculations->hasPages())
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing {{ $aqlCalculations->firstItem() }}–{{ $aqlCalculations->lastItem() }} of {{ $aqlCalculations->total() }}</small>
                        {{ $aqlCalculations->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
