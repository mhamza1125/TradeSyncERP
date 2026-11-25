<?php

namespace App\Models\Concerns;

use App\Models\InspectionRun;
use App\Services\Inspection\InspectionRunLockService;

/**
 * Model-level guard for records that belong to an inspection run: once the run is
 * finished, they can't be created, changed or deleted (defence in depth behind the
 * controller checks; see InspectionRunLockService).
 */
trait GuardsFinishedRun
{
    abstract public function owningRun(): ?InspectionRun;

    protected static function bootGuardsFinishedRun(): void
    {
        $guard = fn ($model) => InspectionRunLockService::ensureEditable($model->owningRun());

        static::saving($guard);
        static::deleting($guard);
    }
}
