<?php

namespace App\Services\Inspection;

/**
 * AQL sampling plans: ISO 2859-1 / ANSI-ASQ Z1.4 / MIL-STD-105, single sampling, normal inspection.
 *
 * THE single source of truth for AQL numbers in this app. The JS previews read
 * tableForJs(); nothing else may hold a copy of these tables.
 *
 * Tables I and II-A are transcribed as printed in MIL-STD-105D and cross-checked cell by
 * cell against an independent transcription (see docs/AQL_SOURCES.md).
 * VERIFY AGAINST THE PURCHASED STANDARD before relying on them contractually.
 *
 * Rules (docs/QC_RULES.md):
 *  - AQL levels are matched as canonical strings ('0.065', '2.5', ...) — never float keys or float equality.
 *  - An arrow cell uses the first plan below/above the arrow, INCLUDING that plan's sample size.
 *  - If the sample size equals or exceeds the lot size, inspect 100% (sample size = lot size).
 */
class AqlCalculationService
{
    /**
     * The basis printed on reports. The tables were verified against MIL-STD-105D and a Z1.4
     * transcription (docs/AQL_SOURCES.md); they have NOT yet been compared with ISO 2859-1:2026,
     * so the report must not claim the 2026 edition (docs/QC_STANDARDS_REFERENCE.md §1.1).
     */
    public const STANDARD_REF = 'ISO 2859-1:1999 / ANSI/ASQ Z1.4-2003 (R2018), single sampling';

    /** Supported AQL levels, as canonical string keys. */
    public const AQL_LEVELS = ['0.065', '0.10', '0.15', '0.25', '0.40', '0.65', '1.0', '1.5', '2.5', '4.0', '6.5'];

    public const INSPECTION_LEVELS = ['I', 'II', 'III', 'S1', 'S2', 'S3', 'S4'];

    /** AQL key => value in thousandths, so matching is integer comparison. */
    private const AQL_THOUSANDTHS = [
        '0.065' => 65, '0.10' => 100, '0.15' => 150, '0.25' => 250, '0.40' => 400, '0.65' => 650,
        '1.0' => 1000, '1.5' => 1500, '2.5' => 2500, '4.0' => 4000, '6.5' => 6500,
    ];

    /**
     * Table I — sample size code letters.
     * [min lot, max lot (null = and over), S1, S2, S3, S4, I, II, III]
     */
    private const TABLE_I = [
        [2,      8,      'A', 'A', 'A', 'A', 'A', 'A', 'B'],
        [9,      15,     'A', 'A', 'A', 'A', 'A', 'B', 'C'],
        [16,     25,     'A', 'A', 'B', 'B', 'B', 'C', 'D'],
        [26,     50,     'A', 'B', 'B', 'C', 'C', 'D', 'E'],
        [51,     90,     'B', 'B', 'C', 'C', 'C', 'E', 'F'],
        [91,     150,    'B', 'B', 'C', 'D', 'D', 'F', 'G'],
        [151,    280,    'B', 'C', 'D', 'E', 'E', 'G', 'H'],
        [281,    500,    'B', 'C', 'D', 'E', 'F', 'H', 'J'],
        [501,    1200,   'C', 'C', 'E', 'F', 'G', 'J', 'K'],
        [1201,   3200,   'C', 'D', 'E', 'G', 'H', 'K', 'L'],
        [3201,   10000,  'C', 'D', 'F', 'G', 'J', 'L', 'M'],
        [10001,  35000,  'C', 'D', 'F', 'H', 'K', 'M', 'N'],
        [35001,  150000, 'D', 'E', 'G', 'J', 'L', 'N', 'P'],
        [150001, 500000, 'D', 'E', 'G', 'J', 'M', 'P', 'Q'],
        [500001, null,   'D', 'E', 'H', 'K', 'N', 'Q', 'R'],
    ];

    private const TABLE_I_COLUMN = ['S1' => 2, 'S2' => 3, 'S3' => 4, 'S4' => 5, 'I' => 6, 'II' => 7, 'III' => 8];

