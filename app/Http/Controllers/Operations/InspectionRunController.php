<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspectionRunController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inspections.edit');
    }

    public function create(Inspection $inspection)
    {
        $inspectionTypes = InspectionType::where('status', true)->orderBy('name')->get();

        return view('operations.inspections.runs.create', compact('inspection', 'inspectionTypes'));
    }

    public function store(Request $request, Inspection $inspection)
    {
        $request->validate([
            'inspection_type_id' => ['nullable', 'exists:inspection_types,id'],
            'remarks'            => ['nullable', 'string', 'max:1000'],
        ]);

        $run = $inspection->runs()->create([
            'inspection_type_id' => $request->inspection_type_id,
            'remarks'            => $request->remarks,
        ]);

        return redirect()->route('inspections.runs.edit', [$inspection, $run])
            ->with('success', 'Run created. Set testing parameter results below.');
    }

    public function edit(Inspection $inspection, InspectionRun $run)
    {
        $inspection->load([
            'samples.category.testingParameters' => fn($q) => $q->where('status', true)->orderBy('parameter_name'),
            'samples.customer',
        ]);
        $run->load(['results.defect', 'results.attachments', 'inspectionType']);

        $inspectionTypes = InspectionType::where('status', true)->orderBy('name')->get();
        $defects         = Defect::where('status', true)->orderBy('defect_name')->get();

        // Map existing results keyed by "sample_id_parameter_id" for quick lookup in the view
        $resultsMap = $run->results->mapWithKeys(
            fn($r) => ["{$r->sample_id}_{$r->testing_parameter_id}" => $r]
        );

        return view('operations.inspections.runs.edit', compact(
            'inspection', 'run', 'inspectionTypes', 'defects', 'resultsMap'
        ));
    }

    public function update(Request $request, Inspection $inspection, InspectionRun $run)
    {
        $request->validate([
            'inspection_type_id'                     => ['nullable', 'exists:inspection_types,id'],
            'remarks'                                => ['nullable', 'string', 'max:1000'],
            'results'                                => ['array'],
            'results.*.sample_id'                   => ['required', 'exists:samples,id'],
            'results.*.testing_parameter_id'        => ['required', 'exists:testing_parameters_master,id'],
            'results.*.status'                      => ['required', 'in:Pending,Pass,Fail,Rejected'],
            'results.*.defect_id'                   => ['nullable', 'exists:defects,id'],
            'results.*.remarks'                     => ['nullable', 'string', 'max:1000'],
            'files'                                  => ['nullable', 'array'],
            'files.*.*'                              => ['file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx'],
        ]);

        return DB::transaction(function () use ($request, $inspection, $run) {
            $run->update([
                'inspection_type_id' => $request->inspection_type_id,
                'remarks'            => $request->remarks,
            ]);

            foreach ($request->input('results', []) as $key => $data) {
                $result = $run->results()->updateOrCreate(
                    [
                        'sample_id'            => $data['sample_id'],
                        'testing_parameter_id' => $data['testing_parameter_id'],
                    ],
                    [
                        'status'    => $data['status'],
                        'defect_id' => ($data['status'] === 'Rejected') ? ($data['defect_id'] ?? null) : null,
                        'remarks'   => $data['remarks'] ?? null,
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

            return redirect()->route('inspections.runs.edit', [$inspection, $run])
                ->with('success', 'Run results saved.');
        });
    }

    public function destroy(Inspection $inspection, InspectionRun $run)
    {
        DB::transaction(function () use ($run) {
            foreach ($run->results as $result) {
                foreach ($result->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $result->attachments()->delete();
                $result->delete();
            }
            $run->delete();
        });

        return redirect()->route('inspections.edit', $inspection)
            ->with('success', 'Run deleted.');
    }
}
