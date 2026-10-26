<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CurrenciesSeeder::class,
            AccountsSeeder::class,
            ExpenseHeadsSeeder::class,
            InspectionTypeSeeder::class,
            DefectsSeeder::class,
            InspectionSectionSeeder::class,
            InspectionTypeSectionDefaultSeeder::class,
            SampleColorsSeeder::class,
            SampleSizesSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Demo data only on local/testing databases (TestDataSeeder also guards itself)
        if (app()->environment('local', 'testing')) {
            $this->call(TestDataSeeder::class);
        }
    }
}
