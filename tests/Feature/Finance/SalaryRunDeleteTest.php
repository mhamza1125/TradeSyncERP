<?php

namespace Tests\Feature\Finance;

use App\Models\Employee;
use App\Models\SalaryRun;
use App\Models\SalaryRunLine;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuildsFinance;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** A Draft salary run can be deleted (it has no transaction); a paid run never can. */
class SalaryRunDeleteTest extends TestCase
{
    use BuildsFinance, BuildsInspections, RefreshDatabase;

    private function draftRun(): SalaryRun
    {
        $account = $this->makeAccount('Cash', '100000.00');
        Employee::create(['employee_name' => 'Ali', 'department' => 'QC', 'designation' => 'Inspector', 'phone' => '000', 'joining_date' => '2026-01-01', 'status' => true]);

        $this->post(route('salary.store'), ['month' => '2026-10', 'account_id' => $account->id])->assertRedirect();

        return SalaryRun::where('month', '2026-10')->sole();
    }

    public function test_a_draft_run_and_its_lines_are_deleted_and_the_month_can_be_used_again(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();
        $this->assertGreaterThan(0, SalaryRunLine::where('salary_run_id', $run->id)->count());

        $this->delete(route('salary.destroy', $run))
            ->assertRedirect(route('salary.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($run);
        $this->assertSame(0, SalaryRunLine::where('salary_run_id', $run->id)->count());

        // The unique month is free again, so a wrong draft can be recreated.
        $this->post(route('salary.store'), ['month' => '2026-10', 'account_id' => $run->account_id])->assertSessionHasNoErrors();
    }

    public function test_a_paid_run_is_never_deleted_and_its_transaction_stays(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();
        $this->post(route('salary.pay', $run), ['payment_date' => '2026-10-31'])->assertRedirect();
        $run->refresh();
        $this->assertTrue($run->isPaid());

        $this->delete(route('salary.destroy', $run))->assertSessionHas('error');

        $this->assertModelExists($run);
        $this->assertModelExists(Transaction::findOrFail($run->transaction_id));
    }

    public function test_the_model_refuses_to_delete_a_paid_run(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();
        $this->post(route('salary.pay', $run), ['payment_date' => '2026-10-31']);

        $this->expectException(\RuntimeException::class);
        $run->fresh()->delete();
    }

    public function test_deleting_needs_the_salary_delete_permission(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();

        $this->actingAs($this->userWithPermissions('salary.index', 'salary.edit'));
        $this->delete(route('salary.destroy', $run))->assertForbidden();
        $this->assertModelExists($run);
    }

    public function test_the_delete_button_shows_only_for_draft_runs(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $run = $this->draftRun();

        $this->get(route('salary.index'))->assertSee('action="'.route('salary.destroy', $run).'"', false);
        $this->get(route('salary.show', $run))->assertSee('action="'.route('salary.destroy', $run).'"', false);

        $this->post(route('salary.pay', $run), ['payment_date' => '2026-10-31']);

        $this->get(route('salary.index'))->assertDontSee('action="'.route('salary.destroy', $run).'"', false);
        $this->get(route('salary.show', $run))->assertDontSee('action="'.route('salary.destroy', $run).'"', false);
    }
}
