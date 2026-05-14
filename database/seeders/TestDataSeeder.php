<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank;
use App\Models\Brand;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceItem;
use App\Models\CustomerPayment;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\ProductCategory;
use App\Models\Sample;
use App\Models\TestingParameter;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing test data in correct dependency order
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        CustomerInvoiceItem::truncate();
        CustomerInvoice::withTrashed()->forceDelete();
        CustomerOrderItem::truncate();
        CustomerOrder::withTrashed()->forceDelete();
        CustomerPayment::truncate();
        Expense::truncate();
        Transaction::truncate();
        Sample::withTrashed()->forceDelete();
        Brand::truncate();
        TestingParameter::truncate();
        ProductCategory::truncate();
        Employee::withTrashed()->forceDelete();
        Customer::withTrashed()->forceDelete();
        Supplier::withTrashed()->forceDelete();
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
            'account_number'  => null,
            'bank_id'         => null,
            'currency'        => 'PKR',
            'opening_balance' => 150000.00,
            'status'          => true,
        ]);
        $hblAccount = Account::firstOrCreate(['account_name' => 'HBL Current Account'], [
            'account_type'    => 'Bank',
            'bank_id'         => $hbl->id,
            'account_number'  => '1234-567890-01',
            'currency'        => 'PKR',
            'opening_balance' => 2500000.00,
            'status'          => true,
        ]);
        $ublAccount = Account::firstOrCreate(['account_name' => 'UBL Savings Account'], [
            'account_type'    => 'Bank',
            'bank_id'         => $ubl->id,
            'account_number'  => '9876-543210-01',
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
                'opening_balance'          => 250000.00,
            ],
            [
                'customer_name'            => 'Global Fabrics GmbH',
                'contact_person'           => 'Hans Mueller',
                'phone'                    => '+49-30-12345678',
                'email'                    => 'hans@globalfabrics.de',
                'address'                  => 'Berlin, Germany',
                'currency_id'              => $eur->id,
                'opening_balance'          => 180000.00,
            ],
            [
                'customer_name'            => 'Premier Garments UK',
                'contact_person'           => 'James Wilson',
                'phone'                    => '+44-20-98765432',
                'email'                    => 'james@premiergarments.co.uk',
                'address'                  => 'Manchester, UK',
                'currency_id'              => $gbp->id,
                'opening_balance'          => 320000.00,
            ],
            [
                'customer_name'            => 'National Textiles Co.',
                'contact_person'           => 'Imran Sheikh',
                'phone'                    => '+92-42-3567890',
                'email'                    => 'imran@nationaltextiles.pk',
                'address'                  => 'Gulberg, Lahore',
                'currency_id'              => $usd->id,
                'opening_balance'          => 95000.00,
            ],
        ])->map(fn($data) => Customer::create(array_merge($data, ['status' => true])));

        // ─── Suppliers ─────────────────────────────────────────────────────
        collect([
            'ATROX', 'ARIAN', 'AMSA', 'BIKER TRADES', 'COBIJA', 'DMD', 'ELEONE',
            'ESTABLISH LEATHER', 'EXPERT ENTERPRISES', 'EUREKA', 'FAAZ', 'FIRCOS',
            'HANSA', 'HAMMAD INDUSTRIES', 'ITTAHAD', 'KSN', 'LEDERBERG',
            'MIR YOUSAF', 'MECCA', 'MOTOWAY', 'PILOT', 'PRIME PROTECTION',
            'ROBIQA', 'ROADSTAR', 'SIGMA', 'SOLEHRE BROTHERS', 'TRADE WELL',
            'VIP', 'VIVIFY', 'WHITELAND', 'ZULFIQAR BROTHERS', 'ZETA-X', 'ZMF',
        ])->each(fn($name) => Supplier::create(['name' => $name, 'status' => true]));

        // ─── Employees ─────────────────────────────────────────────────────
        collect([
            ['employee_name' => 'RIZWAN ALI',      'department' => 'Quality Control', 'designation' => 'Senior QC Auditor',                'phone' => '+92-300-1000001', 'joining_date' => '2022-01-01', 'basic_salary' => 80000],
            ['employee_name' => 'RIZWAN AHMED',    'department' => 'Quality Control', 'designation' => 'Senior QC Auditor',                'phone' => '+92-300-1000002', 'joining_date' => '2022-02-01', 'basic_salary' => 80000],
            ['employee_name' => 'HARIS',           'department' => 'Quality Control', 'designation' => 'QC Auditor',                       'phone' => '+92-300-1000003', 'joining_date' => '2022-06-01', 'basic_salary' => 60000],
            ['employee_name' => 'AHMED',           'department' => 'Quality Control', 'designation' => 'QC Associate',                     'phone' => '+92-300-1000004', 'joining_date' => '2023-01-01', 'basic_salary' => 45000],
            ['employee_name' => 'RAFIQ',           'department' => 'Quality Control', 'designation' => 'QC Associate',                     'phone' => '+92-300-1000005', 'joining_date' => '2023-03-01', 'basic_salary' => 45000],
            ['employee_name' => 'UMER ADIL',       'department' => 'Operations',      'designation' => 'Operations Manager',               'phone' => '+92-300-1000006', 'joining_date' => '2021-09-01', 'basic_salary' => 100000],
            ['employee_name' => 'QADEER',          'department' => 'Reporting',       'designation' => 'Reporting Manager',                'phone' => '+92-300-1000007', 'joining_date' => '2022-04-01', 'basic_salary' => 75000],
            ['employee_name' => 'KASHIF',          'department' => 'Reporting',       'designation' => 'Reporting Manager',                'phone' => '+92-300-1000008', 'joining_date' => '2022-05-01', 'basic_salary' => 75000],
            ['employee_name' => 'QURATULAIN AZHAR','department' => 'Administration',  'designation' => 'Administration Department',        'phone' => '+92-300-1000009', 'joining_date' => '2022-08-01', 'basic_salary' => 55000],
            ['employee_name' => 'VARDAH SHAFIQ',   'department' => 'Marketing',       'designation' => 'Communication & Marketing Manager','phone' => '+92-300-1000010', 'joining_date' => '2023-01-01', 'basic_salary' => 70000],
            ['employee_name' => 'JOHAM UROOSH',    'department' => 'R&D',             'designation' => 'R & D Manager',                   'phone' => '+92-300-1000011', 'joining_date' => '2022-10-01', 'basic_salary' => 85000],
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

        // ─── Sample Categories & Testing Parameters ────────────────────────
        $garmentsCat    = ProductCategory::create(['category_name' => 'GARMENTS (JACKETS / PANTS)', 'status' => true]);
        $bootsCat       = ProductCategory::create(['category_name' => 'BOOTS',                      'status' => true]);
        $glovesCat      = ProductCategory::create(['category_name' => 'GLOVES',                     'status' => true]);
        $accessoriesCat = ProductCategory::create(['category_name' => 'ACCESSORIES',                'status' => true]);

        $garmentsParams = [
            ['parameter_name' => 'Seam Strength',          'description' => 'Force required to break seams (N/5cm)'],
            ['parameter_name' => 'Abrasion Resistance',    'description' => 'Martindale abrasion cycles before failure'],
            ['parameter_name' => 'Color Fastness to Wash', 'description' => 'Wash fastness rating ISO 105-C06'],
            ['parameter_name' => 'CE Protection Level',    'description' => 'CE EN 17092 protection class (A/AA/AAA)'],
            ['parameter_name' => 'Waterproofing Rating',   'description' => 'Hydrostatic pressure resistance (mm H₂O)'],
        ];
        foreach ($garmentsParams as $p) {
            TestingParameter::create(array_merge($p, ['category_id' => $garmentsCat->id, 'status' => true]));
        }

        $bootsParams = [
            ['parameter_name' => 'Sole Adhesion Strength', 'description' => 'Peel strength of sole bond (N/cm)'],
            ['parameter_name' => 'Impact Resistance',      'description' => 'Toecap impact energy absorption (J)'],
            ['parameter_name' => 'Ankle Support Rating',   'description' => 'Lateral rigidity and ankle protection rating'],
            ['parameter_name' => 'Material Durability',    'description' => 'Upper material flex and abrasion endurance'],
        ];
        foreach ($bootsParams as $p) {
            TestingParameter::create(array_merge($p, ['category_id' => $bootsCat->id, 'status' => true]));
        }

        $glovesParams = [
            ['parameter_name' => 'Palm Abrasion Resistance', 'description' => 'Abrasion cycles on palm area before failure'],
            ['parameter_name' => 'Impact Absorption',        'description' => 'Transmitted force on knuckle protector (kN)'],
            ['parameter_name' => 'Stitching Integrity',      'description' => 'Seam burst strength (N)'],
            ['parameter_name' => 'CE Level Rating',          'description' => 'CE EN 13594 Level 1 or 2 classification'],
        ];
        foreach ($glovesParams as $p) {
            TestingParameter::create(array_merge($p, ['category_id' => $glovesCat->id, 'status' => true]));
        }

        $accessoriesParams = [
            ['parameter_name' => 'Buckle Tensile Strength', 'description' => 'Load at which buckle fails (kN)'],
            ['parameter_name' => 'Surface Finish Quality',  'description' => 'Visual and tactile finish inspection'],
            ['parameter_name' => 'Hardware Corrosion Test', 'description' => 'Salt spray hours before visible corrosion'],
        ];
        foreach ($accessoriesParams as $p) {
            TestingParameter::create(array_merge($p, ['category_id' => $accessoriesCat->id, 'status' => true]));
        }

        // ─── Brands ────────────────────────────────────────────────────────
        $brandsByCustomer = [
            $customers[0]->id => ['IXON', 'SPADA', 'MODEKA', 'HEVIK'],
            $customers[1]->id => ['GIVI', 'BARRUFALDI', 'BERGMOTO', 'KOV-PARTS'],
            $customers[2]->id => ['SIFAM', 'SHIMA', 'JAG', 'ATWYLD'],
            $customers[3]->id => ['HOLYFREEDOM', 'FORMA BOOT', 'ASKOLL'],
        ];

        $brands = [];
        foreach ($brandsByCustomer as $customerId => $names) {
            foreach ($names as $name) {
                $key = strtolower(str_replace([' ', '-'], '_', $name));
                $brands[$key] = Brand::create(['customer_id' => $customerId, 'brand_name' => $name, 'status' => true]);
            }
        }

        // ─── Samples ───────────────────────────────────────────────────────
        $sampleData = [
            [
                'sample_code'      => 'SMP-2024-001',
                'category_id'      => $garmentsCat->id,
                'customer_id'      => $customers[0]->id,
                'brand_id'         => $brands['ixon']->id,
                'product_name'     => 'Motorcycle Jacket - Touring Pro',
                'sample_reference' => 'SHP-ATL-2024-011',
                'receive_date'     => now()->subDays(20)->toDateString(),
                'quantity'         => 50,
                'priority_level'   => 'High',
                'alert_days'       => 14,
                'status'           => 'In Testing',
                'remarks'          => 'Urgent — client deadline next week',
            ],
            [
                'sample_code'      => 'SMP-2024-002',
                'category_id'      => $bootsCat->id,
                'customer_id'      => $customers[1]->id,
                'brand_id'         => $brands['givi']->id,
                'product_name'     => 'Touring Boots - Urban Evo',
                'sample_reference' => 'SHP-GFG-2024-007',
                'receive_date'     => now()->subDays(10)->toDateString(),
                'quantity'         => 30,
                'priority_level'   => 'Medium',
                'alert_days'       => 21,
                'status'           => 'Received',
                'remarks'          => '',
            ],
            [
                'sample_code'      => 'SMP-2024-003',
                'category_id'      => $glovesCat->id,
                'customer_id'      => $customers[2]->id,
                'brand_id'         => $brands['shima']->id,
                'product_name'     => 'Racing Gloves - Level 2',
                'sample_reference' => 'SHP-PGU-2024-003',
                'receive_date'     => now()->subDays(35)->toDateString(),
                'quantity'         => 100,
                'priority_level'   => 'Medium',
                'alert_days'       => 30,
                'status'           => 'Completed',
                'remarks'          => 'All tests passed. Report dispatched.',
            ],
            [
                'sample_code'      => 'SMP-2024-004',
                'category_id'      => $accessoriesCat->id,
                'customer_id'      => $customers[3]->id,
                'brand_id'         => $brands['holyfreedom']->id,
                'product_name'     => 'Helmet Buckle Set - Black',
                'sample_reference' => 'SHP-NTC-2024-021',
                'receive_date'     => now()->subDays(5)->toDateString(),
                'quantity'         => 200,
                'priority_level'   => 'Low',
                'alert_days'       => 21,
                'status'           => 'Received',
                'remarks'          => '',
            ],
        ];
        collect($sampleData)->each(fn($d) => Sample::create($d));

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

        // ─── Customer Orders ───────────────────────────────────────────────
        $order1 = CustomerOrder::create([
            'order_code'  => 'CSO-2024-00001',
            'customer_id' => $customers[0]->id,
            'brand_id'    => $brands['ixon']->id,
            'order_date'  => now()->subDays(12)->toDateString(),
            'required_by' => now()->addDays(10)->toDateString(),
            'status'      => 'Confirmed',
            'remarks'     => 'Customer needs samples for upcoming season',
        ]);
        CustomerOrderItem::create(['customer_order_id' => $order1->id, 'product_name' => 'Touring Jacket - Model X', 'quantity' => 50, 'unit' => 'pcs', 'description' => 'CE AA rated, black/grey']);
        CustomerOrderItem::create(['customer_order_id' => $order1->id, 'product_name' => 'Sport Pants - Model P',    'quantity' => 30, 'unit' => 'pcs', 'description' => 'CE A rated, with knee armor']);

        $order2 = CustomerOrder::create([
            'order_code'  => 'CSO-2024-00002',
            'customer_id' => $customers[1]->id,
            'brand_id'    => $brands['givi']->id,
            'order_date'  => now()->subDays(5)->toDateString(),
            'required_by' => null,
            'status'      => 'Draft',
            'remarks'     => 'Initial inquiry',
        ]);
        CustomerOrderItem::create(['customer_order_id' => $order2->id, 'product_name' => 'Adventure Boots - Trail Pro', 'quantity' => 40, 'unit' => 'pairs', 'description' => 'Waterproof, size range 40-46']);

        // ─── Customer Invoices ─────────────────────────────────────────────
        $inv1 = CustomerInvoice::create([
            'invoice_number'     => 'INV-2024-00001',
            'customer_id'        => $customers[0]->id,
            'invoice_date'       => now()->subDays(30)->toDateString(),
            'due_date'           => now()->subDays(15)->toDateString(),
            'subtotal'           => 250000,
            'tax_amount'         => 0,
            'discount_amount'    => 0,
            'total_amount'       => 250000,
            'amount_paid'        => 250000,
            'amount_due'         => 0,
            'status'             => 'Paid',
            'foreign_currency_id' => $usd->id,
            'exchange_rate'      => 278.50,
            'foreign_amount'     => 898.03,
            'remarks'            => 'Q1 testing services',
        ]);
        CustomerInvoiceItem::create(['customer_invoice_id' => $inv1->id, 'description' => 'Fabric Testing — Tensile & Color', 'quantity' => 5, 'unit_price' => 35000, 'line_total' => 175000]);
        CustomerInvoiceItem::create(['customer_invoice_id' => $inv1->id, 'description' => 'GSM & Thread Count Analysis',     'quantity' => 5, 'unit_price' => 15000, 'line_total' => 75000]);

        $inv2 = CustomerInvoice::create([
            'invoice_number'     => 'INV-2024-00002',
            'customer_id'        => $customers[1]->id,
            'invoice_date'       => now()->subDays(10)->toDateString(),
            'due_date'           => now()->addDays(20)->toDateString(),
            'subtotal'           => 180000,
            'tax_amount'         => 0,
            'discount_amount'    => 9000,
            'total_amount'       => 171000,
            'amount_paid'        => 0,
            'amount_due'         => 171000,
            'status'             => 'Sent',
            'foreign_currency_id' => $eur->id,
            'exchange_rate'      => 302.75,
            'foreign_amount'     => 564.72,
            'remarks'            => 'Knit fabric quality inspection',
        ]);
        CustomerInvoiceItem::create(['customer_invoice_id' => $inv2->id, 'description' => 'Pilling & Stretch Testing',    'quantity' => 4, 'unit_price' => 28000, 'line_total' => 112000]);
        CustomerInvoiceItem::create(['customer_invoice_id' => $inv2->id, 'description' => 'Shrinkage Analysis Report',    'quantity' => 2, 'unit_price' => 34000, 'line_total' => 68000]);

        $inv3 = CustomerInvoice::create([
            'invoice_number'     => 'INV-2024-00003',
            'customer_id'        => $customers[2]->id,
            'invoice_date'       => now()->subDays(45)->toDateString(),
            'due_date'           => now()->subDays(15)->toDateString(),
            'subtotal'           => 95000,
            'tax_amount'         => 0,
            'discount_amount'    => 0,
            'total_amount'       => 95000,
            'amount_paid'        => 0,
            'amount_due'         => 95000,
            'status'             => 'Overdue',
            'foreign_currency_id' => $gbp->id,
            'exchange_rate'      => 352.40,
            'foreign_amount'     => 269.70,
            'remarks'            => 'Dye quality reports — overdue',
        ]);
        CustomerInvoiceItem::create(['customer_invoice_id' => $inv3->id, 'description' => 'Color Strength & Fastness Testing', 'quantity' => 1, 'unit_price' => 95000, 'line_total' => 95000]);

        $this->command->info('✅  TestDataSeeder complete:');
        $this->command->info('   Customers:  ' . Customer::count());
        $this->command->info('   Suppliers:  ' . Supplier::count());
        $this->command->info('   Employees:  ' . Employee::count());
        $this->command->info('   Brands:     ' . Brand::count());
        $this->command->info('   Categories: ' . ProductCategory::count() . ' (sample categories)');
        $this->command->info('   Samples:    ' . Sample::count());
        $this->command->info('   Expenses:   ' . Expense::count());
        $this->command->info('   Payments:   ' . CustomerPayment::count());
        $this->command->info('   Invoices:   ' . CustomerInvoice::count());
        $this->command->info('   Orders:       ' . CustomerOrder::count());
        $this->command->info('');
        $this->command->info('   Test users:');
        $this->command->info('   admin@erp.test       / password  (Admin)');
        $this->command->info('   labmanager@erp.test  / password  (Lab Manager)');
        $this->command->info('   accountant@erp.test  / password  (Accountant)');
    }
}
