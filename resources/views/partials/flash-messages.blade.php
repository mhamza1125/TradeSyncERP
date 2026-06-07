@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 flash-auto-dismiss" role="alert">
        <i class="feather-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show mb-4 flash-auto-dismiss" role="alert">
        <i class="feather-info me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="feather-alert-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="feather-alert-triangle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('success') || session('info'))
<script>
(function () {
    var alerts = document.querySelectorAll('.flash-auto-dismiss');
    alerts.forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.6s ease, max-height 0.4s ease 0.4s, margin 0.4s ease 0.4s, padding 0.4s ease 0.4s';
            el.style.opacity = '0';
            el.style.maxHeight = el.offsetHeight + 'px';
            setTimeout(function () {
                el.style.maxHeight = '0';
                el.style.marginBottom = '0';
                el.style.paddingTop = '0';
                el.style.paddingBottom = '0';
                el.style.overflow = 'hidden';
                setTimeout(function () { el.remove(); }, 450);
            }, 600);
        }, 4500);
    });
})();
</script>
@endif
