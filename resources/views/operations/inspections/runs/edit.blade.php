@extends('index')

@section('title', 'Run #' . $run->run_number . ' — ' . $inspection->report_number . ' — TradeSyncERP')

@push('styles')
<style>
/* ── Hide global sidebar; inspection sidebar takes its place ─── */
.nxl-navigation {
    visibility: hidden !important;
    pointer-events: none !important;
}

/* ── Inspection-specific sidebar ─────────────────────────────── */
#insp-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: #fff;
    border-right: 1px solid #e2e8f0;
    z-index: 1035;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 12px rgba(0,0,0,.07);
    overflow: hidden;
    transition: width .25s ease;
}

.nxl-container {
    transition: margin-left .25s ease;
}

/* ── Collapsed (mini) state — mirrors the main nav's "minimenu" mode ─ */
#insp-sidebar.is-collapsed {
    width: 100px;
}
#insp-sidebar.is-collapsed .insp-back-label,
#insp-sidebar.is-collapsed .insp-run-info,
#insp-sidebar.is-collapsed .insp-progress-block,
#insp-sidebar.is-collapsed .insp-sections-caption,
#insp-sidebar.is-collapsed .nav-label,
#insp-sidebar.is-collapsed .nav-status,
#insp-sidebar.is-collapsed .insp-sidebar-footer {
    display: none !important;
}
#insp-sidebar.is-collapsed .insp-sidebar-top {
    text-align: center;
    padding: 14px 6px 10px;
}
#insp-sidebar.is-collapsed .insp-nav-item {
    justify-content: center;
    padding: 9px 0;
}
#insp-sidebar .insp-sidebar-top {
    flex-shrink: 0;
    padding: 14px 14px 10px;
    border-bottom: 1px solid #f0f4f8;
}
#insp-sidebar .insp-sidebar-sections {
    flex-grow: 1;
    overflow-y: auto;
    padding: 8px 8px;
}
#insp-sidebar .insp-sidebar-footer {
    flex-shrink: 0;
    padding: 10px 12px;
    border-top: 1px solid #f0f4f8;
}

/* Nav items */
.insp-nav-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 10px;
    border-radius: 7px;
    text-decoration: none;
    color: #495057;
    font-size: 12.5px;
    line-height: 1.3;
    margin-bottom: 2px;
    transition: background .15s, color .15s;
    cursor: pointer;
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
}
.insp-nav-item:hover {
    background: #f0f4ff;
    color: #3d5cdd;
}
.insp-nav-item.is-active {
    background: #eef2ff;
    color: #3d5cdd;
    font-weight: 600;
}
.insp-nav-item .nav-icon {
    flex-shrink: 0;
    width: 16px;
    text-align: center;
    font-size: 13px;
}
.insp-nav-item .nav-label {
    flex-grow: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.insp-nav-item .nav-status {
    flex-shrink: 0;
    font-size: 13px;
}

/* Progress bar */
.insp-progress-bar {
    height: 5px;
    border-radius: 3px;
    background: #e9ecef;
    overflow: hidden;
    margin-top: 6px;
}
.insp-progress-fill {
    height: 100%;
    background: #28a745;
    transition: width .4s ease;
    border-radius: 3px;
}

/* Mobile toggle button */
#insp-sidebar-toggle {
    display: none;
}
#insp-sidebar-overlay {
    display: none;
}

@media (max-width: 767px) {
    #insp-sidebar {
        transform: translateX(-100%);
        transition: transform .28s cubic-bezier(.4,0,.2,1);
        z-index: 1050;
    }
    #insp-sidebar.is-open {
        transform: translateX(0);
    }
    #insp-sidebar-overlay {
        display: block;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.4);
        z-index: 1049;
        opacity: 0;
        pointer-events: none;
        transition: opacity .28s;
    }
    #insp-sidebar-overlay.is-open {
        opacity: 1;
        pointer-events: all;
    }
    #insp-sidebar-toggle {
        display: inline-flex;
    }
    .nxl-content {
        /* Reset margin on mobile since sidebar is hidden */
        margin-left: 0 !important;
    }
}
</style>
@endpush

@section('content')

@php
$typeColors = [
    'images'            => 'purple',
    'workmanship'       => 'primary',
    'aql'               => 'success',
    'checklist'         => 'info',
    'container'         => 'warning',
    'verification'      => 'warning',
    'review'            => 'secondary',
    'task_list'         => 'primary',
    'checkpoint'        => 'primary',
    'production_stages' => 'info',
    'quantity_sampling' => 'info',
    'cartons'           => 'warning',
    'cover_photo'       => 'purple',
    'files_review'      => 'secondary',
    'defects'           => 'danger',
    'finish'            => 'success',
    'article_results'   => 'info',
    'conclusion'        => 'success',
    'general_info'      => 'primary',
];

// Slugs whose own section partial already renders a notes/remarks/comments field —
// the generic header notes field is hidden for these to avoid duplicate note inputs.
$selfNotedSlugs = [
    'product_screening', 'container_details', 'final_review', 'files_to_review',
    'seal_verification', 'shipment_verification', 'overall_article_result',
    'protector_evaluation', 'sample_conformity', 'measurement_check',
    'variations_techpack', 'variations_sample', 'production_status',
    'number_of_cartons_loaded', 'overall_carton_condition', 'carton_dimensions_weight',
];

// Slugs that are no longer rendered as their own card (merged into / replaced
// by another section) — old runs may still carry these rows, so filter them
// out of the sidebar nav and progress counts as well.
$hiddenSlugs = [
    'corrective_action', 'inspection_conclusion', 'finish_inspection',
    'textile_sample_conformity', 'denim_textile_defects',
    'cover_photo', 'workmanship_check',
];

$visibleRunSections = $run->runSections->whereNotIn('section.slug', $hiddenSlugs)->values();

$totalSecs    = $visibleRunSections->count();
$completeSecs = $visibleRunSections->where('status', 'complete')->count();
$progressPct  = $totalSecs > 0 ? round($completeSecs / $totalSecs * 100) : 0;

