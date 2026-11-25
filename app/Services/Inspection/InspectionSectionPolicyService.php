<?php

namespace App\Services\Inspection;

use App\Models\InspectionSection;

/**
 * Section skip policy and completeness, enforced on the server (audit F-18, docs/QC_RULES.md).
 *
 *  mandatory  — must be 'complete' before the run can be finished; cannot be skipped
 *  optional   — need not be completed; cannot be skipped (no Skip action)
 *  skippable  — must be 'complete' or skipped ('na') before the run can be finished
 */
class InspectionSectionPolicyService
{
    public static function policy(?InspectionSection $section): string
    {
        return $section?->skip_policy ?: 'mandatory';
    }

    public static function allowsStatus(?InspectionSection $section, ?string $status): bool
    {
        return $status !== 'na' || self::policy($section) === 'skippable';
    }

    /**
     * Names of the sections that stop a run from being finished.
     *
     * @param  iterable<array{section: ?InspectionSection, status: string}>  $sections
     * @return string[]
     */
    public static function blockingFinish(iterable $sections): array
    {
        $blocking = [];

        foreach ($sections as $item) {
            $done = match (self::policy($item['section'])) {
                'optional' => true,
                'skippable' => in_array($item['status'], ['complete', 'na'], true),
                default => $item['status'] === 'complete',
            };

            if (! $done) {
                $blocking[] = $item['section']?->name ?? 'Unnamed section';
            }
        }

        return $blocking;
    }
}
