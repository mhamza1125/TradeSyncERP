{{-- Reusable sample form partial --}}
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Sample Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customerSelect" class="form-select @error('customer_id') is-invalid @enderror">
                            <option value="">— Select Customer —</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id', $sample->customer_id ?? '') == $customer->id)>
                                    {{ $customer->customer_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Supplier / Factory</label>
                        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                            <option value="">— Select Supplier —</option>
                            @foreach($suppliers ?? [] as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $sample->supplier_id ?? '') == $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Product Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">— Select Category —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $sample->category_id ?? '') == $category->id)>
                                    {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="product_name" class="form-control @error('product_name') is-invalid @enderror"
                               placeholder="Product name" value="{{ old('product_name', $sample->product_name ?? '') }}">
                        @error('product_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Article</label>
                        <input type="text" name="article" class="form-control @error('article') is-invalid @enderror"
                               placeholder="Article / style no." value="{{ old('article', $sample->article ?? '') }}">
                        @error('article')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Sample Reference</label>
                        <input type="text" name="sample_reference" class="form-control @error('sample_reference') is-invalid @enderror"
                               placeholder="AWB / BL / Reference no." value="{{ old('sample_reference', $sample->sample_reference ?? '') }}">
                        @error('sample_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Physical Location</label>
                        <input type="text" name="physical_location" class="form-control @error('physical_location') is-invalid @enderror"
                               placeholder="e.g. Rack A-3, Lab Shelf 2" value="{{ old('physical_location', $sample->physical_location ?? '') }}">
                        @error('physical_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Receive Date <span class="text-danger">*</span></label>
                        <input type="date" name="receive_date" class="form-control @error('receive_date') is-invalid @enderror"
                               value="{{ old('receive_date', isset($sample) ? $sample->receive_date?->toDateString() : '') }}">
                        @error('receive_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Additional notes...">{{ old('remarks', $sample->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Color / Size / Quantity Variations --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Color / Size / Quantity Variations</h5>
                    <small class="text-muted">Add one row per color-size combination.</small>
                </div>
                <button type="button" id="addVariationRow" class="btn btn-sm btn-primary">
                    <i class="feather-plus me-1"></i> Add Row
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" id="variationsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th class="wd-120">Quantity <span class="text-danger">*</span></th>
                                <th class="wd-50"></th>
                            </tr>
                        </thead>
                        <tbody id="variationsBody">
                            @php
                                $existingVariations = isset($sample) ? $sample->variations : collect();
                                $varCount = max($existingVariations->count(), 1);
                            @endphp
                            @if($existingVariations->count())
                                @foreach($existingVariations as $vi => $v)
                                <tr class="variation-row">
                                    <td class="var-num">{{ $vi + 1 }}</td>
                                    <td>
                                        <select name="variations[{{ $vi }}][color_id]" class="form-select form-select-sm">
                                            <option value="">— Any —</option>
                                            @foreach($colors as $color)
                                            <option value="{{ $color->id }}" @selected(old("variations.{$vi}.color_id", $v->color_id) == $color->id)>{{ $color->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="variations[{{ $vi }}][size_id]" class="form-select form-select-sm">
                                            <option value="">— Any —</option>
                                            @foreach($sizes as $size)
                                            <option value="{{ $size->id }}" @selected(old("variations.{$vi}.size_id", $v->size_id) == $size->id)>{{ $size->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="variations[{{ $vi }}][quantity]" class="form-control form-control-sm" min="1" value="{{ old("variations.{$vi}.quantity", $v->quantity) }}" required></td>
                                    <td><button type="button" class="btn btn-sm btn-light-brand remove-variation"><i class="feather-trash-2"></i></button></td>
                                </tr>
                                @endforeach
                            @else
                            <tr class="variation-row">
                                <td class="var-num">1</td>
                                <td>
                                    <select name="variations[0][color_id]" class="form-select form-select-sm">
                                        <option value="">— Any —</option>
                                        @foreach($colors as $color)
                                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="variations[0][size_id]" class="form-select form-select-sm">
                                        <option value="">— Any —</option>
                                        @foreach($sizes as $size)
                                        <option value="{{ $size->id }}">{{ $size->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="variations[0][quantity]" class="form-control form-control-sm" min="1" value="1" required></td>
                                <td></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Images Card --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Images</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Main / Cover Image</label>
                        @if(isset($sample) && $sample->main_image)
                            <div class="mb-2">
                                <img src="{{ Storage::url($sample->main_image) }}" alt="Main Image"
                                     class="img-thumbnail" style="max-height:120px;">
                            </div>
                        @endif
                        <input type="file" name="main_image_file" class="form-control @error('main_image_file') is-invalid @enderror"
                               accept="image/*">
                        <small class="text-muted">Featured image for this sample (JPEG, PNG, WebP).</small>
                        @error('main_image_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Gallery Images</label>
                        <input type="file" name="gallery_images[]" class="form-control @error('gallery_images') is-invalid @enderror"
                               accept="image/*" multiple>
                        <small class="text-muted">Select multiple images for the product gallery.</small>
                        @error('gallery_images')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if(isset($sample))
                            @php $gallery = $sample->attachments->where('attachment_type','gallery'); @endphp
                            @if($gallery->count())
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach($gallery as $img)
                                <div class="position-relative">
                                    <img src="{{ Storage::url($img->file_path) }}" alt="{{ $img->title }}"
                                         class="img-thumbnail" style="max-height:80px;">
                                    <a href="{{ route('attachments.destroy', $img->id) }}"
                                       class="position-absolute top-0 end-0 btn btn-xs btn-danger"
                                       data-confirm="Remove this image?"
                                       style="font-size:10px;padding:2px 5px;">&times;</a>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents / Attachments Card --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Attachments / Documents</h5>
                <button type="button" id="addAttachmentRow" class="btn btn-sm btn-primary">
                    <i class="feather-plus me-1"></i> Add File
                </button>
            </div>
            <div class="card-body">
                <div id="attachmentRows">
                    <div class="row attachment-row mb-3">
                        <div class="col-lg-5">
                            <input type="text" name="attachment_titles[0]" class="form-control"
                                   placeholder="Document title / name">
                        </div>
                        <div class="col-lg-6">
                            <input type="file" name="attachments[0]" class="form-control">
                        </div>
                        <div class="col-lg-1 d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-light remove-attachment-row" style="display:none;">
                                <i class="feather-trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @if(isset($sample))
                    @php $docs = $sample->attachments->where('attachment_type','document'); @endphp
                    @if($docs->count())
                    <div class="mt-3">
                        <h6 class="text-muted mb-2">Existing Attachments</h6>
                        @foreach($docs as $doc)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="feather-file text-muted"></i>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank">{{ $doc->title }}</a>
                            <small class="text-muted">{{ $doc->humanFileSize() }}</small>
                        </div>
                        @endforeach
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Settings</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Alert Days</label>
                    <input type="number" min="1" name="alert_days" class="form-control @error('alert_days') is-invalid @enderror"
                           placeholder="7" value="{{ old('alert_days', $sample->alert_days ?? 7) }}">
                    <small class="text-muted">Alert when due in this many days.</small>
                    @error('alert_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @isset($sample->id)
                <div class="alert alert-light border">
                    <small class="text-muted">
                        <strong>Status</strong> is computed automatically from movements:<br>
                        <span class="text-warning fw-semibold">In Testing</span> — has open movement(s)<br>
                        <span class="text-primary fw-semibold">Received</span> — no open movements
                    </small>
                </div>
                @endisset
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let varIdx = {{ isset($sample) ? max($sample->variations->count(), 1) : 1 }};

    const colorsData = @json($colors->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));
    const sizesData  = @json($sizes->map(fn($s) => ['id' => $s->id, 'name' => $s->name]));

    function buildColorSelect(name) {
        let opts = `<option value="">— Any —</option>`;
        colorsData.forEach(c => opts += `<option value="${c.id}">${c.name}</option>`);
        return `<select name="${name}" class="form-select form-select-sm">${opts}</select>`;
    }

    function buildSizeSelect(name) {
        let opts = `<option value="">— Any —</option>`;
        sizesData.forEach(s => opts += `<option value="${s.id}">${s.name}</option>`);
        return `<select name="${name}" class="form-select form-select-sm">${opts}</select>`;
    }

    function renumberVars() {
        document.querySelectorAll('.variation-row .var-num').forEach((td, i) => td.textContent = i + 1);
    }

    document.getElementById('addVariationRow').addEventListener('click', function () {
        const tbody = document.getElementById('variationsBody');
        const tr = document.createElement('tr');
        tr.className = 'variation-row';
        tr.innerHTML = `
            <td class="var-num">${tbody.querySelectorAll('.variation-row').length + 1}</td>
            <td>${buildColorSelect('variations[' + varIdx + '][color_id]')}</td>
            <td>${buildSizeSelect('variations[' + varIdx + '][size_id]')}</td>
            <td><input type="number" name="variations[${varIdx}][quantity]" class="form-control form-control-sm" min="1" value="1" required></td>
            <td><button type="button" class="btn btn-sm btn-light-brand remove-variation"><i class="feather-trash-2"></i></button></td>
        `;
        tbody.appendChild(tr);
        varIdx++;
    });

    document.getElementById('variationsBody').addEventListener('click', function (e) {
        if (e.target.closest('.remove-variation')) {
            const rows = document.querySelectorAll('.variation-row');
            if (rows.length > 1) { e.target.closest('tr').remove(); renumberVars(); }
        }
    });

    let attachIdx = 1;
    document.getElementById('addAttachmentRow')?.addEventListener('click', function () {
        const container = document.getElementById('attachmentRows');
        const row = document.createElement('div');
        row.className = 'row attachment-row mb-3';
        row.innerHTML = `
            <div class="col-lg-5">
                <input type="text" name="attachment_titles[${attachIdx}]" class="form-control" placeholder="Document title / name">
            </div>
            <div class="col-lg-6">
                <input type="file" name="attachments[${attachIdx}]" class="form-control">
            </div>
            <div class="col-lg-1 d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-light remove-attachment-row">
                    <i class="feather-trash-2"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        attachIdx++;
    });

    document.getElementById('attachmentRows')?.addEventListener('click', function (e) {
        if (e.target.closest('.remove-attachment-row')) {
            e.target.closest('.attachment-row').remove();
        }
    });
</script>
@endpush