    /** Code letter => sample size (Table II-A). Order matters: it is the row order of Table II-A. */
    private const SAMPLE_SIZES = [
        'A' => 2,   'B' => 3,   'C' => 5,   'D' => 8,
        'E' => 13,  'F' => 20,  'G' => 32,  'H' => 50,
        'J' => 80,  'K' => 125, 'L' => 200, 'M' => 315,
        'N' => 500, 'P' => 800, 'Q' => 1250, 'R' => 2000,
    ];

    private const D = 'down'; // use first sampling plan below the arrow

    private const U = 'up';   // use first sampling plan above the arrow

    /**
     * Table II-A — single sampling plans for normal inspection, as printed.
     * Columns: 0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5
     * Cells: [Ac, Re] or an arrow.
     */
    private const TABLE_II_A = [
        'A' => [self::D, self::D, self::D, self::D, self::D, self::D, self::D, self::D, self::D, self::D, [0, 1]],
        'B' => [self::D, self::D, self::D, self::D, self::D, self::D, self::D, self::D, self::D, [0, 1], self::U],
        'C' => [self::D, self::D, self::D, self::D, self::D, self::D, self::D, self::D, [0, 1], self::U, self::D],
        'D' => [self::D, self::D, self::D, self::D, self::D, self::D, self::D, [0, 1], self::U, self::D, [1, 2]],
        'E' => [self::D, self::D, self::D, self::D, self::D, self::D, [0, 1], self::U, self::D, [1, 2], [2, 3]],
        'F' => [self::D, self::D, self::D, self::D, self::D, [0, 1], self::U, self::D, [1, 2], [2, 3], [3, 4]],
        'G' => [self::D, self::D, self::D, self::D, [0, 1], self::U, self::D, [1, 2], [2, 3], [3, 4], [5, 6]],
        'H' => [self::D, self::D, self::D, [0, 1], self::U, self::D, [1, 2], [2, 3], [3, 4], [5, 6], [7, 8]],
        'J' => [self::D, self::D, [0, 1], self::U, self::D, [1, 2], [2, 3], [3, 4], [5, 6], [7, 8], [10, 11]],
        'K' => [self::D, [0, 1], self::U, self::D, [1, 2], [2, 3], [3, 4], [5, 6], [7, 8], [10, 11], [14, 15]],
        'L' => [[0, 1], self::U, self::D, [1, 2], [2, 3], [3, 4], [5, 6], [7, 8], [10, 11], [14, 15], [21, 22]],
        'M' => [self::U, self::D, [1, 2], [2, 3], [3, 4], [5, 6], [7, 8], [10, 11], [14, 15], [21, 22], self::U],
        'N' => [self::D, [1, 2], [2, 3], [3, 4], [5, 6], [7, 8], [10, 11], [14, 15], [21, 22], self::U, self::U],
        'P' => [[1, 2], [2, 3], [3, 4], [5, 6], [7, 8], [10, 11], [14, 15], [21, 22], self::U, self::U, self::U],
        'Q' => [[2, 3], [3, 4], [5, 6], [7, 8], [10, 11], [14, 15], [21, 22], self::U, self::U, self::U, self::U],
        'R' => [[3, 4], [5, 6], [7, 8], [10, 11], [14, 15], [21, 22], self::U, self::U, self::U, self::U, self::U],
    ];

    /**
     * Canonical AQL key for a user/DB value ('2.5', '2.500', 2.5, '0.1' ...), or null if unsupported.
     * Compared in integer thousandths — no float keys, no float equality.
     */
    public static function aqlKey(float|int|string|null $aql): ?string
    {
        if ($aql === null || ! is_numeric($aql)) {
            return null;
        }

        $thousandths = (int) round(((float) $aql) * 1000);
        $key = array_search($thousandths, self::AQL_THOUSANDTHS, true);

        return $key === false ? null : $key;
    }

    /** Code letter for a lot size and inspection level (Table I), or null if the lot is below the table. */
    public function resolveCodeLetter(int $lotSize, string $level = 'II'): ?string
    {
        $col = self::TABLE_I_COLUMN[$level] ?? self::TABLE_I_COLUMN['II'];

        foreach (self::TABLE_I as $row) {
            if ($lotSize >= $row[0] && ($row[1] === null || $lotSize <= $row[1])) {
                return $row[$col];
            }
        }

        return null;
    }

