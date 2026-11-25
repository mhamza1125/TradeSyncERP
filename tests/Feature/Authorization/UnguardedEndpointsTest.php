<?php

namespace Tests\Feature\Authorization;

use App\Models\AllowanceType;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\InspectionRunSection;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-02: endpoints that had no permission check at all. */
class UnguardedEndpointsTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    // ── Activity log ─────────────────────────────────────────────────────────

    public function test_roleless_user_cannot_read_the_activity_log(): void
    {
        $user = $this->userWithoutRole();
        Customer::create(['customer_name' => 'Logged Co', 'contact_person' => 'X', 'status' => true]);
        $activity = Activity::firstOrFail();

        $this->actingAs($user)->get(route('activities.index'))->assertForbidden();
        $this->actingAs($user)->get(route('activities.show', $activity))->assertForbidden();
    }

    public function test_admin_can_read_the_activity_log(): void
    {
        $this->actingAs($this->userWithRole('Admin'))->get(route('activities.index'))->assertOk();
    }

    public function test_activity_permission_is_admin_only(): void
    {
        $this->seedPermissions();

        foreach (['Lab Manager', 'Accountant', 'Employee'] as $role) {
            $this->assertFalse(Role::findByName($role)->hasPermissionTo('activities.view'), $role);
        }
        $this->assertTrue(Role::findByName('Admin')->hasPermissionTo('activities.view'));
    }

    // ── Allowance types ──────────────────────────────────────────────────────

    public function test_roleless_user_cannot_use_allowance_types(): void
    {
        $user = $this->userWithoutRole();
        $type = AllowanceType::create(['name' => 'Fuel', 'is_active' => true]);

        $this->actingAs($user)->get(route('allowance-types.index'))->assertForbidden();
        $this->actingAs($user)->get(route('allowance-types.create'))->assertForbidden();
        $this->actingAs($user)->post(route('allowance-types.store'), ['name' => 'Hack'])->assertForbidden();
        $this->actingAs($user)->get(route('allowance-types.edit', $type))->assertForbidden();
        $this->actingAs($user)->put(route('allowance-types.update', $type), ['name' => 'Hack'])->assertForbidden();
        $this->actingAs($user)->delete(route('allowance-types.destroy', $type))->assertForbidden();

        $this->assertDatabaseMissing('allowance_types', ['name' => 'Hack']);
        $this->assertDatabaseHas('allowance_types', ['id' => $type->id]);
    }

    public function test_accountant_can_list_allowance_types(): void
    {
        $this->actingAs($this->userWithRole('Accountant'))->get(route('allowance-types.index'))->assertOk();
    }

    // ── Generic attachments ──────────────────────────────────────────────────

    public function test_roleless_user_cannot_upload_or_delete_attachments(): void
    {
        $user = $this->userWithoutRole();
        $customer = Customer::create(['customer_name' => 'C', 'contact_person' => 'X', 'status' => true]);
        $existing = $customer->attachments()->create(['title' => 't', 'file_name' => 'a.pdf', 'file_path' => 'customers/1/a.pdf']);

        $this->actingAs($user)->post(route('attachments.store', ['customers', $customer->id]), [
            'attachments' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')],
            'attachment_titles' => ['A'],
        ])->assertForbidden();

        $this->actingAs($user)->delete(route('attachments.destroy', $existing))->assertForbidden();

        $this->assertSame(1, Attachment::count());
    }

    public function test_upload_requires_edit_permission_on_that_parent_type(): void
    {
        $user = $this->userWithPermissions('customers.edit');
        $customer = Customer::create(['customer_name' => 'C', 'contact_person' => 'X', 'status' => true]);
        $supplier = Supplier::create(['name' => 'S', 'status' => true]);

        $this->actingAs($user)->post(route('attachments.store', ['customers', $customer->id]), [
            'attachments' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')],
            'attachment_titles' => ['A'],
        ])->assertRedirect();
        $this->assertSame(1, $customer->attachments()->count());

        $this->actingAs($user)->post(route('attachments.store', ['suppliers', $supplier->id]), [
            'attachments' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')],
            'attachment_titles' => ['A'],
        ])->assertForbidden();
        $this->assertSame(0, $supplier->attachments()->count());
    }

    public function test_delete_checks_the_permission_of_the_attachments_own_parent(): void
    {
        $user = $this->userWithPermissions('customers.edit');
        $supplier = Supplier::create(['name' => 'S', 'status' => true]);
        $supplierFile = $supplier->attachments()->create(['title' => 't', 'file_name' => 'a.pdf', 'file_path' => 'suppliers/1/a.pdf']);

        $this->actingAs($user)->delete(route('attachments.destroy', $supplierFile))->assertForbidden();
        $this->assertModelExists($supplierFile);
    }

    public function test_generic_route_cannot_delete_inspection_evidence(): void
    {
        $admin = $this->userWithRole('Admin');
        $inspection = $this->makeInspection();
        $run = $this->makeRun($inspection, [[$this->makeSection('photos_test', 'images'), [], 'complete']]);
        $runSection = InspectionRunSection::where('inspection_run_id', $run->id)->firstOrFail();
        $photo = $runSection->attachments()->create(['title' => 'p', 'file_name' => 'p.jpg', 'file_path' => 'inspection-sections/1/p.jpg']);

        $this->actingAs($admin)->delete(route('attachments.destroy', $photo))->assertForbidden();
        $this->assertModelExists($photo);
    }

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function test_dashboard_does_not_compute_or_show_finance_for_users_without_permission(): void
    {
        $response = $this->actingAs($this->userWithoutRole())->get(route('dashboard'));

        $response->assertOk()
            ->assertViewHas('totalReceivable', null)
            ->assertViewHas('totalInvoiced', null)
            ->assertViewHas('paymentsThisMonth', null)
            ->assertViewHas('expensesThisMonth', null)
            ->assertViewHas('recentActivity', null)
            ->assertDontSee('Outstanding Receivables')
            ->assertDontSee('Finance Overview')
            ->assertDontSee(route('activities.index'))
            ->assertDontSee(route('allowance-types.index'));
    }

    public function test_dashboard_still_shows_finance_to_admin(): void
    {
        $this->actingAs($this->userWithRole('Admin'))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Outstanding Receivables')
            ->assertSee('Finance Overview')
            ->assertSee(route('activities.index'));
    }
}
