@extends('backend.layouts.master')
@section('title', 'Interview Schedules List')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Interview Scheduled List</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('admin.interview-schedules.export') }}" id="interview-schedule-export" class="btn btn-sm btn-success">
                                        <i class="ri-file-excel-2-line me-1"></i> Export
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas" data-bs-target="#interviewScheduleFilterOffcanvas" aria-controls="interviewScheduleFilterOffcanvas">
                                        <i class="ri-filter-3-line me-1"></i> Filter
                                    </button>
                                    @can('create', \App\Models\Candidate::class)
                                        <a href="{{ route('admin.interview-schedules.create') }}" class="btn btn-sm btn-primary">Add Schedule</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="interview-schedules-table" class="table table-bordered nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Candidate</th>
                                                <th>Mobile No</th>
                                                <th>Recruiter</th>
                                                <th>Client</th>
                                                <th>Job Role</th>
                                                <th>Level</th>
                                                <th>Schedule Date</th>
                                                <th>Status</th>
                                                <th>Notes</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="interviewScheduleFilterOffcanvas" aria-labelledby="interviewScheduleFilterOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="interviewScheduleFilterOffcanvasLabel">Interview Schedule Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <form id="interview-schedule-filter-form" class="offcanvas-body d-flex flex-column gap-3">
            <div>
                <label for="filter_from_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="filter_from_date" name="from_date">
            </div>
            <div>
                <label for="filter_to_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="filter_to_date" name="to_date">
            </div>
            <div>
                <label for="filter_candidate_id" class="form-label">Candidate</label>
                <select class="form-select" id="filter_candidate_id" name="candidate_id">
                    <option value="">All candidates</option>
                    @foreach ($candidates as $candidate)
                        <option value="{{ $candidate->id }}">{{ $candidate->candidate_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_recruiter_id" class="form-label">Recruiter</label>
                @if ($isRecruiterScheduleList)
                    <select class="form-select" id="filter_recruiter_id" disabled>
                        <option selected>{{ $linkedRecruiter?->recruiter_name ?? 'Recruiter not linked' }}</option>
                    </select>
                    @if ($linkedRecruiter)
                        <input type="hidden" name="recruiter_id" value="{{ $linkedRecruiter->id }}">
                    @endif
                @else
                    <select class="form-select" id="filter_recruiter_id" name="recruiter_id">
                        <option value="">All recruiters</option>
                        @foreach ($recruiters as $recruiter)
                            <option value="{{ $recruiter->id }}">{{ $recruiter->recruiter_name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div>
                <label for="filter_client_id" class="form-label">Client</label>
                <select class="form-select" id="filter_client_id" name="client_id">
                    <option value="">All clients</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->client }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_job_role_id" class="form-label">Job Role</label>
                <select class="form-select" id="filter_job_role_id" name="job_role_id">
                    <option value="">All job roles</option>
                    @foreach ($jobRoles as $jobRole)
                        <option value="{{ $jobRole->id }}">{{ $jobRole->job_role }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_level_of_interview_id" class="form-label">Level Of Interview</label>
                <select class="form-select" id="filter_level_of_interview_id" name="level_of_interview_id[]" multiple>
                    @foreach ($interviewLevels as $level)
                        <option value="{{ $level->id }}">{{ $level->level }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_status" class="form-label">Status</label>
                <select class="form-select" id="filter_status" name="status">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-auto d-flex gap-2 border-top pt-3">
                <button type="button" class="btn btn-light w-50" id="interview-schedule-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary w-50">Apply</button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            var currentFilters = {};
            var exportBaseUrl = @json(route('admin.interview-schedules.export'));
            var interviewLevelFilter = new Choices('#filter_level_of_interview_id', {
                removeItemButton: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Select interview levels'
            });

            function collectFilters() {
                var filters = {};
                $('#interview-schedule-filter-form').serializeArray().forEach(function(item) {
                    if (item.value) {
                        var name = item.name.replace(/\[\]$/, '');
                        if (item.name.endsWith('[]')) {
                            filters[name] = filters[name] || [];
                            filters[name].push(item.value);
                        } else {
                            filters[name] = item.value;
                        }
                    }
                });
                return filters;
            }

            function updateExportUrl() {
                var url = new URL(exportBaseUrl, window.location.origin);
                Object.entries(currentFilters).forEach(function(entry) {
                    var key = entry[0];
                    var value = entry[1];
                    (Array.isArray(value) ? value : [value]).forEach(function(item) {
                        url.searchParams.append(Array.isArray(value) ? key + '[]' : key, item);
                    });
                });
                $('#interview-schedule-export').attr('href', url.toString());
            }

            var table = $('#interview-schedules-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: @json(route('admin.interview-schedules.index')),
                    type: 'GET',
                    data: function(d) {
                        Object.assign(d, currentFilters);
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'candidate_name',
                    name: 'candidate_name',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'candidate_mobile',
                    name: 'candidate_mobile',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'recruiter_name',
                    name: 'recruiter_name',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'client_name',
                    name: 'client_name',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'job_role_name',
                    name: 'job_role_name',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'interview_level',
                    name: 'interview_level',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'schedule_date',
                    name: 'schedule_date'
                }, {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'notes',
                    name: 'notes'
                }, {
                    data: 'created_at',
                    name: 'created_at',
                }, {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }]
            });

            $('#interview-schedule-filter-form').on('submit', function(e) {
                e.preventDefault();
                currentFilters = collectFilters();
                updateExportUrl();
                table.ajax.reload();

                var offcanvasEl = document.getElementById('interviewScheduleFilterOffcanvas');
                var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (offcanvas) {
                    offcanvas.hide();
                }
            });

            $('#interview-schedule-filter-reset').on('click', function() {
                $('#interview-schedule-filter-form')[0].reset();
                interviewLevelFilter.removeActiveItems();
                currentFilters = {};
                updateExportUrl();
                table.ajax.reload();
            });

            $(document).on('click', '.delete-record', function() {
                if (!confirm('Are you sure you want to delete this interview schedule?')) return;
                $.ajax({
                    url: $(this).data('route'),
                    type: 'DELETE',
                    success: function(res) {
                        res.status ? toastr.success(res.message) : toastr.error(res.message);
                        table.ajax.reload(null, false);
                    }
                });
            });
        });
    </script>
@endsection
