    <!--! BEGIN: Vendors JS !-->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <!-- vendors.min.js {always must need to be top} -->
    <script src="{{ asset('assets/vendors/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>
    <!--! END: Vendors JS !-->
    <!--! BEGIN: Apps Init !-->
    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>
    <!-- Sidebar groups, mobile menu keyboard/focus handling (docs/DESIGN.md §4) -->
    <script src="{{ asset('assets/js/sidebar-nav.js') }}?v={{ @filemtime(public_path('assets/js/sidebar-nav.js')) }}"></script>
    <!-- Searchable selects: must load before page scripts so `new TomSelect()` gets the app defaults -->
    <script src="{{ asset('assets/vendor/tom-select/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/js/searchable-select.js') }}?v={{ @filemtime(public_path('assets/js/searchable-select.js')) }}"></script>
    @stack('scripts')
    <!--! END: Apps Init !-->
    <!--! BEGIN: Theme Customizer !-->
    <script src="{{ asset('assets/js/theme-customizer-init.min.js') }}"></script>
    <!--! END: Theme Customizer !-->
