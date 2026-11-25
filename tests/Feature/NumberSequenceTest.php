<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\Inspection;
use App\Models\InspectionType;
use App\Services\Finance\InvoiceNumberService;
use App\Services\NumberSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-19 / QC proposal d2: numbers come from a locked sequence, never max(id)+1. */
class NumberSequenceTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    public function test_sequence_is_seeded_once_and_then_increments(): void
    {
        $seq = app(NumberSequenceService::class);
        $seeds = 0;
        $seed = function () use (&$seeds) {
            $seeds++;

            return 41;
        };

        $this->assertSame(41, $seq->next('test', $seed));
        $this->assertSame(42, $seq->next('test', $seed));
        $this->assertSame(43, $seq->peek('test', $seed));
        $this->assertSame(43, $seq->next('test', $seed));
        $this->assertSame(1, $seeds);
    }

    public function test_report_numbers_continue_the_old_series_and_are_never_reused(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $old = $this->makeInspection(['report_number' => 'RPT-OLD']); // existing data, id N
        $type = InspectionType::firstOrFail();
        $payload = ['inspection_type_id' => $type->id, 'inspection_date' => now()->toDateString()];
        $year = now()->year;

        $this->post(route('inspections.store'), $payload)->assertSessionHasNoErrors();
        $first = Inspection::latest('id')->firstOrFail();
        $this->assertSame(sprintf('RPT-%d-%05d', $year, $old->id + 1), $first->report_number); // same as before for the next one

        $first->delete(); // the newest inspection is removed…
        $this->post(route('inspections.store'), $payload)->assertSessionHasNoErrors();
        $second = Inspection::latest('id')->firstOrFail();

        $this->assertNotSame($first->report_number, $second->report_number); // …and its number is not handed out again
        $this->assertSame(sprintf('RPT-%d-%05d', $year, $old->id + 2), $second->report_number);
    }

    public function test_invoice_numbers_continue_from_the_existing_invoices(): void
    {
        $customer = Customer::create(['customer_name' => 'C', 'contact_person' => 'X', 'status' => true]);
        $existing = CustomerInvoice::create([
            'invoice_number' => 'LEGACY-1', 'customer_id' => $customer->id, 'invoice_date' => '2026-01-01', 'due_date' => '2026-02-01',
            'subtotal' => 1, 'tax_amount' => 0, 'discount_amount' => 0, 'total_amount' => 1, 'amount_paid' => 0, 'amount_due' => 1, 'status' => 'Draft',
        ]);
        $numbers = app(InvoiceNumberService::class);

        $this->assertSame($existing->id + 1, $numbers->nextSequentialId()); // preview, nothing taken
        $a = $numbers->generateNext();
        $b = $numbers->generateNext();

        $this->assertNotSame($a, $b);
        $this->assertSame($numbers->format($numbers->pattern(), $existing->id + 1), $a);
        $this->assertSame($existing->id + 3, $numbers->nextSequentialId());
    }

    public function test_run_numbers_increment_per_inspection(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $inspection = $this->makeInspection();
        $sample = $this->makeSample();

        $this->post(route('inspections.runs.store', $inspection), ['sample_id' => $sample->id.':'])->assertRedirect();
        $this->post(route('inspections.runs.store', $inspection), ['sample_id' => $sample->id.':'])->assertRedirect();

        $this->assertSame([1, 2], $inspection->runs()->orderBy('id')->pluck('run_number')->all());
    }
}
