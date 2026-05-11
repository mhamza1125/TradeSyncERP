@php
    $r = request()->route()?->getName() ?? '';
    $is = fn(string|array $p) => collect((array) $p)->contains(fn($pat) => \Str::is($pat, $r));
@endphp

<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand">
                <img src="{{ asset('assets/images/logo-full.png') }}" alt="TradeSyncERP" class="logo logo-lg" />
                <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="TradeSyncERP" class="logo logo-sm" />
            </a>
        </div>

        <div class="navbar-content">
            <ul class="nxl-navbar">

                {{-- ─── Dashboard ──────────────────────────────────────────────── --}}
                <li class="nxl-item nxl-caption">
                    <label>Main</label>
                </li>
                <li class="nxl-item {{ $is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>

                {{-- ─── Operations ─────────────────────────────────────────────── --}}
                @canany(['samples.index','sample-movements.index','inspections.index'])
                <li class="nxl-item nxl-caption">
                    <label>Operations</label>
                </li>

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
                @endcanany

                {{-- ─── Finance ─────────────────────────────────────────────────── --}}
                @canany(['customer-payments.index','vendor-bills.index','expenses.index','salary.index'])
                <li class="nxl-item nxl-caption">
                    <label>Finance</label>
                </li>

                @can('customer-payments.index')
                <li class="nxl-item {{ $is('customer-payments.*') ? 'active' : '' }}">
                    <a href="{{ route('customer-payments.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-credit-card"></i></span>
                        <span class="nxl-mtext">Customer Payments</span>
                    </a>
                </li>
                @endcan

                @can('vendor-bills.index')
                <li class="nxl-item {{ $is('vendor-bills.*') ? 'active' : '' }}">
                    <a href="{{ route('vendor-bills.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-file-text"></i></span>
                        <span class="nxl-mtext">Vendor Bills</span>
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
                @endcanany

                {{-- ─── Masters ─────────────────────────────────────────────────── --}}
                @canany(['customers.index','vendors.index','employees.index'])
                <li class="nxl-item nxl-caption">
                    <label>Masters</label>
                </li>

                @can('customers.index')
                <li class="nxl-item {{ $is('masters.customers.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.customers.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-user-check"></i></span>
                        <span class="nxl-mtext">Customers</span>
                    </a>
                </li>
                @endcan

                @can('vendors.index')
                <li class="nxl-item {{ $is('masters.vendors.*') ? 'active' : '' }}">
                    <a href="{{ route('masters.vendors.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-truck"></i></span>
                        <span class="nxl-mtext">Vendors</span>
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
                @endcanany

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

                {{-- ─── Settings ────────────────────────────────────────────────── --}}
                @canany(['accounts.index','currencies.index','banks.index','expense-heads.index','brands.index','categories.index','parameters.index'])
                <li class="nxl-item nxl-caption">
                    <label>Settings</label>
                </li>

                @canany(['accounts.index','currencies.index','banks.index'])
                <li class="nxl-item nxl-hasmenu {{ $is(['masters.accounts.*','masters.currencies.*','masters.banks.*']) ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                        <span class="nxl-mtext">Accounting</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @can('accounts.index')
                        <li class="nxl-item {{ $is('masters.accounts.*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('masters.accounts.index') }}">Accounts</a>
                        </li>
                        @endcan
                        @can('banks.index')
                        <li class="nxl-item {{ $is('masters.banks.*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('masters.banks.index') }}">Banks</a>
                        </li>
                        @endcan
                        @can('currencies.index')
                        <li class="nxl-item {{ $is('masters.currencies.*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('masters.currencies.index') }}">Currencies</a>
                        </li>
                        @endcan
                        @can('expense-heads.index')
                        <li class="nxl-item {{ $is('masters.expense-heads.*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('masters.expense-heads.index') }}">Expense Heads</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany

                @canany(['brands.index','categories.index','parameters.index'])
                <li class="nxl-item nxl-hasmenu {{ $is(['masters.brands.*','masters.categories.*','masters.parameters.*']) ? 'active' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-layers"></i></span>
                        <span class="nxl-mtext">Catalog</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        @can('brands.index')
                        <li class="nxl-item {{ $is('masters.brands.*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('masters.brands.index') }}">Brands</a>
                        </li>
                        @endcan
                        @can('categories.index')
                        <li class="nxl-item {{ $is('masters.categories.*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('masters.categories.index') }}">Categories</a>
                        </li>
                        @endcan
                        @can('parameters.index')
                        <li class="nxl-item {{ $is('masters.parameters.*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('masters.parameters.index') }}">Testing Parameters</a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
                @endcanany

            </ul>
        </div>
    </div>
</nav>
