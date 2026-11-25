<?php

namespace App\Services\Inspection;

/**
 * Run verdicts and the inspection overall status — the one place these rules live
 * (docs/QC_RULES.md). Pure functions: no database access.
 */
class InspectionVerdictService
{
    /** inspection_runs.verdict */
    public const RUN_VERDICTS = ['Pending', 'Pass', 'Fail', 'Conditional', 'Re-Inspection'];

    /** inspections.overall_status ('N/A' is only for inspection types without a verdict) */
    public const OVERALL_STATUSES = ['Pending', 'Pass', 'Fail', 'Conditional', 'Re-Inspection', 'N/A'];

    /** Roll-up precedence, strongest first. An unfinished run counts as Pending. */
    private const PRECEDENCE = ['Fail', 'Re-Inspection', 'Conditional', 'Pending', 'Pass'];

    /** Final Review "overall_verdict" choice => run verdict. */
    private const FINAL_REVIEW_MAP = [
        'Pass' => 'Pass',
        'Fail' => 'Fail',
        'Conditional Pass' => 'Conditional',
        'Re-Inspection Required' => 'Re-Inspection',
    ];

    public static function fromFinalReview(?string $choice): ?string
    {
        return $choice === null ? null : (self::FINAL_REVIEW_MAP[$choice] ?? null);
    }

    /**
     * Overall status from the runs: Fail > Re-Inspection > Conditional > Pending > Pass.
     * Conditional and Re-Inspection never roll up to Pass. No runs => Pending.
     *
     * @param  iterable<array{verdict: ?string, finished: bool}>  $runs
     */
    public static function rollUp(iterable $runs): string
    {
        $seen = [];
        foreach ($runs as $run) {
            $verdict = $run['finished'] ? ($run['verdict'] ?? 'Pending') : 'Pending';
            $seen[in_array($verdict, self::PRECEDENCE, true) ? $verdict : 'Pending'] = true;
        }

        if ($seen === []) {
            return 'Pending';
        }

        foreach (self::PRECEDENCE as $status) {
            if (isset($seen[$status])) {
                return $status;
            }
        }

        return 'Pending';
    }

    /**
     * How a status is shown: label for PDFs/badges, PDF verdict-box class, Bootstrap colour.
     *
     * @return array{label: string, pdf: string, badge: string}
     */
    public static function display(?string $status): array
    {
        return match ($status) {
            'Pass' => ['label' => 'PASS', 'pdf' => 'vb-pass', 'badge' => 'success'],
            'Fail' => ['label' => 'FAIL', 'pdf' => 'vb-fail', 'badge' => 'danger'],
            'Conditional' => ['label' => 'CONDITIONAL', 'pdf' => 'vb-conditional', 'badge' => 'warning'],
            'Re-Inspection' => ['label' => 'RE-INSPECTION', 'pdf' => 'vb-conditional', 'badge' => 'warning'],
            'N/A' => ['label' => 'N/A', 'pdf' => 'vb-pending', 'badge' => 'secondary'],
            default => ['label' => 'PENDING', 'pdf' => 'vb-pending', 'badge' => 'secondary'],
        };
    }
}