$sectionSaveUrls = [];
foreach ($visibleRunSections as $rs) {
    $sectionSaveUrls[$rs->id] = route('inspections.runs.sections.save', [$inspection, $run, $rs]);
}
@endphp

{{-- ── Mobile sidebar overlay ─────────────────────────────────────────── --}}
<div id="insp-sidebar-overlay" onclick="closeMobileSidebar()"></div>

{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- INSPECTION SIDEBAR                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div id="insp-sidebar" role="navigation" aria-label="Inspection sections">

    {{-- Top: back link + run info --}}
    <div class="insp-sidebar-top">
        <a href="{{ route('inspections.edit', $inspection) }}"
           class="d-flex align-items-center gap-1 text-muted text-decoration-none mb-3 fs-12"
           style="font-size:11.5px !important">
            <i class="feather-arrow-left" style="font-size:12px"></i>
            <span class="insp-back-label">Back to Inspection</span>
        </a>

        <div class="d-flex align-items-start gap-2 insp-run-info">
            <div class="d-flex align-items-center justify-content-center rounded flex-shrink-0
                        {{ $run->completed_at ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }}"
                 style="width:34px;height:34px">
                <i class="{{ $run->completed_at ? 'feather-check-circle' : 'feather-clipboard' }}" style="font-size:16px"></i>
            </div>
            <div>
                <div class="fw-semibold fs-13 lh-sm">Run #{{ $run->run_number }}</div>
                <div class="text-muted" style="font-size:11px">{{ $inspection->report_number }}</div>
                @if($run->completed_at)
                    <span class="badge bg-soft-success text-success" style="font-size:10px">Finished</span>
                @else
                    <span class="badge bg-soft-warning text-warning" style="font-size:10px">In Progress</span>
                @endif
            </div>
        </div>

        {{-- Progress --}}
        <div class="mt-3 insp-progress-block">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span style="font-size:11px;color:#6c757d">Subsections Completed</span>
                <span style="font-size:11px;font-weight:700;color:#28a745" id="sidebar-progress-text">{{ $completeSecs }}/{{ $totalSecs }} Completed</span>
            </div>
            <div class="insp-progress-bar">
                <div class="insp-progress-fill" id="sidebar-progress-fill" style="width:{{ $progressPct }}%"></div>
            </div>
        </div>
    </div>

    {{-- Section navigation list --}}
    <div class="insp-sidebar-sections">
        <div class="insp-sections-caption" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#adb5bd;padding:4px 10px 6px">
            Sections
        </div>

        @if($visibleRunSections->isEmpty())
            <p class="text-muted fs-12 px-2">No sections found.</p>
        @else
            @foreach($visibleRunSections as $rs)
            @php
                $sec   = $rs->section;
                $sColor = $typeColors[$sec->section_type] ?? 'secondary';
            @endphp
            <button type="button"
                    class="insp-nav-item"
                    data-nav-section="{{ $rs->id }}"
                    onclick="navigateToSection({{ $rs->id }})">
                <span class="nav-icon">
                    <i class="{{ $sec->icon ?? 'feather-layers' }} text-{{ $sColor }}"></i>
                </span>
                <span class="nav-label">{{ $sec->name }}</span>
                <span class="nav-status" id="nav-dot-{{ $rs->id }}">
                    @if($rs->status === 'complete')
                        <i class="feather-check-circle text-success"></i>
                    @elseif($rs->status === 'na')
                        <span class="text-muted" style="font-size:10px;font-weight:600">N/A</span>
                    @else
                        <i class="feather-circle text-muted" style="opacity:.35"></i>
                    @endif
                </span>
            </button>
            @endforeach
        @endif
    </div>

    {{-- Footer: hint --}}
    @if(!$run->completed_at)
    <div class="insp-sidebar-footer">
        <p class="text-muted mb-0" style="font-size:11px;line-height:1.4">
            <i class="feather-info me-1"></i>
            Each subsection saves itself — mark it complete as you finish it.
        </p>
    </div>
    @endif
</div>
{{-- ══════════════════════════════════════════════════════════════════════ --}}

<div class="nxl-content">

    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            {{-- Mobile sidebar toggle --}}
            <button id="insp-sidebar-toggle"
                    type="button"
                    class="btn btn-sm btn-outline-secondary me-2"
                    onclick="toggleMobileSidebar()"
                    title="Toggle inspection navigation">
                <i class="feather-menu"></i>
            </button>

            <div class="page-header-title">
                <h5 class="m-b-10">
                    Run #{{ $run->run_number }}
                    @if($run->completed_at)
                        <span class="badge bg-soft-success text-success fs-11 ms-2">
                            <i class="feather-check-circle me-1"></i>Finished
                        </span>
                    @else
                        <span class="badge bg-soft-warning text-warning fs-11 ms-2">In Progress</span>
                    @endif
                </h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inspections.edit', $inspection) }}">{{ $inspection->report_number }}</a></li>
                <li class="breadcrumb-item">Run #{{ $run->run_number }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inspections.runs.export-pdf', [$inspection, $run]) }}"
                   class="btn btn-outline-secondary"
                   target="_blank"
                   title="Export this run as PDF">
                    <i class="feather-download me-2"></i>Export PDF
                </a>
                <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-light-brand">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="runForm"
              action="{{ route('inspections.runs.update', [$inspection, $run]) }}"
              method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="finish_run" id="finish_run_flag" value="0">

            {{-- ── Run summary card ─────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1">Run Details</h5>
                        <div class="fs-12 text-muted">
                            Type: <strong>{{ $inspection->inspectionType?->name ?? '—' }}</strong>
                            &nbsp;|&nbsp;
                            Report: <strong>{{ $inspection->report_number }}</strong>
                            @if($run->completed_at)
                                &nbsp;|&nbsp;
                                Finished: <strong>{{ $run->completed_at->format('d M Y H:i') }}</strong>
                            @endif
                        </div>
                    </div>
                    {{-- Overall progress pill --}}
                    <div class="text-end">
                        <div class="fs-12 text-muted mb-1">Subsections Completed</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px;min-width:120px">
                                <div class="progress-bar bg-success" id="overall-progress-bar"
                                     style="width:{{ $totalSecs > 0 ? round($completeSecs/$totalSecs*100) : 0 }}%">
                                </div>
                            </div>
                            <span class="badge bg-soft-success text-success fs-12" id="overall-progress-text">
                                {{ $completeSecs }}/{{ $totalSecs }} Completed
                            </span>
                        </div>
                    </div>
                </div>

                @if($run->sample)
                <div class="card-body border-top py-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($run->sample->main_image)
                            <img src="{{ Storage::url($run->sample->main_image) }}"
                                 class="rounded border"
                                 style="width:56px;height:56px;object-fit:cover"
                                 alt="{{ $run->sample->sample_code }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-soft-primary text-primary rounded"
                                 style="width:56px;height:56px;flex-shrink:0">
                                <i class="feather-package fs-4"></i>
                            </div>
                        @endif
                        <div>
                            <div class="fw-semibold fs-14">{{ $run->sample->sample_code }}</div>
                            <div class="text-muted fs-12">{{ $run->sample->product_name }}</div>
                            <div class="d-flex gap-2 mt-1">
                                @if($run->sample->customer)
                                    <span class="badge bg-soft-secondary text-secondary">{{ $run->sample->customer->customer_name }}</span>
                                @endif
                                @if($run->sample->category)
                                    <span class="badge bg-soft-primary text-primary">{{ $run->sample->category->category_name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- ── Sections accordion ───────────────────────────────────── --}}
            @if($visibleRunSections->isEmpty())
            <div class="card mb-4">
                <div class="card-body text-center py-5 text-muted">
                    <i class="feather-layers" style="font-size:2rem;opacity:.3"></i>
                    <p class="mt-2 mb-0">No sections were resolved for this run.</p>
                    <small>Delete this run and recreate it. Sections are auto-applied based on the inspection type and sample category.</small>
                </div>
            </div>
            @else
            <div id="sectionsAccordion">
                @foreach($visibleRunSections as $loopIdx => $runSection)
                @php
                    $sec     = $runSection->section;
                    $secSlug = $sec->slug;
                @endphp

                @php
                    $accId   = 'sec-' . $runSection->id;

                    // --- task-based (checkpoint / task_list sections) ---
                    $taskDefs  = $sec->default_data['tasks'] ?? [];
                    $taskData  = $runSection->data['tasks'] ?? [];
                    $tasksDone = 0;
                    foreach ($taskDefs as $td) {
                        if (!empty($taskData[$td['key']]['selected'])) $tasksDone++;
                    }

                    // --- item-based (checklist / verification sections with items array) ---
                    $itemDefs  = $sec->default_data['items'] ?? [];
                    $itemsData = $runSection->data['items'] ?? [];
                    $itemsDone = collect($itemsData)->filter(fn($cd) => !empty($cd['result']))->count();

                    // --- single-field virtual checkpoints (e.g. overall_carton_condition) ---
                    $virtualCount = in_array($secSlug, ['overall_carton_condition']) ? 1 : 0;
                    $virtualDone  = ($virtualCount > 0 && !empty($runSection->data['overall_condition'] ?? null)) ? 1 : 0;

                    // Unified count used for the header badge
                    $checkpointCount = count($taskDefs) ?: (count($itemDefs) ?: $virtualCount);
                    $checkpointsDone = count($taskDefs) ? $tasksDone : (count($itemDefs) ? $itemsDone : $virtualDone);

                    $color     = $typeColors[$sec->section_type] ?? 'secondary';
                    $startOpen = $loopIdx === 0;

                    $statusColors = ['pending' => 'secondary', 'complete' => 'success', 'na' => 'light text-muted'];
                    $statusLabels = ['pending' => 'Pending', 'complete' => 'Complete', 'na' => 'N/A'];

                    $uploadUrl = route('inspections.runs.sections.upload', [$inspection, $run, $runSection]);
                @endphp

                <div class="card mb-3 border-0 shadow-sm"
                     id="card-{{ $accId }}"
                     data-section-anchor="{{ $runSection->id }}"
                     data-section-name="{{ $sec->name }}">
                    <div class="card-header p-0 border-0">
                        <button class="d-flex align-items-center gap-3 w-100 p-3 bg-transparent border-0 text-start"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#body-{{ $accId }}"
                                aria-expanded="{{ $startOpen ? 'true' : 'false' }}"
                                aria-controls="body-{{ $accId }}">
                            <i class="{{ $sec->icon }} text-{{ $color }}" style="font-size:18px;flex-shrink:0"></i>
                            <div class="flex-grow-1 text-start">
                                <div class="fw-semibold fs-14">{{ $sec->name }}</div>
                                @if($sec->description)
                                    <small class="text-muted fw-normal">{{ $sec->description }}</small>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                @if($checkpointCount > 0)
                                    <span class="badge bg-soft-{{ $color }} text-{{ explode(' ',$color)[0] }} fs-11"
                                          id="task-progress-{{ $runSection->id }}">
                                        {{ $checkpointsDone }}/{{ $checkpointCount }} Checkpoints
                                    </span>
                                @endif
                                <span class="badge bg-soft-{{ $statusColors[$runSection->status] ?? 'secondary' }} text-{{ explode(' ', $statusColors[$runSection->status] ?? 'secondary')[0] }} fs-11"
                                      id="status-badge-{{ $runSection->id }}">
                                    {{ $statusLabels[$runSection->status] ?? 'Pending' }}
                                </span>
                            </div>
                            <i class="feather-chevron-down text-muted ms-2" style="flex-shrink:0"></i>
                        </button>
                    </div>

                    <div id="body-{{ $accId }}"
                         class="collapse {{ $startOpen ? 'show' : '' }}">
                        <div class="card-body border-top">

                            {{-- Status is controlled solely by the "Mark as Complete" action bar below —
                                 no separate Section Status selector (it would duplicate that control). --}}
                            <input type="hidden" name="sections[{{ $runSection->id }}][status]"
                                   class="section-hidden-status" value="{{ $runSection->status }}" id="hidden-status-{{ $runSection->id }}">

                            @if(in_array($secSlug, $selfNotedSlugs))
                                {{-- This section's own partial already provides a notes/remarks field —
                                     keep its value flowing through the same hidden input to avoid a duplicate. --}}
                                <input type="hidden" name="sections[{{ $runSection->id }}][notes]" value="{{ $runSection->notes }}">
                            @endif

                            {{-- Section-specific content --}}
                            @switch($secSlug)

                                @case('carton_dimensions_weight')
                                    @include('operations.inspections.runs.sections._carton_dimensions_weight', [
                                        'runSection' => $runSection,
                                        'uploadUrl'  => $uploadUrl,
                                        'inspection' => $inspection,
                                        'run'        => $run,
                                    ])
                                @break

                                @case('factory_readiness')
                                    @include('operations.inspections.runs.sections._factory_readiness', [
                                        'runSection' => $runSection,
                                    ])
                                @break

                                @case('loading_schedule_and_timing')
                                    @include('operations.inspections.runs.sections._loading_schedule', [
                                        'runSection' => $runSection,
                                    ])
                                @break

                                @case('number_of_cartons_loaded')
                                    @include('operations.inspections.runs.sections._number_of_cartons_loaded', [
                                        'runSection' => $runSection,
                                    ])
                                @break

                                @case('quantity_per_carton')
                                    @include('operations.inspections.runs.sections._quantity_per_carton', [
                                        'runSection' => $runSection,
                                    ])
                                @break

                                @case('overall_carton_condition')
                                    @include('operations.inspections.runs.sections._overall_carton_condition', [
                                        'runSection' => $runSection,
                                        'uploadUrl'  => $uploadUrl,
                                        'inspection' => $inspection,
                                        'run'        => $run,
                                    ])
                                @break

                                @case('quantity_sampling')
                                    @include('operations.inspections.runs.sections._quantity_sampling', [
                                        'runSection' => $runSection,
                                        'uploadUrl'  => $uploadUrl,
                                    ])
                                @break

                                @case('selected_cartons_si')
                                    @include('operations.inspections.runs.sections._selected_cartons', [
                                        'runSection' => $runSection,
                                    ])
                                @break

                                @case('cover_photo')
                                    @include('operations.inspections.runs.sections._cover_photo', [
                                        'runSection'  => $runSection,
                                        'uploadUrl'   => $uploadUrl,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                    ])
                                @break

                                @case('files_to_review')
                                    @include('operations.inspections.runs.sections._files_review', [
                                        'runSection'  => $runSection,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                    ])
                                @break

                                @case('denim_textile_defects')
                                @case('defect_recording')
                                    @include('operations.inspections.runs.sections._defects_check', [
                                        'runSection'  => $runSection,
                                        'defects'     => $defects,
                                        'uploadUrl'   => $uploadUrl,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                    ])
                                @break

                                @case('finish_inspection')
                                    @include('operations.inspections.runs.sections._finish_inspection', [
                                        'runSection'  => $runSection,
                                        'run'         => $run,
                                    ])
                                @break

                                @case('sample_conformity')
                                    @include('operations.inspections.runs.sections._sample_conformity', [
                                        'runSection'  => $runSection,
                                        'uploadUrl'   => $uploadUrl,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                    ])
                                @break

                                @case('measurement_check')
                                    @include('operations.inspections.runs.sections._measurement_check', [
                                        'runSection'  => $runSection,
                                        'uploadUrl'   => $uploadUrl,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                    ])
                                @break

                                @case('variations_techpack')
                                    @include('operations.inspections.runs.sections._variation_techpack', [
                                        'runSection'  => $runSection,
                                        'uploadUrl'   => $uploadUrl,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                    ])
                                @break

                                @case('production_status')
                                    @include('operations.inspections.runs.sections._production_status', [
                                        'runSection'  => $runSection,
                                        'uploadUrl'   => $uploadUrl,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                    ])
                                @break

                                @case('variations_sample')
                                    @include('operations.inspections.runs.sections._variations_sample', [
                                        'runSection'  => $runSection,
                                        'uploadUrl'   => $uploadUrl,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                    ])
                                @break

                                @default
                                    @if($sec->section_type === 'task_list' || $sec->section_type === 'checkpoint')
                                        @include('operations.inspections.runs.sections._task_list', [
                                            'runSection'  => $runSection,
                                            'uploadUrl'   => $uploadUrl,
                                            'inspection'  => $inspection,
                                            'run'         => $run,
                                        ])
                                    @elseif($sec->section_type === 'aql')
                                        @include('operations.inspections.runs.sections._aql_sampling', [
                                            'runSection' => $runSection,
                                            'aql'        => $run->aql,
                                            'aqlJsData'  => $aqlJsData,
                                        ])
                                    @elseif($sec->section_type === 'images')
                                        @include('operations.inspections.runs.sections._product_screening', [
                                            'runSection'  => $runSection,
                                            'uploadUrl'   => $uploadUrl,
                                            'inspection'  => $inspection,
                                            'run'         => $run,
                                        ])
                                    @elseif($sec->section_type === 'container')
                                        @include('operations.inspections.runs.sections._container_details', [
                                            'runSection'  => $runSection,
                                            'uploadUrl'   => $uploadUrl,
                                            'inspection'  => $inspection,
                                            'run'         => $run,
                                        ])
                                    @elseif($sec->section_type === 'verification')
                                        @include('operations.inspections.runs.sections._verification', [
                                            'runSection'  => $runSection,
                                            'uploadUrl'   => $uploadUrl,
                                            'inspection'  => $inspection,
                                            'run'         => $run,
                                        ])
                                    @elseif($sec->section_type === 'review')
                                        @if($secSlug === 'corrective_action')
                                            @include('operations.inspections.runs.sections._corrective_action', ['runSection' => $runSection])
                                        @elseif($secSlug === 'overall_article_result')
                                            @include('operations.inspections.runs.sections._overall_article_result', ['runSection' => $runSection])
                                        @else
                                            @include('operations.inspections.runs.sections._final_review', [
                                                'runSection'  => $runSection,
                                                'sectionMap'  => $sectionMap,
                                                'inspection'  => $inspection,
                                                'run'         => $run,
                                            ])
                                        @endif
                                    @elseif($sec->section_type === 'general_info')
                                        @include('operations.inspections.runs.sections._general_information', ['runSection' => $runSection])
                                    @elseif($secSlug === 'barcode_testing')
                                        @include('operations.inspections.runs.sections._barcode_testing', [
                                            'runSection'  => $runSection,
                                            'uploadUrl'   => $uploadUrl,
                                            'inspection'  => $inspection,
                                            'run'         => $run,
                                        ])
                                    @elseif($secSlug === 'protector_evaluation')
                                        @include('operations.inspections.runs.sections._protector_evaluation', [
                                            'runSection'  => $runSection,
                                            'uploadUrl'   => $uploadUrl,
                                            'inspection'  => $inspection,
                                            'run'         => $run,
                                        ])
                                    @elseif($sec->section_type === 'article_results')
                                        @include('operations.inspections.runs.sections._article_results', ['runSection' => $runSection])
                                    @elseif($sec->section_type === 'conclusion')
                                        @include('operations.inspections.runs.sections._conclusion', ['runSection' => $runSection])
                                    @else
                                        @include('operations.inspections.runs.sections._checklist', [
                                            'runSection' => $runSection,
                                            'uploadUrl'  => $uploadUrl,
                                            'inspection' => $inspection,
                                            'run'        => $run,
                                        ])
                                    @endif
                                @break

                            @endswitch

                            @unless(in_array($secSlug, $selfNotedSlugs))
                            {{-- Generic remarks field at the bottom of every section (skipped for self-noted slugs) --}}
                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <label class="form-label fw-semibold fs-12">Remarks</label>
                                    <textarea name="sections[{{ $runSection->id }}][notes]"
                                              rows="2"
                                              class="form-control form-control-sm"
                                              placeholder="Remarks for this section…">{{ old("sections.{$runSection->id}.notes", $runSection->notes) }}</textarea>
                                </div>
                            </div>
                            @endunless

                            {{-- Subsection completion action bar --}}
                            @unless($run->completed_at)
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
                                <div class="text-muted fs-11" id="save-indicator-{{ $runSection->id }}">&nbsp;</div>
                                <div class="d-flex gap-2">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary subsection-save-btn"
                                            data-section-id="{{ $runSection->id }}">
                                        <i class="feather-save me-1"></i>Save
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm {{ $runSection->status === 'complete' ? 'btn-success' : 'btn-outline-success' }} subsection-complete-btn"
                                            data-section-id="{{ $runSection->id }}"
                                            data-current-status="{{ $runSection->status }}">
                                        <i class="feather-{{ $runSection->status === 'complete' ? 'rotate-ccw' : 'check' }} me-1 subsection-complete-icon"></i>
                                        <span class="subsection-complete-label">{{ $runSection->status === 'complete' ? 'Mark as Pending' : 'Mark as Complete' }}</span>
                                    </button>
                                </div>
                            </div>
                            @endunless

                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- ── Finish Inspection footer (always visible, not a section) ─── --}}
            <div class="card shadow-sm mt-4 border-0">
                <div class="card-body text-center py-4">
                    @if($run->completed_at)
                    <div class="d-flex flex-column align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center bg-soft-success text-success rounded-circle"
                             style="width:72px;height:72px">
                            <i class="feather-check-circle" style="font-size:36px"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-success mb-1">Inspection Finished</h5>
                            <p class="text-muted mb-0 fs-13">
                                Completed on {{ $run->completed_at->format('d M Y \a\t H:i') }}
                            </p>
                        </div>
                        <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-outline-primary mt-1">
                            <i class="feather-arrow-left me-2"></i>Back to Inspection
                        </a>
                    </div>
                    @else
                    <div class="d-flex flex-column align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center bg-soft-secondary text-muted rounded-circle"
                             style="width:72px;height:72px">
                            <i class="feather-flag" style="font-size:36px"></i>
                        </div>
                        <div>
                            <h5 class="fw-semibold mb-1">Ready to Finish?</h5>
                            <p class="text-muted fs-13 mb-3">
                                Review all sections above, then click <strong>Finish Inspection</strong> to close this run.
                            </p>
                        </div>
                        <button type="button" id="finish-inspection-btn" class="btn btn-success btn-lg px-5">
                            <i class="feather-check-circle me-2"></i>Finish Inspection
                        </button>
                        <p class="text-muted fs-12 mb-0 mt-2">Finishing will lock this run and mark it as complete.</p>
                    </div>
                    @endif
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ═══════════════════════════════════════════════════════════════
    // SIDEBAR NAVIGATION
    // ═══════════════════════════════════════════════════════════════

    window.navigateToSection = function (runSectionId) {
        const card = document.querySelector(`[data-section-anchor="${runSectionId}"]`);
        if (!card) return;

        // Find the accordion collapse inside the card
        const collapseEl = card.querySelector('.collapse');
        if (collapseEl && !collapseEl.classList.contains('show')) {
            try {
                bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
            } catch (e) {
                collapseEl.classList.add('show');
            }
        }

        // Small delay so expand animation starts before scroll
        setTimeout(function () {
            const offset = 80; // approximate fixed header height
            const top = card.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top: top, behavior: 'smooth' });
        }, 60);

        // Close mobile sidebar if open
        closeMobileSidebar();
    };

    window.toggleMobileSidebar = function () {
        const sidebar  = document.getElementById('insp-sidebar');
        const overlay  = document.getElementById('insp-sidebar-overlay');
        const isOpen   = sidebar.classList.contains('is-open');
        sidebar.classList.toggle('is-open', !isOpen);
        overlay.classList.toggle('is-open', !isOpen);
    };

    window.closeMobileSidebar = function () {
        document.getElementById('insp-sidebar')?.classList.remove('is-open');
        document.getElementById('insp-sidebar-overlay')?.classList.remove('is-open');
    };

    // ═══════════════════════════════════════════════════════════════
    // KEEP THE INSPECTION SIDEBAR IN SYNC WITH THE MAIN NAV TOGGLE
    // ═══════════════════════════════════════════════════════════════
    // The global header's hamburger / mini-menu buttons toggle classes on
    // <html> (e.g. "minimenu") that the theme uses to resize ".nxl-container".
    // Our sidebar replaces the main nav visually, so it must mirror that
    // collapsed/expanded state itself — otherwise it stays at a fixed width
    // while the content area's offset shifts underneath/behind it.

    const inspSidebar   = document.getElementById('insp-sidebar');
    const inspContainer = document.querySelector('.nxl-container');
    const MOBILE_BREAK  = 767;

    function syncInspLayout() {
        if (!inspSidebar || !inspContainer) return;

        if (window.innerWidth <= MOBILE_BREAK) {
            // Sidebar becomes an off-canvas drawer; container spans full width.
            inspSidebar.classList.remove('is-collapsed');
            inspContainer.style.setProperty('margin-left', '0px', 'important');
            return;
        }

        const collapsed = document.documentElement.classList.contains('minimenu');
        inspSidebar.classList.toggle('is-collapsed', collapsed);
        inspContainer.style.setProperty('margin-left', collapsed ? '100px' : '280px', 'important');
    }

    syncInspLayout();
    window.addEventListener('resize', syncInspLayout);

    // The mini-menu toggle simply adds/removes a class on <html> — observe it
    // so we react no matter which control (or persisted state) changes it.
    new MutationObserver(syncInspLayout)
        .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    // The global hamburger normally slides the main nav in/out on small
    // screens; since that nav is hidden on this page, route it to our own
    // off-canvas sidebar instead so the control still does something useful.
    document.getElementById('mobile-collapse')?.addEventListener('click', function () {
        if (window.innerWidth <= MOBILE_BREAK) toggleMobileSidebar();
    });

    // ── Active section tracking via IntersectionObserver ──────────────────────

    const sectionCards = document.querySelectorAll('[data-section-anchor]');
    if (sectionCards.length && 'IntersectionObserver' in window) {
        const ioOptions = {
            rootMargin: '-15% 0px -70% 0px',
            threshold: 0,
        };
        const io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    setActiveNav(entry.target.dataset.sectionAnchor);
                }
            });
        }, ioOptions);
        sectionCards.forEach(function (el) { io.observe(el); });
    }

    function setActiveNav(sectionId) {
        document.querySelectorAll('.insp-nav-item').forEach(function (btn) {
            btn.classList.remove('is-active');
        });
        const active = document.querySelector(`.insp-nav-item[data-nav-section="${sectionId}"]`);
        if (active) {
            active.classList.add('is-active');
            // Scroll sidebar so the active item is visible
            active.scrollIntoView({ block: 'nearest' });
        }
    }

    // ── Sidebar overall progress update ───────────────────────────────────────

    function updateSidebarProgress() {
        const total    = document.querySelectorAll('[data-section-anchor]').length;
        if (!total) return;

        let complete = 0;

        document.querySelectorAll('[data-section-anchor]').forEach(function (card) {
            const sectionId = card.dataset.sectionAnchor;
            const badge = document.getElementById('status-badge-' + sectionId);
            if (badge && badge.textContent.trim() === 'Complete') complete++;
        });

        const pct = Math.round(complete / total * 100);
        const fill = document.getElementById('sidebar-progress-fill');
        const text = document.getElementById('sidebar-progress-text');
        if (fill) fill.style.width = pct + '%';
        if (text) text.textContent = complete + '/' + total + ' Completed';

        // Also sync main progress bar
        const mainBar  = document.getElementById('overall-progress-bar');
        const mainText = document.getElementById('overall-progress-text');
        if (mainBar) mainBar.style.width = pct + '%';
        if (mainText) mainText.textContent = complete + '/' + total + ' Completed';
    }

    function updateNavDot(sectionId, status) {
        const dot = document.getElementById('nav-dot-' + sectionId);
        if (!dot) return;
        if (status === 'complete') {
            dot.innerHTML = '<i class="feather-check-circle text-success"></i>';
        } else if (status === 'na') {
            dot.innerHTML = '<span class="text-muted" style="font-size:10px;font-weight:600">N/A</span>';
        } else {
            dot.innerHTML = '<i class="feather-circle text-muted" style="opacity:.35"></i>';
        }
        updateSidebarProgress();
    }

    // ═══════════════════════════════════════════════════════════════
    // 0.  SUBSECTION-LEVEL SAVE / COMPLETE  (replaces page-level Save Progress)
    // ═══════════════════════════════════════════════════════════════

    const SECTION_SAVE_URLS = @json($sectionSaveUrls);

    function collectSectionPayload(card, rsId) {
        const dataPrefix = `sections[${rsId}][data]`;
        const notesName  = `sections[${rsId}][notes]`;
        const data = {};
        let notes = null;

        card.querySelectorAll(`[name^="sections[${rsId}]["]`).forEach(function (el) {
            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
            const name = el.name;
            if (name === notesName) { notes = el.value; return; }
            if (!name.startsWith(dataPrefix + '[')) return;

            const rest = name.slice(dataPrefix.length);
            const keys = [...rest.matchAll(/\[([^\]]*)\]/g)].map(m => m[1]);
            let obj = data;
            keys.forEach((key, i) => {
                if (i === keys.length - 1) {
                    obj[key] = el.value;
                } else {
                    if (typeof obj[key] !== 'object' || obj[key] === null) obj[key] = {};
                    obj = obj[key];
                }
            });
        });

        return { data, notes };
    }

    function setIndicator(rsId, msg, isError) {
        const el = document.getElementById('save-indicator-' + rsId);
        if (!el) return;
        el.textContent = msg;
        el.className = 'fs-11 ' + (isError ? 'text-danger' : 'text-success');
        if (msg) {
            setTimeout(function () {
                if (el.textContent === msg) { el.textContent = ''; el.className = 'text-muted fs-11'; }
            }, 3000);
        }
    }

    function applyStatusEverywhere(rsId, status) {
        const labels = { pending: 'Pending', complete: 'Complete', na: 'N/A' };
        const colors = { pending: 'secondary', complete: 'success', na: 'secondary' };
        const cls    = colors[status] || 'secondary';

        const badge = document.getElementById('status-badge-' + rsId);
        if (badge) {
            badge.className = `badge bg-soft-${cls} text-${cls} fs-11`;
            badge.textContent = labels[status] || 'Pending';
        }

        const hidden = document.getElementById('hidden-status-' + rsId);
        if (hidden) hidden.value = status;

        const completeBtn = document.querySelector(`.subsection-complete-btn[data-section-id="${rsId}"]`);
        if (completeBtn) {
            completeBtn.dataset.currentStatus = status;
            const icon  = completeBtn.querySelector('.subsection-complete-icon');
            const label = completeBtn.querySelector('.subsection-complete-label');
            if (status === 'complete') {
                completeBtn.classList.remove('btn-outline-success');
                completeBtn.classList.add('btn-success');
                if (icon)  { icon.classList.remove('feather-check'); icon.classList.add('feather-rotate-ccw'); }
                if (label) label.textContent = 'Mark as Pending';
            } else {
                completeBtn.classList.remove('btn-success');
                completeBtn.classList.add('btn-outline-success');
                if (icon)  { icon.classList.remove('feather-rotate-ccw'); icon.classList.add('feather-check'); }
                if (label) label.textContent = 'Mark as Complete';
            }
        }

        updateNavDot(rsId, status);
    }

    function saveSection(rsId, status, btn, successMsg) {
        const url = SECTION_SAVE_URLS[rsId];
        const card = document.querySelector(`[data-section-anchor="${rsId}"]`);
        if (!url || !card) return;

        const { data, notes } = collectSectionPayload(card, rsId);
        const originalHtml = btn ? btn.innerHTML : null;
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ status: status, notes: notes, data: data }),
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Save failed');
            return res.json();
        })
        .then(function (resp) {
            applyStatusEverywhere(rsId, resp.status);
            setIndicator(rsId, successMsg, false);
        })
        .catch(function () {
            setIndicator(rsId, 'Could not save. Try again.', true);
        })
        .finally(function () {
            if (btn) {
                btn.disabled = false;
                if (originalHtml !== null) btn.innerHTML = originalHtml;
            }
        });
    }

    document.querySelectorAll('.subsection-save-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const rsId   = this.dataset.sectionId;
            const hidden = document.getElementById('hidden-status-' + rsId);
            const currentStatus = (hidden && hidden.value) || 'pending';
            saveSection(rsId, currentStatus, this, 'Saved.');
        });
    });

    document.querySelectorAll('.subsection-complete-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const rsId = this.dataset.sectionId;
            const target = this.dataset.currentStatus === 'complete' ? 'pending' : 'complete';
            saveSection(rsId, target, this, target === 'complete' ? 'Marked as complete.' : 'Reopened — marked as pending.');
        });
    });

    // ═══════════════════════════════════════════════════════════════
    // 1.  AJAX FILE UPLOAD SYSTEM
    // ═══════════════════════════════════════════════════════════════

    function initAttachmentArea(area) {
        const uploadUrl  = area.dataset.uploadUrl;
        if (!uploadUrl) return;

        const taskKey    = area.dataset.taskKey || '';
        const previews   = area.querySelector('.att-previews');
        const addBtn     = area.querySelector('.add-files-btn');
        const fileInput  = area.querySelector('.att-file-input');

        if (!previews || !addBtn || !fileInput) return;

        addBtn.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', async function () {
            const files = [...this.files];
            if (!files.length) return;

            addBtn.disabled = true;
            addBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading…';

            const formData = new FormData();
            files.forEach(f => formData.append('files[]', f));
            if (taskKey) formData.append('task_key', taskKey);

            try {
                const res = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: formData,
                });

                if (!res.ok) throw new Error('Upload failed');
                const { attachments } = await res.json();
                attachments.forEach(att => addPreviewEl(previews, att));
            } catch (e) {
                alert('Upload failed. Please try again.');
            } finally {
                addBtn.disabled = false;
                addBtn.innerHTML = '<i class="feather-plus me-1"></i>Add Files';
                this.value = '';
            }
        });

        wireDeleteButtons(previews);
    }

    function wireDeleteButtons(container) {
        container.querySelectorAll('.att-delete-btn').forEach(btn => {
            if (btn.dataset.wired) return;
            btn.dataset.wired = '1';
            btn.addEventListener('click', function () {
                const url = this.dataset.deleteUrl;
                const el  = document.getElementById(this.dataset.thumbId);
                if (!url || !el) return;
                if (!confirm('Remove this file?')) return;

                fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                }).then(r => {
                    if (r.ok) el.remove();
                    else alert('Could not remove file.');
                }).catch(() => alert('Network error.'));
            });
        });
    }

    function addPreviewEl(container, att) {
        const id  = 'att-' + att.id;
        const div = document.createElement('div');
        div.className = 'att-thumb position-relative d-inline-block me-2 mb-2';
        div.id = id;

        if (att.is_image) {
            div.innerHTML = `<a href="${att.url}" target="_blank" rel="noopener noreferrer"><img src="${att.url}" class="rounded border" style="width:64px;height:64px;object-fit:cover" alt=""></a>`;
        } else {
            div.innerHTML = `
                <a href="${att.url}" target="_blank" rel="noopener noreferrer" class="d-flex flex-column align-items-center justify-content-center border rounded bg-light text-decoration-none" style="width:64px;height:64px">
                    <i class="feather-file text-muted" style="font-size:20px"></i>
                    <small class="text-muted mt-1" style="font-size:9px;max-width:60px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${att.name}</small>
                </a>`;
        }

        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'att-delete-btn btn btn-danger btn-sm p-0 position-absolute top-0 end-0 d-flex align-items-center justify-content-center';
        delBtn.style.cssText = 'width:18px;height:18px;font-size:10px;border-radius:50%;margin:-4px;z-index:1;';
        delBtn.innerHTML = '×';
        delBtn.dataset.deleteUrl = att.delete_url;
        delBtn.dataset.thumbId   = id;
        delBtn.addEventListener('click', function () {
            if (!confirm('Remove this file?')) return;
            fetch(att.delete_url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            }).then(r => {
                if (r.ok) div.remove();
                else alert('Could not remove file.');
            });
        });

        div.appendChild(delBtn);
        container.appendChild(div);
    }

    document.querySelectorAll('.attachment-area').forEach(initAttachmentArea);

    // Exposed so dynamically-inserted attachment areas (e.g. Defects Recording rows
    // added after page load via the searchable picker) can be wired up too.
    window.initAttachmentArea = initAttachmentArea;

    // ═══════════════════════════════════════════════════════════════
    // 2.  TASK RADIO → progress badge + sidebar dot update
    // ═══════════════════════════════════════════════════════════════

    function updateTaskProgress(sectionId) {
        // Look up the section wrapper — this is the element that owns [data-task-key] rows.
        // (NOT [data-section-id], which is on the save/complete buttons.)
        const wrapper = document.querySelector(`[data-section-wrapper="${sectionId}"]`);
        if (!wrapper) return;

        // Use tr[data-task-key] to count only checkpoint rows, not the nested
        // attachment-area divs that also carry data-task-key on the same key.
        const taskRows = wrapper.querySelectorAll('tr[data-task-key]');
        if (!taskRows.length) return;

        let done  = 0;
        const total = taskRows.length;

        taskRows.forEach(tr => {
            const radios = tr.querySelectorAll('.task-radio');
            if ([...radios].some(r => r.checked)) done++;
        });

        const badge = document.getElementById('task-progress-' + sectionId);
        if (badge) badge.textContent = done + '/' + total + ' Checkpoints';

        const newStatus = (done === total && total > 0) ? 'complete' : 'pending';

        const hiddenStatus = document.getElementById('hidden-status-' + sectionId);
        if (hiddenStatus) hiddenStatus.value = newStatus;

        const statusBadge = document.getElementById('status-badge-' + sectionId);
        if (statusBadge) {
            if (newStatus === 'complete') {
                statusBadge.className = 'badge bg-soft-success text-success fs-11';
                statusBadge.textContent = 'Complete';
            } else {
                statusBadge.className = 'badge bg-soft-secondary text-secondary fs-11';
                statusBadge.textContent = 'Pending';
            }
        }

        updateNavDot(sectionId, newStatus);
    }

    document.querySelectorAll('[data-section-wrapper]').forEach(wrapper => {
        const sectionId = wrapper.dataset.sectionWrapper;
        wrapper.querySelectorAll('.task-radio').forEach(radio => {
            radio.addEventListener('change', () => updateTaskProgress(sectionId));
        });
        updateTaskProgress(sectionId);
    });

    // ═══════════════════════════════════════════════════════════════
    // 3.  RESULT-TOGGLE RADIO → progress badge for checklist sections
    //     (checklist / verification / review sections that use items[])
    // ═══════════════════════════════════════════════════════════════

    function updateChecklistProgress(sectionId) {
        const wrapper = document.querySelector(`[data-checklist-wrapper="${sectionId}"]`);
        if (!wrapper) return;

        const rows = wrapper.querySelectorAll('tr[data-result-row]');
        if (!rows.length) return;

        let done = 0;
        const total = rows.length;

        rows.forEach(tr => {
            const radios = tr.querySelectorAll('.result-toggle-radio');
            if ([...radios].some(r => r.checked)) done++;
        });

        const badge = document.getElementById('task-progress-' + sectionId);
        if (badge) badge.textContent = done + '/' + total + ' Checkpoints';
    }

    document.querySelectorAll('[data-checklist-wrapper]').forEach(wrapper => {
        const sectionId = wrapper.dataset.checklistWrapper;
        wrapper.querySelectorAll('.result-toggle-radio').forEach(radio => {
            radio.addEventListener('change', () => updateChecklistProgress(sectionId));
        });
        updateChecklistProgress(sectionId);
    });

    // ═══════════════════════════════════════════════════════════════
    // 4.  Finish inspection — validate incomplete sections first
    // ═══════════════════════════════════════════════════════════════

    document.getElementById('finish-inspection-btn')?.addEventListener('click', function () {
        // Count pending sections
        const pending = [];
        document.querySelectorAll('[data-section-anchor]').forEach(function (card) {
            const sectionId   = card.dataset.sectionAnchor;
            const sectionName = card.dataset.sectionName || ('Section ' + sectionId);
            const badge = document.getElementById('status-badge-' + sectionId);
            if (badge && badge.textContent.trim() !== 'Complete' && badge.textContent.trim() !== 'N/A') {
                pending.push(sectionName);
            }
        });

        if (pending.length > 0) {
            const list = pending.map(n => '  • ' + n).join('\n');
            const msg  = `${pending.length} section(s) are not yet complete:\n\n${list}\n\nFinish anyway?`;
            if (!confirm(msg)) return;
        } else {
            if (!confirm('Mark this inspection run as finished? This cannot be undone.')) return;
        }

        document.getElementById('finish_run_flag').value = '1';
        document.getElementById('runForm').submit();
    });

    // ═══════════════════════════════════════════════════════════════
    // 5.  AQL calculator — handled by inline script in _aql_sampling.blade.php
    // ═══════════════════════════════════════════════════════════════

    // ═══════════════════════════════════════════════════════════════
    // 6.  Defect card toggle
    // ═══════════════════════════════════════════════════════════════

    document.querySelectorAll('.defect-toggle').forEach(cb => {
        cb.addEventListener('change', function () {
            const details = document.getElementById('dd-' + this.dataset.uid);
            if (details) {
                details.classList.toggle('d-none', !this.checked);
            }
            this.closest('.defect-card')?.classList.toggle('border-warning', this.checked);
        });
    });

    // ── Init: activate first section in sidebar ────────────────────────────────
    const firstCard = document.querySelector('[data-section-anchor]');
    if (firstCard) {
        setActiveNav(firstCard.dataset.sectionAnchor);
    }

})();
</script>
@endpush
