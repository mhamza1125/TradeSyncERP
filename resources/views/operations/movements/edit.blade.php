@extends('index')

@section('title', 'Update Return – TradeSyncERP')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
<style>
    .ts-wrapper.form-control { padding: 0; border: 0; }
    .ts-wrapper .ts-control { border-radius: 0.375rem; }
</style>
@endpush

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Update Return</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('movements.index') }}">Sample Movements</a></li>
                <li class="breadcrumb-item"><a href="{{ route('movements.show', $movement) }}">Detail</a></li>
                <li class="breadcrumb-item">Update Return</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="{{ route('movements.show', $movement) }}" class="btn btn-light-brand">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                    <button type="submit" form="editMovementForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="editMovementForm" action="{{ route('movements.update', $movement) }}" method="POST">
            @csrf @method('PUT')

            <div class="row">

                {{-- ── Main column ─────────────────────────────────────────────── --}}
                <div class="col-xl-8">

                    {{-- Group-level return --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Group Return</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted fs-13 mb-3">
                                Setting these values applies to the entire movement. Individual items can be overridden below.
                            </p>
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Overall Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        @foreach(['Issued', 'Returned', 'Overdue'] as $s)
                                        <option value="{{ $s }}" @selected(old('status', $movement->status) === $s)>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Group Actual Return Date</label>
                                    <input type="date" name="actual_return_date"
                                           class="form-control @error('actual_return_date') is-invalid @enderror"
                                           value="{{ old('actual_return_date', $movement->actual_return_date?->toDateString()) }}">
                                    <div class="form-text">Applies to all items unless overridden per item.</div>
                                    @error('actual_return_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 mb-0">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" rows="2"
                                              class="form-control @error('remarks') is-invalid @enderror"
                                              placeholder="Notes about the return or current status…">{{ old('remarks', $movement->remarks ?? '') }}</textarea>
                                    @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Per-item return --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                Per-Item Return
                                <span class="text-muted fw-normal fs-13">(optional overrides)</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sample</th>
                                            <th style="width:90px">Color</th>
                                            <th style="width:70px">Size</th>
                                            <th class="text-center" style="width:60px">Qty</th>
                                            <th style="width:165px">Actual Return Date</th>
                                            <th style="width:150px">Status Override</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($movement->items as $idx => $item)
                                        <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item->id }}">
                                        <tr>
                                            <td>
                                                @if($item->sample)
                                                <span class="fw-semibold fs-13">{{ $item->sample->sample_code }}</span>
                                                <div class="text-muted fs-11">{{ $item->sample->product_name }}</div>
                                                @else
                                                <span class="text-muted fst-italic">Removed</span>
                                                @endif
                                            </td>
                                            <td class="fs-12 text-muted">
                                                {{ optional($item->variation?->color)->name ?? '—' }}
                                            </td>
                                            <td class="fs-12 text-muted">
                                                {{ optional($item->variation?->size)->name ?? '—' }}
                                            </td>
                                            <td class="text-center text-muted">{{ $item->quantity }}</td>
                                            <td>
                                                <input type="date"
                                                       name="items[{{ $idx }}][actual_return_date]"
                                                       class="form-control form-control-sm"
                                                       value="{{ old("items.{$idx}.actual_return_date", $item->actual_return_date?->toDateString()) }}">
                                            </td>
                                            <td>
                                                <select name="items[{{ $idx }}][status]" class="form-select form-select-sm">
                                                    <option value="">— Inherit group —</option>
                                                    @foreach(['Issued', 'Returned', 'Overdue'] as $s)
                                                    <option value="{{ $s }}"
                                                        @selected(old("items.{$idx}.status", $item->status) === $s)>
                                                        {{ $s }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="items[{{ $idx }}][remarks]"
                                                       class="form-control form-control-sm"
                                                       placeholder="Item note…"
                                                       value="{{ old("items.{$idx}.remarks", $item->remarks) }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
                <div class="col-xl-4">

                    {{-- Movement summary --}}
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="card-title mb-0">Movement Summary</h5></div>
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
                                    <span class="text-muted fs-12">Items</span>
                                    <strong>{{ $movement->items->count() }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Assigned employees --}}
                    <div class="card mb-4">
                        <div class="card-header"><h5 class="card-title mb-0">Assigned Employees</h5></div>
                        <div class="card-body">
                            <select name="employee_ids[]" id="employeeEditSelect" multiple
                                    placeholder="Select employee(s)…"
                                    class="@error('employee_ids') is-invalid @enderror">
                                @php $currentEmpIds = $movement->employees->pluck('id')->toArray(); @endphp
                                @foreach($employees as $e)
                                <option value="{{ $e->id }}"
                                    @if(in_array($e->id, old('employee_ids', $currentEmpIds))) selected @endif>
                                    {{ $e->employee_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('employee_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Status guide --}}
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Status Guide</h5></div>
                        <div class="card-body">
                            <div class="alert alert-light border mb-0">
                                <small class="text-muted">
                                    <strong class="text-primary">Issued</strong> — Sample(s) currently out.<br>
                                    <strong class="text-success">Returned</strong> — Returned; set the actual return date.<br>
                                    <strong class="text-danger">Overdue</strong> — Past expected return date, not yet returned.<br><br>
                                    Per-item overrides take precedence over the group status in reporting.
                                </small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    new TomSelect('#employeeEditSelect', {
        plugins: ['remove_button'],
        placeholder: 'Select employee(s)…',
    });
</script>
@endpush

@endsection
