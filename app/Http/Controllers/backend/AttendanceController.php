<?php

namespace App\Http\Controllers\backend;

use App\Exports\AttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    use AuthorizesRequests;

    public function dashboard()
    {
        $this->authorize('read', Attendance::class);
        $today = now()->toDateString();
        $eligible = Employee::eligibleForAttendance($today)->count();
        $counts = Attendance::eligible()->whereDate('attendance_date', $today)->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total', 'status');
        $marked = (int) $counts->sum();
        $monthCounts = Attendance::eligible()->whereYear('attendance_date', now()->year)->whereMonth('attendance_date', now()->month)->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total', 'status');

        return view('backend.attendance.dashboard', [
            'eligible' => $eligible,
            'counts' => $counts,
            'unmarked' => max(0, $eligible - $marked),
            'monthCounts' => $monthCounts,
            'recentAttendance' => Attendance::eligible()->with(['employee', 'user'])->latest('updated_at')->limit(8)->get(),
            'recentLeaves' => LeaveRequest::eligible()->with('user')->latest()->limit(8)->get(),
        ]);
    }

    public function index(Request $request)
    {
        $this->authorize('read', Attendance::class);
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'employee_id' => ['nullable', 'integer'],
            'designation' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:' . implode(',', Attendance::STATUSES)],
        ]);
        $date = $filters['date'] ?? now()->toDateString();

        $employees = Employee::eligibleForAttendance($date)
            ->with(['attendances' => fn ($q) => $q->whereDate('attendance_date', $date)])
            ->when($filters['employee_id'] ?? null, fn ($q, $id) => $q->whereKey($id))
            ->when($filters['designation'] ?? null, fn ($q, $v) => $q->where('designation', $v))
            ->orderBy('employee_name')
            ->paginate(50)
            ->withQueryString();

        $recent = Attendance::eligible()
            ->with(['employee', 'user'])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['employee_id'] ?? null, fn ($q, $id) => $q->where('employee_id', $id))
            ->latest('attendance_date')
            ->latest('id')
            ->paginate(20, ['*'], 'recent_page')
            ->withQueryString();

        $allEmployees = Employee::eligibleForAttendance($date)
            ->orderBy('employee_name')
            ->get(['id', 'employee_name', 'employee_no', 'designation']);

        $designations = Employee::eligibleForAttendance($date)
            ->whereNotNull('designation')
            ->where('designation', '!=', '')
            ->distinct()
            ->orderBy('designation')
            ->pluck('designation');

        return view('backend.attendance.index', [
            'employees' => $employees,
            'recent' => $recent,
            'date' => $date,
            'filters' => $filters,
            'allEmployees' => $allEmployees,
            'designations' => $designations,
        ]);
    }

    public function store(StoreAttendanceRequest $request)
    {
        $data = $request->validated();
        $employeeIds = collect($data['records'])->pluck('employee_id');
        $existingEmployeeIds = Attendance::whereDate('attendance_date', $data['attendance_date'])
            ->whereIn('employee_id', $employeeIds)
            ->pluck('employee_id');

        if ($existingEmployeeIds->isNotEmpty() && ! $request->user()->can('edit', Attendance::class)) {
            abort(403, 'You do not have permission to edit existing attendance.');
        }

        DB::transaction(function () use ($data, $request) {
            foreach ($data['records'] as $record) {
                $values = collect($record)->only(['status', 'check_in', 'check_out', 'remarks'])->all();
                if (! in_array($values['status'], ['present', 'half_day'], true)) {
                    $values['check_in'] = $values['check_out'] = null;
                }

                $employee = Employee::find($record['employee_id']);
                $linkedUser = $employee?->linkedUser();

                $attendance = Attendance::where('employee_id', $record['employee_id'])
                    ->whereDate('attendance_date', $data['attendance_date'])
                    ->first();

                $payload = array_merge($values, [
                    'employee_id' => $record['employee_id'],
                    'user_id' => $linkedUser?->id,
                    'attendance_date' => $data['attendance_date'],
                    'marked_by' => $request->user()->id,
                    'leave_request_id' => null,
                ]);

                if ($attendance) {
                    $attendance->update($payload);
                } else {
                    Attendance::create($payload);
                }
            }
        });

        return back()->with('success', 'Attendance saved successfully.');
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->authorize('edit', Attendance::class);
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', Attendance::STATUSES)],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after:check_in'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);
        if (! in_array($data['status'], ['present', 'half_day'], true)) {
            $data['check_in'] = $data['check_out'] = null;
        }
        $attendance->update($data + [
            'marked_by' => $request->user()->id,
            'leave_request_id' => $data['status'] === 'on_leave' ? $attendance->leave_request_id : null,
        ]);

        return back()->with('success', 'Attendance updated successfully.');
    }

    public function monthly(Request $request)
    {
        $this->authorize('read', Attendance::class);
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'employee_id' => ['nullable', 'integer'],
        ]);
        $month = (int) ($validated['month'] ?? now()->month);
        $year = (int) ($validated['year'] ?? now()->year);
        $start = CarbonImmutable::create($year, $month, 1);
        $days = range(1, $start->daysInMonth);

        $employees = Employee::eligibleForAttendance($start->toDateString())
            ->when($validated['employee_id'] ?? null, fn ($q, $id) => $q->whereKey($id))
            ->with(['attendances' => fn ($q) => $q->whereBetween('attendance_date', [$start, $start->endOfMonth()])])
            ->orderBy('employee_name')
            ->paginate(25)
            ->withQueryString();

        return view('backend.attendance.monthly', [
            'employees' => $employees,
            'days' => $days,
            'month' => $month,
            'year' => $year,
            'allEmployees' => Employee::eligibleForAttendance($start->toDateString())->orderBy('employee_name')->get(['id', 'employee_name', 'employee_no']),
        ]);
    }

    public function report(Request $request)
    {
        $this->authorize('read', Attendance::class);
        [$records, $filters, $allEmployees, $summary] = $this->reportRecords($request, true);

        return view('backend.attendance.report', compact('records', 'filters', 'allEmployees', 'summary'));
    }

    public function export(Request $request, string $format)
    {
        $this->authorize('export', Attendance::class);
        abort_unless(in_array($format, ['csv', 'xlsx', 'pdf', 'print'], true), 404);
        [$records, $filters] = $this->reportRecords($request, false);
        $name = 'attendance-report-' . now()->format('Ymd-His');

        if ($format === 'pdf') {
            return Pdf::loadView('backend.attendance.pdf', compact('records', 'filters'))->setPaper('a4', 'landscape')->download($name . '.pdf');
        }
        if ($format === 'print') {
            return view('backend.attendance.pdf', compact('records', 'filters') + ['print' => true]);
        }

        return Excel::download(
            new AttendanceReportExport($records),
            $name . '.' . $format,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    private function reportRecords(Request $request, bool $paginate): array
    {
        $filters = $request->validate([
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'employee_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:' . implode(',', Attendance::STATUSES)],
        ]);

        $query = Attendance::eligible()
            ->with(['employee', 'user', 'leaveRequest'])
            ->when($filters['employee_id'] ?? null, fn ($q, $v) => $q->where('employee_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['from_date'] ?? null, fn ($q, $v) => $q->whereDate('attendance_date', '>=', $v))
            ->when($filters['to_date'] ?? null, fn ($q, $v) => $q->whereDate('attendance_date', '<=', $v))
            ->when($filters['month'] ?? null, fn ($q, $v) => $q->whereMonth('attendance_date', $v))
            ->when($filters['year'] ?? null, fn ($q, $v) => $q->whereYear('attendance_date', $v))
            ->latest('attendance_date')
            ->latest('id');

        $summary = (clone $query)->reorder()->selectRaw('status, COUNT(*) total')->groupBy('status')->pluck('total', 'status');
        $records = $paginate ? $query->paginate(50)->withQueryString() : $query->get();
        $allEmployees = Employee::eligibleForAttendance()->orderBy('employee_name')->get(['id', 'employee_name', 'employee_no']);

        return [$records, $filters, $allEmployees, $summary];
    }
}
