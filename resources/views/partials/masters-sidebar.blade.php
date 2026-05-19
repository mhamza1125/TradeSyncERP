<div class="content-sidebar content-sidebar-md" data-scrollbar-target="#psScrollbarInit">
    <div class="content-sidebar-header bg-white sticky-top hstack justify-content-between">
        <h4 class="fw-bolder mb-0">Configuration</h4>
        <a href="javascript:void(0);" class="app-sidebar-close-trigger d-flex">
            <i class="feather-x"></i>
        </a>
    </div>
    <div class="content-sidebar-body">
        <ul class="nav flex-column nxl-content-sidebar-item">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('masters.categories.*') ? 'active' : '' }}"
                   href="{{ route('masters.categories.index') }}">
                    <i class="feather-layers"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('masters.parameters.*') ? 'active' : '' }}"
                   href="{{ route('masters.parameters.index') }}">
                    <i class="feather-sliders"></i>
                    <span>Testing Parameters</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('masters.currencies.*') ? 'active' : '' }}"
                   href="{{ route('masters.currencies.index') }}">
                    <i class="feather-dollar-sign"></i>
                    <span>Currencies</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('masters.expense-heads.*') ? 'active' : '' }}"
                   href="{{ route('masters.expense-heads.index') }}">
                    <i class="feather-briefcase"></i>
                    <span>Expense Heads</span>
                </a>
            </li>
        </ul>
    </div>
</div>
