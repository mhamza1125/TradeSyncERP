/**
 * Searchable select — progressive enhancement of native <select> elements via Tom Select.
 *
 * Usage:
 *   - Include the `partials.searchable-select` Blade partial on the page (loads CSS + JS once).
 *   - Add `data-searchable` to any <select>. `multiple` selects get removable tags.
 *   - Optional: `data-placeholder="..."` (defaults to the text of the empty-value option).
 *   - For rows added after page load: SearchableSelect.init(container)
 *   - Before removing rows from the DOM:  SearchableSelect.destroy(container)
 *
 * The native <select> stays in the form, so names, submitted values, `selected`
 * options (edit mode / old() input) and validation classes keep working unchanged.
 */
(function () {
    const SELECTOR = 'select[data-searchable]';

    function enhance(select) {
        if (select.tomselect || typeof TomSelect === 'undefined') return;

        const isMultiple = select.multiple;
        const emptyOption = select.querySelector('option[value=""]');

        new TomSelect(select, {
            plugins: isMultiple ? ['remove_button'] : [],
            // Keep the "— Select … —" / "— Any —" option so optional fields can be cleared
            allowEmptyOption: !isMultiple,
            placeholder: select.dataset.placeholder || (emptyOption ? emptyOption.textContent.trim() : 'Search…'),
            maxOptions: null,
            create: false,
            // Render in <body> so dropdowns aren't clipped by .table-responsive / card overflow
            dropdownParent: 'body',
        });
    }

    function scope(root, fn) {
        root = root || document;
        if (root.matches && root.matches(SELECTOR)) fn(root);
        root.querySelectorAll(SELECTOR).forEach(fn);
    }

    window.SearchableSelect = {
        init(root) {
            scope(root, enhance);
        },
        destroy(root) {
            scope(root, (select) => select.tomselect && select.tomselect.destroy());
        },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.SearchableSelect.init());
    } else {
        window.SearchableSelect.init();
    }
})();
