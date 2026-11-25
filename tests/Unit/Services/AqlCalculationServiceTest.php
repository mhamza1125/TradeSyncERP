<?php

namespace Tests\Unit\Services;

use App\Services\Inspection\AqlCalculationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * AQL sampling (ISO 2859-1 / ANSI-ASQ Z1.4 / MIL-STD-105, single sampling, normal inspection).
 *
 * The reference grids below come from SOURCE A in docs/AQL_SOURCES.md (CRAN package
 * AQLSchemes, R/AAZ14Single.R), which stores every cell already resolved (arrows replaced
 * by the target plan's n / Ac / Re). The service encodes SOURCE B (MIL-STD-105D Table II-A as
 * printed, arrows included) and resolves arrows itself, so these tests cross-check two
 * independent transcriptions. Verify against the purchased standard before relying on them.
 *
 * Replaces the old test that asserted the float-key bug (critical/major Ac = null).
 */
class AqlCalculationServiceTest extends TestCase
{
    private AqlCalculationService $service;

    private const LETTERS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R'];

    /** SOURCE A Table I: lot range => [S1, S2, S3, S4, I, II, III]. */
    private const TABLE_I = [
        [2, 8, 'A', 'A', 'A', 'A', 'A', 'A', 'B'],
        [9, 15, 'A', 'A', 'A', 'A', 'A', 'B', 'C'],
        [16, 25, 'A', 'A', 'B', 'B', 'B', 'C', 'D'],
        [26, 50, 'A', 'B', 'B', 'C', 'C', 'D', 'E'],
        [51, 90, 'B', 'B', 'C', 'C', 'C', 'E', 'F'],
        [91, 150, 'B', 'B', 'C', 'D', 'D', 'F', 'G'],
        [151, 280, 'B', 'C', 'D', 'E', 'E', 'G', 'H'],
        [281, 500, 'B', 'C', 'D', 'E', 'F', 'H', 'J'],
        [501, 1200, 'C', 'C', 'E', 'F', 'G', 'J', 'K'],
        [1201, 3200, 'C', 'D', 'E', 'G', 'H', 'K', 'L'],
        [3201, 10000, 'C', 'D', 'F', 'G', 'J', 'L', 'M'],
        [10001, 35000, 'C', 'D', 'F', 'H', 'K', 'M', 'N'],
        [35001, 150000, 'D', 'E', 'G', 'J', 'L', 'N', 'P'],
        [150001, 500000, 'D', 'E', 'G', 'J', 'M', 'P', 'Q'],
        [500001, null, 'D', 'E', 'H', 'K', 'N', 'Q', 'R'],
    ];

    private const LEVEL_COLUMNS = ['S1' => 2, 'S2' => 3, 'S3' => 4, 'S4' => 5, 'I' => 6, 'II' => 7, 'III' => 8];

    /**
     * SOURCE A single normal: per AQL, "n:Ac" for code letters A..R (Re = Ac + 1).
     */
    private const REFERENCE = [
        '0.065' => '200:0 200:0 200:0 200:0 200:0 200:0 200:0 200:0 200:0 200:0 200:0 200:0 800:1 800:1 1250:2 2000:3',
        '0.10' => '125:0 125:0 125:0 125:0 125:0 125:0 125:0 125:0 125:0 125:0 125:0 500:1 500:1 800:2 1250:3 2000:5',
        '0.15' => '80:0 80:0 80:0 80:0 80:0 80:0 80:0 80:0 80:0 80:0 315:1 315:1 500:2 800:3 1250:5 2000:7',
        '0.25' => '50:0 50:0 50:0 50:0 50:0 50:0 50:0 50:0 50:0 200:1 200:1 315:2 500:3 800:5 1250:7 2000:10',
        '0.40' => '32:0 32:0 32:0 32:0 32:0 32:0 32:0 32:0 125:1 125:1 200:2 315:3 500:5 800:7 1250:10 2000:14',
        '0.65' => '20:0 20:0 20:0 20:0 20:0 20:0 20:0 80:1 80:1 125:2 200:3 315:5 500:7 800:10 1250:14 2000:21',
        '1.0' => '13:0 13:0 13:0 13:0 13:0 13:0 50:1 50:1 80:2 125:3 200:5 315:7 500:10 800:14 1250:21 1250:21',
        '1.5' => '8:0 8:0 8:0 8:0 8:0 32:1 32:1 50:2 80:3 125:5 200:7 315:10 500:14 800:21 800:21 800:21',
        '2.5' => '5:0 5:0 5:0 5:0 20:1 20:1 32:2 50:3 80:5 125:7 200:10 315:14 500:21 500:21 500:21 500:21',
        '4.0' => '3:0 3:0 3:0 13:1 13:1 20:2 32:3 50:5 80:7 125:10 200:14 315:21 315:21 315:21 315:21 315:21',
        '6.5' => '2:0 2:0 8:1 8:1 13:2 20:3 32:5 50:7 80:10 125:14 200:21 200:21 200:21 200:21 200:21 200:21',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AqlCalculationService;
    }

