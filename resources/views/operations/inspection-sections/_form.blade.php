{{-- Shared form for inspection section create / edit --}}
@php
    $sectionTypes = [
        'general_info', 'images', 'workmanship', 'defects', 'aql', 'checklist',
        'container', 'verification', 'review', 'article_results', 'conclusion',
        'finish', 'quantity_sampling', 'cartons', 'cover_photo', 'files_review',
        'checkpoint', 'production_stages',
    ];
    $icons = [
        'feather-camera','feather-check-square','feather-bar-chart-2','feather-list',
        'feather-box','feather-shield','feather-clipboard','feather-layers','feather-package',
        'feather-file-text','feather-search','feather-tool','feather-eye','feather-award',
    ];
    $is = isset($inspectionSection);
    $sec = $is ? $inspectionSection : null;
@endphp

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Section Information</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label">Section Name <span class="text-danger">*</span></label>
                <input type="text" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $sec?->name) }}"
                       placeholder="e.g. Workmanship Check"
                       required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-6">
                <label class="form-label">
                    Slug <span class="text-danger">*</span>
                    <small class="text-muted">(lowercase, underscores only)</small>
                </label>
                <input type="text" name="slug" id="slugField"
                       class="form-control @error('slug') is-invalid @enderror"
                       value="{{ old('slug', $sec?->slug) }}"
                       placeholder="e.g. workmanship_check"
                       pattern="[a-z0-9_]+"
                       required>
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-4">
                <label class="form-label">Section Type <span class="text-danger">*</span></label>
                <select name="section_type" class="form-select @error('section_type') is-invalid @enderror" required>
                    <option value="">— Select Type —</option>
                    @foreach($sectionTypes as $t)
                        <option value="{{ $t }}" @selected(old('section_type', $sec?->section_type) === $t)>
                            {{ ucwords(str_replace('_', ' ', $t)) }}
                        </option>
                    @endforeach
                </select>
                @error('section_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-4">
                <label class="form-label">Icon Class</label>
                <input type="text" name="icon"
                       class="form-control @error('icon') is-invalid @enderror"
                       value="{{ old('icon', $sec?->icon) }}"
                       placeholder="e.g. feather-layers">
                @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Feather icon class (feather-*)</small>
            </div>
            <div class="col-lg-4">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" min="0"
                       class="form-control @error('sort_order') is-invalid @enderror"
                       value="{{ old('sort_order', $sec?->sort_order ?? 0) }}">
                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-8">
                <label class="form-label">Skip Policy</label>
                <select name="skip_policy" class="form-select @error('skip_policy') is-invalid @enderror">
                    @foreach(['mandatory' => 'Mandatory — must be completed, cannot be skipped', 'optional' => 'Optional — not required to complete, but no Skip action', 'skippable' => 'Skippable — shows a Skip Section action on the run'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('skip_policy', $sec?->skip_policy ?? 'mandatory') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('skip_policy')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Controls whether inspectors can explicitly skip this section on an inspection run (e.g. Finish Inspection should stay Mandatory).</small>
            </div>
            <div class="col-lg-4">
                <label class="form-label d-block" for="isActiveSwitch">Active</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="is_active" id="isActiveSwitch"
                           value="1"
                           @checked(old('is_active', $sec ? $sec->is_active : true))>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="2"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Brief description of what this section covers…">{{ old('description', $sec?->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Default Data (JSON)</h5>
            <small class="text-muted">Optional JSON template used to pre-populate section data when a run is created.</small>
        </div>
    </div>
    <div class="card-body">
        <textarea name="default_data" rows="10"
                  class="form-control font-monospace @error('default_data') is-invalid @enderror"
                  placeholder='{"items": [{"name": "Stitching Quality", "required": true}]}'
                  style="font-size:12px">{{ old('default_data', $sec && $sec->default_data ? json_encode($sec->default_data, JSON_PRETTY_PRINT) : '') }}</textarea>
        @error('default_data')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted">Must be valid JSON. Leave empty if not needed.</small>
    </div>
</div>

@push('scripts')
<script>
// Auto-generate slug from name (create mode only)
@if(!isset($inspectionSection))
const nameInput = document.querySelector('[name="name"]');
const slugInput = document.getElementById('slugField');
let slugEdited = false;

slugInput.addEventListener('input', () => slugEdited = true);
nameInput.addEventListener('input', function () {
    if (slugEdited) return;
    slugInput.value = this.value
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
});
@endif
</script>
@endpush
