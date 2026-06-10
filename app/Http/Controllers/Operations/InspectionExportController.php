<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionRun;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InspectionExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:inspections.edit');
    }

    // ── Single run PDF ────────────────────────────────────────────────────────

    public function exportRun(Inspection $inspection, InspectionRun $run)
    {
        $inspection->load(['inspectionType', 'inspectors', 'customerOrders']);

        $run->load([
            'sample.customer',
            'sample.category',
            'runSections.section',
            'runSections.attachments',
            'aql',
        ]);

        $pdf = Pdf::loadView('exports.inspection-run-pdf', [
            'inspection' => $inspection,
            'runs'       => collect([$run]),
            'imgBase64'  => $this->imgBase64Closure(),
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false)
        ->setOption('defaultFont', 'sans-serif');

        $filename = sprintf('INS-%s-Run%d.pdf', $inspection->report_number, $run->run_number);

        return $pdf->download($filename);
    }

    // ── Bulk PDF: all runs for an inspection (or selected subset) ─────────────

    public function bulkExport(Request $request, Inspection $inspection)
    {
        $runIds = array_filter((array) $request->input('run_ids', []));

        $query = $inspection->runs()
            ->with([
                'sample.customer',
                'sample.category',
                'runSections.section',
                'runSections.attachments',
                'aql',
            ])
            ->orderBy('run_number');

        if (!empty($runIds)) {
            $query->whereIn('id', $runIds);
        }

        $runs = $query->get();

        if ($runs->isEmpty()) {
            return back()->with('error', 'No inspection runs to export.');
        }

        $inspection->load(['inspectionType', 'inspectors', 'customerOrders']);

        $pdf = Pdf::loadView('exports.inspection-run-pdf', [
            'inspection' => $inspection,
            'runs'       => $runs,
            'imgBase64'  => $this->imgBase64Closure(),
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false)
        ->setOption('defaultFont', 'sans-serif');

        $filename = sprintf('INS-%s-AllRuns.pdf', $inspection->report_number);

        return $pdf->download($filename);
    }

    // ── Helper: closure that converts a storage-relative path to base64 URI ──

    private function imgBase64Closure(): \Closure
    {
        return function (?string $filePath): ?string {
            if (!$filePath) {
                return null;
            }

            // Resolve against both public disk and public/assets
            $candidates = [
                storage_path('app/public/' . $filePath),
                public_path($filePath),
                public_path('storage/' . $filePath),
            ];

            foreach ($candidates as $abs) {
                if (file_exists($abs)) {
                    $mime = mime_content_type($abs) ?: 'image/jpeg';
                    return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($abs));
                }
            }

            return null;
        };
    }
}
