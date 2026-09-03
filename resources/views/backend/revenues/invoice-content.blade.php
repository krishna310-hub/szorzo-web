<style>
    @page { size: A4 portrait; margin: 0; }
    .invoice-page, .invoice-page * { box-sizing: content-box !important; }
    .invoice-page {
        position: relative;
        width: 168.86mm;
        height: 271.36mm;
        margin: 8.47mm 0 0 8.47mm;
        padding: 3.1mm 7.8mm 4mm 14.8mm;
        overflow: hidden;
        color: #000;
        background: #fff;
        border: 2.25pt double #000;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10pt;
        line-height: 1.08;
    }
    .invoice-page table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .invoice-page td, .invoice-page th { padding: 0; vertical-align: top; }
    .invoice-header { height: 35.3mm; }
    .invoice-header td { border: 0; }
    .company-block { width: 62%; padding: 0 0 2.4mm 1.3mm !important; vertical-align: top !important; }
    .logo-block { width: 38%; text-align: right; vertical-align: top !important; }
    .company-name, .company-tag { color: #ed1c24; font-weight: 700; }
    .company-name { margin-top: 1.4mm; font-size: 10.5pt; }
    .company-tag { font-size: 10pt; margin-top: .25mm; }
    .company-address { margin-top: .55mm; font-size: 8.9pt; line-height: 1.25; }
    .company-address a { color: #0563c1; text-decoration: underline; }
    .company-rule { width: 96%; margin-top: 2.9mm; border-bottom: .55pt solid #9b9b9b; }
    .logo-lockup { display: inline-block; width: 52.32mm; height: 25.54mm; margin: 2.5mm 8.8mm 0 0; }
    .invoice-title { height: 12.6mm; margin: 0; padding-top: .6mm; text-align: center; font-size: 24pt; line-height: 1; font-weight: 700; }
    .main-grid { border: .55pt solid #606060; }
    .main-grid col:first-child { width: 42.62%; }
    .main-grid col:last-child { width: 57.38%; }
    .main-grid td, .main-grid th { border: .55pt solid #606060; }
    .party-row td { height: 39.7mm; padding: 1.2mm 1.4mm; font-size: 9.7pt; line-height: 1.15; }
    .party-label { display: block; margin-bottom: 3.8mm; }
    .party-name { display: block; font-weight: 700; line-height: 1.08; margin-bottom: 3.4mm; }
    .party-address { line-height: 1.17; }
    .party-gst { display: block; margin-top: 4.2mm; }
    .from-name { display: block; margin-top: 3.6mm; margin-bottom: 2.2mm; font-weight: 700; }
    .from-lines { width: 100%; border: 0 !important; }
    .from-lines td { height: auto !important; padding: 0 !important; border: 0 !important; line-height: 1.12; }
    .from-lines td:first-child { width: 46%; white-space: nowrap; }
    .from-lines td:nth-child(2) { width: 4%; text-align: center; }
    .from-lines td:last-child { width: 50%; }
    .description-head th { height: 6.7mm; padding-top: 3mm; text-align: center; font-size: 11.5pt; line-height: 1; font-weight: 700; background: #fff; }
    .service-row td { height: 20.5mm; padding: .6mm 1.3mm; font-size: 10pt; }
    .service-heading { font-weight: 700; margin-bottom: 2.3mm; }
    .candidate-detail { width: 100%; border: 0 !important; }
    .candidate-detail td { height: auto !important; padding: 0 !important; border: 0 !important; line-height: 1.2; }
    .candidate-detail td:first-child { width: 36%; white-space: nowrap; }
    .candidate-detail td:nth-child(2) { width: 5%; text-align: center; }
    .candidate-detail td:last-child { width: 59%; }
    .amount-cell { text-align: center; vertical-align: top !important; padding-top: 6.3mm !important; font-weight: 700; }
    .total-row td { height: 3.4mm; padding: .4mm 1.3mm; font-weight: 700; }
    .total-row td:last-child { text-align: center; }
    .words-row td { height: 8.6mm; padding: 3.1mm 1.3mm 1mm; }
    .words-row td:first-child { font-weight: 700; font-style: italic; }
    .words-row td:last-child { text-align: center; font-weight: 700; font-style: italic; font-size: 9.5pt; }
    .transfer-note { height: 3mm; padding: 4mm 1.3mm 0; font-size: 8.5pt; font-weight: 700; }
    .bank-table { width: 98.2% !important; margin: 6.8mm 0 0 1.3mm; border-collapse: collapse; }
    .bank-table col { width: 50%; }
    .bank-table td { height: 3.95mm; padding: .45mm 1.1mm .25mm; border-top: .55pt solid #686868; border-bottom: .55pt solid #b0b0b0; border-left: .55pt solid #686868; font-size: 9.5pt; line-height: 1; font-weight: 700; }
    .bank-table td:last-child { border-right: 0; }
    .signature { margin: 3.2mm 0 0 1.3mm; font-size: 8.8pt; line-height: 1.18; }
    .signature-heading { font-weight: 700; }
    .signature-name { margin-top: 18.5mm; font-weight: 700; }
    .signature-role { margin-top: .6mm; }
    .invoice-preview-shell .invoice-page { margin-top: 0; box-shadow: 0 2px 12px rgba(15, 23, 42, .14); }
</style>

<div class="invoice-page">
    <table class="invoice-header">
        <tr>
            <td class="company-block">
                <div class="company-name">SZORZO Technologies Private Limited</div>
                <div class="company-tag">Let’s Build Together. Multiply Faster.</div>
                <div class="company-address">
                    No 81/1, 82/2, 1st Floor, Clayworks Shankara Campus,<br>
                    Doddakallasandra, Kanakapura Road, Bangalore - 560062<br>
                    Desk: +91 990 141 9393 | Email: <a href="mailto:business@szorzo.com">business@szorzo.com</a><br>
                    Social : www.szorzo.com
                </div>
                <div class="company-rule"></div>
            </td>
            <td class="logo-block">
                <img class="logo-lockup" src="data:image/png;base64,{{ trim(file_get_contents(resource_path('assets/invoice-logo.b64'))) }}" alt="SZORZO">
            </td>
        </tr>
    </table>

    <h1 class="invoice-title">TAX INVOICE</h1>

    <table class="main-grid">
        <colgroup><col><col></colgroup>
        <tr class="party-row">
            <td>
                <span class="party-label">To,</span>
                <span class="party-name">{{ strtoupper($revenue->client_name) }}</span>
                <span class="party-address">{!! nl2br(e($revenue->client_address)) !!}</span>
                @if($revenue->client_gst_number)
                    <span class="party-gst">GST Number: {{ $revenue->client_gst_number }}</span>
                @endif
            </td>
            <td>
                <span class="party-label">From,</span>
                <span class="from-name">SZORZO TECHNOLOGIES PRIVATE LIMITED</span>
                <table class="from-lines">
                    <tr><td>SZ UNIVERSE NUMBER</td><td>:</td><td>{{ $revenue->universe_number ?: '-' }}</td></tr>
                    <tr><td>SZ INVOICE NUMBER</td><td>:</td><td>{{ $revenue->invoice_number }}</td></tr>
                    <tr><td>SZ GST NUMBER</td><td>:</td><td>29ABQCS7001R1ZG</td></tr>
                    <tr><td>SZ INVOICE DATE</td><td>:</td><td>{{ $revenue->invoice_date->format('d-m-Y') }}</td></tr>
                    <tr><td>SZ CIN Number</td><td>:</td><td>U62099KA2025PTC205182</td></tr>
                    <tr><td>HSN/SAC Code</td><td>:</td><td>9985</td></tr>
                </table>
            </td>
        </tr>
        <tr class="description-head">
            <th>DESCRIPTION</th>
            <th>AMOUNT IN RUPEES</th>
        </tr>
        <tr class="service-row">
            <td>
                @if($revenue->candidates->count() > 1)
                    <div class="service-heading">Contract Staffing Charges</div>
                    @foreach($revenue->candidates as $cand)
                        <table class="candidate-detail" style="margin-bottom: 6px; border-bottom: 1px dashed #ccc; padding-bottom: 4px;">
                            <tr><td style="width: 140px;">Candidate Name</td><td style="width: 10px;">:</td><td><strong>{{ $cand->candidate_name }}</strong> ({{ $cand->jobRole?->job_role ?? '-' }})</td></tr>
                            <tr><td>Payable Salary / CTC</td><td>:</td><td>Rs {{ number_format((float) ($cand->pivot->payable_salary ?? $cand->onboarding_ctc ?? $cand->take_home), 2) }}/-</td></tr>
                            <tr><td>Date of Joining</td><td>:</td><td>{{ $cand->onboarding_date?->format('d-m-Y') ?? ($cand->contract_from_date?->format('d-m-Y') ?? '-') }}</td></tr>
                        </table>
                    @endforeach
                @else
                    @php
                        $singleCand = $revenue->candidates->first() ?? $revenue->candidate;
                    @endphp
                    <div class="service-heading">Recruitment Service Charges</div>
                    <table class="candidate-detail">
                        <tr><td>Candidate Name</td><td>:</td><td>{{ $singleCand?->candidate_name ?? '-' }}</td></tr>
                        <tr><td>Offered CTC</td><td>:</td><td>Rs {{ $offeredCtcDisplay }}/-</td></tr>
                        <tr><td>Date of Joining</td><td>:</td><td>{{ $singleCand?->onboarding_date?->format('d-m-Y') ?? ($singleCand?->contract_from_date?->format('d-m-Y') ?? '-') }}</td></tr>
                    </table>
                @endif
            </td>
            <td class="amount-cell">{{ number_format((float) $revenue->service_amount, 2, '.', '') }}</td>
        </tr>
        <tr class="total-row">
            <td>GST @ {{ rtrim(rtrim(number_format((float) $revenue->gst_percentage, 2), '0'), '.') }}%</td>
            <td>{{ number_format((float) $revenue->gst_amount, 2, '.', '') }}</td>
        </tr>
        <tr class="total-row">
            <td>Total Billing Amount</td>
            <td>{{ number_format((float) $revenue->total_amount, 2, '.', '') }}</td>
        </tr>
        <tr class="words-row">
            <td>In Words</td>
            <td>{{ $amountInWords }}</td>
        </tr>
    </table>

    <div class="transfer-note">Requesting you to Transfer the Funds to the below mentioned Bank Account Details.</div>

    <table class="bank-table">
        <colgroup><col><col></colgroup>
        <tr><td>SZORZO Pan Card Number</td><td>ABQCS7001R</td></tr>
        <tr><td>SZORZO GST Number</td><td>29ABQCS7001R1ZG</td></tr>
        <tr><td>Account Full Name</td><td>SZORZO Technologies Private Limited</td></tr>
        <tr><td>Bank Account Name</td><td>IDFC Bank Ltd</td></tr>
        <tr><td>Bank Account Number</td><td>2326102000000365</td></tr>
        <tr><td>IFSC Code</td><td>IBKL0002326</td></tr>
    </table>

    <div class="signature">
        <div class="signature-heading">For SZORZO Technologies Private Limited,</div>
        <div class="signature-name">Kannan PC</div>
        <div class="signature-role">Director: Administration</div>
    </div>
</div>
