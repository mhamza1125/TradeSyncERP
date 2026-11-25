<?php

namespace Tests\Feature\Inspections;

use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunDefect;
use App\Models\InspectionRunSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/**
 * RR-03: "Save run" saves everything without finishing; the AJAX section save carries defect
 * rows and returns their ids. RR-19: failures carry the server's reason.
 */
class SaveWithoutFinishingTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspection;

    private InspectionRun $run;

    private InspectionRunSection $aqlSection;

    private InspectionRunSection $defectSection;

    private Defect $major;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Admin'));
        $this->inspection = $this->makeInspection();
        $this->run = $this->makeRun($this->inspection, [
            [$this->makeSection('aql_sampling', 'aql'), [], 'pending'],
            [$this->makeSection('defect_recording', 'defects'), [], 'pending'],
        ]);
        $this->aqlSection = $this->run->runSections()->whereHas('section', fn ($q) => $q->where('slug', 'aql_sampling'))->firstOrFail();
        $this->defectSection = $this->run->runSections()->whereHas('section', fn ($q) => $q->where('slug', 'defect_recording'))->firstOrFail();
        $this->major = Defect::create(['defect_name' => 'Open seam', 'severity' => 'major', 'status' => true]);
    }

    private function sectionSave(array $payload)
    {
        return $this->postJson(route('inspections.runs.sections.save', [$this->inspection, $this->run, $this->defectSection]), $payload);
    }

    public function test_save_run_without_the_finish_flag_saves_defects_and_aql_and_does_not_finish(): void
    {
        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), [
            'finish_run' => 0,
            'aql' => ['lot_size' => 1000, 'inspection_level' => 'II', 'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0'],
            'sections' => [
                $this->aqlSection->id => ['status' => 'complete'],
                $this->defectSection->id => ['status' => 'complete', 'defects' => [['defect_id' => $this->major->id, 'severity' => 'major', 'qty' => 2]]],
            ],
        ])->assertRedirect(route('inspections.runs.edit', [$this->inspection, $this->run]))
            ->assertSessionHas('success', 'Run saved successfully.');

        $run = $this->run->fresh();
        $this->assertNull($run->completed_at);
        $this->assertSame('Pending', $run->verdict);
        $this->assertSame(1, InspectionRunDefect::count());
        $this->assertSame(1000, (int) $run->aql->lot_size);
        $this->assertSame(2, (int) $run->aql->found_major);
        $this->assertSame('Pass', $run->aql->verdict); // 2 <= Ac 5, AQL section complete
    }

    public function test_a_new_run_with_the_aql_placeholder_can_be_saved_before_the_lot_size_is_known(): void
    {
        // A new run's AQL placeholder has sample_size 0; the read-only field posts it back.
        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), [
            'aql' => ['lot_size' => '', 'sample_size' => '0', 'inspection_level' => 'II', 'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0'],
            'sections' => [$this->defectSection->id => ['status' => 'pending', 'notes' => 'Saved before the lot size is known']],
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame('Saved before the lot size is known', $this->defectSection->fresh()->notes);
    }

    public function test_ajax_section_save_with_rows_returns_their_ids_in_posted_order(): void
    {
        $minor = Defect::create(['defect_name' => 'Loose thread', 'severity' => 'minor', 'status' => true]);

        $response = $this->sectionSave(['status' => 'pending', 'defects' => [
            ['defect_id' => $this->major->id, 'severity' => 'major', 'qty' => 1],
            ['defect_id' => $minor->id, 'severity' => 'minor', 'qty' => 3],
        ]])->assertOk()->assertJsonPath('success', true);

        $ids = $response->json('defect_ids');
        $this->assertSame(InspectionRunDefect::where('defect_id', $this->major->id)->value('id'), $ids['0']);
        $this->assertSame(InspectionRunDefect::where('defect_id', $minor->id)->value('id'), $ids['1']);

        // The next save posts those ids: the rows are updated in place, not recreated.
        $this->sectionSave(['status' => 'pending', 'defects' => [
            ['id' => $ids['0'], 'defect_id' => $this->major->id, 'severity' => 'major', 'qty' => 4],
            ['id' => $ids['1'], 'defect_id' => $minor->id, 'severity' => 'minor', 'qty' => 3],
        ]])->assertOk();

        $this->assertSame([$ids['0'], $ids['1']], InspectionRunDefect::orderBy('id')->pluck('id')->all());
        $this->assertSame(4, InspectionRunDefect::find($ids['0'])->qty);
    }

    public function test_ajax_section_save_without_defects_returns_null_ids(): void
    {
        $this->sectionSave(['status' => 'pending'])->assertOk()->assertJsonPath('defect_ids', null);
    }

    public function test_a_refused_skip_returns_the_reason(): void
    {
        $this->sectionSave(['status' => 'na'])
            ->assertStatus(422)
            ->assertJsonPath('errors.status.0', "'Defect Recording' cannot be skipped.");
    }

    public function test_a_locked_run_returns_its_reason(): void
    {
        $this->run->update(['completed_at' => now(), 'verdict' => 'Pass']);

        $this->sectionSave(['status' => 'pending'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This run is finished and read-only. Reopen it to make changes.');
    }

    public function test_the_edit_page_wires_save_run_the_unsaved_changes_guard_and_error_messages(): void
    {
        $html = $this->get(route('inspections.runs.edit', [$this->inspection, $this->run]))->assertOk()->getContent();

        $this->assertStringContainsString('id="save-run-btn"', $html);
        $this->assertStringContainsString('submitRunForm(false)', $html);
        $this->assertStringContainsString("addEventListener('beforeunload'", $html);
        $this->assertStringContainsString('Your session has expired', $html);
        $this->assertStringContainsString('function saveErrorMessage', $html);
        $this->assertStringNotContainsString("'Could not save. Try again.'", $html);
    }

    public function test_a_finished_run_has_no_save_run_button(): void
    {
        $this->run->update(['completed_at' => now(), 'verdict' => 'Pass']);

        $this->get(route('inspections.runs.edit', [$this->inspection, $this->run]))
            ->assertOk()->assertDontSee('id="save-run-btn"', false);
    }
}
