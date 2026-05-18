<?php

namespace App\Services\Inspection;

/**
 * ISO 2859-1 AQL sampling plan calculator.
 *
 * Supports Normal Inspection Levels I, II, III and Special Levels S1–S4.
 * AQL levels supported: 0.065, 0.10, 0.15, 0.25, 0.40, 0.65, 1.0, 1.5, 2.5, 4.0, 6.5
 */
class AqlCalculationService
{
    // Lot-size ranges → code letters per inspection level
    // Format: [min, max, level_I, level_II, level_III, S1, S2, S3, S4]
    private const LOT_SIZE_TABLE = [
        [2,          8,          'A', 'A', 'B', 'A', 'A', 'A', 'A'],
        [9,          15,         'A', 'B', 'C', 'A', 'A', 'A', 'A'],
        [16,         25,         'B', 'C', 'D', 'A', 'A', 'B', 'B'],
        [26,         50,         'C', 'D', 'E', 'A', 'B', 'B', 'C'],
        [51,         90,         'C', 'E', 'F', 'B', 'B', 'C', 'C'],
        [91,         150,        'D', 'F', 'G', 'B', 'B', 'C', 'D'],
        [151,        280,        'E', 'G', 'H', 'B', 'C', 'D', 'E'],
        [281,        500,        'F', 'H', 'J', 'B', 'C', 'D', 'E'],
        [501,        1200,       'G', 'J', 'K', 'C', 'C', 'E', 'F'],
        [1201,       3200,       'H', 'K', 'L', 'C', 'D', 'E', 'G'],
        [3201,       10000,      'J', 'L', 'M', 'C', 'D', 'F', 'G'],
        [10001,      35000,      'K', 'M', 'N', 'C', 'D', 'F', 'H'],
        [35001,      150000,     'L', 'N', 'P', 'D', 'E', 'G', 'J'],
        [150001,     500000,     'M', 'P', 'Q', 'D', 'E', 'G', 'J'],
        [500001,     PHP_INT_MAX,'N', 'Q', 'R', 'D', 'E', 'H', 'K'],
    ];

    // Code letter → sample size
    private const SAMPLE_SIZES = [
        'A' => 2,   'B' => 3,   'C' => 5,   'D' => 8,
        'E' => 13,  'F' => 20,  'G' => 32,  'H' => 50,
        'J' => 80,  'K' => 125, 'L' => 200, 'M' => 315,
        'N' => 500, 'P' => 800, 'Q' => 1250,'R' => 2000,
    ];

