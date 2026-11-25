<?php

namespace App\Console\Commands;

use App\Services\Inspection\AqlCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * READ-ONLY report: what every stored run AQL verdict would be under the corrected AQL tables.
 *
 * It never writes. Everything runs inside START TRANSACTION READ ONLY (the database itself
 * refuses any write) and is rolled back. There is deliberately no --fix / --write option:
 * historical records are not changed by this command (audit F-03).
 */
class QcAqlReevaluate extends Command
{
    protected $signature = 'qc:aql-reevaluate
        {--summary : Print only the aggregate counts (no report numbers or customers)}';

    protected $description = 'READ-ONLY: list stored run AQL verdicts next to the verdict recomputed with the corrected AQL tables';

    public function handle(AqlCalculationService $aql): int
    {
        $connection = DB::connection();
        $this->line('Database: '.$connection->getDatabaseName().' (read-only transaction, rolled back at the end)');

        // Outside tests no transaction is open, so the whole read runs in a READ ONLY
        // transaction. (Inside a test's wrapping transaction, START TRANSACTION would
        // implicitly commit it, so there we rely on the queries being SELECT-only.)
        $ownTransaction = $connection->transactionLevel() === 0;
        if ($ownTransaction) {
            $connection->unprepared('START TRANSACTION READ ONLY');
        }

        try {
            $rows = $this->rows($aql);
        } finally {
            if ($ownTransaction) {
                $connection->unprepared('ROLLBACK');
            }
        }

        if (! $this->option('summary')) {
            $this->table(
                ['Run', 'Report', 'Customer', 'Date', 'Stored AQL verdict', 'Recomputed', 'Changed'],
                array_map(fn ($r) => [$r['run_id'], $r['report'], $r['customer'], $r['date'], $r['stored'], $r['recomputed'], $r['changed'] ? 'yes' : 'no'], $rows)
            );
        }

        $changed = array_filter($rows, fn ($r) => $r['changed']);
        $transitions = [];
        foreach ($changed as $r) {
            $key = "{$r['stored']} -> {$r['recomputed']}";
            $transitions[$key] = ($transitions[$key] ?? 0) + 1;
        }

        $this->info(sprintf('Runs with an AQL record: %d | changed: %d | unchanged: %d', count($rows), count($changed), count($rows) - count($changed)));
        foreach ($transitions as $t => $n) {
            $this->line("  {$t}: {$n}");
        }

        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function rows(AqlCalculationService $aql): array
    {
        $aqlSectionComplete = DB::table('inspection_run_sections as rs')
            ->join('inspection_sections as sec', 'sec.id', '=', 'rs.inspection_section_id')
            ->whereColumn('rs.inspection_run_id', 'r.id')
            ->where('sec.section_type', 'aql')
            ->where('rs.status', 'complete')
            ->selectRaw('1');

        $records = DB::table('inspection_run_aql as a')
            ->join('inspection_runs as r', 'r.id', '=', 'a.inspection_run_id')
            ->join('inspections as i', 'i.id', '=', 'r.inspection_id')
            ->leftJoin('samples as s', 's.id', '=', 'r.sample_id')
            ->leftJoin('customers as c', 'c.id', '=', 's.customer_id')
            ->select([
                'a.*', 'r.id as run_id', 'r.completed_at', 'i.report_number', 'i.inspection_date', 'c.customer_name',
            ])
            ->selectSub($aqlSectionComplete->limit(1), 'aql_section_complete')
            ->orderBy('a.id')
            ->get();

        return $records->map(function ($a) use ($aql) {
            // Stored sentinel -1 = "Not Allowed" (Ac 0 / Re 1).
            $level = fn ($v) => $v === null ? null : ((float) $v === -1.0 ? 'not_allowed' : (string) $v);

            $plan = $aql->calculate(
                (int) $a->lot_size,
                $a->inspection_level,
                $level($a->aql_critical),
                $level($a->aql_major),
                $level($a->aql_minor),
            );

            $recomputed = $aql->verdict(
                (int) $a->found_critical, (int) $a->found_major, (int) $a->found_minor,
                $plan['critical']['ac'] ?? null, $plan['major']['ac'] ?? null, $plan['minor']['ac'] ?? null,
                complete: $a->completed_at !== null || (bool) $a->aql_section_complete,
            );

            return [
                'run_id' => $a->run_id,
                'report' => $a->report_number,
                'customer' => $a->customer_name,
                'date' => $a->inspection_date,
                'stored' => $a->verdict,
                'recomputed' => $recomputed,
                'changed' => $a->verdict !== $recomputed,
            ];
        })->all();
    }
}
