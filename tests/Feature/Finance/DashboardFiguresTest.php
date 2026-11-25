<?php

namespace Tests\Feature\Finance;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\Movement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-16: dashboard totals per currency, overdue derived from the due date, new movements counted. */
class DashboardFiguresTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private function invoice(string $currency, string $total, string $due, string $status = 'Sent', string $paid = '0.00'): CustomerInvoice
    {
        $cur = Currency::firstOrCreate(['currency_code' => $currency], ['currency_name' => $currency, 'symbol' => $currency, 'exchange_rate' => 1, 'status' => true]);
        $customer = Customer::create(['customer_name' => "C {$currency}", 'contact_person' => 'X', 'status' => true, 'currency_id' => $cur->id]);

        return CustomerInvoice::create([
            'invoice_number' => 'INV-'.uniqid(), 'customer_id' => $customer->id, 'invoice_date' => now()->subMonth()->toDateString(), 'due_date' => $due,
            'subtotal' => $total, 'tax_amount' => '0', 'discount_amount' => '0', 'total_amount' => $total,
            'amount_paid' => $paid, 'amount_due' => bcsub($total, $paid, 2), 'status' => $status,
        ]);
    }

    public function test_invoice_totals_are_grouped_by_currency_never_summed_across(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $this->invoice('EUR', '100.00', now()->addWeek()->toDateString());
        $this->invoice('EUR', '50.00', now()->addWeek()->toDateString());
        $this->invoice('USD', '70.00', now()->addWeek()->toDateString());

        $this->get(route('dashboard'))->assertOk()
            ->assertViewHas('totalInvoiced', fn ($t) => $t == ['EUR' => '150.00', 'USD' => '70.00'])
            ->assertViewHas('totalReceivable', fn ($t) => $t == ['EUR' => '150.00', 'USD' => '70.00'])
            ->assertSee('EUR 150')
            ->assertSee('USD 70')
            ->assertDontSee('PKR 220');
    }

    public function test_overdue_is_derived_from_the_due_date(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $past = $this->invoice('EUR', '100.00', now()->subDay()->toDateString());                 // overdue, status still "Sent"
        $this->invoice('EUR', '100.00', now()->toDateString());                                    // due today: not overdue
        $this->invoice('EUR', '100.00', now()->subDays(5)->toDateString(), 'Paid', '100.00');      // paid: never overdue
        $this->invoice('EUR', '100.00', now()->subDays(5)->toDateString(), 'Cancelled');           // cancelled: never overdue

        $this->assertSame([$past->id], CustomerInvoice::overdue()->pluck('id')->all());
        $this->assertSame('Overdue', $past->fresh()->display_status);
        $this->assertSame('Sent', $past->fresh()->status); // nothing written

        $this->get(route('dashboard'))->assertViewHas('overdueInvoices', 1);
    }

    public function test_overdue_movements_include_the_new_movement_system(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        Movement::create(['status' => 'Issued', 'issue_date' => now()->subDays(10)->toDateString(), 'expected_return_date' => now()->subDay()->toDateString()]);
        Movement::create(['status' => 'Issued', 'issue_date' => now()->subDays(10)->toDateString(), 'expected_return_date' => now()->addDay()->toDateString()]);
        Movement::create(['status' => 'Returned', 'issue_date' => now()->subDays(10)->toDateString(), 'expected_return_date' => now()->subDay()->toDateString(), 'actual_return_date' => now()->toDateString()]);

        $this->get(route('dashboard'))->assertViewHas('overdueMovements', 1);
    }
}
