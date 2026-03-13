<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{route('index')}}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/glow-unlock-favicon.png')}}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/Glow unlock colour.png')}}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{route('index')}}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/glow-unlock-favicon.png')}}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/Glow unlock white.png')}}" alt="" height="70">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user" src="{{ asset('admin/images/users/avatar-1.jpg')}}" alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">Anna Adame</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <!-- item-->
            <h6 class="dropdown-header">Welcome Anna!</h6>
            <a class="dropdown-item" href="pages-profile.html"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
            <a class="dropdown-item" href="apps-chat.html"><i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a>
            <a class="dropdown-item" href="apps-tasks-kanban.html"><i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Taskboard</span></a>
            <a class="dropdown-item" href="pages-faqs.html"><i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Help</span></a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="pages-profile.html"><i class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Balance : <b>$5971.67</b></span></a>
            <a class="dropdown-item" href="pages-profile-settings.html"><span class="badge bg-success-subtle text-success mt-1 float-end">New</span><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Settings</span></a>
            <a class="dropdown-item" href="auth-lockscreen-basic.html"><i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock screen</span></a>
            <a class="dropdown-item" href="auth-logout-basic.html"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a>
        </div>
    </div>
    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                @can('dashboard', \App\Models\General::class)
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('admin/dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                        </a>
                    </li>
                @endcan
                 <!-- end Dashboard Menu -->
                 @can('dashboard', \App\Models\General::class)
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('admin/enquiry*') ? 'active' : '' }}" href="{{ route('admin.enquiry.index') }}">
                            <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Enquiry List</span>
                        </a>
                    </li>
                @endcan
                 <!-- end Dashboard Menu -->

                @if(auth()->user()->can('read', \App\Models\User::class) || auth()->user()->can('read', \App\Models\Role::class))
                    <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-settings">Roles & Permission</span></li>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarRoles" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('roles*') ? 'true' : 'false' }}"
                        aria-controls="sidebarAuth">
                            <i class="ri-account-circle-line"></i> <span data-key="t-authentication">Roles</span>
                        </a>
                        <div class="collapse menu-dropdown {{ request()->is('admin/roles*','admin/users*') ? 'show' : '' }}" id="sidebarRoles">
                            <ul class="nav nav-sm flex-column">
                                @can('read', \App\Models\Role::class)
                                    <li class="nav-item ">
                                        <a href="{{ route('admin.role.index') }}" class="nav-link menu-link {{ request()->is('admin/roles/role*','roles*') ? 'active' : '' }}" data-key="t-settings"> Role </a>
                                    </li>
                                @endcan
                                @can('read', \App\Models\User::class)
                                    <li class="nav-item ">
                                        <a href="{{ route('admin.user.index') }}" class="nav-link {{ request()->is('admin/users/user*','users*') ? 'active' : '' }}" data-key="t-settings"> Users </a>
                                    </li>
                                @endcan
                                {{-- <li class="nav-item">
                                    <a href="apps-chat.html" class="nav-link" data-key="t-settings"> Permission </a>
                                </li> --}}
                            </ul>
                        </div>
                    </li>
                @endif
                @if(auth()->user()->can('generalSetting', \App\Models\Setting::class) || auth()->user()->can('emailSetting', \App\Models\Setting::class) ||
                auth()->user()->can('socialSetting', \App\Models\Setting::class))
                    {{-- <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-settings">Settings</span></li> --}}
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ request()->is('admin/settings') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                            <i class="ri-settings-2-line"></i> <span data-key="t-settings">Settings</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
