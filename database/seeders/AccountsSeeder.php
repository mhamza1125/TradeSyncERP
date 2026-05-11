<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['account_name' => 'Main Cash',  'account_type' => 'Cash', 'currency' => 'PKR', 'opening_balance' => 0],
            ['account_name' => 'Petty Cash', 'account_type' => 'Cash', 'currency' => 'PKR', 'opening_balance' => 0],
            ['account_name' => 'HBL Bank',   'account_type' => 'Bank', 'currency' => 'PKR', 'opening_balance' => 0],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['account_name' => $account['account_name']], $account + ['status' => true]);
        }
    }
}
