@php
    $user = auth()->user();
    $isSuperAdmin = $user && (
        $user->id === 1 ||
        ($user->role && str_contains(strtolower(str_replace('_', '-', (string)$user->role->access_level)), 'super-admin'))
    );
    $isAdmin = auth()->check() && (
        $isSuperAdmin ||
        auth()->user()->can('edit', \App\Models\Attendance::class) ||
        auth()->user()->can('create', \App\Models\Attendance::class)
    );
    // Personal timer is only shown for other roles (NOT Super Admin)
    $showPersonalTimer = $user && !$isSuperAdmin;

    $todayAtt = $showPersonalTimer ? $user->todayAttendance() : null;
    $initialTimerStatus = $todayAtt?->timer_status ?? 'not_started';
    $initialWorkSecs = (int) ($todayAtt?->current_work_seconds ?? 0);
    $initialBreakSecs = (int) ($todayAtt?->current_break_seconds ?? 0);
    $empObj = $showPersonalTimer ? $user->linkedEmployee() : null;
    $initialEmpName = $empObj?->employee_name ?? ($user?->name ?? 'My Attendance');
    $initialEmpNo = $empObj?->employee_no ?? '—';
    $initialCheckIn = $todayAtt?->check_in ? substr((string)$todayAtt->check_in, 0, 5) : '—';
    $initialCheckOut = $todayAtt?->check_out ? substr((string)$todayAtt->check_out, 0, 5) : ($initialTimerStatus === 'running' ? 'Active' : '—');

    $formatSecs = function($s) {
        $s = max(0, (int)$s);
        return sprintf('%02d:%02d:%02d', intdiv($s, 3600), intdiv($s % 3600, 60), $s % 60);
    };

    $initialClockText = $formatSecs($initialWorkSecs);
    $initialDotClass = 'att-pulse-stopped';
    $initialBadgeClass = 'bg-light text-muted border';
    $initialBadgeText = 'Not Started';

    if ($initialTimerStatus === 'running') {
        $initialDotClass = 'att-pulse-running';
        $initialBadgeClass = 'bg-success-subtle text-success border border-success';
        $initialBadgeText = 'Working';
    } elseif ($initialTimerStatus === 'on_break') {
        $initialDotClass = 'att-pulse-break';
        $initialBadgeClass = 'bg-warning-subtle text-warning border border-warning';
        $initialBadgeText = 'Break (' . $formatSecs($initialBreakSecs) . ')';
    } elseif ($initialTimerStatus === 'on_lunch') {
        $initialDotClass = 'att-pulse-lunch';
        $initialBadgeClass = 'bg-danger-subtle text-danger border border-danger';
        $initialBadgeText = 'Lunch (' . $formatSecs($initialBreakSecs) . ')';
    } elseif ($initialTimerStatus === 'completed') {
        $initialBadgeClass = 'bg-info-subtle text-info border border-info';
        $initialBadgeText = 'Day Closed';
    }
@endphp