    private static function reference(string $aql, string $letter): array
    {
        $cells = explode(' ', self::REFERENCE[$aql]);
        [$n, $ac] = array_map('intval', explode(':', $cells[array_search($letter, self::LETTERS, true)]));

        return ['n' => $n, 'ac' => $ac, 're' => $ac + 1];
    }

    // ── Table II-A: every AQL level × every code letter ──────────────────────

    public static function everyCell(): array
    {
        $cases = [];
        foreach (array_keys(self::REFERENCE) as $aql) {
            foreach (self::LETTERS as $letter) {
                $cases["{$aql} {$letter}"] = [$aql, $letter];
            }
        }

        return $cases;
    }

    #[DataProvider('everyCell')]
    public function test_plan_matches_reference_table(string $aql, string $letter): void
    {
        $ref = self::reference($aql, $letter);
        $plan = $this->service->planFor($letter, $aql);

        $this->assertSame($ref['ac'], $plan['ac'], 'Ac');
        $this->assertSame($ref['re'], $plan['re'], 'Re');
        $this->assertSame($ref['n'], $plan['sample_size'], 'arrow target sample size');
        $this->assertSame($ref['n'], $this->service->sampleSizeFromCode($plan['code_letter']));
    }

    // ── Table I: every lot range × every level ───────────────────────────────

    public function test_code_letters_match_reference_table_i(): void
    {
        foreach (self::TABLE_I as $row) {
            foreach (self::LEVEL_COLUMNS as $level => $col) {
                foreach (array_filter([$row[0], $row[1] ?? 10_000_000]) as $lot) {
                    $this->assertSame($row[$col], $this->service->resolveCodeLetter($lot, $level), "lot {$lot} level {$level}");
                }
            }
        }
    }

    // ── Invariants that do not depend on any source ──────────────────────────

    public function test_sample_sizes_follow_the_preferred_series(): void
    {
        $this->assertSame(
            [2, 3, 5, 8, 13, 20, 32, 50, 80, 125, 200, 315, 500, 800, 1250, 2000],
            array_map(fn ($l) => $this->service->sampleSizeFromCode($l), self::LETTERS)
        );
    }

    public function test_re_is_ac_plus_one_and_every_arrow_resolves(): void
    {
        foreach (AqlCalculationService::AQL_LEVELS as $aql) {
            foreach (self::LETTERS as $letter) {
                $plan = $this->service->planFor($letter, $aql);
                $this->assertNotNull($plan, "{$aql} {$letter} must resolve to a defined cell");
                $this->assertSame($plan['ac'] + 1, $plan['re'], "{$aql} {$letter}");
            }
        }
    }

    public function test_ac_never_decreases_as_sample_size_grows_at_a_fixed_aql(): void
    {
        foreach (AqlCalculationService::AQL_LEVELS as $aql) {
            $prevAc = -1;
            $prevN = 0;
            foreach (self::LETTERS as $letter) {
                $plan = $this->service->planFor($letter, $aql);
                $this->assertGreaterThanOrEqual($prevAc, $plan['ac'], "{$aql} {$letter}");
                $this->assertGreaterThanOrEqual($prevN, $plan['sample_size'], "{$aql} {$letter}");
                $prevAc = $plan['ac'];
                $prevN = $plan['sample_size'];
            }
        }
    }

