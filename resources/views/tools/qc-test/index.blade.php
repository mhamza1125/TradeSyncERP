@extends('index')

@section('title', 'QC Image Test Tool - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">QC Image Test Tool</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">QC Image Test Tool</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <button type="submit" form="qcTestForm" id="qcTestSubmit" class="btn btn-primary">
                        <i class="feather-upload me-2"></i><span id="qcTestSubmitLabel">Upload &amp; Analyze</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <div class="alert alert-warning d-flex align-items-start mb-4">
            <i class="feather-alert-triangle me-2 mt-1"></i>
            <div>
                <strong>Standalone test tool.</strong> Validates the blur-score + OCR-confidence approach
                against real product photos before it gets built into the QC/Inspection workflow.
                No pass/fail thresholds are applied yet, and nothing here is stored permanently.
            </div>
        </div>

        <div class="row">
            {{-- ─── Image & options ────────────────────────────────────────── --}}
            <div class="col-xl-4 col-lg-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><i class="feather-image me-2 text-primary"></i>Image &amp; Options</h5>
                    </div>
                    <div class="card-body">

                        @if ($existingPath)
                            <div class="d-flex align-items-center justify-content-between rounded bg-light border px-3 py-2 mb-3 fs-12">
                                <span>Currently loaded: <strong>{{ $originalName ?? $existingPath }}</strong></span>
                                <button type="submit" form="qcTestDestroyForm" class="btn btn-sm btn-link text-danger p-0">Remove</button>
                            </div>
                            {{-- A sibling form (not nested inside the main one) referenced via the
                                 "Remove" button's form="" attribute above — nesting a <form> inside
                                 another silently breaks the outer one in every browser. --}}
                            <form id="qcTestDestroyForm" action="{{ route('tools.qc-test.destroy') }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="existing_path" value="{{ $existingPath }}">
                            </form>
                        @endif

                        <form id="qcTestForm" action="{{ route('tools.qc-test.process') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @if ($existingPath)
                                <input type="hidden" name="existing_path" value="{{ $existingPath }}">
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-semibold fs-12">
                                    Image {{ $existingPath ? '(optional — leave blank to reuse the one above)' : '' }}
                                </label>
                                <input type="file" id="qcTestImageInput" name="image" accept="image/*"
                                       class="form-control @error('image') is-invalid @enderror">
                                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text fs-11">jpg, jpeg, png, bmp, gif, webp — max 10MB.</div>
                                <img id="qcTestLocalPreview" alt="Selected file preview" class="mt-2 rounded border d-none" style="max-height:160px">
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="contains_text" value="1" id="qcTestContainsText"
                                       {{ $containsText ? 'checked' : '' }}>
                                <label class="form-check-label" for="qcTestContainsText">This image contains text/label (run OCR)</label>
                            </div>

                            <div class="mb-1">
                                <label class="form-label fw-semibold fs-12">Tesseract page segmentation mode (PSM)</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="psm" value="6" id="qcTestPsm6"
                                           {{ (int) $psm === 6 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="qcTestPsm6">PSM 6 — Uniform block of text</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="psm" value="11" id="qcTestPsm11"
                                           {{ (int) $psm === 11 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="qcTestPsm11">PSM 11 — Sparse text (find as much text as possible, no particular order)</label>
                                </div>
                            </div>

                            <div class="form-text fs-11 mt-2">
                                Picking a file only shows a local preview — nothing is uploaded until you click
                                "Upload &amp; Analyze" above.
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ─── Results ─────────────────────────────────────────────────── --}}
            <div class="col-xl-8 col-lg-7">
                @if ($results)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-eye me-2 text-primary"></i>Preview</h5>
                        </div>
                        <div class="card-body">
                            @if ($preview)
                                <img src="{{ $preview }}" alt="Uploaded image preview" class="img-fluid rounded border" style="max-height:360px">
                            @else
                                <p class="text-muted mb-0">No preview available.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="feather-zap-off me-2 text-primary"></i>Blur / Sharpness Score</h5>
                            <span class="badge bg-soft-secondary text-secondary">{{ $results['blur_ms'] ?? '—' }} ms</span>
                        </div>
                        <div class="card-body">
                            @if ($results['blur_error'])
                                <div class="alert alert-danger mb-0">{{ $results['blur_error'] }}</div>
                            @else
                                <h2 class="fw-bold mb-1">{{ $results['blur_score'] }}</h2>
                                <p class="text-muted fs-12 mb-0">
                                    Laplacian variance (grayscale, kernel [0,1,0,1,-4,1,0,1,0], std-dev²).
                                    Raw number only — no pass/fail threshold applied yet.
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0"><i class="feather-type me-2 text-primary"></i>OCR Result (PSM {{ $psm }})</h5>
                            @if ($containsText)
                                <span class="badge bg-soft-secondary text-secondary">{{ $results['ocr_ms'] ?? '—' }} ms</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if (! $containsText)
                                <p class="text-muted mb-0">Skipped — "contains text/label" was not checked.</p>
                            @elseif ($results['ocr_error'])
                                <div class="alert alert-danger mb-0">{{ $results['ocr_error'] }}</div>
                            @else
                                @php $ocr = $results['ocr']; @endphp
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="text-muted fs-11">Word count</div>
                                        <div class="fw-semibold fs-16">{{ $ocr['word_count'] }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted fs-11">Avg. word confidence</div>
                                        <div class="fw-semibold fs-16">{{ $ocr['average_confidence'] !== null ? $ocr['average_confidence'].'%' : '—' }}</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-muted fs-11 mb-1">Extracted text</div>
                                    @if ($ocr['text'] !== '')
                                        <p class="bg-light border rounded px-3 py-2 mb-0" style="white-space:pre-wrap; word-break:break-word;">{{ $ocr['text'] }}</p>
                                    @else
                                        <p class="text-muted fst-italic mb-0">No text detected.</p>
                                    @endif
                                </div>

                                @if (count($ocr['words']) > 0)
                                    <div class="text-muted fs-11 mb-1">Per-word confidence</div>
                                    <div class="table-responsive" style="max-height:280px; overflow-y:auto;">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr><th>Word</th><th>Confidence</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ocr['words'] as $word)
                                                    <tr class="{{ $word['confidence'] < 60 ? 'table-danger' : '' }}">
                                                        <td>{{ $word['text'] }}</td>
                                                        <td>{{ $word['confidence'] }}%</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">
                            <i class="feather-image fs-1 d-block mb-2"></i>
                            Pick an image and click "Upload &amp; Analyze" to see results here.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Small, self-contained UX helpers — no framework needed. Two things:
    // 1) instant local preview on file select, so picking a file gives
    //    immediate visual confirmation before the (multi-second) upload runs;
    // 2) disable + relabel the submit button while the request is in flight,
    //    since blur+OCR processing can take a few seconds and a plain button
    //    gives no feedback that anything is happening.
    (function () {
        var form = document.getElementById('qcTestForm');
        var fileInput = document.getElementById('qcTestImageInput');
        var preview = document.getElementById('qcTestLocalPreview');
        var submitBtn = document.getElementById('qcTestSubmit');
        var submitLabel = document.getElementById('qcTestSubmitLabel');

        // Windows often reports an empty/unrecognized MIME type for some
        // extensions (.jfif in particular) via the browser File API, so
        // FileReader's own "data:<mime>;base64,..." prefix can be wrong or
        // blank, leaving the <img> unable to render (shows broken-image alt
        // text instead). Guess the real MIME from the extension ourselves.
        function guessMime(filename) {
            var ext = (filename.split('.').pop() || '').toLowerCase();
            var map = {
                jpg: 'image/jpeg', jpeg: 'image/jpeg', jfif: 'image/jpeg', pjpeg: 'image/jpeg',
                png: 'image/png', gif: 'image/gif', bmp: 'image/bmp', webp: 'image/webp',
            };
            return map[ext] || null;
        }

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                var file = fileInput.files && fileInput.files[0];
                if (!file) {
                    preview.classList.add('d-none');
                    preview.removeAttribute('src');
                    return;
                }
                var reader = new FileReader();
                reader.onload = function (e) {
                    var result = e.target.result; // "data:<mime>;base64,...."
                    var mime = guessMime(file.name) || file.type;
                    if (mime) {
                        var commaIndex = result.indexOf(',');
                        result = 'data:' + mime + ';base64,' + result.substring(commaIndex + 1);
                    }
                    preview.onerror = function () { preview.classList.add('d-none'); };
                    preview.src = result;
                    preview.classList.remove('d-none');
                };
                reader.onerror = function () {
                    preview.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            });
        }

        if (form) {
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitLabel.textContent = 'Processing…';
            });
        }
    })();
</script>
@endpush
