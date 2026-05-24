<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Services\Inspection\AqlCalculationService;

class AqlCalculatorController extends Controller
{
    public function __invoke(AqlCalculationService $aql)
    {
        $aqlJsData = $aql->tableForJs();
        return view('tools.aql-calculator', compact('aqlJsData'));
    }
}
