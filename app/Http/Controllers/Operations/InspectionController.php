<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreInspectionRequest;
use App\Http\Requests\Operations\UpdateInspectionRequest;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionType;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inspections.index')->only(['index', 'show']);
        $this->middleware('permission:inspections.create')->only(['create', 'store']);
        $this->middleware('permission:inspections.edit')->only(['edit', 'update']);
        $this->middleware('permission:inspections.delete')->only('destroy');
    }

    public function index(Request $request, Sample $sample)
    {
        $sample->load('customer', 'supplier');
        $inspections = $sample->inspections()
            ->with(['results', 'inspectionType', 'inspectors'])
            ->latest()
            ->paginate(20);

        return view('operations.inspections.index', compact('sample', 'inspections'));
    }

    public function create(Sample $sample)
    {
        $sample->load(['category.testingParameters', 'customer', 'supplier', 'testingParameters.parameter']);

        // Auto-seed from category when no sample-specific parameters have been assigned yet
        if ($sample->testingParameters->isEmpty() && $sample->category) {
            $categoryParams = $sample->category->testingParameters()->where('status', true)->get();
            if ($categoryParams->isNotEmpty()) {
                $sample->testingParameters()->createMany(
                    $categoryParams->map(fn ($p) => ['parameter_id' => $p->id])->toArray()
                );
                $sample->load('testingParameters.parameter');
            }
        }

        $employees         = Employee::where('status', true)->orderBy('employee_name')->get();
        $inspectionTypes   = InspectionType::where('status', true)->orderBy('name')->get();
        $testingParameters = $sample->testingParameters()->with('parameter.category')->get();

        return view('operations.inspections.create', compact(
            'sample', 'employees', 'inspectionTypes', 'testingParameters'
        ));
    }

    public function store(StoreInspectionRequest $request, Sample $sample)
    {
        return DB::transaction(function () use ($request, $sample) {
            $data = $request->validated();
            $data['sample_id']     = $sample->id;
            $data['report_number'] = $this->generateReportNumber();

            $inspectorIds = $data['inspector_ids'] ?? [];
            unset($data['inspector_ids'], $data['results']);

            $inspection = Inspection::create($data);

            if (!empty($inspectorIds)) {
                $inspection->inspectors()->sync($inspectorIds);
            }

            $results = $request->input('results', []);
            foreach ($results as $result) {
                if (!empty($result['attachment']) && $result['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                    $result['attachment'] = $result['attachment']->store('inspection-results', 'public');
                }
                $inspection->results()->create($result);
            }

            $inspection->update(['overall_status' => $this->deriveOverallStatus($inspection)]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'inspection' => $inspection->load('results')]);
            }

            // Shallow route: inspections.show, not samples.inspections.show
            return redirect()->route('inspections.show', $inspection)
                ->with('success', "Inspection {$inspection->report_number} created.");
        });
    }

    public function show(Inspection $inspection)
    {
        $inspection->load([
            'sample.customer',
            'sample.supplier',
            'sample.brand',
            'inspectionType',
            'inspectors',
            'results.sampleTestingParameter.parameter',
        ]);
        $sample = $inspection->sample;
        return view('operations.inspections.show', compact('sample', 'inspection'));
    }

    public function edit(Inspection $inspection)
    {
        $sample          = $inspection->load('sample.customer')->sample;
        $employees       = Employee::where('status', true)->orderBy('employee_name')->get();
        $inspectionTypes = InspectionType::where('status', true)->orderBy('name')->get();
        $inspection->load('results.sampleTestingParameter', 'inspectors', 'inspectionType');

        return view('operations.inspections.edit', compact(
            'sample', 'inspection', 'employees', 'inspectionTypes'
        ));
    }

    public function update(UpdateInspectionRequest $request, Inspection $inspection)
    {
        $data         = $request->validated();
        $inspectorIds = $data['inspector_ids'] ?? [];
        unset($data['inspector_ids']);

        $inspection->update($data);
        $inspection->inspectors()->sync($inspectorIds);
        $inspection->update(['overall_status' => $this->deriveOverallStatus($inspection)]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'inspection' => $inspection]);
        }

        return redirect()->route('inspections.show', $inspection)
            ->with('success', 'Inspection updated.');
    }

    public function destroy(Inspection $inspection)
    {
        $sample = $inspection->sample;
        $inspection->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('samples.inspections.index', $sample)
            ->with('success', 'Inspection deleted.');
    }

    private function generateReportNumber(): string
    {
        $year    = now()->year;
        $lastId  = Inspection::max('id') ?? 0;
        $nextSeq = str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
        return "RPT-{$year}-{$nextSeq}";
    }

    private function deriveOverallStatus(Inspection $inspection): string
    {
        $results = $inspection->results()->get();
        if ($results->isEmpty()) {
            return 'Pending';
        }
        return $results->contains('pass_fail', 'Fail') ? 'Fail' : 'Pass';
    }
}
