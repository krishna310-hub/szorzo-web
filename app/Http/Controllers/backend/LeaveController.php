<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveStatusNotification;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeaveController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('read', LeaveRequest::class);
        $user = $request->user();
        $canApprove = $user->can('approve', LeaveRequest::class) || $user->isSuperAdmin();

        if (!$canApprove && !$user->isInternalEmployee()) {
            abort(403, 'Leave requests are only available for internal employees.');
        }

        $filters = $request->validate([
            'status' => ['nullable', 'in:' . implode(',', LeaveRequest::STATUSES)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $baseQuery = LeaveRequest::eligible()
            ->with(['user.employee', 'reviewer'])
            ->when(!$canApprove, fn ($q) => $q->where('user_id', $user->id));

        // Counts for summary stat widgets
        $statsQuery = clone $baseQuery;
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        $leaves = $baseQuery
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('reason', 'like', "%{$search}%")
                        ->orWhere('leave_type', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $currentYear = (int) $request->input('holiday_year', now()->year);
        $holidays = collect();
        $holidayYears = [now()->year];
        try {
            $holidays = \App\Models\Holiday::forYear($currentYear)->orderBy('date', 'asc')->get();
            $dbYears = \App\Models\Holiday::selectRaw('DISTINCT year')->orderByDesc('year')->pluck('year')->all();
            if (!empty($dbYears)) {
                $holidayYears = array_values(array_unique(array_merge([now()->year], $dbYears)));
                sort($holidayYears);
            }
        } catch (\Throwable) {}

        return view('backend.leaves.index', compact('leaves', 'canApprove', 'filters', 'stats', 'holidays', 'holidayYears', 'currentYear'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', LeaveRequest::class);
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->isInternalEmployee()) {
            return redirect()->route('admin.leaves.index')->with('error', 'Leave requests are restricted to internal employees only.');
        }

        $currentYear = now()->year;
        $holidays = collect();
        $holidayYears = [$currentYear];
        try {
            $holidays = \App\Models\Holiday::forYear($currentYear)->orderBy('date', 'asc')->get();
            $dbYears = \App\Models\Holiday::selectRaw('DISTINCT year')->orderByDesc('year')->pluck('year')->all();
            if (!empty($dbYears)) {
                $holidayYears = array_values(array_unique(array_merge([$currentYear], $dbYears)));
                sort($holidayYears);
            }
        } catch (\Throwable) {}

        return view('backend.leaves.create', compact('holidays', 'holidayYears', 'currentYear'));
    }

    public function store(StoreLeaveRequest $request)
    {
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->isInternalEmployee()) {
            return redirect()->route('admin.leaves.index')->with('error', 'Leave requests are restricted to internal employees only.');
        }

        $data = $request->validated();
        $isHalfDay = ($data['day_type'] ?? 'full_day') === 'half_day';

        if ($isHalfDay) {
            $data['to_date'] = $data['from_date'];
            $data['day_type'] = 'half_day';
            $data['half_day_type'] = $data['half_day_type'] ?? 'morning';
            $data['number_of_days'] = 0.5;
        } else {
            $data['day_type'] = 'full_day';
            $data['half_day_type'] = null;
        }

        $from = CarbonImmutable::parse($data['from_date']);
        $to = CarbonImmutable::parse($data['to_date']);

        if (!$isHalfDay) {
            $data['number_of_days'] = $from->diffInDays($to) + 1;
        }

        // Check for conflicting requests for the same user
        $conflictQuery = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('from_date', '<=', $to->toDateString())
            ->where('to_date', '>=', $from->toDateString());

        if ($isHalfDay) {
            // A half day only conflicts if there is a full-day request or another half-day request for the same session
            $conflictQuery->where(function ($q) use ($data) {
                $q->where('number_of_days', '>', 0.5)
                  ->orWhereNull('day_type')
                  ->orWhere('day_type', 'full_day')
                  ->orWhere('half_day_type', $data['half_day_type']);
            });
        }

        $conflict = $conflictQuery->first();

        if ($conflict) {
            $cStatus = ucfirst($conflict->status);
            $cFrom = CarbonImmutable::parse($conflict->from_date)->format('d M Y');
            $cTo = CarbonImmutable::parse($conflict->to_date)->format('d M Y');
            $cSession = $conflict->isHalfDay() && $conflict->half_day_type ? ' (' . ucfirst($conflict->half_day_type) . ')' : '';
            throw ValidationException::withMessages([
                'from_date' => "You already have a {$cStatus} leave request covering overlapping dates ({$cFrom} to {$cTo}{$cSession})."
            ]);
        }

        $data['user_id'] = $user->id;
        $data['status'] = 'pending';

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('leave-attachments', 'public');
        }

        // Schema safety check: omit new columns if migration is not yet executed on this database
        $saveData = $data;
        $hasDayTypeCol = false;
        try {
            $hasDayTypeCol = \Illuminate\Support\Facades\Schema::hasTable('leave_requests')
                && \Illuminate\Support\Facades\Schema::hasColumn('leave_requests', 'day_type');
        } catch (\Throwable $e) {
            $hasDayTypeCol = false;
        }

        if (!$hasDayTypeCol) {
            unset($saveData['day_type'], $saveData['half_day_type']);
            if ($isHalfDay && !str_contains($saveData['reason'], '[Half Day')) {
                $saveData['reason'] = '[Half Day - ' . ucfirst($data['half_day_type']) . '] ' . $saveData['reason'];
            }
        }

        $leave = LeaveRequest::create($saveData);

        // Notify Admins & Leave Approvers
        try {
            $approvers = User::where('is_active', 1)
                ->where(function ($q) {
                    $q->whereHas('role', function ($r) {
                        $r->where('access_level', 'super_admin')
                          ->orWhere('name', 'Super Admin')
                          ->orWhereHas('permissions', function ($p) {
                              $p->where('page', 'leave')->where('name', 'Approve');
                          });
                    });
                })
                ->get()
                ->filter(fn ($u) => $u->isSuperAdmin() || $u->can('approve', LeaveRequest::class))
                ->unique('id');

            $fromDateStr = $from->format('d M Y');
            $toDateStr = $to->format('d M Y');
            $dateRangeStr = $fromDateStr === $toDateStr ? $fromDateStr : "{$fromDateStr} - {$toDateStr}";
            $reasonSnippet = Str::limit($leave->reason, 100);
            $durationStr = $isHalfDay ? "0.5 day (" . ucfirst($data['half_day_type']) . ")" : "{$leave->number_of_days} day(s)";

            foreach ($approvers as $admin) {
                if ($admin->id === $user->id) {
                    continue;
                }
                $admin->notify(new LeaveStatusNotification(
                    title: 'New Leave Request Received',
                    message: "{$user->name} requested {$leave->leave_type} for {$durationStr} ({$dateRangeStr}). Reason: {$reasonSnippet}",
                    actionUrl: route('admin.leaves.index', ['status' => 'pending']),
                    status: 'pending',
                    leaveId: $leave->id,
                    leaveType: $leave->leave_type,
                    actionType: 'leave_request',
                    userId: $user->id
                ));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to notify admins of new leave request: ' . $e->getMessage());
        }

        return redirect()->route('admin.leaves.index')->with('success', 'Leave request submitted successfully. Waiting for admin approval.');
    }

    public function review(Request $request, LeaveRequest $leave)
    {
        $this->authorize('approve', LeaveRequest::class);

        $isRejecting = $request->input('status') === 'rejected';

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_remarks' => [
                $isRejecting ? 'required' : 'nullable',
                'string',
                $isRejecting ? 'min:3' : 'nullable',
                'max:2000'
            ],
        ], [
            'review_remarks.required' => 'A reason is required when rejecting a leave request.',
            'review_remarks.min' => 'Please provide a clear reason for the rejection (at least 3 characters).',
        ]);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be reviewed.');
        }

        DB::transaction(function () use ($leave, $data, $request) {
            $leave->update([
                'status' => $data['status'],
                'review_remarks' => $data['review_remarks'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $from = CarbonImmutable::parse($leave->from_date);
            $to = CarbonImmutable::parse($leave->to_date);
            $fromDateStr = $from->format('d M Y');
            $toDateStr = $to->format('d M Y');
            $dateRangeStr = $fromDateStr === $toDateStr ? $fromDateStr : "{$fromDateStr} - {$toDateStr}";

            if ($data['status'] === 'approved') {
                $employee = $leave->user?->linkedEmployee() ?? $leave->user?->getOrCreateEmployee();
                $employeeId = $employee?->id;

                $isHalfDay = $leave->isHalfDay();
                $attendanceStatus = $isHalfDay ? 'half_day' : 'on_leave';
                $sessionSuffix = ($isHalfDay && $leave->half_day_type) ? ' (' . ucfirst($leave->half_day_type) . ')' : '';
                $remarksPrefix = $isHalfDay
                    ? "Approved Half Day {$leave->leave_type}{$sessionSuffix}"
                    : "Approved {$leave->leave_type}";

                for ($date = $from; $date->lte($to); $date = $date->addDay()) {
                    $dateStr = $date->toDateString();
                    $attendance = null;
                    if ($employeeId) {
                        $attendance = Attendance::where('employee_id', $employeeId)
                            ->whereDate('attendance_date', $dateStr)
                            ->first();
                    }
                    if (!$attendance) {
                        $attendance = Attendance::where('user_id', $leave->user_id)
                            ->whereDate('attendance_date', $dateStr)
                            ->first();
                    }

                    $values = [
                        'employee_id' => $employeeId,
                        'user_id' => $leave->user_id,
                        'attendance_date' => $dateStr,
                        'status' => $attendanceStatus,
                        'timer_status' => Attendance::TIMER_COMPLETED,
                        'check_in' => $isHalfDay ? ($attendance?->check_in) : null,
                        'check_out' => $isHalfDay ? ($attendance?->check_out) : null,
                        'leave_request_id' => $leave->id,
                        'marked_by' => $request->user()->id,
                        'remarks' => $remarksPrefix . ($leave->reason ? ': ' . $leave->reason : ''),
                    ];

                    if ($attendance) {
                        $attendance->update($values);
                    } else {
                        Attendance::create($values);
                    }
                }

                // Notify Employee of Approval
                try {
                    $remarksNote = !empty($data['review_remarks']) ? " Note: " . $data['review_remarks'] : '';
                    $durationDesc = $isHalfDay ? "0.5 day{$sessionSuffix}" : "{$leave->number_of_days} day(s)";
                    $leave->user?->notify(new LeaveStatusNotification(
                        title: 'Leave Request Approved',
                        message: "Your {$leave->leave_type} request for {$durationDesc} ({$dateRangeStr}) has been approved.{$remarksNote}",
                        actionUrl: route('admin.leaves.index'),
                        status: 'approved',
                        leaveId: $leave->id,
                        leaveType: $leave->leave_type,
                        actionType: 'leave_approved',
                        userId: $leave->user_id
                    ));
                } catch (\Throwable $e) {
                    Log::error('Failed to notify employee of leave approval: ' . $e->getMessage());
                }

            } elseif ($data['status'] === 'rejected') {
                // If any attendance record was tied to this leave, clean it up
                Attendance::where('leave_request_id', $leave->id)->delete();

                // Notify Employee of Rejection with required reason
                try {
                    $sessionSuffix = ($leave->isHalfDay() && $leave->half_day_type) ? ' (' . ucfirst($leave->half_day_type) . ')' : '';
                    $durationDesc = $leave->isHalfDay() ? "0.5 day{$sessionSuffix}" : "{$leave->number_of_days} day(s)";
                    $leave->user?->notify(new LeaveStatusNotification(
                        title: 'Leave Request Rejected',
                        message: "Your {$leave->leave_type} request for {$durationDesc} ({$dateRangeStr}) was rejected. Reason: " . $data['review_remarks'],
                        actionUrl: route('admin.leaves.index'),
                        status: 'rejected',
                        leaveId: $leave->id,
                        leaveType: $leave->leave_type,
                        actionType: 'leave_rejected',
                        userId: $leave->user_id
                    ));
                } catch (\Throwable $e) {
                    Log::error('Failed to notify employee of leave rejection: ' . $e->getMessage());
                }
            }
        });

        $msg = $data['status'] === 'approved'
            ? 'Leave request approved and attendance recorded successfully.'
            : 'Leave request rejected and employee notified with reason.';

        return back()->with('success', $msg);
    }

    public function cancel(Request $request, LeaveRequest $leave)
    {
        $user = $request->user();

        if ($leave->user_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized to cancel this leave request.');
        }

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be cancelled.');
        }

        // Delete attachment if present
        if ($leave->attachment && Storage::disk('public')->exists($leave->attachment)) {
            Storage::disk('public')->delete($leave->attachment);
        }

        $leave->delete();

        return back()->with('success', 'Leave request cancelled successfully.');
    }

    public function attachment(LeaveRequest $leave)
    {
        $this->authorize('view', $leave);
        abort_unless($leave->attachment && Storage::disk('public')->exists($leave->attachment), 404, 'Attachment file not found.');
        return Storage::disk('public')->download($leave->attachment);
    }
}
