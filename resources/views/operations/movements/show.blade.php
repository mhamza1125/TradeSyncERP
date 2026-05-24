@extends('index')

@section('title', 'Movement Detail - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Movement Detail</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('movements.index') }}">Sample Movements</a></li>
                <li class="breadcrumb-item">Detail</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('movements.index') }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    @can('sample-movements.edit')
                    <a href="{{ route('movements.edit', $movement) }}" class="btn btn-primary">
                        <i class="feather-edit me-2"></i><span>Update Return</span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        @php $mc = ['Issued'=>'primary','Returned'=>'success','Overdue'=>'danger']; @endphp

        <div class="row">

            {{-- ── Main column ─────────────────────────────────────────────── --}}
            <div class="col-xl-8">

                {{-- Movement Items table --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center gap-2">
                        <h5 class="card-title mb-0">Samples in this Movement</h5>
                        <span class="badge bg-soft-secondary text-secondary ms-auto">
                            {{ $movement->items->count() }} line(s)
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sample</th>
                                        <th style="width:90px">Color</th>
                                        <th style="width:70px">Size</th>
                                        <th class="text-center" style="width:70px">Qty</th>
                                        <th style="width:130px">Actual Return</th>
                                        <th style="width:110px">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movement->items as $item)
                                    @php
                                        $itemStatus = $item->status ?? $movement->status;
                                        $sc = ['Issued'=>'primary','Returned'=>'success','Overdue'=>'danger'];
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($item->sample)
                                            <a href="{{ route('samples.show', $item->sample) }}" class="fw-semibold text-primary">
                                                {{ $item->sample->sample_code }}
                                            </a>
                                            <div class="text-muted fs-11">{{ $item->sample->product_name }}</div>
                                            @else
                                            <span class="text-muted fst-italic">Removed</span>
                                            @endif
                                        </td>
                                        <td class="fs-12">
                                            {{ optional($item->variation?->color)->name ?? '—' }}
                                        </td>
                                        <td class="fs-12">
                                            {{ optional($item->variation?->size)->name ?? '—' }}
                                        </td>
                                        <td class="text-center fw-semibold">{{ $item->quantity }}</td>
                                        <td class="fs-12">
                                            @if($item->actual_return_date)
                                                <span class="text-success">{{ $item->actual_return_date->format('d M Y') }}</span>
                                            @elseif($movement->actual_return_date)
                                                <span class="text-muted fst-italic">{{ $movement->actual_return_date->format('d M Y') }} (group)</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-soft-{{ $sc[$itemStatus] ?? 'secondary' }} text-{{ $sc[$itemStatus] ?? 'secondary' }} fs-11">
                                                {{ $itemStatus }}{{ $item->status ? '' : ' *' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No items found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($movement->items->whereNull('status')->count())
                        <div class="px-3 py-2 border-top">
                            <small class="text-muted">* Inheriting group status</small>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Linked Inspection Run --}}
                @if($movement->inspectionRun)
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Linked Inspection Run</h5></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <i class="feather-clipboard text-muted fs-4"></i>
                            <div>
                                <a href="{{ route('inspections.show', $movement->inspectionRun->inspection) }}"
                                   class="fw-semibold text-primary">
                                    {{ $movement->inspectionRun->inspection->report_number ?? '—' }}
                                </a>
                                <div class="text-muted fs-12">
                                    Run #{{ $movement->inspectionRun->run_number ?? $movement->inspectionRun->id }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>

            {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
            <div class="col-xl-4">

                {{-- Movement Info --}}
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center gap-2">
                        <h5 class="card-title mb-0">Movement Info</h5>
                        <span class="badge bg-soft-{{ $mc[$movement->status] ?? 'secondary' }} text-{{ $mc[$movement->status] ?? 'secondary' }} ms-auto fs-12">
                            {{ $movement->status }}
                        </span>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted fs-12">Issue Date</span>
                                <strong>{{ $movement->issue_date->format('d M Y') }}</strong>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted fs-12">Expected Return</span>
                                <strong>{{ $movement->expected_return_date?->format('d M Y') ?? '—' }}</strong>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted fs-12">Actual Return</span>
                                <strong class="{{ $movement->actual_return_date ? 'text-success' : 'text-muted' }}">
                                    {{ $movement->actual_return_date?->format('d M Y') ?? 'Not yet returned' }}
                                </strong>
                            </li>
                            @if($movement->alert_days)
                            <li class="list-group-item px-0 d-flex justify-content-between">
                                <span class="text-muted fs-12">Alert Days</span>
                                <strong>{{ $movement->alert_days }} days</strong>
                            </li>
                            @endif
                        </ul>
                        @if($movement->remarks)
                        <div class="mt-3 pt-2 border-top">
                            <div class="text-muted fs-12 mb-1">Remarks</div>
                            <div class="fs-13">{{ $movement->remarks }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Assigned Employees --}}
                <div class="card mb-4">
                    <div class="card-header"><h5 class="card-title mb-0">Assigned Employees</h5></div>
                    <div class="card-body">
                        @forelse($movement->employees as $e)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="avatar-text avatar-sm bg-soft-primary text-primary rounded-circle fw-bold fs-12">
                                {{ strtoupper(substr($e->employee_name, 0, 1)) }}
                            </div>
                            <span class="fs-13">{{ $e->employee_name }}</span>
                        </div>
                        @empty
                        <span class="text-muted fs-12">No employees assigned.</span>
                        @endforelse
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Actions</h5></div>
                    <div class="card-body">
                        @can('sample-movements.edit')
                        <a href="{{ route('movements.edit', $movement) }}" class="btn btn-light-brand w-100 mb-3">
                            <i class="feather-edit-3 me-2"></i> Update Return
                        </a>
                        @endcan
                        @can('sample-movements.delete')
                        <form action="{{ route('movements.destroy', $movement) }}" method="POST"
                              onsubmit="return confirm('Delete this movement event and all its items?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-light-danger w-100" type="submit">
                                <i class="feather-trash-2 me-2"></i> Delete Movement
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
