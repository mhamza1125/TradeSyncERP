<?php

namespace Tests\Feature\Inspections;

use App\Models\InspectionRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-03 / F-14 through the real run-save endpoint. */
class RunAqlSaveTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private function runWithAqlSection(): array
    {
        $this->actingAs($this->userWithRole('Admin'));
        $inspection = $this->makeInspection();
        $run = $this->makeRun($inspection, [[$this->makeSection('aql_sampling', 'aql'), [], 'pending']]);

        return [$inspection, $run, $run->runSections()->firstOrFail()];
    }

    private function save($inspection, InspectionRun $run, array $aql, array $extra = [])
    {
        return $this->put(route('inspections.runs.update', [$inspection, $run]), array_merge([
            'aql' => array_merge([
                'lot_size' => 1000, 'inspection_level' => 'II',
                'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0',
                'found_critical' => 0, 'found_major' => 0, 'found_minor' => 0,
            ], $aql),
        ], $extra));
    }

    public function test_major_defects_over_ac_now_fail(): void
    {
        [$inspection, $run] = $this->runWithAqlSection();

        $this->save($inspection, $run, ['found_major' => 6])->assertRedirect();

        $aql = $run->aql()->firstOrFail();
        $this->assertSame('J', $aql->code_letter);
        $this->assertSame(5, (int) $aql->ac_major);
        $this->assertSame(6, (int) $aql->re_major);
        $this->assertSame(0, (int) $aql->ac_critical);
        $this->assertSame(200, $aql->sample_size); // critical 0.065 arrow → n=200
        $this->assertSame('Fail', $aql->verdict);
    }

    public function test_one_critical_defect_fails(): void
    {
        [$inspection, $run] = $this->runWithAqlSection();

        $this->save($inspection, $run, ['found_critical' => 1]);

        $this->assertSame('Fail', $run->aql()->value('verdict'));
    }

    public function test_float_style_aql_values_from_old_forms_still_resolve(): void
    {
        [$inspection, $run] = $this->runWithAqlSection();

        $this->save($inspection, $run, ['aql_major' => '1', 'aql_minor' => '4', 'found_major' => 3]);

        $aql = $run->aql()->firstOrFail();
        $this->assertSame(2, (int) $aql->ac_major); // AQL 1.0 at J = 2/3
        $this->assertSame('Fail', $aql->verdict);
    }

    public function test_zero_defects_is_pending_until_the_aql_section_is_complete(): void
    {
        [$inspection, $run, $aqlSection] = $this->runWithAqlSection();

        $this->save($inspection, $run, []);
        $this->assertSame('Pending', $run->aql()->value('verdict'));

        $this->save($inspection, $run, [], ['sections' => [$aqlSection->id => ['status' => 'complete']]]);
        $this->assertSame('Pass', $run->aql()->value('verdict'));
    }

    public function test_each_severity_plan_is_stored_and_printed(): void
    {
        [$inspection, $run] = $this->runWithAqlSection();

        $this->save($inspection, $run, ['aql_critical' => 'not_allowed', 'found_major' => 2]);

        $aql = $run->aql()->firstOrFail();
        // Lot 1000, level II => J (n=80); critical "not allowed" uses the base plan.
        $this->assertSame(80, $aql->sample_size_critical);
        $this->assertSame('J', $aql->code_letter_critical);
        $this->assertSame(80, $aql->sample_size_major);
        $this->assertSame(80, $aql->sample_size_minor);
        $this->assertSame(\App\Services\Inspection\AqlCalculationService::STANDARD_REF, $aql->standard_ref);

        $this->save($inspection, $run, ['aql_critical' => '0.065']);
        $aql->refresh();
        $this->assertSame(200, $aql->sample_size_critical); // arrow to code L
        $this->assertSame('L', $aql->code_letter_critical);

        $html = view('exports.inspection-run-pdf', [
            'inspection' => $inspection->load(['inspectionType', 'inspectors', 'customerOrders']),
            'runs' => collect([$run->fresh()->load(['sample.customer', 'sample.category', 'sampleColor', 'runSections.section', 'runSections.attachments', 'runSections.defects.defect', 'aql.sizeBreakdowns'])]),
            'imgBase64' => fn () => null, 'defects' => collect(), 'logoBase64' => null,
        ])->render();

        $this->assertStringContainsString('Sampling basis', $html);
        $this->assertStringContainsString(e(\App\Services\Inspection\AqlCalculationService::STANDARD_REF), $html);
        $this->assertStringContainsString('switching rules not applied', $html);
        $this->assertMatchesRegularExpression('/Critical<\/td>\s*<td>0\.065%<\/td>\s*<td>L<\/td>\s*<td>200<\/td>/', $html);
    }

    public function test_not_allowed_is_printed_as_words_not_minus_one(): void
    {
        [$inspection, $run] = $this->runWithAqlSection();
        $this->save($inspection, $run, ['aql_critical' => 'not_allowed']);

        $html = view('exports.inspection-run-pdf', [
            'inspection' => $inspection->load(['inspectionType', 'inspectors', 'customerOrders']),
            'runs' => collect([$run->fresh()->load(['sample.customer', 'sample.category', 'sampleColor', 'runSections.section', 'runSections.attachments', 'runSections.defects.defect', 'aql.sizeBreakdowns'])]),
            'imgBase64' => fn () => null, 'defects' => collect(), 'logoBase64' => null,
        ])->render();

        $this->assertStringNotContainsString('-1%', $html);
        $this->assertStringContainsString('Not allowed', $html);
    }
}
