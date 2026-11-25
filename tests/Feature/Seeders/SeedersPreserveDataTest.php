<?php

namespace Tests\Feature\Seeders;

use App\Models\InspectionSection;
use App\Models\InspectionTypeSectionDefault;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\InspectionSectionSeeder;
use Database\Seeders\InspectionTypeSeeder;
use Database\Seeders\InspectionTypeSectionDefaultSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Re-running the production seeders must never delete or overwrite existing data (trade_test only). */
class SeedersPreserveDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_reseeding_keeps_admin_edits_to_sections_and_type_defaults(): void
    {
        $this->seed([InspectionTypeSeeder::class, InspectionSectionSeeder::class, InspectionTypeSectionDefaultSeeder::class]);

        $section = InspectionSection::firstOrFail();
        $section->update(['name' => 'Renamed by admin', 'default_data' => ['custom' => true], 'sort_order' => 999]);
        $default = InspectionTypeSectionDefault::firstOrFail();
        $default->update(['is_required' => ! $default->is_required, 'sort_order' => 777]);
        $countsBefore = [InspectionSection::count(), InspectionTypeSectionDefault::count()];

        $this->seed([InspectionTypeSeeder::class, InspectionSectionSeeder::class, InspectionTypeSectionDefaultSeeder::class]);

        $this->assertSame('Renamed by admin', $section->fresh()->name);
        $this->assertSame(['custom' => true], $section->fresh()->default_data);
        $this->assertSame(999, $section->fresh()->sort_order);
        $this->assertSame(777, $default->fresh()->sort_order);
        $this->assertSame($default->is_required, $default->fresh()->is_required);
        $this->assertSame($countsBefore, [InspectionSection::count(), InspectionTypeSectionDefault::count()]);
    }

    public function test_reseeding_keeps_custom_role_permissions_and_the_admin_password(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, AdminUserSeeder::class]);
        Role::findByName('Employee')->givePermissionTo('customers.view'); // customised in the Roles screen
        $admin = User::where('email', 'admin@erp.test')->firstOrFail();
        $admin->update(['password' => 'a-rotated-password']);

        $this->seed([RolesAndPermissionsSeeder::class, AdminUserSeeder::class]);

        $this->assertTrue(Role::findByName('Employee')->fresh()->hasPermissionTo('customers.view'));
        $this->assertTrue(Hash::check('a-rotated-password', $admin->fresh()->password));
    }
}
