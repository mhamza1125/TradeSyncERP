<?php

namespace Tests\Feature\Inspections;

use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunSection;
use App\Models\InspectionSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/**
 * RR-01 / owner decision D1 (Q28): a run with an AQL section can only be finished with a
 * valid AQL plan (lot size >= 2, a computed plan, AQL verdict not Pending). docs/QC_RULES.md §6.
 */
class FinishRequiresAqlPlanTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspection;

    private InspectionRun $run;

    private InspectionRunSection $aqlSection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Admin'));
        $this->inspection = $this->makeInspection();
        $this->run = $this->makeRun($this->inspection, [[$this->aqlLibrarySection(), [], 'pending']]);
        $this->aqlSection = $this->run->runSections()->firstOrFail();
    }

    private function aqlLibrarySection(string $policy = 'mandatory'): InspectionSection
    {
        return InspectionSection::firstOrCreate(['slug' => 'aql_sampling'], [
            'name' => 'AQL Sampling', 'section_type' => 'aql', 'default_data' => [], 'is_active' => true, 'skip_policy' => $policy,
        ]);
    }

    private function finish(?array $aql, string $status = 'complete')
    {
        $payload = ['finish_run' => 1, 'sections' => [$this->aqlSection->id => ['status' => $status]]];
        if ($aql !== null) {
            $payload['aql'] = array_merge([
                'inspection_level' => 'II', 'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0',
                'found_critical' => 0, 'found_major' => 0, 'found_minor' => 0,
            ], $aql);
        }

        return $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), $payload);
    }

    private function assertNotFinished(): void
    {
        $this->assertNull($this->run->fresh()->completed_at);
        $this->assertSame('Pending', $this->run->fresh()->verdict);
    }

    public function test_finish_is_refused_when_the_lot_size_is_blank(): void
    {
        $this->finish(['lot_size' => ''])
            ->assertRedirect()
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'not finished') && str_contains($m, 'AQL Sampling') && str_contains($m, 'lot size'));

        $this->assertNotFinished();
        // The rest of the data is still saved.
        $this->assertSame('complete', $this->aqlSection->fresh()->status);
    }

    public function test_finish_is_refused_when_no_aql_data_is_posted_at_all(): void
    {
        $this->finish(null)->assertSessionHas('error', fn ($m) => str_contains($m, 'AQL Sampling'));
        $this->assertNotFinished();
    }

    public function test_finish_is_refused_with_lot_size_zero(): void
    {
        // Lot 0 is already a validation error (min 1); nothing is saved or finished.
        $this->finish(['lot_size' => 0])->assertSessionHasErrors('aql.lot_size');
        $this->assertNotFinished();
    }

    public function test_finish_is_refused_with_lot_size_one_because_no_plan_exists(): void
    {
        $this->finish(['lot_size' => 1])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'AQL Sampling'));

        $this->assertNotFinished();
        $aql = $this->run->aql()->firstOrFail();
        $this->assertSame(1, (int) $aql->lot_size);   // saved
        $this->assertNull($aql->code_letter);          // no plan below lot 2
    }

    public function test_finish_is_allowed_with_a_valid_plan(): void
    {
        $this->finish(['lot_size' => 1000])->assertSessionHas('success');

        $run = $this->run->fresh();
        $this->assertNotNull($run->completed_at);
        $this->assertSame('Pass', $run->verdict);
        $this->assertSame('J', $run->aql->code_letter);
    }

    public function test_a_valid_plan_with_a_fail_verdict_still_finishes_as_fail(): void
    {
        $this->finish(['lot_size' => 1000, 'found_major' => 6])->assertSessionHas('success'); // Ac 5 at J / 2.5

        $run = $this->run->fresh();
        $this->assertNotNull($run->completed_at);
        $this->assertSame('Fail', $run->verdict);
    }

    public function test_finish_is_refused_when_the_aql_verdict_is_pending(): void
    {
        // Critical AQL left empty: no plan for criticals, so a critical defect cannot pass or fail.
        $this->finish(['lot_size' => 1000, 'aql_critical' => '', 'found_critical' => 1])
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'AQL Sampling') && str_contains($m, 'Pending'));

        $this->assertNotFinished();
    }

    public function test_a_type_without_an_aql_section_is_unaffected(): void
    {
        $inspection = $this->makeInspection();
        $run = $this->makeRun($inspection, [[$this->makeSection('packing_check', 'checklist'), [], 'pending']]);
        $section = $run->runSections()->firstOrFail();

        $this->put(route('inspections.runs.update', [$inspection, $run]), [
            'finish_run' => 1, 'sections' => [$section->id => ['status' => 'complete']],
        ])->assertSessionHas('success');

        $this->assertNotNull($run->fresh()->completed_at);
    }

    public function test_both_reasons_are_listed_when_sections_are_open_and_the_plan_is_missing(): void
    {
        $inspection = $this->makeInspection();
        $run = $this->makeRun($inspection, [
            [$this->aqlLibrarySection(), [], 'pending'],
            [$this->makeSection('packing_check', 'checklist'), [], 'pending'],
        ]);
        $aql = $run->runSections()->whereHas('section', fn ($q) => $q->where('slug', 'aql_sampling'))->firstOrFail();

        $this->put(route('inspections.runs.update', [$inspection, $run]), [
            'finish_run' => 1, 'sections' => [$aql->id => ['status' => 'complete']],
        ])->assertSessionHas('error', fn ($m) => str_contains($m, 'Packing Check') && str_contains($m, 'AQL Sampling'));

        $this->assertNull($run->fresh()->completed_at);
    }

    public function test_saving_without_finishing_is_not_affected(): void
    {
        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), [
            'sections' => [$this->aqlSection->id => ['status' => 'pending']],
        ])->assertSessionHas('success');
    }

    public function test_defect_rows_and_a_valid_plan_finish_with_the_row_counts(): void
    {
        $major = Defect::create(['defect_name' => 'Open seam', 'severity' => 'major', 'status' => true]);
        $defectLib = $this->makeSection('defect_recording', 'defects');
        $ds = InspectionRunSection::create(['inspection_run_id' => $this->run->id, 'inspection_section_id' => $defectLib->id, 'sort_order' => 20, 'data' => [], 'status' => 'pending']);

        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), [
            'finish_run' => 1,
            'aql' => ['lot_size' => 1000, 'inspection_level' => 'II', 'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0'],
            'sections' => [
                $this->aqlSection->id => ['status' => 'complete'],
                $ds->id => ['status' => 'complete', 'defects' => [['defect_id' => $major->id, 'severity' => 'major', 'qty' => 6]]],
            ],
        ])->assertSessionHas('success');

        $this->assertSame('Fail', $this->run->fresh()->verdict);
    }
}
