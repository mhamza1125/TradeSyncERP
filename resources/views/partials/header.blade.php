<header class="nxl-header">
    <div class="header-wrapper">

        {{-- ─── Header Left ──────────────────────────────────────────────── --}}
        <div class="header-left d-flex align-items-center gap-4">

            {{-- Mobile collapse toggle --}}
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>

            {{-- Desktop sidebar mini/expand toggle --}}
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display:none">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>

            {{-- Mobile mega-menu toggle --}}
            <div class="nxl-lavel-mega-menu-toggle d-flex d-lg-none">
                <a href="javascript:void(0);" id="nxl-lavel-mega-menu-open">
                    <i class="feather-align-left"></i>
                </a>
            </div>

        </div>
        {{-- ─── End Header Left ──────────────────────────────────────────── --}}

        {{-- ─── Header Right ─────────────────────────────────────────────── --}}
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">

                <div class="nxl-h-item d-none d-sm-flex">
                        <div class="full-screen-switcher">
                            <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$('body').fullScreenHelper('toggle');">
                                <i class="feather-maximize maximize"></i>
                                <i class="feather-minimize minimize"></i>
                            </a>
                        </div>
                    </div>
                    <div class="nxl-h-item dark-light-theme">
                        <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                            <i class="feather-moon"></i>
                        </a>
                        <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                            <i class="feather-sun"></i>
                        </a>
                    </div>
                    
                {{-- Notifications bell --}}
                <div class="dropdown nxl-h-item d-none d-sm-flex me-1">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown">
                        <i class="feather-bell"></i>
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

                {{-- User profile dropdown --}}
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" class="d-flex align-items-center gap-2 text-dark text-decoration-none">
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
