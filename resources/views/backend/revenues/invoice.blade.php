<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $revenue->invoice_number }}</title>
    <style>@page { size: A4 portrait; margin: 0; } html, body { margin: 0; padding: 0; }</style>
</head>
<body>
    @include('backend.revenues.invoice-content')
</body>
</html>
