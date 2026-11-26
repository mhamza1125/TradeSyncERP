@php
    $r = request()->route()?->getName() ?? '';
    $is = fn(string|array $p) => collect((array) $p)->contains(fn($pat) => \Str::is($pat, $r));
    $user = auth()->user();

    // The navigation, grouped by module (docs/DESIGN.md §4). Each link names the SAME permission
    // its route's controller middleware requires (route:list), so a link is shown only to users
    // who can open it. Hiding is cosmetic: the server-side checks stay as they are.
    //   perm: permission name | role: role name | null = every signed-in user
    $groups = [
        ['key' => 'overview', 'label' => 'Overview', 'links' => [
            ['label' => 'Dashboard', 'icon' => 'feather-airplay', 'route' => 'dashboard', 'active' => 'dashboard', 'perm' => null],
            ['label' => 'Recent Activities', 'icon' => 'feather-activity', 'route' => 'activities.index', 'active' => 'activities.*', 'perm' => 'activities.view'],
        ]],
        ['key' => 'operations', 'label' => 'Operations', 'links' => [
            ['label' => 'Customer Orders', 'icon' => 'feather-clipboard', 'active' => 'customer-orders.*', 'perm' => 'customer-orders.index', 'children' => [
                ['label' => 'All Orders', 'route' => 'customer-orders.index', 'active' => 'customer-orders.index', 'perm' => 'customer-orders.index'],
                ['label' => 'New Order', 'route' => 'customer-orders.create', 'active' => 'customer-orders.create', 'perm' => 'customer-orders.create'],
            ]],
            ['label' => 'Samples', 'icon' => 'feather-package', 'active' => 'samples.*', 'perm' => 'samples.index', 'children' => [
                ['label' => 'All Samples', 'route' => 'samples.index', 'active' => 'samples.index', 'perm' => 'samples.index'],
                ['label' => 'New Sample', 'route' => 'samples.create', 'active' => 'samples.create', 'perm' => 'samples.create'],
            ]],
            ['label' => 'Sample Movements', 'icon' => 'feather-send', 'active' => ['movements.*', 'samples.movements.*'], 'perm' => 'sample-movements.index', 'children' => [
                ['label' => 'All Movements', 'route' => 'movements.index', 'active' => 'movements.index', 'perm' => 'sample-movements.index'],
                ['label' => 'Record Movement', 'route' => 'movements.create', 'active' => 'movements.create', 'perm' => 'sample-movements.create'],
            ]],
        ]],
        ['key' => 'quality', 'label' => 'Quality control', 'links' => [
            ['label' => 'Inspections', 'icon' => 'feather-search', 'active' => 'inspections.*', 'perm' => 'inspections.index', 'children' => [
                ['label' => 'All Inspections', 'route' => 'inspections.index', 'active' => 'inspections.index', 'perm' => 'inspections.index'],
                ['label' => 'New Inspection', 'route' => 'inspections.create', 'active' => 'inspections.create', 'perm' => 'inspections.create'],
            ]],
            ['label' => 'Inspection Types', 'icon' => 'feather-check-square', 'route' => 'masters.inspection-types.index', 'active' => 'masters.inspection-types.*', 'perm' => 'inspection-types.index'],
            ['label' => 'Inspection Sections', 'icon' => 'feather-layers', 'route' => 'inspection-sections.index', 'active' => 'inspection-sections.*', 'perm' => 'inspection-sections.index'],
            ['label' => 'Defects', 'icon' => 'feather-alert-triangle', 'route' => 'masters.defects.index', 'active' => 'masters.defects.*', 'perm' => 'defects.index'],
            ['label' => 'AQL Calculator', 'icon' => 'feather-cpu', 'route' => 'tools.aql-calculator.index', 'active' => 'tools.aql-calculator.*', 'perm' => 'aql-calculator.index'],
            ['label' => 'QC Image Test', 'icon' => 'feather-image', 'route' => 'tools.qc-test.index', 'active' => 'tools.qc-test.*', 'perm' => 'qc-test.index'],
        ]],
        ['key' => 'finance', 'label' => 'Finance', 'links' => [
            ['label' => 'Customer Invoices', 'icon' => 'feather-file-text', 'route' => 'customer-invoices.index', 'active' => 'customer-invoices.*', 'perm' => 'customer-invoices.index'],
            ['label' => 'Customer Payments', 'icon' => 'feather-credit-card', 'route' => 'customer-payments.index', 'active' => 'customer-payments.*', 'perm' => 'customer-payments.index'],
            ['label' => 'Expenses', 'icon' => 'feather-trending-down', 'route' => 'expenses.index', 'active' => 'expenses.*', 'perm' => 'expenses.index'],
            ['label' => 'Purchase Orders', 'icon' => 'feather-shopping-cart', 'route' => 'purchase-orders.index', 'active' => 'purchase-orders.*', 'perm' => 'purchase-orders.index'],
            ['label' => 'Ledgers', 'icon' => 'feather-bar-chart-2', 'active' => 'ledger.*', 'perm' => 'reports.view', 'children' => [
                ['label' => 'Cash Ledger', 'route' => 'ledger.cash', 'active' => 'ledger.cash', 'perm' => 'reports.view'],
                ['label' => 'Bank Ledger', 'route' => 'ledger.bank', 'active' => 'ledger.bank', 'perm' => 'reports.view'],
            ]],
            ['label' => 'Accounts', 'icon' => 'feather-book', 'route' => 'masters.accounts.index', 'active' => 'masters.accounts.*', 'perm' => 'accounts.index'],
            ['label' => 'Banks', 'icon' => 'feather-home', 'route' => 'masters.banks.index', 'active' => 'masters.banks.*', 'perm' => 'banks.index'],
            ['label' => 'Expense Heads', 'icon' => 'feather-list', 'route' => 'masters.expense-heads.index', 'active' => 'masters.expense-heads.*', 'perm' => 'expense-heads.index'],
            ['label' => 'Currencies', 'icon' => 'feather-dollar-sign', 'route' => 'masters.currencies.index', 'active' => 'masters.currencies.*', 'perm' => 'currencies.index'],
        ]],
        ['key' => 'hr', 'label' => 'HR & payroll', 'links' => [
            ['label' => 'Employees', 'icon' => 'feather-briefcase', 'route' => 'masters.employees.index', 'active' => 'masters.employees.*', 'perm' => 'employees.index'],
            ['label' => 'Salary Runs', 'icon' => 'feather-users', 'route' => 'salary.index', 'active' => 'salary.*', 'perm' => 'salary.index'],
            ['label' => 'Allowance Types', 'icon' => 'feather-gift', 'route' => 'allowance-types.index', 'active' => 'allowance-types.*', 'perm' => 'allowance-types.index'],
        ]],
        ['key' => 'masters', 'label' => 'Master data', 'links' => [
            ['label' => 'Customers', 'icon' => 'feather-user-check', 'route' => 'masters.customers.index', 'active' => 'masters.customers.*', 'perm' => 'customers.index'],
            ['label' => 'Suppliers', 'icon' => 'feather-truck', 'route' => 'masters.suppliers.index', 'active' => 'masters.suppliers.*', 'perm' => 'suppliers.index'],
            ['label' => 'Categories', 'icon' => 'feather-tag', 'route' => 'masters.categories.index', 'active' => 'masters.categories.*', 'perm' => 'categories.index'],
            ['label' => 'Colors', 'icon' => 'feather-droplet', 'route' => 'masters.colors.index', 'active' => 'masters.colors.*', 'perm' => 'colors.index'],
            ['label' => 'Sizes', 'icon' => 'feather-maximize-2', 'route' => 'masters.sizes.index', 'active' => 'masters.sizes.*', 'perm' => 'sizes.index'],
        ]],
        ['key' => 'admin', 'label' => 'Administration', 'links' => [
            ['label' => 'User Management', 'icon' => 'feather-settings', 'active' => ['admin.users.*', 'admin.roles.*'], 'role' => 'Admin', 'children' => [
                ['label' => 'Users', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'role' => 'Admin'],
                ['label' => 'Roles & Permissions', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'role' => 'Admin'],
            ]],
            ['label' => 'Company Settings', 'icon' => 'feather-briefcase', 'route' => 'admin.company-settings.edit', 'active' => 'admin.company-settings.*', 'role' => 'Admin'],
        ]],
    ];

    $allowed = function (array $item) use ($user): bool {
        if (! $user) {
            return false;
        }
        if (isset($item['role'])) {
            return $user->hasRole($item['role']);
        }

        return empty($item['perm']) || $user->can($item['perm']);
    };

    // Keep only what this user may open; drop empty groups.
    $visibleGroups = collect($groups)->map(function ($g) use ($allowed) {
        $g['links'] = collect($g['links'])->filter($allowed)->map(function ($link) use ($allowed) {
            if (isset($link['children'])) {
                $link['children'] = collect($link['children'])->filter($allowed)->values()->all();
            }

            return $link;
        })->filter(fn ($link) => ! isset($link['children']) || $link['children'] !== [])->values()->all();

        return $g;
    })->filter(fn ($g) => $g['links'] !== [])->values();