    public function sampleSizeFromCode(string $codeLetter): int
    {
        return self::SAMPLE_SIZES[$codeLetter] ?? 0;
    }

    /**
     * The plan for a code letter and AQL from Table II-A, following arrows. Not capped at
     * any lot size (see calculate()).
     *
     * @return array{ac:int, re:int, sample_size:int, code_letter:string}|null
     */
    public function planFor(string $codeLetter, float|int|string $aql): ?array
    {
        $key = self::aqlKey($aql);
        $letters = array_keys(self::SAMPLE_SIZES);
        $row = array_search($codeLetter, $letters, true);

        if ($key === null || $row === false) {
            return null;
        }

        $col = array_search($key, self::AQL_LEVELS, true);
        $cell = self::TABLE_II_A[$letters[$row]][$col];

        // Follow the arrow to the first cell with numbers, in the arrow's direction.
        $step = $cell === self::D ? 1 : ($cell === self::U ? -1 : 0);
        while ($step !== 0 && ! is_array($cell)) {
            $row += $step;
            if (! isset($letters[$row])) {
                return null;
            }
            $cell = self::TABLE_II_A[$letters[$row]][$col];
        }

        return [
            'ac' => $cell[0],
            're' => $cell[1],
            'sample_size' => self::SAMPLE_SIZES[$letters[$row]],
            'code_letter' => $letters[$row],
        ];
    }

    /**
     * Full plan for a lot. Each severity gets its own plan (an arrow can move it to a larger
     * sample); every sample size is capped at the lot size (100% inspection).
     *
     * 'sample_size' is the number of units to inspect so that every plan in use can be
     * applied: the largest per-severity sample size (owner question Q12).
     *
     * @return array{lot_size:int, level:string, code_letter:?string, sample_size:int, full_inspection:bool,
     *               critical:?array, major:?array, minor:?array}
     */
    public function calculate(
        int $lotSize,
        string $level = 'II',
        float|string|null $aqlCritical = '0.065',
        float|string|null $aqlMajor = '2.5',
        float|string|null $aqlMinor = '4.0'
    ): array {
        $codeLetter = $this->resolveCodeLetter($lotSize, $level);

        $plans = [];
        foreach (['critical' => $aqlCritical, 'major' => $aqlMajor, 'minor' => $aqlMinor] as $severity => $aql) {
            $plans[$severity] = $this->severityPlan($codeLetter, $aql, $lotSize);
        }

        $base = $codeLetter ? min($this->sampleSizeFromCode($codeLetter), $lotSize) : 0;
        $sampleSize = max([$base, ...array_map(fn ($p) => $p['sample_size'] ?? 0, $plans)]);

        return [
            'lot_size' => $lotSize,
            'level' => $level,
            'code_letter' => $codeLetter,
            'sample_size' => $sampleSize,
            'full_inspection' => $codeLetter !== null && $sampleSize >= $lotSize,
            ...$plans,
        ];
    }

    /**
     * calculate() for string inputs from forms, honouring the "not_allowed" option
     * (Ac 0 / Re 1 at the base sample size: any defect fails).
     */
    public function planForLot(int $lotSize, string $level, ?string $aqlCritical, ?string $aqlMajor, ?string $aqlMinor): array
    {
        return $this->calculate($lotSize, $level, $aqlCritical, $aqlMajor, $aqlMinor);
    }

    /** @return array{ac:int, re:int, sample_size:int, code_letter:string}|null */
    private function severityPlan(?string $codeLetter, float|string|null $aql, int $lotSize): ?array
    {
        if ($codeLetter === null || $aql === null || $aql === '') {
            return null;
        }

        $plan = $aql === 'not_allowed'
            ? ['ac' => 0, 're' => 1, 'sample_size' => $this->sampleSizeFromCode($codeLetter), 'code_letter' => $codeLetter]
            : $this->planFor($codeLetter, $aql);

        if ($plan !== null) {
            $plan['sample_size'] = min($plan['sample_size'], $lotSize);
        }

        return $plan;
    }

