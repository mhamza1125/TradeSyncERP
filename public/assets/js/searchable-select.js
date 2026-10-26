/**
 * Searchable select — makes every <select> in the app searchable via Tom Select.
 *
 * Loaded once from the main layout (after tom-select.complete.min.js, before page scripts).
 *
 *   - Every <select> is enhanced automatically, including ones added later
 *     (rows built with innerHTML, re-rendered tables, etc.).
 *   - Opt out with `data-native` on a <select>.
 *   - Optional `data-placeholder="..."` (defaults to the text of the empty-value option).
 *   - Clicking a single select gives an empty search box: the "— Select … —" option is a
 *     placeholder, and an already-selected value is shown as the placeholder while searching.
 *
 * The native <select> stays in the form and remains the source of truth, so names,
 * submitted values, preselected options, old() input, validation classes, inline
 * onchange handlers and `change` listeners keep working. Page scripts that set
 * `.value`, `.selectedIndex`, `option.selected`, `.disabled`, or rebuild the options
 * are mirrored into the searchable control automatically.
 *
 * Pages may still create their own `new TomSelect(...)` with custom settings; that
 * replaces the automatic instance on the same element.
 */
(function () {
    if (typeof window.TomSelect === 'undefined') return;

    const BaseTomSelect = window.TomSelect;
    const SKIP = '[data-native], .dataTables_length select';

    // ── Clean search: hide the selected item while the dropdown is open ──────────
    BaseTomSelect.define('clean_search', function () {
        const self = this;
        if (self.settings.mode !== 'single') return;

        const showCurrentAsPlaceholder = () => {
            if (!self.wrapper.classList.contains('ts-clean-search')) return;
            const option = self.options[self.getValue()];
            self.control_input.placeholder = option ? String(option[self.settings.labelField] ?? '').trim() : '';
        };

        self.on('dropdown_open', () => {
            if (!self.options[self.getValue()]) return;
            self.wrapper.classList.add('ts-clean-search');
            showCurrentAsPlaceholder();
        });

        self.on('dropdown_close', () => {
            self.wrapper.classList.remove('ts-clean-search');
            self.inputState();
        });

        // Tom Select resets the placeholder on focus / typing; re-apply it while searching
        self.hook('after', 'inputState', showCurrentAsPlaceholder);
    });

    function withCleanSearch(settings) {
        settings = Object.assign({}, settings);
        const plugins = settings.plugins;
        if (Array.isArray(plugins)) {
            settings.plugins = plugins.concat('clean_search');
        } else if (plugins && typeof plugins === 'object') {
            settings.plugins = Object.assign({ clean_search: {} }, plugins);
        } else {
            settings.plugins = ['clean_search'];
        }
        return settings;
    }

    // Tom Select's destroy() restores the options as they were at init; keep the current ones
    function destroyKeepingState(select) {
        const html = select.innerHTML;
        const values = Array.from(select.selectedOptions, (o) => o.value);
        select.tomselect.destroy();
        select.innerHTML = html;
        Array.from(select.options).forEach((o) => { o.selected = values.includes(o.value); });
    }

    // Every `new TomSelect()` gets clean search; page-specific instances replace automatic ones
    class SearchableTomSelect extends BaseTomSelect {
        constructor(input, settings) {
            const el = typeof input === 'string' ? document.querySelector(input) : input;
            if (el && el.tomselect && el.tomselect._autoEnhanced) destroyKeepingState(el);
            super(input, withCleanSearch(settings));
        }

        // Tom Select moves the selected <option> to the end of the native select;
        // for single selects, select it in place so option order never changes.
        updateOriginalInput(opts = {}) {
            if (!this.is_select_tag || this.settings.mode !== 'single') {
                return super.updateOriginalInput(opts);
            }

            const value = this.items.length ? this.items[0] : '';
            let target = Array.from(this.input.options).find((o) => o.value === value);
            if (!target) {
                const data = this.options[value];
                target = new Option(data ? data[this.settings.labelField] ?? value : '', value);
                value === '' ? this.input.prepend(target) : this.input.append(target);
                if (data) data.$option = target;
            }
            target.selected = true;

            if (this.isSetup && !opts.silent) this.trigger('change', this.getValue());
        }
    }
    window.TomSelect = SearchableTomSelect;

    // ── Automatic enhancement ────────────────────────────────────────────────────
    function optionsSignature(select) {
        return Array.from(select.options)
            .filter((o) => o.value !== '')
            .map((o) => o.value + '\u0000' + o.text + '\u0000' + (o.disabled ? 1 : 0))
            .sort()
            .join('\u0001');
    }

    // A cloned row carries a dead copy of the Tom Select markup — strip it first
    function removeStaleMarkup(select) {
        if (!select.classList.contains('tomselected')) return;
        const next = select.nextElementSibling;
        if (next && next.classList.contains('ts-wrapper')) next.remove();
        select.classList.remove('tomselected', 'ts-hidden-accessible');
        select.removeAttribute('tabindex');
    }

    function enhance(select) {
        if (select.tomselect || select.matches(SKIP)) return;
        removeStaleMarkup(select);

        const isMultiple = select.multiple;
        const emptyOption = select.querySelector('option[value=""]');
        const plugins = isMultiple ? ['remove_button'] : [];
        if (!isMultiple && emptyOption && !select.required) plugins.push('clear_button');

        const inlineStyle = select.getAttribute('style');
        const ts = new SearchableTomSelect(select, {
            plugins,
            // The empty option becomes a placeholder, so the search box starts clean
            allowEmptyOption: false,
            placeholder: select.dataset.placeholder || select.getAttribute('placeholder')
                || (emptyOption ? emptyOption.textContent.trim() : null),
            maxOptions: null,
            create: false,
            // Render in <body> so dropdowns aren't clipped by .table-responsive / card overflow
            dropdownParent: 'body',
        });
        ts._autoEnhanced = true;
        ts._signature = optionsSignature(select);
        if (inlineStyle) ts.wrapper.style.cssText += ';' + inlineStyle;
    }

    function scan(root) {
        if (root.nodeType !== 1) return;
        if (root.tagName === 'SELECT') enhance(root);
        else root.querySelectorAll('select').forEach(enhance);
    }

    // ── Keep the searchable control in sync with changes made by page scripts ────
    const pending = new Set();

    function schedule(select) {
        if (!select || !select.tomselect) return;
        if (pending.size === 0) queueMicrotask(flush);
        pending.add(select);
    }

    function flush() {
        const selects = Array.from(pending);
        pending.clear();
        selects.forEach(syncSelect);
    }

    function syncSelect(select) {
        const ts = select.tomselect;
        if (!ts) return;

        if (!select.isConnected) {
            // Removed from the page: drop its dropdown from <body>
            if (ts._autoEnhanced) destroyKeepingState(select);
            return;
        }

        if (ts.isDisabled !== select.disabled) {
            select.disabled ? ts.disable() : ts.enable();
        }

        if (!ts._autoEnhanced) return;

        // Options added / removed / disabled by page code → rebuild from the native select
        if (optionsSignature(select) !== ts._signature) {
            destroyKeepingState(select);
            enhance(select);
            return;
        }

        const nativeValue = select.multiple
            ? Array.from(select.selectedOptions, (o) => o.value)
            : select.value;
        if (JSON.stringify(nativeValue) !== JSON.stringify(ts.getValue())) {
            ts.setValue(nativeValue, true);
        }
    }

    // Programmatic value changes don't fire events — hook the native setters
    function hookSetter(proto, prop, selectOf) {
        const desc = Object.getOwnPropertyDescriptor(proto, prop);
        if (!desc || !desc.set) return;
        Object.defineProperty(proto, prop, {
            configurable: true,
            enumerable: desc.enumerable,
            get: desc.get,
            set(value) {
                desc.set.call(this, value);
                schedule(selectOf(this));
            },
        });
    }
    hookSetter(HTMLSelectElement.prototype, 'value', (el) => el);
    hookSetter(HTMLSelectElement.prototype, 'selectedIndex', (el) => el);
    hookSetter(HTMLOptionElement.prototype, 'selected', (el) => el.closest('select'));

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((m) => {
            const owner = m.target.nodeType === 1
                ? (m.target.tagName === 'SELECT' ? m.target : m.target.closest('select'))
                : null;
            if (owner) schedule(owner);

            if (m.type === 'childList') {
                m.addedNodes.forEach(scan);
                m.removedNodes.forEach((node) => {
                    if (node.nodeType !== 1) return;
                    if (node.tagName === 'SELECT') schedule(node);
                    else node.querySelectorAll('select').forEach(schedule);
                });
            }
        });
    });

    // form.reset() restores native values without firing change events
    document.addEventListener('reset', (e) => {
        setTimeout(() => e.target.querySelectorAll('select').forEach(schedule));
    }, true);

    function start() {
        scan(document.body);
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['disabled'],
        });
    }

    window.SearchableSelect = {
        init(root) { scan(root || document.body); },
        sync(select) { schedule(select); },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
