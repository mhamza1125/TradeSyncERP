<?php

namespace App\Services\Finance;

use App\Models\Account;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Result of LedgerService::statement(). Money values are decimal strings. */
final class LedgerStatement
{
    public function __construct(
        public readonly Account $account,
        public readonly string $opening,
        public readonly string $closing,
        public readonly string $totalIn,
        public readonly string $totalOut,
        public readonly Collection $rows,
        public readonly ?LengthAwarePaginator $paginator,
    ) {}
}
