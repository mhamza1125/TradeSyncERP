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

    /** Inspector's assessment choice (Overall Article Result) => run verdict. "Pending" = no assessment. */
    private const ASSESSMENT_MAP = [
        'Pass' => 'Pass',
        'Fail' => 'Fail',
        'Conditional Pass' => 'Conditional',
        'Re-Inspection Required' => 'Re-Inspection',
    ];

    /** The inspector's assessment as a run verdict, or null when none was given. */
    public static function fromAssessment(?string $choice): ?string
    {
        return $choice === null ? null : (self::ASSESSMENT_MAP[$choice] ?? null);
    }

    /**
     * The run's decision (Q15 = a): the computed verdict (AQL, then defect rows). Only when
     * nothing computes (Pending) does the inspector's assessment become the verdict (A1).
     */
    public static function decide(string $computed, ?string $assessment): string
    {
        return $computed !== 'Pending' ? $computed : ($assessment ?? 'Pending');
    }

    /** An assessment contradicts the decision when both exist and differ (a reason is then required). */
    public static function contradicts(?string $assessment, ?string $decision): bool
    {
        return $assessment !== null && $decision !== null && $decision !== 'Pending' && $assessment !== $decision;
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
