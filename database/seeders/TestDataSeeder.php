<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank;
use App\Models\Brand;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\ProductCategory;
use App\Models\Sample;
use App\Models\TestingParameter;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Transaction;
use App\Models\VendorBill;
use App\Models\VendorBillItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing test data in correct dependency order
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        VendorBillItem::truncate();
        VendorBill::truncate();
        CustomerPayment::truncate();
        Expense::truncate();
        Transaction::truncate();
        Sample::withTrashed()->forceDelete();
        Brand::truncate();
        TestingParameter::truncate();
        ProductCategory::truncate();
        Employee::withTrashed()->forceDelete();
        Customer::withTrashed()->forceDelete();
        Vendor::withTrashed()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ─── Currencies ────────────────────────────────────────────────────
        $usd = Currency::firstOrCreate(['currency_code' => 'USD'], [
            'currency_name' => 'US Dollar',
            'symbol'        => '$',
            'exchange_rate' => 278.50,
            'is_default'    => false,
            'status'        => true,
        ]);
        $eur = Currency::firstOrCreate(['currency_code' => 'EUR'], [
            'currency_name' => 'Euro',
            'symbol'        => '€',
            'exchange_rate' => 302.75,
            'is_default'    => false,
            'status'        => true,
        ]);
        $gbp = Currency::firstOrCreate(['currency_code' => 'GBP'], [
            'currency_name' => 'British Pound',
            'symbol'        => '£',
            'exchange_rate' => 352.40,
            'is_default'    => false,
            'status'        => true,
        ]);

        // ─── Bank & Accounts ───────────────────────────────────────────────
        $hbl = Bank::firstOrCreate(['bank_name' => 'Habib Bank Limited'], [
            'branch_name'    => 'Main Branch, Karachi',
            'account_number' => '01234567890123',
            'swift_code'     => 'HABBPKKA',
            'status'         => true,
        ]);
        $ubl = Bank::firstOrCreate(['bank_name' => 'United Bank Limited'], [
            'branch_name'    => 'SITE Branch, Karachi',
            'account_number' => '98765432109876',
            'swift_code'     => 'UNILPKKA',
            'status'         => true,
        ]);

        $cashAccount = Account::firstOrCreate(['account_name' => 'Main Cash'], [
            'account_type'    => 'Cash',
            'bank_id'         => null,
            'currency'        => 'PKR',
            'opening_balance' => 150000.00,
            'status'          => true,
        ]);
        $hblAccount = Account::firstOrCreate(['account_name' => 'HBL Current Account'], [
            'account_type'    => 'Bank',
            'bank_id'         => $hbl->id,
            'currency'        => 'PKR',
            'opening_balance' => 2500000.00,
            'status'          => true,
        ]);
        $ublAccount = Account::firstOrCreate(['account_name' => 'UBL Savings Account'], [
            'account_type'    => 'Bank',
            'bank_id'         => $ubl->id,
            'currency'        => 'PKR',
            'opening_balance' => 1800000.00,
            'status'          => true,
        ]);

        // ─── Expense Heads ─────────────────────────────────────────────────
        collect([
            'Office Rent', 'Electricity', 'Internet & Phone', 'Petrol & Transport',
            'Office Supplies', 'Lab Equipment Maintenance', 'Miscellaneous',
        ])->each(fn($name) => ExpenseHead::firstOrCreate(['expense_name' => $name], ['status' => true]));

        // ─── Customers ─────────────────────────────────────────────────────
        $customers = collect([
            [
                'customer_name'            => 'Allied Textiles Ltd',
                'contact_person'           => 'Ahmed Raza',
                'phone'                    => '+92-21-3456789',
                'email'                    => 'ahmed@alliedtextiles.com',
                'address'                  => 'SITE Area, Karachi',
                'currency_id'              => $usd->id,
                'default_currency'         => 'USD',
                'opening_balance'          => 250000.00,
                'opening_balance_currency' => 'PKR',
            ],
            [
                'customer_name'            => 'Global Fabrics GmbH',
                'contact_person'           => 'Hans Mueller',
                'phone'                    => '+49-30-12345678',
                'email'                    => 'hans@globalfabrics.de',
                'address'                  => 'Berlin, Germany',
                'currency_id'              => $eur->id,
                'default_currency'         => 'EUR',
                'opening_balance'          => 180000.00,
                'opening_balance_currency' => 'PKR',
            ],
            [
                'customer_name'            => 'Premier Garments UK',
                'contact_person'           => 'James Wilson',
                'phone'                    => '+44-20-98765432',
                'email'                    => 'james@premiergarments.co.uk',
                'address'                  => 'Manchester, UK',
                'currency_id'              => $gbp->id,
                'default_currency'         => 'GBP',
                'opening_balance'          => 320000.00,
                'opening_balance_currency' => 'PKR',
            ],
            [
                'customer_name'            => 'National Textiles Co.',
                'contact_person'           => 'Imran Sheikh',
                'phone'                    => '+92-42-3567890',
                'email'                    => 'imran@nationaltextiles.pk',
                'address'                  => 'Gulberg, Lahore',
                'currency_id'              => $usd->id,
                'default_currency'         => 'USD',
                'opening_balance'          => 95000.00,
                'opening_balance_currency' => 'PKR',
            ],
        ])->map(fn($data) => Customer::create(array_merge($data, ['status' => true])));

        // ─── Vendors ───────────────────────────────────────────────────────
        $vendors = collect([
            [
                'vendor_name'     => 'ChemLab Supplies Pvt Ltd',
                'company_name'    => 'ChemLab Supplies',
                'phone'           => '+92-21-5678901',
                'email'           => 'info@chemlab.pk',
                'address'         => 'Korangi Industrial Area, Karachi',
                'payment_terms'   => 'Net 30',
                'opening_balance' => 0,
            ],
            [
                'vendor_name'     => 'TechInstruments Inc.',
                'company_name'    => 'TechInstruments',
                'phone'           => '+92-42-7890123',
                'email'           => 'sales@techinstruments.com',
                'address'         => 'M.M. Alam Road, Lahore',
                'payment_terms'   => 'Net 15',
                'opening_balance' => 50000,
            ],
            [
                'vendor_name'     => 'Office Mart Pakistan',
                'company_name'    => 'Office Mart',
                'phone'           => '+92-51-2345678',
                'email'           => 'orders@officemart.pk',
                'address'         => 'Blue Area, Islamabad',
                'payment_terms'   => 'Immediate',
                'opening_balance' => 0,
            ],
        ])->map(fn($data) => Vendor::create(array_merge($data, ['status' => true])));

        // ─── Employees ─────────────────────────────────────────────────────
        collect([
            ['employee_name' => 'Muhammad Ali',   'department' => 'Laboratory', 'designation' => 'Lab Manager',       'phone' => '+92-300-1234567', 'joining_date' => '2022-01-15', 'basic_salary' => 75000],
            ['employee_name' => 'Fatima Khan',    'department' => 'Laboratory', 'designation' => 'Lab Technician',    'phone' => '+92-301-2345678', 'joining_date' => '2022-03-01', 'basic_salary' => 45000],
            ['employee_name' => 'Usman Ahmed',    'department' => 'Laboratory', 'designation' => 'Junior Technician', 'phone' => '+92-302-3456789', 'joining_date' => '2023-06-01', 'basic_salary' => 35000],
            ['employee_name' => 'Sara Malik',     'department' => 'Finance',    'designation' => 'Accountant',        'phone' => '+92-303-4567890', 'joining_date' => '2022-07-15', 'basic_salary' => 60000],
            ['employee_name' => 'Bilal Hussain',  'department' => 'Admin',      'designation' => 'Office Manager',    'phone' => '+92-304-5678901', 'joining_date' => '2021-11-01', 'basic_salary' => 55000],
        ])->each(fn($data) => Employee::create(array_merge($data, ['status' => true])));

        // ─── Users for Lab Manager & Accountant roles ──────────────────────
        $labUser = User::firstOrCreate(['email' => 'labmanager@erp.test'], [
            'name'     => 'Muhammad Ali',
            'password' => Hash::make('password'),
            'status'   => true,
        ]);
        $labUser->syncRoles(['Lab Manager']);

        $accountantUser = User::firstOrCreate(['email' => 'accountant@erp.test'], [
            'name'     => 'Sara Malik',
            'password' => Hash::make('password'),
            'status'   => true,
        ]);
        $accountantUser->syncRoles(['Accountant']);

        // ─── Product Categories & Testing Parameters ────────────────────────
        $fabricCat = ProductCategory::create(['category_name' => 'Woven Fabrics',        'status' => true]);
        $knitCat   = ProductCategory::create(['category_name' => 'Knit Fabrics',         'status' => true]);
        $dyeCat    = ProductCategory::create(['category_name' => 'Dyed Yarns',           'status' => true]);
        $trimCat   = ProductCategory::create(['category_name' => 'Trims & Accessories',  'status' => true]);

        TestingParameter::create(['category_id' => $trimCat->id, 'parameter_name' => 'Button Pull Strength',  'description' => 'Force required to detach button (N)', 'status' => true]);
        TestingParameter::create(['category_id' => $trimCat->id, 'parameter_name' => 'Zipper Cycle Endurance','description' => 'Number of open/close cycles before failure',  'status' => true]);

        $fabricParams = [
            ['parameter_name' => 'Tensile Strength',    'description' => 'Warp and weft tensile strength test (N/cm²)'],
            ['parameter_name' => 'Tear Resistance',     'description' => 'Elmendorf tear test'],
            ['parameter_name' => 'Color Fastness',      'description' => 'Wash fastness to ISO 105-C06'],
            ['parameter_name' => 'GSM Weight',          'description' => 'Grams per square meter measurement'],
            ['parameter_name' => 'Thread Count',        'description' => 'Threads per inch (warp + weft)'],
        ];
        foreach ($fabricParams as $p) {
            TestingParameter::create(array_merge($p, ['category_id' => $fabricCat->id, 'status' => true]));
        }

        $knitParams = [
            ['parameter_name' => 'Pilling Resistance',  'description' => 'Martindale pilling test'],
            ['parameter_name' => 'Stretch & Recovery',  'description' => 'Elongation and recovery percentage'],
            ['parameter_name' => 'Shrinkage',           'description' => 'Wash and dry shrinkage test'],
        ];
        foreach ($knitParams as $p) {
            TestingParameter::create(array_merge($p, ['category_id' => $knitCat->id, 'status' => true]));
        }

        $dyeParams = [
            ['parameter_name' => 'Color Strength (K/S)',    'description' => 'Spectrophotometer color strength measurement'],
            ['parameter_name' => 'Fastness to Perspiration','description' => 'Acid and alkaline perspiration fastness'],
            ['parameter_name' => 'Rubbing Fastness',        'description' => 'Dry and wet crock test'],
        ];
        foreach ($dyeParams as $p) {
            TestingParameter::create(array_merge($p, ['category_id' => $dyeCat->id, 'status' => true]));
        }

        // ─── Brands ────────────────────────────────────────────────────────
        $brands = [];
        $brands['allied_denim']   = Brand::create(['customer_id' => $customers[0]->id, 'brand_name' => 'Allied Denim',   'remarks' => 'Premium denim range', 'status' => true]);
        $brands['allied_canvas']  = Brand::create(['customer_id' => $customers[0]->id, 'brand_name' => 'Allied Canvas',  'remarks' => 'Canvas fabrics',      'status' => true]);
        $brands['global_premium'] = Brand::create(['customer_id' => $customers[1]->id, 'brand_name' => 'GlobalPremium',  'remarks' => 'EU market range',     'status' => true]);
        $brands['premier_eco']    = Brand::create(['customer_id' => $customers[2]->id, 'brand_name' => 'PremierEco',     'remarks' => 'Organic certified',   'status' => true]);
        $brands['national_basic'] = Brand::create(['customer_id' => $customers[3]->id, 'brand_name' => 'NationalBasic',  'remarks' => 'Standard grade',      'status' => true]);

        // ─── Samples ───────────────────────────────────────────────────────
        $sampleData = [
            [
                'sample_code'         => 'SMP-2024-001',
                'category_id'         => $fabricCat->id,
                'customer_id'         => $customers[0]->id,
                'brand_id'            => $brands['allied_denim']->id,
                'product_name'        => 'Indigo Denim 12oz',
                'shipment_reference'  => 'SHP-ATL-2024-011',
                'receive_date'        => now()->subDays(20)->toDateString(),
                'quantity'            => 150,
                'priority_level'      => 'High',
                'alert_days'          => 14,
                'status'              => 'In Testing',
                'remarks'             => 'Urgent — client deadline next week',
            ],
            [
                'sample_code'         => 'SMP-2024-002',
                'category_id'         => $knitCat->id,
                'customer_id'         => $customers[1]->id,
                'brand_id'            => $brands['global_premium']->id,
                'product_name'        => 'Cotton Jersey 180GSM',
                'shipment_reference'  => 'SHP-GFG-2024-007',
                'receive_date'        => now()->subDays(10)->toDateString(),
                'quantity'            => 80,
                'priority_level'      => 'Medium',
                'alert_days'          => 21,
                'status'              => 'Received',
                'remarks'             => '',
            ],
            [
                'sample_code'         => 'SMP-2024-003',
                'category_id'         => $dyeCat->id,
                'customer_id'         => $customers[2]->id,
                'brand_id'            => $brands['premier_eco']->id,
                'product_name'        => 'Reactive Dyed Yarn Lot A',
                'shipment_reference'  => 'SHP-PGU-2024-003',
                'receive_date'        => now()->subDays(35)->toDateString(),
                'quantity'            => 500,
                'priority_level'      => 'Medium',
                'alert_days'          => 30,
                'status'              => 'Completed',
                'remarks'             => 'All tests passed. Report dispatched.',
            ],
            [
                'sample_code'         => 'SMP-2024-004',
                'category_id'         => $fabricCat->id,
                'customer_id'         => $customers[3]->id,
                'brand_id'            => $brands['national_basic']->id,
                'product_name'        => 'Cotton Twill 240GSM',
                'shipment_reference'  => 'SHP-NTC-2024-021',
                'receive_date'        => now()->subDays(5)->toDateString(),
                'quantity'            => 200,
                'priority_level'      => 'Low',
                'alert_days'          => 21,
                'status'              => 'Received',
                'remarks'             => '',
            ],
        ];
        collect($sampleData)->each(fn($d) => Sample::create($d));

        // ─── Vendor Bills ──────────────────────────────────────────────────
        $bill1 = VendorBill::create([
            'vendor_id'    => $vendors[0]->id,
            'bill_number'  => 'VB-2024-001',
            'bill_date'    => now()->subDays(25)->toDateString(),
            'due_date'     => now()->subDays(5)->toDateString(),
            'total_amount' => 0,
            'status'       => 'Unpaid',
            'remarks'      => 'Monthly chemical reagents supply',
        ]);
        VendorBillItem::create(['vendor_bill_id' => $bill1->id, 'description' => 'Sodium Hydroxide 25kg',       'quantity' => 5,  'unit_price' => 3500,  'line_total' => 17500]);
        VendorBillItem::create(['vendor_bill_id' => $bill1->id, 'description' => 'Acetic Acid 30L',             'quantity' => 3,  'unit_price' => 4800,  'line_total' => 14400]);
        VendorBillItem::create(['vendor_bill_id' => $bill1->id, 'description' => 'Lab Grade Distilled Water',   'quantity' => 20, 'unit_price' => 450,   'line_total' => 9000]);
        $bill1->update(['total_amount' => $bill1->items()->sum('line_total')]);

        $bill2 = VendorBill::create([
            'vendor_id'    => $vendors[1]->id,
            'bill_number'  => 'VB-2024-002',
            'bill_date'    => now()->subDays(40)->toDateString(),
            'due_date'     => now()->subDays(10)->toDateString(),
            'total_amount' => 0,
            'status'       => 'Overdue',
            'remarks'      => 'Lab equipment calibration tools',
        ]);
        VendorBillItem::create(['vendor_bill_id' => $bill2->id, 'description' => 'Digital Caliper Set',           'quantity' => 2,  'unit_price' => 12500, 'line_total' => 25000]);
        VendorBillItem::create(['vendor_bill_id' => $bill2->id, 'description' => 'Tensile Testing Grips (pair)', 'quantity' => 1,  'unit_price' => 45000, 'line_total' => 45000]);
        VendorBillItem::create(['vendor_bill_id' => $bill2->id, 'description' => 'Calibration Weights Set',      'quantity' => 1,  'unit_price' => 8500,  'line_total' => 8500]);
        $bill2->update(['total_amount' => $bill2->items()->sum('line_total')]);

        $bill3 = VendorBill::create([
            'vendor_id'    => $vendors[2]->id,
            'bill_number'  => 'VB-2024-003',
            'bill_date'    => now()->subDays(15)->toDateString(),
            'due_date'     => now()->addDays(15)->toDateString(),
            'total_amount' => 0,
            'status'       => 'Unpaid',
            'remarks'      => 'Office supplies Q2',
        ]);
        VendorBillItem::create(['vendor_bill_id' => $bill3->id, 'description' => 'A4 Paper Reams (80GSM)',        'quantity' => 10, 'unit_price' => 950,   'line_total' => 9500]);
        VendorBillItem::create(['vendor_bill_id' => $bill3->id, 'description' => 'Printer Toner Cartridges',     'quantity' => 2,  'unit_price' => 3800,  'line_total' => 7600]);
        VendorBillItem::create(['vendor_bill_id' => $bill3->id, 'description' => 'Stationery Pack',              'quantity' => 5,  'unit_price' => 1200,  'line_total' => 6000]);
        $bill3->update(['total_amount' => $bill3->items()->sum('line_total')]);

        // ─── Expenses ──────────────────────────────────────────────────────
        $adminUser = User::where('email', 'admin@erp.test')->first();

        $expHeadsKeyed = ExpenseHead::whereIn('expense_name', [
            'Office Rent', 'Electricity', 'Internet & Phone', 'Petrol & Transport', 'Office Supplies',
        ])->get()->keyBy('expense_name');

        $expenseRows = [
            ['expense_head_id' => $expHeadsKeyed['Office Rent']->id,         'account_id' => $hblAccount->id,  'amount' => 85000, 'expense_date' => now()->subDays(30)->toDateString(), 'description' => 'Monthly office & lab rent — May 2024'],
            ['expense_head_id' => $expHeadsKeyed['Electricity']->id,          'account_id' => $cashAccount->id, 'amount' => 22500, 'expense_date' => now()->subDays(28)->toDateString(), 'description' => 'KESC electricity bill — April 2024'],
            ['expense_head_id' => $expHeadsKeyed['Internet & Phone']->id,     'account_id' => $cashAccount->id, 'amount' => 8500,  'expense_date' => now()->subDays(25)->toDateString(), 'description' => 'Fiber broadband + phone lines monthly'],
            ['expense_head_id' => $expHeadsKeyed['Petrol & Transport']->id,   'account_id' => $cashAccount->id, 'amount' => 15000, 'expense_date' => now()->subDays(15)->toDateString(), 'description' => 'Vehicle fuel and sample pickup/delivery'],
            ['expense_head_id' => $expHeadsKeyed['Office Supplies']->id,      'account_id' => $cashAccount->id, 'amount' => 6200,  'expense_date' => now()->subDays(10)->toDateString(), 'description' => 'Lab consumables and stationery'],
            ['expense_head_id' => $expHeadsKeyed['Office Rent']->id,          'account_id' => $hblAccount->id,  'amount' => 85000, 'expense_date' => now()->subDays(1)->toDateString(),  'description' => 'Monthly office & lab rent — June 2024'],
        ];
        foreach ($expenseRows as $e) {
            $txn = Transaction::create([
                'transaction_date'  => $e['expense_date'],
                'transaction_type'  => 'Expense',
                'reference_type'    => 'expense',
                'debit_account_id'  => $e['account_id'],
                'credit_account_id' => $e['account_id'],
                'amount'            => $e['amount'],
                'remarks'           => $e['description'] ?? null,
                'created_by'        => $adminUser->id,
            ]);
            $e['transaction_id'] = $txn->id;
            $expense = Expense::create($e);
            $txn->update(['reference_id' => $expense->id]);
        }

        // ─── Customer Payments ─────────────────────────────────────────────
        $paymentRows = [
            [
                'customer_id'         => $customers[0]->id,
                'account_id'          => $hblAccount->id,
                'payment_date'        => now()->subDays(18)->toDateString(),
                'invoice_reference'   => 'INV-ATL-2024-055',
                'foreign_currency'    => 'USD',
                'invoiced_amount_fc'  => 12500.00,
                'deduction_fc'        => 125.00,
                'received_fc'         => 12375.00,
                'exchange_rate'       => 278.50,
                'expected_pkr'        => 3481250.00,
                'actual_pkr_received' => 3495600.00,
                'pkr_gain_loss'       => 14350.00,
                'fc_gain_loss'        => -125.00,
                'remarks'             => 'Payment for Q1 testing services',
            ],
            [
                'customer_id'         => $customers[1]->id,
                'account_id'          => $hblAccount->id,
                'payment_date'        => now()->subDays(8)->toDateString(),
                'invoice_reference'   => 'INV-GFG-2024-012',
                'foreign_currency'    => 'EUR',
                'invoiced_amount_fc'  => 8000.00,
                'deduction_fc'        => 0.00,
                'received_fc'         => 8000.00,
                'exchange_rate'       => 302.75,
                'expected_pkr'        => 2422000.00,
                'actual_pkr_received' => 2418400.00,
                'pkr_gain_loss'       => -3600.00,
                'fc_gain_loss'        => 0.00,
                'remarks'             => 'Full payment — no deduction',
            ],
            [
                'customer_id'         => $customers[2]->id,
                'account_id'          => $ublAccount->id,
                'payment_date'        => now()->subDays(3)->toDateString(),
                'invoice_reference'   => 'INV-PGU-2024-008',
                'foreign_currency'    => 'GBP',
                'invoiced_amount_fc'  => 5500.00,
                'deduction_fc'        => 55.00,
                'received_fc'         => 5445.00,
                'exchange_rate'       => 352.40,
                'expected_pkr'        => 1938200.00,
                'actual_pkr_received' => 1940580.00,
                'pkr_gain_loss'       => 2380.00,
                'fc_gain_loss'        => -55.00,
                'remarks'             => '1% bank charge deducted',
            ],
        ];
        foreach ($paymentRows as $p) {
            $txn = Transaction::create([
                'transaction_date'  => $p['payment_date'],
                'transaction_type'  => 'CustomerReceipt',
                'reference_type'    => 'customer_payment',
                'debit_account_id'  => $p['account_id'],
                'credit_account_id' => $p['account_id'],
                'amount'            => $p['actual_pkr_received'],
                'remarks'           => $p['remarks'] ?? null,
                'created_by'        => $adminUser->id,
            ]);
            $p['transaction_id'] = $txn->id;
            $payment = CustomerPayment::create($p);
            $txn->update(['reference_id' => $payment->id]);
        }

        $this->command->info('✅  TestDataSeeder complete:');
        $this->command->info('   Customers:  ' . Customer::count());
        $this->command->info('   Vendors:    ' . Vendor::count());
        $this->command->info('   Employees:  ' . Employee::count());
        $this->command->info('   Brands:     ' . Brand::count());
        $this->command->info('   Categories: ' . ProductCategory::count());
        $this->command->info('   Samples:    ' . Sample::count());
        $this->command->info('   Bills:      ' . VendorBill::count());
        $this->command->info('   Expenses:   ' . Expense::count());
        $this->command->info('   Payments:   ' . CustomerPayment::count());
        $this->command->info('');
        $this->command->info('   Test users:');
        $this->command->info('   admin@erp.test       / password  (Admin)');
        $this->command->info('   labmanager@erp.test  / password  (Lab Manager)');
        $this->command->info('   accountant@erp.test  / password  (Accountant)');
    }
}
