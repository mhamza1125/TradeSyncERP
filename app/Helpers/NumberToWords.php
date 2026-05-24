<?php

namespace App\Helpers;

/**
 * Converts a numeric amount to words using South Asian numbering (Crore / Lac / Thousand).
 * Handles integers and two decimal places (paise/cents).
 */
class NumberToWords
{
    private static array $ones = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen',
        'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private static array $tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
        'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    private static function below100(int $n): string
    {
        if ($n < 20) {
            return self::$ones[$n];
        }
        $t = self::$tens[(int) ($n / 10)];
        $o = $n % 10;
        return $o ? "{$t} " . self::$ones[$o] : $t;
    }

    private static function below1000(int $n): string
    {
        if ($n < 100) {
            return self::below100($n);
        }
        $h     = (int) ($n / 100);
        $rest  = $n % 100;
        $str   = self::$ones[$h] . ' Hundred';
        return $rest ? $str . ' ' . self::below100($rest) : $str;
    }

    /**
     * Convert a numeric amount to South Asian words.
     *
     * @param  float   $amount      The amount to convert
     * @param  string  $currency    Currency name prefix (e.g. "Rupee", "Dollar")
     * @param  string  $subunit     Sub-unit name (e.g. "Paisa", "Cent")
     */
    public static function convert(
        float  $amount,
        string $currency = 'Rupee',
        string $subunit  = 'Paisa'
    ): string {
        if ($amount < 0) {
            return 'Minus ' . self::convert(abs($amount), $currency, $subunit);
        }

        $whole  = (int) $amount;
        $frac   = (int) round(($amount - $whole) * 100);

        if ($whole === 0 && $frac === 0) {
            return $currency . ' Zero Only';
        }

        $parts = [];

        // Crores (10,000,000)
        $crores = (int) ($whole / 10_000_000);
        $whole %= 10_000_000;

        // Lacs (100,000)
        $lacs = (int) ($whole / 100_000);
        $whole %= 100_000;

        // Thousands
        $thousands = (int) ($whole / 1_000);
        $remainder = $whole % 1_000;

        if ($crores)    $parts[] = self::below1000($crores) . ' Crore';
        if ($lacs)      $parts[] = self::below100($lacs) . ' Lac';
        if ($thousands) $parts[] = self::below1000($thousands) . ' Thousand';
        if ($remainder) $parts[] = self::below1000($remainder);

        $result = $currency . ' ' . implode(' ', $parts);

        if ($frac > 0) {
            $result .= ' and ' . self::below100($frac) . ' ' . $subunit;
        }

        return $result . ' Only';
    }

    /**
     * Derive the currency word-name from a currency code.
     * Falls back to the code itself when not mapped.
     */
    public static function currencyName(string $code): string
    {
        return match(strtoupper($code)) {
            'PKR'  => 'Rupee',
            'USD'  => 'Dollar',
            'EUR'  => 'Euro',
            'GBP'  => 'Pound',
            'AED'  => 'Dirham',
            'SAR'  => 'Riyal',
            'CNY'  => 'Yuan',
            default => $code,
        };
    }

    /**
     * Derive the sub-unit word from a currency code.
     */
    public static function subunitName(string $code): string
    {
        return match(strtoupper($code)) {
            'PKR'  => 'Paisa',
            'USD'  => 'Cent',
            'EUR'  => 'Cent',
            'GBP'  => 'Pence',
            'AED'  => 'Fils',
            'SAR'  => 'Halalas',
            default => 'Cents',
        };
    }
}
