<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Master data
            'customers.index', 'customers.create', 'customers.edit', 'customers.delete',
            'brands.index', 'brands.create', 'brands.edit', 'brands.delete',
            'categories.index', 'categories.create', 'categories.edit', 'categories.delete',
            'employees.index', 'employees.create', 'employees.edit', 'employees.delete',
            'vendors.index', 'vendors.create', 'vendors.edit', 'vendors.delete',
            'parameters.index', 'parameters.create', 'parameters.edit', 'parameters.delete',
            'accounts.index', 'accounts.create', 'accounts.edit', 'accounts.delete',
            'expense-heads.index', 'expense-heads.create', 'expense-heads.edit', 'expense-heads.delete',
            'currencies.index', 'currencies.create', 'currencies.edit', 'currencies.delete',
            'banks.index', 'banks.create', 'banks.edit', 'banks.delete',

            // Operations
            'samples.index', 'samples.create', 'samples.edit', 'samples.delete',
            'sample-movements.index', 'sample-movements.create', 'sample-movements.edit', 'sample-movements.delete',
            'inspections.index', 'inspections.create', 'inspections.edit', 'inspections.delete',

            // Finance
            'vendor-bills.index', 'vendor-bills.create', 'vendor-bills.edit', 'vendor-bills.delete', 'vendor-bills.pay',
            'expenses.index', 'expenses.create', 'expenses.delete',
            'salary.index', 'salary.create', 'salary.edit', 'salary.pay',
            'customer-payments.index', 'customer-payments.create', 'customer-payments.delete',

            // Reports
            'reports.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Admin — full access to everything
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions(Permission::all());

        // Lab Manager — full operations, read-only finance
        $labManager = Role::firstOrCreate(['name' => 'Lab Manager']);
        $labManager->syncPermissions([
            'customers.index', 'customers.create', 'customers.edit',
            'brands.index', 'brands.create', 'brands.edit',
            'categories.index', 'categories.create', 'categories.edit',
            'employees.index', 'employees.create', 'employees.edit',
            'vendors.index', 'vendors.create', 'vendors.edit',
            'parameters.index', 'parameters.create', 'parameters.edit', 'parameters.delete',
            'samples.index', 'samples.create', 'samples.edit', 'samples.delete',
            'sample-movements.index', 'sample-movements.create', 'sample-movements.edit', 'sample-movements.delete',
            'inspections.index', 'inspections.create', 'inspections.edit', 'inspections.delete',
            'vendor-bills.index', 'vendor-bills.create', 'vendor-bills.edit',
            'expenses.index',
            'salary.index',
            'customer-payments.index',
            'reports.view',
        ]);

        // Accountant — full finance, read-only operations
        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $accountant->syncPermissions([
            'customers.index', 'customers.create', 'customers.edit',
            'vendors.index', 'vendors.create', 'vendors.edit',
            'accounts.index', 'accounts.create', 'accounts.edit',
            'expense-heads.index', 'expense-heads.create', 'expense-heads.edit',
            'currencies.index', 'currencies.create', 'currencies.edit',
            'banks.index', 'banks.create', 'banks.edit',
            'samples.index',
            'inspections.index',
            'vendor-bills.index', 'vendor-bills.create', 'vendor-bills.edit', 'vendor-bills.delete', 'vendor-bills.pay',
            'expenses.index', 'expenses.create', 'expenses.delete',
            'salary.index', 'salary.create', 'salary.edit', 'salary.pay',
            'customer-payments.index', 'customer-payments.create', 'customer-payments.delete',
            'reports.view',
        ]);

        // Employee — read-only on assigned samples and own movements
        $employee = Role::firstOrCreate(['name' => 'Employee']);
        $employee->syncPermissions([
            'samples.index',
            'sample-movements.index',
            'inspections.index',
        ]);
    }
}
