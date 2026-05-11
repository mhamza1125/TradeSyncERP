<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\StoreInspectionRequest;
use App\Http\Requests\Operations\UpdateInspectionRequest;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\Sample;
use App\Models\Vendor;
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
        $inspections = $sample->inspections()
            ->with('results')
            ->latest()
            ->paginate(20);

        return view('operations.inspections.index', compact('sample', 'inspections'));
    }

    public function create(Sample $sample)
    {
        $sample->load('testingParameters.parameter');
        $employees = Employee::where('status', true)->orderBy('employee_name')->get();
        $vendors   = Vendor::where('status', true)->orderBy('vendor_name')->get();
        return view('operations.inspections.create', compact('sample', 'employees', 'vendors'));
    }

    public function store(StoreInspectionRequest $request, Sample $sample)
    {
        return DB::transaction(function () use ($request, $sample) {
            $data = $request->validated();
            $data['sample_id']     = $sample->id;
            $data['report_number'] = $this->generateReportNumber();

            $inspection = Inspection::create($data);

            if (!empty($data['results'])) {
                foreach ($data['results'] as $result) {
                    if (!empty($result['attachment'])) {
                        $result['attachment'] = $result['attachment']->store('inspection-results', 'public');
                    }
                    $inspection->results()->create($result);
                }
            }

            $inspection->update(['overall_status' => $this->deriveOverallStatus($inspection)]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'inspection' => $inspection->load('results')]);
            }

            return redirect()->route('samples.inspections.show', [$sample, $inspection])
                ->with('success', "Inspection {$inspection->report_number} created.");
        });
    }

    public function show(Sample $sample, Inspection $inspection)
    {
        $inspection->load(['sample.customer', 'sample.brand', 'results.sampleTestingParameter.parameter']);
        return view('operations.inspections.show', compact('sample', 'inspection'));
    }

    public function edit(Sample $sample, Inspection $inspection)
    {
        $employees = Employee::where('status', true)->orderBy('employee_name')->get();
        $vendors   = Vendor::where('status', true)->orderBy('vendor_name')->get();
        $inspection->load('results.sampleTestingParameter');
        return view('operations.inspections.edit', compact('sample', 'inspection', 'employees', 'vendors'));
    }

    public function update(UpdateInspectionRequest $request, Sample $sample, Inspection $inspection)
    {
        $inspection->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'inspection' => $inspection]);
        }

        return redirect()->route('samples.inspections.show', [$sample, $inspection])
            ->with('success', 'Inspection updated.');
    }

    public function destroy(Sample $sample, Inspection $inspection)
    {
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
        $results = $inspection->results;
        if ($results->isEmpty()) {
            return 'Pending';
        }
        return $results->contains('pass_fail', 'Fail') ? 'Fail' : 'Pass';
    }
}
