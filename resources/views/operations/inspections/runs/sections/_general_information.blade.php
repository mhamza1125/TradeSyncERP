{{-- General Information Section — report header details --}}
@php
    $data  = $runSection->data ?? $runSection->section->default_data ?? [];
    $secId = $runSection->id;
    $v     = fn(string $key) => old("sections.{$secId}.data.{$key}", $data[$key] ?? '');
@endphp

<div class="row g-3 mb-3">
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Buyer / Client Name</label>
        <input type="text" name="sections[{{ $secId }}][data][buyer_name]"
               class="form-control form-control-sm"
               value="{{ $v('buyer_name') }}" placeholder="e.g. Acme Retail Ltd.">
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Factory / Supplier Name</label>
        <input type="text" name="sections[{{ $secId }}][data][factory_name]"
               class="form-control form-control-sm"
               value="{{ $v('factory_name') }}" placeholder="e.g. Sunrise Garments Ltd.">
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">PO / Order Number</label>
        <input type="text" name="sections[{{ $secId }}][data][po_number]"
               class="form-control form-control-sm"
               value="{{ $v('po_number') }}" placeholder="e.g. PO-2026-0456">
    </div>
    <div class="col-lg-3 col-md-6">
        <label class="form-label fw-semibold fs-12">Style / Article No.</label>
        <input type="text" name="sections[{{ $secId }}][data][style_article_no]"
               class="form-control form-control-sm"
               value="{{ $v('style_article_no') }}" placeholder="e.g. ART-1234">
    </div>

    <div class="col-lg-6 col-md-6">
        <label class="form-label fw-semibold fs-12">Product Description</label>
        <input type="text" name="sections[{{ $secId }}][data][product_description]"
               class="form-control form-control-sm"
               value="{{ $v('product_description') }}" placeholder="e.g. Men's cotton crew-neck T-shirt">
    </div>
    <div class="col-lg-2 col-md-6">
        <label class="form-label fw-semibold fs-12">Order Quantity</label>
        <input type="number" name="sections[{{ $secId }}][data][order_quantity]"
               class="form-control form-control-sm"
               value="{{ $v('order_quantity') }}" min="0" placeholder="0">
    </div>
    <div class="col-lg-2 col-md-6">
        <label class="form-label fw-semibold fs-12">Inspection Date</label>
        <input type="date" name="sections[{{ $secId }}][data][inspection_date]"
               class="form-control form-control-sm"
               value="{{ $v('inspection_date') }}">
    </div>
    <div class="col-lg-2 col-md-6">
        <label class="form-label fw-semibold fs-12">Inspector Name</label>
        <input type="text" name="sections[{{ $secId }}][data][inspector_name]"
               class="form-control form-control-sm"
               value="{{ $v('inspector_name') }}" placeholder="e.g. Ali Raza">
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold fs-12">Inspection Location</label>
        <input type="text" name="sections[{{ $secId }}][data][inspection_location]"
               class="form-control form-control-sm"
               value="{{ $v('inspection_location') }}" placeholder="e.g. Factory floor — Lahore, Pakistan">
    </div>
</div>
