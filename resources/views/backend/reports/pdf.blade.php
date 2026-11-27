<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recruitment Report - {{ $scopeLabel }}</title>
    @php
        $levelCount = count($levelReport);
        $clientCount = count($clientReport);
        $recruiterCount = count($recruiterReport);

        // Dynamic column widths for Client Report (A4 Landscape)
        $nameWidth = $levelCount > 18 ? 22 : ($levelCount > 12 ? 24 : ($levelCount > 6 ? 26 : 30));
        $countWidth = 5;
        $pctWidth = 5.5;
        $remainingWidth = 100 - $nameWidth - $countWidth - $pctWidth;
        $levelWidth = $levelCount > 0 ? ($remainingWidth / $levelCount) : 0;

        $thFontSize = $levelCount > 18 ? '6.2pt' : ($levelCount > 12 ? '6.8pt' : '7.5pt');
        $tdFontSize = $levelCount > 18 ? '7.2pt' : ($levelCount > 12 ? '7.8pt' : '8.5pt');
        $nameFontSize = $levelCount > 18 ? '7.3pt' : ($levelCount > 12 ? '7.8pt' : '8.5pt');
        $cellPadding = $levelCount > 18 ? '2.5px 2px' : ($levelCount > 12 ? '3.5px 3px' : '5px 4px');

        // Short 2-line column headers for wide Client Report matrix
        $shortLabelMap = [
            'Internal Duplicate'       => ['Int', 'Dup'],
            'Profile Feedback Pending' => ['FB', 'Pend'],
            'Client Duplicate'         => ['Cl', 'Dup'],
            'Screen Select'            => ['Scr', 'Sel'],
            'Screen Reject'            => ['Scr', 'Rej'],
            'L1 Scheduled'             => ['L1', 'Sch'],
            'L1 Select'                => ['L1', 'Sel'],
            'L1 Reject'                => ['L1', 'Rej'],
            'L1 Re-Schedule'           => ['L1', 'ReSch'],
            'L2 Scheduled'             => ['L2', 'Sch'],
            'L2 Select'                => ['L2', 'Sel'],
            'L2 Reject'                => ['L2', 'Rej'],
            'L3 Scheduled'             => ['L3', 'Sch'],
            'L3 Select'                => ['L3', 'Sel'],
            'L3 Reject'                => ['L3', 'Rej'],
            'HR Discussion'            => ['HR', 'Disc'],
            'HR Reject'                => ['HR', 'Rej'],
            'Offer Accepted'           => ['Off', 'Acc'],
            'Offer Declined'           => ['Off', 'Dec'],
            'Onboarded with Client'    => ['Onb', 'Clnt'],
            'Joiner Declined'          => ['Jnr', 'Dec'],
            'Position Hold'            => ['Pos', 'Hold'],
            'Position Closed'          => ['Pos', 'Clsd'],
            'Candidate Not Responding' => ['Cand', 'NR'],
            'Candidate Not Interested' => ['Cand', 'NI'],
            'No interview level'       => ['No', 'Lvl'],
        ];

        $formatShortHeader = function (string $label) use ($shortLabelMap): array {
            if (isset($shortLabelMap[$label])) {
                return $shortLabelMap[$label];
            }
            $words = preg_split('/\s+/', trim($label));
            if (count($words) >= 2) {
                return [
                    mb_substr($words[0], 0, 4),
                    mb_substr($words[1], 0, 4),
                ];
            }
            return [mb_substr($label, 0, 5), ''];
        };

        $levelMeta = [];
        foreach ($levelReport as $lvl) {
            $parts = $formatShortHeader($lvl->label);
            $levelMeta[$lvl->label] = [
                'line1' => $parts[0],
                'line2' => $parts[1],
                'code'  => trim($parts[0] . ' ' . $parts[1]),
            ];
        }

        $periodText = ($filters['from_date'] ? date('d M Y', strtotime($filters['from_date'])) : 'Beginning')
            . ' to '
            . ($filters['to_date'] ? date('d M Y', strtotime($filters['to_date'])) : 'Present');

        // Split Level of Interview rows into 2 columns if > 10 for clean Page 1 fit
        $splitLevels = $levelCount > 10;
        $halfLevelCount = $splitLevels ? (int) ceil($levelCount / 2) : $levelCount;
        $levelLeft = $splitLevels ? $levelReport->slice(0, $halfLevelCount)->values() : $levelReport;
        $levelRight = $splitLevels ? $levelReport->slice($halfLevelCount)->values() : collect();
    @endphp
    <style>
        @page {
            margin: 16px 18px 22px 18px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            font-size: 8.5pt;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }

        /* Header Banner */
        .header-banner {
            width: 100%;
            border-collapse: collapse;
            background-color: #1e3a8a;
            color: #ffffff;
            margin-bottom: 10px;
        }
        .header-banner td {
            padding: 10px 14px;
            vertical-align: middle;
        }
        .header-title {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 3px 0;
            letter-spacing: 0.2px;
        }
        .header-meta {
            font-size: 8.5pt;
            color: #dbeafe;
        }
        .header-meta strong {
            color: #ffffff;
        }
        .header-right {
            text-align: right;
            font-size: 8pt;
            color: #bfdbfe;
            white-space: nowrap;
        }

        /* KPI Summary Strip */
        .kpi-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 12px -8px;
        }
        .kpi-card {
            width: 25%;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-top: 3px solid #1e3a8a;
            padding: 7px 10px;
            vertical-align: top;
        }
        .kpi-card.primary {
            background-color: #eff6ff;
            border-color: #93c5fd;
            border-top: 3px solid #1d4ed8;
        }
        .kpi-label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .kpi-value {
            font-size: 15pt;
            font-weight: bold;
            color: #0f172a;
        }
        .kpi-card.primary .kpi-value {
            color: #1e3a8a;
        }

        /* Section Titles */
        .section {
            margin-bottom: 12px;
        }
        .section-title-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
            page-break-after: avoid;
        }
        .section-title-bar td {
            padding: 3px 0 3px 8px;
            border-left: 4px solid #1e3a8a;
            vertical-align: middle;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .section-subtitle {
            font-size: 7.5pt;
            color: #64748b;
            text-align: right;
            border-left: none !important;
            padding-left: 0 !important;
        }

        /* Standard 3-column & Split tables */
        table.std-report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #94a3b8;
        }
        table.std-report thead {
            display: table-header-group;
        }
        table.std-report tr {
            page-break-inside: avoid;
        }
        table.std-report th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 5px 8px;
            border: 1px solid #1e40af;
            text-align: left;
        }
        table.std-report th.number,
        table.std-report td.number {
            text-align: right;
        }
        table.std-report td {
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
            font-size: 8pt;
            color: #1e293b;
            vertical-align: middle;
        }
        table.std-report tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.std-report tfoot td {
            font-weight: bold;
            background-color: #e2e8f0;
            color: #0f172a;
            border-top: 2px solid #1e3a8a;
            padding: 5px 8px;
        }

        /* Mini visual share bar */
        .bar-track {
            width: 100%;
            background-color: #e2e8f0;
            height: 6px;
        }
        .bar-fill {
            background-color: #2563eb;
            height: 6px;
        }

        /* Side-by-side layout for Level of Interview Report */
        .two-col-wrap {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col-wrap > tbody > tr > td.col-pane {
            width: 49%;
            vertical-align: top;
            padding: 0;
        }
        .two-col-wrap > tbody > tr > td.col-gap {
            width: 2%;
            padding: 0;
        }
        .split-total-bar {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            border: 1px solid #94a3b8;
            background-color: #e2e8f0;
        }
        .split-total-bar td {
            padding: 5px 8px;
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
        }

        /* Client Report Matrix (Page 2+) */
        .page-break {
            page-break-before: always;
        }
        table.client-report {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1.5px solid #64748b;
        }
        table.client-report thead {
            display: table-header-group;
        }
        table.client-report tr {
            page-break-inside: avoid;
        }
        table.client-report th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            font-size: {{ $thFontSize }};
            line-height: 1.15;
            padding: 5px 1px;
            border: 1px solid #3b82f6;
        }
        table.client-report th.th-name {
            text-align: left;
            font-size: 8pt;
            padding-left: 6px;
        }
        table.client-report th.th-count {
            text-align: right;
            font-size: 7.5pt;
            padding-right: 4px;
            background-color: #172554;
        }
        table.client-report th.th-pct {
            text-align: right;
            font-size: 7.5pt;
            padding-right: 5px;
            background-color: #172554;
        }
        table.client-report td {
            border: 1px solid #cbd5e1;
            padding: {{ $cellPadding }};
            font-size: {{ $tdFontSize }};
            vertical-align: middle;
        }
        table.client-report tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.client-report td.td-name {
            text-align: left;
            font-weight: bold;
            font-size: {{ $nameFontSize }};
            color: #0f172a;
            padding-left: 6px;
            padding-right: 4px;
            word-break: normal;
            overflow-wrap: break-word;
            line-height: 1.2;
        }
        table.client-report td.td-count {
            text-align: right;
            font-weight: bold;
            color: #1e3a8a;
            background-color: #eff6ff;
            padding-right: 4px;
            border-left: 1.5px solid #94a3b8;
            border-right: 1.5px solid #94a3b8;
        }
        table.client-report td.td-level {
            text-align: center;
            font-weight: bold;
            color: #0f172a;
        }
        table.client-report td.td-level.zero {
            font-weight: normal;
            color: #94a3b8;
        }
        table.client-report td.td-pct {
            text-align: right;
            padding-right: 5px;
            white-space: nowrap;
            font-weight: 600;
            color: #334155;
            border-left: 1.5px solid #94a3b8;
        }
        table.client-report tfoot td {
            font-weight: bold;
            background-color: #e2e8f0;
            color: #0f172a;
            border-top: 2px solid #1e3a8a;
            font-size: {{ $tdFontSize }};
            padding-top: 5px;
            padding-bottom: 5px;
        }
        table.client-report tfoot td.td-count {
            background-color: #dbeafe;
            color: #1e3a8a;
        }

        /* Legend Box */
        .legend-box {
            margin-top: 5px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 4px 7px;
            page-break-inside: avoid;
        }
        .legend-title {
            font-size: 7pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }
        table.legend-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.legend-table td {
            font-size: 6.4pt;
            color: #334155;
            padding: 1px 3px;
            vertical-align: top;
            width: 20%;
        }
        .legend-code {
            font-weight: bold;
            color: #0f172a;
        }

        .footer {
            margin-top: 4px;
            color: #64748b;
            font-size: 7pt;
            text-align: right;
        }
    </style>
