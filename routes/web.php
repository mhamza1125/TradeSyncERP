<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\CustomerPaymentController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\SalaryRunController;
use App\Http\Controllers\Finance\VendorBillController;
use App\Http\Controllers\Masters\AccountController;
use App\Http\Controllers\Masters\BankController;
use App\Http\Controllers\Masters\BrandController;
use App\Http\Controllers\Masters\CurrencyController;
use App\Http\Controllers\Masters\CustomerController;
use App\Http\Controllers\Masters\EmployeeController;
use App\Http\Controllers\Masters\ExpenseHeadController;
use App\Http\Controllers\Masters\ProductCategoryController;
use App\Http\Controllers\Masters\TestingParameterController;
use App\Http\Controllers\Masters\VendorController;
use App\Http\Controllers\Operations\InspectionController;
use App\Http\Controllers\Operations\SampleController;
use App\Http\Controllers\Operations\SampleMovementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\LedgerController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── Master Data ────────────────────────────────────────────────────────────
    Route::prefix('masters')->name('masters.')->group(function () {
        Route::resource('customers',    CustomerController::class);
        Route::resource('brands',       BrandController::class);
        Route::resource('categories',   ProductCategoryController::class);
        Route::resource('employees',    EmployeeController::class);
        Route::resource('vendors',      VendorController::class);
        Route::resource('parameters',   TestingParameterController::class);
        Route::resource('accounts',     AccountController::class);
        Route::resource('expense-heads', ExpenseHeadController::class)->parameters(['expense-heads' => 'expense_head']);
        Route::resource('currencies',   CurrencyController::class);
        Route::resource('banks',        BankController::class);
    });

    // ─── Sample Operations ───────────────────────────────────────────────────────
    Route::resource('samples', SampleController::class);

    Route::prefix('samples/{sample}')->name('samples.')->group(function () {
        Route::resource('movements',  SampleMovementController::class)->shallow();
        Route::resource('inspections', InspectionController::class)->shallow();
    });

    // ─── Finance ─────────────────────────────────────────────────────────────────
    Route::resource('vendor-bills', VendorBillController::class)->parameters(['vendor-bills' => 'vendorBill']);
    Route::post('vendor-bills/{vendorBill}/pay', [VendorBillController::class, 'pay'])->name('vendor-bills.pay');

    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::resource('salary', SalaryRunController::class)->parameters(['salary' => 'salaryRun'])
        ->only(['index', 'create', 'store', 'show']);
    Route::put('salary/{salaryRun}/lines',  [SalaryRunController::class, 'updateLines'])->name('salary.lines.update');
    Route::post('salary/{salaryRun}/pay',   [SalaryRunController::class, 'pay'])->name('salary.pay');

    Route::resource('customer-payments', CustomerPaymentController::class)->parameters(['customer-payments' => 'customerPayment'])
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    // ─── Ledgers & Reports ───────────────────────────────────────────────────────
    Route::prefix('ledger')->name('ledger.')->group(function () {
        Route::get('cash',               [LedgerController::class, 'cash'])->name('cash');
        Route::get('bank',               [LedgerController::class, 'bank'])->name('bank');
        Route::get('customers/{customer}', [LedgerController::class, 'customer'])->name('customer');
        Route::get('vendors/{vendor}',   [LedgerController::class, 'vendor'])->name('vendor');
    });
});

require __DIR__ . '/auth.php';
