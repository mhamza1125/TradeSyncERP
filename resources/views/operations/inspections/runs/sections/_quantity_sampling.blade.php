{{-- Quantity & Sampling Count Section --}}
{{-- Expects: $runSection --}}
@php
    $d    = $runSection->data ?? [];
    $rsId = $runSection->id;
@endphp

<div class="row g-3" data-section-wrapper="{{ $rsId }}">

    <div class="col-md-4">
        <label class="form-label fw-semibold">Product Quantity <span class="text-danger">*</span></label>
        <input type="number"
               name="sections[{{ $rsId }}][data][product_quantity]"
               class="form-control qty-field"
               value="{{ old("sections.{$rsId}.data.product_quantity", $d['product_quantity'] ?? '') }}"
               placeholder="Total production quantity"
               min="0">
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Packed Goods Quantity <span class="text-danger">*</span></label>
        <input type="number"
               name="sections[{{ $rsId }}][data][packed_goods_qty]"
               class="form-control qty-field"
               value="{{ old("sections.{$rsId}.data.packed_goods_qty", $d['packed_goods_qty'] ?? '') }}"
               placeholder="Quantity of packed goods"
               min="0">
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Packed Cartons Quantity <span class="text-danger">*</span></label>
        <input type="number"
               name="sections[{{ $rsId }}][data][packed_cartons_qty]"
               class="form-control qty-field"
               value="{{ old("sections.{$rsId}.data.packed_cartons_qty", $d['packed_cartons_qty'] ?? '') }}"
               placeholder="Number of packed cartons"
               min="0">
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">AQL Inspection Level</label>
        <select name="sections[{{ $rsId }}][data][aql_level]" class="form-select">
            <option value="">— Select Level —</option>
            @foreach(['Level I','Level II','Level III','S1','S2','S3','S4'] as $lvl)
                <option value="{{ $lvl }}"
                        @selected(old("sections.{$rsId}.data.aql_level", $d['aql_level'] ?? '') === $lvl)>
                    {{ $lvl }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">AQL Notes</label>
        <input type="text"
               name="sections[{{ $rsId }}][data][aql_notes]"
               class="form-control"
               value="{{ old("sections.{$rsId}.data.aql_notes", $d['aql_notes'] ?? '') }}"
               placeholder="Optional sampling notes…">
    </div>

</div>

@push('scripts')
<script>
(function () {
    const rsId    = {{ $rsId }};
    const wrapper = document.querySelector('[data-section-wrapper="' + rsId + '"]');
    if (!wrapper) return;

    function checkComplete() {
        const fields   = wrapper.querySelectorAll('.qty-field');
        const allFilled = [...fields].every(f => f.value.trim() !== '');
        const hidden   = document.getElementById('hidden-status-' + rsId);
        const badge    = document.getElementById('status-badge-' + rsId);
        if (hidden) hidden.value = allFilled ? 'complete' : 'pending';
        if (badge) {
            badge.className = allFilled
                ? 'badge bg-soft-success text-success fs-11'
                : 'badge bg-soft-secondary text-secondary fs-11';
            badge.textContent = allFilled ? 'Complete' : 'Pending';
        }
    }

    wrapper.querySelectorAll('.qty-field').forEach(f => f.addEventListener('input', checkComplete));
    checkComplete();
})();
</script>
@endpush
