<?php

namespace Database\Seeders;

use App\Models\ExpenseHead;
use Illuminate\Database\Seeder;

class ExpenseHeadsSeeder extends Seeder
{
    public function run(): void
    {
        $heads = [
            'Rent',
            'Electricity',
            'Petrol',
            'Salaries',
            'Maintenance',
            'Miscellaneous',
            'Internet & Telecom',
            'Office Supplies',
            'Travel & Transport',
        ];

        foreach ($heads as $name) {
            ExpenseHead::firstOrCreate(['expense_name' => $name], ['status' => true]);
        }
    }
}
