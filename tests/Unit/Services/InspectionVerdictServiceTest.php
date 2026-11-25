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

    public function test_final_review_choices_map_to_distinct_run_verdicts(): void
    {
        $this->assertSame('Pass', InspectionVerdictService::fromFinalReview('Pass'));
        $this->assertSame('Fail', InspectionVerdictService::fromFinalReview('Fail'));
        $this->assertSame('Conditional', InspectionVerdictService::fromFinalReview('Conditional Pass'));
        $this->assertSame('Re-Inspection', InspectionVerdictService::fromFinalReview('Re-Inspection Required'));
        $this->assertNull(InspectionVerdictService::fromFinalReview(null));
        $this->assertNull(InspectionVerdictService::fromFinalReview('Something else'));
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
