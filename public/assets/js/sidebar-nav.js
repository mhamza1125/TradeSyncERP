/*
 * Sidebar behaviour on top of the theme's own menu code (vendors.min.js / common-init.min.js).
 * docs/DESIGN.md §4. No server calls; preferences live in this browser only (localStorage).
 *
 *  - Group headers (.ts-nav-group-toggle) collapse/expand their links; the choice is remembered.
 *    The group holding the current page always starts expanded.
 *  - Mobile off-canvas menu (#mobile-collapse, theme class .mob-navigation-active):
 *    Escape closes it, focus moves into it when it opens and back to the toggle when it closes,
 *    Tab stays inside it while it is open. Tapping outside is handled by the theme's overlay.
 *  - The sidebar's scroll position is remembered between pages.
 */
(function () {
    'use strict';

    var nav = document.querySelector('.nxl-navigation');
    if (!nav) return;

    // ── 1. Collapsible groups ─────────────────────────────────────────────
    var KEY = 'ts.nav.collapsed';
    var collapsed = [];
    try { collapsed = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { collapsed = []; }

    function setGroup(header, expanded, remember) {
        var key = header.getAttribute('data-nav-group-header');
        var button = header.querySelector('.ts-nav-group-toggle');
        if (button) button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        nav.querySelectorAll('[data-nav-group="' + key + '"]').forEach(function (item) {
            item.classList.toggle('ts-nav-collapsed', !expanded);
        });
        if (remember) {
            collapsed = collapsed.filter(function (k) { return k !== key; });
            if (!expanded) collapsed.push(key);
            try { localStorage.setItem(KEY, JSON.stringify(collapsed)); } catch (e) { /* storage unavailable */ }
        }
    }

    nav.querySelectorAll('[data-nav-group-header]').forEach(function (header) {
        var key = header.getAttribute('data-nav-group-header');
        var isActiveGroup = header.hasAttribute('data-nav-group-active');
        setGroup(header, isActiveGroup || collapsed.indexOf(key) === -1, false);

        var button = header.querySelector('.ts-nav-group-toggle');
        if (button) {
            button.addEventListener('click', function (e) {
                e.stopPropagation(); // the theme binds clicks on <li> items
                setGroup(header, button.getAttribute('aria-expanded') !== 'true', true);
            });
        }
    });

    // ── 2. Mobile off-canvas: Escape, focus management ────────────────────
    var toggle = document.getElementById('mobile-collapse');
    var OPEN = 'mob-navigation-active';

    function focusables() {
        return Array.prototype.filter.call(
            nav.querySelectorAll('a[href], button:not([disabled])'),
            function (el) { return el.offsetParent !== null; }
        );
    }

    if (toggle) {
        toggle.setAttribute('role', 'button');
        toggle.setAttribute('aria-controls', 'app-sidebar');
        toggle.setAttribute('aria-expanded', 'false');
        if (!toggle.getAttribute('aria-label')) toggle.setAttribute('aria-label', 'Open navigation menu');

        new MutationObserver(function () {
            var open = nav.classList.contains(OPEN);
            if (toggle.getAttribute('aria-expanded') === String(open)) return;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close navigation menu' : 'Open navigation menu');
            if (open) {
                var first = focusables()[0];
                if (first) setTimeout(function () { first.focus(); }, 50);
            } else if (nav.contains(document.activeElement)) {
                toggle.focus();
            }
        }).observe(nav, { attributes: true, attributeFilter: ['class'] });
    }

    document.addEventListener('keydown', function (e) {
        if (!nav.classList.contains(OPEN)) return;

        if (e.key === 'Escape') {
            var overlay = nav.querySelector('.nxl-menu-overlay');
            if (overlay) overlay.click(); // the theme's own close handler (also resets the hamburger)
            else nav.classList.remove(OPEN);
            if (toggle) toggle.focus();
            return;
        }

        if (e.key === 'Tab') {
            var items = focusables();
            if (!items.length) return;
            var first = items[0];
            var last = items[items.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });

    // ── 3. Remember the sidebar's scroll position ─────────────────────────
    var SCROLL_KEY = 'sidebar_scroll';
    var wrap = nav.querySelector('.navbar-content');
    if (wrap) {
        var saved = localStorage.getItem(SCROLL_KEY);
        if (saved) wrap.scrollTop = parseInt(saved, 10);
        var tid;
        wrap.addEventListener('scroll', function () {
            clearTimeout(tid);
            tid = setTimeout(function () { localStorage.setItem(SCROLL_KEY, wrap.scrollTop); }, 100);
        }, { passive: true });
    }
})();
