<?php

namespace Tests\Feature\Inspections;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/**
 * RR-05 / owner decision D2 (Q29): the inspection header (type, date, remarks, inspectors,
 * orders) is locked while any of its runs is finished. Reopen the run to change it.
 */
class InspectionHeaderLockTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Inspection $inspection;

    private InspectionRun $run;

    private Employee $inspector;

    private CustomerOrder $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Admin'));
        $this->inspection = $this->makeInspection(['remarks' => 'Original remarks', 'inspection_date' => '2026-11-20']);
        $this->run = $this->makeRun($this->inspection, [[$this->makeSection('packing_check', 'checklist'), [], 'complete']]);

        $this->inspector = $this->employee('Inspector A');
        $customer = Customer::create(['customer_name' => 'Buyer', 'contact_person' => 'C', 'status' => true]);
        $this->order = CustomerOrder::create(['order_code' => 'PO-1', 'customer_id' => $customer->id, 'order_date' => '2026-11-01']);
        $this->inspection->inspectors()->sync([$this->inspector->id]);
        $this->inspection->customerOrders()->sync([$this->order->id]);
    }

    private function employee(string $name): Employee
    {
        return Employee::create(['employee_name' => $name, 'department' => 'QC', 'designation' => 'Inspector', 'phone' => '000', 'joining_date' => '2026-01-01', 'status' => true]);
    }

    private function finishRun(): void
    {
        $this->run->update(['completed_at' => now(), 'verdict' => 'Pass']);
    }

    private function headerPayload(array $overrides = []): array
    {
        return array_merge([
            'inspection_type_id' => $this->inspection->inspection_type_id,
            'inspection_date' => '2026-11-20',
            'remarks' => 'Original remarks',
            'inspector_ids' => [$this->inspector->id],
            'customer_order_ids' => [$this->order->id],
        ], $overrides);
    }

    private function snapshot(): array
    {
        $i = $this->inspection->fresh();

        return [
            $i->inspection_type_id, $i->inspection_date?->toDateString() ?? (string) $i->inspection_date, $i->remarks,
            $i->inspectors()->pluck('employees.id')->all(), $i->customerOrders()->pluck('customer_orders.id')->all(),
        ];
    }

    public function test_each_header_change_is_refused_after_a_run_is_finished(): void
    {
        $this->finishRun();
        $otherType = InspectionType::create(['name' => 'Other type', 'status' => true]);
        $otherInspector = $this->employee('Inspector B');
        $before = $this->snapshot();

        $changes = [
            'type' => ['inspection_type_id' => $otherType->id],
            'date' => ['inspection_date' => '2026-01-01'],
            'remarks' => ['remarks' => 'Changed after issue'],
            'inspectors' => ['inspector_ids' => [$otherInspector->id]],
            'orders' => ['customer_order_ids' => []],
        ];

        foreach ($changes as $label => $change) {
            $this->put(route('inspections.update', $this->inspection), $this->headerPayload($change))
                ->assertRedirect(route('inspections.edit', $this->inspection))
                ->assertSessionHas('error', fn ($m) => str_contains($m, 'Reopen') && str_contains($m, '#'.$this->run->run_number));

            $this->assertSame($before, $this->snapshot(), "{$label} changed on a locked header");
        }
    }

    public function test_the_header_can_change_again_after_the_run_is_reopened(): void
    {
        $this->finishRun();
        $this->post(route('inspections.runs.reopen', [$this->inspection, $this->run]), ['reason' => 'Correct the inspection date'])
            ->assertSessionHas('success');

        $this->put(route('inspections.update', $this->inspection), $this->headerPayload(['inspection_date' => '2026-11-21', 'remarks' => 'Fixed']))
            ->assertSessionHas('success');

        $this->assertSame('Fixed', $this->inspection->fresh()->remarks);
    }

    public function test_the_header_can_change_while_no_run_is_finished(): void
    {
        $this->put(route('inspections.update', $this->inspection), $this->headerPayload(['remarks' => 'Still open']))
            ->assertSessionHas('success');

        $this->assertSame('Still open', $this->inspection->fresh()->remarks);
    }

    public function test_a_user_without_permission_still_gets_403(): void
    {
        $this->finishRun();
        $this->actingAs($this->userWithRole('Employee'));

        $this->put(route('inspections.update', $this->inspection), $this->headerPayload(['remarks' => 'x']))->assertForbidden();
    }

    public function test_reads_still_work_and_the_edit_page_shows_the_lock(): void
    {
        $this->finishRun();

        $this->get(route('inspections.show', $this->inspection))->assertOk();
        $this->get(route('inspections.edit', $this->inspection))
            ->assertOk()
            ->assertSee('Inspection details are locked', false)
            ->assertSee('<fieldset disabled', false);
    }

    public function test_a_new_run_can_still_be_added_to_an_inspection_with_a_finished_run(): void
    {
        $this->finishRun();
        $sample = $this->makeSample();

        $this->post(route('inspections.runs.store', $this->inspection), ['sample_id' => $sample->id.':'])
            ->assertRedirect();

        $this->assertSame(2, $this->inspection->runs()->count());
    }
}
