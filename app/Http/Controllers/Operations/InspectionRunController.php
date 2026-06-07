<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunAql;
use App\Models\InspectionRunSection;
use App\Models\Defect;
use App\Models\InspectionSection;
use App\Models\InspectionTypeSectionDefault;
use App\Models\Sample;
use App\Services\Inspection\AqlCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspectionRunController extends Controller
{
    public function __construct(private AqlCalculationService $aql)
    {
        $this->middleware('permission:inspections.edit');
    }

    // ── Create: sample picker ────────────────────────────────────────────────

    public function create(Inspection $inspection)
    {
        $inspection->load('inspectionType');

        $samples = Sample::with('customer', 'category')
            ->orderBy('sample_code')
            ->get()
            ->map(fn($s) => [
                'id'   => $s->id,
                'text' => $s->sample_code
                    . ($s->product_name ? ' — ' . $s->product_name : '')
                    . ($s->customer ? ' (' . $s->customer->customer_name . ')' : ''),
            ]);

        return view('operations.inspections.runs.create', compact('inspection', 'samples'));
    }

    // ── Store: create run with single sample, auto-resolve sections ──────────

    public function store(Request $request, Inspection $inspection)
    {
        $request->validate([
            'sample_id' => ['required', 'exists:samples,id'],
        ]);

        return DB::transaction(function () use ($request, $inspection) {
            $inspection->load('inspectionType');
            $runNumber = $inspection->runs()->max('run_number') + 1;

            $run = $inspection->runs()->create([
                'sample_id'  => $request->sample_id,
                'run_number' => $runNumber,
                'verdict'    => 'Pending',
            ]);

            $this->resolveRunSections($run, $request->sample_id, $inspection->inspection_type_id);

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
            'runSections.section',
            'runSections.attachments',
            'aql',
        ]);

        $defects = Defect::where('status', true)
            ->with('category')
            ->orderBy('defect_name')
            ->get();

        // Map run sections by slug for view access
        $sectionMap = $run->runSections->mapWithKeys(
            fn($rs) => [$rs->section->slug => $rs]
        );

        $aqlJsData = $this->aql->tableForJs();

        return view('operations.inspections.runs.edit', compact(
            'inspection', 'run', 'defects', 'sectionMap', 'aqlJsData'
        ));
    }

    // ── Update: save all section data in one transaction ─────────────────────

    public function update(Request $request, Inspection $inspection, InspectionRun $run)
    {
        $request->validate([
            // AQL
            'aql.lot_size'                       => ['nullable', 'integer', 'min:1'],
            'aql.inspection_level'               => ['nullable', 'in:I,II,III,S1,S2,S3,S4'],
            'aql.sample_size'                    => ['nullable', 'integer', 'min:1'],
            'aql.aql_critical'                   => ['nullable', 'numeric'],
            'aql.aql_major'                      => ['nullable', 'numeric'],
            'aql.aql_minor'                      => ['nullable', 'numeric'],
            'aql.found_critical'                 => ['nullable', 'integer', 'min:0'],
            'aql.found_major'                    => ['nullable', 'integer', 'min:0'],
            'aql.found_minor'                    => ['nullable', 'integer', 'min:0'],
            'aql.notes'                          => ['nullable', 'string', 'max:2000'],

            // Dynamic sections
            'sections'          => ['nullable', 'array'],
            'sections.*.status' => ['nullable', 'in:pending,complete,na'],
            'sections.*.notes'  => ['nullable', 'string', 'max:2000'],
            'sections.*.data'   => ['nullable', 'array'],
            'finish_run'        => ['nullable', 'boolean'],
        ]);

        return DB::transaction(function () use ($request, $inspection, $run) {

            // ── 2. AQL record ─────────────────────────────────────────────────
            if ($request->filled('aql.lot_size')) {
                $aqlInput = $request->input('aql', []);

                $lotSize     = (int) ($aqlInput['lot_size'] ?? 0);
                $level       = $aqlInput['inspection_level'] ?? 'II';
                $aqlCritical = isset($aqlInput['aql_critical']) ? (float) $aqlInput['aql_critical'] : null;
                $aqlMajor    = isset($aqlInput['aql_major'])    ? (float) $aqlInput['aql_major']    : null;
                $aqlMinor    = isset($aqlInput['aql_minor'])    ? (float) $aqlInput['aql_minor']    : null;

                $plan = $this->aql->calculate($lotSize, $level, $aqlCritical, $aqlMajor, $aqlMinor);

                $foundCritical = (int) ($aqlInput['found_critical'] ?? 0);
                $foundMajor    = (int) ($aqlInput['found_major']    ?? 0);
                $foundMinor    = (int) ($aqlInput['found_minor']    ?? 0);

                $verdict = $this->aql->verdict(
                    $foundCritical, $foundMajor, $foundMinor,
                    $plan['critical']['ac'] ?? null,
                    $plan['major']['ac']    ?? null,
                    $plan['minor']['ac']    ?? null,
                );

                InspectionRunAql::updateOrCreate(
                    ['inspection_run_id' => $run->id],
                    [
                        'lot_size'         => $lotSize,
                        'inspection_level' => $level,
                        'code_letter'      => $plan['code_letter'],
                        'sample_size'      => $plan['sample_size'],
                        'aql_critical'     => $aqlCritical,
                        'aql_major'        => $aqlMajor,
                        'aql_minor'        => $aqlMinor,
                        'ac_critical'      => $plan['critical']['ac'] ?? null,
                        're_critical'      => $plan['critical']['re'] ?? null,
                        'ac_major'         => $plan['major']['ac']    ?? null,
                        're_major'         => $plan['major']['re']    ?? null,
                        'ac_minor'         => $plan['minor']['ac']    ?? null,
                        're_minor'         => $plan['minor']['re']    ?? null,
                        'found_critical'   => $foundCritical,
                        'found_major'      => $foundMajor,
                        'found_minor'      => $foundMinor,
                        'verdict'          => $verdict,
                        'notes'            => $aqlInput['notes'] ?? null,
                    ]
                );
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
                    'notes'  => $sectionData['notes']  ?? null,
                    'data'   => $mergedData,
                ]);
            }

            // ── 4. Handle finish flag ─────────────────────────────────────────
            if ($request->boolean('finish_run')) {
                $run->update(['completed_at' => now()]);
            }

            return redirect()->route('inspections.runs.edit', [$inspection, $run])
                ->with('success', 'Run saved successfully.');
        });
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Inspection $inspection, InspectionRun $run)
    {
        DB::transaction(function () use ($run) {
            foreach ($run->runSections as $rs) {
                foreach ($rs->attachments as $att) {
                    Storage::disk('public')->delete($att->file_path);
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

        $request->validate([
            'files'    => ['required', 'array', 'max:20'],
            'files.*'  => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx'],
            'task_key' => ['nullable', 'string', 'max:100'],
        ]);

        $taskKey = $request->input('task_key') ?: null;
        $result  = [];

        foreach ($request->file('files') as $file) {
            $path = $file->store("inspection-sections/{$runSection->id}", 'public');
            $att  = $runSection->attachments()->create([
                'title'           => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_name'       => $file->getClientOriginalName(),
                'file_path'       => $path,
                'mime_type'       => $file->getMimeType(),
                'file_size'       => $file->getSize(),
                'attachment_type' => str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'document',
                'task_key'        => $taskKey,
                'uploaded_by'     => auth()->id(),
            ]);

            $result[] = [
                'id'         => $att->id,
                'url'        => Storage::disk('public')->url($att->file_path),
                'name'       => $att->file_name,
                'is_image'   => $att->isImage(),
                'delete_url' => route('inspections.runs.attachments.delete', [$inspection, $run, $att]),
            ];
        }

        return response()->json(['attachments' => $result]);
    }

    // ── AJAX: delete a run-section attachment ─────────────────────────────────

    public function deleteAttachment(Request $request, Inspection $inspection, InspectionRun $run, Attachment $attachment)
    {
        $runSection = $attachment->attachable;
        abort_unless(
            $runSection instanceof InspectionRunSection && $runSection->inspection_run_id === $run->id,
            403
        );

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(['success' => true]);
    }

    // ── AJAX: save a single section's data + completion status ───────────────

    public function saveSection(Request $request, Inspection $inspection, InspectionRun $run, InspectionRunSection $runSection)
    {
        abort_unless($runSection->inspection_run_id === $run->id, 403);
        abort_if($run->completed_at, 422, 'This run is already finished.');

        $validated = $request->validate([
            'status' => ['required', 'in:pending,complete,na'],
            'notes'  => ['nullable', 'string', 'max:2000'],
            'data'   => ['nullable', 'array'],
        ]);

        $mergedData = array_merge($runSection->data ?? [], $validated['data'] ?? []);

        $runSection->update([
            'status' => $validated['status'],
            'notes'  => $validated['notes'] ?? $runSection->notes,
            'data'   => $mergedData,
        ]);

        $run->loadMissing('runSections');
        $total    = $run->runSections->count();
        $complete = $run->runSections->where('status', 'complete')->count();

        return response()->json([
            'success'        => true,
            'status'         => $runSection->status,
            'sections_total' => $total,
            'sections_done'  => $complete,
        ]);
    }

    // ── AJAX: AQL plan calculation ────────────────────────────────────────────

    public function aqlCalculate(Request $request)
    {
        $request->validate([
            'lot_size'         => ['required', 'integer', 'min:1'],
            'inspection_level' => ['nullable', 'in:I,II,III,S1,S2,S3,S4'],
            'aql_critical'     => ['nullable', 'numeric'],
            'aql_major'        => ['nullable', 'numeric'],
            'aql_minor'        => ['nullable', 'numeric'],
        ]);

        $plan = $this->aql->calculate(
            (int)   $request->lot_size,
            $request->input('inspection_level', 'II'),
            $request->filled('aql_critical') ? (float) $request->aql_critical : 0.065,
            $request->filled('aql_major')    ? (float) $request->aql_major    : 2.5,
            $request->filled('aql_minor')    ? (float) $request->aql_minor    : 4.0,
        );

        return response()->json($plan);
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
            ->whereHas('section', fn($q) => $q->where('is_active', true))
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
                'inspection_run_id'     => $run->id,
                'inspection_section_id' => $default->inspection_section_id,
                'sort_order'            => ($i + 1) * 10,
                'data'                  => $default->section->default_data,
                'status'                => 'pending',
            ]);
        }

        // Create AQL record if aql_sampling section is included and none exists yet
        $hasAql = $defaults->contains(fn($d) => $d->section?->slug === 'aql_sampling');
        if ($hasAql && ! $run->aql()->exists()) {
            InspectionRunAql::create([
                'inspection_run_id' => $run->id,
                'aql_major'         => 2.5,
                'aql_minor'         => 4.0,
                'aql_critical'      => 0.065,
                'inspection_level'  => 'II',
            ]);
        }
    }
}
