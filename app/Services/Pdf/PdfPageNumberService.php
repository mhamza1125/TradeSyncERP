<?php

namespace App\Services\Pdf;

use Dompdf\Canvas;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;

/**
 * Draws "Page N of M" on PDFs whose template opts in with
 * <meta name="pdf-page-numbers" content="1"> (see exports/partials/_pdf-head).
 *
 * This replaces the old <script type="text/php"> block: dompdf's isPhpEnabled is off
 * (it let template data execute as PHP), so page numbers are drawn from here instead,
 * through dompdf's end_document callback — a PHP closure, no eval.
 */
class PdfPageNumberService
{
    public const META_NAME = 'pdf-page-numbers';

    public static function attach(Dompdf $dompdf): Dompdf
    {
        $enabled = null;

        $dompdf->setCallbacks([[
            'event' => 'end_document',
            'f' => function (int $pageNumber, int $pageCount, Canvas $canvas, FontMetrics $fontMetrics) use ($dompdf, &$enabled) {
                $enabled ??= self::requested($dompdf);
                if (! $enabled) {
                    return;
                }

                $canvas->text(
                    $canvas->get_width() - 90,
                    $canvas->get_height() - 28,
                    "Page {$pageNumber} of {$pageCount}",
                    $fontMetrics->getFont('DejaVu Sans', 'normal'),
                    7,
                    [0.00, 0.69, 0.93],
                );
            },
        ]]);

        return $dompdf;
    }

    private static function requested(Dompdf $dompdf): bool
    {
        foreach ($dompdf->getDom()->getElementsByTagName('meta') as $meta) {
            if ($meta->getAttribute('name') === self::META_NAME) {
                return true;
            }
        }

        return false;
    }
}
