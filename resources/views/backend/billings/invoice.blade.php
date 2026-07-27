<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $billing->invoice_number ?: '#'.$billing->id }}</title>
    <style>
        body{font-family:Arial,sans-serif;color:#253046;margin:40px}.invoice{max-width:760px;margin:auto;border:1px solid #dde3ea;padding:36px}
        h1{margin:0;color:#1c3faa}.top{display:flex;justify-content:space-between;border-bottom:2px solid #1c3faa;padding-bottom:20px;margin-bottom:28px}
        table{width:100%;border-collapse:collapse;margin-top:25px}th,td{padding:13px;border:1px solid #dde3ea;text-align:left}th{background:#f5f7fb}
        .amount{text-align:right;font-size:22px;font-weight:bold;margin-top:25px}.muted{color:#687386;white-space:pre-wrap}
    </style>
</head>
<body>
<main class="invoice">
    <div class="top"><div><h1>INVOICE</h1><p>Szorzo</p></div><div><strong>{{ $billing->invoice_number ?: '#'.$billing->id }}</strong><br>{{ $billing->invoice_date?->format('d M Y') ?: 'Date not specified' }}</div></div>
    <h2>{{ $billing->title ?: 'Billing Invoice' }}</h2>
    <table><tr><th>Billing percentage</th><td>{{ rtrim(rtrim(number_format((float)$billing->value, 2), '0'), '.') }}%</td></tr><tr><th>Status</th><td>{{ $billing->status ? 'Active' : 'Inactive' }}</td></tr></table>
    @if($billing->amount !== null)<div class="amount">Total: ₹{{ number_format((float)$billing->amount, 2) }}</div>@endif
    @if($billing->notes)<h3>Notes</h3><p class="muted">{{ $billing->notes }}</p>@endif
</main>
</body>
</html>
