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
            SampleColorsSeeder::class,
            SampleSizesSeeder::class,
            AdminUserSeeder::class,
            TestDataSeeder::class,
        ]);
    }
}
