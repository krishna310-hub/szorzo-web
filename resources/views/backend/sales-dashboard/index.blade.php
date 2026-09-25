@extends('backend.layouts.master')
@section('title', 'Sales Dashboard')
@section('content')

<!-- Include ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Title -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-transparent shadow-none">
                        <h4 class="mb-sm-0 fw-bold fs-24 text-dark"><i class="ri-rocket-line text-primary me-2"></i>Sales Analytics{{ auth()->user()->isSales() ? ' — '.auth()->user()->name : '' }}</h4>
                        @if (auth()->user()->role_id == 1)
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary rounded-pill shadow-sm px-4 fw-medium transition-all">
                                <i class="ri-arrow-go-back-line me-1"></i> Admin Console
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KPI Widgets -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate shadow-sm border-0 rounded-4 overflow-hidden h-100" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-semibold text-white-50 text-truncate mb-2 fs-12">Total Leads</p>
                                    <h4 class="fs-28 fw-bold mb-0 text-white"><span class="counter-value" data-target="{{ $totalLeads }}">{{ $totalLeads }}</span></h4>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-3">
                                        <i class="ri-focus-3-line text-white"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate shadow-sm border-0 rounded-4 overflow-hidden h-100" style="background: linear-gradient(135deg, #FF416C 0%, #FF4B2B 100%); color: white;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-semibold text-white-50 text-truncate mb-2 fs-12">Pending Clients</p>
                                    <h4 class="fs-28 fw-bold mb-0 text-white"><span class="counter-value" data-target="{{ $activeTempClients }}">{{ $activeTempClients }}</span></h4>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-3">
                                        <i class="ri-time-line text-white"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate shadow-sm border-0 rounded-4 overflow-hidden h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-semibold text-white-50 text-truncate mb-2 fs-12">Converted Clients</p>
                                    <h4 class="fs-28 fw-bold mb-0 text-white"><span class="counter-value" data-target="{{ $convertedClients }}">{{ $convertedClients }}</span></h4>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-3">
                                        <i class="ri-check-double-line text-white"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card card-animate shadow-sm border-0 rounded-4 overflow-hidden h-100" style="background: linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%); color: white;">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-uppercase fw-semibold text-white-50 text-truncate mb-2 fs-12">Active Services</p>
                                    <h4 class="fs-28 fw-bold mb-0 text-white"><span class="counter-value" data-target="{{ $totalServices }}">{{ $totalServices }}</span></h4>
                                </div>
                                <div class="avatar-sm flex-shrink-0">
                                    <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-3">
                                        <i class="ri-briefcase-4-line text-white"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent pt-4 pb-0">
                            <h4 class="card-title mb-0 flex-grow-1 fw-bold fs-18"><i class="ri-bar-chart-2-fill text-primary me-2"></i>Leads Generated (Last 6 Months)</h4>
                        </div>
                        <div class="card-body pb-2">
                            <div id="leads-bar-chart" class="apex-charts" dir="ltr"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent pt-4 pb-0">
                            <h4 class="card-title mb-0 flex-grow-1 fw-bold fs-18"><i class="ri-pie-chart-2-fill text-danger me-2"></i>Conversion Rate</h4>
                        </div>
                        <div class="card-body pb-2 d-flex justify-content-center align-items-center" style="min-height: 320px;">
                            <div id="conversion-donut-chart" class="apex-charts" dir="ltr"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Leads Table -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header align-items-center d-flex border-0 bg-transparent pt-4 pb-3">
                            <h4 class="card-title mb-0 flex-grow-1 fw-bold fs-18"><i class="ri-list-check-2 text-success me-2"></i>Recent Lead Generations</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('admin.lead-generations.index') }}" class="btn btn-soft-primary btn-sm rounded-pill fw-medium px-3">View All</a>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive table-card">
                                <table class="table table-hover table-nowrap align-middle mb-0">
                                    <thead class="table-light text-muted">
                                        <tr>
                                            <th scope="col">Title</th>
                                            <th scope="col">Assigned To</th>
                                            <th scope="col">Stage</th>
                                            <th scope="col">Next Follow-up</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentLeads as $lead)
                                            <tr>
                                                <td class="fw-medium">{{ $lead->account_name }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <div class="avatar-xs">
                                                                <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-12">
                                                                    {{ substr($lead->assignee?->name ?? 'U', 0, 1) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">{{ $lead->assignee?->name ?? 'Unassigned' }}</div>
                                                    </div>
                                                </td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $lead->pipeline_stage)) }}</td>
                                                <td>{{ $lead->next_follow_up_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                                <td>{{ $lead->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    @if($lead->status)
                                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No recent leads found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Bar Chart
        var barChartOptions = {
            series: [{
                name: 'Leads',
                data: {!! json_encode($chartLeadCounts) !!}
            }],
            chart: {
                height: 350,
                type: 'bar',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '45%',
                    distributed: true,
                }
            },
            colors: ['#2a5298', '#11998e', '#FF416C', '#8E2DE2', '#f6d365', '#fda085'],
            dataLabels: { enabled: false },
            legend: { show: false },
            xaxis: {
                categories: {!! json_encode($chartMonths) !!},
                labels: {
                    style: {
                        colors: '#9ca3af',
                        fontSize: '13px'
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#9ca3af',
                        fontSize: '13px'
                    }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } }
            }
        };

        var barChart = new ApexCharts(document.querySelector("#leads-bar-chart"), barChartOptions);
        barChart.render();

        // Donut Chart
        var donutOptions = {
            series: [{!! $conversionRate['Pending'] !!}, {!! $conversionRate['Converted'] !!}],
            labels: ['Pending Clients', 'Converted Clients'],
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'Inter, sans-serif'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '14px', color: '#64748b' },
                            value: { show: true, fontSize: '24px', fontWeight: 700, color: '#1e293b' },
                            total: {
                                show: true,
                                label: 'Total',
                                color: '#64748b',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => { return a + b }, 0)
                                }
                            }
                        }
                    }
                }
            },
            colors: ['#FF416C', '#11998e'],
            dataLabels: { enabled: false },
            stroke: { width: 0 },
            legend: {
                position: 'bottom',
                markers: { radius: 12 },
                itemMargin: { horizontal: 10, vertical: 5 }
            }
        };

        var donutChart = new ApexCharts(document.querySelector("#conversion-donut-chart"), donutOptions);
        donutChart.render();
    });
</script>
@endsection