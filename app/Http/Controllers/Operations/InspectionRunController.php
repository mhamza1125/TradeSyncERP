<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\ReorderAttachmentsRequest;
use App\Http\Requests\Operations\UpdateAttachmentImageRequest;
use App\Models\Attachment;
use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunAql;
use App\Models\InspectionRunDefect;
use App\Models\InspectionRunSection;
use App\Models\InspectionSection;
use App\Models\InspectionTypeSectionDefault;
use App\Models\Sample;
use App\Models\SampleColor;
use App\Models\SampleSize;
use App\Services\Inspection\AqlCalculationService;
use App\Services\Inspection\InspectionDefectCountService;
use App\Services\Inspection\InspectionRunLockService;
use App\Services\Inspection\InspectionSectionPolicyService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\Inspection\InspectionVerdictService;
use App\Http\Requests\Operations\ReopenInspectionRunRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class InspectionRunController extends Controller
{
    /** Column limits for the run save (QC_RULES §7): INT UNSIGNED and SMALLINT UNSIGNED. */
    private const MAX_UINT = 4294967295;

    private const MAX_USMALLINT = 65535;

    /** Allowed values of aql_critical / aql_major / aql_minor in a save. */
    private static function aqlValueOptions(): array
    {
        return [...AqlCalculationService::AQL_LEVELS, 'not_allowed'];
    }

    public function __construct(private AqlCalculationService $aql)
    {
        $this->middleware('permission:inspections.edit');
        $this->middleware('permission:inspections.reopen')->only('reopen');
    }

    // ── Finished runs are read-only (docs/QC_RULES.md) ──────────────────────

    private function lockedRedirect(Inspection $inspection, InspectionRun $run)
    {
        return redirect()->route('inspections.runs.edit', [$inspection, $run])
            ->with('error', 'This run is finished and read-only. Reopen it to make changes.');
    }

    private function lockedJson()
    {
        return response()->json(['success' => false, 'message' => 'This run is finished and read-only. Reopen it to make changes.', 'errors' => []], 422);
    }

    // ── Create: sample + color-variant picker ────────────────────────────────

    public function create(Inspection $inspection)
    {
        $inspection->load('inspectionType');

        $samples = Sample::with('customer', 'category', 'variations.color')
            ->orderBy('sample_code')
            ->get()
            ->flatMap(function ($s) {
                $label = $s->sample_code
                    .($s->product_name ? ' — '.$s->product_name : '')
                    .($s->customer ? ' ('.$s->customer->customer_name.')' : '');

                $colors = $s->variations->pluck('color')->filter()->unique('id')->sortBy('name');

                // No colors recorded for this sample — still allow selecting it,
                // just without a color component (option value has no color id).
                if ($colors->isEmpty()) {
                    return [[
                        'value' => $s->id.':',
                        'text' => $label,
                    ]];
                }

                return $colors->map(fn ($c) => [
                    'value' => $s->id.':'.$c->id,
                    'text' => $label.' — '.$c->name,
                ])->values()->all();
            });

        return view('operations.inspections.runs.create', compact('inspection', 'samples'));
    }

    // ── Store: create run for one sample + color variant, auto-resolve sections ──

    public function store(Request $request, Inspection $inspection)
    {
        $request->validate([
            'sample_id' => ['required', 'string'],
            'review_files' => ['nullable', 'array', 'max:20'],
            'review_files.*' => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx'],
        ]);

        // The picker submits "sampleId:colorId" (colorId empty when the
        // sample has no recorded color variations).
        [$sampleId, $colorId] = array_pad(explode(':', $request->input('sample_id'), 2), 2, null);

        abort_unless(Sample::whereKey($sampleId)->exists(), 422, 'Invalid sample selected.');
        if ($colorId) {
            abort_unless(SampleColor::whereKey($colorId)->exists(), 422, 'Invalid color selected.');
        }

        return DB::transaction(function () use ($sampleId, $colorId, $request, $inspection) {
            $inspection->load('inspectionType');
            // Lock the inspection row so two simultaneous "new run" requests can't share a number.
            Inspection::whereKey($inspection->id)->lockForUpdate()->first();
            $runNumber = $inspection->runs()->max('run_number') + 1;

            $run = $inspection->runs()->create([
                'sample_id' => $sampleId,
                'sample_color_id' => $colorId ?: null,
                'run_number' => $runNumber,
                'verdict' => 'Pending',
            ]);

            $this->resolveRunSections($run, $sampleId, $inspection->inspection_type_id);

            $reviewFiles = $request->file('review_files', []);
            if (! empty($reviewFiles)) {
                $filesToReview = $run->runSections()
                    ->whereHas('section', fn ($q) => $q->where('slug', 'files_to_review'))
                    ->first();

                if ($filesToReview) {
                    foreach ($reviewFiles as $file) {
                        $this->storeSectionAttachment($filesToReview, $file, 'review_files');
                    }
                }
            }

            return redirect()->route('inspections.runs.edit', [$inspection, $run])
                ->with('success', "Run #{$runNumber} created. Complete the sections below.");
        });
    }

    // ── Edit: full modular section form ──────────────────────────────────────

    public function edit(Inspection $inspection, InspectionRun $run)
    {
        $inspection->load('inspectionType');

        $run->load([
            'sample.customer',
            'sample.category',
            'sampleColor',
            'runSections.section',
            'runSections.attachments',
            'runSections.defects.defect',
            'aql.sizeBreakdowns',
        ]);

        $defects = Defect::where('status', true)
            ->orderBy('defect_name')
            ->get();

        $sizeOptions = $run->declaredSizeOptions();

        // Map run sections by slug for view access
        $sectionMap = $run->runSections->mapWithKeys(
            fn ($rs) => [$rs->section->slug => $rs]
        );

        $aqlJsData = $this->aql->tableForJs();
        $colors = SampleColor::orderBy('name')->get();
        $sizes = SampleSize::orderBy('name')->get();

        return view('operations.inspections.runs.edit', compact(
            'inspection', 'run', 'defects', 'sectionMap', 'aqlJsData', 'colors', 'sizes', 'sizeOptions'
        ));
    }

    // ── Update: save all section data in one transaction ─────────────────────

    public function update(Request $request, Inspection $inspection, InspectionRun $run)
    {
        if (InspectionRunLockService::isLocked($run)) {
            return $this->lockedRedirect($inspection, $run);
        }

        $request->validate([
            // AQL (upper bounds = column limits, QC_RULES §7)
            'aql.lot_size' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_UINT],
            'aql.inspection_level' => ['nullable', 'in:I,II,III,S1,S2,S3,S4'],
            'aql.sample_size' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_USMALLINT], // ignored: computed; a new run's placeholder posts 0
            'aql.aql_critical' => ['nullable', Rule::in(self::aqlValueOptions())],
            'aql.aql_major' => ['nullable', Rule::in(self::aqlValueOptions())],
            'aql.aql_minor' => ['nullable', Rule::in(self::aqlValueOptions())],
            'aql.found_critical' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_USMALLINT],
            'aql.found_major' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_USMALLINT],
            'aql.found_minor' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_USMALLINT],
            'aql.variations' => ['nullable', 'array'],
            'aql.variations.*.color' => ['nullable', 'string', 'max:100'],
            'aql.variations.*.size' => ['nullable', 'string', 'max:100'],
            'aql.variations.*.order_qty' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_UINT],
            'aql.variations.*.inspect_qty' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_UINT], // ignored: computed
            'aql.size_breakdown' => ['nullable', 'array'],
            'aql.size_breakdown.*.size_label' => ['nullable', 'string', 'max:50'],
            'aql.size_breakdown.*.order_qty' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_UINT],
            'aql.size_breakdown.*.checked_qty' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_UINT],
            'aql.size_breakdown.*.error_qty' => ['nullable', 'integer', 'min:0', 'max:'.self::MAX_UINT],

            // Dynamic sections
            'sections' => ['nullable', 'array'],
            'sections.*.status' => ['nullable', 'in:pending,complete,na'],
            'sections.*.notes' => ['nullable', 'string', 'max:2000'],
            'sections.*.data' => ['nullable', 'array'],
            'sections.*.defects' => ['nullable', 'array'],
            'sections.*.defects.*.id' => ['nullable', 'integer'],
            'sections.*.defects.*.defect_id' => ['nullable', 'exists:defects,id'],
            'sections.*.defects.*.severity' => ['nullable', 'in:critical,major,minor,functional'],
            'sections.*.defects.*.size' => ['nullable', 'string', 'max:50'],
            'sections.*.defects.*.qty' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_USMALLINT],
            'sections.*.defects.*.carton_no' => ['nullable', 'string', 'max:50'],
            'sections.*.defects.*.status' => ['nullable', 'in:open,rectified,rejected'],
            'sections.*.defects.*.disposition_code' => ['nullable', 'in:MACDF,MACSO,MACDE'],
            'sections.*.defects.*.notes' => ['nullable', 'string', 'max:2000'],
            'finish_run' => ['nullable', 'boolean'],
        ]);

        // Section statuses after this save (submitted value, else what is stored).
        $runSections = $run->runSections()->with('section')->get();
        $submitted = $request->input('sections', []);
        $skipErrors = [];
        $effective = $runSections->map(function ($rs) use ($submitted, &$skipErrors) {
            $status = array_key_exists($rs->id, $submitted) ? ($submitted[$rs->id]['status'] ?? 'pending') : $rs->status;
            if (! InspectionSectionPolicyService::allowsStatus($rs->section, $status)) {
                $skipErrors["sections.{$rs->id}.status"] = "'{$rs->section->name}' cannot be skipped.";
            }

            return ['section' => $rs->section, 'status' => $status];
        });

        if ($skipErrors) {
            throw ValidationException::withMessages($skipErrors);
        }

        $finishRequested = $request->boolean('finish_run');
        $blocking = $finishRequested ? InspectionSectionPolicyService::blockingFinish($effective) : [];
        $finishing = $finishRequested && $blocking === [];

        return DB::transaction(function () use ($request, $inspection, $run, $blocking, $finishing, $finishRequested) {

            // Two posts of the same run (double submit) run one after the other, and the
            // second sees the first's rows and lock (RR-18).
            if (InspectionRunLockService::isLocked(InspectionRun::whereKey($run->id)->lockForUpdate()->first())) {
                return $this->lockedRedirect($inspection, $run);
            }

            $notices = [];

            // ── 2. AQL record ─────────────────────────────────────────────────
            if ($request->filled('aql.lot_size')) {
                $aqlInput = $request->input('aql', []);

                // Recalculate lot_size from variations if present
                $variations = array_values(array_filter($aqlInput['variations'] ?? [], fn ($v) => ! empty($v['order_qty']) || ! empty($v['color']) || ! empty($v['size'])));
                if (! empty($variations)) {
                    $variationsTotal = array_sum(array_map(fn ($v) => (int) ($v['order_qty'] ?? 0), $variations));
                    if ($variationsTotal > self::MAX_UINT) {
                        throw ValidationException::withMessages(['aql.variations' => 'The total ordered quantity of the variations may not be greater than '.number_format(self::MAX_UINT).'.']);
                    }
                    $lotSize = $variationsTotal > 0 ? $variationsTotal : (int) ($aqlInput['lot_size'] ?? 0);
                    $typedLot = (int) ($aqlInput['lot_size'] ?? 0);
                    if ($variationsTotal > 0 && $typedLot !== $variationsTotal) {
                        $notices[] = "Lot size set to {$variationsTotal}, the total ordered quantity of the variations (you entered {$typedLot}).";
                    }
                } else {
                    $lotSize = (int) ($aqlInput['lot_size'] ?? 0);
                    $variations = null;
                }

                $level = $aqlInput['inspection_level'] ?? 'II';

                // Resolve AQL levels — support "not_allowed" string
                $aqlCriticalRaw = $aqlInput['aql_critical'] ?? null;
                $aqlMajorRaw = $aqlInput['aql_major'] ?? null;
                $aqlMinorRaw = $aqlInput['aql_minor'] ?? null;

                $notAllowedCritical = ($aqlCriticalRaw === 'not_allowed');
                $notAllowedMajor = ($aqlMajorRaw === 'not_allowed');
                $notAllowedMinor = ($aqlMinorRaw === 'not_allowed');

                $aqlCritical = $notAllowedCritical ? null : (is_numeric($aqlCriticalRaw) ? (float) $aqlCriticalRaw : null);
                $aqlMajor = $notAllowedMajor ? null : (is_numeric($aqlMajorRaw) ? (float) $aqlMajorRaw : null);
                $aqlMinor = $notAllowedMinor ? null : (is_numeric($aqlMinorRaw) ? (float) $aqlMinorRaw : null);

                $plan = $this->aql->calculate($lotSize, $level, $aqlCritical, $aqlMajor, $aqlMinor);

                // Units to inspect per variation are computed here, never taken from the post (RR-10):
                // the plan's sample size spread over the order quantities (same method as the page).
                if ($variations !== null) {
                    $split = $this->aql->distributeProportionally(
                        (int) $plan['sample_size'],
                        array_map(fn ($v) => (int) ($v['order_qty'] ?? 0), $variations)
                    );
                    foreach ($variations as $i => $v) {
                        $variations[$i]['inspect_qty'] = $split[$i];
                    }
                }

                // "Not Allowed" → Ac=0, Re=1 (any defect fails)
                $acCritical = $notAllowedCritical ? 0 : ($plan['critical']['ac'] ?? null);
                $reCritical = $notAllowedCritical ? 1 : ($plan['critical']['re'] ?? null);
                $acMajor = $notAllowedMajor ? 0 : ($plan['major']['ac'] ?? null);
                $reMajor = $notAllowedMajor ? 1 : ($plan['major']['re'] ?? null);
                $acMinor = $notAllowedMinor ? 0 : ($plan['minor']['ac'] ?? null);
                $reMinor = $notAllowedMinor ? 1 : ($plan['minor']['re'] ?? null);

                $foundCritical = (int) ($aqlInput['found_critical'] ?? 0);
                $foundMajor = (int) ($aqlInput['found_major'] ?? 0);
                $foundMinor = (int) ($aqlInput['found_minor'] ?? 0);

                // Zero defects only becomes Pass once the AQL check is complete
                // (its section is marked complete, or the run is being finished).
                $verdict = $this->aql->verdict(
                    $foundCritical, $foundMajor, $foundMinor,
                    $acCritical, $acMajor, $acMinor,
                    complete: $finishing || $this->aqlSectionComplete($run, $request),
                );

                // Each severity's own plan (an arrow can move it to a larger sample; QC_RULES §1).
                // "Not allowed" uses the base code letter's plan (Ac 0 / Re 1).
                $basePlan = $plan['code_letter']
                    ? ['sample_size' => min($this->aql->sampleSizeFromCode($plan['code_letter']), $lotSize), 'code_letter' => $plan['code_letter']]
                    : null;
                $perSeverity = [
                    'critical' => $notAllowedCritical ? $basePlan : $plan['critical'],
                    'major' => $notAllowedMajor ? $basePlan : $plan['major'],
                    'minor' => $notAllowedMinor ? $basePlan : $plan['minor'],
                ];

                // Store "not_allowed" as a special sentinel float (-1) so it round-trips
                $storeAqlCritical = $notAllowedCritical ? -1.0 : $aqlCritical;
                $storeAqlMajor = $notAllowedMajor ? -1.0 : $aqlMajor;
                $storeAqlMinor = $notAllowedMinor ? -1.0 : $aqlMinor;

                $runAql = InspectionRunAql::updateOrCreate(
                    ['inspection_run_id' => $run->id],
                    [
                        'lot_size' => $lotSize,
                        'inspection_level' => $level,
                        'code_letter' => $plan['code_letter'],
                        'sample_size' => $plan['sample_size'],
                        'aql_critical' => $storeAqlCritical,
                        'aql_major' => $storeAqlMajor,
                        'aql_minor' => $storeAqlMinor,
                        'ac_critical' => $acCritical,
                        're_critical' => $reCritical,
                        'ac_major' => $acMajor,
                        're_major' => $reMajor,
                        'ac_minor' => $acMinor,
                        're_minor' => $reMinor,
                        'found_critical' => $foundCritical,
                        'found_major' => $foundMajor,
                        'found_minor' => $foundMinor,
                        'verdict' => $verdict,
                        'variations' => $variations,
                        'sample_size_critical' => $perSeverity['critical']['sample_size'] ?? null,
                        'code_letter_critical' => $perSeverity['critical']['code_letter'] ?? null,
                        'sample_size_major' => $perSeverity['major']['sample_size'] ?? null,
                        'code_letter_major' => $perSeverity['major']['code_letter'] ?? null,
                        'sample_size_minor' => $perSeverity['minor']['sample_size'] ?? null,
                        'code_letter_minor' => $perSeverity['minor']['code_letter'] ?? null,
                        'standard_ref' => AqlCalculationService::STANDARD_REF,
                    ]
                );

                // ── Per-size checked/error breakdown (additive input detail) ──
                // Does not feed lot_size/sample_size/found_* above — those keep
                // coming from the inputs/variations this section already had.
                $sizeRows = array_values(array_filter(
                    $aqlInput['size_breakdown'] ?? [],
                    fn ($r) => ! empty($r['size_label'])
                ));

                $runAql->sizeBreakdowns()->delete();
                foreach ($sizeRows as $i => $row) {
                    $runAql->sizeBreakdowns()->create([
                        'size_label' => $row['size_label'],
                        'order_qty' => (int) ($row['order_qty'] ?? 0),
                        'checked_qty' => (int) ($row['checked_qty'] ?? 0),
                        'error_qty' => (int) ($row['error_qty'] ?? 0),
                        'sort_order' => $i,
                    ]);
                }
            }

            // ── 3. Dynamic section data ───────────────────────────────────────
            foreach ($request->input('sections', []) as $runSectionId => $sectionData) {
                $runSection = InspectionRunSection::where('id', $runSectionId)
                    ->where('inspection_run_id', $run->id)
                    ->first();

                if (! $runSection) {
                    continue;
                }

                $mergedData = array_merge($runSection->data ?? [], $sectionData['data'] ?? []);

                $runSection->update([
                    'status' => $sectionData['status'] ?? 'pending',
                    'notes' => $sectionData['notes'] ?? null,
                    'data' => $mergedData,
                ]);

                if (array_key_exists('defects', $sectionData)) {
                    $this->syncSectionDefects($runSection, $sectionData['defects'] ?? []);
                }
            }

            // ── 3b. AQL found counts come from recorded defect rows when there are any ──
            $defectSectionKey = collect($request->input('sections', []))
                ->filter(fn ($s) => is_array($s) && array_key_exists('defects', $s))->keys()->first();
            $this->refreshAqlFromDefects(
                $run,
                $finishing || $this->aqlSectionComplete($run, $request),
                $defectSectionKey !== null ? "sections.{$defectSectionKey}.defects" : 'sections',
            );

            // ── 4. Handle finish flag ─────────────────────────────────────────
            // The data above is saved either way; finishing needs every required section done
            // and, when the run has an AQL section, a valid AQL plan (QC_RULES §6, D1/Q28).
            $aqlProblem = $finishRequested ? $this->aqlFinishProblem($run) : null;

            if ($aqlProblem !== null && $finishing) {
                // The AQL verdict above was computed as if the run were finishing; it is not.
                $this->recomputeAqlVerdict($run, $this->aqlSectionComplete($run, $request));
                $finishing = false;
            }

            if ($finishRequested && ! $finishing) {
                $reasons = [];
                if ($blocking !== []) {
                    $reasons[] = 'Complete these sections first: '.implode(', ', $blocking).'.';
                }
                if ($aqlProblem !== null) {
                    $reasons[] = $aqlProblem;
                }

                return redirect()->route('inspections.runs.edit', [$inspection, $run])
                    ->with('error', 'Saved, but the run was not finished. '.implode(' ', [...$reasons, ...$notices]));
            }

            if ($finishing) {
                $run->update([
                    'completed_at' => now(),
                    'verdict' => $this->resolveRunVerdict($run),
                ]);

                $this->syncInspectionStatus($inspection);
            }

            return redirect()->route('inspections.runs.edit', [$inspection, $run])
                ->with('success', implode(' ', ['Run saved successfully.', ...$notices]));
        });
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Inspection $inspection, InspectionRun $run)
    {
        if (InspectionRunLockService::isLocked($run)) {
            return $this->lockedRedirect($inspection, $run);
        }

        DB::transaction(function () use ($run) {
            foreach ($run->runSections as $rs) {
                foreach ($rs->attachments as $att) {
                    $att->deleteFiles();
                }
                $rs->attachments()->delete();
                $rs->delete();
            }

            $run->aql?->delete();
            $run->delete();
        });

        return redirect()->route('inspections.edit', $inspection)
            ->with('success', 'Run deleted.');
    }

    // ── AJAX: upload attachments for a run section ───────────────────────────

    public function uploadAttachment(Request $request, Inspection $inspection, InspectionRun $run, InspectionRunSection $runSection)
    {
        abort_unless($runSection->inspection_run_id === $run->id, 403);
        if (InspectionRunLockService::isLocked($run)) {
            return $this->lockedJson();
        }

        $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx'],
            'task_key' => ['nullable', 'string', 'max:100'],
        ]);

        $taskKey = $request->input('task_key') ?: null;
        $result = [];

        foreach ($request->file('files') as $file) {
            $att = $this->storeSectionAttachment($runSection, $file, $taskKey);

            $result[] = [
                'id' => $att->id,
                'url' => Storage::disk('public')->url($att->file_path),
                'name' => $att->file_name,
                'is_image' => $att->isImage(),
                'delete_url' => route('inspections.runs.attachments.delete', [$inspection, $run, $att]),
                'edit_url' => $att->isImage() ? route('inspections.runs.attachments.edit', [$inspection, $run, $att]) : null,
            ];
        }

        return response()->json(['attachments' => $result]);
    }

    // ── Shared: store an uploaded file as a polymorphic attachment on a run section ──

    private function storeSectionAttachment(InspectionRunSection $runSection, $file, ?string $taskKey): Attachment
    {
        $path = $file->store("inspection-sections/{$runSection->id}", 'public');

        return $runSection->attachments()->create([
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'attachment_type' => str_starts_with($file->getMimeType(), 'image/') ? 'gallery' : 'document',
            'task_key' => $taskKey,
            'uploaded_by' => auth()->id(),
        ]);
    }

    // ── AJAX: delete a run-section attachment ─────────────────────────────────

    public function deleteAttachment(Request $request, Inspection $inspection, InspectionRun $run, Attachment $attachment)
    {
        abort_unless($run->inspection_id === $inspection->id, 404); // route is not scope-bound
        $runSection = $attachment->attachable;
        abort_unless(
            $runSection instanceof InspectionRunSection && $runSection->inspection_run_id === $run->id,
            403
        );
        if (InspectionRunLockService::isLocked($run)) {
            return $this->lockedJson();
        }

        $attachment->deleteFiles();
        $attachment->delete();

        return response()->json(['success' => true]);
    }

    // ── AJAX: persist drag-and-drop reorder of a run-section's attachments ───

    public function reorderAttachments(ReorderAttachmentsRequest $request, Inspection $inspection, InspectionRun $run, InspectionRunSection $runSection)
    {
        abort_unless($runSection->inspection_run_id === $run->id, 403);
        if (InspectionRunLockService::isLocked($run)) {
            return $this->lockedJson();
        }

        $posted = $request->validated('order');

        // Only ever touch attachments that actually belong to this run section —
        // silently drop anything else a tampered request tried to sneak in.
        $ownedIds = $runSection->attachments()->pluck('id')->all();
        $orderedIds = array_values(array_intersect($posted, $ownedIds));

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $attachmentId) {
                Attachment::whereKey($attachmentId)->update(['sort_order' => $index]);
            }
        });

        return response()->json(['success' => true]);
    }

    // ── AJAX: replace an attachment's image with a cropped/rotated/zoomed edit ──

    public function updateAttachmentImage(UpdateAttachmentImageRequest $request, Inspection $inspection, InspectionRun $run, Attachment $attachment)
    {
        abort_unless($run->inspection_id === $inspection->id, 404); // route is not scope-bound
        $runSection = $attachment->attachable;
        abort_unless(
            $runSection instanceof InspectionRunSection && $runSection->inspection_run_id === $run->id,
            403
        );
        if (InspectionRunLockService::isLocked($run)) {
            return $this->lockedJson();
        }
        abort_unless($attachment->isImage(), 422, 'Only images can be edited.');

        $manager = ImageManager::gd();

        try {
            $image = $manager->read($request->file('image')->getRealPath());
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'The edited image could not be processed.'], 422);
        }

        // Re-encoding through Intervention (rather than trusting the browser's
        // blob as-is) guards against non-image payloads smuggled past the mime
        // check, and caps runaway dimensions.
        $image->scaleDown(4000, 4000);
        $encoded = $image->toJpeg(quality: 90);

        $newPath = "inspection-sections/{$runSection->id}/".uniqid('edit_').'.jpg';
        Storage::disk('public')->put($newPath, (string) $encoded);

        $oldPath = $attachment->file_path;
        // Keep the very first file as the original evidence; later edits replace only the edit.
        $originalPath = $attachment->original_file_path ?? $oldPath;

        $attachment->update([
            'original_file_path' => $originalPath,
            'file_path' => $newPath,
            'file_name' => pathinfo($attachment->file_name, PATHINFO_FILENAME).'.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => Storage::disk('public')->size($newPath),
        ]);

        if ($oldPath !== $originalPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return response()->json([
            'success' => true,
            'attachment' => [
                'id' => $attachment->id,
                'url' => Storage::disk('public')->url($attachment->file_path),
                'name' => $attachment->file_name,
            ],
        ]);
    }

    // ── AJAX: save a single section's data + completion status ───────────────

    public function saveSection(Request $request, Inspection $inspection, InspectionRun $run, InspectionRunSection $runSection)
    {
        abort_unless($runSection->inspection_run_id === $run->id, 403);
        if (InspectionRunLockService::isLocked($run)) {
            return $this->lockedJson();
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,complete,na'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'data' => ['nullable', 'array'],
            'defects' => ['nullable', 'array'],
            'defects.*.id' => ['nullable', 'integer'],
            'defects.*.defect_id' => ['nullable', 'exists:defects,id'],
            'defects.*.severity' => ['nullable', 'in:critical,major,minor,functional'],
            'defects.*.size' => ['nullable', 'string', 'max:50'],
            'defects.*.qty' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_USMALLINT],
            'defects.*.carton_no' => ['nullable', 'string', 'max:50'],
            'defects.*.status' => ['nullable', 'in:open,rectified,rejected'],
            'defects.*.disposition_code' => ['nullable', 'in:MACDF,MACSO,MACDE'],
            'defects.*.notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! InspectionSectionPolicyService::allowsStatus($runSection->section, $validated['status'])) {
            throw ValidationException::withMessages(['status' => "'{$runSection->section->name}' cannot be skipped."]);
        }

        $mergedData = array_merge($runSection->data ?? [], $validated['data'] ?? []);

        $defectIds = DB::transaction(function () use ($request, $run, $runSection, $validated, $mergedData) {
            $runSection->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? $runSection->notes,
                'data' => $mergedData,
            ]);

            if (! $request->has('defects')) {
                return null;
            }

            $ids = $this->syncSectionDefects($runSection, $validated['defects'] ?? []);
            $this->refreshAqlFromDefects($run, $this->aqlSectionComplete($run, $request), 'defects');

            return $ids;
        });

        $run->loadMissing('runSections');
        $total = $run->runSections->count();
        $complete = $run->runSections->where('status', 'complete')->count();

        return response()->json([
            'success' => true,
            'message' => 'Saved.',
            'status' => $runSection->status,
            'sections_total' => $total,
            'sections_done' => $complete,
            // Saved defect row ids by posted row index, so the page can post them next time (RR-18).
            'defect_ids' => $defectIds === null ? null : (object) $defectIds,
        ]);
    }

    // ── AJAX: AQL plan calculation ────────────────────────────────────────────

    public function aqlCalculate(Request $request)
    {
        $request->validate([
            'lot_size' => ['required', 'integer', 'min:1'],
            'inspection_level' => ['nullable', 'in:I,II,III,S1,S2,S3,S4'],
            'aql_critical' => ['nullable', 'numeric'],
            'aql_major' => ['nullable', 'numeric'],
            'aql_minor' => ['nullable', 'numeric'],
        ]);

        $plan = $this->aql->calculate(
            (int) $request->lot_size,
            $request->input('inspection_level', 'II'),
            $request->filled('aql_critical') ? (float) $request->aql_critical : 0.065,
            $request->filled('aql_major') ? (float) $request->aql_major : 2.5,
            $request->filled('aql_minor') ? (float) $request->aql_minor : 4.0,
        );

        return response()->json($plan);
    }

    // ── Sync a defects_check section's rows into inspection_run_defects ──────

    /** @return array<int, int> posted row index => saved row id */
    private function syncSectionDefects(InspectionRunSection $runSection, array $rows): array
    {
        $existing = $runSection->defects()->get()->keyBy('id');
        $existingIds = $existing->keys()->all();
        $claimedByPostedId = array_values(array_intersect(
            array_map('intval', array_filter(array_column($rows, 'id'))),
            $existingIds
        ));
        $keptIds = [];
        $idsByIndex = [];

        foreach (array_values($rows) as $i => $row) {
            if (empty($row['defect_id'])) {
                continue;
            }

            $status = $row['status'] ?? 'open';

            $attrs = [
                'inspection_run_section_id' => $runSection->id,
                'defect_id' => $row['defect_id'],
                'severity' => $row['severity'] ?? null,
                'size' => $row['size'] ?? null,
                'qty' => (int) ($row['qty'] ?? 1),
                'carton_no' => $row['carton_no'] ?? null,
                'status' => $status,
                // Disposition code only makes sense once a unit has been rejected.
                'disposition_code' => $status === 'rejected' ? ($row['disposition_code'] ?? null) : null,
                'notes' => $row['notes'] ?? null,
                'sort_order' => $i,
            ];

            $rowId = ! empty($row['id']) && in_array((int) $row['id'], $existingIds, true)
                ? (int) $row['id']
                : null;

            // A repeated or stale form posts its new rows again without ids (double submit,
            // RR-18). Reuse an identical saved row that no posted id claims, instead of
            // deleting it and creating a copy: its photos are keyed by its id.
            if (! $rowId) {
                $rowId = $existing->first(fn (InspectionRunDefect $saved) => ! in_array($saved->id, $claimedByPostedId, true)
                    && ! in_array($saved->id, $keptIds, true)
                    && $this->sameDefectRow($saved, $attrs))?->id;
            }

            if ($rowId) {
                InspectionRunDefect::whereKey($rowId)->update($attrs);
            } else {
                $rowId = InspectionRunDefect::create($attrs)->id;
            }
            $keptIds[] = $rowId;
            $idsByIndex[$i] = $rowId;
        }

        // Remove rows no longer present, cleaning up their photos with them.
        $removed = InspectionRunDefect::where('inspection_run_section_id', $runSection->id)
            ->whereNotIn('id', $keptIds)
            ->get();

        foreach ($removed as $defectRow) {
            $runSection->attachments()->where('task_key', $defectRow->taskKey())->get()->each(function ($att) {
                $att->deleteFiles();
                $att->delete();
            });
            $defectRow->delete();
        }

        return $idsByIndex;
    }

    private function sameDefectRow(InspectionRunDefect $saved, array $attrs): bool
    {
        $norm = fn ($v) => $v === null || $v === '' ? null : (string) $v;

        foreach (['defect_id', 'severity', 'size', 'qty', 'carton_no', 'status', 'disposition_code', 'notes'] as $key) {
            if ($norm($saved->{$key}) !== $norm($attrs[$key])) {
                return false;
            }
        }

        return true;
    }

    // ── AQL found counts = recorded defect rows (one source of truth, QC_RULES §2) ──

    private function refreshAqlFromDefects(InspectionRun $run, bool $complete, string $errorKey = 'sections'): void
    {
        $aql = $run->aql()->first();
        if (! $aql) {
            return;
        }

        $counts = InspectionDefectCountService::countsFor($run);

        // found_* are SMALLINT UNSIGNED: refuse (and roll back) rather than fail with HTTP 500 (RR-16).
        foreach ($counts ?? [] as $class => $total) {
            if ($total > self::MAX_USMALLINT) {
                throw ValidationException::withMessages([$errorKey => "The recorded {$class} defects add up to {$total}; the total per class may not be greater than ".number_format(self::MAX_USMALLINT).'.']);
            }
        }

        if ($counts === null) {
            // Rows were all removed: the counts they produced no longer exist.
            if ($aql->counts_source === 'defect_rows') {
                $counts = ['critical' => 0, 'major' => 0, 'minor' => 0];
                $source = 'manual';
            } else {
                return; // typed counts stay as entered
            }
        }

        $ac = fn ($v) => $v === null ? null : (int) $v;

        $aql->update([
            'found_critical' => $counts['critical'],
            'found_major' => $counts['major'],
            'found_minor' => $counts['minor'],
            'counts_source' => $source ?? 'defect_rows',
            'verdict' => $this->aql->verdict(
                $counts['critical'], $counts['major'], $counts['minor'],
                $ac($aql->ac_critical), $ac($aql->ac_major), $ac($aql->ac_minor),
                complete: $complete,
            ),
        ]);
    }

    // ── Why a run with an AQL section cannot be finished yet (null = it can) ──
    // A skipped AQL section needs no plan. Otherwise: lot size >= 2, a computed plan,
    // and an AQL verdict that is not Pending (QC_RULES §6, owner decision D1/Q28).

    private function aqlFinishProblem(InspectionRun $run): ?string
    {
        $aqlSection = $run->runSections()->with('section')
            ->whereHas('section', fn ($q) => $q->where('section_type', 'aql'))
            ->first();

        if (! $aqlSection || $aqlSection->status === 'na') {
            return null;
        }

        $name = $aqlSection->section->name;
        $aql = $run->aql()->first();

        if (! $aql || (int) $aql->lot_size < 2 || ! $aql->code_letter || (int) $aql->sample_size < 1) {
            return "{$name} has no sampling plan: enter a lot size of at least 2 so the plan can be calculated.";
        }

        if ($aql->verdict === 'Pending') {
            return "{$name} verdict is still Pending: defects were found in a class that has no AQL value. Choose an AQL value (or Not Allowed) for that class.";
        }

        return null;
    }

    // ── Re-evaluate the stored AQL verdict from the stored counts and plan ──

    private function recomputeAqlVerdict(InspectionRun $run, bool $complete): void
    {
        $aql = $run->aql()->first();
        if (! $aql) {
            return;
        }

        $ac = fn ($v) => $v === null ? null : (int) $v;

        $aql->update(['verdict' => $this->aql->verdict(
            (int) $aql->found_critical, (int) $aql->found_major, (int) $aql->found_minor,
            $ac($aql->ac_critical), $ac($aql->ac_major), $ac($aql->ac_minor),
            complete: $complete,
        )]);
    }

    // ── Is the run's AQL section marked complete (in this request, or already)? ──

    private function aqlSectionComplete(InspectionRun $run, Request $request): bool
    {
        $aqlSection = $run->runSections()
            ->whereHas('section', fn ($q) => $q->where('section_type', 'aql'))
            ->first();

        if (! $aqlSection) {
            return false;
        }

        return ($request->input("sections.{$aqlSection->id}.status") ?? $aqlSection->status) === 'complete';
    }

    // ── Determine a run's final verdict from its Final Review section / AQL ───

    private function resolveRunVerdict(InspectionRun $run): string
    {
        $sections = $run->runSections()->with(['section', 'defects'])->get();

        // 1. Explicit verdict chosen by the inspector in Final Review
        $finalReview = $sections->first(fn ($rs) => $rs->section?->slug === 'final_review');
        $selected = InspectionVerdictService::fromFinalReview($finalReview?->data['overall_verdict'] ?? null);

        if ($selected) {
            return $selected;
        }

        // 2. AQL sampling verdict, when the run includes an AQL plan
        $aqlVerdict = $run->aql()->value('verdict');
        if ($aqlVerdict && $aqlVerdict !== 'Pending') {
            return $aqlVerdict;
        }

        // 3. Derive from recorded defects (Critical/Major => Fail, Minor only => Conditional, none => Pass)
        // Only depends on each row's severity — size/carton/status/disposition
        // added alongside the promotion to a real table don't affect this.
        $defectSection = $sections->first(fn ($rs) => in_array($rs->section?->slug, ['defect_recording', 'denim_textile_defects'], true));
        if ($defectSection) {
            return InspectionRunDefect::verdictFromSeverities($defectSection->defects->pluck('severity'));
        }

        return 'Pending';
    }

    // ── Recalculate the parent inspection's overall status from its runs ─────

    private function syncInspectionStatus(Inspection $inspection): void
    {
        $inspection->syncOverallStatus();
    }

    // ── Reopen a finished run: own permission, mandatory reason, logged ─────

    public function reopen(ReopenInspectionRunRequest $request, Inspection $inspection, InspectionRun $run, InspectionRunLockService $locks)
    {
        if (! $run->completed_at) {
            return redirect()->route('inspections.runs.edit', [$inspection, $run])->with('error', 'This run is not finished.');
        }

        DB::transaction(fn () => $locks->reopen($run, $request->validated('reason'), $request->user()));

        return redirect()->route('inspections.runs.edit', [$inspection, $run])
            ->with('success', "Run #{$run->run_number} reopened. Finish it again when the changes are done.");
    }

    // ── Auto-resolve sections ─────────────────────────────────────────────────

    private function resolveRunSections(InspectionRun $run, int $sampleId, ?int $inspectionTypeId): void
    {
        if (! $inspectionTypeId) {
            return;
        }

        $sample = Sample::with('category')->find($sampleId);

        // Load active type-section defaults: global (NULL category) OR matching sample category
        $defaults = InspectionTypeSectionDefault::with('section')
            ->whereHas('section', fn ($q) => $q->where('is_active', true))
            ->where('inspection_type_id', $inspectionTypeId)
            ->where(function ($q) use ($sample) {
                $q->whereNull('category_id');
                if ($sample?->category_id) {
                    $q->orWhere('category_id', $sample->category_id);
                }
            })
            ->orderBy('sort_order')
            ->get();

        // Guard against duplicate sections already on this run
        $existingSectionIds = $run->runSections()->pluck('inspection_section_id')->all();

        foreach ($defaults as $i => $default) {
            if (! $default->section || in_array($default->inspection_section_id, $existingSectionIds)) {
                continue;
            }

            InspectionRunSection::create([
                'inspection_run_id' => $run->id,
                'inspection_section_id' => $default->inspection_section_id,
                'sort_order' => ($i + 1) * 10,
                'data' => $default->section->default_data,
                'status' => 'pending',
            ]);
        }

        // Always ensure final_review is the last section (sort_order=9999)
        $hasFinalReview = $run->runSections()
            ->whereHas('section', fn ($q) => $q->where('slug', 'final_review'))
            ->exists();
        if (! $hasFinalReview) {
            $finalReviewSection = InspectionSection::where('slug', 'final_review')
                ->where('is_active', true)
                ->first();
            if ($finalReviewSection) {
                InspectionRunSection::create([
                    'inspection_run_id' => $run->id,
                    'inspection_section_id' => $finalReviewSection->id,
                    'sort_order' => 9999,
                    'data' => $finalReviewSection->default_data,
                    'status' => 'pending',
                ]);
            }
        }

        // Create AQL record if aql_sampling section is included and none exists yet
        $hasAql = $defaults->contains(fn ($d) => $d->section?->slug === 'aql_sampling');
        if ($hasAql && ! $run->aql()->exists()) {
            InspectionRunAql::create([
                'inspection_run_id' => $run->id,
                'aql_major' => 2.5,
                'aql_minor' => 4.0,
                'aql_critical' => 0.065,
                'inspection_level' => 'II',
            ]);
        }
    }
}
