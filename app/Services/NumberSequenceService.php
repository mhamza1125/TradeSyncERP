<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Gap-tolerant, never-reused document numbers (audit F-19). The row for a series is
 * read with SELECT … FOR UPDATE, so two simultaneous requests can't get the same value.
 * A series is created on first use from $seed (the value the old max(id)+1 logic gave).
 */
class NumberSequenceService
{
    public function next(string $name, callable $seed): int
    {
        return DB::transaction(function () use ($name, $seed) {
            $current = $this->lockedValue($name, $seed);

            DB::table('number_sequences')->where('name', $name)
                ->update(['next_value' => $current + 1, 'updated_at' => now()]);

            return $current;
        });
    }

    /** The value next() would return now, without taking it (for display only). */
    public function peek(string $name, callable $seed): int
    {
        return (int) (DB::table('number_sequences')->where('name', $name)->value('next_value') ?? $seed());
    }

    private function lockedValue(string $name, callable $seed): int
    {
        $row = DB::table('number_sequences')->where('name', $name)->lockForUpdate()->first();

        if (! $row) {
            DB::table('number_sequences')->insertOrIgnore([
                'name' => $name, 'next_value' => (int) $seed(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $row = DB::table('number_sequences')->where('name', $name)->lockForUpdate()->first();
        }

        return (int) $row->next_value;
    }
}
