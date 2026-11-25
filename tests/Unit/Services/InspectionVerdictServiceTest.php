<?php

namespace Tests\Unit\Services;

use App\Services\Inspection\InspectionVerdictService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Audit F-04: run verdicts roll up to the inspection's overall status (docs/QC_RULES.md). */
class InspectionVerdictServiceTest extends TestCase
{
    public static function cases(): array
    {
        $r = fn ($v, $f = true) => ['verdict' => $v, 'finished' => $f];

        return [
            'no runs' => [[], 'Pending'],
            'all pass' => [[$r('Pass'), $r('Pass')], 'Pass'],
            'conditional never rolls up to pass' => [[$r('Pass'), $r('Conditional')], 'Conditional'],
            're-inspection never rolls up to pass' => [[$r('Pass'), $r('Re-Inspection')], 'Re-Inspection'],
            're-inspection beats conditional' => [[$r('Conditional'), $r('Re-Inspection')], 'Re-Inspection'],
            'fail beats everything' => [[$r('Re-Inspection'), $r('Conditional'), $r('Fail'), $r('Pass')], 'Fail'],
            'fail beats an unfinished run' => [[$r('Fail'), $r('Pending', false)], 'Fail'],
            'unfinished run keeps it pending' => [[$r('Pass'), $r('Pass', false)], 'Pending'],
            'finished but undecided run is pending' => [[$r('Pass'), $r('Pending')], 'Pending'],
            'conditional beats pending' => [[$r('Conditional'), $r('Pass', false)], 'Conditional'],
            'unfinished run ignores its stale verdict' => [[$r('Pass'), $r('Pass', false)], 'Pending'],
        ];
    }

    #[DataProvider('cases')]
    public function test_roll_up(array $runs, string $expected): void
    {
        $this->assertSame($expected, InspectionVerdictService::rollUp($runs));
    }

    public function test_assessment_choices_map_to_distinct_run_verdicts(): void
    {
        $this->assertSame('Pass', InspectionVerdictService::fromAssessment('Pass'));
        $this->assertSame('Fail', InspectionVerdictService::fromAssessment('Fail'));
        $this->assertSame('Conditional', InspectionVerdictService::fromAssessment('Conditional Pass'));
        $this->assertSame('Re-Inspection', InspectionVerdictService::fromAssessment('Re-Inspection Required'));
        $this->assertNull(InspectionVerdictService::fromAssessment(null));
        $this->assertNull(InspectionVerdictService::fromAssessment('Something else'));
    }

    public function test_the_computed_verdict_decides_and_the_assessment_only_fills_pending(): void
    {
        $this->assertSame('Fail', InspectionVerdictService::decide('Fail', 'Pass'));
        $this->assertSame('Conditional', InspectionVerdictService::decide('Conditional', null));
        $this->assertSame('Re-Inspection', InspectionVerdictService::decide('Pending', 'Re-Inspection'));
        $this->assertSame('Pending', InspectionVerdictService::decide('Pending', null));
    }

    public function test_an_assessment_contradicts_only_a_different_real_decision(): void
    {
        $this->assertTrue(InspectionVerdictService::contradicts('Pass', 'Fail'));
        $this->assertTrue(InspectionVerdictService::contradicts('Re-Inspection', 'Fail'));
        $this->assertFalse(InspectionVerdictService::contradicts('Fail', 'Fail'));
        $this->assertFalse(InspectionVerdictService::contradicts(null, 'Fail'));
        $this->assertFalse(InspectionVerdictService::contradicts('Pass', 'Pending'));
    }

    public function test_every_status_has_a_label_and_style(): void
    {
        foreach ([...InspectionVerdictService::OVERALL_STATUSES, 'unknown'] as $status) {
            $display = InspectionVerdictService::display($status);
            $this->assertNotEmpty($display['label']);
            $this->assertNotEmpty($display['pdf']);
            $this->assertNotEmpty($display['badge']);
        }
        $this->assertSame('RE-INSPECTION', InspectionVerdictService::display('Re-Inspection')['label']);
        $this->assertNotSame(InspectionVerdictService::display('Pass')['pdf'], InspectionVerdictService::display('Conditional')['pdf']);
    }
}
