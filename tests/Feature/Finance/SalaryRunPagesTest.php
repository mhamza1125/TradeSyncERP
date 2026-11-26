<?php

namespace Tests\Feature\Finance;

use App\Models\AllowanceType;
use App\Models\Employee;
use App\Models\SalaryRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsFinance;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** The compact salary create / edit / run pages render and still post the same fields. */
class SalaryRunPagesTest extends TestCase
{
    use BuildsFinance, BuildsInspections, RefreshDatabase;

    private function draftRun(): SalaryRun
    {
        $account = $this->makeAccount('Cash', '100000.00');
        Employee::create(['employee_name' => 'Ali', 'department' => 'QC', 'designation' => 'Inspector', 'phone' => '000', 'joining_date' => '2026-01-01', 'status' => true]);
        $this->post(route('salary.store'), ['month' => '2026-10', 'account_id' => $account->id, 'working_days' => 26]);

        return SalaryRun::with('lines')->where('month', '2026-10')->sole();
    }

    public function test_create_and_edit_render_the_compact_form(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $this->get(route('salary.create'))->assertOk()->assertSee('ts-dense', false)->assertSee('name="month"', false);

        $run = $this->draftRun();
        $this->get(route('salary.edit', $run))->assertOk()->assertSee('value="2026-10"', false)->assertSee('name="remarks"', false);
    }

    public function test_a_draft_run_shows_compact_inputs_and_saving_a_line_works(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();
        $line = $run->lines->first();
        $transport = AllowanceType::create(['name' => 'Transport', 'is_active' => true]);

        $this->get(route('salary.show', $run))->assertOk()
            ->assertSee('ts-sheet', false)
            ->assertSee('name="lines[0][basic_salary]"', false)
            ->assertSee('name="lines[0][late_minutes]"', false)
            ->assertSee('name="lines[0][line_allowances][0][amount]"', false)
            ->assertSee('aria-label="Bonus for Ali"', false);

        $this->put(route('salary.lines.update', $run), [
            'account_id' => $run->account_id, 'working_days' => 26,
            'lines' => [[
                'id' => $line->id, 'basic_salary' => '30000', 'bonus' => '500', 'deduction' => '0', 'advance' => '0',
                'allowances' => '200', 'line_allowances' => [['allowance_type_id' => $transport->id, 'amount' => '200']],
                'leave_days' => 0, 'deductible_leaves' => 0, 'leave_deduction_amount' => '0',
                'loan_balance' => '0', 'loan_deduction' => '0', 'late_hours' => 0, 'late_minutes' => 0, 'late_deduction' => '0',
            ]],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertEquals('30700.00', $line->fresh()->net_payable);
    }

    public function test_a_paid_run_shows_values_without_inputs(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();
        $this->post(route('salary.pay', $run), ['payment_date' => '2026-10-31']);

        $this->get(route('salary.show', $run))->assertOk()
            ->assertDontSee('type="number" step="0.01" min="0"', false)
            ->assertDontSee('Save Changes')
            ->assertSee('Total net payable');
    }

    /** One line posted with the given overrides (everything else 0). */
    private function saveLine(SalaryRun $run, array $overrides)
    {
        $line = $run->lines->first();

        return $this->put(route('salary.lines.update', $run), [
            'account_id' => $run->account_id,
            'lines' => [array_merge([
                'id' => $line->id, 'basic_salary' => '31200', 'bonus' => '0', 'deduction' => '0', 'advance' => '0', 'allowances' => '0',
                'leave_days' => 0, 'deductible_leaves' => 0, 'leave_deduction_amount' => '0', 'loan_balance' => '0', 'loan_deduction' => '0',
                'late_hours' => 0, 'late_minutes' => 0,
            ], $overrides)],
        ]);
    }

    public function test_the_late_deduction_is_part_of_the_line_net_pay_and_the_run_total(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();

        $this->saveLine($run, ['late_hours' => 2, 'late_deduction' => '300'])->assertSessionHasNoErrors();

        $line = $run->lines->first()->fresh();
        $this->assertEquals('30900.00', $line->net_payable);
        $this->assertEquals('30900.00', $run->fresh()->total_net_payable);
    }

    public function test_without_an_amount_the_late_deduction_is_calculated_from_the_working_days(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun(); // 26 working days

        // 31,200 ÷ 26 ÷ 8 = 150 per hour; 2h 30m late = 375
        $this->saveLine($run, ['late_hours' => 2, 'late_minutes' => 30])->assertSessionHasNoErrors();

        $this->assertEquals('375.00', $run->lines->first()->fresh()->late_deduction);
    }

    public function test_an_entered_zero_waives_the_late_deduction(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();

        $this->saveLine($run, ['late_hours' => 2, 'late_deduction' => '0'])->assertSessionHasNoErrors();

        $line = $run->lines->first()->fresh();
        $this->assertEquals('0.00', $line->late_deduction);
        $this->assertEquals('31200.00', $line->net_payable);
    }

    public function test_an_allowance_amount_without_a_type_is_refused(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();

        $this->saveLine($run, ['allowances' => '500', 'line_allowances' => [['allowance_type_id' => null, 'amount' => '500']]])
            ->assertSessionHasErrors('lines.0.line_allowances.0.allowance_type_id');

        // An untyped row with amount 0 (the default empty row) is fine
        $this->saveLine($run, ['line_allowances' => [['allowance_type_id' => null, 'amount' => '0']]])->assertSessionHasNoErrors();
    }
}
