@extends('index')

@section('title', 'AQL Calculation - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">AQL Calculator</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tools.aql-calculator.index') }}">AQL Calculator</a></li>
                <li class="breadcrumb-item">{{ $aqlCalculation->title }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('tools.aql-calculator.index') }}" class="btn btn-icon btn-light-brand">
                        <i class="feather-arrow-left"></i>
                    </a>
                    <a href="{{ route('tools.aql-calculator.export-pdf', $aqlCalculation) }}" class="btn btn-light-brand" target="_blank">
                        <i class="feather-download me-2"></i>Export PDF
                    </a>
                    @can('aql-calculator.edit')
                    <a href="{{ route('tools.aql-calculator.edit', $aqlCalculation) }}" class="btn btn-light-brand">
                        <i class="feather-edit me-2"></i>Edit
                    </a>
                    @endcan
                    @can('aql-calculator.delete')
                    <form action="{{ route('tools.aql-calculator.destroy', $aqlCalculation) }}" method="POST"
                          onsubmit="return confirm('Delete this AQL calculation? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-light-brand" type="submit">
                            <i class="feather-trash-2 me-2"></i>Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="row">
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="feather-bar-chart-2 me-2 text-primary"></i>Sampling Plan</h5>
                        <div>
                            <span class="badge bg-soft-secondary text-secondary me-1">Level: <strong>{{ $aqlCalculation->inspection_level }}</strong></span>
                            <span class="badge bg-soft-primary text-primary me-1">Code: <strong>{{ $aqlCalculation->code_letter ?? '—' }}</strong></span>
                            <span class="badge bg-soft-success text-success">Base n = <strong>{{ $aqlCalculation->sample_size ?? '—' }}</strong></span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Defect Type</th>
                                        <th class="text-center">AQL Level</th>
                                        <th class="text-center">Sample Size</th>
                                        <th class="text-center">Accept (Ac)</th>
                                        <th class="text-center">Reject (Re)</th>
                                        <th class="text-center">Found</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([
                                        ['label' => 'Critical', 'badge' => 'danger', 'aql' => $aqlCalculation->aql_critical, 'ac' => $aqlCalculation->ac_critical, 're' => $aqlCalculation->re_critical, 'found' => $aqlCalculation->found_critical],
                                        ['label' => 'Major', 'badge' => 'warning', 'aql' => $aqlCalculation->aql_major, 'ac' => $aqlCalculation->ac_major, 're' => $aqlCalculation->re_major, 'found' => $aqlCalculation->found_major],
                                        ['label' => 'Minor', 'badge' => 'info', 'aql' => $aqlCalculation->aql_minor, 'ac' => $aqlCalculation->ac_minor, 're' => $aqlCalculation->re_minor, 'found' => $aqlCalculation->found_minor],
                                    ] as $row)
                                    <tr>
                                        <td><span class="badge bg-soft-{{ $row['badge'] }} text-{{ $row['badge'] }}">{{ $row['label'] }}</span></td>
                                        <td class="text-center">{{ $row['aql'] === 'not_allowed' ? 'Not Allowed' : ($row['aql'] ?? '—') }}</td>
                                        <td class="text-center fw-semibold">{{ $aqlCalculation->sample_size ?? '—' }}</td>
                                        <td class="text-center">{{ $row['ac'] ?? '—' }}</td>
                                        <td class="text-center">{{ $row['re'] ?? '—' }}</td>
                                        <td class="text-center">{{ $row['found'] }}</td>
                                        <td class="text-center">
                                            @php
                                                $status = 'Pending'; $cls = 'bg-soft-secondary text-secondary';
                                                if ($row['found'] > 0 && $row['ac'] !== null) {
                                                    if ($row['found'] > $row['ac']) { $status = 'Fail'; $cls = 'bg-soft-danger text-danger'; }
                                                    else { $status = 'Pass'; $cls = 'bg-soft-success text-success'; }
                                                }
                                            @endphp
                                            <span class="badge {{ $cls }}">{{ $status }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if($aqlCalculation->variations)
                @php
                    $variationTotalQty = collect($aqlCalculation->variations)->sum('qty');
                    $variationTotalInspect = collect($aqlCalculation->variations)->sum('inspect_qty');
                @endphp
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="feather-grid me-2 text-success"></i>Variations</h5>
                        <span class="badge bg-soft-secondary text-secondary">
                            Distributed across Base n = <strong>{{ $aqlCalculation->sample_size ?? '—' }}</strong>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th class="text-center">Ordered Qty</th>
                                        <th class="text-center">Share %</th>
                                        <th class="text-center">Inspect Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($aqlCalculation->variations as $row)
                                    <tr>
                                        <td>{{ $row['color'] ?: '—' }}</td>
                                        <td>{{ $row['size'] ?: '—' }}</td>
                                        <td class="text-center">{{ number_format($row['qty'] ?? 0) }}</td>
                                        <td class="text-center text-muted">{{ $variationTotalQty > 0 ? number_format((($row['qty'] ?? 0) / $variationTotalQty) * 100, 1).'%' : '—' }}</td>
                                        <td class="text-center fw-semibold text-success">{{ $row['inspect_qty'] ?? 0 }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light fw-semibold">
                                        <td colspan="2" class="text-end text-muted fs-12">Total</td>
                                        <td class="text-center">{{ number_format($variationTotalQty) }}</td>
                                        <td class="text-center">100%</td>
                                        <td class="text-center">{{ number_format($variationTotalInspect) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                @if($aqlCalculation->notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="feather-file-text me-2 text-muted"></i>Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted">{{ $aqlCalculation->notes }}</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            @php
                                $verdictMap = [
                                    'Pass' => ['cls' => 'bg-soft-success text-success', 'ico' => 'feather-check-circle', 'tcls' => 'text-success'],
                                    'Fail' => ['cls' => 'bg-soft-danger text-danger', 'ico' => 'feather-x-circle', 'tcls' => 'text-danger'],
                                    'Pending' => ['cls' => 'bg-soft-secondary text-secondary', 'ico' => 'feather-clock', 'tcls' => 'text-secondary'],
                                ];
                                $vm = $verdictMap[$aqlCalculation->verdict] ?? $verdictMap['Pending'];
                            @endphp
                            <div class="avatar-text avatar-lg rounded {{ $vm['cls'] }}">
                                <i class="{{ $vm['ico'] }}"></i>
                            </div>
                            <div>
                                <div class="fs-12 text-muted">Overall Verdict</div>
                                <div class="fs-4 fw-bold {{ $vm['tcls'] }}">{{ $aqlCalculation->verdict }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Record Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Lot Size</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ number_format($aqlCalculation->lot_size) }} units</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Inspection Level</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ $aqlCalculation->inspection_level }}</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Code Letter</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ $aqlCalculation->code_letter ?? '—' }}</div>
                        </div>
                        <div class="row g-0 mb-3">
                            <div class="col-sm-5 text-muted">Base Sample</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ $aqlCalculation->sample_size ?? '—' }}</div>
                        </div>
                        <div class="row g-0">
                            <div class="col-sm-5 text-muted">Saved</div>
                            <div class="col-sm-7 fw-semibold text-dark">{{ $aqlCalculation->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
