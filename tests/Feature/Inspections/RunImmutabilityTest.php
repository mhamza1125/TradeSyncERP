<?php

namespace Tests\Feature\Inspections;

use App\Exceptions\RunLockedException;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunSection;
use App\Services\Inspection\InspectionRunLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-08 / owner assumption A4: finished runs are read-only; reopening is permissioned, reasoned and logged. */
class RunImmutabilityTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspection;

    private InspectionRun $run;

    private InspectionRunSection $section;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs($this->userWithPermissions('inspections.edit', 'inspections.delete', 'inspections.index', 'inspections.view'));
        $this->inspection = $this->makeInspection();
        $this->run = $this->makeRun($this->inspection, [[$this->makeSection('photos_lock', 'images'), ['note' => 'original'], 'complete']], [
            'completed_at' => now()->subDay(), 'verdict' => 'Pass',
        ]);
        $this->section = $this->run->runSections()->firstOrFail();
    }

    // ── Edits are blocked ────────────────────────────────────────────────────

    public function test_full_form_update_of_a_finished_run_is_refused(): void
    {
        $completedAt = $this->run->completed_at->toDateTimeString();

        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), [
            'sections' => [$this->section->id => ['status' => 'pending', 'data' => ['note' => 'changed']]],
            'finish_run' => 1,
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame('original', $this->section->fresh()->data['note']);
        $this->assertSame('complete', $this->section->fresh()->status);
        $this->assertSame($completedAt, $this->run->fresh()->completed_at->toDateTimeString());
    }

    public function test_attachment_endpoints_refuse_a_finished_run(): void
    {
        $photo = $this->section->attachments()->create(['title' => 'p', 'file_name' => 'p.jpg', 'file_path' => 'x/p.jpg', 'mime_type' => 'image/jpeg']);

        $this->postJson(route('inspections.runs.sections.upload', [$this->inspection, $this->run, $this->section]), [
            'files' => [UploadedFile::fake()->image('a.jpg')],
        ])->assertStatus(422);
        $this->deleteJson(route('inspections.runs.attachments.delete', [$this->inspection, $this->run, $photo]))->assertStatus(422);
        $this->postJson(route('inspections.runs.sections.attachments.reorder', [$this->inspection, $this->run, $this->section]), ['order' => [$photo->id]])->assertStatus(422);
        $this->postJson(route('inspections.runs.attachments.edit', [$this->inspection, $this->run, $photo]), [
            'image' => UploadedFile::fake()->image('b.jpg'),
        ])->assertStatus(422);

        $this->assertSame(1, $this->section->attachments()->count());
        $this->assertModelExists($photo);
    }

    public function test_finished_run_cannot_be_deleted(): void
    {
        $this->delete(route('inspections.runs.destroy', [$this->inspection, $this->run]))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertModelExists($this->run);
    }

    public function test_model_layer_also_refuses_writes_to_a_finished_runs_children(): void
    {
        $this->expectException(RunLockedException::class);

        $this->section->update(['status' => 'pending']);
    }

    public function test_fixture_bypass_only_applies_inside_the_callback(): void
    {
        InspectionRunLockService::bypass(fn () => $this->section->update(['notes' => 'fixture']));
        $this->assertSame('fixture', $this->section->fresh()->notes);

        $this->expectException(RunLockedException::class);
        $this->section->update(['notes' => 'after bypass']);
    }

    // ── Reopen ───────────────────────────────────────────────────────────────

    public function test_reopen_needs_its_own_permission(): void
    {
        $this->post(route('inspections.runs.reopen', [$this->inspection, $this->run]), ['reason' => 'Customer asked for a recheck of cartons'])
            ->assertForbidden();

        $this->assertNotNull($this->run->fresh()->completed_at);
    }

    public function test_reopen_permission_is_admin_only_by_default(): void
    {
        foreach (['Lab Manager', 'Accountant', 'Employee'] as $role) {
            $this->assertFalse(Role::findByName($role)->hasPermissionTo('inspections.reopen'), $role);
        }
        $this->assertTrue(Role::findByName('Admin')->hasPermissionTo('inspections.reopen'));
    }

    public function test_reopen_requires_a_reason(): void
    {
        auth()->user()->givePermissionTo('inspections.reopen');

        $this->post(route('inspections.runs.reopen', [$this->inspection, $this->run]), ['reason' => ''])
            ->assertSessionHasErrors('reason');

        $this->assertNotNull($this->run->fresh()->completed_at);
    }

    public function test_reopen_unlocks_the_run_logs_the_reason_and_updates_the_roll_up(): void
    {
        auth()->user()->givePermissionTo('inspections.reopen');
        $this->inspection->update(['overall_status' => 'Pass']);

        $this->post(route('inspections.runs.reopen', [$this->inspection, $this->run]), ['reason' => 'Wrong carton count recorded'])
            ->assertRedirect(route('inspections.runs.edit', [$this->inspection, $this->run]));

        $run = $this->run->fresh();
        $this->assertNull($run->completed_at);
        $this->assertSame('Pending', $run->verdict);
        $this->assertSame('Pending', $this->inspection->fresh()->overall_status);

        $log = Activity::where('subject_type', InspectionRun::class)->where('subject_id', $run->id)->where('event', 'reopened')->firstOrFail();
        $this->assertSame('Wrong carton count recorded', $log->properties['reason']);
        $this->assertSame('Pass', $log->properties['previous_verdict']);
        $this->assertSame(auth()->id(), $log->causer_id);

        // Now editable again.
        $this->section->update(['status' => 'pending']);
        $this->assertSame('pending', $this->section->fresh()->status);
    }

    // ── Inspection delete ────────────────────────────────────────────────────

    public function test_inspection_with_a_finished_run_cannot_be_deleted(): void
    {
        $this->delete(route('inspections.destroy', $this->inspection))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertModelExists($this->inspection);
    }

    public function test_inspection_without_finished_runs_can_still_be_deleted(): void
    {
        $open = $this->makeInspection();
        $this->makeRun($open);

        $this->delete(route('inspections.destroy', $open))->assertRedirect(route('inspections.index'));
        $this->assertModelMissing($open);
    }

    // ── Audit trail ──────────────────────────────────────────────────────────

    public function test_run_models_are_logged_without_large_json(): void
    {
        auth()->user()->givePermissionTo('inspections.reopen');
        $this->post(route('inspections.runs.reopen', [$this->inspection, $this->run]), ['reason' => 'Recheck needed']);
        $this->section->update(['status' => 'pending', 'notes' => 'n', 'data' => ['note' => str_repeat('x', 500)]]);

        $log = Activity::where('subject_type', InspectionRunSection::class)->where('subject_id', $this->section->id)->latest('id')->firstOrFail();
        $this->assertSame('pending', $log->properties['attributes']['status']);
        $this->assertArrayNotHasKey('data', $log->properties['attributes']);
    }
}
