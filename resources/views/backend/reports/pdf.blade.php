<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recruitment Report - {{ $scopeLabel }}</title>
    @php
        $levelCount = count($levelReport);
        $nameWidth = $levelCount > 15 ? 14 : ($levelCount > 10 ? 16 : ($levelCount > 6 ? 20 : 25));
        $countWidth = 4.5;
        $pctWidth = 4.5;
        $remainingWidth = 100 - $nameWidth - $countWidth - $pctWidth;
        $levelWidth = $levelCount > 0 ? ($remainingWidth / $levelCount) : 0;

        $thFontSize = $levelCount > 16 ? '5.2pt' : ($levelCount > 12 ? '6pt' : ($levelCount > 8 ? '6.8pt' : '7.5pt'));
        $tdFontSize = $levelCount > 16 ? '6pt' : ($levelCount > 12 ? '6.8pt' : ($levelCount > 8 ? '7.5pt' : '8.5pt'));
        $cellPadding = $levelCount > 16 ? '2px 0.5px' : ($levelCount > 12 ? '3px 1px' : '4px 3px');
    @endphp
    <style>
        @page { margin: 12px 14px; }
        body { font-family: DejaVu Sans, sans-serif; color: #263238; font-size: 8px; line-height: 1.15; }
        h1 { margin: 0 0 2px; color: #263b80; font-size: 16px; }
        h2 { margin: 0; font-size: 10.5px; color: #1e293b; }
        .meta { color: #607d8b; margin-bottom: 6px; font-size: 8px; }
        .summary-card { background: #263b80; color: white; padding: 5px 10px; margin-bottom: 8px; border-radius: 3px; }
        .summary-card .label { font-size: 7px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; }
        .summary-card .value { font-size: 15px; font-weight: bold; margin-top: 1px; }

        .section { margin-bottom: 8px; }
        .section-header { background: #eef2f7; padding: 4px 6px; border: 1px solid #dce3ea; border-bottom: none; font-weight: bold; page-break-after: avoid; }

        /* Standard 3-column table (Recruiter & Level of Interview reports) */
        table.std-report { width: 100%; border-collapse: collapse; table-layout: fixed; border: 1px solid #dce3ea; }
        table.std-report thead { display: table-header-group; }
        table.std-report tr { page-break-inside: avoid; }
        table.std-report th, table.std-report td { border: 1px solid #e5e9ee; padding: 3px 6px; font-size: 8px; }
        table.std-report th { background: #f8fafc; color: #334155; text-align: left; font-weight: 600; }
        table.std-report th.number, table.std-report td.number { text-align: right; }
        table.std-report tfoot td { font-weight: bold; background: #f1f5f9; border-top: 1px solid #cbd5e1; }

        /* Multi-column wide table (Client report with all interview levels) */
        table.client-report { width: 100%; border-collapse: collapse; table-layout: fixed; border: 1px solid #cbd5e1; }
        table.client-report thead { display: table-header-group; }
        table.client-report tr { page-break-inside: avoid; }
        table.client-report th, table.client-report td {
            border: 1px solid #dbe2ea;
            padding: {{ $cellPadding }};
            font-size: {{ $tdFontSize }};
            word-wrap: break-word;
            word-break: break-word;
            overflow: hidden;
        }
        table.client-report th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
            text-align: center;
            vertical-align: bottom;
            font-size: {{ $thFontSize }};
            line-height: 1.12;
            padding-top: 4px;
            padding-bottom: 4px;
        }
        table.client-report th.th-name { text-align: left; font-size: 7.5pt; padding-left: 4px; }
        table.client-report th.th-count { text-align: right; font-size: 6.8pt; }
        table.client-report th.th-pct { text-align: right; font-size: 6.8pt; padding-right: 4px; }
        table.client-report td.td-name { text-align: left; font-weight: 600; color: #0f172a; padding-left: 4px; }
        table.client-report td.td-count { text-align: right; font-weight: bold; }
        table.client-report td.td-level { text-align: center; }
        table.client-report td.td-pct { text-align: right; padding-right: 4px; }
        table.client-report td.zero { color: #94a3b8; }
        table.client-report tfoot td {
            font-weight: bold;
            background: #f1f5f9;
            border-top: 2px solid #94a3b8;
            font-size: {{ $tdFontSize }};
        }
        table.client-report tbody tr:nth-child(even) { background-color: #fafbfc; }

        .footer { margin-top: 10px; color: #94a3b8; font-size: 7.5px; text-align: right; }
    </style>
</head>
<body>
    <h1>Recruitment Report</h1>
    <div class="meta">
        Scope: <strong>{{ $scopeLabel }}</strong> |
        Period: {{ $filters['from_date'] ? date('d M Y', strtotime($filters['from_date'])) : 'Beginning' }}
        to {{ $filters['to_date'] ? date('d M Y', strtotime($filters['to_date'])) : 'Present' }}
    </div>
    <div class="summary-card">
        <div class="label">Total candidates</div>
        <div class="value">{{ number_format($total) }}</div>
    </div>

    <!-- Recruiter Report -->
    <div class="section">
        <div class="section-header"><h2>Recruiter Report</h2></div>
        <table class="std-report">
            <thead>
                <tr>
                    <th style="width: 70%;">Name</th>
                    <th class="number" style="width: 15%;">Count</th>
                    <th class="number" style="width: 15%;">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recruiterReport as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        <td class="number">{{ number_format($row->total) }}</td>
                        <td class="number">{{ number_format($row->percentage, 2) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No candidate data found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Result</td>
                    <td class="number">{{ number_format($total) }}</td>
                    <td class="number">{{ $total ? '100.00%' : '0.00%' }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Client Report with all Interview Levels -->
    <div class="section">
        <div class="section-header"><h2>Client Report</h2></div>
        <table class="client-report">
            <colgroup>
                <col style="width: {{ $nameWidth }}%;">
                <col style="width: {{ $countWidth }}%;">
                @foreach($levelReport as $lvl)
                    <col style="width: {{ $levelWidth }}%;">
                @endforeach
                <col style="width: {{ $pctWidth }}%;">
            </colgroup>
            <thead>
                <tr>
                    <th class="th-name">Name</th>
                    <th class="th-count">Count</th>
                    @foreach($levelReport as $lvl)
                        <th>{{ $lvl->label }}</th>
                    @endforeach
                    <th class="th-pct">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientReport as $row)
                    <tr>
                        <td class="td-name">{{ $row->label }}</td>
                        <td class="td-count">{{ number_format($row->total) }}</td>
                        @foreach($levelReport as $lvl)
                            @php $cnt = $row->level_counts[$lvl->label] ?? 0; @endphp
                            <td class="td-level {{ $cnt > 0 ? '' : 'zero' }}">
                                {{ $cnt > 0 ? number_format($cnt) : '0' }}
                            </td>
                        @endforeach
                        <td class="td-pct">{{ number_format($row->percentage, 2) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($levelReport) + 3 }}">No candidate data found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td class="td-name">Total Result</td>
                    <td class="td-count">{{ number_format($total) }}</td>
                    @foreach($levelReport as $lvl)
                        <td class="td-level">{{ number_format($lvl->total) }}</td>
                    @endforeach
                    <td class="td-pct">{{ $total ? '100.00%' : '0.00%' }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Level of Interview Report -->
    <div class="section">
        <div class="section-header"><h2>Level of Interview Report</h2></div>
        <table class="std-report">
            <thead>
                <tr>
                    <th style="width: 70%;">Name</th>
                    <th class="number" style="width: 15%;">Count</th>
                    <th class="number" style="width: 15%;">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($levelReport as $row)
                    <tr>
                        <td>{{ $row->label }}</td>
                        <td class="number">{{ number_format($row->total) }}</td>
                        <td class="number">{{ number_format($row->percentage, 2) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No candidate data found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Result</td>
                    <td class="number">{{ number_format($total) }}</td>
                    <td class="number">{{ $total ? '100.00%' : '0.00%' }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">Generated {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