@endphp

<nav class="nxl-navigation" aria-label="Main navigation" id="app-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand" aria-label="TradeSyncERP — Dashboard">
                <img src="{{ asset('assets/images/logo-trade.png') }}" alt="Trade Solutions" class="logo logo-lg" style="height: 40px;" />
                <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="" class="logo logo-sm" />
            </a>
        </div>

        <div class="navbar-content">
            <ul class="nxl-navbar pb-5">
                @foreach($visibleGroups as $group)
                    @php
                        $groupActive = collect($group['links'])->contains(fn ($l) => $is($l['active']));
                        $itemIds = collect($group['links'])->keys()->map(fn ($i) => "nav-{$group['key']}-{$i}")->implode(' ');
                    @endphp
                    <li class="nxl-item nxl-caption ts-nav-group" data-nav-group-header="{{ $group['key'] }}" @if($groupActive) data-nav-group-active @endif>
                        <button type="button" class="ts-nav-group-toggle" aria-expanded="true" aria-controls="{{ $itemIds }}">
                            <span>{{ $group['label'] }}</span>
                            <i class="feather-chevron-down ts-nav-chevron" aria-hidden="true"></i>
                        </button>
                    </li>

                    @foreach($group['links'] as $i => $link)
                        @php $active = $is($link['active']); @endphp
                        @if(isset($link['children']))
                        <li class="nxl-item nxl-hasmenu {{ $active ? 'active' : '' }}" data-nav-group="{{ $group['key'] }}" id="nav-{{ $group['key'] }}-{{ $i }}">
                            <a href="javascript:void(0);" class="nxl-link" role="button" aria-haspopup="true">
                                <span class="nxl-micon"><i class="{{ $link['icon'] }}" aria-hidden="true"></i></span>
                                <span class="nxl-mtext">{{ $link['label'] }}</span>
                                <span class="nxl-arrow"><i class="feather-chevron-right" aria-hidden="true"></i></span>
                            </a>
                            <ul class="nxl-submenu">
                                @foreach($link['children'] as $child)
                                <li class="nxl-item {{ $is($child['active']) ? 'active' : '' }}">
                                    <a class="nxl-link" href="{{ route($child['route']) }}" @if($is($child['active'])) aria-current="page" @endif>{{ $child['label'] }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </li>
                        @else
                        <li class="nxl-item {{ $active ? 'active' : '' }}" data-nav-group="{{ $group['key'] }}" id="nav-{{ $group['key'] }}-{{ $i }}">
                            <a href="{{ route($link['route']) }}" class="nxl-link" @if($active) aria-current="page" @endif>
                                <span class="nxl-micon"><i class="{{ $link['icon'] }}" aria-hidden="true"></i></span>
                                <span class="nxl-mtext">{{ $link['label'] }}</span>
                            </a>
                        </li>
                        @endif
                    @endforeach
                @endforeach
            </ul>
        </div>
    </div>
</nav>
