@extends('backend.layouts.master')

@section('title', 'Recruitment Dashboard')

@section('content')
    <style>
        .dashboard-shell {
            background: linear-gradient(145deg, #f8fafc 0%, #fff7f7 48%, #f1f5f9 100%);
            min-height: 100vh;
        }

        .dashboard-hero {
            border-radius: 24px;
            color: #fff;
            background: linear-gradient(120deg, #171923, #4a1621 65%, #b91c1c);
            box-shadow: 0 18px 45px rgba(31, 41, 55, .16);
            overflow: hidden;
            position: relative;
        }

        .dashboard-hero:after {
            content: '';
            position: absolute;
            width: 260px;
            height: 260px;
            right: -65px;
            top: -100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .09);
        }

        .scope-pill {
            display: inline-flex;
            gap: 7px;
            align-items: center;
            padding: 7px 12px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .13);
            font-size: .8rem;
        }

        .metric-card,
        .panel-card {
            border: 0;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, .06);
            height: 100%;
        }

        .metric-card {
            transition: transform .2s, box-shadow .2s;
            overflow: hidden;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 35px rgba(15, 23, 42, .1);
        }

        .metric-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            /* background: #fde8e8; */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: visible;
        }

        .metric-logo {
            width: 80px;
            height: auto;
            transform: translate(0px, 8px);
        }

        .metric-value {
            font-size: 1.9rem;
            font-weight: 800;
            color: #111827;
            line-height: 1;
        }

        .metric-label {
            color: #64748b;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .55px;
            font-weight: 700;
        }

        .requirements-card {
            position: relative;
            background: linear-gradient(145deg, #ffffff 0%, #fff7f7 100%);
        }

        .requirements-card:before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, #ef4444, #991b1b);
        }

        .requirement-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .requirement-stat {
            min-width: 0;
            padding: 10px;
            border: 1px solid #fee2e2;
            border-radius: 12px;
            background: rgba(255, 255, 255, .8);
            text-align: center;
        }

        .requirement-stat-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-bottom: 5px;
            color: #64748b;
            font-size: .7rem;
            font-weight: 700;
        }

        .requirement-status-dot {
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            border-radius: 50%;
        }

        .requirement-stat-value {
            overflow: hidden;
            color: #111827;
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1;
            text-overflow: ellipsis;
        }

        .interview-summary {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }

        .interview-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-top: 20px;
        }

        .interview-stat {
            min-width: 0;
            padding: 8px 6px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            text-align: center;
        }

        .interview-stat-label {
            display: block;
            margin-bottom: 3px;
            color: #64748b;
            font-size: .7rem;
            font-weight: 700;
        }

        .interview-stat-value {
            color: #111827;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1;
        }

        @media (max-width: 767.98px) {
            .interview-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .section-title {
            font-weight: 800;
            color: #172033;
        }

        .pipeline-board {
            padding: 22px;
            border: 1px solid #e8edf5;
            border-radius: 22px;
            background: #f8fafc;
        }

        .pipeline-overview {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 24px;
            padding: 18px 20px;
            border-radius: 18px;
            color: #fff;
            background: linear-gradient(115deg, #172033, #32203b 58%, #8d1f2d);
            overflow: hidden;
            position: relative;
        }

        .pipeline-overview:after {
            content: '';
            width: 160px;
            height: 160px;
            border: 28px solid rgba(255, 255, 255, .06);
            border-radius: 50%;
            position: absolute;
            top: -70px;
            right: -38px;
        }

        .pipeline-overview-label {
            color: rgba(255, 255, 255, .65);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .pipeline-overview-value {
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.15;
        }

        .pipeline-flow {
            position: relative;
        }

        .pipeline-flow:before {
            content: '';
            position: absolute;
            top: 28px;
            bottom: 28px;
            left: 23px;
            width: 2px;
            background: #dce3ed;
        }

        .pipeline-stage {
            --stage-color: #475569;
            --stage-soft: #f1f5f9;
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr);
            gap: 16px;
            margin-bottom: 22px;
            position: relative;
        }

        .pipeline-stage:last-child {
            margin-bottom: 0;
        }

        .pipeline-stage-marker {
            width: 48px;
            height: 48px;
            border: 5px solid #f8fafc;
            border-radius: 15px;
            color: #fff;
            background: var(--stage-color);
            box-shadow: 0 5px 16px rgba(15, 23, 42, .14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }

        .pipeline-stage-card {
            min-width: 0;
            border: 1px solid #e3e8f0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 6px 20px rgba(15, 23, 42, .04);
            overflow: hidden;
        }

        .pipeline-stage-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 17px 20px;
            border-left: 4px solid var(--stage-color);
            background: linear-gradient(90deg, var(--stage-soft), #fff 38%);
        }

        .pipeline-stage-title h6 {
            margin: 0 0 3px;
            color: #172033;
            font-size: .95rem;
            font-weight: 800;
        }

        .pipeline-stage-meta {
            color: #8792a5;
            font-size: .72rem;
            font-weight: 600;
        }

        .pipeline-stage-count {
            flex: 0 0 auto;
            min-width: 68px;
            text-align: right;
        }

        .pipeline-stage-count strong {
            display: block;
            color: var(--stage-color);
            font-size: 1.35rem;
            line-height: 1;
        }

        .pipeline-stage-count span {
            color: #94a3b8;
            font-size: .66rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .pipeline-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 28px;
            padding: 8px 20px 14px;
        }

        .pipeline-item {
            min-width: 0;
            padding: 12px 0;
            border-bottom: 1px solid #eef2f6;
        }

        .pipeline-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .pipeline-item-label {
            min-width: 0;
            color: #475569;
            font-size: .8rem;
            font-weight: 700;
        }

        .pipeline-item-value {
            flex: 0 0 auto;
            min-width: 34px;
            color: #172033;
            font-size: .88rem;
            font-weight: 800;
            text-align: right;
        }

        .pipeline-track {
            height: 5px;
            border-radius: 20px;
            background: #edf1f5;
            overflow: hidden;
        }

        .pipeline-fill {
            height: 100%;
            min-width: 2px;
            border-radius: 20px;
            background: var(--stage-color);
        }

        .pipeline-chart-body {
            padding: 6px 18px 12px;
        }

        @media (max-width: 767.98px) {
            .pipeline-board { padding: 14px; }
            .pipeline-overview { align-items: flex-start; flex-direction: column; }
            .pipeline-grid { grid-template-columns: 1fr; }
            .pipeline-stage { grid-template-columns: 38px minmax(0, 1fr); gap: 10px; }
            .pipeline-flow:before { left: 18px; }
            .pipeline-stage-marker { width: 38px; height: 38px; border-radius: 12px; }
            .pipeline-stage-title { padding: 14px; }
            .pipeline-grid { padding-right: 14px; padding-left: 14px; }
        }

        .interview-item {
            padding: 14px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .interview-item:last-child {
            border: 0;
        }

        .date-box {
            min-width: 52px;
            text-align: center;
            border-radius: 13px;
            padding: 7px;
            background: #fff1f2;
            color: #991b1b;
        }

        .target-panel {
            position: relative;
            overflow: hidden;
            background: #fff;
        }

        .target-panel:before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, #b91c1c, #ef4444, #f59e0b);
        }

        .target-month {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border: 1px solid #fee2e2;
            border-radius: 12px;
            color: #991b1b;
            background: #fff7f7;
            font-size: .82rem;
            font-weight: 700;
        }

        .target-summary {
            height: 100%;
            padding: 20px;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            background: linear-gradient(145deg, #fff, #fafafa);
        }

        .target-summary-value {
            color: #172033;
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .target-progress {
            height: 8px;
            overflow: hidden;
            border-radius: 20px;
            background: #f1f5f9;
        }

        .target-progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #991b1b, #ef4444);
        }

        .individual-target-summary-value.percentage-green {
            color: #15803d;
        }

        .individual-target-summary-value.percentage-blue {
            color: #2563eb;
        }

        .individual-target-summary-value.percentage-red {
            color: #dc2626;
        }

        .target-progress-bar.percentage-green {
            background: linear-gradient(90deg, #16a34a, #22c55e);
        }

        .target-progress-bar.percentage-blue {
            background: linear-gradient(90deg, #2563eb, #60a5fa);
        }

        .target-progress-bar.percentage-red {
            background: linear-gradient(90deg, #dc2626, #ef4444);
        }

        .individual-target-summary-value {
            color: #172033;
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .kpi-card {
            height: 100%;
            padding: 18px;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            transition: border-color .2s, transform .2s;
        }

        .kpi-card:hover {
            border-color: #fecaca;
            transform: translateY(-2px);
        }

        .kpi-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            flex: 0 0 40px;
            border-radius: 12px;
            color: #b91c1c;
            background: #fff1f2;
            font-size: 1.15rem;
        }

        .role-performance-row {
            display: grid;
            grid-template-columns: minmax(150px, 1.4fr) 2fr 70px 60px 110px;
            gap: 16px;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .role-performance-row:last-child {
            border-bottom: 0;
        }

        .role-avatar {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            flex: 0 0 38px;
            border-radius: 50%;
            color: #fff;
            background: linear-gradient(145deg, #172033, #b91c1c);
            font-weight: 800;
        }

        .analytics-chart-wrap {
            min-height: 330px;
            padding: 8px 4px 0;
        }

        .analytics-person {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border: 1px solid #fee2e2;
            border-radius: 14px;
            background: #fff7f7;
        }

        .analytics-note {
            padding: 12px 14px;
            border-radius: 12px;
            color: #475569;
            background: #f8fafc;
            font-size: .78rem;
        }

        @media (max-width: 576px) {
            .dashboard-hero {
                border-radius: 16px;
            }

            .role-performance-row {
                grid-template-columns: 1fr 60px;
            }

            .role-performance-row .role-progress-column {
                grid-column: 1 / -1;
                grid-row: 2;
            }

            .role-performance-row .role-target-column {
                display: none;
            }

            .role-performance-row>strong {
                grid-column: 1;
                grid-row: 3;
                text-align: left !important;
            }

            .role-performance-row>.btn {
                grid-column: 2;
                grid-row: 3;
            }
        }

        /* birthday reminder */
        .birthday-reminder-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 22px;
            background: linear-gradient(135deg, #fff7ed, #fff1f2);
            border: 1px solid #fed7aa;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .birthday-icon {
            width: 52px;
            height: 52px;
            min-width: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: #f97316;
            border-radius: 12px;
            font-size: 28px;
            box-shadow: 0 3px 10px rgba(249, 115, 22, 0.15);
        }

        .birthday-content {
            flex: 1;
        }

        .birthday-title {
            font-size: 16px;
            font-weight: 700;
            color: #9a3412;
        }

        .birthday-emoji {
            font-size: 18px;
        }

        .birthday-message {
            margin-top: 4px;
            color: #57534e;
            font-size: 14px;
        }

        .birthday-message strong {
            color: #ea580c;
            font-weight: 700;
        }

        .birthday-decoration {
            position: absolute;
            right: 20px;
            bottom: -8px;
            font-size: 48px;
            opacity: 0.15;
            transform: rotate(-10deg);
        }

        /* birthday welcome */
        .birthday-welcome {
            position: relative;
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 22px 26px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            overflow: hidden;
        }

        .birthday-welcome-icon {
            width: 58px;
            height: 58px;
            min-width: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.18);
            font-size: 30px;
        }

        .birthday-welcome-content {
            position: relative;
            z-index: 2;
        }

        .birthday-welcome-title {
            font-size: 25px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .birthday-welcome-message {
            font-size: 15px;
            font-weight: 500;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
        }

        .birthday-confetti {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 65px;
            opacity: 0.15;
        }

        /* Independence day */
        .independence-day-banner {
            position: relative;
            display: flex;
            align-items: center;
            gap: 20px;
            min-height: 125px;
            padding: 22px 28px;
            border-radius: 18px;
            overflow: hidden;

            background: linear-gradient(
                120deg,
                #ff9933 0%,
                #ffb15c 22%,
                #ffffff 50%,
                #e9f7ec 78%,
                #138808 100%
            );

            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        /* Left icon */

        .independence-left {
            position: relative;
            z-index: 3;
        }

        .independence-icon {
            width: 64px;
            height: 64px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: rgba(255, 255, 255, 0.75);
            border: 2px solid rgba(255, 255, 255, 0.9);

            border-radius: 50%;

            font-size: 32px;

            box-shadow:
                0 6px 15px rgba(0, 0, 0, 0.1),
                0 0 0 6px rgba(255, 255, 255, 0.18);
        }

        /* Main content */

        .independence-content {
            position: relative;
            z-index: 4;
            flex: 1;
        }

        .independence-date {
            display: flex;
            align-items: center;
            gap: 8px;

            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;

            color: #6b7280;

            margin-bottom: 5px;
        }

        .date-line {
            width: 25px;
            height: 2px;
            background: #ff9933;
            border-radius: 5px;
        }

        .independence-content h3 {
            margin: 0 0 5px;

            font-size: 23px;
            font-weight: 800;

            color: #1f2937;
        }

        .independence-content p {
            margin: 0;

            max-width: 650px;

            font-size: 14px;
            line-height: 1.6;

            color: #4b5563;
        }

        .independence-content strong {
            color: #138808;
            font-weight: 800;
        }

        /* Ashoka Chakra */

        .ashoka-chakra {
            position: absolute;

            right: 135px;
            top: 50%;

            transform: translateY(-50%);

            font-size: 105px;

            color: #1d4ed8;

            opacity: 0.08;

            z-index: 1;
        }

        /* Flag */

        .independence-flag {
            position: relative;
            z-index: 4;

            font-size: 62px;

            filter: drop-shadow(0 5px 5px rgba(0, 0, 0, 0.1));

            animation: flagFloat 3s ease-in-out infinite;
        }

        @keyframes flagFloat {

            0%, 100% {
                transform: translateY(0) rotate(-2deg);
            }

            50% {
                transform: translateY(-5px) rotate(2deg);
            }
        }

        /* Decorative glowing circles */

        .independence-glow {
            position: absolute;

            width: 120px;
            height: 120px;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.18);

            filter: blur(2px);
        }

        .glow-one {
            left: -45px;
            bottom: -70px;
        }

        .glow-two {
            right: 50px;
            top: -80px;
        }

        /* Bottom wave */

        .wave-decoration {
            position: absolute;

            left: 0;
            bottom: -32px;

            width: 120%;

            height: 55px;

            background: rgba(255, 255, 255, 0.22);

            border-radius: 50% 50% 0 0;

            transform: rotate(-2deg);

            z-index: 2;
        }

        /* Mobile */

        @media (max-width: 576px) {

            .independence-day-banner {
                padding: 18px;
                gap: 14px;
            }

            .independence-icon {
                width: 50px;
                height: 50px;
                font-size: 25px;
            }

            .independence-content h3 {
                font-size: 18px;
            }

            .independence-content p {
                font-size: 12px;
            }

            .independence-date {
                font-size: 8px;
            }

            .independence-flag {
                display: none;
            }

            .ashoka-chakra {
                right: 20px;
            }
        }
    </style>

    <div class="main-content dashboard-shell">
        <div class="page-content">
            <div class="container-fluid">
                <div class="dashboard-hero p-4 p-lg-5 mb-4">
                    <div class="position-relative" style="z-index:1">
                        <div class="scope-pill mb-3"><i class="ri-shield-user-line"></i>{{ $scopeLabel }}</div>
                        <h2 class="fw-bold text-white mb-2">Welcome back, {{ auth()->user()->name }}</h2>

                        @if (auth()->user()->role_id == 1 && $birthdayEmployees->isNotEmpty())
                            <div class="birthday-reminder-card mb-4">
                                <div class="birthday-icon">
                                    <i class="ri-cake-2-line"></i>
                                </div>

                                <div class="birthday-content">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="birthday-title">Birthday Reminder</span>
                                        <span class="birthday-emoji">🎉</span>
                                    </div>

                                    @foreach ($birthdayEmployees as $birthdayEmployee)
                                        <div class="birthday-message">
                                            Today is
                                            <strong>{{ $birthdayEmployee->employee_name }}</strong>'s birthday!
                                        </div>
                                    @endforeach
                                </div>

                                <div class="birthday-decoration">🎂</div>
                            </div>
                        @endif

                        @if ($birthdayEmployee && auth()->user()->role_id != 1)
                            <div class="birthday-welcome">
                                <div class="birthday-welcome-icon">
                                    🎂
                                </div>

                                <div class="birthday-welcome-content">
                                    <div class="birthday-welcome-title">
                                        🎉 Happy Birthday!
                                    </div>

                                    <div class="birthday-welcome-message">
                                        Wishing you a wonderful birthday filled with happiness, success,
                                        and a fantastic year ahead! ✨
                                    </div>
                                </div>

                                <div class="birthday-confetti">🎈</div>
                            </div>
                        @else
                            <p class="mb-0 text-white-50">
                                A live view of requirements, applicants and interviews available to your role.
                            </p>
                        @endif

                        {{-- Independence day --}}
                        @php $isIndependenceDay = now()->format('m-d') === '08-14'; @endphp

                        @if ($isIndependenceDay)
                            <div class="independence-day-banner mb-4 mt-3">

                                <!-- Decorative elements -->
                                <div class="independence-glow glow-one"></div>
                                <div class="independence-glow glow-two"></div>

                                <div class="independence-left">
                                    <div class="independence-icon">
                                        🇮🇳
                                    </div>
                                </div>

                                <div class="independence-content">
                                    <div class="independence-date">
                                        <span>15 AUGUST</span>
                                        <span class="date-line"></span>
                                        <span>INDEPENDENCE DAY</span>
                                    </div>

                                    <h3>
                                        Happy Independence Day! 🇮🇳
                                    </h3>

                                    <p>
                                        Wishing you and your family a day filled with pride,
                                        happiness and the spirit of freedom.
                                        <strong>Jai Hind!</strong>
                                    </p>
                                </div>

                                <div class="ashoka-chakra">
                                    ☸
                                </div>

                                <div class="independence-flag">
                                    🇮🇳
                                </div>

                                <div class="wave-decoration"></div>

                            </div>
                        @endif
                    </div>
                </div>

                <div class="card panel-card mb-4">
                    <div class="card-body p-3 p-lg-4">
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3 align-items-end">
                            @unless ($isRecruiterDashboard)
                                <div class="col-md-6 col-xl-2">
                                    <label for="dashboard_recruiter_id" class="form-label fw-semibold">Recruiter</label>
                                    <select id="dashboard_recruiter_id" name="recruiter_id" class="form-select">
                                        <option value="">All recruiters</option>
                                        @foreach ($recruiters as $recruiter)
                                            <option value="{{ $recruiter->id }}" @selected((int) $selectedRecruiterId === $recruiter->id)>
                                            {{ $recruiter->recruiter_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endunless
                            @if ($showClientFilter)
                                <div class="col-md-6 col-xl-2">
                                    <label for="dashboard_client_id" class="form-label fw-semibold">Client</label>
                                    <select id="dashboard_client_id" name="client_id" class="form-select">
                                        <option value="">All clients</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" @selected((int) $selectedClientId === $client->id)>
                                                {{ $client->client }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-6 col-xl-{{ $isRecruiterDashboard ? '3' : '3' }}">
                                <label for="dashboard_from_date" class="form-label fw-semibold">From Date</label>
                                <input type="date" id="dashboard_from_date" name="dashboard_from_date"
                                    class="form-control {{ $fromDateError ? 'is-invalid' : '' }}"
                                    value="{{ $selectedFromDate }}">
                                @if ($fromDateError)
                                    <div class="invalid-feedback">{{ $fromDateError }}</div>
                                @endif
                            </div>
                            <div class="col-md-6 col-xl-{{ $isRecruiterDashboard ? '3' : '3' }}">
                                <label for="dashboard_to_date" class="form-label fw-semibold">To Date</label>
                                <input type="date" id="dashboard_to_date" name="dashboard_to_date"
                                    class="form-control {{ $toDateError ? 'is-invalid' : '' }}"
                                    value="{{ $selectedToDate }}">
                                @if ($toDateError)
                                    <div class="invalid-feedback">{{ $toDateError }}</div>
                                @endif
                            </div>
                            <div
                                class="col-md-6 col-xl-{{ $isRecruiterDashboard ? '4' : ($showClientFilter ? '2' : '3') }} d-flex gap-2">
                                <button type="submit" class="btn btn-danger flex-grow-1">
                                    <i class="ri-filter-3-line me-1"></i>Apply
                                </button>
                                @if ($selectedFromDate || $selectedToDate)
                                    <a href="{{ route(
                                        'admin.dashboard',
                                        array_filter([
                                            'recruiter_id' => $isRecruiterDashboard ? null : $selectedRecruiterId,
                                            'client_id' => $selectedClientId,
                                        ]),
                                    ) }}"
                                        class="btn btn-outline-primary" title="Remove date range"
                                        aria-label="Remove date range">
                                        <i class="ri-calendar-close-line"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-light" title="Clear all filters"
                                    aria-label="Clear all filters">
                                    <i class="ri-refresh-line"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                @unless ($recruiterLinked)
                    <div class="alert alert-warning border-0 shadow-sm"><i class="ri-alert-line me-2"></i>Your login email is
                        not linked to a recruiter record, so your personal dashboard currently has no records.</div>
                @endunless

                <div class="row g-3 mb-4">
                    @if ($isRecruiterDashboard || auth()->user()->can('read', \App\Models\ClientRequirement::class))
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card requirements-card">
                                <div class="card-body p-3 p-lg-4">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                        <div class="metric-label">Requirements</div>
                                        <div class="metric-icon">
                                            <img src="{{ asset('frontend/images/adminlogos.png') }}" alt="SZORZO Logo"
                                                class="metric-logo">
                                        </div>
                                    </div>
                                    <div class="requirement-stats">
                                        <div class="requirement-stat">
                                            <div class="requirement-stat-label">
                                                <span class="requirement-status-dot bg-success"></span>Active
                                            </div>
                                            <div class="requirement-stat-value">{{ number_format($activeRequirements ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="requirement-stat">
                                            <div class="requirement-stat-label">
                                                <span class="requirement-status-dot bg-warning"></span>Priority
                                            </div>
                                            <div class="requirement-stat-value">{{ number_format($priorityRequirements ?? 0) }}
                                            </div>
                                        </div>
                                        <div class="requirement-stat">
                                            <div class="requirement-stat-label">
                                                <span class="requirement-status-dot bg-secondary"></span>Inactive
                                            </div>
                                            <div class="requirement-stat-value">{{ number_format($inActiveRequirements ?? 0) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @can('read', \App\Models\Candidate::class)
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-3 p-lg-4">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                        <div class="metric-label">Candidate Stage</div>
                                        <div class="metric-icon">
                                            <img src="{{ asset('frontend/images/adminlogos.png') }}" alt="SZORZO Logo"
                                                class="metric-logo">
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="border rounded-3 bg-light p-2 h-100">
                                                <div class="small text-muted mb-1">Profile sourced</div>
                                                <div class="metric-value">{{ number_format($myProfiles ?? 0) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded-3 bg-light p-2 h-100">
                                                <div class="small text-muted mb-1">Profile submitted</div>
                                                <div class="metric-value">{{ number_format($myApplicants ?? 0) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-3 p-lg-4">
                                    <div class="interview-summary">
                                        <div>
                                            <div class="metric-label mb-2">Interview Stage</div>
                                            <div class="metric-value">
                                                {{ number_format($candidateInterviewStages->count()) }}
                                            </div>
                                        </div>
                                        <div class="metric-icon">
                                            <img src="{{ asset('frontend/images/adminlogos.png') }}" alt="SZORZO Logo"
                                                class="metric-logo">
                                        </div>
                                    </div>

                                    <div class="interview-stats">
                                        <div class="interview-stat">
                                            <span class="interview-stat-label">L1</span>
                                            <div class="interview-stat-value">
                                                {{ number_format($candidateInterviewStages->whereIn('level_of_interview_id', [7, 8, 31])->count()) }}
                                            </div>
                                        </div>
                                        <div class="interview-stat">
                                            <span class="interview-stat-label">L2</span>
                                            <div class="interview-stat-value">
                                                {{ number_format($candidateInterviewStages->whereIn('level_of_interview_id', [11, 12, 32])->count()) }}
                                            </div>
                                        </div>
                                        <div class="interview-stat">
                                            <span class="interview-stat-label">L3</span>
                                            <div class="interview-stat-value">
                                                {{ number_format($candidateInterviewStages->whereIn('level_of_interview_id', [23, 25, 33])->count()) }}
                                            </div>
                                        </div>
                                        <div class="interview-stat">
                                            <span class="interview-stat-label">L4</span>
                                            <div class="interview-stat-value">
                                                {{ number_format($candidateInterviewStages->whereIn('level_of_interview_id', [27, 28, 34])->count()) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border rounded-3 bg-light p-2 h-100 mt-3">
                                            <div class="small text-muted mb-1">HR Selected</div>
                                            <div class="metric-value">{{ number_format($hrSelected ?? 0) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="card metric-card">
                                <div class="card-body p-3 p-lg-4">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                        <div class="metric-label">Onboarding Stage</div>
                                        <div class="metric-icon">
                                            <img src="{{ asset('frontend/images/adminlogos.png') }}" alt="SZORZO Logo"
                                                class="metric-logo">
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="border rounded-3 bg-light p-2 h-100">
                                                <div class="small text-muted mb-1">Offered</div>
                                                <div class="metric-value">{{ number_format($offered ?? 0) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded-3 bg-light p-2 h-100">
                                                <div class="small text-muted mb-1">Onboarded</div>
                                                <div class="metric-value">{{ number_format($onboarded ?? 0) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>

                @php
                    $selectedDashboardRecruiter = $selectedRecruiterId
                        ? $recruiters->firstWhere('id', (int) $selectedRecruiterId)
                        : null;
                    $individualAnalyticsVisible = $isRecruiterDashboard || (bool) $selectedDashboardRecruiter;
                    $individualAnalyticsName = $isRecruiterDashboard
                        ? auth()->user()->name
                        : $selectedDashboardRecruiter?->recruiter_name ?? 'All recruiters';
                    $monthlyKpis = $monthlyTargetAnalytics['kpis'];
                @endphp

                <div class="card panel-card target-panel mb-4">
                    <div class="card-body p-3 p-lg-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h5 class="section-title mb-0">Monthly Target Analytics</h5>
                                    <span class="badge bg-success-subtle text-success">Live records</span>
                                </div>
                                <p class="text-muted small mb-0">
                                    @if ($isSuperAdminDashboard)
                                        All-role performance visibility for Super Admin
                                    @elseif ($isDeliveryLeadDashboard)
                                        {{ $selectedDashboardRecruiter ? $selectedDashboardRecruiter->recruiter_name . ' target and process completion' : 'Recruiter-DL team target and process completion' }}
                                    @else
                                        Your individual monthly target and process completion
                                    @endif
                                </p>
                            </div>
                            <div class="target-month"><i class="ri-calendar-2-line"></i>{{ now()->format('F Y') }}</div>
                        </div>

                        {{-- <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-xl-3">
                                <div class="target-summary">
                                    <div class="metric-label mb-2">Overall Completion</div>
                                    <div class="d-flex justify-content-between align-items-end mb-3">
                                        <div class="target-summary-value">{{ $monthlyTargetAnalytics['overallPercentage'] }}%</div><small class="text-muted">{{ $monthlyTargetAnalytics['targetMultiplier'] > 1 ? $monthlyTargetAnalytics['targetMultiplier'].' recruiters' : 'Monthly target' }}</small>
                                    </div>
                                    <div class="target-progress"><div class="target-progress-bar" style="width:{{ $monthlyTargetAnalytics['overallPercentage'] }}%"></div></div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="target-summary">
                                    <div class="metric-label mb-2">Process Completed</div>
                                    <div class="target-summary-value">{{ $monthlyTargetAnalytics['completedProcesses'] }} <small class="fs-6 fw-normal text-muted">/ 7 KPIs</small></div>
                                    <div class="small text-muted mt-3"><i class="ri-checkbox-circle-line me-1"></i>Monthly workflow</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="target-summary">
                                    <div class="metric-label mb-2">Offers Progress</div>
                                    <div class="target-summary-value">{{ $monthlyTargetAnalytics['offers']['completed'] }} <small class="fs-6 fw-normal text-muted">/ {{ $monthlyTargetAnalytics['offers']['target'] }}</small></div>
                                    <div class="small text-muted mt-3"><i class="ri-draft-line me-1"></i>Minimum to stretch target</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="target-summary">
                                    <div class="metric-label mb-2">Joining Progress</div>
                                    <div class="target-summary-value">{{ $monthlyTargetAnalytics['joining']['completed'] }} <small class="fs-6 fw-normal text-muted">/ {{ $monthlyTargetAnalytics['joining']['target'] }}</small></div>
                                    <div class="small text-muted mt-3"><i class="ri-user-add-line me-1"></i>Monthly joiners</div>
                                </div>
                            </div>
                        </div> --}}

                        <div class="row g-3">
                            @foreach ($monthlyKpis as $kpi)
                                @php
                                    $percentageColor =
                                        $kpi['percentage'] > 80
                                            ? 'percentage-green'
                                            : ($kpi['percentage'] >= 60
                                                ? 'percentage-blue'
                                                : 'percentage-red');
                                @endphp
                                <div class="col-md-6 col-xl-4">
                                    <div class="kpi-card">
                                        <div class="d-flex justify-content-between gap-3 mb-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="kpi-icon"><i class="{{ $kpi['icon'] }}"></i></div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $kpi['label'] }}</div><small
                                                        class="text-muted">Monthly target</small>
                                                </div>
                                            </div>
                                            <div class="text-end"><strong
                                                    class="text-dark">{{ $kpi['target'] }}</strong><small
                                                    class="d-block text-muted">{{ $kpi['unit'] }}</small></div>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-2"><span
                                                class="text-muted">Completed:
                                                {{ number_format($kpi['completed']) }}</span><strong
                                                class="individual-target-summary-value {{ $percentageColor }}">{{ $kpi['percentage'] }}%</strong>
                                        </div>
                                        <div class="target-progress">
                                            <div class="target-progress-bar {{ $percentageColor }}"
                                                style="width:{{ $kpi['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- @if ($isSuperAdminDashboard || $isDeliveryLeadDashboard)
                            <div class="mt-4 pt-4 border-top">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div>
                                        <h6 class="section-title mb-1">All Roles Performance</h6><span
                                            class="text-muted small">Role-wise monthly target completion</span>
                                    </div>
                                    <span
                                        class="badge bg-light text-dark">{{ $isDeliveryLeadDashboard ? 'Recruiter-DL view' : 'Super Admin view' }}</span>
                                </div>
                                <div class="role-performance-row">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="role-avatar">DL</div>
                                        <div>
                                            <div class="fw-semibold text-dark">Recruiter - DL</div><small
                                                class="text-muted">Delivery leadership</small>
                                        </div>
                                    </div>
                                    <div class="role-progress-column">
                                        <div class="target-progress">
                                            <div class="target-progress-bar"
                                                style="width:{{ $deliveryLeadAnalytics['overallPercentage'] }}%"></div>
                                        </div>
                                    </div>
                                    <div class="role-target-column text-muted small">7 KPIs</div><strong
                                        class="text-end">{{ $deliveryLeadAnalytics['overallPercentage'] }}%</strong>
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="btn btn-sm {{ $selectedRecruiterId ? 'btn-outline-danger' : 'btn-danger' }}">Overview</a>
                                </div>
                                @forelse ($recruiters as $recruiter)
                                    <div class="role-performance-row">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="role-avatar">
                                                {{ strtoupper(substr($recruiter->recruiter_name, 0, 1)) }}</div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $recruiter->recruiter_name }}</div>
                                                <small class="text-muted">Recruiter</small>
                                            </div>
                                        </div>
                                        <div class="role-progress-column">
                                            <div class="target-progress">
                                                <div class="target-progress-bar"
                                                    style="width:{{ $recruiterPerformance[$recruiter->id] ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                        <div class="role-target-column text-muted small">7 KPIs</div><strong
                                            class="text-end">{{ $recruiterPerformance[$recruiter->id] ?? 0 }}%</strong>
                                        <a href="{{ route('admin.dashboard', ['recruiter_id' => $recruiter->id]) }}"
                                            class="btn btn-sm {{ (int) $selectedRecruiterId === $recruiter->id ? 'btn-danger' : 'btn-outline-danger' }}">
                                            <i
                                                class="ri-line-chart-line me-1"></i>{{ (int) $selectedRecruiterId === $recruiter->id ? 'Viewing' : 'Analytics' }}
                                        </a>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4"><i
                                            class="ri-team-line fs-2 d-block mb-1"></i>No recruiter rows available for
                                        preview</div>
                                @endforelse
                            </div>
                        @endif --}}
                    </div>
                </div>

                @if ($individualAnalyticsVisible)
                    <div class="card panel-card mb-4 target-panel">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="section-title mb-0">Individual Recruiter Analytics</h5>
                                        <span class="badge bg-success-subtle text-success">Live records</span>
                                    </div>
                                    <p class="text-muted small mb-0">Monthly candidate-profile activity and visible
                                        recruitment pipeline</p>
                                </div>
                                <div class="analytics-person">
                                    <div class="role-avatar">{{ strtoupper(substr($individualAnalyticsName, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $individualAnalyticsName }}</div><small
                                            class="text-muted">{{ $isRecruiterDashboard ? 'My analytics' : 'Selected recruiter' }}</small>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="row g-3 mb-3">
                                <div class="col-6 col-lg-3">
                                    <div class="target-summary">
                                        <div class="metric-label mb-2">Visible Applicants</div>
                                        <div class="target-summary-value">{{ number_format($myApplicants) }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <div class="target-summary">
                                        <div class="metric-label mb-2">Interview Stage</div>
                                        <div class="target-summary-value">{{ number_format($candidateInterviewStages->count()) }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <div class="target-summary">
                                        <div class="metric-label mb-2">Offers</div>
                                        <div class="target-summary-value">{{ number_format($offered) }}</div>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <div class="target-summary">
                                        <div class="metric-label mb-2">Active Requirements</div>
                                        <div class="target-summary-value">{{ number_format($activeRequirements) }}</div>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="row g-4 align-items-stretch">
                                <div class="col-xl-8">
                                    <div class="border rounded-4 h-100 p-3">
                                        <div
                                            class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-1">
                                            <div>
                                                <h6 class="section-title mb-1">Monthly Profile Completion</h6><span
                                                    class="text-muted small">Candidate profiles recorded during the last
                                                    six months</span>
                                            </div>
                                            <span class="badge bg-danger-subtle text-danger">Target: 100/month</span>
                                        </div>
                                        <div id="individualCompletionChart" class="analytics-chart-wrap"></div>
                                    </div>
                                </div>
                                <div class="col-xl-4">
                                    <div class="border rounded-4 h-100 p-4">
                                        <h6 class="section-title mb-1">Current Month Completion</h6>
                                        <p class="text-muted small mb-4">Against the minimum profile target</p>
                                        <div id="individualRadialChart" style="min-height:220px"></div>
                                        <div class="analytics-note"><i class="ri-information-line me-1"></i>This graph
                                            uses actual candidate records. HR assessments, acceptances and joining will
                                            appear after those data fields are connected.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif ($isSuperAdminDashboard)
                    <div class="card panel-card mb-4 target-panel">
                        <div class="card-body text-center py-5">
                            <div class="metric-icon bg-danger-subtle text-danger mx-auto mb-3"><i
                                    class="ri-user-search-line"></i></div>
                            <h5 class="section-title">Select a recruiter to view individual analytics</h5>
                            <p class="text-muted mb-0">Use the recruiter filter or an Analytics button in the
                                role-performance list.</p>
                        </div>
                    </div>
                @endif
                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 mb-3">
                    <label for="dashboardChartYear" class="form-label mb-0 fw-semibold">Chart Year</label>
                    <select id="dashboardChartYear" class="form-select form-select-sm" style="width: 120px"
                        data-url="{{ route('admin.dashboard.year-charts') }}"
                        data-recruiter="{{ $selectedRecruiterId }}" data-client="{{ $selectedClientId }}">
                        @for($year = now()->year + 1; $year >= now()->year - 9; $year--)
                            <option value="{{ $year }}" {{ $year === $chartYear ? 'selected' : '' }}>{{ $year }}</option>
                        @endfor
                    </select>
                    <span id="dashboardChartLoading" class="spinner-border spinner-border-sm text-primary d-none" role="status" aria-label="Loading"></span>
                </div>
                <div id="yearWiseDashboardCharts">
                <div class="row g-4 mb-4">
                    @can('read', \App\Models\Candidate::class)
                        <div class="col-12">
                            <div class="card panel-card target-panel">
                                <div class="card-body p-3 p-lg-4">
                                    @php
                                        $pipelineTotal = $groupedLevels->sum(fn ($group) => $group['levels']->sum('candidates_count'));
                                        $pipelineStatusCount = $groupedLevels->sum(fn ($group) => $group['levels']->count());
                                        $stageDesign = [
                                            'Sourcing Stage' => ['icon' => 'ri-user-search-line', 'color' => '#2563eb', 'soft' => '#eff6ff', 'caption' => 'Candidate discovery and screening'],
                                            'Interview Stage' => ['icon' => 'ri-discuss-line', 'color' => '#7c3aed', 'soft' => '#f5f3ff', 'caption' => 'Interview rounds and decisions'],
                                            'Offer Stage' => ['icon' => 'ri-file-paper-2-line', 'color' => '#ea580c', 'soft' => '#fff7ed', 'caption' => 'HR review and offer outcomes'],
                                            'Monthly Joining Details' => ['icon' => 'ri-bar-chart-grouped-line', 'color' => '#d97706', 'soft' => '#fffbeb', 'caption' => 'Monthly offer acceptance trend'],
                                            'Onboarding Stage' => ['icon' => 'ri-user-follow-line', 'color' => '#059669', 'soft' => '#ecfdf5', 'caption' => 'Final joining status'],
                                            'Monthly Onboarding Details' => ['icon' => 'ri-line-chart-line', 'color' => '#0f766e', 'soft' => '#f0fdfa', 'caption' => 'Monthly onboarding trend'],
                                        ];
                                    @endphp
                                    <div class="pipeline-board">
                                        <div class="pipeline-overview">
                                            <div class="position-relative" style="z-index: 1">
                                                <div class="pipeline-overview-label mb-1">Recruitment journey</div>
                                                <div class="pipeline-overview-value">Interview Pipeline</div>
                                                <div class="small mt-1" style="color: rgba(255,255,255,.68)">A stage-by-stage view of candidate movement</div>
                                            </div>
                                            <div class="d-flex gap-4 position-relative" style="z-index: 1">
                                                <div>
                                                    <div class="pipeline-overview-label">Activity</div>
                                                    <div class="pipeline-overview-value">{{ number_format($pipelineTotal) }}</div>
                                                </div>
                                                <div>
                                                    <div class="pipeline-overview-label">Statuses</div>
                                                    <div class="pipeline-overview-value">{{ number_format($pipelineStatusCount) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pipeline-flow">

                                        @foreach ($groupedLevels as $group)
                                            @php
                                                $design = $stageDesign[$group['title']];
                                                $stageTotal = (int) $group['levels']->sum('candidates_count');
                                                $stageMax = max(1, (int) $group['levels']->max('candidates_count'));
                                            @endphp
                                            <div class="pipeline-stage" style="--stage-color: {{ $design['color'] }}; --stage-soft: {{ $design['soft'] }};">
                                                <div class="pipeline-stage-marker">
                                                    <i class="{{ $design['icon'] }}"></i>
                                                </div>
                                                <div class="pipeline-stage-card">

                                                    <div class="pipeline-stage-title">
                                                        <div>
                                                            <h6>{{ $group['title'] }}</h6>
                                                            <div class="pipeline-stage-meta">{{ $design['caption'] }}</div>
                                                        </div>
                                                        @unless (in_array($group['title'], ['Monthly Joining Details', 'Monthly Onboarding Details'], true))
                                                            <div class="pipeline-stage-count">
                                                                <strong>{{ number_format($stageTotal) }}</strong>
                                                                <span>Candidates</span>
                                                            </div>
                                                        @endunless
                                                    </div>

                                                @if ($group['title'] === 'Monthly Joining Details')
                                                    <div class="pipeline-chart-body">
                                                        <div id="pipelineMonthlyJoiningBarChart" style="min-height: 250px;"></div>
                                                    </div>
                                                @elseif($group['title'] === 'Monthly Onboarding Details')
                                                    <div class="pipeline-chart-body">
                                                        <div id="pipelineMonthlyOnboardingBarChart" style="min-height: 250px;"></div>
                                                    </div>
                                                @else
                                                    <div class="pipeline-grid">

                                                        @foreach ($group['levels'] as $level)
                                                            @php
                                                                $count = (int) $level->candidates_count;
                                                                $percentage = round(($count / $stageMax) * 100);
                                                            @endphp

                                                            <div class="pipeline-item">
                                                                <div class="pipeline-item-header">
                                                                    <span class="pipeline-item-label">
                                                                        {{ $level->level }}
                                                                    </span>

                                                                    <span class="pipeline-item-value">
                                                                        {{ number_format($count) }}
                                                                    </span>
                                                                </div>

                                                                <div class="pipeline-track">
                                                                    <div class="pipeline-fill"
                                                                        style="width: {{ $percentage }}%">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach

                                                    </div>
                                                @endif
                                                </div>
                                            </div>
                                        @endforeach
                                        </div>
                                    </div>
                                    {{-- <div class="row g-2 mt-3 pt-3 border-top text-center">
                                        <div class="col-6">
                                            <div class="fw-bold fs-4">{{ $yetToOffer }}</div><small class="text-muted">Yet
                                                to
                                                offer</small>
                                        </div>
                                        <div class="col-6">
                                            <div class="fw-bold fs-4 text-success">{{ $offered }}</div><small
                                                class="text-muted">Offered</small>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    @endcan
                    {{-- @can('read', \App\Models\Candidate::class)
                        <div class="col-xl-8">
                            <div class="card panel-card">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <h5 class="section-title mb-1">Upcoming Interviews</h5><span
                                                class="text-muted small">Date-wise interview calendar</span>
                                        </div><a href="{{ route('admin.interview-schedules.index') }}"
                                            class="btn btn-sm btn-dark">View calendar</a>
                                    </div>
                                    @forelse($upcomingInterviews as $interview)
                                        <div class="interview-item d-flex gap-3 align-items-center">
                                            <div class="date-box"><strong
                                                    class="d-block fs-5 lh-1">{{ $interview->schedule_date->format('d') }}</strong><small>{{ $interview->schedule_date->format('M') }}</small>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold text-dark">
                                                    {{ $interview->candidate?->candidate_name ?? 'Candidate' }}</div><small
                                                    class="text-muted">{{ $interview->client?->client ?? 'No client' }} &middot;
                                                    {{ $interview->interviewLevel?->level ?? 'Interview' }}</small>
                                            </div>
                                            <div class="text-end"><span
                                                    class="badge bg-light text-dark">{{ $interview->schedule_date->format('h:i A') }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-5"><i
                                                class="ri-calendar-check-line fs-1 d-block mb-2"></i>No upcoming interviews
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endcan --}}
                </div>
                @if ($showRevenueDashboard)
                        <div class="card panel-card target-panel mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div>
                                        <h5 class="section-title mb-1">Revenue Outcomes</h5>
                                        <p class="text-muted small mb-0">Onboarded and joiner-declined revenue for <span class="selected-chart-year">{{ $chartYear }}</span></p>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span id="onboardedRevenueBadge" class="badge bg-success-subtle text-success">Onboarded: &#8377;{{ number_format($onboardedRevenue, 2) }}</span>
                                        <span id="declinedRevenueBadge" class="badge bg-danger-subtle text-danger">Declined: &#8377;{{ number_format($declinedRevenue, 2) }}</span>
                                    </div>
                                </div>
                                <div id="monthlyRevenueBarChart" style="min-height: 300px;"></div>
                            </div>
                        </div>
                @endif
                <div class="row g-4">
                    @can('read', \App\Models\Candidate::class)
                        <div class="col-xl-8">
                            <div class="card panel-card target-panel">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="section-title mb-1">Applicant Momentum</h5><span
                                                class="text-muted small">New applicants during <span class="selected-chart-year">{{ $chartYear }}</span></span>
                                        </div><span class="badge bg-danger-subtle text-danger">Live</span>
                                    </div>
                                    <div id="applicantChart" style="min-height:310px"></div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-xl-4">
                            <div class="card panel-card">
                                <div class="card-body p-4">
                                    <h5 class="section-title">Revenue Overview</h5>
                                    <p class="text-muted small">Revenue recorded on visible requirements</p>
                                    <div class="py-4">
                                        <div class="metric-label mb-2">Total Pipeline Revenue</div>
                                        <div class="display-6 fw-bold text-dark">&#8377;{{ number_format($revenue, 2) }}</div>
                                    </div>
                                    <div class="rounded-4 bg-danger-subtle text-danger p-3 small"><i
                                            class="ri-information-line me-1"></i> Values follow your role-based requirement
                                        access.</div>
                                </div>
                            </div>
                        </div> --}}
                    @endcan
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var target = document.querySelector('#applicantChart');
            if (!target || typeof ApexCharts === 'undefined') return;
            window.dashboardApplicantChart = new ApexCharts(target, {
                chart: {
                    type: 'area',
                    height: 310,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    }
                },
                series: [{
                    name: 'Applicants',
                    data: @json($chartApplicants)
                }],
                xaxis: {
                    categories: @json($chartMonths),
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true,
                    labels: {
                        formatter: function(v) {
                            return Math.round(v);
                        }
                    }
                },
                colors: ['#b91c1c'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: .35,
                        opacityTo: .03,
                        stops: [0, 95, 100]
                    }
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    borderColor: '#eef2f7',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: function(v) {
                            return v + ' applicant' + (v === 1 ? '' : 's');
                        }
                    }
                }
            });
            window.dashboardApplicantChart.render();
        });

        document.addEventListener('DOMContentLoaded', function() {
            var joiningChartTarget = document.querySelector('#pipelineMonthlyJoiningBarChart');
            var onboardingChartTarget = document.querySelector('#pipelineMonthlyOnboardingBarChart');
            if (typeof ApexCharts === 'undefined') return;

            function renderMonthlyOutcomeChart(target, positiveName, positiveData, negativeName, negativeData) {
                if (!target) return;

                var chart = new ApexCharts(target, {
                    chart: {
                        type: 'bar',
                        height: 250,
                        toolbar: { show: false },
                        sparkline: { enabled: false },
                        stacked: true
                    },
                    series: [
                        { name: positiveName, data: positiveData },
                        { name: negativeName, data: negativeData }
                    ],
                    xaxis: {
                        categories: @json($joiningChartMonths),
                        labels: {
                            rotate: -35,
                            trim: true,
                            style: { fontSize: '10px' }
                        },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        min: 0,
                        forceNiceScale: true,
                        labels: {
                            formatter: function(value) {
                                return Number.isInteger(value) ? value : '';
                            }
                        }
                    },
                    colors: ['#22c55e', '#ef4444'],
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            columnWidth: '52%',
                            dataLabels: { position: 'top' }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        offsetY: -16,
                        formatter: function(value) { return value > 0 ? value : ''; },
                        style: {
                            fontSize: '10px',
                            colors: ['#334155']
                        }
                    },
                    grid: {
                        borderColor: '#eef2f7',
                        strokeDashArray: 3,
                        padding: { top: 15, right: 0, bottom: 0, left: 0 }
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value + ' candidate' + (value === 1 ? '' : 's');
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    },
                    noData: { text: 'No monthly data' }
                });
                chart.render();
                return chart;
            }

            window.dashboardJoiningChart = renderMonthlyOutcomeChart(
                joiningChartTarget,
                'Offer Accepted',
                @json($offerAcceptedChartTotals),
                'Offer Declined',
                @json($offerDeclinedChartTotals)
            );
            window.dashboardOnboardingChart = renderMonthlyOutcomeChart(
                onboardingChartTarget,
                'Onboarded with Client',
                @json($onboardedChartTotals),
                'Joiner Declined',
                @json($joinerDeclinedChartTotals)
            );
        });

        document.addEventListener('DOMContentLoaded', function() {
            var revenueTarget = document.querySelector('#monthlyRevenueBarChart');
            if (!revenueTarget || typeof ApexCharts === 'undefined') return;

            window.dashboardRevenueChart = new ApexCharts(revenueTarget, {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: { show: false }
                },
                series: [
                    { name: 'Onboarded Revenue', data: @json($revenueChartTotals) },
                    { name: 'Declined Revenue', data: @json($declinedRevenueChartTotals) }
                ],
                xaxis: {
                    categories: @json($revenueChartMonths),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    min: 0,
                    labels: {
                        formatter: function(value) {
                            return '₹' + Number(value).toLocaleString('en-IN', { maximumFractionDigits: 0 });
                        }
                    }
                },
                colors: ['#22c55e', '#ef4444'],
                plotOptions: {
                    bar: {
                        borderRadius: 5,
                        columnWidth: '48%',
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -18,
                    formatter: function(value) {
                        return value > 0 ? '₹' + Number(value).toLocaleString('en-IN', { maximumFractionDigits: 0 }) : '';
                    },
                    style: {
                        fontSize: '10px',
                        colors: ['#334155']
                    }
                },
                grid: {
                    borderColor: '#eef2f7',
                    strokeDashArray: 3,
                    padding: { top: 20 }
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return '₹' + Number(value).toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                },
                noData: { text: 'No revenue data' }
            });
            window.dashboardRevenueChart.render();
        });

        document.addEventListener('DOMContentLoaded', function() {
            var yearSelect = document.querySelector('#dashboardChartYear');
            var loading = document.querySelector('#dashboardChartLoading');
            if (!yearSelect) return;

            var loadedYear = yearSelect.value;
            var currency = new Intl.NumberFormat('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            yearSelect.addEventListener('change', async function() {
                var requestedYear = yearSelect.value;
                var url = new URL(yearSelect.dataset.url, window.location.origin);
                url.searchParams.set('year', requestedYear);
                if (yearSelect.dataset.recruiter) url.searchParams.set('recruiter_id', yearSelect.dataset.recruiter);
                if (yearSelect.dataset.client) url.searchParams.set('client_id', yearSelect.dataset.client);

                yearSelect.disabled = true;
                loading?.classList.remove('d-none');

                try {
                    var response = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!response.ok) throw new Error('Unable to load chart data.');
                    var data = await response.json();

                    await Promise.all([
                        window.dashboardApplicantChart?.updateOptions({
                            xaxis: { categories: data.months },
                            series: [{ name: 'Applicants', data: data.applicants }]
                        }),
                        window.dashboardJoiningChart?.updateOptions({
                            xaxis: { categories: data.months },
                            series: [
                                { name: 'Offer Accepted', data: data.offer_accepted },
                                { name: 'Offer Declined', data: data.offer_declined }
                            ]
                        }),
                        window.dashboardOnboardingChart?.updateOptions({
                            xaxis: { categories: data.months },
                            series: [
                                { name: 'Onboarded with Client', data: data.onboarded },
                                { name: 'Joiner Declined', data: data.joiner_declined }
                            ]
                        }),
                        window.dashboardRevenueChart?.updateOptions({
                            xaxis: { categories: data.months },
                            series: [
                                { name: 'Onboarded Revenue', data: data.onboarded_revenue },
                                { name: 'Declined Revenue', data: data.declined_revenue }
                            ]
                        })
                    ]);

                    document.querySelectorAll('.selected-chart-year').forEach(function(label) {
                        label.textContent = data.year;
                    });
                    var onboardedBadge = document.querySelector('#onboardedRevenueBadge');
                    var declinedBadge = document.querySelector('#declinedRevenueBadge');
                    if (onboardedBadge) onboardedBadge.textContent = 'Onboarded: ₹' + currency.format(data.onboarded_revenue_total);
                    if (declinedBadge) declinedBadge.textContent = 'Declined: ₹' + currency.format(data.declined_revenue_total);
                    loadedYear = requestedYear;
                } catch (error) {
                    yearSelect.value = loadedYear;
                    window.alert(error.message);
                } finally {
                    yearSelect.disabled = false;
                    loading?.classList.add('d-none');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var completionTarget = document.querySelector('#individualCompletionChart');
            var radialTarget = document.querySelector('#individualRadialChart');
            if ((!completionTarget && !radialTarget) || typeof ApexCharts === 'undefined') return;

            var monthlyProfiles = @json($chartApplicants);
            var currentMonthProfiles = Number(monthlyProfiles[monthlyProfiles.length - 1] || 0);
            var profileTarget = 100;
            var completionPercent = Math.min(100, Math.round((currentMonthProfiles / profileTarget) * 100));

            if (completionTarget) {
                new ApexCharts(completionTarget, {
                    chart: {
                        type: 'bar',
                        height: 310,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Completed profiles',
                        data: monthlyProfiles
                    }],
                    xaxis: {
                        categories: @json($chartMonths),
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        min: 0,
                        forceNiceScale: true,
                        labels: {
                            formatter: function(value) {
                                return Math.round(value);
                            }
                        }
                    },
                    colors: ['#b91c1c'],
                    plotOptions: {
                        bar: {
                            borderRadius: 7,
                            columnWidth: '48%'
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    grid: {
                        borderColor: '#eef2f7',
                        strokeDashArray: 4
                    },
                    annotations: {
                        yaxis: [{
                            y: profileTarget,
                            borderColor: '#f59e0b',
                            strokeDashArray: 5,
                            label: {
                                text: 'Minimum target 100',
                                style: {
                                    background: '#f59e0b',
                                    color: '#fff'
                                }
                            }
                        }]
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value + ' candidate profile' + (value === 1 ? '' : 's');
                            }
                        }
                    }
                }).render();
            }

            if (radialTarget) {
                new ApexCharts(radialTarget, {
                    chart: {
                        type: 'radialBar',
                        height: 235,
                        sparkline: {
                            enabled: true
                        }
                    },
                    series: [completionPercent],
                    colors: ['#b91c1c'],
                    plotOptions: {
                        radialBar: {
                            hollow: {
                                size: '62%'
                            },
                            track: {
                                background: '#f1f5f9'
                            },
                            dataLabels: {
                                name: {
                                    show: true,
                                    offsetY: 22,
                                    color: '#64748b'
                                },
                                value: {
                                    offsetY: -14,
                                    fontSize: '28px',
                                    fontWeight: 800,
                                    color: '#172033',
                                    formatter: function(value) {
                                        return Math.round(value) + '%';
                                    }
                                }
                            }
                        }
                    },
                    labels: [currentMonthProfiles + ' of ' + profileTarget + ' profiles']
                }).render();
            }
        });
    </script>
@endsection
