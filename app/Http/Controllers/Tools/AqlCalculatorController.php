<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\SampleColor;
use App\Models\SampleSize;
use App\Services\Inspection\AqlCalculationService;

class AqlCalculatorController extends Controller
{
    public function __invoke(AqlCalculationService $aql)
    {
        $aqlJsData = $aql->tableForJs();
        $colors = SampleColor::orderBy('name')->get();
        $sizes = SampleSize::orderBy('name')->get();
        return view('tools.aql-calculator', compact('aqlJsData', 'colors', 'sizes'));
    }
}