</head>
<body>
    <!-- Header Banner -->
    <table class="header-banner">
        <tr>
            <td>
                <div class="header-title">Recruitment Report</div>
                <div class="header-meta">
                    Scope: <strong>{{ $scopeLabel }}</strong> &nbsp;|&nbsp; Period: <strong>{{ $periodText }}</strong>
                </div>
            </td>
            <td class="header-right">
                Generated {{ now()->format('d M Y, h:i A') }}
            </td>
        </tr>
    </table>

    <!-- KPI Summary Strip -->
    <table class="kpi-grid">
        <tr>
            <td class="kpi-card primary">
                <div class="kpi-label">Total Candidates</div>
                <div class="kpi-value">{{ number_format($total) }}</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-label">Clients Covered</div>
                <div class="kpi-value">{{ number_format($clientCount) }}</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-label">Recruiters</div>
                <div class="kpi-value">{{ number_format($recruiterCount) }}</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-label">Interview Stages</div>
                <div class="kpi-value">{{ number_format($levelCount) }}</div>
            </td>
        </tr>
    </table>

    <!-- Recruiter Report -->
    <div class="section">
        <table class="section-title-bar">
            <tr>
                <td><span class="section-title">Recruiter Report</span></td>
                <td class="section-subtitle">{{ $recruiterCount }} {{ $recruiterCount === 1 ? 'recruiter' : 'recruiters' }}</td>
            </tr>
        </table>
        <table class="std-report">
            <thead>
                <tr>
                    <th style="width: 52%;">Recruiter Name</th>
                    <th class="number" style="width: 12%;">Candidates</th>
                    <th style="width: 24%;">Share Distribution</th>
                    <th class="number" style="width: 12%;">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recruiterReport as $row)
                    <tr>
                        <td style="font-weight: bold;">{{ $row->label }}</td>
                        <td class="number" style="font-weight: bold;">{{ number_format($row->total) }}</td>
                        <td>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: {{ min(100, max(0, $row->percentage)) }}%;"></div>
                            </div>
                        </td>
                        <td class="number">{{ number_format($row->percentage, 2) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No candidate data found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Result</td>
                    <td class="number">{{ number_format($total) }}</td>
                    <td></td>
                    <td class="number">{{ $total ? '100.00%' : '0.00%' }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Level of Interview Report -->
    <div class="section">
        <table class="section-title-bar">
            <tr>
                <td><span class="section-title">Level of Interview Report</span></td>
                <td class="section-subtitle">Pipeline stage breakdown across {{ number_format($total) }} candidates</td>
            </tr>
        </table>

        @if($splitLevels)
            <table class="two-col-wrap">
                <tr>
                    <td class="col-pane">
                        <table class="std-report">
                            <thead>
                                <tr>
                                    <th style="width: 54%;">Interview Stage</th>
                                    <th class="number" style="width: 14%;">Count</th>
                                    <th style="width: 16%;">Share</th>
                                    <th class="number" style="width: 16%;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($levelLeft as $row)
                                    <tr>
                                        <td>{{ $row->label }}</td>
                                        <td class="number" style="font-weight: bold;">{{ number_format($row->total) }}</td>
                                        <td>
                                            <div class="bar-track">
                                                <div class="bar-fill" style="width: {{ min(100, max(0, $row->percentage)) }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="number">{{ number_format($row->percentage, 2) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                    <td class="col-gap"></td>
                    <td class="col-pane">
                        <table class="std-report">
                            <thead>
                                <tr>
                                    <th style="width: 54%;">Interview Stage</th>
                                    <th class="number" style="width: 14%;">Count</th>
                                    <th style="width: 16%;">Share</th>
                                    <th class="number" style="width: 16%;">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($levelRight as $row)
                                    <tr>
                                        <td>{{ $row->label }}</td>
                                        <td class="number" style="font-weight: bold;">{{ number_format($row->total) }}</td>
                                        <td>
                                            <div class="bar-track">
                                                <div class="bar-fill" style="width: {{ min(100, max(0, $row->percentage)) }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="number">{{ number_format($row->percentage, 2) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
            <table class="split-total-bar">
                <tr>
                    <td style="width: 70%;">Total Result (All {{ $levelCount }} Stages)</td>
                    <td style="width: 15%; text-align: right;">{{ number_format($total) }}</td>
                    <td style="width: 15%; text-align: right;">{{ $total ? '100.00%' : '0.00%' }}</td>
                </tr>
            </table>
        @else
            <table class="std-report">
                <thead>
                    <tr>
                        <th style="width: 52%;">Interview Stage</th>
                        <th class="number" style="width: 12%;">Count</th>
                        <th style="width: 24%;">Share Distribution</th>
                        <th class="number" style="width: 12%;">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($levelReport as $row)
                        <tr>
                            <td>{{ $row->label }}</td>
                            <td class="number" style="font-weight: bold;">{{ number_format($row->total) }}</td>
                            <td>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: {{ min(100, max(0, $row->percentage)) }}%;"></div>
                                </div>
                            </td>
                            <td class="number">{{ number_format($row->percentage, 2) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No candidate data found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total Result</td>
                        <td class="number">{{ number_format($total) }}</td>
                        <td></td>
                        <td class="number">{{ $total ? '100.00%' : '0.00%' }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    <!-- Client Report with all Interview Levels (Dedicated Page) -->
    <div class="page-break"></div>
    <div class="section">
        <table class="section-title-bar">
            <tr>
                <td><span class="section-title">Client Report &mdash; Stage-wise Breakdown</span></td>
                <td class="section-subtitle">{{ $clientCount }} {{ $clientCount === 1 ? 'client' : 'clients' }} &nbsp;|&nbsp; &ldquo;-&rdquo; indicates 0</td>
            </tr>
        </table>
        <table class="client-report">
            <thead>
                <tr>
                    <th class="th-name" style="width: {{ $nameWidth }}%;">Client Name</th>
                    <th class="th-count" style="width: {{ $countWidth }}%;">Total</th>
                    @foreach($levelReport as $lvl)
                        @php $meta = $levelMeta[$lvl->label]; @endphp
                        <th style="width: {{ number_format($levelWidth, 4, '.', '') }}%;">
                            {{ $meta['line1'] }}@if($meta['line2'] !== '')<br>{{ $meta['line2'] }}@endif
                        </th>
                    @endforeach
                    <th class="th-pct" style="width: {{ $pctWidth }}%;">Share</th>
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
                                {{ $cnt > 0 ? number_format($cnt) : '-' }}
                            </td>
                        @endforeach
                        <td class="td-pct">{{ number_format($row->percentage, 2) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $levelCount + 3 }}" style="text-align: center; padding: 12px;">No candidate data found.</td></tr>
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

        @if($levelCount > 0)
            <div class="legend-box">
                <div class="legend-title">Stage Column Legend</div>
                <table class="legend-table">
                    @foreach($levelReport->chunk(5) as $chunk)
                        <tr>
                            @foreach($chunk as $lvl)
                                @php $meta = $levelMeta[$lvl->label]; @endphp
                                <td><span class="legend-code">{{ $meta['code'] }}:</span> {{ $lvl->label }}</td>
                            @endforeach
                            @for($i = $chunk->count(); $i < 5; $i++)
                                <td></td>
                            @endfor
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>

    <div class="footer">Recruitment Report &bull; {{ $scopeLabel }} &bull; Generated {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
