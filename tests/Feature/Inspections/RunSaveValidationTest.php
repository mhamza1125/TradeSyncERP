<?php

namespace Tests\Feature\Inspections;

use App\Models\Defect;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunSection;
use App\Services\Inspection\AqlCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/**
 * RR-09 (AQL values), RR-10 (inspect_qty is computed on the server), RR-16 (upper bounds give
 * 422, never HTTP 500). Limits follow the column types; see docs/QC_RULES.md §7.
 */
class RunSaveValidationTest extends TestCase
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

    private function save(array $aql = [], array $sections = [])
    {
        return $this->putJson(route('inspections.runs.update', [$this->inspection, $this->run]), array_filter([
            'aql' => array_merge([
                'lot_size' => 1000, 'inspection_level' => 'II', 'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0',
                'found_critical' => 0, 'found_major' => 0, 'found_minor' => 0,
            ], $aql),
            'sections' => $sections,
        ]));
    }

    // ── RR-09 ──

    public function test_unsupported_aql_values_are_rejected(): void
    {
        foreach (['3.0', 'abc', '2.6', '-1', '0'] as $bad) {
            $this->save(['aql_major' => $bad])->assertStatus(422)->assertJsonValidationErrors('aql.aql_major');
        }
        $this->save(['aql_critical' => '0.2'])->assertStatus(422)->assertJsonValidationErrors('aql.aql_critical');
        $this->save(['aql_minor' => 'x'])->assertStatus(422)->assertJsonValidationErrors('aql.aql_minor');
        $this->assertNull($this->run->aql()->first());
    }

    public function test_every_supported_aql_value_and_not_allowed_are_accepted(): void
    {
        foreach ([...AqlCalculationService::AQL_LEVELS, 'not_allowed'] as $value) {
            $this->save(['aql_critical' => $value, 'aql_major' => $value, 'aql_minor' => $value])
                ->assertSessionHasNoErrors()->assertRedirect();
        }
        $this->assertSame(-1.0, (float) $this->run->aql()->firstOrFail()->aql_major);
    }

    // ── RR-10 ──

    public function test_posted_inspect_qty_is_ignored_and_recomputed_from_the_order_quantities(): void
    {
        $this->save(['lot_size' => 7, 'variations' => [
            ['color' => 'Red', 'size' => 'M', 'order_qty' => 300, 'inspect_qty' => 9999],
            ['color' => 'Blue', 'size' => 'L', 'order_qty' => 700, 'inspect_qty' => 0],
        ]])->assertSessionHasNoErrors();

        $aql = $this->run->aql()->firstOrFail();
        $this->assertSame(1000, (int) $aql->lot_size);
        $this->assertSame(200, (int) $aql->sample_size); // lot 1000, level II: critical 0.065 moves to L (n 200), the largest plan (Q12)
        $this->assertSame([60, 140], array_map(fn ($v) => (int) $v['inspect_qty'], $aql->variations));
    }

    public function test_the_user_is_told_when_the_typed_lot_size_is_replaced_by_the_variations_total(): void
    {
        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), ['aql' => [
            'lot_size' => 7, 'inspection_level' => 'II', 'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0',
            'variations' => [['color' => 'Red', 'order_qty' => 300], ['color' => 'Blue', 'order_qty' => 700]],
        ]])->assertSessionHas('success', fn ($m) => str_contains($m, '1000') && str_contains($m, '7'));
    }

    public function test_no_notice_when_the_typed_lot_size_matches_the_variations(): void
    {
        $this->put(route('inspections.runs.update', [$this->inspection, $this->run]), ['aql' => [
            'lot_size' => 1000, 'inspection_level' => 'II', 'aql_critical' => '0.065', 'aql_major' => '2.5', 'aql_minor' => '4.0',
            'variations' => [['color' => 'Red', 'order_qty' => 300], ['color' => 'Blue', 'order_qty' => 700]],
        ]])->assertSessionHas('success', 'Run saved successfully.');
    }

    // ── RR-16 ──

    public function test_values_beyond_the_column_limits_give_422_not_500(): void
    {
        $cases = [
            'aql.lot_size' => ['lot_size' => 4294967296],
            'aql.found_critical' => ['found_critical' => 65536],
            'aql.found_major' => ['found_major' => 999999999],
            'aql.found_minor' => ['found_minor' => 65536],
            'aql.variations.0.order_qty' => ['variations' => [['color' => 'Red', 'order_qty' => 4294967296]]],
            'aql.variations.0.inspect_qty' => ['variations' => [['color' => 'Red', 'order_qty' => 10, 'inspect_qty' => 4294967296]]],
            'aql.size_breakdown.0.order_qty' => ['size_breakdown' => [['size_label' => 'M', 'order_qty' => 4294967296]]],
            'aql.size_breakdown.0.checked_qty' => ['size_breakdown' => [['size_label' => 'M', 'checked_qty' => 4294967296]]],
            'aql.size_breakdown.0.error_qty' => ['size_breakdown' => [['size_label' => 'M', 'error_qty' => 4294967296]]],
        ];

        foreach ($cases as $field => $aql) {
            $this->save($aql)->assertStatus(422)->assertJsonValidationErrors($field);
        }
    }

    public function test_a_variations_total_beyond_the_lot_size_column_gives_422(): void
    {
        $this->save(['variations' => [
            ['color' => 'Red', 'order_qty' => 4294967295],
            ['color' => 'Blue', 'order_qty' => 10],
        ]])->assertStatus(422)->assertJsonValidationErrors('aql.variations');
    }

    public function test_a_huge_defect_qty_gives_422(): void
    {
        $defect = Defect::create(['defect_name' => 'Stain', 'severity' => 'major', 'status' => true]);

        $this->save([], [$this->defectSection->id => ['status' => 'pending', 'defects' => [
            ['defect_id' => $defect->id, 'severity' => 'major', 'qty' => 100000000],
        ]]])->assertStatus(422)->assertJsonValidationErrors('sections.'.$this->defectSection->id.'.defects.0.qty');

        $this->postJson(route('inspections.runs.sections.save', [$this->inspection, $this->run, $this->defectSection]), [
            'status' => 'pending', 'defects' => [['defect_id' => $defect->id, 'severity' => 'major', 'qty' => 65536]],
        ])->assertStatus(422)->assertJsonValidationErrors('defects.0.qty');
    }

    public function test_defect_rows_whose_total_exceeds_the_found_count_column_give_422_and_save_nothing(): void
    {
        $defect = Defect::create(['defect_name' => 'Stain', 'severity' => 'major', 'status' => true]);
        $rows = [
            ['defect_id' => $defect->id, 'severity' => 'major', 'qty' => 65535],
            ['defect_id' => $defect->id, 'severity' => 'major', 'qty' => 1],
        ];

        $this->save([], [$this->defectSection->id => ['status' => 'pending', 'defects' => $rows]])
            ->assertStatus(422)->assertJsonValidationErrors('sections.'.$this->defectSection->id.'.defects');
        $this->assertSame(0, $this->defectSection->defects()->count());

        $this->save(); // AQL record exists now
        $this->postJson(route('inspections.runs.sections.save', [$this->inspection, $this->run, $this->defectSection]), [
            'status' => 'pending', 'defects' => $rows,
        ])->assertStatus(422)->assertJsonValidationErrors('defects');
        $this->assertSame(0, $this->defectSection->defects()->count());
    }

    public function test_values_at_the_limits_are_accepted(): void
    {
        $this->save(['lot_size' => 4294967295, 'found_minor' => 65535])->assertSessionHasNoErrors();
        $aql = $this->run->aql()->firstOrFail();
        $this->assertSame(4294967295, (int) $aql->lot_size);
        $this->assertSame(65535, (int) $aql->found_minor);
    }
}
