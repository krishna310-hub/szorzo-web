@extends('backend.layouts.master')
@section('title', 'Contract Report')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h4 class="mb-1">Contract Report</h4>
                        <div class="text-muted">Monthly attendance and payable salary for contract candidates</div>
                    </div>
                    <div class="d-flex gap-2">
                        @can('export', \App\Models\Report::class)
                            <form method="POST" action="{{ route('admin.contract-reports.refresh') }}">@csrf
                                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                                <button class="btn btn-primary"><i class="ri-refresh-line me-1"></i>Refresh Candidates</button>
                            </form>
                            <a class="btn btn-danger"
                                href="{{ route('admin.contract-reports.pdf', ['month' => $month->format('Y-m')]) }}"><i
                                    class="ri-file-pdf-2-line me-1"></i>Download PDF</a>
                        @endcan
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.contract-reports.index') }}"
                            class="row g-3 align-items-end">
                            <div class="col-sm-4 col-lg-3"><label class="form-label">Salary month</label><input
                                    type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control"
                                    required></div>
                            <div class="col-auto"><button class="btn btn-primary">View Month</button></div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title mb-0">{{ $month->format('F Y') }}</h5><span
                            class="badge bg-info-subtle text-info">{{ $daysInMonth }} calendar days</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Candidate</th>
                                        <th>Client / Role</th>
                                        <th>Recruiter</th>
                                        <th class="text-end">Monthly Take Home</th>
                                        <th style="min-width:110px">Present</th>
                                        <th style="min-width:110px">Leave Days</th>
                                        <th class="text-end">Payable Salary</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reports as $report)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $report->candidate?->candidate_name }}</div>
                                                <small class="text-muted">{{ $report->candidate?->email }}</small>
                                            </td>
                                            <td>{{ $report->candidate?->client?->client ?? '—' }}<br><small
                                                    class="text-muted">{{ $report->candidate?->jobRole?->job_role ?? '—' }}</small>
                                            </td>
                                            <td>{{ $report->candidate?->recruiter?->recruiter_name ?? '—' }}</td>
                                            <td class="text-end">&#8377;{{ number_format($report->monthly_take_home, 2) }}
                                            </td>
                                            <td><input form="contract-report-{{ $report->id }}" type="number"
                                                    name="present_days" min="0" max="{{ $daysInMonth }}"
                                                    value="{{ $report->present_days }}"
                                                    class="form-control form-control-sm" required></td>
                                            <td><input form="contract-report-{{ $report->id }}" type="number"
                                                    name="absent_days" min="0" max="{{ $daysInMonth }}"
                                                    value="{{ $report->absent_days }}" class="form-control form-control-sm"
                                                    required></td>
                                            <td class="text-end fw-semibold text-success">
                                                &#8377;{{ number_format($report->payable_salary, 2) }}</td>
                                            <td>
                                                @can('export', \App\Models\Report::class)
                                                    <div class="d-flex gap-1">
                                                        <form id="contract-report-{{ $report->id }}" method="POST"
                                                            action="{{ route('admin.contract-reports.update', $report) }}">
                                                            @csrf @method('PUT')<button
                                                                class="btn btn-sm btn-success">Save</button></form><a
                                                            class="btn btn-sm btn-danger"
                                                            href="{{ route('admin.contract-reports.invoice', $report) }}"
                                                            title="Download individual invoice"><i
                                                                class="ri-file-pdf-2-line"></i></a>
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">No active candidates with
                                                mode ID 2 have a contract covering this month. Click <strong>Refresh
                                                    Candidates</strong> after adding the contract dates in the candidate
                                                module.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if ($reports->count())
                                    <tfoot class="table-light fw-semibold">
                                        <tr>
                                            <td colspan="3">Page total</td>
                                            <td class="text-end">
                                                &#8377;{{ number_format($reports->sum('monthly_take_home'), 2) }}</td>
                                            <td class="text-center">{{ $reports->sum('present_days') }}</td>
                                            <td class="text-center">{{ $reports->sum('absent_days') }}</td>
                                            <td class="text-end">
                                                &#8377;{{ number_format($reports->sum('payable_salary'), 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                    @if ($reports->hasPages())
                        <div class="card-footer">{{ $reports->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