<!-- Attendance Navbar Widget -->
<div class="header-item d-flex align-items-center ms-1 ms-md-2" id="attendance-navbar-widget">
    <style>
        .att-pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .att-pulse-running {
            background-color: #0ab39c;
            box-shadow: 0 0 0 0 rgba(10, 179, 156, 0.7);
            animation: attPulse 1.8s infinite;
        }
        .att-pulse-break {
            background-color: #f7b84b;
            box-shadow: 0 0 0 0 rgba(247, 184, 75, 0.7);
            animation: attPulse 1.8s infinite;
        }
        .att-pulse-lunch {
            background-color: #f06548;
            box-shadow: 0 0 0 0 rgba(240, 101, 72, 0.7);
            animation: attPulse 1.8s infinite;
        }
        .att-pulse-stopped {
            background-color: #878a99;
        }
        @keyframes attPulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(10, 179, 156, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(10, 179, 156, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(10, 179, 156, 0); }
        }
        .att-timer-bar {
            background: rgba(var(--vz-light-rgb), 0.7);
            border: 1px solid var(--vz-border-color);
            border-radius: 30px;
            padding: 3px 8px 3px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .att-clock-text {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
            color: var(--vz-heading-color);
        }
        .att-dropdown-menu {
            min-width: 320px;
            padding: 12px;
            border-radius: 10px;
        }

        /* Auto & High z-index for Admin Employee Attendance Modal */
        #adminEmpAttendanceModal {
            z-index: 1065 !important;
        }
        #adminEmpAttendanceModal .modal-dialog {
            z-index: 1070 !important;
            margin-top: 50px;
        }
        .modal-backdrop {
            z-index: 1055 !important;
        }
    </style>

    @if($showPersonalTimer)
    <!-- Main Live Timer Pill for Non-Admin Roles -->
    <div class="att-timer-bar shadow-sm">
        <!-- Live Dot & Clock Display with Dropdown trigger -->
        <div class="dropdown d-inline-block">
            <a href="javascript:void(0);" class="text-reset text-decoration-none d-flex align-items-center" id="attTimerDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Click for attendance summary">
                <span id="attPulseDot" class="att-pulse-dot {{ $initialDotClass }}"></span>
                <span id="attClockDisplay" class="att-clock-text">{{ $initialClockText }}</span>
                <span id="attStatusBadge" class="badge ms-1 fs-11 py-1 {{ $initialBadgeClass }}">{{ $initialBadgeText }}</span>
                <i class="mdi mdi-chevron-down ms-1 text-muted fs-12"></i>
            </a>

            <!-- Dropdown Details Popover -->
            <div class="dropdown-menu dropdown-menu-end shadow-lg att-dropdown-menu" aria-labelledby="attTimerDropdownBtn">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
                    <div>
                        <h6 class="mb-0 fs-13 fw-bold" id="attDropEmpName">{{ $initialEmpName }}</h6>
                        <small class="text-muted fs-11" id="attDropEmpId">Employee ID: {{ $initialEmpNo }}</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary" id="attDropDate">{{ date('d M Y') }}</span>
                </div>

                <div class="row g-2 mb-2 text-center fs-12">
                    <div class="col-6">
                        <div class="p-2 border rounded bg-light-subtle">
                            <span class="text-muted d-block fs-11">Check In</span>
                            <strong id="attDropCheckIn" class="text-success">{{ $initialCheckIn }}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-light-subtle">
                            <span class="text-muted d-block fs-11">Check Out</span>
                            <strong id="attDropCheckOut" class="text-danger">{{ $initialCheckOut }}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-light-subtle">
                            <span class="text-muted d-block fs-11">Total Work</span>
                            <strong id="attDropWorkTime" class="text-primary">{{ $formatSecs($initialWorkSecs) }}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 border rounded bg-light-subtle">
                            <span class="text-muted d-block fs-11">Total Break</span>
                            <strong id="attDropBreakTime" class="text-warning">{{ $formatSecs($initialBreakSecs) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Break Log List -->
                <div id="attBreakLogSection" class="mb-2 {{ ($todayAtt && !empty($todayAtt->breaks)) ? '' : 'd-none' }}">
                    <span class="text-muted fs-11 fw-semibold text-uppercase">Today's Breaks:</span>
                    <ul class="list-unstyled mb-0 mt-1 fs-11" id="attBreakLogList" style="max-height: 90px; overflow-y: auto;">
                        @if($todayAtt && !empty($todayAtt->breaks))
                            @foreach($todayAtt->breaks as $b)
                                @php
                                    $isLunch = ($b['type'] ?? '') === 'lunch';
                                    $tLabel = $isLunch ? 'Lunch' : 'Break';
                                    $icon = $isLunch ? 'mdi-food text-danger' : 'mdi-coffee text-warning';
                                @endphp
                                <li class="d-flex justify-content-between py-1 border-bottom border-light">
                                    <span><i class="mdi {{ $icon }} me-1"></i>{{ $tLabel }}: {{ $b['start'] ?? '' }} – {{ $b['end'] ?? '' }}</span>
                                    <span class="text-muted">{{ $b['minutes'] ?? round(($b['seconds'] ?? 0) / 60) }}m</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Dynamic Action Buttons Group for Non-Admin Roles -->
        <div class="d-flex align-items-center gap-1" id="attActionButtons">
            <!-- 1. Start Button (Shown when not started) -->
            <button type="button" class="btn btn-sm btn-success px-2 py-1 fs-12 {{ $initialTimerStatus === 'not_started' ? '' : 'd-none' }}" id="btnAttStart" onclick="attendanceTimer.start()" title="Start Today's Attendance">
                <i class="mdi mdi-play me-1"></i><span>Start</span>
            </button>

            <!-- 2. Break Dropdown (Shown when running) -->
            <div class="btn-group btn-group-sm {{ $initialTimerStatus === 'running' ? '' : 'd-none' }}" id="btnGroupAttBreak">
                <button type="button" class="btn btn-warning px-2 py-1 fs-12 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Take a break">
                    <i class="mdi mdi-pause me-1"></i><span>Break</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center" href="javascript:void(0);" onclick="attendanceTimer.pause('break')">
                            <i class="mdi mdi-coffee text-warning fs-16 me-2"></i>
                            <div>
                                <div class="fw-semibold">Tea / Short Break</div>
                                <small class="text-muted">Pause timer for a quick break</small>
                            </div>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center" href="javascript:void(0);" onclick="attendanceTimer.pause('lunch')">
                            <i class="mdi mdi-food text-danger fs-16 me-2"></i>
                            <div>
                                <div class="fw-semibold">Lunch Break</div>
                                <small class="text-muted">Pause timer for lunch</small>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- 3. Restart / Resume Button (Shown when on break/lunch) -->
            <button type="button" class="btn btn-sm btn-success px-2 py-1 fs-12 {{ ($initialTimerStatus === 'on_break' || $initialTimerStatus === 'on_lunch') ? '' : 'd-none' }}" id="btnAttResume" onclick="attendanceTimer.resume()" title="Resume Working">
                <i class="mdi mdi-play me-1"></i><span>Resume</span>
            </button>

            <!-- 4. End Day / Stop Button (Shown when running or on break) -->
            <button type="button" class="btn btn-sm btn-danger px-2 py-1 fs-12 {{ ($initialTimerStatus === 'running' || $initialTimerStatus === 'on_break' || $initialTimerStatus === 'on_lunch') ? '' : 'd-none' }}" id="btnAttStop" onclick="attendanceTimer.stopDay()" title="End of the day close time">
                <i class="mdi mdi-stop me-1"></i><span>Stop</span>
            </button>

            <!-- 5. Completed Restart (Shown when completed) -->
            <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1 fs-12 {{ $initialTimerStatus === 'completed' ? '' : 'd-none' }}" id="btnAttRestartCompleted" onclick="attendanceTimer.start()" title="Continue working today">
                <i class="mdi mdi-refresh me-1"></i><span>Restart</span>
            </button>
        </div>
    </div>
    @endif

    <!-- Admin Quick Button directly in navbar (Available to Super Admin and Admin managers) -->
    @if($isAdmin)
        <button type="button" class="btn btn-sm btn-soft-primary px-3 py-1 ms-1 d-inline-flex align-items-center rounded-pill" onclick="attendanceTimer.openAdminModal()" title="Update Employee Attendance by ID">
            <i class="ri-user-settings-line me-1"></i>
            <span class="fs-12 fw-medium">Emp Attendance</span>
        </button>
    @endif
</div>

<!-- Admin Employee Attendance Management Modal -->
@if($isAdmin)
<div class="modal fade" id="adminEmpAttendanceModal" tabindex="-1" aria-labelledby="adminEmpAttendanceModalLabel" aria-hidden="true" style="z-index: 1065 !important;">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="z-index: 1070 !important;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-user-settings-line fs-20"></i>
                    <div>
                        <h5 class="modal-title text-white mb-0" id="adminEmpAttendanceModalLabel">Update Employee Attendance</h5>
                        <small class="text-white-50">Manage Start Time, End Time, Pause, Restart, and Status based on Employee ID</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Employee Selection Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-7">
                        <label class="form-label fw-bold">Select Employee <span class="text-danger">*</span></label>
                        <select class="form-select" id="adminEmpSelect" onchange="attendanceTimer.onAdminSelectChange()">
                            <option value="">-- Choose Employee (ID / Name) --</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Attendance Date</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="adminEmpDate" value="{{ date('Y-m-d') }}" onchange="attendanceTimer.loadAdminEmployee()">
                            <button class="btn btn-dark" type="button" onclick="attendanceTimer.loadAdminEmployee()">
                                <i class="ri-refresh-line"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Live Status & Quick Action Panel -->
                <div id="adminEmpDetailsCard" class="card border border-primary-subtle bg-light-subtle mb-4 d-none">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 border-bottom pb-2">
                            <div>
                                <h5 class="mb-0 fw-bold" id="adminEmpCardName">—</h5>
                                <span class="badge bg-secondary me-2 fs-11" id="adminEmpCardId">Emp ID: —</span>
                                <span class="text-muted fs-12" id="adminEmpCardDesig">—</span>
                            </div>
                            <div class="text-end">
                                <span class="badge fs-12 py-1 px-2" id="adminEmpCardStatusBadge">Not Started</span>
                                <div class="font-monospace fw-bold fs-15 text-dark mt-1" id="adminEmpCardLiveClock">00:00:00</div>
                            </div>
                        </div>

                        <!-- Admin Quick Action Buttons for Selected Employee -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-success" id="btnAdminEmpStart" onclick="attendanceTimer.adminAction('start')">
                                <i class="mdi mdi-play me-1"></i>Start Timer
                            </button>

                            <button type="button" class="btn btn-sm btn-warning text-dark" id="btnAdminEmpBreak" onclick="attendanceTimer.adminAction('pause', 'break')">
                                <i class="mdi mdi-coffee me-1"></i>Pause (Break)
                            </button>

                            <button type="button" class="btn btn-sm btn-danger" id="btnAdminEmpLunch" onclick="attendanceTimer.adminAction('pause', 'lunch')">
                                <i class="mdi mdi-food me-1"></i>Pause (Lunch)
                            </button>

                            <button type="button" class="btn btn-sm btn-info text-white" id="btnAdminEmpResume" onclick="attendanceTimer.adminAction('resume')">
                                <i class="mdi mdi-refresh me-1"></i>Restart / Resume
                            </button>

                            <button type="button" class="btn btn-sm btn-dark" id="btnAdminEmpStop" onclick="attendanceTimer.adminAction('stop')">
                                <i class="mdi mdi-stop me-1"></i>End Day / Stop
                            </button>
                        </div>

                        <!-- Manual Time & Status Editing Section -->
                        <div class="bg-white p-3 rounded border">
                            <h6 class="fs-13 fw-semibold text-muted text-uppercase mb-3"><i class="mdi mdi-pencil me-1"></i>Manual Time & Attendance Adjustment</h6>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fs-12 mb-1">Check In Time</label>
                                    <input type="time" class="form-control form-control-sm" id="adminEditCheckIn">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-12 mb-1">Check Out Time</label>
                                    <input type="time" class="form-control form-control-sm" id="adminEditCheckOut">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-12 mb-1">Attendance Status</label>
                                    <select class="form-select form-select-sm" id="adminEditStatus">
                                        @foreach(\App\Models\Attendance::STATUSES as $status)
                                            <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fs-12 mb-1">Break Duration (Mins)</label>
                                    <input type="number" min="0" class="form-control form-control-sm" id="adminEditBreakMins" placeholder="e.g. 45">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fs-12 mb-1">Remarks / Note</label>
                                    <input type="text" class="form-control form-control-sm" id="adminEditRemarks" placeholder="Optional remarks...">
                                </div>
                                <div class="col-12 text-end">
                                    <button type="button" class="btn btn-primary btn-sm px-3" onclick="attendanceTimer.saveAdminUpdate()">
                                        <i class="mdi mdi-check me-1"></i>Save Attendance Updates
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="adminEmpEmptyNotice" class="text-center py-4 text-muted">
                    <i class="ri-user-search-line fs-36 text-muted mb-2 d-block"></i>
                    <p class="mb-0">Select an employee from the dropdown above to view or update attendance.</p>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- JavaScript Attendance Engine -->
<script>
window.attendanceTimer = (function() {
    let currentData = null;
    let timerInterval = null;
    let workSecs = {{ $initialWorkSecs }};
    let breakSecs = {{ $initialBreakSecs }};
    let status = '{{ $initialTimerStatus }}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const isPersonalTimerActive = {{ $showPersonalTimer ? 'true' : 'false' }};

    function formatTime(totalSeconds) {
        totalSeconds = Math.max(0, Math.floor(totalSeconds));
        const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');
        return `${hours}:${minutes}:${seconds}`;
    }

    function showNotification(msg, type = 'success') {
        if (window.toastr) {
            toastr[type](msg);
        } else {
            console.log(`[${type.toUpperCase()}] ${msg}`);
        }
    }

    function ensureModalInBody() {
        const modal = document.getElementById('adminEmpAttendanceModal');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    }

    function startTicker() {
        if (!isPersonalTimerActive) return;
        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            const clockEl = document.getElementById('attClockDisplay');
            const badgeEl = document.getElementById('attStatusBadge');
            const workDrop = document.getElementById('attDropWorkTime');
            const breakDrop = document.getElementById('attDropBreakTime');

            if (status === 'running') {
                workSecs++;
                if (clockEl) clockEl.textContent = formatTime(workSecs);
                if (workDrop) workDrop.textContent = formatTime(workSecs);
            } else if (status === 'on_break' || status === 'on_lunch') {
                breakSecs++;
                // Work timer remains intact and visible on the navbar clock
                if (clockEl) clockEl.textContent = formatTime(workSecs);
                if (badgeEl) {
                    const prefix = status === 'on_lunch' ? 'Lunch' : 'Break';
                    badgeEl.textContent = `${prefix} (${formatTime(breakSecs)})`;
                }
                if (breakDrop) breakDrop.textContent = formatTime(breakSecs);
            }
        }, 1000);
    }

    function renderUI(data) {
        if (!isPersonalTimerActive) return;
        if (!data || !data.attendance) return;
        currentData = data;
        const att = data.attendance;
        const emp = data.employee;
        status = att.timer_status || 'not_started';
        workSecs = parseInt(att.current_work_seconds || 0, 10);
        breakSecs = parseInt(att.current_break_seconds || 0, 10);

        // Update Pulse Dot
        const dot = document.getElementById('attPulseDot');
        if (dot) {
            dot.className = 'att-pulse-dot';
            if (status === 'running') dot.classList.add('att-pulse-running');
            else if (status === 'on_break') dot.classList.add('att-pulse-break');
            else if (status === 'on_lunch') dot.classList.add('att-pulse-lunch');
            else dot.classList.add('att-pulse-stopped');
        }

        // Update Clock Display - ALWAYS preserves total work time!
        const clock = document.getElementById('attClockDisplay');
        if (clock) {
            clock.textContent = formatTime(workSecs);
        }

        // Update Status Badge
        const badge = document.getElementById('attStatusBadge');
        if (badge) {
            badge.className = 'badge ms-1 fs-11 py-1';
            if (status === 'running') {
                badge.className += ' bg-success-subtle text-success border border-success';
                badge.textContent = 'Working';
            } else if (status === 'on_break') {
                badge.className += ' bg-warning-subtle text-warning border border-warning';
                badge.textContent = `Break (${formatTime(breakSecs)})`;
            } else if (status === 'on_lunch') {
                badge.className += ' bg-danger-subtle text-danger border border-danger';
                badge.textContent = `Lunch (${formatTime(breakSecs)})`;
            } else if (status === 'completed') {
                badge.className += ' bg-info-subtle text-info border border-info';
                badge.textContent = 'Day Closed';
            } else {
                badge.className += ' bg-light text-muted border';
                badge.textContent = 'Not Started';
            }
        }

        // Update Action Buttons
        const btnStart = document.getElementById('btnAttStart');
        const grpBreak = document.getElementById('btnGroupAttBreak');
        const btnResume = document.getElementById('btnAttResume');
        const btnStop = document.getElementById('btnAttStop');
        const btnRestartDone = document.getElementById('btnAttRestartCompleted');

        if (btnStart) btnStart.classList.add('d-none');
        if (grpBreak) grpBreak.classList.add('d-none');
        if (btnResume) btnResume.classList.add('d-none');
        if (btnStop) btnStop.classList.add('d-none');
        if (btnRestartDone) btnRestartDone.classList.add('d-none');

        if (status === 'not_started') {
            if (btnStart) btnStart.classList.remove('d-none');
        } else if (status === 'running') {
            if (grpBreak) grpBreak.classList.remove('d-none');
            if (btnStop) btnStop.classList.remove('d-none');
        } else if (status === 'on_break' || status === 'on_lunch') {
            if (btnResume) btnResume.classList.remove('d-none');
            if (btnStop) btnStop.classList.remove('d-none');
        } else if (status === 'completed') {
            if (btnRestartDone) btnRestartDone.classList.remove('d-none');
        }

        // Update Dropdown Details
        const nameEl = document.getElementById('attDropEmpName');
        if (nameEl && emp) nameEl.textContent = emp.employee_name || 'My Attendance';

        const idEl = document.getElementById('attDropEmpId');
        if (idEl && emp) idEl.textContent = `Employee ID: ${emp.employee_no || '—'}`;

        const inEl = document.getElementById('attDropCheckIn');
        if (inEl) inEl.textContent = att.check_in ? att.check_in.substring(0, 5) : '—';

        const outEl = document.getElementById('attDropCheckOut');
        if (outEl) outEl.textContent = att.check_out ? att.check_out.substring(0, 5) : (status === 'running' ? 'Active' : '—');

        const workDrop = document.getElementById('attDropWorkTime');
        if (workDrop) workDrop.textContent = formatTime(workSecs);

        const breakDrop = document.getElementById('attDropBreakTime');
        if (breakDrop) breakDrop.textContent = formatTime(breakSecs);

        // Break Logs
        const breakSec = document.getElementById('attBreakLogSection');
        const breakList = document.getElementById('attBreakLogList');
        if (breakSec && breakList) {
            if (att.breaks && Array.isArray(att.breaks) && att.breaks.length > 0) {
                breakSec.classList.remove('d-none');
                breakList.innerHTML = att.breaks.map(b => {
                    const typeLabel = b.type === 'lunch' ? 'Lunch' : 'Break';
                    const icon = b.type === 'lunch' ? 'mdi-food text-danger' : 'mdi-coffee text-warning';
                    return `<li class="d-flex justify-content-between py-1 border-bottom border-light">
                        <span><i class="mdi ${icon} me-1"></i>${typeLabel}: ${b.start || ''} – ${b.end || ''}</span>
                        <span class="text-muted">${b.minutes || Math.round((b.seconds || 0) / 60)}m</span>
                    </li>`;
                }).join('');
            } else {
                breakSec.classList.add('d-none');
                breakList.innerHTML = '';
            }
        }

        startTicker();
    }

    async function fetchStatus() {
        if (!isPersonalTimerActive) return;
        try {
            const res = await fetch("{{ route('admin.attendance.timer.status') }}", {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data && data.success && data.show_timer !== false) {
                renderUI(data);
            }
        } catch (e) {
            console.error('Failed to fetch attendance status:', e);
        }
    }

    async function sendAction(url, bodyData = {}) {
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(bodyData)
            });
            const result = await res.json();
            if (result && result.success) {
                showNotification(result.message || 'Action updated successfully.');
                if (result.data) {
                    renderUI(result.data);
                } else {
                    fetchStatus();
                }
            } else {
                showNotification(result.message || 'Operation failed.', 'error');
            }
        } catch (e) {
            console.error('Attendance action failed:', e);
            showNotification('Error processing request.', 'error');
        }
    }

    // Public User Actions
    function start() {
        sendAction("{{ route('admin.attendance.timer.start') }}");
    }

    function pause(type = 'break') {
        sendAction("{{ route('admin.attendance.timer.pause') }}", { type });
    }

    function resume() {
        sendAction("{{ route('admin.attendance.timer.resume') }}");
    }

    function stopDay() {
        if (confirm("Are you sure you want to end today's attendance (Clock Out)?")) {
            sendAction("{{ route('admin.attendance.timer.stop') }}");
        }
    }

    // Admin Features
    let adminEmployeesList = [];
    let selectedAdminEmp = null;
    let adminTimerTicker = null;
    let adminWorkSecs = 0;

    function openAdminModal() {
        ensureModalInBody();
        if (adminEmployeesList.length === 0) {
            loadAdminEmployeesList();
        }
        const modalEl = document.getElementById('adminEmpAttendanceModal');
        if (modalEl && window.bootstrap) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    async function loadAdminEmployeesList() {
        try {
            const res = await fetch("{{ route('admin.attendance.timer.admin.employees') }}", {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data && data.success) {
                adminEmployeesList = data.employees || [];
                const select = document.getElementById('adminEmpSelect');
                if (select) {
                    select.innerHTML = '<option value="">-- Choose Employee (ID / Name) --</option>' +
                        adminEmployeesList.map(e => `<option value="${e.id}">[${e.employee_no}] ${e.employee_name} (${e.designation})</option>`).join('');
                }
            }
        } catch (e) {
            console.error('Failed to load admin employee list:', e);
        }
    }

    function onAdminSelectChange() {
        const empId = document.getElementById('adminEmpSelect')?.value;
        if (empId) {
            loadAdminEmployee(empId);
        } else {
            document.getElementById('adminEmpDetailsCard')?.classList.add('d-none');
            document.getElementById('adminEmpEmptyNotice')?.classList.remove('d-none');
        }
    }

    async function loadAdminEmployee(empIdentifier = null) {
        const id = empIdentifier || document.getElementById('adminEmpSelect')?.value;
        if (!id) return;

        const date = document.getElementById('adminEmpDate')?.value || '{{ date("Y-m-d") }}';
        try {
            const res = await fetch(`{{ url('admin/attendance-timer/admin/employee') }}/${id}?date=${date}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data && data.success) {
                renderAdminEmployeeCard(data);
            } else {
                showNotification(data.message || 'Failed to load employee attendance', 'error');
            }
        } catch (e) {
            console.error('Failed loading admin employee data:', e);
        }
    }

    function renderAdminEmployeeCard(data) {
        selectedAdminEmp = data;
        const emp = data.employee;
        const att = data.attendance;

        document.getElementById('adminEmpDetailsCard')?.classList.remove('d-none');
        document.getElementById('adminEmpEmptyNotice')?.classList.add('d-none');

        document.getElementById('adminEmpCardName').textContent = emp.employee_name;
        document.getElementById('adminEmpCardId').textContent = `Emp ID: ${emp.employee_no}`;
        document.getElementById('adminEmpCardDesig').textContent = emp.designation;

        const badge = document.getElementById('adminEmpCardStatusBadge');
        badge.className = 'badge fs-12 py-1 px-2';
        const tStatus = att.timer_status || 'not_started';
        if (tStatus === 'running') {
            badge.className += ' bg-success text-white';
            badge.textContent = 'Running';
        } else if (tStatus === 'on_break') {
            badge.className += ' bg-warning text-dark';
            badge.textContent = 'On Break';
        } else if (tStatus === 'on_lunch') {
            badge.className += ' bg-danger text-white';
            badge.textContent = 'On Lunch';
        } else if (tStatus === 'completed') {
            badge.className += ' bg-info text-white';
            badge.textContent = 'Completed';
        } else {
            badge.className += ' bg-secondary text-white';
            badge.textContent = 'Not Started';
        }

        // Inputs
        document.getElementById('adminEditCheckIn').value = att.check_in || '';
        document.getElementById('adminEditCheckOut').value = att.check_out || '';
        document.getElementById('adminEditStatus').value = att.status || 'present';
        document.getElementById('adminEditRemarks').value = att.remarks || '';
        document.getElementById('adminEditBreakMins').value = Math.round((att.total_break_seconds || 0) / 60);

        // Live Clock
        adminWorkSecs = parseInt(att.current_work_seconds || 0, 10);
        const clockEl = document.getElementById('adminEmpCardLiveClock');
        if (clockEl) clockEl.textContent = formatTime(adminWorkSecs);

        if (adminTimerTicker) clearInterval(adminTimerTicker);
        if (tStatus === 'running') {
            adminTimerTicker = setInterval(() => {
                adminWorkSecs++;
                if (clockEl) clockEl.textContent = formatTime(adminWorkSecs);
            }, 1000);
        }
    }

    async function adminAction(action, type = null) {
        if (!selectedAdminEmp || !selectedAdminEmp.employee) {
            showNotification('No employee selected.', 'warning');
            return;
        }
        const empId = selectedAdminEmp.employee.id;
        const date = document.getElementById('adminEmpDate')?.value || '{{ date("Y-m-d") }}';

        try {
            const res = await fetch(`{{ url('admin/attendance-timer/admin/employee') }}/${empId}/action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ action, type, date })
            });
            const result = await res.json();
            if (result && result.success) {
                showNotification(result.message || 'Updated successfully.');
                renderAdminEmployeeCard(result.data);
                // If the updated employee happens to be current user, re-sync navbar
                if (currentData && currentData.employee && currentData.employee.id === empId) {
                    renderUI(result.data);
                }
            } else {
                showNotification(result.message || 'Action failed.', 'error');
            }
        } catch (e) {
            console.error('Admin employee action error:', e);
            showNotification('Failed to update employee attendance.', 'error');
        }
    }

    async function saveAdminUpdate() {
        if (!selectedAdminEmp || !selectedAdminEmp.employee) {
            showNotification('No employee selected.', 'warning');
            return;
        }
        const empId = selectedAdminEmp.employee.id;
        const date = document.getElementById('adminEmpDate')?.value || '{{ date("Y-m-d") }}';
        const check_in = document.getElementById('adminEditCheckIn')?.value;
        const check_out = document.getElementById('adminEditCheckOut')?.value;
        const status = document.getElementById('adminEditStatus')?.value;
        const remarks = document.getElementById('adminEditRemarks')?.value;
        const total_break_minutes = document.getElementById('adminEditBreakMins')?.value;

        try {
            const res = await fetch(`{{ url('admin/attendance-timer/admin/employee') }}/${empId}/action`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    action: 'update',
                    date,
                    check_in,
                    check_out,
                    status,
                    remarks,
                    total_break_minutes
                })
            });
            const result = await res.json();
            if (result && result.success) {
                showNotification(result.message || 'Attendance updated successfully.');
                renderAdminEmployeeCard(result.data);
                if (currentData && currentData.employee && currentData.employee.id === empId) {
                    renderUI(result.data);
                }
            } else {
                showNotification(result.message || 'Update failed.', 'error');
            }
        } catch (e) {
            console.error('Failed to save admin update:', e);
            showNotification('Error saving update.', 'error');
        }
    }

    // Auto-init on page load
    document.addEventListener('DOMContentLoaded', function() {
        ensureModalInBody();

        if (isPersonalTimerActive) {
            startTicker();
            fetchStatus();
            // Periodically refresh every 60 seconds to stay in sync with server
            setInterval(fetchStatus, 60000);
        }

        @if($isAdmin)
            const adminModal = document.getElementById('adminEmpAttendanceModal');
            if (adminModal) {
                adminModal.addEventListener('show.bs.modal', function() {
                    ensureModalInBody();
                    if (adminEmployeesList.length === 0) {
                        loadAdminEmployeesList();
                    }
                });
            }
        @endif
    });

    return {
        start,
        pause,
        resume,
        stopDay,
        fetchStatus,
        openAdminModal,
        loadAdminEmployee,
        onAdminSelectChange,
        adminAction,
        saveAdminUpdate
    };
})();
</script>