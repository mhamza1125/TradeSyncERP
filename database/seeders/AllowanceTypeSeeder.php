<?php

namespace Database\Seeders;

use App\Models\AllowanceType;
use Illuminate\Database\Seeder;

class AllowanceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Petrol Allowance',  'description' => 'Monthly fuel/petrol reimbursement'],
            ['name' => 'Mobile Package',     'description' => 'Monthly mobile/data package allowance'],
            ['name' => 'Travel Allowance',   'description' => 'Daily or monthly travel cost reimbursement'],
            ['name' => 'Other',              'description' => 'Miscellaneous allowance'],
        ];

        foreach ($types as $type) {
            AllowanceType::firstOrCreate(
                ['name' => $type['name']],
                ['description' => $type['description'], 'is_active' => true]
            );
        }
    }
}
