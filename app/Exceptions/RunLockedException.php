<?php

namespace App\Exceptions;

use RuntimeException;

/** A finished inspection run is read-only until it is reopened (docs/QC_RULES.md). */
class RunLockedException extends RuntimeException
{
    public function __construct(string $message = 'This run is finished and read-only. Reopen it to make changes.')
    {
        parent::__construct($message);
    }
}
