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
            'customers.index', 'customers.create', 'customers.edit', 'customers.delete', 'customers.view',
            'brands.index', 'brands.create', 'brands.edit', 'brands.delete', 'brands.view',
            'categories.index', 'categories.create', 'categories.edit', 'categories.delete', 'categories.view',
            'employees.index', 'employees.create', 'employees.edit', 'employees.delete', 'employees.view',
            'suppliers.index', 'suppliers.create', 'suppliers.edit', 'suppliers.delete', 'suppliers.view',
            'inspection-types.index', 'inspection-types.create', 'inspection-types.edit', 'inspection-types.delete', 'inspection-types.view',
            'accounts.index', 'accounts.create', 'accounts.edit', 'accounts.delete', 'accounts.view',
            'expense-heads.index', 'expense-heads.create', 'expense-heads.edit', 'expense-heads.delete', 'expense-heads.view',
            'currencies.index', 'currencies.create', 'currencies.edit', 'currencies.delete', 'currencies.view',
            'banks.index', 'banks.create', 'banks.edit', 'banks.delete', 'banks.view',
            'colors.index', 'colors.create', 'colors.edit', 'colors.delete', 'colors.view',
            'sizes.index', 'sizes.create', 'sizes.edit', 'sizes.delete', 'sizes.view',

            // Operations
            'samples.index', 'samples.create', 'samples.edit', 'samples.delete', 'samples.view',
            'sample-movements.index', 'sample-movements.create', 'sample-movements.edit', 'sample-movements.delete', 'sample-movements.view',
            'inspections.index', 'inspections.create', 'inspections.edit', 'inspections.delete', 'inspections.view',
            'inspection-sections.index', 'inspection-sections.create', 'inspection-sections.edit', 'inspection-sections.delete',

            // Operations – customer orders
            'customer-orders.index', 'customer-orders.create', 'customer-orders.edit', 'customer-orders.delete', 'customer-orders.view',

            // Finance
            'expenses.index', 'expenses.create', 'expenses.edit', 'expenses.delete', 'expenses.view',
            'salary.index', 'salary.create', 'salary.edit', 'salary.delete', 'salary.pay', 'salary.view',
            'customer-payments.index', 'customer-payments.create', 'customer-payments.edit', 'customer-payments.delete', 'customer-payments.view',
            'customer-invoices.index', 'customer-invoices.create', 'customer-invoices.edit', 'customer-invoices.delete', 'customer-invoices.view',
            'allowance-types.index', 'allowance-types.create', 'allowance-types.edit', 'allowance-types.delete',
            'transfers.create',

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
            'customers.index', 'customers.create', 'customers.edit', 'customers.view',
            'brands.index', 'brands.create', 'brands.edit', 'brands.view',
            'categories.index', 'categories.create', 'categories.edit', 'categories.view',
            'employees.index', 'employees.create', 'employees.edit', 'employees.view',
            'suppliers.index', 'suppliers.create', 'suppliers.edit', 'suppliers.view',
            'inspection-types.index', 'inspection-types.create', 'inspection-types.edit', 'inspection-types.view',
            'colors.index', 'colors.create', 'colors.edit', 'colors.view',
            'sizes.index', 'sizes.create', 'sizes.edit', 'sizes.view',
            'customer-orders.index', 'customer-orders.create', 'customer-orders.edit', 'customer-orders.delete', 'customer-orders.view',
            'samples.index', 'samples.create', 'samples.edit', 'samples.delete', 'samples.view',
            'sample-movements.index', 'sample-movements.create', 'sample-movements.edit', 'sample-movements.delete', 'sample-movements.view',
            'inspections.index', 'inspections.create', 'inspections.edit', 'inspections.delete', 'inspections.view',
            'inspection-sections.index', 'inspection-sections.create', 'inspection-sections.edit',
            'expenses.index', 'expenses.view',
            'salary.index', 'salary.view',
            'customer-payments.index', 'customer-payments.view',
            'customer-invoices.index', 'customer-invoices.view',
            'reports.view',
        ]);

        // Accountant — full finance, read-only operations
        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $accountant->syncPermissions([
            'customers.index', 'customers.create', 'customers.edit', 'customers.view',
            'suppliers.index', 'suppliers.create', 'suppliers.edit', 'suppliers.view',
            'accounts.index', 'accounts.create', 'accounts.edit', 'accounts.view',
            'expense-heads.index', 'expense-heads.create', 'expense-heads.edit', 'expense-heads.view',
            'currencies.index', 'currencies.create', 'currencies.edit', 'currencies.view',
            'banks.index', 'banks.create', 'banks.edit', 'banks.view',
            'customer-orders.index', 'customer-orders.view',
            'samples.index', 'samples.view',
            'inspections.index', 'inspections.view',
            'expenses.index', 'expenses.create', 'expenses.edit', 'expenses.delete', 'expenses.view',
            'salary.index', 'salary.create', 'salary.edit', 'salary.delete', 'salary.pay', 'salary.view',
            'customer-payments.index', 'customer-payments.create', 'customer-payments.edit', 'customer-payments.delete', 'customer-payments.view',
            'customer-invoices.index', 'customer-invoices.create', 'customer-invoices.edit', 'customer-invoices.delete', 'customer-invoices.view',
            'allowance-types.index', 'allowance-types.create', 'allowance-types.edit',
            'transfers.create',
            'reports.view',
        ]);

        // Employee — read-only on assigned samples and own movements
        $employee = Role::firstOrCreate(['name' => 'Employee']);
        $employee->syncPermissions([
            'samples.index', 'samples.view',
            'sample-movements.index', 'sample-movements.view',
            'inspections.index', 'inspections.view',
        ]);
    }
}
