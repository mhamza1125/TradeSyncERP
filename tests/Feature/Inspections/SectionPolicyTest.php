<?php

namespace Tests\Feature\Inspections;

use App\Models\Inspection;
use App\Models\InspectionRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-18: the section skip policy and completeness are enforced on the server. */
class SectionPolicyTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspection;

    private InspectionRun $run;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Admin'));
        $this->inspection = $this->makeInspection();
        $this->run = $this->makeRun($this->inspection, [
            [$this->makeSection('must_do', 'checklist', [], 'mandatory'), [], 'pending'],
            [$this->makeSection('may_do', 'checklist', [], 'optional'), [], 'pending'],
            [$this->makeSection('can_skip', 'checklist', [], 'skippable'), [], 'pending'],
        ]);
    }

    private function section(string $slug)
    {
        return $this->run->runSections()->whereHas('section', fn ($q) => $q->where('slug', $slug))->firstOrFail();
    }

    // ── Skipping (status "na") ───────────────────────────────────────────────

    public function test_only_skippable_sections_can_be_skipped_via_ajax(): void
    {
        foreach (['must_do', 'may_do'] as $slug) {
            $this->postJson(route('inspections.runs.sections.save', [$this->inspection, $this->run, $this->section($slug)]), ['status' => 'na'])
                ->assertStatus(422)->assertJsonValidationErrors('status');
            $this->assertSame('pending', $this->section($slug)->status, $slug);
        }

        $this->postJson(route('inspections.runs.sections.save', [$this->inspection, $this->run, $this->section('can_skip')]), ['status' => 'na'])
            ->assertOk();
        $this->assertSame('na', $this->section('can_skip')->status);
    }

    public function test_only_skippable_sections_can_be_skipped_via_the_full_form(): void
    {
        $mandatory = $this->section('must_do');

        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), [
            'sections' => [$mandatory->id => ['status' => 'na']],
        ])->assertSessionHasErrors("sections.{$mandatory->id}.status");

        $this->assertSame('pending', $mandatory->fresh()->status);
    }

    // ── Completeness at finish ───────────────────────────────────────────────

    public function test_cannot_finish_with_an_incomplete_mandatory_section_but_the_data_is_saved(): void
    {
        $optional = $this->section('may_do');

        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), [
            'sections' => [$optional->id => ['status' => 'complete', 'notes' => 'saved anyway']],
            'finish_run' => 1,
        ])->assertRedirect()->assertSessionHas('error', fn ($m) => str_contains($m, 'Must Do') && str_contains($m, 'Can Skip'));

        $this->assertNull($this->run->fresh()->completed_at);
        $this->assertSame('saved anyway', $optional->fresh()->notes);
    }

    public function test_can_finish_when_mandatory_is_complete_and_skippable_is_skipped(): void
    {
        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), [
            'sections' => [
                $this->section('must_do')->id => ['status' => 'complete'],
                $this->section('can_skip')->id => ['status' => 'na'],
                // optional left pending on purpose
            ],
            'finish_run' => 1,
        ])->assertSessionHasNoErrors()->assertSessionMissing('error');

        $this->assertNotNull($this->run->fresh()->completed_at);
    }
}