    /**
     * AQL verdict from found counts and accept numbers.
     *  - any severity with found > Ac  => Fail
     *  - defects found for a severity that has no plan (Ac null) => cannot pass => Pending
     *  - no defects: Pending while the AQL check is still open, Pass once it is marked complete
     */
    public function verdict(
        int $foundCritical,
        int $foundMajor,
        int $foundMinor,
        ?int $acCritical,
        ?int $acMajor,
        ?int $acMinor,
        bool $complete = false,
    ): string {
        $checks = [[$foundCritical, $acCritical], [$foundMajor, $acMajor], [$foundMinor, $acMinor]];

        foreach ($checks as [$found, $ac]) {
            if ($ac !== null && $found > $ac) {
                return 'Fail';
            }
        }

        foreach ($checks as [$found, $ac]) {
            if ($ac === null && $found > 0) {
                return 'Pending';
            }
        }

        if ($foundCritical + $foundMajor + $foundMinor === 0) {
            return $complete ? 'Pass' : 'Pending';
        }

        return 'Pass';
    }

    /**
     * Proportionally distribute a total sample size across a set of
     * quantities (e.g. per color/size variation), using largest-remainder
     * rounding so the parts always sum back to the sample size.
     *
     * @param  int[]  $qtys
     * @return int[] distributed counts, same order/length as $qtys
     */
    public function distributeProportionally(int $sampleSize, array $qtys): array
    {
        $totalQty = array_sum($qtys);

        if ($totalQty <= 0 || count($qtys) === 0) {
            return array_fill(0, count($qtys), 0);
        }

        $raw = array_map(fn ($q) => ($q / $totalQty) * $sampleSize, $qtys);
        $floored = array_map('intval', array_map('floor', $raw));
        $remainder = $sampleSize - array_sum($floored);

        $fracs = [];
        foreach ($raw as $i => $r) {
            $fracs[] = ['i' => $i, 'frac' => $r - floor($r)];
        }
        usort($fracs, fn ($a, $b) => $b['frac'] <=> $a['frac']);

        for ($j = 0; $j < $remainder && $j < count($fracs); $j++) {
            $floored[$fracs[$j]['i']]++;
        }

        return $floored;
    }

    /**
     * Sum checked_qty/error_qty across a run's per-size AQL breakdown rows.
     * Display/reporting only: does not feed calculate() or verdict().
     *
     * @param  array<int, array{checked_qty?: int|string, error_qty?: int|string}>  $rows
     * @return array{checked_qty: int, error_qty: int}
     */
    public function summarizeSizeBreakdown(array $rows): array
    {
        $checked = 0;
        $error = 0;

        foreach ($rows as $row) {
            $checked += (int) ($row['checked_qty'] ?? 0);
            $error += (int) ($row['error_qty'] ?? 0);
        }

        return ['checked_qty' => $checked, 'error_qty' => $error];
    }

    /**
     * Everything the JS previews need, generated from the tables above (arrows already
     * resolved), so the browser never holds its own copy of the standard.
     */
    public function tableForJs(): array
    {
        $plans = [];
        foreach (array_keys(self::SAMPLE_SIZES) as $letter) {
            foreach (self::AQL_LEVELS as $aql) {
                $p = $this->planFor($letter, $aql);
                $plans[$letter][$aql] = ['ac' => $p['ac'], 're' => $p['re'], 'n' => $p['sample_size'], 'code' => $p['code_letter']];
            }
        }

        return [
            'levels' => self::INSPECTION_LEVELS,
            'aqls' => self::AQL_LEVELS,
            'sampleSizes' => self::SAMPLE_SIZES,
            'lotSizeTable' => array_map(fn ($row) => [
                'min' => $row[0],
                'max' => $row[1],
                'codes' => array_map(fn ($col) => $row[$col], self::TABLE_I_COLUMN),
            ], self::TABLE_I),
            'plans' => $plans,
        ];
    }
}
