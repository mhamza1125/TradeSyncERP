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
    'quantity_sampling' => 'info',
    'cartons'           => 'warning',
    'cover_photo'       => 'purple',
    'files_review'      => 'secondary',
    'defects'           => 'danger',
    'finish'            => 'success',
];

$totalSecs    = $run->runSections->count();
$completeSecs = $run->runSections->where('status', 'complete')->count();
$progressPct  = $totalSecs > 0 ? round($completeSecs / $totalSecs * 100) : 0;

$sectionSaveUrls = [];
foreach ($run->runSections as $rs) {
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

        @if($run->runSections->isEmpty())
            <p class="text-muted fs-12 px-2">No sections found.</p>
        @else
            @foreach($run->runSections as $rs)
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
            @if($run->runSections->isEmpty())
            <div class="card mb-4">
                <div class="card-body text-center py-5 text-muted">
                    <i class="feather-layers" style="font-size:2rem;opacity:.3"></i>
                    <p class="mt-2 mb-0">No sections were resolved for this run.</p>
                    <small>Delete this run and recreate it. Sections are auto-applied based on the inspection type and sample category.</small>
                </div>
            </div>
            @else
            <div id="sectionsAccordion">
                @foreach($run->runSections as $loopIdx => $runSection)
                @php
                    $sec     = $runSection->section;
                    $secSlug = $sec->slug;
                    $accId   = 'sec-' . $runSection->id;

                    // Compute task progress for display
                    $taskDefs   = $sec->default_data['tasks'] ?? [];
                    $taskCount  = count($taskDefs);
                    $taskData   = $runSection->data['tasks'] ?? [];
                    $tasksDone  = 0;
                    foreach ($taskDefs as $td) {
                        if (!empty($taskData[$td['key']]['selected'])) $tasksDone++;
                    }

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
                                @if($taskCount > 0)
                                    <span class="badge bg-soft-{{ $color }} text-{{ explode(' ',$color)[0] }} fs-11"
                                          id="task-progress-{{ $runSection->id }}">
                                        {{ $tasksDone }}/{{ $taskCount }} Tasks
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

                            {{-- Section-level status + notes (shown for legacy/checklist types) --}}
                            @if(in_array($sec->section_type, ['workmanship','aql','checklist','container','verification','review','images']))
                            <div class="row g-3 mb-4">
                                <div class="col-auto">
                                    <label class="form-label fw-semibold fs-12">Section Status</label>
                                    <select name="sections[{{ $runSection->id }}][status]"
                                            class="form-select form-select-sm section-status-select"
                                            data-section-id="{{ $runSection->id }}"
                                            style="width:140px">
                                        <option value="pending"  @selected($runSection->status === 'pending')>Pending</option>
                                        <option value="complete" @selected($runSection->status === 'complete')>Complete</option>
                                        <option value="na"       @selected($runSection->status === 'na')>N/A</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label fw-semibold fs-12">Section Notes</label>
                                    <input type="text"
                                           name="sections[{{ $runSection->id }}][notes]"
                                           class="form-control form-control-sm"
                                           value="{{ old("sections.{$runSection->id}.notes", $runSection->notes) }}"
                                           placeholder="Optional notes…">
                                </div>
                            </div>
                            @else
                            {{-- Hidden status/notes for new task-based sections --}}
                            <input type="hidden" name="sections[{{ $runSection->id }}][status]"
                                   class="section-hidden-status" value="{{ $runSection->status }}" id="hidden-status-{{ $runSection->id }}">
                            <input type="hidden" name="sections[{{ $runSection->id }}][notes]" value="{{ $runSection->notes }}">
                            @endif

                            {{-- Section-specific content --}}
                            @switch($secSlug)

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
                                        'uploadUrl'   => $uploadUrl,
                                        'inspection'  => $inspection,
                                        'run'         => $run,
                                    ])
                                @break

                                @case('denim_textile_defects')
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

                                @default
                                    @if($sec->section_type === 'task_list')
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
                                    @elseif($sec->section_type === 'workmanship')
                                        @include('operations.inspections.runs.sections._workmanship', [
                                            'runSection' => $runSection,
                                            'run'        => $run,
                                        ])
                                    @elseif($sec->section_type === 'images')
                                        @include('operations.inspections.runs.sections._product_screening', [
                                            'runSection' => $runSection,
                                        ])
                                    @elseif($sec->section_type === 'container')
                                        @include('operations.inspections.runs.sections._container_details', [
                                            'runSection' => $runSection,
                                        ])
                                    @elseif($sec->section_type === 'verification')
                                        @include('operations.inspections.runs.sections._verification', [
                                            'runSection' => $runSection,
                                        ])
                                    @elseif($sec->section_type === 'review')
                                        @if($secSlug === 'corrective_action')
                                            @include('operations.inspections.runs.sections._corrective_action', ['runSection' => $runSection])
                                        @else
                                            @include('operations.inspections.runs.sections._final_review', ['runSection' => $runSection])
                                        @endif
                                    @elseif($secSlug === 'barcode_testing')
                                        @include('operations.inspections.runs.sections._barcode_testing', ['runSection' => $runSection])
                                    @elseif($secSlug === 'protector_evaluation')
                                        @include('operations.inspections.runs.sections._protector_evaluation', ['runSection' => $runSection])
                                    @else
                                        @include('operations.inspections.runs.sections._checklist', [
                                            'runSection' => $runSection,
                                            'defects'    => $defects,
                                        ])
                                    @endif
                                @break

                            @endswitch

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

        const select = document.querySelector(`.section-status-select[data-section-id="${rsId}"]`);
        if (select) select.value = status;

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
            const select = document.querySelector(`.section-status-select[data-section-id="${rsId}"]`);
            const currentStatus = (select && select.value) || (hidden && hidden.value) || 'pending';
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

    // Status dropdown (legacy section types) now persists immediately too
    document.querySelectorAll('.section-status-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            saveSection(this.dataset.sectionId, this.value, null, 'Status updated.');
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
            div.innerHTML = `<img src="${att.url}" class="rounded border" style="width:64px;height:64px;object-fit:cover" alt="">`;
        } else {
            div.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center border rounded bg-light" style="width:64px;height:64px">
                    <i class="feather-file text-muted" style="font-size:20px"></i>
                    <small class="text-muted mt-1" style="font-size:9px;max-width:60px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${att.name}</small>
                </div>`;
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

    // ═══════════════════════════════════════════════════════════════
    // 2.  TASK RADIO → progress badge + sidebar dot update
    // ═══════════════════════════════════════════════════════════════

    function updateTaskProgress(sectionId) {
        const card    = document.querySelector(`[data-section-id="${sectionId}"]`);
        if (!card) return;

        const taskCards = card.querySelectorAll('[data-task-key]');
        if (!taskCards.length) return;

        let done  = 0;
        const total = taskCards.length;

        taskCards.forEach(tc => {
            const radios = tc.querySelectorAll('.task-radio');
            if ([...radios].some(r => r.checked)) done++;
        });

        const badge = document.getElementById('task-progress-' + sectionId);
        if (badge) badge.textContent = done + '/' + total + ' Tasks';

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

    // Note: section-status-select changes are persisted immediately by the
    // "SUBSECTION-LEVEL SAVE / COMPLETE" handlers above (saveSection → applyStatusEverywhere).

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
    // 5.  AQL calculator
    // ═══════════════════════════════════════════════════════════════

    const calculateBtn = document.getElementById('aql-calculate-btn');
    if (calculateBtn) {
        calculateBtn.addEventListener('click', function () {
            const lotSize = document.getElementById('aql_lot_size')?.value;
            const level   = document.getElementById('aql_inspection_level')?.value;
            const aqlCrit = document.getElementById('aql_aql_critical')?.value;
            const aqlMaj  = document.getElementById('aql_aql_major')?.value;
            const aqlMin  = document.getElementById('aql_aql_minor')?.value;

            if (!lotSize) { alert('Enter lot size first.'); return; }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Calculating…';

            fetch('{{ route("inspections.aql.calculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    lot_size:         parseInt(lotSize),
                    inspection_level: level,
                    aql_critical:     parseFloat(aqlCrit) || 0.065,
                    aql_major:        parseFloat(aqlMaj)  || 2.5,
                    aql_minor:        parseFloat(aqlMin)  || 4.0,
                }),
            })
            .then(r => r.json())
            .then(data => {
                const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
                set('aql_code_letter', data.code_letter);
                set('aql_sample_size', data.sample_size);
                set('aql_ac_critical', data.critical?.ac);
                set('aql_re_critical', data.critical?.re);
                set('aql_ac_major',    data.major?.ac);
                set('aql_re_major',    data.major?.re);
                set('aql_ac_minor',    data.minor?.ac);
                set('aql_re_minor',    data.minor?.re);
                document.getElementById('aql-result-row')?.classList.remove('d-none');
            })
            .catch(() => alert('AQL calculation failed.'))
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="feather-cpu me-1"></i>Calculate';
            });
        });

        ['aql_found_critical','aql_found_major','aql_found_minor'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', updateAqlVerdict);
        });

        function updateAqlVerdict() {
            const foundCrit = parseInt(document.getElementById('aql_found_critical')?.value) || 0;
            const foundMaj  = parseInt(document.getElementById('aql_found_major')?.value)    || 0;
            const foundMin  = parseInt(document.getElementById('aql_found_minor')?.value)    || 0;
            const acCrit    = parseInt(document.getElementById('aql_ac_critical')?.value);
            const acMaj     = parseInt(document.getElementById('aql_ac_major')?.value);
            const acMin     = parseInt(document.getElementById('aql_ac_minor')?.value);
            const verdictEl = document.getElementById('aql_verdict_display');
            if (!verdictEl) return;
            if (foundCrit + foundMaj + foundMin === 0) {
                verdictEl.className = 'badge bg-soft-secondary text-secondary fs-13 px-3 py-2';
                verdictEl.textContent = 'Pending';
                return;
            }
            const fail = (!isNaN(acCrit) && foundCrit > acCrit) ||
                         (!isNaN(acMaj)  && foundMaj  > acMaj)  ||
                         (!isNaN(acMin)  && foundMin  > acMin);
            verdictEl.className = fail ? 'badge bg-soft-danger text-danger fs-13 px-3 py-2'
                                       : 'badge bg-soft-success text-success fs-13 px-3 py-2';
            verdictEl.textContent = fail ? 'FAIL' : 'PASS';
        }
    }

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
