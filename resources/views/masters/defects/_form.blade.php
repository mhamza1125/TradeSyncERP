@php
    $severityOptions = [
        'critical'   => 'Critical — renders product unsafe or completely unusable',
        'major'      => 'Major — significantly affects function or appearance',
        'minor'      => 'Minor — deviates from spec but does not reduce usability',
        'functional' => 'Functional — affects a specific function or feature',
    ];
    $severityColors = ['critical' => 'danger', 'major' => 'warning', 'minor' => 'info', 'functional' => 'secondary'];
@endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label fw-semibold">Defect Name <span class="text-danger">*</span></label>
        <input type="text"
               name="defect_name"
               class="form-control @error('defect_name') is-invalid @enderror"
               value="{{ old('defect_name', $defect->defect_name ?? '') }}"
               placeholder="e.g. Belt Loop Missing"
               required>
        @error('defect_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Severity <span class="text-danger">*</span></label>
        <div class="d-flex flex-wrap gap-2">
            @foreach($severityOptions as $val => $desc)
            @php $color = $severityColors[$val]; @endphp
            <div class="form-check p-0">
                <input class="btn-check" type="radio"
                       name="severity"
                       id="severity_{{ $val }}"
                       value="{{ $val }}"
                       @checked(old('severity', $defect->severity ?? '') === $val)
                       required>
                <label class="btn btn-outline-{{ $color }} btn-sm" for="severity_{{ $val }}">
                    {{ ucfirst($val) }}
                </label>
            </div>
            @endforeach
        </div>
        @error('severity')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">Corrective Action</label>
        <textarea name="corrective_action"
                  class="form-control @error('corrective_action') is-invalid @enderror"
                  rows="3"
                  placeholder="Describe the corrective action to resolve this defect…">{{ old('corrective_action', $defect->corrective_action ?? '') }}</textarea>
        @error('corrective_action')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="status" id="defectStatus" value="1"
                   @checked(old('status', $defect->status ?? true))>
            <label class="form-check-label fw-semibold" for="defectStatus">Active</label>
        </div>
    </div>
</div>
