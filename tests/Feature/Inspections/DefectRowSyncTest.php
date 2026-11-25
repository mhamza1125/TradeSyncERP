<?php

namespace Tests\Feature\Inspections;

use App\Models\Attachment;
use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunDefect;
use App\Models\InspectionRunSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/**
 * RR-04: removing the last defect row must delete it (the form posts an empty `defects` key).
 * RR-18: posting the same form twice must not delete and recreate rows (photos are keyed by row id).
 */
class DefectRowSyncTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspection;

    private InspectionRun $run;

    private InspectionRunSection $defectSection;

    private Defect $major;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs($this->userWithRole('Admin'));
        $this->inspection = $this->makeInspection();
        $this->run = $this->makeRun($this->inspection, [
            [$this->makeSection('aql_sampling', 'aql'), [], 'pending'],
            [$this->makeSection('defect_recording', 'defects'), [], 'pending'],
        ]);
        $this->defectSection = $this->run->runSections()->whereHas('section', fn ($q) => $q->where('slug', 'defect_recording'))->firstOrFail();
        $this->major = Defect::create(['defect_name' => 'Open seam', 'severity' => 'major', 'status' => true]);
    }

    private function saveDefects(string|array $defects, array $aql = [])
    {
        return $this->call('PUT', route('inspections.runs.update', [$this->inspection, $this->run]), array_filter([
            'aql' => $aql ? array_merge(['lot_size' => 1000, 'inspection_level' => 'II', 'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0'], $aql) : null,
            'sections' => [$this->defectSection->id => ['status' => 'pending', 'defects' => $defects]],
        ]));
    }

    private function row(array $extra = []): array
    {
        return array_merge(['defect_id' => $this->major->id, 'severity' => 'major', 'qty' => 6, 'size' => 'M', 'carton_no' => '12', 'status' => 'open'], $extra);
    }

    public function test_removing_the_last_row_deletes_it_and_recomputes_the_counts(): void
    {
        $this->saveDefects([$this->row()], ['found_major' => 0])->assertSessionHasNoErrors();
        $aql = $this->run->aql()->firstOrFail();
        $this->assertSame(6, $aql->found_major);
        $this->assertSame('Fail', $aql->verdict);

        // What the form posts once the last row is removed: the hidden sentinel, empty.
        $this->saveDefects('', ['found_major' => 6])->assertSessionHasNoErrors();

        $this->assertSame(0, InspectionRunDefect::count());
        $aql->refresh();
        $this->assertSame(0, $aql->found_major);       // the rows' counts no longer exist
        $this->assertSame('manual', $aql->counts_source);
        $this->assertSame('Pending', $aql->verdict);   // zero defects, AQL section still open

        // From now on, with zero rows, the typed counts apply as before.
        $this->saveDefects('', ['found_major' => 2])->assertSessionHasNoErrors();
        $this->assertSame(2, $aql->fresh()->found_major);
    }

    public function test_ajax_save_with_an_empty_defects_key_deletes_all_rows(): void
    {
        $this->saveDefects([$this->row()], ['found_major' => 0]);

        $this->postJson(route('inspections.runs.sections.save', [$this->inspection, $this->run, $this->defectSection]), [
            'status' => 'pending', 'defects' => '',
        ])->assertOk();

        $this->assertSame(0, InspectionRunDefect::count());
    }

    public function test_omitting_the_defects_key_leaves_the_rows_alone(): void
    {
        $this->saveDefects([$this->row()]);
        $this->call('PUT', route('inspections.runs.update', [$this->inspection, $this->run]), [
            'sections' => [$this->defectSection->id => ['status' => 'pending']],
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, InspectionRunDefect::count());
    }

    public function test_the_same_form_posted_twice_keeps_one_row_with_the_same_id_and_its_photos(): void
    {
        $this->saveDefects([$this->row()])->assertSessionHasNoErrors();
        $row = InspectionRunDefect::sole();

        Storage::disk('public')->put('inspection-sections/x/photo.jpg', 'img');
        $photo = $this->defectSection->attachments()->create([
            'title' => 'photo', 'file_name' => 'photo.jpg', 'file_path' => 'inspection-sections/x/photo.jpg',
            'mime_type' => 'image/jpeg', 'file_size' => 3, 'attachment_type' => 'gallery', 'task_key' => $row->taskKey(),
        ]);

        // A double submit (or a stale tab) posts the same row again, still without its id.
        $this->saveDefects([$this->row()])->assertSessionHasNoErrors();

        $this->assertSame([$row->id], InspectionRunDefect::pluck('id')->all());
        $this->assertNotNull(Attachment::find($photo->id));
        Storage::disk('public')->assertExists('inspection-sections/x/photo.jpg');
    }

    public function test_two_identical_new_rows_stay_two_rows_on_a_repeat_post(): void
    {
        $this->saveDefects([$this->row(), $this->row()]);
        $ids = InspectionRunDefect::orderBy('id')->pluck('id')->all();

        $this->saveDefects([$this->row(), $this->row()]);

        $this->assertSame($ids, InspectionRunDefect::orderBy('id')->pluck('id')->all());
    }

    public function test_a_changed_row_without_an_id_is_still_a_new_row(): void
    {
        $this->saveDefects([$this->row()]);
        $old = InspectionRunDefect::sole();

        $this->saveDefects([$this->row(['qty' => 2])]);

        $this->assertSame(1, InspectionRunDefect::count());
        $this->assertNotSame($old->id, InspectionRunDefect::sole()->id);
        $this->assertSame(2, InspectionRunDefect::sole()->qty);
    }

    public function test_a_row_posted_with_its_id_is_updated_in_place(): void
    {
        $this->saveDefects([$this->row()]);
        $row = InspectionRunDefect::sole();

        $this->saveDefects([$this->row(['id' => $row->id, 'qty' => 3])]);

        $this->assertSame($row->id, InspectionRunDefect::sole()->id);
        $this->assertSame(3, InspectionRunDefect::sole()->qty);
    }

    public function test_the_edit_form_posts_the_empty_defects_sentinel_before_the_rows(): void
    {
        $html = $this->get(route('inspections.runs.edit', [$this->inspection, $this->run]))->assertOk()->getContent();

        $sentinel = 'name="sections['.$this->defectSection->id.'][defects]" value=""';
        $this->assertStringContainsString($sentinel, $html);
        // PHP keeps the last value for a repeated key: the sentinel must come before the row inputs.
        $this->assertLessThan(
            strpos($html, 'defectsTableBody-'.$this->defectSection->id),
            strpos($html, $sentinel)
        );
    }
}
