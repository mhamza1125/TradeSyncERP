<header class="nxl-header">
    <div class="header-wrapper">

        {{-- ─── Header Left ──────────────────────────────────────────────── --}}
        <div class="header-left d-flex align-items-center gap-4">

            {{-- Mobile collapse toggle --}}
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse" role="button" aria-label="Open navigation menu" aria-controls="app-sidebar" aria-expanded="false">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>

            {{-- Desktop sidebar mini/expand toggle --}}
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button" class="nxl-head-link" role="button" aria-label="Collapse sidebar to icons">
                    <i class="feather-align-left" aria-hidden="true"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" class="nxl-head-link" role="button" aria-label="Expand sidebar" style="display:none">
                    <i class="feather-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            {{-- Company logo in the topbar while the sidebar is off-canvas (narrow screens).
                 (Replaces a "mega menu" toggle that had no menu to open.) --}}
            <a href="{{ route('dashboard') }}" class="ts-header-brand" aria-label="Trade Solutions — Dashboard">
                <img src="{{ asset('assets/images/logo-trade.png') }}" alt="">
            </a>

        </div>
        {{-- ─── End Header Left ──────────────────────────────────────────── --}}

        {{-- ─── Header Right ─────────────────────────────────────────────── --}}
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">

                <div class="nxl-h-item d-none d-sm-flex">
                        <div class="full-screen-switcher">
                            <a href="javascript:void(0);" class="nxl-head-link me-0" role="button" aria-label="Toggle full screen" onclick="$('body').fullScreenHelper('toggle');">
                                <i class="feather-maximize maximize"></i>
                                <i class="feather-minimize minimize"></i>
                            </a>
                        </div>
                    </div>
                    <div class="nxl-h-item dark-light-theme">
                        <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button" role="button" aria-label="Switch to dark mode">
                            <i class="feather-moon"></i>
                        </a>
                        <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" role="button" aria-label="Switch to light mode" style="display: none">
                            <i class="feather-sun"></i>
                        </a>
                    </div>
                    
                {{-- Notifications bell: only for users who may read the activity log (its feed is gated in AppServiceProvider) --}}
                @can('activities.view')
                <div class="dropdown nxl-h-item d-none d-sm-flex me-1">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" role="button" aria-label="Recent activity" aria-haspopup="true" aria-expanded="false">
                        <i class="feather-bell" aria-hidden="true"></i>
                        @if($headerActivityCount > 0)
                            <span class="badge bg-danger nxl-h-badge">{{ $headerActivityCount > 9 ? '9+' : $headerActivityCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown" style="width:340px;max-height:420px;overflow-y:auto;">
                        <div class="d-flex align-items-center justify-content-between notifications-head px-3 py-2 border-bottom">
                            <h6 class="fw-bold text-dark mb-0">Recent Activity</h6>
                            @if($headerActivityCount > 0)
                                <span class="badge bg-soft-danger text-danger fs-10">{{ $headerActivityCount }} new today</span>
                            @endif
                        </div>
                        @forelse($headerActivities as $activity)
                            @php
                                $modelName = class_basename($activity->subject_type ?? '');
                                $event = $activity->event ?? $activity->description;
                                $icons = ['created' => 'feather-plus-circle text-success', 'updated' => 'feather-edit-2 text-warning', 'deleted' => 'feather-trash-2 text-danger'];
                                $iconClass = $icons[$event] ?? 'feather-activity text-primary';
                            @endphp
                            <div class="d-flex align-items-start gap-3 px-3 py-2 border-bottom">
                                <div class="mt-1">
                                    <i class="{{ $iconClass }} fs-16"></i>
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <div class="fw-semibold fs-13 text-dark text-truncate">
                                        {{ ucfirst($event) }} {{ $modelName }}
                                    </div>
                                    <div class="fs-11 text-muted">{{ $activity->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="feather-bell-off fs-2 d-block mb-2"></i>
                                <small>No recent activity</small>
                            </div>
                        @endforelse
                    </div>
                </div>

                @endcan

                {{-- User profile dropdown --}}
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" class="d-flex align-items-center gap-2 text-dark text-decoration-none ts-user-menu-toggle" role="button" aria-label="User menu for {{ auth()->user()->name }}" aria-haspopup="true" aria-expanded="false">
                        <div class="avatar-text avatar-md bg-soft-primary text-primary rounded-circle fw-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="d-none d-md-block lh-sm">
                            <div class="fw-semibold fs-13 text-dark">{{ auth()->user()->name }}</div>
                            <div class="fs-11 text-muted">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</div>
                        </div>
                        <i class="feather-chevron-down fs-12 text-muted d-none d-md-block"></i>
                    </a>

                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown" style="min-width:220px">
                        <div class="dropdown-header border-bottom pb-3 mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-text avatar-lg bg-soft-primary text-primary rounded-circle fw-bold fs-20">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="fw-bold text-dark mb-0 text-truncate">{{ auth()->user()->name }}</h6>
                                    <div class="fs-12 text-muted text-truncate">{{ auth()->user()->email }}</div>
                                    <span class="badge bg-soft-success text-success fs-10">
                                        {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="feather-user me-2"></i>
                            <span>My Profile</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="feather-log-out me-2"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        {{-- ─── End Header Right ─────────────────────────────────────────── --}}

    </div>
</header>
