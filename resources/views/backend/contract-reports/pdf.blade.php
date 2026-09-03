<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contract Report {{ $month->format('F Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 10px; margin: 0; padding: 0; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        .meta { color: #666; margin-bottom: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #bbb; padding: 6px 8px; font-size: 9.5px; }
        th { background: #eee; text-align: left; }
        .num { text-align: right; }
        .center { text-align: center; }
        tfoot { font-weight: bold; background: #f3f3f3; }
        .text-success { color: #047857; }
        .text-primary { color: #1d4ed8; }
    </style>
</head>
<body>
    <h1>Contract Report</h1>
    <div class="meta">{{ $month->format('F Y') }} &middot; {{ ucfirst($contractType) }} contracts &middot; {{ $month->daysInMonth }} calendar days &middot; Generated {{ now()->format('d M Y, h:i A') }}</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Candidate</th>
                <th>Client</th>
                <th>Job Role</th>
                <th>Recruiter</th>
                <th class="num">Monthly Billing Payment</th>
                <th class="num">Hourly Salary</th>
                <th class="num">Billing</th>
                <th class="num">Hourly Billing Payment</th>
                <th class="center">Present</th>
                <th class="center">Leave Days</th>
                <th class="center">Worked Hours</th>
                <th class="num">Total Salary</th>
                <th class="num">Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $report->candidate?->candidate_name }}</strong></td>
                    <td>{{ $report->candidate?->client?->client ?? '—' }}</td>
                    <td>{{ $report->candidate?->jobRole?->job_role ?? '—' }}</td>
                    <td>{{ $report->candidate?->recruiter?->recruiter_name ?? '—' }}</td>
                    <td class="num">{{ $report->is_hourly ? '—' : 'INR '.number_format($report->monthly_take_home, 2) }}</td>
                    <td class="num">{{ $report->is_hourly ? 'INR '.number_format($report->hourly_salary, 2) : '—' }}</td>
                    <td class="num">{{ number_format($report->revenue_percentage, 2).'%' }}</td>
                    <td class="num">{{ $report->is_hourly ? 'INR '.number_format($report->revenue_per_hour, 2) : '—' }}</td>
                    <td class="center">{{ $report->present_days }}</td>
                    <td class="center">{{ $report->absent_days }}</td>
                    <td class="center">{{ $report->is_hourly ? number_format($report->worked_hours, 2) : '—' }}</td>
                    <td class="num text-success"><strong>INR {{ number_format((float) $report->payable_salary, 2) }}</strong></td>
                    <td class="num text-primary"><strong>INR {{ number_format((float) $report->contract_revenue, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="14" style="text-align:center">No contract records found.</td>
                </tr>
            @endforelse
        </tbody>
        @if($reports->count())
            <tfoot>
                <tr>
                    <td colspan="5">Total ({{ $reports->count() }} candidates)</td>
                    <td class="num">INR {{ number_format($reports->where('is_hourly', false)->sum('monthly_take_home'), 2) }}</td>
                    <td colspan="3" class="center">—</td>
                    <td class="center">{{ $reports->sum('present_days') }}</td>
                    <td class="center">{{ $reports->sum('absent_days') }}</td>
                    <td class="center">{{ number_format($reports->where('is_hourly', true)->sum('worked_hours'), 2) }}</td>
                    <td class="num text-success">INR {{ number_format($reports->sum('payable_salary'), 2) }}</td>
                    <td class="num text-primary">INR {{ number_format($reports->sum('contract_revenue'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