    public function test_aql_keys_are_matched_without_float_equality(): void
    {
        foreach (['2.5', '2.50', '2.500', 2.5] as $v) {
            $this->assertSame('2.5', AqlCalculationService::aqlKey($v));
        }
        $this->assertSame('0.10', AqlCalculationService::aqlKey('0.1'));
        $this->assertSame('0.10', AqlCalculationService::aqlKey(0.10));
        $this->assertSame('1.0', AqlCalculationService::aqlKey(1));
        $this->assertSame('4.0', AqlCalculationService::aqlKey('4'));
        $this->assertSame('0.065', AqlCalculationService::aqlKey('0.065'));
        $this->assertNull(AqlCalculationService::aqlKey('3.0'));
        $this->assertNull(AqlCalculationService::aqlKey('abc'));
        $this->assertNull(AqlCalculationService::aqlKey(null));
    }

    // ── calculate(): the audit examples ──────────────────────────────────────

    public function test_typical_lot_now_has_critical_and_major_plans(): void
    {
        // Lot 1000, level II => code J (n=80). Critical 0.065 at J is an arrow to L (n=200, 0/1).
        $plan = $this->service->calculate(1000, 'II', 0.065, 2.5, 4.0);

        $this->assertSame('J', $plan['code_letter']);
        $this->assertSame(['ac' => 0, 're' => 1, 'sample_size' => 200, 'code_letter' => 'L'], $plan['critical']);
        $this->assertSame(['ac' => 5, 're' => 6, 'sample_size' => 80, 'code_letter' => 'J'], $plan['major']);
        $this->assertSame(['ac' => 7, 're' => 8, 'sample_size' => 80, 'code_letter' => 'J'], $plan['minor']);
        // Units to inspect: enough for every plan in use.
        $this->assertSame(200, $plan['sample_size']);
        $this->assertFalse($plan['full_inspection']);
    }

    public function test_aql_1_0_no_longer_returns_the_1_5_numbers(): void
    {
        $plan = $this->service->calculate(1000, 'II', null, 1.0, 1.5);

        $this->assertSame(['ac' => 2, 're' => 3, 'sample_size' => 80, 'code_letter' => 'J'], $plan['major']);
        $this->assertSame(['ac' => 3, 're' => 4, 'sample_size' => 80, 'code_letter' => 'J'], $plan['minor']);
    }

    public function test_sample_size_is_capped_at_lot_size_for_100_percent_inspection(): void
    {
        // Lot 10, level II => code B (n=3). Critical 0.065 points to n=200 > lot => inspect all 10.
        $plan = $this->service->calculate(10, 'II', 0.065, 2.5, 4.0);

        $this->assertSame('B', $plan['code_letter']);
        $this->assertSame(10, $plan['critical']['sample_size']);
        $this->assertSame(['ac' => 0, 're' => 1], ['ac' => $plan['critical']['ac'], 're' => $plan['critical']['re']]);
        $this->assertSame(5, $plan['major']['sample_size']);
        $this->assertSame(3, $plan['minor']['sample_size']);
        $this->assertSame(10, $plan['sample_size']);
        $this->assertTrue($plan['full_inspection']);
    }

    public function test_sample_never_exceeds_lot_even_for_the_base_code_letter(): void
    {
        // Lot 2, level III => code B (n=3) > lot 2.
        $plan = $this->service->calculate(2, 'III', null, null, 6.5);

        $this->assertSame(2, $plan['minor']['sample_size']);
        $this->assertSame(2, $plan['sample_size']);
        $this->assertTrue($plan['full_inspection']);
    }

    public function test_lot_below_table_range_gives_no_plan(): void
    {
        $plan = $this->service->calculate(1, 'II', 0.065, 2.5, 4.0);

        $this->assertNull($plan['code_letter']);
        $this->assertSame(0, $plan['sample_size']);
        $this->assertNull($plan['major']);
    }

