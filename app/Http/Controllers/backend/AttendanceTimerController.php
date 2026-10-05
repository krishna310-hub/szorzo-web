<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceTimerController extends Controller
{
    /**
     * Get timer status for the authenticated user for today.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if ($this->isSuperAdminUser($user)) {
            return response()->json([
                'success' => true,
                'show_timer' => false,
                'is_admin' => true,
                'is_super_admin' => true,
                'server_time' => now()->toIso8601String(),
            ]);
        }

        $employee = $user->getOrCreateEmployee();
        $date = $request->query('date', now()->toDateString());
        $attendance = $this->resolveAttendance($user, $employee, $date);

        $payload = $this->formatAttendancePayload($attendance, $user, $employee, $date);
        $payload['show_timer'] = true;
        $payload['is_super_admin'] = false;
        return response()->json($payload);
    }

    /**
     * Start / Clock in attendance timer for the authenticated user.
     */
    public function start(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($this->isSuperAdminUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Super Admin does not record personal attendance. Please use Emp Attendance to manage employee attendance.',
            ], 403);
        }

        $employee = $user->getOrCreateEmployee();
        $date = now()->toDateString();
        $attendance = $this->resolveAttendance($user, $employee, $date, true);

        // If attendance was already completed today, restarting it reopens check_out
        if ($attendance->timer_status === Attendance::TIMER_COMPLETED) {
            $attendance->check_out = null;
        }

        if (!$attendance->check_in) {
            $attendance->check_in = now()->format('H:i:s');
        }

        if (!$attendance->status || $attendance->status === 'absent') {
            $attendance->status = 'present';
        }

        $attendance->timer_status = Attendance::TIMER_RUNNING;
        $attendance->timer_started_at = now();
        $attendance->break_started_at = null;
        $attendance->marked_by = $user->id;
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Attendance timer started successfully.',
            'data' => $this->formatAttendancePayload($attendance, $user, $employee, $date),
        ]);
    }

    /**
     * Pause timer for break or lunch.
     */
    public function pause(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($this->isSuperAdminUser($user)) {
            return response()->json(['success' => false, 'message' => 'Super Admin does not record personal attendance.'], 403);
        }

        $employee = $user->getOrCreateEmployee();
        $date = now()->toDateString();
        $attendance = $this->resolveAttendance($user, $employee, $date);

        if (!$attendance || $attendance->timer_status !== Attendance::TIMER_RUNNING) {
            return response()->json([
                'success' => false,
                'message' => 'Timer is not currently running.',
            ], 422);
        }

        $type = $request->input('type', 'break'); // 'break' or 'lunch'
        $newStatus = ($type === 'lunch') ? Attendance::TIMER_ON_LUNCH : Attendance::TIMER_ON_BREAK;

        // Accumulate work time from active run
        if ($attendance->timer_started_at) {
            $elapsedWork = max(0, (int) now()->diffInSeconds($attendance->timer_started_at));
            $attendance->total_work_seconds = (int) $attendance->total_work_seconds + $elapsedWork;
        }

        $attendance->timer_status = $newStatus;
        $attendance->break_started_at = now();
        $attendance->timer_started_at = null;
        $attendance->save();

        $typeName = ($type === 'lunch') ? 'Lunch' : 'Break';
        return response()->json([
            'success' => true,
            'message' => "{$typeName} started successfully.",
            'data' => $this->formatAttendancePayload($attendance, $user, $employee, $date),
        ]);
    }

    /**
     * Resume / Restart timer after break or lunch.
     */
    public function resume(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($this->isSuperAdminUser($user)) {
            return response()->json(['success' => false, 'message' => 'Super Admin does not record personal attendance.'], 403);
        }

        $employee = $user->getOrCreateEmployee();
        $date = now()->toDateString();
        $attendance = $this->resolveAttendance($user, $employee, $date);

        if (!$attendance || !in_array($attendance->timer_status, [Attendance::TIMER_ON_BREAK, Attendance::TIMER_ON_LUNCH], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Timer is not currently on break or lunch.',
            ], 422);
        }

        // Accumulate break time
        if ($attendance->break_started_at) {
            $elapsedBreak = max(0, (int) now()->diffInSeconds($attendance->break_started_at));
            $attendance->total_break_seconds = (int) $attendance->total_break_seconds + $elapsedBreak;

            $breaks = is_array($attendance->breaks) ? $attendance->breaks : [];
            $breaks[] = [
                'type' => ($attendance->timer_status === Attendance::TIMER_ON_LUNCH) ? 'lunch' : 'break',
                'start' => $attendance->break_started_at->format('H:i:s'),
                'end' => now()->format('H:i:s'),
                'seconds' => $elapsedBreak,
                'minutes' => (int) round($elapsedBreak / 60),
            ];
            $attendance->breaks = $breaks;
        }

        $attendance->timer_status = Attendance::TIMER_RUNNING;
        $attendance->timer_started_at = now();
        $attendance->break_started_at = null;
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Timer resumed successfully.',
            'data' => $this->formatAttendancePayload($attendance, $user, $employee, $date),
        ]);
    }

    /**
     * Stop timer / End of the day close time update.
     */
    public function stop(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($this->isSuperAdminUser($user)) {
            return response()->json(['success' => false, 'message' => 'Super Admin does not record personal attendance.'], 403);
        }

        $employee = $user->getOrCreateEmployee();
        $date = now()->toDateString();
        $attendance = $this->resolveAttendance($user, $employee, $date);

        if (!$attendance || $attendance->timer_status === Attendance::TIMER_NOT_STARTED) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance has not been started yet today.',
            ], 422);
        }

        // If running, accumulate remaining work seconds
        if ($attendance->timer_status === Attendance::TIMER_RUNNING && $attendance->timer_started_at) {
            $elapsedWork = max(0, (int) now()->diffInSeconds($attendance->timer_started_at));
            $attendance->total_work_seconds = (int) $attendance->total_work_seconds + $elapsedWork;
        }

        // If on break/lunch, accumulate break seconds
        if (in_array($attendance->timer_status, [Attendance::TIMER_ON_BREAK, Attendance::TIMER_ON_LUNCH], true) && $attendance->break_started_at) {
            $elapsedBreak = max(0, (int) now()->diffInSeconds($attendance->break_started_at));
            $attendance->total_break_seconds = (int) $attendance->total_break_seconds + $elapsedBreak;

            $breaks = is_array($attendance->breaks) ? $attendance->breaks : [];
            $breaks[] = [
                'type' => ($attendance->timer_status === Attendance::TIMER_ON_LUNCH) ? 'lunch' : 'break',
                'start' => $attendance->break_started_at->format('H:i:s'),
                'end' => now()->format('H:i:s'),
                'seconds' => $elapsedBreak,
                'minutes' => (int) round($elapsedBreak / 60),
            ];
            $attendance->breaks = $breaks;
        }

        $attendance->timer_status = Attendance::TIMER_COMPLETED;
        $attendance->check_out = now()->format('H:i:s');
        $attendance->timer_started_at = null;
        $attendance->break_started_at = null;
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Day attendance ended and clocked out successfully.',
            'data' => $this->formatAttendancePayload($attendance, $user, $employee, $date),
        ]);
    }

    /**
     * Admin: List all active employees for selection by Employee ID or Name.
     */
    public function adminEmployees(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $employees = Employee::whereNull('deleted_at')
            ->orderBy('employee_no')
            ->orderBy('employee_name')
            ->get(['id', 'employee_no', 'employee_name', 'designation', 'official_mail'])
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'employee_no' => $emp->employee_no ?? ('SZ ' . str_pad($emp->id, 3, '0', STR_PAD_LEFT)),
                    'employee_name' => $emp->employee_name,
                    'designation' => $emp->designation ?? 'Employee',
                    'email' => $emp->official_mail,
                ];
            });

        return response()->json([
            'success' => true,
            'employees' => $employees,
        ]);
    }

    /**
     * Admin: Get attendance and timer status of a specific employee by ID or Employee No.
     */
    public function adminEmployeeStatus(Request $request, $employeeIdentifier): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $employee = $this->findEmployee($employeeIdentifier);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $user = $employee->linkedUser();
        $date = $request->query('date', now()->toDateString());
        $attendance = Attendance::where('employee_id', $employee->id)->whereDate('attendance_date', $date)->first();

        return response()->json($this->formatAttendancePayload($attendance, $user, $employee, $date));
    }

    /**
     * Admin: Execute action (start, pause, resume, stop, manual update) for any employee based on Employee ID.
     */
    public function adminEmployeeAction(Request $request, $employeeIdentifier): JsonResponse
    {
        $adminUser = $request->user();
        $this->authorizeAdmin($adminUser);

        $employee = $this->findEmployee($employeeIdentifier);
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        $user = $employee->linkedUser();
        $date = $request->input('date', now()->toDateString());
        $action = $request->input('action', 'start');

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'attendance_date' => $date,
        ]);

        if ($user && !$attendance->user_id) {
            $attendance->user_id = $user->id;
        }
        $attendance->marked_by = $adminUser->id;

        if ($action === 'start') {
            if (!$attendance->check_in) {
                $attendance->check_in = $request->input('check_in', now()->format('H:i:s'));
            }
            if (!$attendance->status || $attendance->status === 'absent') {
                $attendance->status = 'present';
            }
            if ($attendance->timer_status === Attendance::TIMER_COMPLETED) {
                $attendance->check_out = null;
            }
            $attendance->timer_status = Attendance::TIMER_RUNNING;
            $attendance->timer_started_at = now();
            $attendance->break_started_at = null;
            $attendance->save();

            $msg = "Timer started for {$employee->employee_name} ({$employee->employee_no}).";
        } elseif ($action === 'pause') {
            $type = $request->input('type', 'break');
            if ($attendance->timer_status === Attendance::TIMER_RUNNING && $attendance->timer_started_at) {
                $elapsed = max(0, (int) now()->diffInSeconds($attendance->timer_started_at));
                $attendance->total_work_seconds = (int) $attendance->total_work_seconds + $elapsed;
            }
            $attendance->timer_status = ($type === 'lunch') ? Attendance::TIMER_ON_LUNCH : Attendance::TIMER_ON_BREAK;
            $attendance->break_started_at = now();
            $attendance->timer_started_at = null;
            $attendance->save();

            $typeName = ($type === 'lunch') ? 'Lunch' : 'Break';
            $msg = "{$typeName} activated for {$employee->employee_name} ({$employee->employee_no}).";
        } elseif ($action === 'resume') {
            if (in_array($attendance->timer_status, [Attendance::TIMER_ON_BREAK, Attendance::TIMER_ON_LUNCH], true) && $attendance->break_started_at) {
                $elapsedBreak = max(0, (int) now()->diffInSeconds($attendance->break_started_at));
                $attendance->total_break_seconds = (int) $attendance->total_break_seconds + $elapsedBreak;

                $breaks = is_array($attendance->breaks) ? $attendance->breaks : [];
                $breaks[] = [
                    'type' => ($attendance->timer_status === Attendance::TIMER_ON_LUNCH) ? 'lunch' : 'break',
                    'start' => $attendance->break_started_at->format('H:i:s'),
                    'end' => now()->format('H:i:s'),
                    'seconds' => $elapsedBreak,
                    'minutes' => (int) round($elapsedBreak / 60),
                ];
                $attendance->breaks = $breaks;
            }
            $attendance->timer_status = Attendance::TIMER_RUNNING;
            $attendance->timer_started_at = now();
            $attendance->break_started_at = null;
            $attendance->save();

            $msg = "Timer resumed for {$employee->employee_name} ({$employee->employee_no}).";
        } elseif ($action === 'stop') {
            if ($attendance->timer_status === Attendance::TIMER_RUNNING && $attendance->timer_started_at) {
                $elapsed = max(0, (int) now()->diffInSeconds($attendance->timer_started_at));
                $attendance->total_work_seconds = (int) $attendance->total_work_seconds + $elapsed;
            }
            if (in_array($attendance->timer_status, [Attendance::TIMER_ON_BREAK, Attendance::TIMER_ON_LUNCH], true) && $attendance->break_started_at) {
                $elapsedBreak = max(0, (int) now()->diffInSeconds($attendance->break_started_at));
                $attendance->total_break_seconds = (int) $attendance->total_break_seconds + $elapsedBreak;

                $breaks = is_array($attendance->breaks) ? $attendance->breaks : [];
                $breaks[] = [
                    'type' => ($attendance->timer_status === Attendance::TIMER_ON_LUNCH) ? 'lunch' : 'break',
                    'start' => $attendance->break_started_at->format('H:i:s'),
                    'end' => now()->format('H:i:s'),
                    'seconds' => $elapsedBreak,
                    'minutes' => (int) round($elapsedBreak / 60),
                ];
                $attendance->breaks = $breaks;
            }
            $attendance->timer_status = Attendance::TIMER_COMPLETED;
            $attendance->check_out = $request->input('check_out', now()->format('H:i:s'));
            $attendance->timer_started_at = null;
            $attendance->break_started_at = null;
            $attendance->save();

            $msg = "Attendance closed for {$employee->employee_name} ({$employee->employee_no}).";
        } elseif ($action === 'update') {
            // Manual adjustment by Admin
            $inTime = $request->input('check_in');
            $outTime = $request->input('check_out');
            $status = $request->input('status', $attendance->status ?? 'present');
            $remarks = $request->input('remarks', $attendance->remarks);

            $attendance->check_in = $inTime ? Carbon::parse($inTime)->format('H:i:s') : null;
            $attendance->check_out = $outTime ? Carbon::parse($outTime)->format('H:i:s') : null;
            $attendance->status = in_array($status, Attendance::STATUSES, true) ? $status : 'present';
            $attendance->remarks = $remarks;

            if ($request->has('timer_status')) {
                $attendance->timer_status = $request->input('timer_status');
            } elseif ($attendance->check_out) {
                $attendance->timer_status = Attendance::TIMER_COMPLETED;
            } elseif ($attendance->check_in && $attendance->timer_status === Attendance::TIMER_NOT_STARTED) {
                $attendance->timer_status = Attendance::TIMER_RUNNING;
            }

            if ($request->has('total_break_minutes')) {
                $attendance->total_break_seconds = ((int) $request->input('total_break_minutes')) * 60;
            }

            $attendance->save();
            $msg = "Attendance details updated for {$employee->employee_name} ({$employee->employee_no}).";
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid action.'], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data' => $this->formatAttendancePayload($attendance, $user, $employee, $date),
        ]);
    }

    /**
     * Resolve existing attendance or initialize new.
     */
    protected function resolveAttendance(User $user, Employee $employee, string $date, bool $createIfMissing = false): ?Attendance
    {
        $attendance = Attendance::where('employee_id', $employee->id)->whereDate('attendance_date', $date)->first();

        if (!$attendance) {
            $attendance = Attendance::where('user_id', $user->id)->whereDate('attendance_date', $date)->first();
            if ($attendance && !$attendance->employee_id) {
                $attendance->employee_id = $employee->id;
                $attendance->save();
            }
        }

        if (!$attendance && $createIfMissing) {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'user_id' => $user->id,
                'attendance_date' => $date,
                'status' => 'present',
                'timer_status' => Attendance::TIMER_NOT_STARTED,
                'marked_by' => $user->id,
            ]);
        }

        return $attendance;
    }

    /**
     * Format standard response payload for frontend.
     */
    protected function formatAttendancePayload(?Attendance $attendance, ?User $user, Employee $employee, string $date): array
    {
        $currentWorkSeconds = (int) ($attendance?->current_work_seconds ?? 0);
        $currentBreakSeconds = (int) ($attendance?->current_break_seconds ?? 0);
        $timerStatus = $attendance?->timer_status ?? Attendance::TIMER_NOT_STARTED;

        return [
            'success' => true,
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name ?? $employee->employee_name,
            ],
            'employee' => [
                'id' => $employee->id,
                'employee_no' => $employee->employee_no ?? ('SZ ' . str_pad($employee->id, 3, '0', STR_PAD_LEFT)),
                'employee_name' => $employee->employee_name,
                'designation' => $employee->designation ?? 'Employee',
            ],
            'attendance' => [
                'id' => $attendance?->id,
                'attendance_date' => $date,
                'status' => $attendance?->status ?? 'unmarked',
                'timer_status' => $timerStatus,
                'check_in' => $attendance?->check_in ? substr((string) $attendance->check_in, 0, 5) : null,
                'check_out' => $attendance?->check_out ? substr((string) $attendance->check_out, 0, 5) : null,
                'total_work_seconds' => (int) ($attendance?->total_work_seconds ?? 0),
                'current_work_seconds' => $currentWorkSeconds,
                'total_break_seconds' => (int) ($attendance?->total_break_seconds ?? 0),
                'current_break_seconds' => $currentBreakSeconds,
                'timer_started_at' => $attendance?->timer_started_at?->toIso8601String(),
                'break_started_at' => $attendance?->break_started_at?->toIso8601String(),
                'breaks' => $attendance?->breaks ?? [],
                'remarks' => $attendance?->remarks,
            ],
            'is_admin' => auth()->check() ? $this->isAdminUser(auth()->user()) : false,
            'server_time' => now()->toIso8601String(),
        ];
    }

    /**
     * Check if user is Super Admin.
     */
    protected function isSuperAdminUser(User $user): bool
    {
        if ($user->id === 1) {
            return true;
        }
        if ($user->role) {
            $level = strtolower(str_replace('_', '-', (string) $user->role->access_level));
            if (str_contains($level, 'super-admin')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a user has admin privileges to manage attendance.
     */
    protected function isAdminUser(User $user): bool
    {
        return $this->isSuperAdminUser($user)
            || $user->can('edit', Attendance::class)
            || $user->can('create', Attendance::class);
    }

    /**
     * Enforce admin authorization.
     */
    protected function authorizeAdmin(User $user): void
    {
        if (!$this->isAdminUser($user)) {
            abort(403, 'You do not have permission to manage employee attendance.');
        }
    }

    /**
     * Locate employee by numerical ID or by employee_no code (e.g. "SZ 001").
     */
    protected function findEmployee($identifier): ?Employee
    {
        if (is_numeric($identifier)) {
            $emp = Employee::whereNull('deleted_at')->find($identifier);
            if ($emp) {
                return $emp;
            }
        }

        return Employee::whereNull('deleted_at')
            ->where(function ($q) use ($identifier) {
                $q->where('employee_no', $identifier)
                  ->orWhereRaw('LOWER(employee_name) = ?', [mb_strtolower($identifier)]);
            })->first();
    }
}

