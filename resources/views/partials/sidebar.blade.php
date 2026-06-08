@php
    $r = request()->route()?->getName() ?? '';
    $is = fn(string|array $p) => collect((array) $p)->contains(fn($pat) => \Str::is($pat, $r));
@endphp

<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand">
                <img src="{{ asset('assets/images/logo-trade.png') }}" alt="TradeSyncERP" class="logo logo-lg" style="height: 40px;" />
                <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="TradeSyncERP" class="logo logo-sm" />
            </a>
        </div>

        <div class="navbar-content">
            <ul class="nxl-navbar pb-5">

                {{-- ─── Dashboard ──────────────────────────────────────────────── --}}
                <li class="nxl-item nxl-caption">
                    <label>Dashboard</label>
                </li>
                <li class="nxl-item {{ $is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>
                <li class="nxl-item {{ $is('activities.index') ? 'active' : '' }}">
                    <a href="{{ route('activities.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-activity"></i></span>
                        <span class="nxl-mtext">Recent Activities</span>
                    </a>
                </li>

                {{-- ─── Operations (Core Work) ─────────────────────────────────── --}}
                @canany(['customer-orders.index','samples.index','sample-movements.index','inspections.index','inspection-sections.index'])
                <li class="nxl-item nxl-caption">
                    <label>Operations (Core Work)</label>
                </li>

                @can('customer-orders.index')
                <li class="nxl-item nxl-hasmenu {{ $is('customer-orders.*') ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-clipboard"></i></span>
                        <span class="nxl-mtext">Customer Orders</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ $is('customer-orders.index') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('customer-orders.index') }}">All Orders</a>
                        </li>
                        @can('customer-orders.create')
                        <li class="nxl-item {{ $is('customer-orders.create') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('customer-orders.create') }}">New Order</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('samples.index')
                <li class="nxl-item nxl-hasmenu {{ $is('samples.*') ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-package"></i></span>
                        <span class="nxl-mtext">Samples</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ $is('samples.index') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('samples.index') }}">All Samples</a>
                        </li>
                        @can('samples.create')
                        <li class="nxl-item {{ $is('samples.create') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('samples.create') }}">New Sample</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('sample-movements.index')
                <li class="nxl-item nxl-hasmenu {{ $is(['movements.*','samples.movements.*']) ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-send"></i></span>
                        <span class="nxl-mtext">Sample Movements</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ $is('movements.index') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('movements.index') }}">All Movements</a>
                        </li>
                        @can('sample-movements.create')
                        <li class="nxl-item {{ $is('movements.create') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('movements.create') }}">Record Movement</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('inspections.index')
                <li class="nxl-item nxl-hasmenu {{ $is('inspections.*') ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-search"></i></span>
                        <span class="nxl-mtext">Inspections</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ $is('inspections.index') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('inspections.index') }}">All Inspections</a>
                        </li>
                        @can('inspections.create')
                        <li class="nxl-item {{ $is('inspections.create') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('inspections.create') }}">New Inspection</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                @can('inspection-sections.index')
                <li class="nxl-item {{ $is('inspection-sections.*') ? 'active' : '' }}">
                    <a href="{{ route('inspection-sections.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-layers"></i></span>
                        <span class="nxl-mtext">Inspection Sections</span>
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- ─── Finance ─────────────────────────────────────────────────── --}}
                @canany(['customer-invoices.index','customer-payments.index','expenses.index','salary.index'])
                <li class="nxl-item nxl-caption">
                    <label>Finance</label>
                </li>

                @can('customer-invoices.index')
                <li class="nxl-item {{ $is('customer-invoices.*') ? 'active' : '' }}">
                    <a href="{{ route('customer-invoices.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-file-text"></i></span>
                        <span class="nxl-mtext">Customer Invoices</span>
                    </a>
                </li>
                @endcan

                @can('customer-payments.index')
                <li class="nxl-item {{ $is('customer-payments.*') ? 'active' : '' }}">
                    <a href="{{ route('customer-payments.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-credit-card"></i></span>
                        <span class="nxl-mtext">Customer Payments</span>
                    </a>
                </li>
                @endcan

                @can('expenses.index')
                <li class="nxl-item {{ $is('expenses.*') ? 'active' : '' }}">
                    <a href="{{ route('expenses.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-trending-down"></i></span>
                        <span class="nxl-mtext">Expenses</span>
                    </a>
                </li>
                @endcan

                @can('salary.index')
                <li class="nxl-item {{ $is('salary.*') ? 'active' : '' }}">
                    <a href="{{ route('salary.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">Salary Runs</span>
                    </a>
                </li>
                @endcan

                <li class="nxl-item {{ $is('allowance-types.*') ? 'active' : '' }}">
                    <a href="{{ route('allowance-types.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-gift"></i></span>
                        <span class="nxl-mtext">Allowance Types</span>
                    </a>
                </li>
                @endcanany

                {{-- ─── Master Data ─────────────────────────────────────────────── --}}
                @canany(['customers.index','suppliers.index','employees.index','inspection-types.index'])
                <li class="nxl-item nxl-caption">
                    <label>Master Data</label>
                </li>

                @can('customers.index')
                <li class="nxl-item {{ $is('masters.customers.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.customers.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-user-check"></i></span>
                        <span class="nxl-mtext">Customers</span>
                    </a>
                </li>
                @endcan

                @can('suppliers.index')
                <li class="nxl-item {{ $is('masters.suppliers.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.suppliers.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-truck"></i></span>
                        <span class="nxl-mtext">Suppliers</span>
                    </a>
                </li>
                @endcan

                @can('employees.index')
                <li class="nxl-item {{ $is('masters.employees.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.employees.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-briefcase"></i></span>
                        <span class="nxl-mtext">Employees</span>
                    </a>
                </li>
                @endcan

                @can('inspection-types.index')
                <li class="nxl-item nxl-hasmenu {{ $is('masters.inspection-types.*') ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-check-square"></i></span>
                        <span class="nxl-mtext">Inspection Types</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ $is('masters.inspection-types.index') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('masters.inspection-types.index') }}">All Types</a>
                        </li>
                        @can('inspection-types.create')
                        <li class="nxl-item {{ $is('masters.inspection-types.create') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('masters.inspection-types.create') }}">New Type</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan
                @endcanany

                {{-- ─── Tools ──────────────────────────────────────────────────── --}}
                <li class="nxl-item nxl-caption">
                    <label>Tools</label>
                </li>
                <li class="nxl-item {{ $is('tools.aql-calculator') ? 'active' : '' }}">
                    <a href="{{ route('tools.aql-calculator') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-cpu"></i></span>
                        <span class="nxl-mtext">AQL Calculator</span>
                    </a>
                </li>

                {{-- ─── Reports ─────────────────────────────────────────────────── --}}
                @can('reports.view')
                <li class="nxl-item nxl-caption">
                    <label>Reports</label>
                </li>
                <li class="nxl-item nxl-hasmenu {{ $is('ledger.*') ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-bar-chart-2"></i></span>
                        <span class="nxl-mtext">Ledgers</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ $is('ledger.cash') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('ledger.cash') }}">Cash Ledger</a>
                        </li>
                        <li class="nxl-item {{ $is('ledger.bank') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('ledger.bank') }}">Bank Ledger</a>
                        </li>
                    </ul>
                </li>
                @endcan

                {{-- ─── Configuration (Settings) ───────────────────────────────── --}}
                @canany(['categories.index','currencies.index','expense-heads.index','accounts.index','banks.index'])
                <li class="nxl-item nxl-caption">
                    <label>Configuration (Settings)</label>
                </li>

                @can('categories.index')
                <li class="nxl-item {{ $is('masters.categories.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.categories.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-tag"></i></span>
                        <span class="nxl-mtext">Categories</span>
                    </a>
                </li>
                @endcan


                @can('currencies.index')
                <li class="nxl-item {{ $is('masters.currencies.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.currencies.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                        <span class="nxl-mtext">Currencies</span>
                    </a>
                </li>
                @endcan

                @can('expense-heads.index')
                <li class="nxl-item {{ $is('masters.expense-heads.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.expense-heads.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-list"></i></span>
                        <span class="nxl-mtext">Expense Heads</span>
                    </a>
                </li>
                @endcan

                @can('accounts.index')
                <li class="nxl-item {{ $is('masters.accounts.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.accounts.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-book"></i></span>
                        <span class="nxl-mtext">Accounts</span>
                    </a>
                </li>
                @endcan

                @can('banks.index')
                <li class="nxl-item {{ $is('masters.banks.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.banks.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-credit-card"></i></span>
                        <span class="nxl-mtext">Banks</span>
                    </a>
                </li>
                @endcan

                <li class="nxl-item {{ $is('masters.colors.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.colors.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-droplet"></i></span>
                        <span class="nxl-mtext">Colors</span>
                    </a>
                </li>

                <li class="nxl-item {{ $is('masters.sizes.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.sizes.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-maximize-2"></i></span>
                        <span class="nxl-mtext">Sizes</span>
                    </a>
                </li>
                @endcanany

                {{-- ─── Administration (Admin Only) ────────────────────────────── --}}
                @role('Admin')
                <li class="nxl-item nxl-caption">
                    <label>Administration (Admin Only)</label>
                </li>
                <li class="nxl-item nxl-hasmenu {{ $is(['admin.users.*','admin.roles.*']) ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-settings"></i></span>
                        <span class="nxl-mtext">User Management</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item {{ $is('admin.users.*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('admin.users.index') }}">Users</a>
                        </li>
                        <li class="nxl-item {{ $is('admin.roles.*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('admin.roles.index') }}">Roles & Permissions</a>
                        </li>
                    </ul>
                </li>
                @endrole

            </ul>
        </div>
    </div>
</nav>
