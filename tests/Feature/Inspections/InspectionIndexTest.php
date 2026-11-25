<?php

namespace Tests\Feature\Inspections;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

class InspectionIndexTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    public function test_index_renders_and_filters_by_the_new_statuses(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $this->makeInspection(['overall_status' => 'Re-Inspection', 'report_number' => 'RPT-REINSPECT']);
        $this->makeInspection(['overall_status' => 'Pass', 'report_number' => 'RPT-PASSED']);

        $this->get(route('inspections.index', ['status' => 'Re-Inspection']))
            ->assertOk()
            ->assertSee('<option value="Re-Inspection" selected', false)
            ->assertSee('RPT-REINSPECT')
            ->assertDontSee('RPT-PASSED');
    }
}
