@extends('backend.layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            {{-- Greeting --}}
            @php
                $hour = now()->format('H');
                $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : ($hour < 21 ? 'Good Evening' : 'Good Night'));
            @endphp

            <h4 class="mb-4">{{ $greeting }}, {{ auth()->user()->name }} 👋</h4>

            {{-- COUNTS --}}
            <div class="row">

                {{-- Enquiries --}}
                @can('read', \App\Models\ContactEnquiry::class)
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between">
                            <div>
                                <h6>Total Enquiries</h6>
                                <h3>{{ $totalEnquiries }}</h3>
                                <a href="{{ route('admin.enquiry.index') }}">View</a>
                            </div>
                            <i class="ri-question-answer-line fs-1 text-primary"></i>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Pages --}}
                @can('read', \App\Models\Pages::class)
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <div>
                                    <h6>Landing Pages</h6>
                                    <h3>{{ $totalPages }}</h3>
                                    <a href="{{ route('admin.pages.index') }}">View</a>
                                </div>
                                <i class="ri-pages-line fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                @endcan

                {{-- Users --}}
                @can('read', \App\Models\User::class)
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body d-flex justify-content-between">
                                <div>
                                    <h6>Users</h6>
                                    <h3>{{ $totalUsers }}</h3>
                                    <a href="{{ route('admin.user.index') }}">View</a>
                                </div>
                                <i class="ri-user-line fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                @endcan
                {{-- Settings --}}
                @if(auth()->user()->can('generalSetting', \App\Models\Setting::class) || auth()->user()->can('emailSetting', \App\Models\Setting::class) ||
                auth()->user()->can('socialSetting', \App\Models\Setting::class))
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between">
                            <div>
                                <h6>Roles</h6>
                                <h3>{{ $totalRoles }}</h3>
                                <a href="{{ route('admin.role.index') }}">Open</a>
                            </div>
                            <i class="ri-settings-3-line fs-1 text-danger"></i>
                        </div>
                    </div>
                </div>
                @endif

            </div>

            {{-- CHART --}}
            @can('read', \App\Models\ContactEnquiry::class)
                <div class="row mt-4">
                    <div class="col-xl-4">
                        <div class="card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Enquiry Overview</h4>
                                <div class="flex-shrink-0">
                                    {{-- <div class="dropdown card-header-dropdown">
                                        <a class="text-reset dropdown-btn" href="#"
                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            <span class="text-muted">Report<i
                                                    class="mdi mdi-chevron-down ms-1"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Download Report</a>
                                            <a class="dropdown-item" href="#">Export</a>
                                            <a class="dropdown-item" href="#">Import</a>
                                        </div>
                                    </div> --}}
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                    <div id="enquiryChart"
                                        data-colors='[ "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
                                        data-colors-minimal='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]'
                                        data-colors-interactive='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]'
                                        data-colors-galaxy='["--vz-primary", "--vz-primary-rgb, 0.85", "--vz-primary-rgb, 0.70", "--vz-primary-rgb, 0.60", "--vz-primary-rgb, 0.45"]'
                                        class="apex-charts" dir="ltr"></div>
                            </div>
                        </div> <!-- .card-->
                    </div> 

                    {{-- Latest Enquiries --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">Latest Enquiries</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Mobile</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($latestEnquiries as $enquiry)
                                            <tr>
                                                <td>{{ $enquiry->name }}</td>
                                                <td>{{ $enquiry->mobile }}</td>
                                                <td>{{ $enquiry->created_at->format('d M Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3">No data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                <a href="{{ route('admin.enquiry.index') }}" class="btn btn-sm btn-primary mt-2">
                                    View All
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

        </div>
    </div>

    {{-- FOOTER --}}
    <footer class="footer">
        <div class="container-fluid d-flex justify-content-between">
            <div>
                © {{ date('Y') }} SZORZO
            </div>
            <div>
                Developed by NextDNA Technologies
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

    var options = {
        chart: {
            type: 'donut',
            height: 300
        },
        series: [today, week, month],
        labels: ['Today', 'This Week', 'This Month'],
        colors: ['#28a745', '#ffc107', '#0d6efd'],
        legend: {
            position: 'bottom'
        },
        dataLabels: {
            enabled: true
        },
        noData: {
            text: 'No Enquiry Data'
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