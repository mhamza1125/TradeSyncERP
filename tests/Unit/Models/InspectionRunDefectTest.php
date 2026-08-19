<?php

namespace Tests\Unit\Models;

use App\Models\InspectionRunDefect;
use PHPUnit\Framework\TestCase;

/**
 * Covers the defect-derived fallback branch of
 * InspectionRunController::resolveRunVerdict() — extracted as a pure
 * function on InspectionRunDefect so it can be verified without a database.
 * Rule (unchanged from before the size/carton/status/disposition columns
 * were added): any Critical/Major defect => Fail, Minor/Functional-only
 * => Conditional, no defects => Pass.
 */
class InspectionRunDefectTest extends TestCase
{
    public function test_no_defects_recorded_is_pass(): void
    {
        $this->assertSame('Pass', InspectionRunDefect::verdictFromSeverities(collect()));
    }

    public function test_any_critical_defect_is_fail(): void
    {
        $severities = collect(['minor', 'critical', 'minor']);

        $this->assertSame('Fail', InspectionRunDefect::verdictFromSeverities($severities));
    }

    public function test_any_major_defect_is_fail(): void
    {
        $severities = collect(['functional', 'major']);

        $this->assertSame('Fail', InspectionRunDefect::verdictFromSeverities($severities));
    }

    public function test_minor_only_defects_are_conditional(): void
    {
        $severities = collect(['minor', 'minor']);

        $this->assertSame('Conditional', InspectionRunDefect::verdictFromSeverities($severities));
    }

    public function test_functional_only_defects_are_conditional(): void
    {
        $severities = collect(['functional']);

        $this->assertSame('Conditional', InspectionRunDefect::verdictFromSeverities($severities));
    }

    public function test_null_severities_are_treated_like_minor(): void
    {
        // A backfilled row whose original JSON had no valid severity value.
        $severities = collect([null, 'minor']);

        $this->assertSame('Conditional', InspectionRunDefect::verdictFromSeverities($severities));
    }
}
