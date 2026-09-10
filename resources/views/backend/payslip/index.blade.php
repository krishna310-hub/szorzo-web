@extends('backend.layouts.master')
@section('title', 'Payslip')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Employee Payslip</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Payslip</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Action Card -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.payslip.index') }}" class="row g-3 align-items-end">
                        @if($can_manage_all)
                            <div class="col-lg-3 col-md-4">
                                <label class="form-label fw-semibold">Select Employee</label>
                                <select name="user_id" class="form-select">
                                    @foreach($all_users as $u)
                                        <option value="{{ $u->id }}" @selected($target_user->id == $u->id)>
                                            {{ $u->name }} ({{ $u->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-lg-2 col-md-3">
                            <label class="form-label fw-semibold">Month</label>
                            <select name="month" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" @selected($month == $m)>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-3">
                            <label class="form-label fw-semibold">Year</label>
                            <select name="year" class="form-select">
                                @for($y = date('Y') + 1; $y >= 2024; $y--)
                                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-lg-auto col-md-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-filter-3-line align-bottom me-1"></i> View Payslip
                            </button>

                            <a href="{{ route('admin.payslip.download', request()->query()) }}" class="btn btn-success">
                                <i class="ri-download-2-line align-bottom me-1"></i> Download PDF
                            </a>

                            <a href="{{ route('admin.payslip.preview', request()->query()) }}" target="_blank" class="btn btn-outline-secondary">
                                <i class="ri-printer-line align-bottom me-1"></i> Print / Preview
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Attendance & Leave Breakdown Badges -->
            <div class="card border-0 shadow-sm bg-light-subtle mb-4">
                <div class="card-body">
                    <h6 class="fs-14 mb-3 fw-bold text-muted text-uppercase">
                        <i class="ri-calendar-check-line align-middle me-1"></i> Attendance Integration Summary ({{ $full_month_name }} {{ $year }})
                    </h6>
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="fs-11 text-muted text-uppercase d-block">Days in Month</span>
                                <span class="fs-16 fw-bold text-dark">{{ $days_in_month }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="fs-11 text-success text-uppercase d-block">Present Days</span>
                                <span class="fs-16 fw-bold text-success">{{ $attendance_stats['present'] }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="fs-11 text-warning text-uppercase d-block">Half Days (0.5)</span>
                                <span class="fs-16 fw-bold text-warning">{{ $attendance_stats['half_day'] }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="fs-11 text-info text-uppercase d-block">Leaves / Offs</span>
                                <span class="fs-16 fw-bold text-info">{{ $attendance_stats['on_leave'] + $attendance_stats['holiday'] + $attendance_stats['week_off'] }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-white">
                                <span class="fs-11 text-danger text-uppercase d-block">Loss of Pay (LOP)</span>
                                <span class="fs-16 fw-bold text-danger">{{ \App\Services\PayslipService::formatDays($lop) }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="p-2 border rounded bg-white border-primary">
                                <span class="fs-11 text-primary text-uppercase d-block fw-semibold">Effective Work Days</span>
                                <span class="fs-16 fw-bold text-primary">{{ \App\Services\PayslipService::formatDays($effective_work_days) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payslip Paper Preview Card -->
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="card shadow">
                        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Payslip Preview: {{ $month_name }} {{ $year }}</h5>
                            <span class="badge bg-success-subtle text-success fs-12">Net Pay: INR {{ number_format($net_pay, 0) }}</span>
                        </div>
                        <div class="card-body p-4 bg-white">

                            <!-- Visual Paper Design Container -->
                            <div class="payslip-preview-container" style="border: 1.5px solid #000; width: 100%; margin: 0 auto; font-family: Arial, sans-serif; color: #000; font-size: 13px;">

                                <!-- Header -->
                                <table style="width: 100%; border-bottom: 1.5px solid #000; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 20%; padding: 12px 15px; vertical-align: middle;">
                                            @if(!empty($logo_base64))
                                                <img src="{{ $logo_base64 }}" style="width: 65px; height: auto;" alt="Szorzo">
                                            @else
                                                <img src="{{ asset('frontend/images/rhino-logo.png') }}" style="width: 65px; height: auto;" alt="Szorzo">
                                            @endif
                                            <div style="color: #e52528; font-size: 18px; font-weight: bold; letter-spacing: 2px; margin-top: 4px; font-family: Arial, sans-serif;">szorzo</div>
                                        </td>
                                        <td style="width: 80%; text-align: center; padding: 12px 15px 12px 0;">
                                            <div style="font-size: 17px; font-weight: bold; margin-bottom: 4px;">M/s. SZORZO Technologies Private Limited</div>
                                            <div style="font-size: 13px; line-height: 1.4;">
                                                No 81/1, 82/2, 1st Floor, Clayworks Shankara Campus,<br>
                                                Doddakallasandra, Kanakapura Road, Bangalore - 560062
                                            </div>
                                            <div style="font-size: 15px; font-weight: bold; margin-top: 6px;">Payslip for the month of {{ $month_name }} {{ $year }}</div>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Employee Details Table -->
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 22%; border: 1px solid #000; padding: 6px 10px;">Employee No:</td>
                                        <td style="width: 30%; border: 1px solid #000; padding: 6px 10px;">{{ $employee_no }}</td>
                                        <td style="width: 20%; border: 1px solid #000; padding: 6px 10px;">OT Hours</td>
                                        <td style="width: 28%; border: 1px solid #000; padding: 6px 10px;">{{ $ot_hours }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">Name:</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">{{ $name }}</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">LOP:</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">{{ \App\Services\PayslipService::formatDays($lop) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">Father Name</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">{{ $father_name }}</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">Gender:</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">{{ $gender }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">Designation:</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px; font-weight: bold;">{{ $designation }}</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">PF No:</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">{{ $pf_no }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">Location:</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">{{ $location }}</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">PF UAN:</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">{{ $pf_uan }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">Effective Work Days:</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">{{ \App\Services\PayslipService::formatDays($effective_work_days) }}</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">ESI No:</td>
                                        <td style="border: 1px solid #000; padding: 6px 10px;">{{ $esi_no }}</td>
                                    </tr>
                                </table>

                                <!-- Earnings and Deductions Table -->
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: transparent;">
                                            <th style="width: 23%; border: 1px solid #000; padding: 6px 10px; font-weight: bold; text-align: left;">Earnings</th>
                                            <th style="width: 13%; border: 1px solid #000; padding: 6px 10px; font-weight: bold; text-align: left;">Full</th>
                                            <th style="width: 14%; border: 1px solid #000; padding: 6px 10px; font-weight: bold; text-align: left;">Actual</th>
                                            <th style="width: 32%; border: 1px solid #000; padding: 6px 10px; font-weight: bold; text-align: left;">Deductions</th>
                                            <th style="width: 18%; border: 1px solid #000; padding: 6px 10px; font-weight: bold; text-align: left;">Actual</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $earnings['basic']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['basic']['full']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['basic']['actual']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $deductions['pf']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($deductions['pf']['actual']) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $earnings['hra']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['hra']['full']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['hra']['actual']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $deductions['esi']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($deductions['esi']['actual']) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $earnings['conveyance']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['conveyance']['full']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['conveyance']['actual']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $deductions['pt']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($deductions['pt']['actual']) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $earnings['medical']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['medical']['full']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['medical']['actual']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $deductions['income_tax']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($deductions['income_tax']['actual']) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $earnings['special']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['special']['full']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['special']['actual']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $deductions['salary_advance']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($deductions['salary_advance']['actual']) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $earnings['overtime']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['overtime']['full']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['overtime']['actual']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $deductions['fines']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($deductions['fines']['actual']) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $earnings['lta']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['lta']['full']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['lta']['actual']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $deductions['lwf']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($deductions['lwf']['actual']) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $earnings['arrears']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['arrears']['full']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($earnings['arrears']['actual']) }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ $deductions['other']['label'] }}</td>
                                            <td style="border: 1px solid #000; padding: 5px 10px;">{{ \App\Services\PayslipService::formatAmount($deductions['other']['actual']) }}</td>
                                        </tr>
                                        <tr style="font-weight: bold;">
                                            <td style="border: 1px solid #000; padding: 6px 10px;">Total Earnings: INR.</td>
                                            <td style="border: 1px solid #000; padding: 6px 10px;">{{ \App\Services\PayslipService::formatAmount($total_earnings_full) }}</td>
                                            <td style="border: 1px solid #000; padding: 6px 10px;">{{ \App\Services\PayslipService::formatAmount($total_earnings_actual) }}</td>
                                            <td style="border: 1px solid #000; padding: 6px 10px;">Total Deductions: INR.</td>
                                            <td style="border: 1px solid #000; padding: 6px 10px;">{{ \App\Services\PayslipService::formatAmount($total_deductions_actual) }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Net Pay Box -->
                                <div style="border: 1px solid #000; border-top: none; padding: 16px 10px; font-size: 14px; font-weight: normal;">
                                    Net Pay for the month: <strong class="fs-15 text-dark ms-1">{{ \App\Services\PayslipService::formatAmount($net_pay) }}</strong>
                                </div>

                                <!-- Remarks Box -->
                                <div style="border-top: 1px solid #000; padding: 8px 10px; font-size: 13px;">
                                    <strong>Remarks:</strong> {{ $remarks }}
                                </div>
                            </div>

                            <div style="text-align: center; font-size: 12px; margin-top: 12px; color: #555;">
                                This is a system generated pay slip and does not require signature.
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
