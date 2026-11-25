<?php

namespace Tests\Feature\Finance;

use App\Http\Controllers\Finance\CustomerInvoiceController;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerPayment;
use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\Transaction;
use App\Services\Finance\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\BuildsFinance;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-10: deleting an expense or payment left its transaction in the ledger. Now: void both. */
class VoidInsteadOfDeleteTest extends TestCase
{
    use BuildsFinance, BuildsInspections, RefreshDatabase;

    private function expense(string $amount = '400.00'): array
    {
        $cash = $this->makeAccount('Cash', '1000.00');
        $txn = $this->postTxn('Expense', $cash, $amount, '2026-01-10');
        $expense = Expense::create([
            'expense_head_id' => ExpenseHead::firstOrCreate(['expense_name' => 'Fuel'], ['status' => true])->id,
            'account_id' => $cash->id, 'transaction_id' => $txn->id, 'amount' => $amount, 'expense_date' => '2026-01-10',
        ]);

        return [$cash, $txn, $expense];
    }

    private function payment(): array
    {
        $cash = $this->makeAccount('Bank', '0.00', 'Bank');
        $customer = Customer::create(['customer_name' => 'C', 'contact_person' => 'X', 'status' => true]);
        $invoice = CustomerInvoice::create([
            'invoice_number' => 'INV-1', 'customer_id' => $customer->id, 'invoice_date' => '2026-01-01', 'due_date' => '2026-02-01',
            'subtotal' => '100.00', 'tax_amount' => '0.00', 'discount_amount' => '0.00', 'total_amount' => '100.00',
            'amount_paid' => '0.00', 'amount_due' => '100.00', 'status' => 'Sent',
        ]);
        $txn = $this->postTxn('CustomerReceipt', $cash, '28000.00', '2026-01-15');
        $payment = CustomerPayment::create([
            'customer_id' => $customer->id, 'transaction_id' => $txn->id, 'payment_date' => '2026-01-15',
            'invoice_reference' => 'INV-1', 'foreign_currency' => 'USD', 'invoiced_amount_fc' => '100.00', 'received_fc' => '100.00',
            'deduction_fc' => '0.00', 'wh_tax_percent' => '0.00', 'wh_tax_amount_fc' => '0.00', 'exchange_rate' => '280.000000',
            'expected_pkr' => '28000.00', 'actual_pkr_received' => '28000.00', 'remittance_charges' => '0.00',
            'pkr_gain_loss' => '0.00', 'fc_gain_loss' => '0.00', 'account_id' => $cash->id,
        ]);
        CustomerInvoiceController::syncInvoiceStatus('INV-1');

        return [$cash, $txn, $payment, $invoice];
    }

    public function test_voiding_an_expense_voids_its_transaction_and_restores_the_balance(): void
    {
        $this->actingAs($this->userWithRole('Accountant'));
        [$cash, $txn, $expense] = $this->expense();
        $this->assertSame('600.00', app(LedgerService::class)->balanceAt($cash));

        $this->delete(route('expenses.destroy', $expense), ['void_reason' => 'Entered twice'])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertNotNull($expense->fresh()->voided_at);
        $this->assertSame('Entered twice', $expense->fresh()->void_reason);
        $this->assertNotNull($txn->fresh()->voided_at);
        $this->assertSame(auth()->id(), $txn->fresh()->voided_by);
        $this->assertModelExists($expense); // nothing hard-deleted
        $this->assertModelExists($txn);
        $this->assertSame('1000.00', app(LedgerService::class)->balanceAt($cash));
        $this->assertTrue(Activity::where('event', 'voided')->where('subject_type', Expense::class)->exists());

        $this->get(route('expenses.show', $expense))->assertOk()->assertSee('Entered twice')->assertDontSee('</i>Void', false);
        $this->get(route('expenses.index'))->assertOk()->assertSee('Voided');
    }

    public function test_void_requires_a_reason(): void
    {
        $this->actingAs($this->userWithRole('Accountant'));
        [, $txn, $expense] = $this->expense();

        $this->delete(route('expenses.destroy', $expense), ['void_reason' => ''])->assertSessionHasErrors('void_reason');

        $this->assertNull($expense->fresh()->voided_at);
        $this->assertNull($txn->fresh()->voided_at);
    }

    public function test_cannot_void_twice(): void
    {
        $this->actingAs($this->userWithRole('Accountant'));
        [, , $expense] = $this->expense();
        $this->delete(route('expenses.destroy', $expense), ['void_reason' => 'Wrong account']);

        $this->delete(route('expenses.destroy', $expense), ['void_reason' => 'Again'])->assertSessionHas('error');
        $this->assertSame('Wrong account', $expense->fresh()->void_reason); // expenses have no edit route
    }

    public function test_voiding_a_payment_reopens_its_invoice(): void
    {
        $this->actingAs($this->userWithRole('Accountant'));
        [$bank, $txn, $payment, $invoice] = $this->payment();
        $this->assertSame('Paid', $invoice->fresh()->status);

        $this->delete(route('customer-payments.destroy', $payment), ['void_reason' => 'Bounced'])->assertRedirect();

        $this->assertNotNull($payment->fresh()->voided_at);
        $this->assertNotNull($txn->fresh()->voided_at);
        $this->assertSame('0.00', (string) $invoice->fresh()->amount_paid);
        $this->assertSame('100.00', (string) $invoice->fresh()->amount_due);
        $this->assertSame('0.00', app(LedgerService::class)->balanceAt($bank));
        $this->get(route('customer-payments.edit', $payment))->assertRedirect()->assertSessionHas('error');
    }

    public function test_void_needs_the_delete_permission(): void
    {
        $this->actingAs($this->userWithRole('Employee'));
        [, , $expense] = $this->expense();

        $this->delete(route('expenses.destroy', $expense), ['void_reason' => 'x'])->assertForbidden();
        $this->assertNull($expense->fresh()->voided_at);
    }

    public function test_dashboard_excludes_voided_expenses(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $cash = $this->makeAccount('Cash', '0.00');
        $head = ExpenseHead::firstOrCreate(['expense_name' => 'Fuel'], ['status' => true]);
        foreach (['100.00', '250.00'] as $amount) {
            $txn = $this->postTxn('Expense', $cash, $amount, now()->toDateString());
            $expenses[] = Expense::create(['expense_head_id' => $head->id, 'account_id' => $cash->id, 'transaction_id' => $txn->id, 'amount' => $amount, 'expense_date' => now()->toDateString()]);
        }
        $this->delete(route('expenses.destroy', $expenses[1]), ['void_reason' => 'Duplicate']);

        $this->get(route('dashboard'))->assertViewHas('expensesThisMonth', fn ($v) => bccomp((string) $v, '100', 2) === 0);
        $this->assertSame(2, Transaction::count());
    }
}
