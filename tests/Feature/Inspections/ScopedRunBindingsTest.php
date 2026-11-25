<?php

namespace Tests\Feature\Inspections;

use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/**
 * Audit F-11: /inspections/{A}/runs/{run-of-B}/... must 404, not act on B's run
 * under A's report number. (There is no run "show" route; edit/update/finish/export
 * and the section endpoints are the run pages.)
 */
class ScopedRunBindingsTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspectionA;

    private Inspection $inspectionB;

    private InspectionRun $runOfB;

    private InspectionRunSection $sectionOfB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->userWithRole('Admin'));
        $this->inspectionA = $this->makeInspection();
        $this->inspectionB = $this->makeInspection();
        $this->runOfB = $this->makeRun($this->inspectionB, [[$this->makeSection('photos_scope', 'images'), [], 'pending']]);
        $this->sectionOfB = $this->runOfB->runSections()->firstOrFail();
    }

    public function test_edit_under_wrong_inspection_is_404(): void
    {
        $this->get(route('inspections.runs.edit', [$this->inspectionA, $this->runOfB]))->assertNotFound();
    }

    public function test_finish_under_wrong_inspection_is_404_and_changes_nothing(): void
    {
        $this->put(route('inspections.runs.update', [$this->inspectionA, $this->runOfB]), ['finish_run' => 1])
            ->assertNotFound();

        $this->assertNull($this->runOfB->fresh()->completed_at);
        $this->assertSame('Pending', $this->inspectionA->fresh()->overall_status);
    }

    public function test_export_under_wrong_inspection_is_404(): void
    {
        $this->get(route('inspections.runs.export-pdf', [$this->inspectionA, $this->runOfB]))->assertNotFound();
    }

    public function test_destroy_under_wrong_inspection_is_404(): void
    {
        $this->delete(route('inspections.runs.destroy', [$this->inspectionA, $this->runOfB]))->assertNotFound();
        $this->assertModelExists($this->runOfB);
    }

    public function test_section_save_under_wrong_inspection_is_404(): void
    {
        $this->postJson(route('inspections.runs.sections.save', [$this->inspectionA, $this->runOfB, $this->sectionOfB]), ['status' => 'complete'])
            ->assertNotFound();
        $this->assertSame('pending', $this->sectionOfB->fresh()->status);
    }

    public function test_attachment_delete_under_wrong_inspection_is_404(): void
    {
        $photo = $this->sectionOfB->attachments()->create(['title' => 'p', 'file_name' => 'p.jpg', 'file_path' => 'x/p.jpg']);

        $this->deleteJson(route('inspections.runs.attachments.delete', [$this->inspectionA, $this->runOfB, $photo]))
            ->assertNotFound();
        $this->assertModelExists($photo);
    }

    public function test_correct_nesting_still_works(): void
    {
        $this->get(route('inspections.runs.edit', [$this->inspectionB, $this->runOfB]))->assertOk();
        $this->get(route('inspections.runs.export-pdf', [$this->inspectionB, $this->runOfB]))->assertOk();
    }
}
