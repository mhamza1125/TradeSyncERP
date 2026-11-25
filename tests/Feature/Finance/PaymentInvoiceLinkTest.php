<?php

namespace Tests\Feature\Finance;

use App\Http\Controllers\Finance\CustomerInvoiceController;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsFinance;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Finance review FN-3: payments were linked to invoices only by free text. */
class PaymentInvoiceLinkTest extends TestCase
{
    use BuildsFinance, BuildsInspections, RefreshDatabase;

    private Customer $customer;

    private Customer $other;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->userWithRole('Accountant'));
        $this->customer = Customer::create(['customer_name' => 'A', 'contact_person' => 'X', 'status' => true]);
        $this->other = Customer::create(['customer_name' => 'B', 'contact_person' => 'Y', 'status' => true]);
    }

    private function invoice(Customer $c, string $number, string $total = '100.00'): CustomerInvoice
    {
        return CustomerInvoice::create([
            'invoice_number' => $number, 'customer_id' => $c->id, 'invoice_date' => '2026-01-01', 'due_date' => '2026-12-31',
            'subtotal' => $total, 'tax_amount' => '0', 'discount_amount' => '0', 'total_amount' => $total,
            'amount_paid' => '0', 'amount_due' => $total, 'status' => 'Sent',
        ]);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'customer_id' => $this->customer->id, 'payment_date' => '2026-02-01', 'foreign_currency' => 'USD',
            'invoiced_amount_fc' => '100.00', 'deduction_fc' => '0', 'received_fc' => '40.00', 'wh_tax_percent' => '0',
            'exchange_rate' => '280', 'actual_pkr_received' => '11200.00', 'remittance_charges' => '0',
            'debit_account_id' => $this->makeAccount('Bank', '0.00', 'Bank '.uniqid())->id,
        ], $extra);
    }

    public function test_payment_is_linked_to_the_invoice_by_id(): void
    {
        $invoice = $this->invoice($this->customer, 'INV-A1');

        $this->post(route('customer-payments.store'), $this->payload(['invoice_reference' => 'INV-A1']))->assertSessionHasNoErrors();

        $payment = CustomerPayment::firstOrFail();
        $this->assertSame($invoice->id, $payment->customer_invoice_id);
        $this->assertSame('40.00', (string) $invoice->fresh()->amount_paid);
        $this->assertSame('Partial', $invoice->fresh()->status);
    }

    public function test_reference_must_be_an_invoice_of_the_same_customer(): void
    {
        $this->invoice($this->other, 'INV-B1');

        $this->post(route('customer-payments.store'), $this->payload(['invoice_reference' => 'INV-B1']))
            ->assertSessionHasErrors('invoice_reference');
        $this->post(route('customer-payments.store'), $this->payload(['invoice_reference' => 'DOES-NOT-EXIST']))
            ->assertSessionHasErrors('invoice_reference');

        $this->assertSame(0, CustomerPayment::count());
    }

    public function test_payment_without_invoice_reference_is_still_allowed(): void
    {
        $this->post(route('customer-payments.store'), $this->payload(['invoice_reference' => null]))->assertSessionHasNoErrors();

        $this->assertNull(CustomerPayment::firstOrFail()->customer_invoice_id);
    }

    public function test_linked_payment_counts_even_if_its_reference_text_differs(): void
    {
        $invoice = $this->invoice($this->customer, 'INV-A2');
        $this->post(route('customer-payments.store'), $this->payload(['invoice_reference' => 'INV-A2']));
        CustomerPayment::query()->update(['invoice_reference' => 'typo']); // the text no longer matters once linked

        CustomerInvoiceController::syncInvoiceStatus('INV-A2');

        $this->assertSame('40.00', (string) $invoice->fresh()->amount_paid);
    }

    public function test_legacy_text_only_payments_still_count(): void
    {
        $invoice = $this->invoice($this->customer, 'INV-A3');
        $txn = $this->postTxn('CustomerReceipt', $this->makeAccount('Bank', '0.00', 'Legacy bank'), '28000.00', '2026-01-15');
        CustomerPayment::create([
            'customer_id' => $this->customer->id, 'transaction_id' => $txn->id, 'payment_date' => '2026-01-15',
            'invoice_reference' => 'INV-A3', 'foreign_currency' => 'USD', 'invoiced_amount_fc' => '100', 'received_fc' => '100.00',
            'deduction_fc' => '0', 'wh_tax_percent' => '0', 'wh_tax_amount_fc' => '0', 'exchange_rate' => '280',
            'expected_pkr' => '28000', 'actual_pkr_received' => '28000', 'remittance_charges' => '0', 'pkr_gain_loss' => '0', 'fc_gain_loss' => '0',
            'account_id' => $txn->credit_account_id,
        ]);

        CustomerInvoiceController::syncInvoiceStatus('INV-A3');

        $this->assertSame('Paid', $invoice->fresh()->status);
    }

    public function test_editing_a_legacy_payment_keeps_its_unmatched_reference(): void
    {
        $txn = $this->postTxn('CustomerReceipt', $this->makeAccount('Bank', '0.00', 'Legacy bank'), '100.00', '2026-01-15');
        $payment = CustomerPayment::create([
            'customer_id' => $this->customer->id, 'transaction_id' => $txn->id, 'payment_date' => '2026-01-15',
            'invoice_reference' => 'OLD-FREE-TEXT', 'foreign_currency' => 'USD', 'invoiced_amount_fc' => '1', 'received_fc' => '1',
            'deduction_fc' => '0', 'wh_tax_percent' => '0', 'wh_tax_amount_fc' => '0', 'exchange_rate' => '100',
            'expected_pkr' => '100', 'actual_pkr_received' => '100', 'remittance_charges' => '0', 'pkr_gain_loss' => '0', 'fc_gain_loss' => '0',
            'account_id' => $txn->credit_account_id,
        ]);

        $this->put(route('customer-payments.update', $payment), $this->payload(['invoice_reference' => 'OLD-FREE-TEXT', 'remarks' => 'edited']))
            ->assertSessionHasNoErrors();

        $this->assertSame('edited', $payment->fresh()->remarks);
        $this->assertNull($payment->fresh()->customer_invoice_id);
    }
}