    public function test_plan_for_lot_supports_not_allowed(): void
    {
        $plan = $this->service->planForLot(1000, 'II', 'not_allowed', '2.5', '4.0');

        $this->assertSame(0, $plan['critical']['ac']);
        $this->assertSame(1, $plan['critical']['re']);
        $this->assertSame(80, $plan['critical']['sample_size']);
        $this->assertSame(5, $plan['major']['ac']);
        $this->assertSame(80, $plan['sample_size']);
    }

    // ── verdict() ────────────────────────────────────────────────────────────

    public function test_verdict_fails_when_found_defects_exceed_accept_number(): void
    {
        $this->assertSame('Fail', $this->service->verdict(0, 4, 0, 0, 3, 5));
        $this->assertSame('Pass', $this->service->verdict(0, 2, 0, 0, 3, 5));
        $this->assertSame('Fail', $this->service->verdict(1, 0, 0, 0, 3, 5));
    }

    public function test_zero_defects_is_pending_until_complete_then_pass(): void
    {
        $this->assertSame('Pending', $this->service->verdict(0, 0, 0, 0, 3, 5));
        $this->assertSame('Pending', $this->service->verdict(0, 0, 0, 0, 3, 5, complete: false));
        $this->assertSame('Pass', $this->service->verdict(0, 0, 0, 0, 3, 5, complete: true));
    }

    public function test_defects_with_no_plan_for_their_severity_never_pass(): void
    {
        // The old code ignored a null Ac and returned Pass (audit F-03 / F-14).
        $this->assertSame('Pending', $this->service->verdict(1, 0, 0, null, 3, 5, complete: true));
        $this->assertSame('Fail', $this->service->verdict(1, 4, 0, null, 3, 5));
    }

    // ── JS feed: one source of truth ─────────────────────────────────────────

    public function test_table_for_js_carries_resolved_plans_from_the_same_table(): void
    {
        $js = $this->service->tableForJs();

        $this->assertSame(AqlCalculationService::AQL_LEVELS, $js['aqls']);
        foreach (AqlCalculationService::AQL_LEVELS as $aql) {
            foreach (self::LETTERS as $letter) {
                $ref = self::reference($aql, $letter);
                $this->assertSame($ref['n'], $js['plans'][$letter][$aql]['n'], "{$aql} {$letter}");
                $this->assertSame($ref['ac'], $js['plans'][$letter][$aql]['ac'], "{$aql} {$letter}");
            }
        }
        $this->assertSame('J', collect($js['lotSizeTable'])->first(fn ($r) => $r['min'] === 501)['codes']['II']);
        $this->assertNull(end($js['lotSizeTable'])['max']);
        $this->assertNotFalse(json_encode($js));
    }

    // ── Unchanged helpers ────────────────────────────────────────────────────

    public function test_distribute_proportionally_sums_back_to_sample_size(): void
    {
        $dist = $this->service->distributeProportionally(50, [300, 200]);

        $this->assertSame(50, array_sum($dist));
        $this->assertSame([30, 20], $dist);
    }

    public function test_summarize_size_breakdown_sums_checked_and_error_quantities(): void
    {
        $rows = [
            ['size_label' => 'S', 'checked_qty' => 10, 'error_qty' => 1],
            ['size_label' => 'M', 'checked_qty' => 20, 'error_qty' => 3],
            ['size_label' => 'L', 'checked_qty' => 15, 'error_qty' => 0],
        ];

        $this->assertSame(['checked_qty' => 45, 'error_qty' => 4], $this->service->summarizeSizeBreakdown($rows));
    }

    public function test_summarize_size_breakdown_treats_missing_keys_as_zero(): void
    {
        $rows = [['size_label' => 'S'], ['size_label' => 'M', 'checked_qty' => 5]];

        $this->assertSame(['checked_qty' => 5, 'error_qty' => 0], $this->service->summarizeSizeBreakdown($rows));
    }

    public function test_summarize_size_breakdown_of_empty_array_is_zero(): void
    {
        $this->assertSame(['checked_qty' => 0, 'error_qty' => 0], $this->service->summarizeSizeBreakdown([]));
    }
}
