<?php

namespace Tests\Feature\Inspections;

use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunDefect;
use App\Models\InspectionRunSection;
use App\Models\InspectionSection;
use App\Services\Inspection\InspectionRunLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/**
 * Q15 = (a), RR-02: the run's decision is the computed verdict (AQL, then defect rows).
 * "Overall Article Result" is the inspector's assessment: recorded, never the decision.
 * A contradicting assessment needs a written reason to finish (docs/QC_RULES.md §3).
 */
class InspectorAssessmentTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspection;

    private InspectionRun $run;

    private InspectionRunSection $assessment;

    private InspectionRunSection $defectSection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Admin'));
        $this->inspection = $this->makeInspection();
        $this->run = $this->makeRun($this->inspection, [
            [$this->makeSection('defect_recording', 'defects'), [], 'complete'],
            [$this->assessmentSection(), [], 'complete'],
        ]);
        $this->defectSection = $this->section($this->run, 'defect_recording');
        $this->assessment = $this->section($this->run, 'overall_article_result');

        $major = Defect::create(['defect_name' => 'Open seam', 'severity' => 'major', 'status' => true]);
        InspectionRunDefect::create([
            'inspection_run_section_id' => $this->defectSection->id, 'defect_id' => $major->id,
            'severity' => 'major', 'qty' => 1, 'status' => 'open', 'sort_order' => 0,
        ]); // computed verdict: Fail
    }

    private function assessmentSection()
    {
        return InspectionSection::firstOrCreate(['slug' => 'overall_article_result'], [
            'name' => 'Overall Article Result', 'section_type' => 'review',
            'default_data' => ['overall_result' => null, 'remarks' => null], 'is_active' => true, 'skip_policy' => 'mandatory',
        ]);
    }

    private function section(InspectionRun $run, string $slug): InspectionRunSection
    {
        return $run->runSections()->whereHas('section', fn ($q) => $q->where('slug', $slug))->firstOrFail();
    }

    private function finish(?string $assessment, ?string $reason = null, array $extra = [], ?InspectionRun $run = null, ?InspectionRunSection $section = null)
    {
        $run ??= $this->run;
        $section ??= $this->assessment;

        return $this->put(route('inspections.runs.update', [$this->inspection, $run]), array_merge([
            'finish_run' => 1,
            'sections' => [$section->id => ['status' => 'complete', 'data' => array_filter([
                'overall_result' => $assessment,
                'assessment_reason' => $reason,
            ], fn ($v) => $v !== null)]],
        ], $extra));
    }

    private function pdfHtml(InspectionRun $run): string
    {
        $run = InspectionRunLockService::bypass(fn () => $run->fresh()->load([
            'sample.customer', 'sample.category', 'sampleColor', 'runSections.section', 'runSections.attachments', 'runSections.defects.defect', 'aql.sizeBreakdowns',
        ]));

        return view('exports.inspection-run-pdf', [
            'inspection' => $this->inspection->fresh()->load(['inspectionType', 'inspectors', 'customerOrders']),
            'runs' => collect([$run]),
            'imgBase64' => fn () => null,
            'defects' => Defect::all()->keyBy('id'),
            'logoBase64' => null,
        ])->render();
    }

    // (i)
    public function test_a_contradicting_assessment_without_a_reason_is_refused_and_the_data_is_saved(): void
    {
        $this->finish('Pass')
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'not finished') && str_contains($m, 'Reason the assessment differs'));

        $this->assertNull($this->run->fresh()->completed_at);
        $this->assertSame('Pass', $this->assessment->fresh()->data['overall_result']);
    }

    // (ii)
    public function test_with_a_reason_the_run_finishes_with_the_computed_verdict_and_the_reason_is_stored_and_logged(): void
    {
        $this->finish('Pass', 'Seam defect is cosmetic; buyer accepted it by email.')->assertSessionHas('success');

        $run = $this->run->fresh();
        $this->assertNotNull($run->completed_at);
        $this->assertSame('Fail', $run->verdict);
        $this->assertSame('Seam defect is cosmetic; buyer accepted it by email.', $this->assessment->fresh()->data['assessment_reason']);

        $log = Activity::where('subject_type', $run->getMorphClass())->where('subject_id', $run->id)->where('event', 'finished')->sole();
        $this->assertSame('Fail', $log->properties['decision']);
        $this->assertSame('Pass', $log->properties['inspector_assessment']);
        $this->assertSame('Seam defect is cosmetic; buyer accepted it by email.', $log->properties['assessment_reason']);
    }

    // (iii)
    public function test_an_assessment_that_matches_the_computed_verdict_needs_no_reason(): void
    {
        $this->finish('Fail')->assertSessionHas('success');
        $this->assertSame('Fail', $this->run->fresh()->verdict);
    }

    public function test_no_assessment_needs_no_reason(): void
    {
        $this->finish('Pending')->assertSessionHas('success');
        $this->assertSame('Fail', $this->run->fresh()->verdict);
    }

    // (iv) A1
    public function test_when_nothing_computes_the_assessment_is_the_run_verdict(): void
    {
        $run = $this->makeRun($this->inspection, [[$this->assessmentSection(), [], 'complete']]);
        $section = $this->section($run, 'overall_article_result');

        $this->finish('Re-Inspection Required', null, [], $run, $section)->assertSessionHas('success');

        $this->assertSame('Re-Inspection', $run->fresh()->verdict);
    }

    public function test_when_nothing_computes_and_there_is_no_assessment_the_verdict_stays_pending(): void
    {
        $run = $this->makeRun($this->inspection, [[$this->assessmentSection(), [], 'complete']]);

        $this->finish('Pending', null, [], $run, $this->section($run, 'overall_article_result'))->assertSessionHas('success');

        $this->assertSame('Pending', $run->fresh()->verdict);
    }

    // A2
    public function test_the_old_final_review_verdict_field_no_longer_decides(): void
    {
        $finalReview = $this->makeSection('final_review', 'review', ['overall_verdict' => null]);
        $run = $this->makeRun($this->inspection, [[$finalReview, ['overall_verdict' => 'Pass'], 'complete']]);

        $this->put(route('inspections.runs.update', [$this->inspection, $run]), ['finish_run' => 1])->assertSessionHas('success');

        $this->assertSame('Pending', $run->fresh()->verdict);
    }

    // (v)
    public function test_the_pdf_prints_one_decision_and_a_labelled_assessment_with_its_reason(): void
    {
        $this->finish('Pass', 'Buyer accepted the seam by email.');

        $html = $this->pdfHtml($this->run);

        $this->assertSame(1, substr_count($html, 'Final Inspection Decision'));
        $this->assertStringContainsString("Inspector's assessment", $html);
        $this->assertStringContainsString('Buyer accepted the seam by email.', $html);
        $this->assertStringContainsString('differs from the decision', $html);
        // The assessment is no longer printed as a verdict box or as a PASS summary row.
        $this->assertStringNotContainsString('review-verdict-box vb-pass', $html);
        $this->assertDoesNotMatchRegularExpression('/Overall Article Result<\/td>\s*<td class="st-detail">[^<]*<\/td>\s*<td class="st-status"><span class="badge badge-pass">PASS/', $html);
    }

    public function test_an_old_contradicting_run_without_a_reason_prints_no_reason_recorded(): void
    {
        // As in trade_prod run 3: finished Fail, assessment Pass, no reason (stored before this rule).
        InspectionRunLockService::bypass(function () {
            $this->assessment->update(['data' => ['overall_result' => 'Pass']]);
            $this->run->update(['completed_at' => now(), 'verdict' => 'Fail']);
        });

        $html = $this->pdfHtml($this->run);

        $this->assertStringContainsString('no reason recorded', $html);
        $this->assertSame(1, substr_count($html, 'Final Inspection Decision'));
    }

    public function test_a_matching_assessment_prints_without_a_reason_line(): void
    {
        $this->finish('Fail');

        $html = $this->pdfHtml($this->run);

        $this->assertStringContainsString("Inspector's assessment", $html);
        $this->assertStringNotContainsString('no reason recorded', $html);
        $this->assertStringNotContainsString('differs from the decision', $html);
    }

    // (vi)
    public function test_edit_show_and_list_agree_on_the_decision(): void
    {
        $this->finish('Pass', 'Buyer accepted the seam by email.');

        $this->get(route('inspections.runs.edit', [$this->inspection, $this->run]))
            ->assertOk()
            ->assertSee('Decision', false)
            ->assertSee('data-run-decision="Fail"', false)
            ->assertSee("Inspector's assessment", false);
        $this->get(route('inspections.show', $this->inspection))->assertOk()->assertSee('Fail');
        $this->get(route('inspections.index'))->assertOk()->assertSee('Fail');
    }

    public function test_the_edit_page_labels_the_assessment_and_offers_the_reason_field(): void
    {
        $this->get(route('inspections.runs.edit', [$this->inspection, $this->run]))
            ->assertOk()
            ->assertSee("Inspector's assessment", false)
            ->assertSee('name="sections['.$this->assessment->id.'][data][assessment_reason]"', false)
            ->assertSee('calculated from the AQL result and the recorded defects', false)
            ->assertSee('data-computed-verdict="Fail"', false);
    }

    // (vii)
    public function test_posting_a_verdict_directly_is_still_ignored(): void
    {
        $this->finish('Fail', null, ['verdict' => 'Pass', 'completed_at' => '2020-01-01']);

        $this->assertSame('Fail', $this->run->fresh()->verdict);
        $this->assertNotSame('2020-01-01', $this->run->fresh()->completed_at->toDateString());
    }

    public function test_a_very_long_reason_is_rejected(): void
    {
        $this->finish('Pass', str_repeat('x', 2001))->assertSessionHasErrors('sections.'.$this->assessment->id.'.data.assessment_reason');
        $this->assertNull($this->run->fresh()->completed_at);
    }
}
