<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunAql;
use App\Models\InspectionRunSection;
use App\Models\InspectionSection;
use App\Models\InspectionType;
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

    // ── Create: section selector ─────────────────────────────────────────────

    public function create(Inspection $inspection)
    {
        $inspectionTypes = InspectionType::where('status', true)
            ->with(['defaultSections' => fn($q) => $q->orderBy('inspection_type_section_defaults.sort_order')])
            ->orderBy('name')
            ->get();

        $allSections = InspectionSection::active()->orderBy('sort_order')->get();

        return view('operations.inspections.runs.create', compact(
            'inspection', 'inspectionTypes', 'allSections'
        ));
    }

    // ── Store: create run + instantiate chosen sections ──────────────────────

    public function store(Request $request, Inspection $inspection)
    {
        $request->validate([
            'inspection_type_id' => ['nullable', 'exists:inspection_types,id'],
            'remarks'            => ['nullable', 'string', 'max:1000'],
            'section_ids'        => ['nullable', 'array'],
            'section_ids.*'      => ['exists:inspection_sections,id'],
        ]);

        return DB::transaction(function () use ($request, $inspection) {
            $runNumber = $inspection->runs()->max('run_number') + 1;

            $run = $inspection->runs()->create([
                'inspection_type_id' => $request->inspection_type_id,
                'run_number'         => $runNumber,
                'remarks'            => $request->remarks,
                'verdict'            => 'Pending',
            ]);

            // Instantiate selected sections with default data as starting point
            $sectionIds = $request->input('section_ids', []);
            if (! empty($sectionIds)) {
                $sections = InspectionSection::whereIn('id', $sectionIds)
                    ->orderBy('sort_order')
                    ->get();

                foreach ($sections as $order => $section) {
                    InspectionRunSection::create([
                        'inspection_run_id'    => $run->id,
                        'inspection_section_id'=> $section->id,
                        'sort_order'           => ($order + 1) * 10,
                        'data'                 => $section->default_data,
                        'status'               => 'pending',
                    ]);
                }

                // Create the AQL record if aql_sampling section selected
                if ($this->hasSectionSlug($sections, 'aql_sampling')) {
                    InspectionRunAql::create([
                        'inspection_run_id' => $run->id,
                        'aql_major'         => 2.5,
                        'aql_minor'         => 4.0,
                        'aql_critical'      => 0.065,
                        'inspection_level'  => 'II',
                    ]);
                }
            }

            return redirect()->route('inspections.runs.edit', [$inspection, $run])
                ->with('success', "Run #{$runNumber} created. Complete the enabled sections below.");
        });
    }

    // ── Edit: full modular section form ──────────────────────────────────────

    public function edit(Inspection $inspection, InspectionRun $run)
    {
        $inspection->load([
            'samples.category.testingParameters' => fn($q) => $q->where('status', true)->orderBy('parameter_name'),
            'samples.customer',
        ]);

        $run->load([
            'runSections.section',
            'runSections.attachments',
            'results.defect.category',
            'results.attachments',
            'results.testingParameter',
            'aql',
            'inspectionType',
        ]);

        $inspectionTypes = InspectionType::where('status', true)->orderBy('name')->get();
        $defects         = Defect::where('status', true)
            ->with('category')
            ->orderBy('defect_name')
            ->get();

        // Map existing results keyed by "sample_id_parameter_id"
        $resultsMap = $run->results->mapWithKeys(
            fn($r) => ["{$r->sample_id}_{$r->testing_parameter_id}" => $r]
        );

        // Map run sections by slug for view access
        $sectionMap = $run->runSections->mapWithKeys(
            fn($rs) => [$rs->section->slug => $rs]
        );

        // AQL JS data for the client-side calculator
        $aqlJsData = $this->aql->tableForJs();

        return view('operations.inspections.runs.edit', compact(
            'inspection', 'run', 'inspectionTypes',
            'defects', 'resultsMap', 'sectionMap', 'aqlJsData'
        ));
    }

    // ── Update: save all section data in one transaction ─────────────────────

    public function update(Request $request, Inspection $inspection, InspectionRun $run)
    {
        $request->validate([
            'inspection_type_id'                  => ['nullable', 'exists:inspection_types,id'],
            'remarks'                             => ['nullable', 'string', 'max:1000'],
            'verdict'                             => ['nullable', 'in:Pending,Pass,Fail,Conditional'],

            // Testing parameter results
            'results'                             => ['array'],
            'results.*.sample_id'                => ['required', 'exists:samples,id'],
            'results.*.testing_parameter_id'     => ['required', 'exists:testing_parameters_master,id'],
            'results.*.status'                   => ['required', 'in:Pending,Pass,Fail,Rejected'],
            'results.*.defect_id'                => ['nullable', 'exists:defects,id'],
            'results.*.defect_severity'          => ['nullable', 'in:Critical,Major,Minor,Functional'],
            'results.*.remarks'                  => ['nullable', 'string', 'max:1000'],
            'files'                               => ['nullable', 'array'],
            'files.*.*'                           => ['file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx'],

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
            'sections'                           => ['nullable', 'array'],
            'sections.*.status'                  => ['nullable', 'in:pending,complete,na'],
            'sections.*.notes'                   => ['nullable', 'string', 'max:2000'],
            'sections.*.data'                    => ['nullable', 'array'],

            // Section photo uploads
            'section_files'                      => ['nullable', 'array'],
            'section_files.*.*'                  => ['file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf'],
        ]);

        return DB::transaction(function () use ($request, $inspection, $run) {
            // ── 1. Run header ─────────────────────────────────────────────────
            $run->update([
                'inspection_type_id' => $request->inspection_type_id,
                'remarks'            => $request->remarks,
                'verdict'            => $request->input('verdict', 'Pending'),
            ]);

            // ── 2. Testing parameter results ──────────────────────────────────
            foreach ($request->input('results', []) as $key => $data) {
                $result = $run->results()->updateOrCreate(
                    [
                        'sample_id'            => $data['sample_id'],
                        'testing_parameter_id' => $data['testing_parameter_id'],
                    ],
                    [
                        'status'         => $data['status'],
                        'defect_id'      => in_array($data['status'], ['Fail', 'Rejected'])
                            ? ($data['defect_id'] ?? null) : null,
                        'defect_severity'=> in_array($data['status'], ['Fail', 'Rejected'])
                            ? ($data['defect_severity'] ?? null) : null,
                        'remarks'        => $data['remarks'] ?? null,
                    ]
                );

                if ($request->hasFile("files.{$key}")) {
                    foreach ($request->file("files.{$key}") as $file) {
                        $path = $file->store("inspection-results/{$result->id}", 'public');
                        $result->attachments()->create([
                            'title'           => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                            'file_name'       => $file->getClientOriginalName(),
                            'file_path'       => $path,
                            'mime_type'       => $file->getMimeType(),
                            'file_size'       => $file->getSize(),
                            'attachment_type' => 'document',
                            'uploaded_by'     => auth()->id(),
                        ]);
                    }
                }
            }

            // ── 3. AQL record ─────────────────────────────────────────────────
            if ($request->filled('aql.lot_size')) {
                $aqlInput = $request->input('aql', []);

                $lotSize    = (int) ($aqlInput['lot_size'] ?? 0);
                $level      = $aqlInput['inspection_level'] ?? 'II';
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

            // ── 4. Dynamic section data ───────────────────────────────────────
            foreach ($request->input('sections', []) as $runSectionId => $sectionData) {
                $runSection = InspectionRunSection::where('id', $runSectionId)
                    ->where('inspection_run_id', $run->id)
                    ->first();

                if (! $runSection) {
                    continue;
                }

                $runSection->update([
                    'status' => $sectionData['status'] ?? 'pending',
                    'notes'  => $sectionData['notes']  ?? null,
                    'data'   => $sectionData['data']   ?? $runSection->data,
                ]);

                // Section photo uploads
                if ($request->hasFile("section_files.{$runSectionId}")) {
                    foreach ($request->file("section_files.{$runSectionId}") as $file) {
                        $path = $file->store("inspection-sections/{$runSection->id}", 'public');
                        $runSection->attachments()->create([
                            'title'           => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                            'file_name'       => $file->getClientOriginalName(),
                            'file_path'       => $path,
                            'mime_type'       => $file->getMimeType(),
                            'file_size'       => $file->getSize(),
                            'attachment_type' => 'image',
                            'uploaded_by'     => auth()->id(),
                        ]);
                    }
                }
            }

            return redirect()->route('inspections.runs.edit', [$inspection, $run])
                ->with('success', 'Run saved successfully.');
        });
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Inspection $inspection, InspectionRun $run)
    {
        DB::transaction(function () use ($run) {
            // Clean up result attachments
            foreach ($run->results as $result) {
                foreach ($result->attachments as $att) {
                    Storage::disk('public')->delete($att->file_path);
                }
                $result->attachments()->delete();
                $result->delete();
            }

            // Clean up section attachments
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

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function hasSectionSlug($sections, string $slug): bool
    {
        return $sections->contains(fn($s) => $s->slug === $slug);
    }
}
