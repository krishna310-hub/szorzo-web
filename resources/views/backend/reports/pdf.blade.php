<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recruitment Report - {{ $scopeLabel }}</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #263238; font-size: 10px; }
        h1 { margin: 0 0 4px; color: #263b80; font-size: 21px; }
        h2 { margin: 0; font-size: 13px; }
        .meta { color: #607d8b; margin-bottom: 14px; }
        .summary { background: #263b80; color: white; padding: 10px 12px; margin-bottom: 14px; }
        .summary strong { font-size: 20px; }
        .section { border: 1px solid #dce3ea; margin-bottom: 14px; }
        .section-title { background: #eef2f7; padding: 8px; border-bottom: 1px solid #dce3ea; }
        table.report { width: 100%; border-collapse: collapse; }
        table.report th, table.report td { border-bottom: 1px solid #e5e9ee; padding: 6px; }
        table.report th { text-align: left; background: #fafbfc; }
        table.report .number { text-align: right; white-space: nowrap; }
        table.report tfoot td { font-weight: bold; background: #fafbfc; border-top: 1px solid #b9c4ce; }
        .footer { margin-top: 14px; color: #78909c; font-size: 8px; text-align: right; }
    </style>
</head>
<body>
    <h1>Recruitment Report</h1>
    <div class="meta">
        Scope: <strong>{{ $scopeLabel }}</strong> |
        Period: {{ $filters['from_date'] ? date('d M Y', strtotime($filters['from_date'])) : 'Beginning' }}
        to {{ $filters['to_date'] ? date('d M Y', strtotime($filters['to_date'])) : 'Present' }}
    </div>
    <div class="summary">Total candidates<br><strong>{{ number_format($total) }}</strong></div>

    @foreach([['Recruiter Report', $recruiterReport], ['Client Report', $clientReport], ['Level of Interview Report', $levelReport]] as [$title, $rows])
        <div class="section">
            <div class="section-title"><h2>{{ $title }}</h2></div>
            <table class="report">
                <thead><tr><th>Name</th><th class="number">Count</th><th class="number">%</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr><td>{{ $row->label }}</td><td class="number">{{ number_format($row->total) }}</td><td class="number">{{ number_format($row->percentage, 2) }}%</td></tr>
                    @empty
                        <tr><td colspan="3">No candidate data found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr><td>Total Result</td><td class="number">{{ number_format($total) }}</td><td class="number">{{ $total ? '100.00%' : '0.00%' }}</td></tr></tfoot>
            </table>
        </div>
    @endforeach
    <div class="footer">Generated {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
