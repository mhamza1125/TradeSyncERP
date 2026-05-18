<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\CustomerInvoiceController;
use App\Http\Controllers\Finance\CustomerPaymentController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\SalaryRunController;
use App\Http\Controllers\Masters\AccountController;
use App\Http\Controllers\Masters\BankController;
use App\Http\Controllers\Masters\CurrencyController;
use App\Http\Controllers\Masters\CustomerController;
use App\Http\Controllers\Masters\EmployeeController;
use App\Http\Controllers\Masters\ExpenseHeadController;
use App\Http\Controllers\Masters\InspectionTypeController;
use App\Http\Controllers\Masters\ProductCategoryController;
use App\Http\Controllers\Masters\SupplierController;
use App\Http\Controllers\Masters\TestingParameterController;
use App\Http\Controllers\Operations\CustomerOrderController;
use App\Http\Controllers\Operations\InspectionController;
use App\Http\Controllers\Operations\InspectionRunController;
use App\Http\Controllers\Operations\SampleController;
use App\Http\Controllers\Operations\SampleMovementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
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
        Route::resource('customers',         CustomerController::class);
        Route::resource('categories',        ProductCategoryController::class);
        Route::resource('employees',         EmployeeController::class);
        Route::resource('suppliers',         SupplierController::class);
        Route::resource('inspection-types',  InspectionTypeController::class)->parameters(['inspection-types' => 'inspectionType']);
        Route::get('parameters/bulk-create', [TestingParameterController::class, 'bulkCreate'])->name('parameters.bulk-create');
        Route::post('parameters/bulk-store', [TestingParameterController::class, 'bulkStore'])->name('parameters.bulk-store');
        Route::resource('parameters',        TestingParameterController::class);
        Route::resource('accounts',          AccountController::class);
        Route::resource('expense-heads',     ExpenseHeadController::class)->parameters(['expense-heads' => 'expense_head']);
        Route::resource('currencies',        CurrencyController::class);
        Route::resource('banks',             BankController::class);
    });

    // ─── Sample Operations ───────────────────────────────────────────────────────
    Route::resource('customer-orders', CustomerOrderController::class)->parameters(['customer-orders' => 'customerOrder']);

    Route::resource('samples', SampleController::class);

    Route::resource('samples.movements', SampleMovementController::class)->shallow();

    // Inspections (top-level) + run sub-pages
    Route::resource('inspections', InspectionController::class);
    Route::prefix('inspections/{inspection}/runs')->name('inspections.runs.')->group(function () {
        Route::get('create',          [InspectionRunController::class, 'create'])->name('create');
        Route::post('',               [InspectionRunController::class, 'store'])->name('store');
        Route::get('{run}/edit',      [InspectionRunController::class, 'edit'])->name('edit');
        Route::put('{run}',           [InspectionRunController::class, 'update'])->name('update');
        Route::delete('{run}',        [InspectionRunController::class, 'destroy'])->name('destroy');
    });

    // ─── Finance ─────────────────────────────────────────────────────────────────
    Route::resource('customer-invoices', CustomerInvoiceController::class)->parameters(['customer-invoices' => 'customerInvoice']);

    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::resource('salary', SalaryRunController::class)->parameters(['salary' => 'salaryRun'])
        ->only(['index', 'create', 'store', 'show']);
    Route::put('salary/{salaryRun}/lines',  [SalaryRunController::class, 'updateLines'])->name('salary.lines.update');
    Route::post('salary/{salaryRun}/pay',   [SalaryRunController::class, 'pay'])->name('salary.pay');

    Route::resource('customer-payments', CustomerPaymentController::class)->parameters(['customer-payments' => 'customerPayment'])
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    // ─── Attachments (polymorphic) ────────────────────────────────────────────────
    Route::post('attachments/{type}/{id}', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // ─── Admin ───────────────────────────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class);
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    // ─── Ledgers & Reports ───────────────────────────────────────────────────────
    Route::prefix('ledger')->name('ledger.')->group(function () {
        Route::get('cash',                 [LedgerController::class, 'cash'])->name('cash');
        Route::get('bank',                 [LedgerController::class, 'bank'])->name('bank');
        Route::get('customers/{customer}', [LedgerController::class, 'customer'])->name('customer');
    });
});

require __DIR__ . '/auth.php';
