<style>
    /* -----------------------------------------
       1. Logo Wrapper Overrides
    ----------------------------------------- */
    .sz-logo-wrapper-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0;
        line-height: 0;
        margin-top: 30px;
        margin-bottom: 20px;
        width: 100%;
    }

    .sz-logo-top {
        display: block;
        max-width: 100%;
        height: auto;
        max-height: 50px;
        margin-bottom: -5px;
    }

    .sz-logo-bottom {
        display: block;
        width: 100%;
        max-width: 180px;
        height: auto;
        margin-top: 0;
    }

    .horizontal-logo .logo-lg img {
        height: 60px;
        object-fit: cover;
    }

    /* -----------------------------------------
       2. Minimized Sidebar Parent Link Hover
       (Your Custom Red/Dark Gradient)
    ----------------------------------------- */
    :is([data-layout=vertical], [data-layout=semibox])[data-sidebar-size=sm] .navbar-menu .navbar-nav .nav-item:hover>a.menu-link {
        position: relative;
        width: calc(200px + var(--vz-vertical-menu-width-sm)) !important;
        -webkit-transition: none !important;
        transition: none !important;

        /* Applied your custom requested gradient */
        background: #000000 !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;

        /* Polished UI Tweaks: white text contrast and rounded top-right edge */
        color: #ffffff !important;
        border-top-right-radius: 16px !important;
        box-shadow: 10px 4px 30px rgba(0, 0, 0, 0.25) !important;
        z-index: 1001 !important;
    }

    /* Force the icon inside the hovered parent link to white */
    :is([data-layout=vertical], [data-layout=semibox])[data-sidebar-size=sm] .navbar-menu .navbar-nav .nav-item:hover>a.menu-link i {
        color: #ffffff !important;
    }

    /* -----------------------------------------
       3. Premium Sidebar Sub-Menu (Flyout)
    ----------------------------------------- */
    [data-sidebar-size="sm"] .navbar-menu .navbar-nav .nav-item:hover>.menu-dropdown,
    [data-sidebar-size="sm"] .navbar-menu .navbar-nav .nav-item .menu-dropdown.show {
        /* Applied your custom requested gradient */
        background: linear-gradient(180deg, #050505 0%, #1a0a0a 15%, #4b0f0f 35%, #b91c1c 65%, #7f1d1d 85%, #111111 100%) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;

        /* Match the border radius with the parent link hover state */
        border-radius: 0 0 16px 16px !important;
        box-shadow: 10px 10px 30px rgba(0, 0, 0, 0.3) !important;
        padding: 12px 0 !important;
        animation: fadeSlideIn 0.3s ease-out forwards;
    }

    [data-sidebar-size="sm"] .navbar-menu .navbar-nav .nav-item .menu-dropdown .nav-link {
        color: #9ba1a6 !important;
        padding: 10px 24px !important;
        margin: 2px 12px !important;
        border-radius: 8px !important;
        transition: all 0.3s ease !important;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    [data-sidebar-size="sm"] .navbar-menu .navbar-nav .nav-item .menu-dropdown .nav-link:hover {
        background: rgba(255, 255, 255, 0.06) !important;
        color: #ffffff !important;
        transform: translateX(4px);
    }

    [data-sidebar-size="sm"] .navbar-menu .navbar-nav .nav-item .menu-dropdown .nav-link.active {
        background: linear-gradient(90deg, rgba(230, 56, 56, 0.1) 0%, transparent 100%) !important;
        color: #f04444 !important;
        border-left: 2px solid #f04444 !important;
    }

    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>

<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('index') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/rhino-logo.webp') }}"
                    alt="Small Logo" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/rhino-logo.webp') }}"
                    alt="" height="17">
                <img src="{{ asset('frontend/images/logo-bg.webp') }}" alt="Logo" width="230px"
                    class="logo-second">
            </span>
        </a>

        <!-- Light Logo-->
        <a href="{{ route('index') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/adminlogos.png') }}"
                    alt="Small Logo" height="22">
            </span>

            <span class="logo-lg">
                <div class="sz-logo-wrapper-inner">
                    <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/adminlogos.png') }}"
                        alt="Top Logo" height="50" class="sz-logo-top">
                    <img src="{{ asset('frontend/images/logo-bg.webp') }}" alt="Bottom Logo"
                        class="logo-second sz-logo-bottom">
                </div>
            </span>
        </a>

        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user" src="{{ asset('admin/images/users/avatar-1.jpg') }}"
                    alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">Anna Adame</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i
                            class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span
                            class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header">Welcome Anna!</h6>
            <a class="dropdown-item" href="pages-profile.html"><i
                    class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Profile</span></a>
            <a class="dropdown-item" href="apps-chat.html"><i
                    class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Messages</span></a>
            <a class="dropdown-item" href="apps-tasks-kanban.html"><i
                    class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Taskboard</span></a>
            <a class="dropdown-item" href="pages-faqs.html"><i
                    class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Help</span></a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="pages-profile.html"><i
                    class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Balance :
                    <b>$5971.67</b></span></a>
            <a class="dropdown-item" href="pages-profile-settings.html"><span
                    class="badge bg-success-subtle text-success mt-1 float-end">New</span><i
                    class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Settings</span></a>
            <a class="dropdown-item" href="auth-lockscreen-basic.html"><i
                    class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock
                    screen</span></a>
            <a class="dropdown-item" href="auth-logout-basic.html"><i
                    class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle"
                    data-key="t-logout">Logout</span></a>
        </div>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                @can('dashboard', \App\Models\General::class)
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('admin/dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                        </a>
                    </li>
                @endcan

                {{-- @can('read', \App\Models\ContactEnquiry::class)
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('admin/enquiries*') ? 'active' : '' }}"
                            href="{{ route('admin.enquiry.index') }}">
                            <i class="ri-question-answer-line"></i> <span data-key="t-dashboards">Enquiry List</span>
                        </a>
                    </li>
                @endcan --}}

                {{-- @can('read', \App\Models\Pages::class)
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('admin/pages*') ? 'active' : '' }}"
                            href="{{ route('admin.pages.index') }}">
                            <i class="ri-file-text-line"></i>
                            <span>Landing Pages</span>
                        </a>
                    </li>
                @endcan

                @can('sitemap', \App\Models\General::class)
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('admin/sitemap*') ? 'active' : '' }}"
                            href="{{ route('admin.sitemap.sitemap-robots.index') }}">
                            <i class="ri-links-line"></i>
                            <span>Sitemap & Robots</span>
                        </a>
                    </li>
                @endcan --}}

                @can('read', \App\Models\ClientRequirement::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.client-requirements.index') }}"
                            class="nav-link menu-link {{ request()->is('admin/client-requirements*') ? 'active' : '' }}">
                            <i class="ri-file-list-3-line"></i>
                            <span>Client Requirements</span>
                        </a>
                    </li>
                @endcan

                @can('read', \App\Models\ProfileSourced::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.profile-sourced.index') }}"
                            class="nav-link menu-link {{ request()->is('admin/profile-sourced*') ? 'active' : '' }}">
                            <i class="ri-user-line"></i>
                            Profiles </a>
                    </li>
                @endcan

                @can('read', \App\Models\Candidate::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.candidates.index') }}"
                            class="nav-link menu-link {{ request()->is('admin/candidates*') ? 'active' : '' }}">
                            <i class="ri-user-search-line"></i>
                            <span>Candidates</span>
                        </a>
                    </li>
                @endcan

                <li class="nav-item">
                    <a href="{{ route('admin.interview-schedules.index') }}"
                        class="nav-link menu-link {{ request()->is('admin/interview-schedules*') ? 'active' : '' }}">
                        <i class="ri-calendar-check-line"></i>
                        <span>Interview Scheduled List</span>
                    </a>
                </li>

                @can('read', \App\Models\Report::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.reports.index') }}"
                            class="nav-link menu-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                            <i class="ri-bar-chart-box-line"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                @endcan

                @can('read', \App\Models\Report::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.contract-reports.index') }}"
                            class="nav-link menu-link {{ request()->is('admin/contract-reports*') ? 'active' : '' }}">
                            <i class="ri-file-list-3-line"></i>
                            <span>Contract Report</span>
                        </a>
                    </li>
                @endcan

                @can('read', \App\Models\Revenue::class)
                    <li class="nav-item">
                        <a href="{{ route('admin.revenues.index') }}"
                            class="nav-link menu-link {{ request()->is('admin/revenues*') ? 'active' : '' }}">
                            <i class="ri-money-rupee-circle-line"></i>
                            <span>Revenue</span>
                        </a>
                    </li>
                @endcan

                @if (auth()->user()->can('read', \App\Models\Client::class) ||
                        auth()->user()->can('read', \App\Models\ClientJobRole::class) ||
                        auth()->user()->can('read', \App\Models\Recruiter::class) ||
                        auth()->user()->can('read', \App\Models\ProfileSourced::class) ||
                        auth()->user()->can('read', \App\Models\JobRole::class) ||
                        auth()->user()->can('read', \App\Models\Mode::class) ||
                        auth()->user()->can('read', \App\Models\Employee::class) ||
                        auth()->user()->can('read', \App\Models\InterviewMode::class) ||
                        auth()->user()->can('read', \App\Models\Target::class) ||
                        ((int) auth()->id() === 1 && auth()->user()->can('read', \App\Models\Billing::class)) ||
                        auth()->user()->can('read', \App\Models\InterviewLevel::class) ||
                        auth()->user()->can('read', \App\Models\Location::class) ||
                        auth()->user()->can('read', \App\Models\Division::class))
                    <li class="menu-title"><i class="ri-more-fill"></i> <span>Masters</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarMasters" data-bs-toggle="collapse"
                            role="button" aria-expanded="{{ request()->is('admin/masters*') ? 'true' : 'false' }}"
                            aria-controls="sidebarMasters">
                            <i class="ri-database-2-line"></i> <span>Masters</span>
                        </a>
                        <div class="collapse menu-dropdown {{ request()->is('admin/masters*') ? 'show' : '' }}"
                            id="sidebarMasters">
                            <ul class="nav nav-sm flex-column">
                                @can('read', \App\Models\Client::class)
                                    <li class="nav-item">
                                        <a href="{{ route('admin.clients.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/clients*') ? 'active' : '' }}">
                                            Clients </a>
                                    </li>
                                @endcan
                                @can('read', \App\Models\ClientJobRole::class)
                                    <li class="nav-item">
                                        <a href="{{ route('admin.client-job-roles.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/client-job-roles*') ? 'active' : '' }}">
                                            Client Job Roles </a>
                                    </li>
                                @endcan
                                @can('read', \App\Models\Employee::class)
                                <li class="nav-item">
                                    <a href="{{ route('admin.employees.index') }}"
                                        class="nav-link {{ request()->is('admin/masters/employees*') ? 'active' : '' }}">
                                        Employees </a>
                                </li>
                                @endcan
                                @can('read', \App\Models\Recruiter::class)
                                    <li class="nav-item">
                                        <a href="{{ route('admin.recruiters.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/recruiters*') ? 'active' : '' }}">
                                            Recruiters </a>
                                    </li>
                                @endcan
                                @can('read', \App\Models\JobRole::class)
                                    <li class="nav-item">
                                        <a href="{{ route('admin.job-roles.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/job-roles*') ? 'active' : '' }}">
                                            Job Roles </a>
                                    </li>
                                @endcan
                                @can('read', \App\Models\Mode::class)
                                    <li class="nav-item">
                                        <a href="{{ route('admin.modes.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/modes*') ? 'active' : '' }}">
                                            Modes </a>
                                    </li>
                                @endcan
                                @if ((int) auth()->id() === 1 && auth()->user()->can('read', \App\Models\Billing::class))
                                    <li class="nav-item">
                                        <a href="{{ route('admin.billings.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/billings*') ? 'active' : '' }}">
                                            Billings </a>
                                    </li>
                                @endif
                                @can('read', \App\Models\InterviewLevel::class)
                                    <li class="nav-item">
                                        <a href="{{ route('admin.interview-levels.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/interview-levels*') ? 'active' : '' }}">
                                            Level of Interview </a>
                                    </li>
                                @endcan
                                @can('read', \App\Models\InterviewMode::class)
                                <li class="nav-item">
                                    <a href="{{ route('admin.interview-modes.index') }}"
                                        class="nav-link {{ request()->is('admin/masters/interview-modes*') ? 'active' : '' }}">
                                        Interview Mode </a>
                                </li>
                                @endcan
                                @can('read', \App\Models\Target::class)
                                    <li class="nav-item">
                                        <a href="{{ route('admin.targets.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/targets*') ? 'active' : '' }}">
                                            Targets </a>
                                    </li>
                                @endcan
                                @can('read', \App\Models\Location::class)
                                    <li class="nav-item">
                                        <a href="{{ route('admin.locations.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/locations*') ? 'active' : '' }}">
                                            Locations </a>
                                    </li>
                                @endcan
                                @can('read', \App\Models\Division::class)
                                    <li class="nav-item">
                                        <a href="{{ route('admin.divisions.index') }}"
                                            class="nav-link {{ request()->is('admin/masters/divisions*') ? 'active' : '' }}">
                                            Divisions </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->can('read', \App\Models\User::class) || auth()->user()->can('read', \App\Models\Role::class))
                    <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-settings">Roles &
                            Permission</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarRoles" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->is('roles*') ? 'true' : 'false' }}"
                            aria-controls="sidebarAuth">
                            <i class="ri-account-circle-line"></i> <span data-key="t-authentication">Roles</span>
                        </a>
                        <div class="collapse menu-dropdown {{ request()->is('admin/roles*', 'admin/users*') ? 'show' : '' }}"
                            id="sidebarRoles">
                            <ul class="nav nav-sm flex-column">
                                @can('read', \App\Models\Role::class)
                                    <li class="nav-item ">
                                        <a href="{{ route('admin.role.index') }}"
                                            class="nav-link menu-link {{ request()->is('admin/roles/role*', 'roles*') ? 'active' : '' }}"
                                            data-key="t-settings"> Role </a>
                                    </li>
                                @endcan
                                @can('read', \App\Models\User::class)
                                    <li class="nav-item ">
                                        <a href="{{ route('admin.user.index') }}"
                                            class="nav-link {{ request()->is('admin/users/user*', 'users*') ? 'active' : '' }}"
                                            data-key="t-settings"> Users </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->can('read', \App\Models\Pages::class) ||
                        auth()->user()->can('sitemap', \App\Models\General::class))
                    <li class="menu-title"><i class="ri-more-fill"></i> <span>SEO</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarSeo" data-bs-toggle="collapse"
                            role="button" aria-expanded="{{ request()->is('admin/seo*') ? 'true' : 'false' }}"
                            aria-controls="sidebarSeo">
                            <i class="ri-global-line"></i> <span>SEO</span>
                        </a>
                        <div class="collapse menu-dropdown {{ request()->is('admin/seo*') ? 'show' : '' }}"
                            id="sidebarSeo">
                            <ul class="nav nav-sm flex-column">
                                @can('read', \App\Models\Pages::class)
                                    <li class="nav-item">
                                        <a class="nav-link menu-link {{ request()->is('admin/pages*') ? 'active' : '' }}"
                                            href="{{ route('admin.pages.index') }}">
                                            <i class="ri-file-text-line"></i>
                                            <span>Landing Pages</span>
                                        </a>
                                    </li>
                                @endcan
                                @can('sitemap', \App\Models\General::class)
                                    <li class="nav-item">
                                        <a class="nav-link menu-link {{ request()->is('admin/sitemap*') ? 'active' : '' }}"
                                            href="{{ route('admin.sitemap.sitemap-robots.index') }}">
                                            <i class="ri-links-line"></i>
                                            <span>Sitemap & Robots</span>
                                        </a>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endif

                @if (auth()->user()->can('generalSetting', \App\Models\Setting::class) ||
                        auth()->user()->can('emailSetting', \App\Models\Setting::class) ||
                        auth()->user()->can('socialSetting', \App\Models\Setting::class))
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('admin/settings') ? 'active' : '' }}"
                            href="{{ route('admin.settings.index') }}">
                            <i class="ri-settings-2-line"></i> <span data-key="t-settings">Settings</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>
