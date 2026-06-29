<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\AllowanceTypeController;
use App\Http\Controllers\Finance\CustomerInvoiceController;
use App\Http\Controllers\Finance\CustomerPaymentController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\SalaryRunController;
use App\Http\Controllers\Finance\TransferController;
use App\Http\Controllers\Masters\AccountController;
use App\Http\Controllers\Masters\BankController;
use App\Http\Controllers\Masters\ColorController;
use App\Http\Controllers\Masters\CurrencyController;
use App\Http\Controllers\Masters\CustomerController;
use App\Http\Controllers\Masters\EmployeeController;
use App\Http\Controllers\Masters\ExpenseHeadController;
use App\Http\Controllers\Masters\InspectionTypeController;
use App\Http\Controllers\Masters\ProductCategoryController;
use App\Http\Controllers\Masters\SizeController;
use App\Http\Controllers\Masters\SupplierController;
use App\Http\Controllers\Masters\DefectController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Operations\CustomerOrderController;
use App\Http\Controllers\Operations\InspectionController;
use App\Http\Controllers\Operations\InspectionExportController;
use App\Http\Controllers\Operations\InspectionRunController;
use App\Http\Controllers\Operations\InspectionSectionController;
use App\Http\Controllers\Operations\MovementController;
use App\Http\Controllers\Operations\SampleController;
use App\Http\Controllers\Operations\SampleMovementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\LedgerController;
use App\Http\Controllers\Tools\AqlCalculatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Recent Activities
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── Master Data ────────────────────────────────────────────────────────────
    Route::prefix('masters')->name('masters.')->group(function () {
        // PDF exports (must be before resource routes to avoid route-model binding conflicts)
        Route::get('customers/export-pdf',             [CustomerController::class, 'exportPdf'])->name('customers.export-pdf');
        Route::get('customers/{customer}/export-pdf',  [CustomerController::class, 'exportSinglePdf'])->name('customers.export-single-pdf');
        Route::get('suppliers/export-pdf',             [SupplierController::class, 'exportPdf'])->name('suppliers.export-pdf');
        Route::get('suppliers/{supplier}/export-pdf',  [SupplierController::class, 'exportSinglePdf'])->name('suppliers.export-single-pdf');
        Route::get('employees/export-pdf',             [EmployeeController::class, 'exportPdf'])->name('employees.export-pdf');
        Route::get('employees/{employee}/export-pdf',  [EmployeeController::class, 'exportSinglePdf'])->name('employees.export-single-pdf');
        Route::get('defects/export-pdf',               [DefectController::class, 'exportPdf'])->name('defects.export-pdf');
        Route::get('categories/export-pdf',            [ProductCategoryController::class, 'exportPdf'])->name('categories.export-pdf');
        Route::get('currencies/export-pdf',            [CurrencyController::class, 'exportPdf'])->name('currencies.export-pdf');
        Route::get('expense-heads/export-pdf',         [ExpenseHeadController::class, 'exportPdf'])->name('expense-heads.export-pdf');
        Route::get('accounts/export-pdf',              [AccountController::class, 'exportPdf'])->name('accounts.export-pdf');
        Route::get('banks/export-pdf',                 [BankController::class, 'exportPdf'])->name('banks.export-pdf');
        Route::get('colors/export-pdf',                [ColorController::class, 'exportPdf'])->name('colors.export-pdf');
        Route::get('sizes/export-pdf',                 [SizeController::class, 'exportPdf'])->name('sizes.export-pdf');

        Route::resource('customers',         CustomerController::class);
        Route::resource('categories',        ProductCategoryController::class);
        Route::resource('employees',         EmployeeController::class);
        Route::resource('suppliers',         SupplierController::class);
        Route::resource('inspection-types',  InspectionTypeController::class)->parameters(['inspection-types' => 'inspectionType']);
        Route::get( 'inspection-types/{inspectionType}/sections',       [InspectionTypeController::class, 'sections'])->name('inspection-types.sections');
        Route::post('inspection-types/{inspectionType}/sections',       [InspectionTypeController::class, 'syncSections'])->name('inspection-types.sections.sync');
        Route::resource('accounts',          AccountController::class);
        Route::resource('expense-heads',     ExpenseHeadController::class)->parameters(['expense-heads' => 'expense_head']);
        Route::resource('currencies',        CurrencyController::class);
        Route::resource('banks',             BankController::class);
        Route::resource('colors',            ColorController::class);
        Route::resource('sizes',             SizeController::class);
        Route::resource('defects',           DefectController::class);
    });

    // ─── Sample Operations ───────────────────────────────────────────────────────
    Route::get('customer-orders/export-pdf',                 [CustomerOrderController::class, 'exportListPdf'])->name('customer-orders.export-list-pdf');
    Route::get('customer-orders/{customerOrder}/export-pdf', [CustomerOrderController::class, 'exportPdf'])->name('customer-orders.export-pdf');
    Route::resource('customer-orders', CustomerOrderController::class)->parameters(['customer-orders' => 'customerOrder']);

    Route::get('samples/export-pdf',          [SampleController::class, 'exportListPdf'])->name('samples.export-list-pdf');
    Route::get('samples/{sample}/export-pdf', [SampleController::class, 'exportPdf'])->name('samples.export-pdf');
    Route::resource('samples', SampleController::class);

    // Grouped movement system (new)
    Route::get('movements/export-pdf',                 [MovementController::class, 'exportListPdf'])->name('movements.export-list-pdf');
    Route::get('movements/{movement}/export-pdf',      [MovementController::class, 'exportSinglePdf'])->name('movements.export-pdf');
    Route::get('movements',                  [MovementController::class, 'index'])->name('movements.index');
    Route::get('movements/create',           [MovementController::class, 'create'])->name('movements.create');
    Route::post('movements',                 [MovementController::class, 'store'])->name('movements.store');
    Route::get('movements/{movement}',       [MovementController::class, 'show'])->name('movements.show');
    Route::get('movements/{movement}/edit',  [MovementController::class, 'edit'])->name('movements.edit');
    Route::put('movements/{movement}',       [MovementController::class, 'update'])->name('movements.update');
    Route::delete('movements/{movement}',    [MovementController::class, 'destroy'])->name('movements.destroy');

    // Legacy nested individual movements (index / create / store only)
    Route::resource('samples.movements', SampleMovementController::class)->only(['index', 'create', 'store']);

    // Inspection Sections library management
    Route::resource('inspection-sections', InspectionSectionController::class)
        ->parameters(['inspection-sections' => 'inspectionSection'])
        ->except(['show']);

    // Inspections (top-level) + run sub-pages
    Route::resource('inspections', InspectionController::class);
    Route::prefix('inspections/{inspection}/runs')->name('inspections.runs.')->group(function () {
        Route::get('create',          [InspectionRunController::class, 'create'])->name('create');
        Route::post('',               [InspectionRunController::class, 'store'])->name('store');
        Route::get('{run}/edit',      [InspectionRunController::class, 'edit'])->name('edit');
        Route::put('{run}',           [InspectionRunController::class, 'update'])->name('update');
        Route::delete('{run}',        [InspectionRunController::class, 'destroy'])->name('destroy');
        // AJAX: section file upload / delete
        Route::post('{run}/sections/{runSection}/upload',  [InspectionRunController::class, 'uploadAttachment'])->name('sections.upload');
        Route::delete('{run}/attachments/{attachment}',    [InspectionRunController::class, 'deleteAttachment'])->name('attachments.delete');
        // AJAX: per-section (subsection) status + data persistence
        Route::post('{run}/sections/{runSection}/save',    [InspectionRunController::class, 'saveSection'])->name('sections.save');
        // PDF export
        Route::get('{run}/export-pdf',  [InspectionExportController::class, 'exportRun'])->name('export-pdf');
    });

    // Bulk PDF export for all/selected runs of an inspection
    Route::get('inspections/{inspection}/bulk-export-pdf', [InspectionExportController::class, 'bulkExport'])
        ->name('inspections.bulk-export-pdf');

    // AQL plan calculator (AJAX)
    Route::post('inspections/aql-calculate', [InspectionRunController::class, 'aqlCalculate'])
        ->name('inspections.aql.calculate');

    // ─── Finance ─────────────────────────────────────────────────────────────────
    Route::get('customer-invoices/by-customer', [CustomerInvoiceController::class, 'byCustomer'])->name('customer-invoices.by-customer');
    Route::get('customer-invoices/export-pdf',                           [CustomerInvoiceController::class, 'exportListPdf'])->name('customer-invoices.export-list-pdf');
    Route::get('customer-invoices/{customerInvoice}/export-pdf',         [CustomerInvoiceController::class, 'exportPdf'])->name('customer-invoices.export-pdf');
    Route::resource('customer-invoices', CustomerInvoiceController::class)->parameters(['customer-invoices' => 'customerInvoice']);

    Route::get('expenses/export-pdf', [ExpenseController::class, 'exportPdf'])->name('expenses.export-pdf');
    Route::get('expenses/{expense}/export-pdf', [ExpenseController::class, 'exportSinglePdf'])->name('expenses.export-single-pdf');
    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::get('salary/export-pdf',             [SalaryRunController::class, 'exportListPdf'])->name('salary.export-list-pdf');
    Route::get('salary/{salaryRun}/export-pdf', [SalaryRunController::class, 'exportPdf'])->name('salary.export-pdf');
    Route::resource('salary', SalaryRunController::class)->parameters(['salary' => 'salaryRun'])
        ->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::put('salary/{salaryRun}/lines',  [SalaryRunController::class, 'updateLines'])->name('salary.lines.update');
    Route::post('salary/{salaryRun}/pay',   [SalaryRunController::class, 'pay'])->name('salary.pay');

    Route::get('customer-payments/export-pdf',                           [CustomerPaymentController::class, 'exportListPdf'])->name('customer-payments.export-list-pdf');
    Route::get('customer-payments/{customerPayment}/export-pdf',         [CustomerPaymentController::class, 'exportPdf'])->name('customer-payments.export-pdf');
    Route::resource('customer-payments', CustomerPaymentController::class)->parameters(['customer-payments' => 'customerPayment'])
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    Route::resource('allowance-types', AllowanceTypeController::class)
        ->parameters(['allowance-types' => 'allowanceType'])
        ->except(['show']);

    Route::get('transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('transfers',       [TransferController::class, 'store'])->name('transfers.store');

    // ─── Attachments (polymorphic) ────────────────────────────────────────────────
    Route::post('attachments/{type}/{id}', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // ─── Admin ───────────────────────────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminUserController::class);
        Route::resource('roles', RoleController::class)->except(['show']);
    });

    // ─── Tools ───────────────────────────────────────────────────────────────────
    Route::get('tools/aql-calculator', AqlCalculatorController::class)->name('tools.aql-calculator');

    // ─── Ledgers & Reports ───────────────────────────────────────────────────────
    Route::prefix('ledger')->name('ledger.')->group(function () {
        Route::get('cash',                        [LedgerController::class, 'cash'])->name('cash');
        Route::get('cash/export-pdf',             [LedgerController::class, 'exportCash'])->name('cash.export-pdf');
        Route::get('bank',                        [LedgerController::class, 'bank'])->name('bank');
        Route::get('bank/export-pdf',             [LedgerController::class, 'exportBank'])->name('bank.export-pdf');
        Route::get('customers/{customer}',        [LedgerController::class, 'customer'])->name('customer');
        Route::get('customers/{customer}/export-pdf', [LedgerController::class, 'exportCustomer'])->name('customer.export-pdf');
    });
});

require __DIR__ . '/auth.php';
