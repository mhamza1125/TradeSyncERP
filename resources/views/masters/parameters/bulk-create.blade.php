@extends('index')

@section('title', 'Bulk Add Parameters - TradeSyncERP')

@section('content')
<div class="nxl-content apps-container">
    <div class="nxl-content without-header nxl-full-content">
        <div class="main-content d-flex">

            @include('partials.masters-sidebar')

            <div class="content-area" data-scrollbar-target="#psScrollbarInit">
                <div class="content-area-header bg-white sticky-top">
                    <div class="page-header-left d-flex align-items-center">
                        <a href="javascript:void(0);" class="app-sidebar-open-trigger me-2">
                            <i class="feather-align-left fs-24"></i>
                        </a>
                        <div class="page-header-title"><h5 class="m-b-10 mb-0">Testing Parameters</h5></div>
                        <ul class="breadcrumb ms-3 mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('masters.parameters.index') }}">Parameters</a></li>
                            <li class="breadcrumb-item">Bulk Add</li>
                        </ul>
                    </div>
                    <div class="page-header-right ms-auto">
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('masters.parameters.index') }}" class="btn btn-light-brand">
                                <i class="feather-arrow-left me-2"></i><span>Back</span>
                            </a>
                            <button type="submit" form="bulkParamForm" class="btn btn-primary">
                                <i class="feather-save me-2"></i><span>Save All</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="content-area-body">
                    @include('partials.flash-messages')

                    <form id="bulkParamForm" action="{{ route('masters.parameters.bulk-store') }}" method="POST">
                        @csrf
                        <div class="row justify-content-center">
                            <div class="col-xl-10">
                                <div class="card stretch stretch-full">
                                    <div class="card-header">
                                        <h5 class="card-title">Bulk Parameter Entry</h5>
                                        <p class="text-muted fs-12 mb-0">
                                            Select a category once, then add as many parameters as needed. All rows will be saved together.
                                        </p>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-4">
                                            <div class="col-lg-6">
                                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                                    <option value="">— Select Category —</option>
                                                    @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                                        {{ $cat->category_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="paramTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:40px">#</th>
                                                        <th>Parameter Name <span class="text-danger">*</span></th>
                                                        <th>Description</th>
                                                        <th style="width:60px"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="paramRows">
                                                    @if(old('parameters'))
                                                        @foreach(old('parameters') as $i => $row)
                                                        <tr class="param-row">
                                                            <td class="text-center align-middle row-num">{{ $i + 1 }}</td>
                                                            <td>
                                                                <input type="text" name="parameters[{{ $i }}][parameter_name]"
                                                                       class="form-control @error("parameters.{$i}.parameter_name") is-invalid @enderror"
                                                                       placeholder="e.g. pH Level"
                                                                       value="{{ $row['parameter_name'] ?? '' }}">
                                                                @error("parameters.{$i}.parameter_name")
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <input type="text" name="parameters[{{ $i }}][description]"
                                                                       class="form-control"
                                                                       placeholder="Optional description"
                                                                       value="{{ $row['description'] ?? '' }}">
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <button type="button" class="btn btn-sm btn-light remove-param-row"
                                                                        @if($i === 0) style="visibility:hidden;" @endif>
                                                                    <i class="feather-trash-2 text-danger"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    @else
                                                        @for($i = 0; $i < 3; $i++)
                                                        <tr class="param-row">
                                                            <td class="text-center align-middle row-num">{{ $i + 1 }}</td>
                                                            <td>
                                                                <input type="text" name="parameters[{{ $i }}][parameter_name]"
                                                                       class="form-control" placeholder="e.g. pH Level">
                                                            </td>
                                                            <td>
                                                                <input type="text" name="parameters[{{ $i }}][description]"
                                                                       class="form-control" placeholder="Optional description">
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <button type="button" class="btn btn-sm btn-light remove-param-row"
                                                                        @if($i === 0) style="visibility:hidden;" @endif>
                                                                    <i class="feather-trash-2 text-danger"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @endfor
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>

                                        <button type="button" id="addParamRow" class="btn btn-sm btn-primary mt-2">
                                            <i class="feather-plus me-1"></i> Add Row
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let rowIdx = {{ old('parameters') ? count(old('parameters')) : 3 }};

    document.getElementById('addParamRow').addEventListener('click', function () {
        const tbody    = document.getElementById('paramRows');
        const rowCount = tbody.querySelectorAll('.param-row').length;
        const tr = document.createElement('tr');
        tr.className = 'param-row';
        tr.innerHTML = `
            <td class="text-center align-middle row-num">${rowCount + 1}</td>
            <td><input type="text" name="parameters[${rowIdx}][parameter_name]" class="form-control" placeholder="e.g. pH Level"></td>
            <td><input type="text" name="parameters[${rowIdx}][description]" class="form-control" placeholder="Optional description"></td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-light remove-param-row">
                    <i class="feather-trash-2 text-danger"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIdx++;
        renumberRows();
    });

    document.getElementById('paramRows').addEventListener('click', function (e) {
        if (e.target.closest('.remove-param-row')) {
            const rows = document.querySelectorAll('.param-row');
            if (rows.length > 1) {
                e.target.closest('.param-row').remove();
                renumberRows();
            }
        }
    });

    function renumberRows() {
        document.querySelectorAll('.param-row').forEach((row, i) => {
            const numCell = row.querySelector('.row-num');
            if (numCell) numCell.textContent = i + 1;
        });
    }
</script>
@endpush
@endsection
