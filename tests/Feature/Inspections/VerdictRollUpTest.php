<?php

namespace Tests\Feature\Inspections;

use App\Models\Inspection;
use App\Models\InspectionRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-04 end to end: finishing runs, the stored roll-up, and the PDF cover. */
class VerdictRollUpTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Admin'));
        $this->inspection = $this->makeInspection();
    }

    // Q15 = (a): with no AQL and no defect section nothing computes, so the inspector's
    // assessment is the run verdict (A1). The old final_review field no longer decides.
    private function finishWithAssessment(string $choice): InspectionRun
    {
        $section = $this->makeSection('overall_article_result', 'review', ['overall_result' => null]);
        $run = $this->makeRun($this->inspection, [[$section, ['overall_result' => $choice], 'complete']]);

        $this->put(route('inspections.runs.update', [$this->inspection, $run]), ['finish_run' => 1])
            ->assertRedirect();

        return $run->fresh();
    }

    public function test_re_inspection_run_and_inspection_are_not_pass(): void
    {
        $passed = $this->finishWithAssessment('Pass');
        $reinspect = $this->finishWithAssessment('Re-Inspection Required');

        $this->assertSame('Pass', $passed->verdict);
        $this->assertSame('Re-Inspection', $reinspect->verdict);
        $this->assertSame('Re-Inspection', $this->inspection->fresh()->overall_status);
    }

    public function test_conditional_pass_rolls_up_to_conditional(): void
    {
        $this->finishWithAssessment('Pass');
        $this->finishWithAssessment('Conditional Pass');

        $this->assertSame('Conditional', $this->inspection->fresh()->overall_status);
    }

    public function test_pdf_cover_and_color_line_agree(): void
    {
        $passed = $this->finishWithAssessment('Pass');
        $reinspect = $this->finishWithAssessment('Re-Inspection Required');
        // Same style (sample) so they form one "x of y colors" group.
        $reinspect->update(['sample_id' => $passed->sample_id]);

        $html = view('exports.inspection-run-pdf', [
            'inspection' => $this->inspection->fresh()->load(['inspectionType', 'inspectors', 'customerOrders']),
            'runs' => $this->inspection->runs()->with(['sample.customer', 'sample.category', 'sampleColor', 'runSections.section', 'runSections.attachments', 'runSections.defects.defect', 'aql.sizeBreakdowns'])->orderBy('run_number')->get(),
            'imgBase64' => fn () => null,
            'defects' => collect(),
            'logoBase64' => null,
        ])->render();

        $this->assertStringContainsString('RE-INSPECTION', $html);
        $this->assertStringContainsString('1 of 2 colors passed', $html);
        $this->assertStringNotContainsString('✓  PASS', $html); // final decision card
        $this->assertStringNotContainsString('verdict-badge-lg vb-pass', $html); // cover badge
    }
}
