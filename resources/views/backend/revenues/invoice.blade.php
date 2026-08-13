<!doctype html>
<html><head><meta charset="utf-8"><title>{{ $revenue->invoice_number }}</title>
<style>
@page{size:A4 portrait;margin:16px 22px}*{box-sizing:border-box}body{font-family:DejaVu Sans,Arial,sans-serif;color:#000;font-size:10.5px;margin:0}
.sheet{width:100%;margin:auto;background:#fff;border:4px double #111;padding:14px 14px 18px}.header{padding:0 6px 10px}
.invoice-header{border:0}.invoice-header td{border:0;padding:0;vertical-align:middle}.company-heading{color:#ed1c24;font-size:14px;font-weight:bold;line-height:1.25}.tag{color:#ed1c24;font-size:12px;font-weight:bold;line-height:1.25}
.address{font-size:10.5px;line-height:1.35;padding-bottom:12px;border-bottom:1px solid #222}.header-logo{text-align:center;width:35%;padding-left:18px!important}.rhino-logo{display:block;width:100px;height:auto;margin:0 auto 2px}.szorzo-logo{display:block;width:210px;height:auto;margin:0 auto}.title{text-align:center;font-size:25px;font-weight:bold;margin:5px 0 10px}
table{width:100%;border-collapse:collapse}td,th{border:1px solid #555;padding:6px;vertical-align:top}.party td{width:50%;line-height:1.55}
.details td:first-child{width:70%}.details td:last-child{width:30%;text-align:right}.details th{background:#eee;text-align:left}
.amount{text-align:right!important}.words td:first-child{width:20%;font-weight:bold}.bank td:first-child{width:38%;font-weight:bold}
.notice{text-align:center;font-weight:bold;margin:10px 0}.signature{text-align:right;line-height:1.7;margin-top:15px}
.label{font-weight:bold}.candidate-lines{line-height:1.8;margin-top:5px}
</style></head><body><div class="sheet">
    <div class="header">
        <table class="invoice-header"><tr>
            <td>
                <div class="company-heading">SZORZO Technologies Private Limited</div>
                <div class="tag">Let’s Build Together. Multiply Faster.</div>
                <div class="address">No 81/1, 82/2, 1st Floor, Clayworks Shankara Campus,<br>Doddakallasandra, Kanakapura Road, Bangalore - 560062<br>Desk: +91 990 141 9393 | Email: <u>business@szorzo.com</u><br>Social : www.szorzo.com</div>
            </td>
            <td class="header-logo">
                <img class="rhino-logo" src="data:image/webp;base64,{{ base64_encode(file_get_contents(public_path('frontend/images/rhino-logo.webp'))) }}" alt="Szorzo Rhino Logo">
                <img class="szorzo-logo" src="data:image/webp;base64,{{ base64_encode(file_get_contents(public_path('frontend/images/logo-bg.webp'))) }}" alt="Szorzo Logo">
            </td>
        </tr></table>
    </div>
    <div class="title">TAX INVOICE</div>
    <table class="party"><tr>
        <td><span class="label">To,</span><br><strong>{{ strtoupper($revenue->client_name) }}</strong><br>{!! nl2br(e($revenue->client_address)) !!}<br>@if($revenue->client_gst_number)<strong>GST Number: {{ $revenue->client_gst_number }}</strong>@endif</td>
        <td><span class="label">From,</span><br><strong>SZORZO TECHNOLOGIES PRIVATE LIMITED</strong><br>
            SZ UNIVERSE NUMBER : {{ $revenue->universe_number ?: '-' }}<br>SZ INVOICE NUMBER : {{ $revenue->invoice_number }}<br>
            SZ GST NUMBER : 29ABQCS7001R1ZG<br>SZ INVOICE DATE : {{ $revenue->invoice_date->format('d-m-Y') }}<br>
            SZ CIN Number : U62099KA2025PTC205182<br>HSN/SAC Code : 9985
        </td>
    </tr></table>
    <table class="details"><thead><tr><th>DESCRIPTION</th><th>AMOUNT IN RUPEES</th></tr></thead><tbody>
        <tr><td><strong>Recruitment Service Charges</strong><div class="candidate-lines">
            Candidate Name : {{ $revenue->candidate->candidate_name }}<br>
            Onboarding CTC : Rs {{ number_format((float)$revenue->onboarding_ctc, 2) }}/-<br>
            Date of Joining : {{ $revenue->candidate->onboarding_date?->format('d-m-Y') ?? '-' }}
        </div></td><td class="amount">{{ number_format((float)$revenue->service_amount, 2, '.', '') }}</td></tr>
        <tr><td><strong>GST @ {{ rtrim(rtrim(number_format((float)$revenue->gst_percentage, 2), '0'), '.') }}%</strong></td><td class="amount">{{ number_format((float)$revenue->gst_amount, 2, '.', '') }}</td></tr>
        <tr><td><strong>Total Billing Amount</strong></td><td class="amount"><strong>{{ number_format((float)$revenue->total_amount, 2, '.', '') }}</strong></td></tr>
    </tbody></table>
    <table class="words"><tr><td>In Words</td><td>{{ $amountInWords }}</td></tr></table>
    <div class="notice">Requesting you to Transfer the Funds to the below mentioned Bank Account Details.</div>
    <table class="bank">
        <tr><td>SZORZO Pan Card Number</td><td>ABQCS7001R</td></tr>
        <tr><td>SZORZO GST Number</td><td>29ABQCS7001R1ZG</td></tr>
        <tr><td>Account Full Name</td><td>SZORZO Technologies Private Limited</td></tr>
        <tr><td>Bank Account Name</td><td>IDFC Bank Ltd</td></tr>
        <tr><td>Bank Account Number</td><td>2326102000000365</td></tr>
        <tr><td>IFSC Code</td><td>IBKL0002326</td></tr>
    </table>
    @if($revenue->notes)<p><strong>Notes:</strong> {{ $revenue->notes }}</p>@endif
    <div class="signature">For SZORZO Technologies Private Limited,<br><br><br><strong>Kannan PC</strong><br>Director: Administration</div>
</div></body></html>
