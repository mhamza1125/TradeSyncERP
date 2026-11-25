<?php

namespace App\Services\Inspection;

use App\Models\InspectionRun;
use App\Models\InspectionRunDefect;

/**
 * One source of truth for AQL found counts (docs/QC_RULES.md §2): when a run has recorded
 * defect rows, the counts are the sum of their quantities per AQL class.
 * A row's own severity wins; otherwise the defect library's severity is used.
 * "functional" is not one of the three AQL classes and is not counted (question Q27).
 */
class InspectionDefectCountService
{
    /** @return array{critical:int, major:int, minor:int}|null  null when the run has no defect rows */
    public static function countsFor(InspectionRun $run): ?array
    {
        $rows = InspectionRunDefect::query()
            ->join('inspection_run_sections', 'inspection_run_sections.id', '=', 'inspection_run_defects.inspection_run_section_id')
            ->leftJoin('defects', 'defects.id', '=', 'inspection_run_defects.defect_id')
            ->where('inspection_run_sections.inspection_run_id', $run->id)
            ->selectRaw('COALESCE(inspection_run_defects.severity, defects.severity) AS sev, SUM(inspection_run_defects.qty) AS qty, COUNT(*) AS n')
            ->groupBy('sev')
            ->toBase()
            ->get();

        if ((int) $rows->sum('n') === 0) {
            return null;
        }

        $bySeverity = $rows->pluck('qty', 'sev');

        return [
            'critical' => (int) ($bySeverity['critical'] ?? 0),
            'major' => (int) ($bySeverity['major'] ?? 0),
            'minor' => (int) ($bySeverity['minor'] ?? 0),
        ];
    }
}
