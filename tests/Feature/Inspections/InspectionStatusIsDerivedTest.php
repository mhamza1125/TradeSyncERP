<?php

namespace Tests\Feature\Inspections;

use App\Models\Inspection;
use App\Models\InspectionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-12: overall_status is derived from the runs, never typed in. */
class InspectionStatusIsDerivedTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_merge([
            'inspection_type_id' => InspectionType::firstOrCreate(['name' => 'Test Final Inspection'], ['status' => true])->id,
            'inspection_date' => now()->toDateString(),
            'remarks' => 'r',
        ], $extra);
    }

    public function test_new_inspection_starts_pending_and_status_cannot_be_posted(): void
    {
        $this->actingAs($this->userWithRole('Admin'));

        $this->post(route('inspections.store'), $this->payload(['overall_status' => 'Pass']))
            ->assertSessionHasErrors('overall_status');
        $this->assertSame(0, Inspection::count());

        $this->post(route('inspections.store'), $this->payload())->assertSessionHasNoErrors();
        $this->assertSame('Pending', Inspection::firstOrFail()->overall_status);
    }

    public function test_editing_the_header_cannot_set_the_status(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $inspection = $this->makeInspection(['overall_status' => 'Fail']);

        $this->put(route('inspections.update', $inspection), $this->payload(['overall_status' => 'Pass']))
            ->assertSessionHasErrors('overall_status');

        $this->put(route('inspections.update', $inspection), $this->payload(['remarks' => 'edited']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Fail', $inspection->fresh()->overall_status);
        $this->assertSame('edited', $inspection->fresh()->remarks);
    }

    public function test_form_shows_status_read_only(): void
    {
        $this->actingAs($this->userWithRole('Admin'));

        $this->get(route('inspections.create'))->assertOk()->assertDontSee('name="overall_status"', false);
        $this->get(route('inspections.edit', $this->makeInspection()))->assertOk()->assertDontSee('name="overall_status"', false);
    }
}
