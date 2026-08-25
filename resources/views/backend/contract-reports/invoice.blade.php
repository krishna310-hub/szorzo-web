<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoiceNumber }}</title>
    <style>
        @page { size: A4 portrait; margin: 28px; }
        body { margin: 0; color: #20242a; font: 12px DejaVu Sans, sans-serif; }
        .header { background: #991b1b; color: #fff; padding: 24px 28px; }
        .header-table, .details, .summary { width: 100%; border-collapse: collapse; }
        .brand { font-size: 22px; font-weight: bold; }
        .invoice-title { text-align: right; font-size: 25px; font-weight: bold; }
        .invoice-number { text-align: right; margin-top: 5px; }
        .section { margin-top: 22px; }
        .label { color: #6b7280; font-size: 9px; text-transform: uppercase; letter-spacing: .7px; }
        .value { margin-top: 4px; font-weight: bold; font-size: 13px; }
        .details td { width: 33.33%; padding: 10px 14px; border: 1px solid #d8dde5; vertical-align: top; }
        .summary th, .summary td { padding: 11px; border: 1px solid #d8dde5; }
        .summary th { background: #f2f4f7; text-align: left; }
        .num { text-align: right; }
        .total td { background: #fff1f2; color: #991b1b; font-size: 15px; font-weight: bold; }
        .formula { margin-top: 10px; padding: 10px 12px; background: #f8fafc; color: #64748b; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; border-top: 1px solid #ddd; padding-top: 8px; color: #777; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
@php($candidate = $contractReport->candidate)
<div class="header">
    <table class="header-table"><tr>
        <td><div class="brand">{{ $settings['app_name'] ?? config('app.name') }}</div><div>Contract Candidate Monthly Invoice</div></td>
        <td><div class="invoice-title">INVOICE</div><div class="invoice-number">{{ $invoiceNumber }}</div></td>
    </tr></table>
</div>

<div class="section">
    <table class="details">
        <tr>
            <td><div class="label">Candidate</div><div class="value">{{ $candidate?->candidate_name ?? '—' }}</div><div>{{ $candidate?->email }}</div><div>{{ $candidate?->mobile_no }}</div></td>
            <td><div class="label">Client</div><div class="value">{{ $candidate?->client?->client ?? '—' }}</div><div>{{ $candidate?->jobRole?->job_role ?? '—' }}</div></td>
            <td><div class="label">Invoice period</div><div class="value">{{ $contractReport->salary_month->format('F Y') }}</div><div>Issued: {{ now()->format('d M Y') }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Recruiter</div><div class="value">{{ $candidate?->recruiter?->recruiter_name ?? '—' }}</div></td>
            <td><div class="label">Engagement mode</div><div class="value">Contract</div></td>
            <td><div class="label">Calendar days</div><div class="value">{{ $contractReport->salary_month->daysInMonth }}</div></td>
        </tr>
    </table>
</div>

<div class="section">
    <table class="summary">
        <thead><tr><th>Description</th><th class="num">Present</th><th class="num">Leave Days</th><th class="num">Worked Hours</th><th class="num">Amount</th></tr></thead>
        <tbody>
            <tr><td>Monthly contract take-home</td><td class="num">{{ $contractReport->present_days }}</td><td class="num">{{ $contractReport->absent_days }}</td><td class="num">{{ $contractReport->worked_hours !== null ? number_format($contractReport->worked_hours, 2) : '—' }}</td><td class="num">INR {{ number_format($contractReport->monthly_take_home, 2) }}</td></tr>
            <tr class="total"><td colspan="4">Net Payable Amount</td><td class="num">INR {{ number_format($contractReport->payable_salary, 2) }}</td></tr>
        </tbody>
    </table>
    @if ($contractReport->worked_hours !== null)
        <div class="formula">Hourly calculation: INR {{ number_format($contractReport->monthly_take_home, 2) }} / ({{ $contractReport->salary_month->daysInMonth }} days × 8 hours) × {{ number_format($contractReport->worked_hours, 2) }} worked hours.</div>
    @else
        <div class="formula">Calculation: INR {{ number_format($contractReport->monthly_take_home, 2) }} / {{ $contractReport->salary_month->daysInMonth }} days × {{ $contractReport->present_days }} present days.</div>
    @endif
</div>

<div class="footer">Computer-generated contract invoice &middot; {{ $invoiceNumber }}</div>
</body>
</html>
