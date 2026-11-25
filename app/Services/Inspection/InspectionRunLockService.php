<?php

namespace App\Services\Inspection;

use App\Exceptions\RunLockedException;
use App\Models\InspectionRun;
use App\Models\User;

/**
 * Finished runs are read-only (owner assumption A4, audit F-08). The only way back is
 * reopen(): its own permission, a mandatory reason, and an activity-log entry.
 */
class InspectionRunLockService
{
    private static bool $bypassed = false;

    public static function isLocked(?InspectionRun $run): bool
    {
        return ! self::$bypassed && $run?->completed_at !== null;
    }

    public static function ensureEditable(?InspectionRun $run): void
    {
        if (self::isLocked($run)) {
            throw new RunLockedException;
        }
    }

    /**
     * Run $callback with the lock switched off. Only for building fixture data
     * (database/seeders/InspectionExportTestSeeder.php, tests/Support) — never for user actions.
     */
    public static function bypass(callable $callback): mixed
    {
        $previous = self::$bypassed;
        self::$bypassed = true;

        try {
            return $callback();
        } finally {
            self::$bypassed = $previous;
        }
    }

    public function reopen(InspectionRun $run, string $reason, User $by): void
    {
        $previous = ['verdict' => $run->verdict, 'completed_at' => $run->completed_at?->toDateTimeString()];

        $run->update(['completed_at' => null, 'verdict' => 'Pending']);

        activity()
            ->performedOn($run)
            ->causedBy($by)
            ->event('reopened')
            ->withProperties([
                'reason' => $reason,
                'previous_verdict' => $previous['verdict'],
                'previous_completed_at' => $previous['completed_at'],
            ])
            ->log('Run reopened');

        $run->inspection->syncOverallStatus();
    }
}
