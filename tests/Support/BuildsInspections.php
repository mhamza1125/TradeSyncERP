<?php

namespace Tests\Support;

use App\Models\Customer;
use App\Models\Inspection;
use App\Models\InspectionRun;
use App\Models\InspectionRunSection;
use App\Models\InspectionSection;
use App\Models\InspectionType;
use App\Models\ProductCategory;
use App\Models\Sample;
use App\Models\User;
use App\Services\Inspection\InspectionRunLockService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

/**
 * Minimal inspection graph for feature tests (trade_test only — see tests/TestCase.php).
 * Use together with RefreshDatabase.
 */
trait BuildsInspections
{
    protected function seedPermissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function userWithRole(string $role): User
    {
        $this->seedPermissions();

        return tap(User::factory()->create())->assignRole($role);
    }

    protected function userWithPermissions(string ...$permissions): User
    {
        $this->seedPermissions();

        return tap(User::factory()->create())->givePermissionTo($permissions);
    }

    protected function userWithoutRole(): User
    {
        $this->seedPermissions();

        return User::factory()->create();
    }

    protected function makeInspection(array $attributes = []): Inspection
    {
        $type = InspectionType::firstOrCreate(['name' => 'Test Final Inspection'], ['status' => true]);

        return Inspection::create(array_merge([
            'report_number' => 'TEST-'.Str::upper(Str::random(8)),
            'inspection_type_id' => $type->id,
            'inspection_date' => now()->toDateString(),
            'overall_status' => 'Pending',
        ], $attributes));
    }

    protected function makeSample(): Sample
    {
        $customer = Customer::create(['customer_name' => 'Test Customer', 'contact_person' => 'Test Contact', 'status' => true]);
        $category = ProductCategory::firstOrCreate(['category_name' => 'Test Category'], ['status' => true]);

        return Sample::create([
            'sample_code' => 'S-'.Str::upper(Str::random(8)),
            'category_id' => $category->id,
            'customer_id' => $customer->id,
            'product_name' => 'Test Product',
            'receive_date' => now()->toDateString(),
        ]);
    }

    protected function makeSection(string $slug, string $type, array $defaultData = [], string $skipPolicy = 'mandatory'): InspectionSection
    {
        return InspectionSection::firstOrCreate(['slug' => $slug], [
            'name' => Str::headline($slug),
            'section_type' => $type,
            'default_data' => $defaultData,
            'is_active' => true,
            'skip_policy' => $skipPolicy,
        ]);
    }

    /**
     * @param  array<int, array{0: InspectionSection, 1?: array, 2?: string}>  $sections  [section, data, status]
     */
    protected function makeRun(Inspection $inspection, array $sections = [], array $attributes = []): InspectionRun
    {
        $run = $inspection->runs()->create(array_merge([
            'sample_id' => $this->makeSample()->id,
            'run_number' => ($inspection->runs()->max('run_number') ?? 0) + 1,
            'verdict' => 'Pending',
        ], $attributes));

        // Fixtures may describe an already-finished run, which is otherwise read-only.
        InspectionRunLockService::bypass(function () use ($sections, $run) {
            foreach ($sections as $i => $spec) {
                InspectionRunSection::create([
                    'inspection_run_id' => $run->id,
                    'inspection_section_id' => $spec[0]->id,
                    'sort_order' => ($i + 1) * 10,
                    'data' => $spec[1] ?? $spec[0]->default_data,
                    'status' => $spec[2] ?? 'pending',
                ]);
            }
        });

        return $run->fresh();
    }
}