    /**
     * AQL Ac/Re lookup: [aql_level => [sample_size => [Ac, Re]]]
     * null means "use arrow" (insufficient sample — apply next larger size).
     */
    private const AQL_TABLE = [
        0.065 => [
            2 => null, 3 => null, 5 => null, 8 => null, 13 => null,
            20 => null, 32 => null, 50 => null, 80 => [0, 1],
            125 => [0, 1], 200 => [0, 1], 315 => [0, 1],
            500 => [0, 1], 800 => [0, 1], 1250 => [1, 2], 2000 => [1, 2],
        ],
        0.10 => [
            2 => null, 3 => null, 5 => null, 8 => null, 13 => null,
            20 => null, 32 => null, 50 => [0, 1],
            80 => [0, 1], 125 => [0, 1], 200 => [0, 1],
            315 => [0, 1], 500 => [1, 2], 800 => [1, 2], 1250 => [2, 3], 2000 => [3, 4],
        ],
        0.15 => [
            2 => null, 3 => null, 5 => null, 8 => null, 13 => null,
            20 => null, 32 => [0, 1],
            50 => [0, 1], 80 => [0, 1], 125 => [0, 1],
            200 => [1, 2], 315 => [1, 2], 500 => [2, 3], 800 => [3, 4], 1250 => [5, 6], 2000 => [7, 8],
        ],
        0.25 => [
            2 => null, 3 => null, 5 => null, 8 => null, 13 => null,
            20 => [0, 1], 32 => [0, 1],
            50 => [0, 1], 80 => [0, 1], 125 => [1, 2],
            200 => [2, 3], 315 => [3, 4], 500 => [5, 6], 800 => [7, 8], 1250 => [10, 11], 2000 => [14, 15],
        ],
        0.40 => [
            2 => null, 3 => null, 5 => null, 8 => null,
            13 => [0, 1], 20 => [0, 1], 32 => [0, 1],
            50 => [0, 1], 80 => [1, 2], 125 => [2, 3],
            200 => [3, 4], 315 => [5, 6], 500 => [7, 8], 800 => [10, 11], 1250 => [14, 15], 2000 => [21, 22],
        ],
        0.65 => [
            2 => null, 3 => null, 5 => null,
            8 => [0, 1], 13 => [0, 1], 20 => [0, 1], 32 => [1, 2],
            50 => [1, 2], 80 => [2, 3], 125 => [3, 4],
            200 => [5, 6], 315 => [7, 8], 500 => [10, 11], 800 => [14, 15], 1250 => [21, 22], 2000 => [21, 22],
        ],
        1.0 => [
            2 => null, 3 => null,
            5 => [0, 1], 8 => [0, 1], 13 => [0, 1], 20 => [1, 2], 32 => [1, 2],
            50 => [2, 3], 80 => [3, 4], 125 => [5, 6],
            200 => [7, 8], 315 => [10, 11], 500 => [14, 15], 800 => [21, 22], 1250 => [21, 22], 2000 => [21, 22],
        ],
        1.5 => [
            2 => null,
            3 => [0, 1], 5 => [0, 1], 8 => [0, 1], 13 => [1, 2], 20 => [1, 2], 32 => [2, 3],
            50 => [3, 4], 80 => [5, 6], 125 => [7, 8],
            200 => [10, 11], 315 => [14, 15], 500 => [21, 22], 800 => [21, 22], 1250 => [21, 22], 2000 => [21, 22],
        ],
        2.5 => [
            2 => [0, 1], 3 => [0, 1], 5 => [0, 1], 8 => [0, 1], 13 => [1, 2], 20 => [1, 2], 32 => [2, 3],
            50 => [3, 4], 80 => [5, 6], 125 => [7, 8],
            200 => [10, 11], 315 => [14, 15], 500 => [21, 22], 800 => [21, 22], 1250 => [21, 22], 2000 => [21, 22],
        ],
        4.0 => [
            2 => [0, 1], 3 => [0, 1], 5 => [0, 1], 8 => [0, 1], 13 => [1, 2], 20 => [2, 3], 32 => [3, 4],
            50 => [5, 6], 80 => [7, 8], 125 => [10, 11],
            200 => [14, 15], 315 => [21, 22], 500 => [21, 22], 800 => [21, 22], 1250 => [21, 22], 2000 => [21, 22],
        ],
        6.5 => [
            2 => [0, 1], 3 => [0, 1], 5 => [0, 1], 8 => [1, 2], 13 => [2, 3], 20 => [3, 4], 32 => [5, 6],
            50 => [7, 8], 80 => [10, 11], 125 => [14, 15],
            200 => [21, 22], 315 => [21, 22], 500 => [21, 22], 800 => [21, 22], 1250 => [21, 22], 2000 => [21, 22],
        ],
    ];

    private const LEVEL_INDEX = [
        'I' => 2, 'II' => 3, 'III' => 4,
        'S1' => 5, 'S2' => 6, 'S3' => 7, 'S4' => 8,
    ];

    /**
     * Resolve the code letter for a given lot size and inspection level.
     */
    public function resolveCodeLetter(int $lotSize, string $level = 'II'): ?string
    {
        $colIdx = self::LEVEL_INDEX[$level] ?? 3;

        foreach (self::LOT_SIZE_TABLE as $row) {
            if ($lotSize >= $row[0] && $lotSize <= $row[1]) {
                return $row[$colIdx];
            }
        }

        return null;
    }

