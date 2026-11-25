<?php

namespace Tests\Feature\Inspections;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-01: template data must never execute as PHP inside dompdf. */
class PdfExportSafetyTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private string $marker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->marker = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tsk_f01_marker_'.getmypid().'.txt';
        @unlink($this->marker);
    }

    protected function tearDown(): void
    {
        @unlink($this->marker);
        parent::tearDown();
    }

    public function test_dompdf_php_is_disabled(): void
    {
        $this->assertFalse(config('dompdf.options.isPhpEnabled'));
    }

    public function test_embedded_php_in_section_data_is_not_executed_and_is_escaped(): void
    {
        $payload = '<script type="text/php">file_put_contents('.var_export($this->marker, true).', "pwned");</script>';

        $inspection = $this->makeInspection();
        $section = $this->makeSection('workmanship_test', 'workmanship');
        $run = $this->makeRun($inspection, [
            [$section, ['items' => [['name' => 'Stitching', 'status' => $payload]]], 'complete'],
        ]);

        $user = $this->userWithPermissions('inspections.edit');

        // The HTML that dompdf receives must carry the payload as inert text.
        $html = view('exports.inspection-run-pdf', [
            'inspection' => $inspection->load(['inspectionType', 'inspectors', 'customerOrders']),
            'runs' => collect([$run->load(['sample.customer', 'sample.category', 'sampleColor', 'runSections.section', 'runSections.attachments', 'runSections.defects.defect', 'aql.sizeBreakdowns'])]),
            'imgBase64' => fn () => null,
            'defects' => collect(),
            'logoBase64' => null,
        ])->render();

        $this->assertStringNotContainsString('<script type="text/php">', $html);
        $this->assertStringContainsString('&lt;script type=&quot;text/php&quot;&gt;', $html);

        // Rendering the real export must not run it.
        $this->actingAs($user)
            ->get(route('inspections.runs.export-pdf', [$inspection, $run]))
            ->assertOk();

        $this->assertFileDoesNotExist($this->marker);
    }

    public function test_list_pdfs_still_print_page_numbers(): void
    {
        $user = $this->userWithRole('Admin');

        $response = $this->actingAs($user)->get(route('masters.colors.export-pdf'));
        $response->assertOk();

        $text = $this->pdfText($response->getContent());
        $this->assertMatchesRegularExpression('/Page 1 of \d+/', $text);
    }

    private function pdfText(string $pdf): string
    {
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($file, $pdf);
        $text = (string) shell_exec('pdftotext '.escapeshellarg($file).' - 2>&1');
        @unlink($file);

        if ($text === '' || str_contains($text, 'not recognized') || str_contains($text, 'not found')) {
            $this->markTestSkipped('pdftotext is not available to read the PDF text.');
        }

        return $text;
    }
}
