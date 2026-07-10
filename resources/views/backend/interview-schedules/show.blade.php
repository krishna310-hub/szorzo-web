@extends('backend.layouts.master')
@section('title', 'Interview History')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Interview History</h4>
                                <div class="d-flex gap-2">
                                    @can('create', \App\Models\Candidate::class)
                                        <a href="{{ route('admin.interview-schedules.create', ['candidate_id' => $candidate->id]) }}" class="btn btn-sm btn-primary">Add Schedule</a>
                                    @endcan
                                    <a href="{{ route('admin.interview-schedules.index') }}" class="btn btn-sm btn-light">Back</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row gy-3 mb-4">
                                    <div class="col-md-3">
                                        <div class="text-muted small">Candidate</div>
                                        <div class="fw-semibold">{{ $candidate->candidate_name }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small">Mobile</div>
                                        <div class="fw-semibold">{{ $candidate->mobile_no ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small">Recruiter</div>
                                        <div class="fw-semibold">{{ $candidate->recruiter?->recruiter_name ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small">Current Client</div>
                                        <div class="fw-semibold">{{ $candidate->client?->client ?? '-' }}</div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Client</th>
                                                <th>Job Role</th>
                                                <th>Level</th>
                                                <th>Schedule Date</th>
                                                <th>Status</th>
                                                <th>Notes</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($schedules as $schedule)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $schedule->client?->client ?? '-' }}</td>
                                                    <td>{{ $schedule->jobRole?->job_role ?? '-' }}</td>
                                                    <td>{{ $schedule->interviewLevel?->level ?? '-' }}</td>
                                                    <td>{{ $schedule->schedule_date?->format('d-m-Y H:i') ?? '-' }}</td>
                                                    <td>{{ \App\Models\InterviewSchedule::STATUSES[$schedule->status] ?? ucfirst($schedule->status) }}</td>
                                                    <td>{{ $schedule->notes ?: '-' }}</td>
                                                    <td>
                                                        @can('edit', \App\Models\Candidate::class)
                                                            <a href="{{ route('admin.interview-schedules.edit', $schedule->id) }}" class="text-info fs-4 me-1" title="Edit"><i class="bx bxs-edit"></i></a>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No interview schedule history found.</td>
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
