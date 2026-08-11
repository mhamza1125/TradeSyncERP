<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tools\StoreAqlCalculationRequest;
use App\Http\Requests\Tools\UpdateAqlCalculationRequest;
use App\Models\AqlCalculation;
use App\Models\SampleColor;
use App\Models\SampleSize;
use App\Services\Inspection\AqlCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AqlCalculatorController extends Controller
{
    public function __construct(private readonly AqlCalculationService $aql)
    {
        $this->middleware('permission:aql-calculator.index')->only(['index', 'show', 'exportPdf']);
        $this->middleware('permission:aql-calculator.create')->only(['create', 'store']);
        $this->middleware('permission:aql-calculator.edit')->only(['edit', 'update']);
        $this->middleware('permission:aql-calculator.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $aqlCalculations = AqlCalculation::query()
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->verdict, fn ($q, $v) => $q->where('verdict', $v))
            ->when($request->inspection_level, fn ($q, $l) => $q->where('inspection_level', $l))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('tools.aql-calculator.index', compact('aqlCalculations'));
    }

    public function create()
    {
        $aqlJsData = $this->aql->tableForJs();
        $colors = SampleColor::orderBy('name')->get();
        $sizes = SampleSize::orderBy('name')->get();

        return view('tools.aql-calculator.create', compact('aqlJsData', 'colors', 'sizes'));
    }

    public function store(StoreAqlCalculationRequest $request)
    {
        $aqlCalculation = AqlCalculation::create($this->computedAttributes($request->validated()));

        return redirect()->route('tools.aql-calculator.show', $aqlCalculation)
            ->with('success', 'AQL calculation saved successfully.');
    }

    public function show(AqlCalculation $aqlCalculation)
    {
        return view('tools.aql-calculator.show', compact('aqlCalculation'));
    }

    public function edit(AqlCalculation $aqlCalculation)
    {
        $aqlJsData = $this->aql->tableForJs();
        $colors = SampleColor::orderBy('name')->get();
        $sizes = SampleSize::orderBy('name')->get();

        return view('tools.aql-calculator.edit', compact('aqlCalculation', 'aqlJsData', 'colors', 'sizes'));
    }

    public function update(UpdateAqlCalculationRequest $request, AqlCalculation $aqlCalculation)
    {
        $aqlCalculation->update($this->computedAttributes($request->validated()));

        return redirect()->route('tools.aql-calculator.show', $aqlCalculation)
            ->with('success', 'AQL calculation updated successfully.');
    }

    public function destroy(AqlCalculation $aqlCalculation)
    {
        $aqlCalculation->delete();

        return redirect()->route('tools.aql-calculator.index')
            ->with('success', 'AQL calculation deleted.');
    }

    public function exportPdf(AqlCalculation $aqlCalculation)
    {
        $pdf = Pdf::loadView('exports.aql-calculation-pdf', compact('aqlCalculation'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream("AQL-Calculation-{$aqlCalculation->id}.pdf");
    }

    /**
     * Recompute the authoritative sampling plan, verdict and variation
     * distribution server-side from validated raw input — the client-side
     * preview is never trusted for the saved values.
     */
    private function computedAttributes(array $data): array
    {
        $plan = $this->aql->planForLot(
            $data['lot_size'],
            $data['inspection_level'],
            $data['aql_critical'] ?? null,
            $data['aql_major'] ?? null,
            $data['aql_minor'] ?? null,
        );

        $foundCritical = $data['found_critical'] ?? 0;
        $foundMajor = $data['found_major'] ?? 0;
        $foundMinor = $data['found_minor'] ?? 0;

        $verdict = $this->aql->verdict(
            $foundCritical,
            $foundMajor,
            $foundMinor,
            $plan['critical']['ac'] ?? null,
            $plan['major']['ac'] ?? null,
            $plan['minor']['ac'] ?? null,
        );

        $variations = collect($data['variations'] ?? [])
            ->filter(fn ($row) => filled($row['color'] ?? null) || filled($row['size'] ?? null) || ($row['qty'] ?? 0) > 0)
            ->values();

        if ($variations->isNotEmpty() && $plan['sample_size'] > 0) {
            $qtys = $variations->map(fn ($row) => (int) ($row['qty'] ?? 0))->all();
            $distributed = $this->aql->distributeProportionally($plan['sample_size'], $qtys);

            $variations = $variations->values()->map(fn ($row, $i) => [
                'color' => $row['color'] ?? null,
                'size' => $row['size'] ?? null,
                'qty' => (int) ($row['qty'] ?? 0),
                'inspect_qty' => $distributed[$i] ?? 0,
            ]);
        }

        return [
            'title' => $data['title'],
            'lot_size' => $data['lot_size'],
            'inspection_level' => $data['inspection_level'],
            'aql_critical' => $data['aql_critical'] ?? null,
            'aql_major' => $data['aql_major'] ?? null,
            'aql_minor' => $data['aql_minor'] ?? null,
            'code_letter' => $plan['code_letter'],
            'sample_size' => $plan['sample_size'],
            'ac_critical' => $plan['critical']['ac'] ?? null,
            're_critical' => $plan['critical']['re'] ?? null,
            'ac_major' => $plan['major']['ac'] ?? null,
            're_major' => $plan['major']['re'] ?? null,
            'ac_minor' => $plan['minor']['ac'] ?? null,
            're_minor' => $plan['minor']['re'] ?? null,
            'found_critical' => $foundCritical,
            'found_major' => $foundMajor,
            'found_minor' => $foundMinor,
            'verdict' => $verdict,
            'variations' => $variations->isNotEmpty() ? $variations->all() : null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
