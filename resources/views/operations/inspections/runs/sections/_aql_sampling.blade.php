{{-- AQL Sampling Section — ISO 2859-1 --}}
@php
    $a = $aql; // InspectionRunAql model or null
@endphp

<div id="aql-calculator-form">

    {{-- ── Sampling plan inputs ───────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">Lot Size <span class="text-danger">*</span></label>
            <input type="number" id="aql_lot_size" name="aql[lot_size]"
                   class="form-control form-control-sm"
                   value="{{ old('aql.lot_size', $a?->lot_size) }}"
                   placeholder="e.g. 5000" min="1">
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">Inspection Level</label>
            <select id="aql_inspection_level" name="aql[inspection_level]" class="form-select form-select-sm">
                @foreach(['I','II','III','S1','S2','S3','S4'] as $lvl)
                    <option value="{{ $lvl }}" @selected(old('aql.inspection_level', $a?->inspection_level ?? 'II') === $lvl)>
                        {{ $lvl }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">AQL Critical</label>
            <select id="aql_aql_critical" name="aql[aql_critical]" class="form-select form-select-sm">
                <option value="">— None —</option>
                @foreach([0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5] as $lvl)
                    <option value="{{ $lvl }}" @selected((float)old('aql.aql_critical', $a?->aql_critical ?? 0.065) === $lvl)>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">AQL Major</label>
            <select id="aql_aql_major" name="aql[aql_major]" class="form-select form-select-sm">
                <option value="">— None —</option>
                @foreach([0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5] as $lvl)
                    <option value="{{ $lvl }}" @selected((float)old('aql.aql_major', $a?->aql_major ?? 2.5) === $lvl)>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">AQL Minor</label>
            <select id="aql_aql_minor" name="aql[aql_minor]" class="form-select form-select-sm">
                <option value="">— None —</option>
                @foreach([0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5] as $lvl)
                    <option value="{{ $lvl }}" @selected((float)old('aql.aql_minor', $a?->aql_minor ?? 4.0) === $lvl)>{{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4 d-flex align-items-end">
            <button type="button" id="aql-calculate-btn" class="btn btn-sm btn-light-primary w-100">
                <i class="feather-cpu me-1"></i>Calculate
            </button>
        </div>
    </div>

    {{-- ── Calculated plan result ──────────────────────────────────────────── --}}
    <div id="aql-result-row" class="{{ $a && $a->sample_size > 0 ? '' : 'd-none' }}">
        <div class="row g-3 mb-4">
            <div class="col-lg-2 col-md-3">
                <label class="form-label fw-semibold fs-12">Code Letter</label>
                <input type="text" id="aql_code_letter"
                       class="form-control form-control-sm bg-light text-center fw-bold"
                       value="{{ $a?->code_letter }}" readonly tabindex="-1">
            </div>
            <div class="col-lg-2 col-md-3">
                <label class="form-label fw-semibold fs-12">Sample Size</label>
                <input type="number" id="aql_sample_size" name="aql[sample_size]"
                       class="form-control form-control-sm bg-light text-center fw-bold"
                       value="{{ old('aql.sample_size', $a?->sample_size) }}" readonly tabindex="-1">
            </div>

            {{-- Critical Ac / Re --}}
            <div class="col-lg-1 col-md-3">
                <label class="form-label fw-semibold fs-12 text-danger">CR Ac</label>
                <input type="number" id="aql_ac_critical" name="aql[ac_critical]"
                       class="form-control form-control-sm text-center"
                       value="{{ old('aql.ac_critical', $a?->ac_critical) }}">
            </div>
            <div class="col-lg-1 col-md-3">
                <label class="form-label fw-semibold fs-12 text-danger">CR Re</label>
                <input type="number" id="aql_re_critical" name="aql[re_critical]"
                       class="form-control form-control-sm text-center"
                       value="{{ old('aql.re_critical', $a?->re_critical) }}">
            </div>

            {{-- Major Ac / Re --}}
            <div class="col-lg-1 col-md-3">
                <label class="form-label fw-semibold fs-12 text-warning">MA Ac</label>
                <input type="number" id="aql_ac_major" name="aql[ac_major]"
                       class="form-control form-control-sm text-center"
                       value="{{ old('aql.ac_major', $a?->ac_major) }}">
            </div>
            <div class="col-lg-1 col-md-3">
                <label class="form-label fw-semibold fs-12 text-warning">MA Re</label>
                <input type="number" id="aql_re_major" name="aql[re_major]"
                       class="form-control form-control-sm text-center"
                       value="{{ old('aql.re_major', $a?->re_major) }}">
            </div>

            {{-- Minor Ac / Re --}}
            <div class="col-lg-1 col-md-3">
                <label class="form-label fw-semibold fs-12 text-info">MI Ac</label>
                <input type="number" id="aql_ac_minor" name="aql[ac_minor]"
                       class="form-control form-control-sm text-center"
                       value="{{ old('aql.ac_minor', $a?->ac_minor) }}">
            </div>
            <div class="col-lg-1 col-md-3">
                <label class="form-label fw-semibold fs-12 text-info">MI Re</label>
                <input type="number" id="aql_re_minor" name="aql[re_minor]"
                       class="form-control form-control-sm text-center"
                       value="{{ old('aql.re_minor', $a?->re_minor) }}">
            </div>
        </div>
    </div>

    {{-- ── Defect counts found ─────────────────────────────────────────────── --}}
    <hr class="my-4">
    <h6 class="fw-semibold mb-3">Defects Found During Inspection</h6>
    <div class="row g-3 align-items-end mb-4">
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-danger text-danger me-1">CR</span> Critical Found
            </label>
            <input type="number" id="aql_found_critical" name="aql[found_critical]"
                   class="form-control form-control-sm"
                   value="{{ old('aql.found_critical', $a?->found_critical ?? 0) }}"
                   min="0">
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-warning text-warning me-1">MA</span> Major Found
            </label>
            <input type="number" id="aql_found_major" name="aql[found_major]"
                   class="form-control form-control-sm"
                   value="{{ old('aql.found_major', $a?->found_major ?? 0) }}"
                   min="0">
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-semibold fs-12">
                <span class="badge bg-soft-info text-info me-1">MI</span> Minor Found
            </label>
            <input type="number" id="aql_found_minor" name="aql[found_minor]"
                   class="form-control form-control-sm"
                   value="{{ old('aql.found_minor', $a?->found_minor ?? 0) }}"
                   min="0">
        </div>
        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold fs-12">AQL Verdict</label>
            <div>
                @php
                    $verdictClass = match($a?->verdict) {
                        'Pass' => 'bg-soft-success text-success',
                        'Fail' => 'bg-soft-danger text-danger',
                        default => 'bg-soft-secondary text-secondary',
                    };
                @endphp
                <span id="aql_verdict_display"
                      class="badge {{ $verdictClass }} fs-13 px-3 py-2">
                    {{ $a?->verdict ?? 'Pending' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="mb-0">
        <label class="form-label fw-semibold fs-12">AQL Notes</label>
        <textarea name="aql[notes]" rows="2"
                  class="form-control form-control-sm"
                  placeholder="Sampling observations, deviations, inspector notes…">{{ old('aql.notes', $a?->notes) }}</textarea>
    </div>
</div>
