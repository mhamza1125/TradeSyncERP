<?php

namespace Tests\Feature\Inspections;

use App\Models\InspectionRunAql;
use App\Services\Inspection\InspectionRunLockService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

class QcAqlReevaluateCommandTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    public function test_it_reports_changed_verdicts_and_writes_nothing(): void
    {
        $inspection = $this->makeInspection();

        // Stored by the old code: major Ac was null, so 6 majors "passed".
        $buggy = $this->makeRun($inspection, [], ['completed_at' => now(), 'verdict' => 'Pass']);
        InspectionRunLockService::bypass(fn () => InspectionRunAql::create([
            'inspection_run_id' => $buggy->id, 'lot_size' => 1000, 'inspection_level' => 'II',
            'sample_size' => 80, 'code_letter' => 'J', 'aql_critical' => 0.065, 'aql_major' => 2.5, 'aql_minor' => 4.0,
            'ac_critical' => null, 're_critical' => null, 'ac_major' => null, 're_major' => null, 'ac_minor' => 7, 're_minor' => 8,
            'found_critical' => 0, 'found_major' => 6, 'found_minor' => 0, 'verdict' => 'Pass',
        ]));

        // Already correct: 2 majors against Ac 5.
        $fine = $this->makeRun($inspection, [], ['completed_at' => now(), 'verdict' => 'Pass']);
        InspectionRunLockService::bypass(fn () => InspectionRunAql::create([
            'inspection_run_id' => $fine->id, 'lot_size' => 1000, 'inspection_level' => 'II',
            'sample_size' => 80, 'code_letter' => 'J', 'aql_critical' => 0.065, 'aql_major' => 2.5, 'aql_minor' => 4.0,
            'found_critical' => 0, 'found_major' => 2, 'found_minor' => 0, 'verdict' => 'Pass',
        ]));

        $before = DB::table('inspection_run_aql')->orderBy('id')->get()->toJson();

        $this->artisan('qc:aql-reevaluate', ['--summary' => true])
            ->expectsOutputToContain('Runs with an AQL record: 2 | changed: 1 | unchanged: 1')
            ->expectsOutputToContain('Pass -> Fail: 1')
            ->assertSuccessful();

        $this->assertSame($before, DB::table('inspection_run_aql')->orderBy('id')->get()->toJson());
    }

    public function test_it_has_no_write_option(): void
    {
        $definition = $this->app->make(Kernel::class)->all()['qc:aql-reevaluate']->getDefinition();

        $this->assertSame(['summary'], array_values(array_diff(array_keys($definition->getOptions()), ['help', 'silent', 'quiet', 'verbose', 'version', 'ansi', 'no-interaction', 'env'])));
    }
}
