<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank;
use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    public function run(): void
    {
        $hbl = Bank::firstOrCreate(['bank_name' => 'HBL'], [
            'bank_name' => 'HBL',
            'status'    => true,
        ]);

        $accounts = [
            ['account_name' => 'Main Cash',  'bank_id' => null,      'currency' => 'PKR', 'opening_balance' => 0],
            ['account_name' => 'Petty Cash', 'bank_id' => null,      'currency' => 'PKR', 'opening_balance' => 0],
            ['account_name' => 'HBL Bank',   'bank_id' => $hbl->id,  'currency' => 'PKR', 'opening_balance' => 0, 'account_number' => '0000-000000-00'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['account_name' => $account['account_name']], $account + ['status' => true]);
        }
    }
}
