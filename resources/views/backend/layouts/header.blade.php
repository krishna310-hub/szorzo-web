<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <style>
                    .horizontal-logo .logo-lg img {
                        height: 60px;
                        object-fit: cover;
                    }
                </style>
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="index.html" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/glow-unlock-favicon.png')}}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/glow-unlock-favicon.png')}}" alt="" height="17">
                        </span>
                    </a>

                    <a href="index.html" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/glow-unlock-favicon.png')}}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/glow-unlock-favicon.png')}}" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <!-- App Search-->
                <form class="app-search d-none d-md-block">
                    <div class="position-relative">
                        <input type="text" class="form-control" placeholder="Search..." autocomplete="off" id="search-options" value="">
                        <span class="mdi mdi-magnify search-widget-icon"></span>
                        <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none" id="search-close-options"></span>
                    </div>
                    {{-- <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown">
                        <div data-simplebar style="max-height: 320px;">
                            <!-- item-->
                            <div class="dropdown-header">
                                <h6 class="text-overflow text-muted mb-0 text-uppercase">Recent Searches</h6>
                            </div>

                            <div class="dropdown-item bg-transparent text-wrap">
                                <a href="index.html" class="btn btn-soft-secondary btn-sm rounded-pill">how to setup <i class="mdi mdi-magnify ms-1"></i></a>
                                <a href="index.html" class="btn btn-soft-secondary btn-sm rounded-pill">buttons <i class="mdi mdi-magnify ms-1"></i></a>
                            </div>
                            <!-- item-->
                            <div class="dropdown-header mt-2">
                                <h6 class="text-overflow text-muted mb-1 text-uppercase">Pages</h6>
                            </div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="ri-bubble-chart-line align-middle fs-18 text-muted me-2"></i>
                                <span>Analytics Dashboard</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="ri-lifebuoy-line align-middle fs-18 text-muted me-2"></i>
                                <span>Help Center</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="ri-user-settings-line align-middle fs-18 text-muted me-2"></i>
                                <span>My account settings</span>
                            </a>

                            <!-- item-->
                            <div class="dropdown-header mt-2">
                                <h6 class="text-overflow text-muted mb-2 text-uppercase">Members</h6>
                            </div>

                            <div class="notification-list">
                                <!-- item -->
                                <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                    <div class="d-flex">
                                        <img src="{{ asset('admin/images/users/avatar-2.jpg')}}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="m-0">Angela Bernier</h6>
                                            <span class="fs-11 mb-0 text-muted">Manager</span>
                                        </div>
                                    </div>
                                </a>
                                <!-- item -->
                                <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                    <div class="d-flex">
                                        <img src="{{ asset('admin/images/users/avatar-3.jpg')}}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="m-0">David Grasso</h6>
                                            <span class="fs-11 mb-0 text-muted">Web Designer</span>
                                        </div>
                                    </div>
                                </a>
                                <!-- item -->
                                <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                    <div class="d-flex">
                                        <img src="{{ asset('admin/images/users/avatar-5.jpg')}}" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-grow-1">
                                            <h6 class="m-0">Mike Bunch</h6>
                                            <span class="fs-11 mb-0 text-muted">React Developer</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="text-center pt-3 pb-1">
                            <a href="pages-search-results.html" class="btn btn-primary btn-sm">View All Results <i class="ri-arrow-right-line ms-1"></i></a>
                        </div>
                    </div> --}}
                </form>
            </div>

            <div class="d-flex align-items-center">

                <div class="dropdown d-md-none topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-search fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                        <form class="p-3">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                    <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                @php
                    $rawNotifs = auth()->check() ? auth()->user()->notifications()->latest()->take(30)->get() : collect();
                    $headerNotifications = $rawNotifs->filter(function ($notif) {
                        $data = $notif->data ?? [];
                        if (($data['contractable_type'] ?? '') === 'employee') {
                            $empId = $data['contractable_id'] ?? null;
                            if ($empId) {
                                $emp = \App\Models\Employee::find($empId);
                                if (! $emp || ! $emp->status || ($emp->relieving_date && \Carbon\Carbon::parse($emp->relieving_date)->lte(today()))) {
                                    return false;
                                }
                            }
                        }
                        return true;
                    })->values()->take(15);
                    $headerUnreadCount = $headerNotifications->whereNull('read_at')->count();
                @endphp
                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle position-relative" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                        <i class='bx bx-bell fs-22'></i>
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger header-notification-badge {{ $headerUnreadCount > 0 ? '' : 'd-none' }}">
                            {{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}<span class="visually-hidden">unread notifications</span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">

                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white"> Notifications </h6>
                                    </div>
                                    <div class="col-auto d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-body fs-12 header-unread-counter">{{ $headerUnreadCount }} New</span>
                                        @if($headerUnreadCount > 0)
                                        <button type="button" class="btn btn-sm btn-link text-white text-decoration-none p-0 fs-11" id="mark-all-notifications-read" title="Mark all as read">
                                            <i class="ri-check-double-line"></i> Mark all read
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="py-2 ps-2 pe-1" id="notificationItemsContent">
                            <div data-simplebar style="max-height: 350px;" class="pe-2" id="notification-scroll-container">
                                @forelse($headerNotifications as $notif)
                                    @php
                                        $data = $notif->data ?? [];
                                        $isUnread = $notif->read_at === null;
                                        $actionUrl = $data['action_url'] ?? 'javascript:void(0);';
                                        $status = $data['status'] ?? '';
                                    @endphp
                                    <div class="text-reset notification-item d-block dropdown-item position-relative py-2 px-3 border-bottom {{ $isUnread ? 'bg-light-subtle' : '' }}" id="notif-item-{{ $notif->id }}">
                                        <div class="d-flex align-items-start">
                                            <div class="avatar-xs me-3 flex-shrink-0 mt-1">
                                                <span class="avatar-title {{ $status === 'expired' || $status === 'today' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning' }} rounded-circle fs-16">
                                                    <i class="bx bx-calendar-event"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <a href="{{ $actionUrl }}" class="stretched-link text-decoration-none mark-single-read" data-id="{{ $notif->id }}">
                                                    <h6 class="mt-0 mb-1 fs-13 {{ $isUnread ? 'fw-bold text-dark' : 'fw-semibold text-muted' }}">
                                                        {{ $data['title'] ?? 'Notification' }}
                                                    </h6>
                                                </a>
                                                <p class="mb-1 fs-12 text-muted lh-sm">{{ $data['message'] ?? '' }}</p>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="fs-11 text-muted"><i class="mdi mdi-clock-outline me-1"></i>{{ $notif->created_at->diffForHumans() }}</span>
                                                    @if($isUnread)
                                                        <span class="badge bg-primary-subtle text-primary fs-10 unread-pill">Unread</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center p-4 text-muted empty-notif-msg">
                                        <i class="bx bx-bell-off fs-36 text-muted mb-2 d-block"></i>
                                        <p class="mb-0 fs-13">No notifications right now</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const markAllBtn = document.getElementById('mark-all-notifications-read');
                    if (markAllBtn) {
                        markAllBtn.addEventListener('click', function (e) {
                            e.preventDefault();
                            fetch("{{ route('admin.notifications.mark-all-read') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.success) {
                                    document.querySelectorAll('.header-notification-badge').forEach(el => {
                                        el.textContent = '0';
                                        el.classList.add('d-none');
                                    });
                                    const counter = document.querySelector('.header-unread-counter');
                                    if (counter) counter.textContent = '0 New';
                                    document.querySelectorAll('.unread-pill').forEach(el => el.remove());
                                    document.querySelectorAll('.notification-item').forEach(el => el.classList.remove('bg-light-subtle'));
                                    markAllBtn.remove();
                                }
                            })
                            .catch(err => console.error('Error marking all as read:', err));
                        });
                    }

                    document.querySelectorAll('.mark-single-read').forEach(el => {
                        el.addEventListener('click', function () {
                            const id = this.getAttribute('data-id');
                            if (!id) return;
                            fetch("{{ url('admin/notifications') }}/" + id + "/mark-read", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.success) {
                                    const badge = document.querySelector('.header-notification-badge');
                                    if (badge) {
                                        badge.textContent = data.unread_count;
                                        if (data.unread_count <= 0) badge.classList.add('d-none');
                                    }
                                    const counter = document.querySelector('.header-unread-counter');
                                    if (counter) counter.textContent = data.unread_count + ' New';
                                    const parent = document.getElementById('notif-item-' + id);
                                    if (parent) {
                                        parent.classList.remove('bg-light-subtle');
                                        const pill = parent.querySelector('.unread-pill');
                                        if (pill) pill.remove();
                                    }
                                }
                            })
                            .catch(err => console.error('Error marking single as read:', err));
                        });
                    });
                });
                </script>

                @auth
                    @include('backend.layouts.attendance-navbar-widget')
                @endauth

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" src="{{ Auth::user()?->profile_picture ? asset(Auth::user()->profile_picture) : asset('admin/images/users/user-dummy-img.jpg') }}" alt="Profile Picture">
                            <span class="text-start ms-xl-2">
                                @guest
                                @else
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ Auth::user()->name }}</span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">{{ Auth::user()->role->name }}</span>
                                @endguest
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome {{ Auth::user()->name }}!</h6>
                        <a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
                        @if(Auth::user()?->isSuperAdmin())
                        <a class="dropdown-item" href="{{ route('admin.payslip.index') }}"><i class="mdi mdi-file-document-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Payslip</span></a>
                        @endif
                        {{-- <a class="dropdown-item" href="apps-chat.html"><i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a> --}}
                        {{-- <a class="dropdown-item" href="apps-tasks-kanban.html"><i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Taskboard</span></a> --}}
                        {{-- <a class="dropdown-item" href="pages-faqs.html"><i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Help</span></a> --}}
                        {{-- <div class="dropdown-divider"></div> --}}
                        {{-- <a class="dropdown-item" href="pages-profile.html"><i class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Balance : <b>$5971.67</b></span></a> --}}
                        <a class="dropdown-item" href="{{ route('admin.settings.index','general') }}"><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Settings</span></a>
                        <a class="dropdown-item" href="{{ route('admin.lock.screen') }}"><i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock screen</span></a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                                <span class="align-middle" data-key="t-logout">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>