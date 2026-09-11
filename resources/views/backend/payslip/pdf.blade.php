<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $employee_no }} - {{ $month_name }} {{ $year }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 12mm 15mm 12mm;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 10.5px;
            color: #000000;
            background: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        .payslip-wrapper {
            border: 1.5px solid #000000;
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-bottom: 1.5px solid #000000;
        }

        .header-table td {
            padding: 8px 10px 6px 10px;
            vertical-align: middle;
            border: none;
        }

        .logo-box {
            width: 20%;
            text-align: left;
            vertical-align: middle;
        }

        .logo-img {
            width: 58px;
            height: auto;
            display: block;
        }

        .logo-text {
            color: #e52528;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 2px;
            text-transform: lowercase;
            font-family: Arial, sans-serif;
        }

        .company-info {
            width: 80%;
            text-align: center;
            padding-right: 15%;
        }

        .company-name {
            font-size: 13.5px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .company-address {
            font-size: 10.5px;
            line-height: 1.35;
        }

        .payslip-title {
            font-size: 12.5px;
            font-weight: bold;
            margin-top: 4px;
        }

        /* Employee Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            border: 1px solid #000000;
            padding: 3.5px 6px;
            font-size: 10px;
            vertical-align: middle;
        }

        .label-col-1 {
            width: 21%;
        }

        .val-col-1 {
            width: 32%;
        }

        .label-col-2 {
            width: 20%;
        }

        .val-col-2 {
            width: 27%;
        }

        /* Earnings & Deductions Table */
        .salary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .salary-table th {
            border: 1px solid #000000;
            padding: 3.5px 6px;
            font-size: 10px;
            font-weight: bold;
            text-align: left;
            background-color: transparent;
        }

        .salary-table td {
            border: 1px solid #000000;
            padding: 3px 6px;
            font-size: 10px;
            vertical-align: middle;
        }

        .col-earn { width: 23%; }
        .col-full { width: 13%; }
        .col-act1 { width: 14%; }
        .col-ded  { width: 32%; }
        .col-act2 { width: 18%; }

        .bold-text {
            font-weight: bold;
        }

        /* Net Pay Box */
        .net-pay-box {
            border: 1px solid #000000;
            border-top: none;
            padding: 12px 8px 18px 8px;
            font-size: 10.5px;
        }

        /* Remarks Box */
        .remarks-box {
            border-top: 1px solid #000000;
            padding: 4.5px 8px;
            font-size: 10px;
        }

        /* Footer */
        .footer-note {
            text-align: center;
            font-size: 9.5px;
            margin-top: 6px;
            color: #111111;
        }
    </style>
</head>
<body>

    <div class="payslip-wrapper">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td class="logo-box">
                    @if(!empty($logo_base64))
                        <img src="{{ $logo_base64 }}" class="logo-img" alt="Szorzo">
                    @endif
                    <div class="logo-text">szorzo</div>
                </td>
                <td class="company-info">
                    <div class="company-name">M/s. SZORZO Technologies Private Limited</div>
                    <div class="company-address">
                        No 81/1, 82/2, 1st Floor, Clayworks Shankara Campus,<br>
                        Doddakallasandra, Kanakapura Road, Bangalore - 560062
                    </div>
                    <div class="payslip-title">{{ $period_title }}</div>
                </td>
            </tr>
        </table>

        <!-- Employee Information -->
        <table class="info-table">
            <tr>
                <td class="label-col-1">Employee No:</td>
                <td class="val-col-1">{{ $employee_no }}</td>
                <td class="label-col-2">OT Hours</td>
                <td class="val-col-2">{{ $ot_hours }}</td>
            </tr>
            <tr>
                <td class="label-col-1">Name:</td>
                <td class="val-col-1">{{ $name }}</td>
                <td class="label-col-2">LOP:</td>
                <td class="val-col-2">{{ \App\Services\PayslipService::formatDays($lop) }}</td>
            </tr>
            <tr>
                <td class="label-col-1">Father Name</td>
                <td class="val-col-1">{{ $father_name }}</td>
                <td class="label-col-2">Gender:</td>
                <td class="val-col-2">{{ $gender }}</td>
            </tr>
            <tr>
                <td class="label-col-1">Designation:</td>
                <td class="val-col-1 bold-text">{{ $designation }}</td>
                <td class="label-col-2">PF No:</td>
                <td class="val-col-2">{{ $pf_no }}</td>
            </tr>
            <tr>
                <td class="label-col-1">Location:</td>
                <td class="val-col-1">{{ $location }}</td>
                <td class="label-col-2">PF UAN:</td>
                <td class="val-col-2">{{ $pf_uan }}</td>
            </tr>
            <tr>
                <td class="label-col-1">Effective Work Days:</td>
                <td class="val-col-1">{{ \App\Services\PayslipService::formatDays($effective_work_days) }}</td>
                <td class="label-col-2">ESI No:</td>
                <td class="val-col-2">{{ $esi_no }}</td>
            </tr>
            <tr>
                <td class="label-col-1">Employment Mode:</td>
                <td class="val-col-1 bold-text">{{ $mode_label }}</td>
                <td class="label-col-2">Client / Project:</td>
                <td class="val-col-2">{{ $client_name ?: 'SZORZO In-House' }}</td>
            </tr>
            <tr>
                <td class="label-col-1">Pay Period:</td>
                <td class="val-col-1">{{ $from_date_display }} to {{ $to_date_display }}</td>
                <td class="label-col-2">Period Days:</td>
                <td class="val-col-2">{{ $period_days }} Days</td>
            </tr>
        </table>

        <!-- Earnings and Deductions Table -->
        <table class="salary-table">
            <thead>
                <tr>
                    <th class="col-earn">Earnings</th>
                    <th class="col-full">Full</th>
                    <th class="col-act1">Actual</th>
                    <th class="col-ded">Deductions</th>
                    <th class="col-act2">Actual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-earn">{{ $earnings['basic']['label'] }}</td>
                    <td class="col-full">{{ \App\Services\PayslipService::formatAmount($earnings['basic']['full']) }}</td>
                    <td class="col-act1">{{ \App\Services\PayslipService::formatAmount($earnings['basic']['actual']) }}</td>
                    <td class="col-ded">{{ $deductions['pf']['label'] }}</td>
                    <td class="col-act2">{{ \App\Services\PayslipService::formatAmount($deductions['pf']['actual']) }}</td>
                </tr>
                <tr>
                    <td class="col-earn">{{ $earnings['hra']['label'] }}</td>
                    <td class="col-full">{{ \App\Services\PayslipService::formatAmount($earnings['hra']['full']) }}</td>
                    <td class="col-act1">{{ \App\Services\PayslipService::formatAmount($earnings['hra']['actual']) }}</td>
                    <td class="col-ded">{{ $deductions['esi']['label'] }}</td>
                    <td class="col-act2">{{ \App\Services\PayslipService::formatAmount($deductions['esi']['actual']) }}</td>
                </tr>
                <tr>
                    <td class="col-earn">{{ $earnings['conveyance']['label'] }}</td>
                    <td class="col-full">{{ \App\Services\PayslipService::formatAmount($earnings['conveyance']['full']) }}</td>
                    <td class="col-act1">{{ \App\Services\PayslipService::formatAmount($earnings['conveyance']['actual']) }}</td>
                    <td class="col-ded">{{ $deductions['pt']['label'] }}</td>
                    <td class="col-act2">{{ \App\Services\PayslipService::formatAmount($deductions['pt']['actual']) }}</td>
                </tr>
                <tr>
                    <td class="col-earn">{{ $earnings['medical']['label'] }}</td>
                    <td class="col-full">{{ \App\Services\PayslipService::formatAmount($earnings['medical']['full']) }}</td>
                    <td class="col-act1">{{ \App\Services\PayslipService::formatAmount($earnings['medical']['actual']) }}</td>
                    <td class="col-ded">{{ $deductions['income_tax']['label'] }}</td>
                    <td class="col-act2">{{ \App\Services\PayslipService::formatAmount($deductions['income_tax']['actual']) }}</td>
                </tr>
                <tr>
                    <td class="col-earn">{{ $earnings['special']['label'] }}</td>
                    <td class="col-full">{{ \App\Services\PayslipService::formatAmount($earnings['special']['full']) }}</td>
                    <td class="col-act1">{{ \App\Services\PayslipService::formatAmount($earnings['special']['actual']) }}</td>
                    <td class="col-ded">{{ $deductions['salary_advance']['label'] }}</td>
                    <td class="col-act2">{{ \App\Services\PayslipService::formatAmount($deductions['salary_advance']['actual']) }}</td>
                </tr>
                <tr>
                    <td class="col-earn">{{ $earnings['overtime']['label'] }}</td>
                    <td class="col-full">{{ \App\Services\PayslipService::formatAmount($earnings['overtime']['full']) }}</td>
                    <td class="col-act1">{{ \App\Services\PayslipService::formatAmount($earnings['overtime']['actual']) }}</td>
                    <td class="col-ded">{{ $deductions['fines']['label'] }}</td>
                    <td class="col-act2">{{ \App\Services\PayslipService::formatAmount($deductions['fines']['actual']) }}</td>
                </tr>
                <tr>
                    <td class="col-earn">{{ $earnings['lta']['label'] }}</td>
                    <td class="col-full">{{ \App\Services\PayslipService::formatAmount($earnings['lta']['full']) }}</td>
                    <td class="col-act1">{{ \App\Services\PayslipService::formatAmount($earnings['lta']['actual']) }}</td>
                    <td class="col-ded">{{ $deductions['lwf']['label'] }}</td>
                    <td class="col-act2">{{ \App\Services\PayslipService::formatAmount($deductions['lwf']['actual']) }}</td>
                </tr>
                <tr>
                    <td class="col-earn">{{ $earnings['arrears']['label'] }}</td>
                    <td class="col-full">{{ \App\Services\PayslipService::formatAmount($earnings['arrears']['full']) }}</td>
                    <td class="col-act1">{{ \App\Services\PayslipService::formatAmount($earnings['arrears']['actual']) }}</td>
                    <td class="col-ded">{{ $deductions['other']['label'] }}</td>
                    <td class="col-act2">{{ \App\Services\PayslipService::formatAmount($deductions['other']['actual']) }}</td>
                </tr>
                <tr>
                    <td class="col-earn bold-text">Total Earnings: INR.</td>
                    <td class="col-full bold-text">{{ \App\Services\PayslipService::formatAmount($total_earnings_full) }}</td>
                    <td class="col-act1 bold-text">{{ \App\Services\PayslipService::formatAmount($total_earnings_actual) }}</td>
                    <td class="col-ded bold-text">Total Deductions: INR.</td>
                    <td class="col-act2 bold-text">{{ \App\Services\PayslipService::formatAmount($total_deductions_actual) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Net Pay Box -->
        <div class="net-pay-box">
            Net Pay for the period: {{ \App\Services\PayslipService::formatAmount($net_pay) }}
        </div>

        <!-- Remarks Box -->
        <div class="remarks-box">
            <span class="bold-text">Remarks:</span> {{ $remarks }}
        </div>
    </div>

    <div class="footer-note">
        This is a system generated pay slip and does not require signature.
    </div>

</body>
</html>
