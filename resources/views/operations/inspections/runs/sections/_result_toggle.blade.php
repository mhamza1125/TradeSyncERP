{{-- Reusable Pass/Fail/N/A (or Yes/No/N/A) clickable button-toggle --}}
{{-- Expects: $name, $value, optional $options (value => bootstrap color), $id,
     $rowClasses (value => row class), $labels (value => display text, defaults to the value) --}}
@php
    $options    = $options ?? ['Pass' => 'success', 'Fail' => 'danger', 'N/A' => 'secondary'];
    $rowClasses = $rowClasses ?? ['Pass' => 'table-success', 'Fail' => 'table-danger', 'N/A' => 'table-light text-muted'];
    $labels     = $labels ?? [];
    $uid        = $id ?? ('rt_' . str_replace(['[',']','.'], '_', $name));
@endphp
<div class="d-flex flex-wrap gap-1 result-toggle-group"
     data-row-class-map='@json($rowClasses)'>
    @foreach($options as $opt => $color)
    <label class="result-toggle-label btn btn-sm {{ $value === $opt ? "btn-{$color}" : 'btn-outline-secondary' }}"
           style="font-size:12px;min-width:56px;cursor:pointer"
           data-color="{{ $color }}">
        <input type="radio"
               name="{{ $name }}"
               value="{{ $opt }}"
               class="result-toggle-radio d-none"
               {{ $value === $opt ? 'checked' : '' }}>
        {{ $labels[$opt] ?? $opt }}
    </label>
    @endforeach
</div>

@once
@push('scripts')
<script>
(function () {
    if (window.__resultToggleWired) return;
    window.__resultToggleWired = true;

    document.addEventListener('change', function (e) {
        const radio = e.target.closest('.result-toggle-radio');
        if (!radio) return;

        const group = radio.closest('.result-toggle-group');
        if (group) {
            group.querySelectorAll('.result-toggle-label').forEach(lbl => {
                const r = lbl.querySelector('.result-toggle-radio');
                const color = lbl.dataset.color || 'secondary';
                lbl.classList.toggle(`btn-${color}`, r.checked);
                lbl.classList.toggle('btn-outline-secondary', !r.checked);
            });

            let rowMap = {};
            try { rowMap = JSON.parse(group.dataset.rowClassMap || '{}'); } catch (e) {}

            const row = group.closest('tr, [data-result-row]');
            if (row) {
                Object.values(rowMap).forEach(cls => {
                    cls.split(' ').forEach(c => row.classList.remove(c));
                });
                const cls = rowMap[radio.value];
                if (cls) cls.split(' ').forEach(c => row.classList.add(c));
            }
        }
    });
})();
</script>
@endpush
@endonce
