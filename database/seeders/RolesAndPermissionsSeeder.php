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
            'suppliers.index', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'inspection-types.index', 'inspection-types.create', 'inspection-types.edit', 'inspection-types.delete',
            'parameters.index', 'parameters.create', 'parameters.edit', 'parameters.delete',
            'accounts.index', 'accounts.create', 'accounts.edit', 'accounts.delete',
            'expense-heads.index', 'expense-heads.create', 'expense-heads.edit', 'expense-heads.delete',
            'currencies.index', 'currencies.create', 'currencies.edit', 'currencies.delete',
            'banks.index', 'banks.create', 'banks.edit', 'banks.delete',

            // Operations
            'samples.index', 'samples.create', 'samples.edit', 'samples.delete',
            'sample-movements.index', 'sample-movements.create', 'sample-movements.edit', 'sample-movements.delete',
            'inspections.index', 'inspections.create', 'inspections.edit', 'inspections.delete',

            // Operations – customer orders
            'customer-orders.index', 'customer-orders.create', 'customer-orders.edit', 'customer-orders.delete',

            // Finance
            'expenses.index', 'expenses.create', 'expenses.delete',
            'salary.index', 'salary.create', 'salary.edit', 'salary.pay',
            'customer-payments.index', 'customer-payments.create', 'customer-payments.delete',
            'customer-invoices.index', 'customer-invoices.create', 'customer-invoices.edit', 'customer-invoices.delete',

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
            'suppliers.index', 'suppliers.create', 'suppliers.edit',
            'inspection-types.index', 'inspection-types.create', 'inspection-types.edit',
            'parameters.index', 'parameters.create', 'parameters.edit', 'parameters.delete',
            'customer-orders.index', 'customer-orders.create', 'customer-orders.edit', 'customer-orders.delete',
            'samples.index', 'samples.create', 'samples.edit', 'samples.delete',
            'sample-movements.index', 'sample-movements.create', 'sample-movements.edit', 'sample-movements.delete',
            'inspections.index', 'inspections.create', 'inspections.edit', 'inspections.delete',
            'expenses.index',
            'salary.index',
            'customer-payments.index',
            'customer-invoices.index',
            'reports.view',
        ]);

        // Accountant — full finance, read-only operations
        $accountant = Role::firstOrCreate(['name' => 'Accountant']);
        $accountant->syncPermissions([
            'customers.index', 'customers.create', 'customers.edit',
            'suppliers.index', 'suppliers.create', 'suppliers.edit',
            'accounts.index', 'accounts.create', 'accounts.edit',
            'expense-heads.index', 'expense-heads.create', 'expense-heads.edit',
            'currencies.index', 'currencies.create', 'currencies.edit',
            'banks.index', 'banks.create', 'banks.edit',
            'customer-orders.index',
            'samples.index',
            'inspections.index',
            'expenses.index', 'expenses.create', 'expenses.delete',
            'salary.index', 'salary.create', 'salary.edit', 'salary.pay',
            'customer-payments.index', 'customer-payments.create', 'customer-payments.delete',
            'customer-invoices.index', 'customer-invoices.create', 'customer-invoices.edit', 'customer-invoices.delete',
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
