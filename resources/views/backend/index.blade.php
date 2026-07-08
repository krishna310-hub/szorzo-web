@extends('backend.layouts.master')

@section('title', 'Dashboard')

@section('content')

<style>
    .page-content {
        background-color: transparent !important;
        position: relative;
        min-height: 100vh;
        z-index: 1;
    }

    .sz-glass-bg-layer {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
        pointer-events: none;
    }
    .sz-glassy-blob {
        position: absolute;
        filter: blur(80px);
        opacity: 0.15;
        border-radius: 50%;
        animation: sz-float 10s infinite ease-in-out alternate;
    }

    .sz-blob-primary {
        top: -5%; left: -5%;
        width: 450px; height: 450px;
        background: #b91c1c;
    }

    .sz-blob-dark {
        bottom: -10%; right: -5%;
        width: 400px; height: 400px;
        background: #111111;
        animation-delay: -5s;
    }

    @keyframes sz-float {
        0% { transform: translateY(0) scale(1); }
        100% { transform: translateY(-40px) scale(1.05); }
    }

    .sz-greeting-text {
        color: #111111;
        font-weight: 800;
        letter-spacing: -0.5px;
    }
    .sz-glassy-card {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        border-right: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 24px;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.04);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        height: 100%;
        z-index: 2;
    }

    .sz-glassy-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px 0 rgba(185, 28, 28, 0.08);
        border: 1px solid rgba(255, 255, 255, 1);
    }
    .sz-glassy-card::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 50%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transform: skewX(-20deg);
        transition: all 0.6s ease;
    }

    .sz-glassy-card:hover::before {
        left: 150%;
    }

    .sz-card-title {
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
    }

    .sz-card-value {
        color: #111111;
        font-weight: 800;
        font-size: 2rem;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .sz-view-link {
        color: #b91c1c;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
    }

    .sz-view-link:hover {
        color: #111111;
        gap: 8px;
    }

    /* Smooth rounded icon boxes */
    .sz-icon-box {
        width: 70px;
        height: 70px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(185, 28, 28, 0.05);
        color: #b91c1c;
        box-shadow: inset 0 0 0 1px rgba(185, 28, 28, 0.1);
        transition: all 0.4s ease;
    }

    .sz-glassy-card:hover .sz-icon-box {
        background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(185, 28, 28, 0.2);
        transform: scale(1.05) rotate(-5deg);
    }

    /* Glass Table Styling */
    .sz-glassy-table {
        border-collapse: separate;
        border-spacing: 0 12px;
        margin-top: -12px;
    }

    .sz-glassy-table thead th {
        background: transparent;
        color: #6b7280;
        border: none;
        padding: 0 20px 10px 20px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
    }

    .sz-glassy-table tbody tr {
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
    }

    .sz-glassy-table tbody tr:hover {
        transform: scale(1.01) translateX(5px);
        background: #ffffff;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
    }

    .sz-glassy-table tbody td {
        border: none;
        padding: 18px 20px;
        color: #374151;
        vertical-align: middle;
    }

    .sz-glassy-table tbody td:first-child {
        border-radius: 16px 0 0 16px;
        border-left: 3px solid transparent;
    }

    .sz-glassy-table tbody tr:hover td:first-child {
        border-left: 3px solid #b91c1c;
    }

    .sz-glassy-table tbody td:last-child {
        border-radius: 0 16px 16px 0;
    }

    .sz-table-badge {
        background: rgba(243, 244, 246, 0.8);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(0,0,0,0.05);
        color: #111111;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 500;
    }

    /* Custom Glassy Button */
    .sz-btn-glass {
        background: rgba(17, 17, 17, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.1);
        color: white;
        padding: 10px 24px;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .sz-btn-glass:hover {
        background: #b91c1c;
        color: white;
        box-shadow: 0 8px 20px rgba(185, 28, 28, 0.25);
        transform: translateY(-2px);
    }

    .sz-footer {
        background-color: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(10px);
        border-top: 1px solid rgba(0,0,0,0.05);
        color: #6b7280;
        font-weight: 500;
        position: relative;
        z-index: 2;
    }
</style>

<div class="main-content">

    <!-- STRICTLY CONFINED BACKGROUND LAYER FOR BLOBS -->
    <div class="sz-glass-bg-layer">
        <div class="sz-glassy-blob sz-blob-primary"></div>
        <div class="sz-glassy-blob sz-blob-dark"></div>
    </div>

    <div class="page-content position-relative z-1">
        <div class="container-fluid pt-5">

            {{-- Greeting --}}
            @php
                $hour = now()->format('H');
                $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : ($hour < 21 ? 'Good Evening' : 'Good Night'));
            @endphp

            <div class="mb-5 position-relative z-2">
                <h4 class="sz-greeting-text fs-1 mb-2">{{ $greeting }}, {{ auth()->user()->name }} 👋</h4>
                <p class="text-muted fs-6">Here is what's happening with your platform today.</p>
            </div>

            {{-- COUNTS --}}
            <div class="row g-4 mb-5">

                {{-- Enquiries --}}
                @can('read', \App\Models\ContactEnquiry::class)
                <div class="col-sm-6 col-xl-3">
                    <div class="sz-glassy-card">
                        <div class="card-body p-4 d-flex justify-content-between align-items-center h-100">
                            <div>
                                <h6 class="sz-card-title">Total Enquiries</h6>
                                <h3 class="sz-card-value">{{ $totalEnquiries }}</h3>
                                <a href="{{ route('admin.enquiry.index') }}" class="sz-view-link">View Details <i class="ri-arrow-right-line"></i></a>
                            </div>
                            <div class="sz-icon-box">
                                <i class="ri-question-answer-line fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Pages --}}
                {{-- @can('read', \App\Models\Pages::class)
                    <div class="col-sm-6 col-xl-3">
                        <div class="sz-glassy-card">
                            <div class="card-body p-4 d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <h6 class="sz-card-title">Landing Pages</h6>
                                    <h3 class="sz-card-value">{{ $totalPages }}</h3>
                                    <a href="{{ route('admin.pages.index') }}" class="sz-view-link">View Details <i class="ri-arrow-right-line"></i></a>
                                </div>
                                <div class="sz-icon-box">
                                    <i class="ri-pages-line fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan --}}

                {{-- Users --}}
                @can('read', \App\Models\User::class)
                    <div class="col-sm-6 col-xl-3">
                        <div class="sz-glassy-card">
                            <div class="card-body p-4 d-flex justify-content-between align-items-center h-100">
                                <div>
                                    <h6 class="sz-card-title">Active Users</h6>
                                    <h3 class="sz-card-value">{{ $totalUsers }}</h3>
                                    <a href="{{ route('admin.user.index') }}" class="sz-view-link">View Details <i class="ri-arrow-right-line"></i></a>
                                </div>
                                <div class="sz-icon-box">
                                    <i class="ri-user-line fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan

                {{-- Settings --}}
                @if(auth()->user()->can('generalSetting', \App\Models\Setting::class) || auth()->user()->can('emailSetting', \App\Models\Setting::class) || auth()->user()->can('socialSetting', \App\Models\Setting::class))
                <div class="col-sm-6 col-xl-3">
                    <div class="sz-glassy-card">
                        <div class="card-body p-4 d-flex justify-content-between align-items-center h-100">
                            <div>
                                <h6 class="sz-card-title">System Roles</h6>
                                <h3 class="sz-card-value">{{ $totalRoles }}</h3>
                                <a href="{{ route('admin.role.index') }}" class="sz-view-link">Manage Roles <i class="ri-arrow-right-line"></i></a>
                            </div>
                            <div class="sz-icon-box">
                                <i class="ri-settings-3-line fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>

            {{-- CHART & TABLE SECTION --}}
            @can('read', \App\Models\ContactEnquiry::class)
                <div class="row g-4 mb-4">

                    {{-- Enquiry Chart --}}
                    <div class="col-xl-4">
                        <div class="sz-glassy-card">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                                <h4 class="card-title mb-0" style="color: #111111; font-weight: 700;">Enquiry Overview</h4>
                            </div>
                            <div class="card-body px-4">
                                <div id="enquiryChart" class="apex-charts mt-3" dir="ltr"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Latest Enquiries Table --}}
                    <div class="col-xl-8">
                        <div class="sz-glassy-card">
                            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <h4 class="card-title mb-0" style="color: #111111; font-weight: 700;">Latest Enquiries</h4>
                                <a href="{{ route('admin.enquiry.index') }}" class="sz-btn-glass btn-sm">
                                    View All <i class="ri-arrow-right-s-line align-middle ms-1"></i>
                                </a>
                            </div>
                            <div class="card-body px-4 pt-2">
                                <div class="table-responsive">
                                    <table class="table sz-glassy-table w-100">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Mobile</th>
                                                <th>Date Submitted</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($latestEnquiries as $enquiry)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-xs me-3">
                                                                <span class="avatar-title rounded-circle bg-light text-danger fw-bold shadow-sm">
                                                                    {{ substr($enquiry->name, 0, 1) }}
                                                                </span>
                                                            </div>
                                                            <strong class="text-dark">{{ $enquiry->name }}</strong>
                                                        </div>
                                                    </td>
                                                    <td>{{ $enquiry->mobile }}</td>
                                                    <td>
                                                        <span class="sz-table-badge">
                                                            <i class="ri-calendar-line me-1 text-danger"></i> {{ $enquiry->created_at->format('d M Y') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="ri-inbox-line fs-1 mb-3 d-block opacity-50"></i>
                                                            <span class="fs-5">No enquiries found</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

        </div>
    </div>

    {{-- FOOTER --}}
    <footer class="sz-footer mt-auto">
        <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center py-4 px-4 gap-2">
            <div>
                © {{ date('Y') }} <span class="text-dark fw-bold">SZORZO</span>
            </div>
            <div>
                Developed by <strong class="text-danger">SZORZO Technologies</strong>
            </div>
        </div>
    </footer>
</div>
@endsection

@section('script')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const today = @json($todayEnquiries ?? 0);
    const week  = @json($weekEnquiries ?? 0);
    const month = @json($monthEnquiries ?? 0);

    // Smooth Chart Configuration for Glassy Theme
    var options = {
        chart: {
            type: 'donut',
            height: 330,
            fontFamily: 'inherit',
            background: 'transparent',
            dropShadow: {
                enabled: true,
                color: '#000',
                top: 5,
                left: 0,
                blur: 10,
                opacity: 0.05
            }
        },
        series: [today, week, month],
        labels: ['Today', 'This Week', 'This Month'],
        colors: ['#b91c1c', '#ea580c', '#111111'],
        stroke: {
            width: 3,
            colors: ['#ffffff'] // Clean white separators for glass effect
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '13px',
                            fontWeight: 600,
                            color: '#6b7280'
                        },
                        value: {
                            show: true,
                            fontSize: '28px',
                            fontWeight: 800,
                            color: '#111111',
                            offsetY: 5
                        },
                        total: {
                            show: true,
                            showAlways: true,
                            label: 'Total',
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#6b7280'
                        }
                    }
                }
            }
        },
        legend: {
            position: 'bottom',
            offsetY: 5,
            markers: {
                radius: 12,
                width: 10,
                height: 10
            },
            itemMargin: {
                horizontal: 12,
                vertical: 8
            },
            fontWeight: 500,
            labels: {
                colors: '#374151'
            }
        },
        dataLabels: {
            enabled: false
        },
        tooltip: {
            theme: 'light',
            style: {
                fontSize: '13px',
                fontFamily: 'inherit'
            },
            y: {
                formatter: function (val) {
                    return val + " Enquiries"
                }
            }
        },
        noData: {
            text: 'No Enquiry Data',
            align: 'center',
            verticalAlign: 'middle',
            style: {
                color: '#6b7280',
                fontSize: '16px'
            }
        }
    };

    var chartEl = document.querySelector("#enquiryChart");

    if (chartEl) {
        var chart = new ApexCharts(chartEl, options);
        chart.render();
    }

});
</script>
@endsection
