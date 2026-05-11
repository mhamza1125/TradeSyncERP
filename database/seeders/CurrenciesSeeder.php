<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrenciesSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['currency_name' => 'Pakistani Rupee', 'currency_code' => 'PKR', 'symbol' => '₨', 'exchange_rate' => 1.000000, 'is_default' => true],
            ['currency_name' => 'US Dollar',        'currency_code' => 'USD', 'symbol' => '$', 'exchange_rate' => 278.500000, 'is_default' => false],
            ['currency_name' => 'Euro',              'currency_code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 302.000000, 'is_default' => false],
            ['currency_name' => 'British Pound',     'currency_code' => 'GBP', 'symbol' => '£', 'exchange_rate' => 352.000000, 'is_default' => false],
            ['currency_name' => 'Chinese Yuan',      'currency_code' => 'CNY', 'symbol' => '¥', 'exchange_rate' => 38.500000, 'is_default' => false],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(['currency_code' => $currency['currency_code']], $currency + ['status' => true]);
        }
    }
}
