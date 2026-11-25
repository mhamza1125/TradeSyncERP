<?php

namespace Tests\Feature\Inspections;

use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** QC proposal b1 / audit F-05: AQL found counts come from the recorded defect rows. */
class AqlCountsFromDefectsTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspection;

    private InspectionRun $run;

    private InspectionRunSection $defectSection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Admin'));
        $this->inspection = $this->makeInspection();
        $this->run = $this->makeRun($this->inspection, [
            [$this->makeSection('aql_sampling', 'aql'), [], 'pending'],
            [$this->makeSection('defect_recording', 'defects'), [], 'pending'],
        ]);
        $this->defectSection = $this->run->runSections()->whereHas('section', fn ($q) => $q->where('slug', 'defect_recording'))->firstOrFail();
    }

    private function defect(string $severity): Defect
    {
        return Defect::create(['defect_name' => "D {$severity} ".uniqid(), 'severity' => $severity, 'status' => true]);
    }

    private function saveAql(array $aql = [], array $extra = [])
    {
        return $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), array_merge([
            'aql' => array_merge([
                'lot_size' => 1000, 'inspection_level' => 'II', 'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0',
                'found_critical' => 0, 'found_major' => 0, 'found_minor' => 0,
            ], $aql),
        ], $extra));
    }

    public function test_recorded_defect_rows_override_typed_counts(): void
    {
        $major = $this->defect('major');
        $minor = $this->defect('minor');

        $this->saveAql(['found_major' => 0], ['sections' => [$this->defectSection->id => ['status' => 'complete', 'defects' => [
            ['defect_id' => $major->id, 'severity' => 'major', 'qty' => 4],
            ['defect_id' => $major->id, 'severity' => 'major', 'qty' => 2],
            ['defect_id' => $minor->id, 'severity' => 'minor', 'qty' => 3],
        ]]]])->assertSessionHasNoErrors();

        $aql = $this->run->aql()->firstOrFail();
        $this->assertSame(6, $aql->found_major);
        $this->assertSame(3, $aql->found_minor);
        $this->assertSame(0, $aql->found_critical);
        $this->assertSame('defect_rows', $aql->counts_source);
        $this->assertSame('Fail', $aql->verdict); // 6 > Ac 5 at code J, AQL 2.5
    }

    public function test_without_defect_rows_the_typed_counts_are_used(): void
    {
        $this->saveAql(['found_minor' => 2]);

        $aql = $this->run->aql()->firstOrFail();
        $this->assertSame(2, $aql->found_minor);
        $this->assertSame('manual', $aql->counts_source);
    }

    public function test_row_without_severity_uses_the_defect_library_and_functional_is_not_counted(): void
    {
        $critical = $this->defect('critical');
        $functional = $this->defect('functional');

        $this->saveAql([], ['sections' => [$this->defectSection->id => ['status' => 'complete', 'defects' => [
            ['defect_id' => $critical->id, 'qty' => 1],                          // severity from the library
            ['defect_id' => $functional->id, 'severity' => 'functional', 'qty' => 5],
        ]]]]);

        $aql = $this->run->aql()->firstOrFail();
        $this->assertSame(1, $aql->found_critical);
        $this->assertSame(0, $aql->found_major + $aql->found_minor);
        $this->assertSame('Fail', $aql->verdict);
    }

    public function test_ajax_defect_save_refreshes_the_aql_counts(): void
    {
        $this->saveAql();
        $major = $this->defect('major');

        $this->postJson(route('inspections.runs.sections.save', [$this->inspection, $this->run, $this->defectSection]), [
            'status' => 'pending',
            'defects' => [['defect_id' => $major->id, 'severity' => 'major', 'qty' => 9]],
        ])->assertOk();

        $aql = $this->run->aql()->firstOrFail();
        $this->assertSame(9, $aql->found_major);
        $this->assertSame('Fail', $aql->verdict);
    }

    public function test_pdf_states_where_the_counts_came_from(): void
    {
        $major = $this->defect('major');
        $this->saveAql([], ['sections' => [$this->defectSection->id => ['status' => 'complete', 'defects' => [
            ['defect_id' => $major->id, 'severity' => 'major', 'qty' => 1],
        ]]]]);

        $html = view('exports.inspection-run-pdf', [
            'inspection' => $this->inspection->load(['inspectionType', 'inspectors', 'customerOrders']),
            'runs' => collect([$this->run->fresh()->load(['sample.customer', 'sample.category', 'sampleColor', 'runSections.section', 'runSections.attachments', 'runSections.defects.defect', 'aql.sizeBreakdowns'])]),
            'imgBase64' => fn () => null, 'defects' => collect(), 'logoBase64' => null,
        ])->render();

        $this->assertStringContainsString('counted from the recorded defects', $html);
    }
}