    /**
     * Resolve sample size from code letter.
     */
    public function sampleSizeFromCode(string $codeLetter): int
    {
        return self::SAMPLE_SIZES[$codeLetter] ?? 0;
    }

    /**
     * Get the Ac/Re numbers for a given AQL level and sample size.
     * Returns null if the sample size is too small for that AQL level (arrow up).
     *
     * @return array{ac: int|null, re: int|null}|null
     */
    public function acReNumbers(float $aqlLevel, int $sampleSize): ?array
    {
        // Normalize AQL level key
        $key = $this->normalizeAqlKey($aqlLevel);
        if ($key === null || ! isset(self::AQL_TABLE[$key])) {
            return null;
        }

        $table = self::AQL_TABLE[$key];

        if (isset($table[$sampleSize])) {
            $entry = $table[$sampleSize];
            if ($entry === null) {
                // Arrow up: find the next larger sample size that has values
                $sizes = array_keys($table);
                sort($sizes);
                foreach ($sizes as $sz) {
                    if ($sz > $sampleSize && $table[$sz] !== null) {
                        return ['ac' => $table[$sz][0], 're' => $table[$sz][1]];
                    }
                }
                return null;
            }
            return ['ac' => $entry[0], 're' => $entry[1]];
        }

        return null;
    }

    /**
     * Full plan calculation for a lot size and set of AQL levels.
     */
    public function calculate(
        int    $lotSize,
        string $level     = 'II',
        ?float $aqlCritical = 0.065,
        ?float $aqlMajor    = 2.5,
        ?float $aqlMinor    = 4.0
    ): array {
        $codeLetter = $this->resolveCodeLetter($lotSize, $level);
        $sampleSize = $codeLetter ? $this->sampleSizeFromCode($codeLetter) : 0;

        $critical = $aqlCritical !== null ? $this->acReNumbers($aqlCritical, $sampleSize) : null;
        $major    = $aqlMajor    !== null ? $this->acReNumbers($aqlMajor,    $sampleSize) : null;
        $minor    = $aqlMinor    !== null ? $this->acReNumbers($aqlMinor,    $sampleSize) : null;

        return [
            'lot_size'    => $lotSize,
            'level'       => $level,
            'code_letter' => $codeLetter,
            'sample_size' => $sampleSize,
            'critical'    => $critical,
            'major'       => $major,
            'minor'       => $minor,
        ];
    }

    /**
     * Calculate verdict from found counts vs. accept numbers.
     */
    public function verdict(
        int  $foundCritical,
        int  $foundMajor,
        int  $foundMinor,
        ?int $acCritical,
        ?int $acMajor,
        ?int $acMinor
    ): string {
        $anyFound = ($foundCritical + $foundMajor + $foundMinor) > 0;

        if (! $anyFound) {
            return 'Pending';
        }

        $fail =
            ($acCritical !== null && $foundCritical > $acCritical) ||
            ($acMajor    !== null && $foundMajor    > $acMajor)    ||
            ($acMinor    !== null && $foundMinor    > $acMinor);

        return $fail ? 'Fail' : 'Pass';
    }

    /**
     * Return the AQL table as a JSON-safe structure for use in JavaScript.
     */
    public function tableForJs(): array
    {
        return [
            'lotSizeTable'  => self::LOT_SIZE_TABLE,
            'sampleSizes'   => self::SAMPLE_SIZES,
            'levelIndex'    => self::LEVEL_INDEX,
            'aqlTable'      => self::AQL_TABLE,
            'supportedAqls' => array_keys(self::AQL_TABLE),
        ];
    }

    private function normalizeAqlKey(float $aql): ?float
    {
        $supported = array_keys(self::AQL_TABLE);
        foreach ($supported as $key) {
            if (abs($key - $aql) < 0.001) {
                return $key;
            }
        }
        return null;
    }
}
