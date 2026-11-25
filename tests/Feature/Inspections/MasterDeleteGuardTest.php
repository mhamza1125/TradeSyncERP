<?php

namespace Tests\Feature\Inspections;

use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunDefect;
use App\Models\InspectionType;
use App\Services\Inspection\InspectionRunLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/**
 * RR-06 / owner decision D3 (Q30): a defect on a finished run's defect rows, and an inspection
 * type used by an inspection with a finished run, cannot be deleted; deactivating stays allowed.
 */
class MasterDeleteGuardTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private InspectionType $type;

    private Inspection $inspection;

    private InspectionRun $run;

    private Defect $defect;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Admin'));
        $this->type = InspectionType::create(['name' => 'Final Random Inspection', 'status' => true]);
        $this->inspection = $this->makeInspection(['inspection_type_id' => $this->type->id]);
        $this->run = $this->makeRun($this->inspection, [[$this->makeSection('defect_recording', 'defects'), [], 'complete']]);
        $this->defect = Defect::create(['defect_name' => 'Broken stitch', 'severity' => 'major', 'status' => true]);

        InspectionRunDefect::create([
            'inspection_run_section_id' => $this->run->runSections()->firstOrFail()->id,
            'defect_id' => $this->defect->id, 'severity' => 'major', 'qty' => 2, 'status' => 'open', 'sort_order' => 0,
        ]);
    }

    private function finishRun(): void
    {
        $this->run->update(['completed_at' => now(), 'verdict' => 'Fail']);
    }

    // ── Defects ──

    public function test_a_defect_used_by_a_finished_run_cannot_be_deleted(): void
    {
        $this->finishRun();

        $this->delete(route('masters.defects.destroy', $this->defect))
            ->assertRedirect()
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'finished') && str_contains($m, 'Deactivate'));

        $this->assertNotNull($this->defect->fresh());
        $this->assertSame($this->defect->id, InspectionRunDefect::sole()->defect_id);

        $this->deleteJson(route('masters.defects.destroy', $this->defect))
            ->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_a_defect_used_only_by_an_unfinished_run_can_be_deleted(): void
    {
        $this->delete(route('masters.defects.destroy', $this->defect))->assertSessionHas('success');
        $this->assertNull($this->defect->fresh());
    }

    public function test_an_unused_defect_can_be_deleted(): void
    {
        $unused = Defect::create(['defect_name' => 'Unused', 'severity' => 'minor', 'status' => true]);
        $this->delete(route('masters.defects.destroy', $unused))->assertSessionHas('success');
        $this->assertNull($unused->fresh());
    }

    public function test_a_used_defect_can_still_be_deactivated(): void
    {
        $this->finishRun();

        $this->put(route('masters.defects.update', $this->defect), [
            'defect_name' => 'Broken stitch', 'severity' => 'major', 'status' => 0,
        ])->assertSessionHas('success');

        $this->assertFalse((bool) $this->defect->fresh()->status);
    }

    // ── Inspection types ──

    public function test_a_type_used_by_an_inspection_with_a_finished_run_cannot_be_deleted(): void
    {
        $this->finishRun();

        $this->delete(route('masters.inspection-types.destroy', $this->type))
            ->assertRedirect()
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'finished') && str_contains($m, 'Deactivate'));

        $this->assertNotNull($this->type->fresh());
        $this->assertSame($this->type->id, $this->inspection->fresh()->inspection_type_id);

        $this->deleteJson(route('masters.inspection-types.destroy', $this->type))
            ->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_a_type_without_finished_runs_can_be_deleted(): void
    {
        $this->delete(route('masters.inspection-types.destroy', $this->type))->assertSessionHas('success');
        $this->assertNull($this->type->fresh());
    }

    public function test_an_unused_type_can_be_deleted(): void
    {
        $unused = InspectionType::create(['name' => 'Unused type', 'status' => true]);
        $this->delete(route('masters.inspection-types.destroy', $unused))->assertSessionHas('success');
        $this->assertNull($unused->fresh());
    }

    public function test_a_used_type_can_still_be_deactivated(): void
    {
        $this->finishRun();

        $this->put(route('masters.inspection-types.update', $this->type), ['name' => 'Final Random Inspection', 'status' => 0])
            ->assertSessionHas('success');

        $this->assertFalse((bool) $this->type->fresh()->status);
    }

    // ── Old reports after deactivation ──

    public function test_a_finished_report_still_shows_a_deactivated_defect_and_type(): void
    {
        $this->finishRun();
        $this->defect->update(['status' => false]);
        $this->type->update(['status' => false]);

        $run = InspectionRunLockService::bypass(fn () => $this->run->fresh()->load([
            'sample.customer', 'sample.category', 'sampleColor', 'runSections.section', 'runSections.attachments', 'runSections.defects.defect', 'aql.sizeBreakdowns',
        ]));

        $html = view('exports.inspection-run-pdf', [
            'inspection' => $this->inspection->fresh()->load(['inspectionType', 'inspectors', 'customerOrders']),
            'runs' => collect([$run]),
            'imgBase64' => fn () => null,
            'defects' => Defect::all()->keyBy('id'), // as InspectionExportController passes it (active or not)
            'logoBase64' => null,
        ])->render();

        $this->assertStringContainsString('Broken stitch', $html);
        $this->assertStringContainsString('Final Random Inspection', $html);

        $this->get(route('inspections.runs.export-pdf', [$this->inspection, $this->run]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
