<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1e293b;
            margin: 15px;
        }
        h2 {
            margin: 0 0 4px 0;
            color: #0f172a;
            font-size: 16px;
        }
        .meta {
            color: #64748b;
            margin-bottom: 12px;
            font-size: 8.5px;
        }
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 14px;
        }
        .summary-card {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 6px 8px;
            border-radius: 4px;
            text-align: center;
        }
        .summary-card .label {
            font-size: 7.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .summary-card .value {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin: 2px 0;
        }
        .summary-card .sub {
            font-size: 7.5px;
            color: #94a3b8;
            font-family: monospace;
        }
        table.records-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.records-table th,
        table.records-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            text-align: left;
            vertical-align: middle;
            font-size: 8.5px;
        }
        table.records-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: bold;
        }
        table.records-table tr:nth-child(even) td {
            background: #fafafa;
        }
        .mono {
            font-family: monospace;
        }
        .badge {
            display: inline-block;
            padding: 1.5px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: capitalize;
        }
        .b-present { background: #dcfce7; color: #166534; }
        .b-absent { background: #fee2e2; color: #991b1b; }
        .b-half_day { background: #fef3c7; color: #92400e; }
        .b-on_leave { background: #dbeafe; color: #1e40af; }
        .b-holiday { background: #f3e8ff; color: #6b21a8; }
        .b-week_off { background: #e2e8f0; color: #334155; }
        .print-btn {
            display: inline-block;
            margin-bottom: 12px;
            padding: 5px 12px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 10px;
            cursor: pointer;
        }
        @media print {
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    @isset($print)
        <button class="print-btn" onclick="window.print()">Print Report</button>
    @endisset

    <h2>Attendance Detailed Report</h2>
    <div class="meta">
        Generated on {{ now()->format('d M Y, H:i') }} &middot;
        Filters: {{ collect($filters ?? [])->filter(fn($v) => $v !== null && $v !== '')->map(fn($v, $k) => str_replace('_', ' ', ucwords($k, '_')) . ': ' . $v)->implode(' &middot; ') ?: 'All records' }}
    </div>

    @if(isset($timeMetrics))
    <table class="summary-table">
        <tr>
            <td class="summary-card" style="border-left: 3px solid #16a34a;">
                <div class="label">Total Work Time</div>
                <div class="value">{{ $timeMetrics['total_work_formatted'] ?? '0m' }}</div>
                <div class="sub">{{ \App\Models\Attendance::formatSecondsToTime($timeMetrics['total_work_seconds'] ?? 0) }} (hh:mm)</div>
            </td>
            <td class="summary-card" style="border-left: 3px solid #d97706;">
                <div class="label">Total Tea / Break</div>
                <div class="value">{{ $timeMetrics['total_break_formatted'] ?? '0m' }}</div>
                <div class="sub">{{ \App\Models\Attendance::formatSecondsToTime($timeMetrics['total_break_seconds'] ?? 0) }} (hh:mm)</div>
            </td>
            <td class="summary-card" style="border-left: 3px solid #0284c7;">
                <div class="label">Total Lunch Time</div>
                <div class="value">{{ $timeMetrics['total_lunch_formatted'] ?? '0m' }}</div>
                <div class="sub">{{ \App\Models\Attendance::formatSecondsToTime($timeMetrics['total_lunch_seconds'] ?? 0) }} (hh:mm)</div>
            </td>
            <td class="summary-card" style="border-left: 3px solid #4f46e5;">
                <div class="label">Combined Total Break</div>
                <div class="value">{{ $timeMetrics['total_combined_break_formatted'] ?? '0m' }}</div>
                <div class="sub">{{ \App\Models\Attendance::formatSecondsToTime($timeMetrics['total_combined_seconds'] ?? 0) }} (hh:mm)</div>
            </td>
            <td class="summary-card" style="border-left: 3px solid #475569;">
                <div class="label">Total Records</div>
                <div class="value">{{ count($records) }}</div>
                <div class="sub">Entries Listed</div>
            </td>
        </tr>
    </table>
    @endif

    <table class="records-table">
        <thead>
            <tr>
                <th style="width: 8%;">Date</th>
                <th style="width: 17%;">Employee / User</th>
                <th style="width: 7%;">Emp ID</th>
                <th style="width: 7%;">Status</th>
                <th style="width: 7%;">Timer</th>
                <th style="width: 6%;">Check In</th>
                <th style="width: 6%;">Check Out</th>
                <th style="width: 9%;">Work Hours</th>
                <th style="width: 8%;">Break (Tea)</th>
                <th style="width: 8%;">Lunch Time</th>
                <th style="width: 8%;">Total Break</th>
                <th style="width: 9%;">Leave / Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $a)
                <tr>
                    <td>{{ $a->attendance_date?->format('Y-m-d') }}</td>
                    <td>
                        <strong>{{ $a->display_name }}</strong><br>
                        <span style="color: #64748b; font-size: 7.5px;">{{ $a->user?->email ?? $a->employee?->official_mail ?? '—' }}</span>
                    </td>
                    <td class="mono">{{ $a->employee_no ?? '—' }}</td>
                    <td>
                        <span class="badge b-{{ $a->status }}">
                            {{ ucwords(str_replace('_', ' ', $a->status)) }}
                        </span>
                    </td>
                    <td style="font-size: 7.5px; text-transform: capitalize;">
                        {{ str_replace('_', ' ', $a->timer_status ?? 'not_started') }}
                    </td>
                    <td class="mono">{{ $a->check_in ? substr((string)$a->check_in, 0, 5) : '—' }}</td>
                    <td class="mono">{{ $a->check_out ? substr((string)$a->check_out, 0, 5) : '—' }}</td>
                    <td class="mono">
                        <strong>{{ $a->formatted_work_time }}</strong>
                    </td>
                    <td class="mono">
                        {{ $a->formatted_break_time }}
                        @if($a->break_count > 0)
                            <span style="font-size: 7px; color: #64748b;">({{ $a->break_count }})</span>
                        @endif
                    </td>
                    <td class="mono">
                        {{ $a->formatted_lunch_time }}
                        @if($a->lunch_count > 0)
                            <span style="font-size: 7px; color: #64748b;">({{ $a->lunch_count }})</span>
                        @endif
                    </td>
                    <td class="mono">
                        <strong>{{ $a->formatted_total_break_time }}</strong>
                    </td>
                    <td>
                        @if($a->leaveRequest?->leave_type)
                            <strong>{{ $a->leaveRequest->leave_type }}</strong>
                        @endif
                        @if($a->remarks)
                            <span style="color: #475569; font-size: 7.5px;">{{ $a->remarks }}</span>
                        @endif
                        @if(!$a->leaveRequest?->leave_type && !$a->remarks)
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 15px; color: #64748b;">
                        No attendance records match the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @isset($print)
        <script>
            window.onload = () => window.print()
        </script>
    @endisset
</body>
</html>
