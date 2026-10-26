{{--
    Searchable select assets (Tom Select). Include once per page, then add
    `data-searchable` to any <select>. See public/assets/js/searchable-select.js.
--}}
@once
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/tom-select/tom-select.bootstrap5.min.css') }}">
<style>
    /* Dropdown is rendered in <body>; keep it above the sticky header and modals */
    body > .ts-dropdown { z-index: 1060; }
    td .ts-wrapper { min-width: 10rem; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/tom-select/tom-select.complete.min.js') }}"></script>
<script src="{{ asset('assets/js/searchable-select.js') }}"></script>
@endpush
@endonce
