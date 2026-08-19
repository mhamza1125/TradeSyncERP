<?php

namespace Tests\Unit\Services;

use App\Services\Inspection\AqlCalculationService;
use PHPUnit\Framework\TestCase;

class AqlCalculationServiceTest extends TestCase
{
    private AqlCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AqlCalculationService;
    }

    // ── Baseline regression: existing single-lot behavior is untouched ──────

    public function test_calculate_matches_known_iso_2859_1_plan_for_a_typical_lot(): void
    {
        // Lot 500, Level II => code letter H, sample size 50 (ISO 2859-1 table).
        //
        // PRE-EXISTING BUG (not touched by this change, documented here so it
        // isn't mistaken for a regression): AQL_TABLE is keyed by float AQL
        // levels (0.065, 0.10, 2.5, ...), but PHP truncates float array-literal
        // keys to int, so 0.065/0.10/0.15/0.25/0.40/0.65 all collide on key 0
        // (last one wins) and 1.0/1.5 collide on key 1. normalizeAqlKey() then
        // compares the requested level against those truncated int keys with
        // `abs($key - $aql) < 0.001`, which only ever matches when the level
        // is itself a whole number — so every non-integer AQL level (0.065,
        // 2.5, 1.5, 0.65, ...) resolves to no match and acReNumbers() returns
        // null. In practice this means Critical (0.065) and Major (2.5) Ac/Re
        // — the two most commonly used severities — silently come back null
        // from calculate()/planForLot() today; only whole-number levels like
        // 4.0 (Minor's default) happen to work. Flagged separately — out of
        // scope for this test, which exists only to prove this task's changes
        // don't alter calculate()'s behavior, whatever that behavior is.
        $plan = $this->service->calculate(500, 'II', 0.065, 2.5, 4.0);

        $this->assertSame('H', $plan['code_letter']);
        $this->assertSame(50, $plan['sample_size']);
        $this->assertNull($plan['critical']);
        $this->assertNull($plan['major']);
        $this->assertSame(['ac' => 5, 're' => 6], $plan['minor']);
    }

    public function test_verdict_fails_when_found_defects_exceed_accept_number(): void
    {
        $this->assertSame('Fail', $this->service->verdict(0, 4, 0, 0, 3, 5));
        $this->assertSame('Pass', $this->service->verdict(0, 2, 0, 0, 3, 5));
        $this->assertSame('Pending', $this->service->verdict(0, 0, 0, 0, 3, 5));
    }

    public function test_distribute_proportionally_sums_back_to_sample_size(): void
    {
        $dist = $this->service->distributeProportionally(50, [300, 200]);

        $this->assertSame(50, array_sum($dist));
        $this->assertSame([30, 20], $dist);
    }

    // ── New: size-breakdown summarization is additive, not a behavior change ─

    public function test_summarize_size_breakdown_sums_checked_and_error_quantities(): void
    {
        $rows = [
            ['size_label' => 'S', 'checked_qty' => 10, 'error_qty' => 1],
            ['size_label' => 'M', 'checked_qty' => 20, 'error_qty' => 3],
            ['size_label' => 'L', 'checked_qty' => 15, 'error_qty' => 0],
        ];

        $this->assertSame(
            ['checked_qty' => 45, 'error_qty' => 4],
            $this->service->summarizeSizeBreakdown($rows)
        );
    }

    public function test_summarize_size_breakdown_treats_missing_keys_as_zero(): void
    {
        $rows = [
            ['size_label' => 'S'],
            ['size_label' => 'M', 'checked_qty' => 5],
        ];

        $this->assertSame(
            ['checked_qty' => 5, 'error_qty' => 0],
            $this->service->summarizeSizeBreakdown($rows)
        );
    }

    public function test_summarize_size_breakdown_of_empty_array_is_zero(): void
    {
        $this->assertSame(
            ['checked_qty' => 0, 'error_qty' => 0],
            $this->service->summarizeSizeBreakdown([])
        );
    }

    public function test_summarize_size_breakdown_does_not_affect_calculate_or_verdict(): void
    {
        // Sanity check that summarizeSizeBreakdown is a pure, side-effect-free
        // helper — calling it does not mutate any shared/static state that
        // calculate()/verdict() rely on.
        $before = $this->service->calculate(500, 'II', 0.065, 2.5, 4.0);
        $this->service->summarizeSizeBreakdown([
            ['checked_qty' => 999, 'error_qty' => 999],
        ]);
        $after = $this->service->calculate(500, 'II', 0.065, 2.5, 4.0);

        $this->assertSame($before, $after);
    }
}
