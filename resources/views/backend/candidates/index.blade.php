@extends('backend.layouts.master')
@section('title', 'Candidates')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Candidates</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    @include('backend.partials.master-import-export', [
                                        'routePrefix' => 'candidates',
                                        'moduleName' => 'Candidates',
                                        'model' => \App\Models\Candidate::class,
                                        'showExportFilters' => true,
                                        'fields' => [
                                            'Record ID',
                                            'Recruiter',
                                            'Client',
                                            'Job Role',
                                            'Candidate Name',
                                            'Mobile No',
                                            'Email',
                                            'Qualification',
                                            'Total Experience',
                                            'Relevant Experience',
                                            'Take Home',
                                            'Variable',
                                            'Current CTC',
                                            'Expected CTC',
                                            'Notice Period',
                                            'Current Company',
                                            'Current Location',
                                            'Preferred Location',
                                            'Reason For Change',
                                            'Level Of Interview',
                                            'Status',
                                        ],
                                    ])
                                    @can('create', \App\Models\Candidate::class)
                                        <a href="{{ route('admin.candidates.create') }}" class="btn btn-sm btn-primary">Add New
                                            Candidate</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">@include('backend.partials.import-feedback')<div class="table-responsive">
                                    <table id="candidates-table" class="table table-bordered nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Candidate Name</th>
                                                <th>CV</th>
                                                <th>Recruiter</th>
                                                <th>Client</th>
                                                <th>Job Role</th>
                                                <th>Mobile No</th>
                                                <th>Email</th>
                                                <th>Qualification</th>
                                                <th>Total Experience</th>
                                                <th>Relevant Experience</th>
                                                <th>Take Home</th>
                                                <th>Variable</th>
                                                <th>Current CTC</th>
                                                <th>Expected CTC</th>
                                                <th>Notice Period</th>
                                                <th>Current Company</th>
                                                <th>Current Location</th>
                                                <th>Preferred Location</th>
                                                <th>Reason For Change</th>
                                                <th>Level Of Interview</th>
                                                <th>Status</th>
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

    <div class="offcanvas offcanvas-end" tabindex="-1" id="candidateFilterOffcanvas" aria-labelledby="candidateFilterOffcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="candidateFilterOffcanvasLabel">Candidate Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <form id="candidate-filter-form" class="offcanvas-body d-flex flex-column gap-3">
            <div>
                <label for="filter_from_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="filter_from_date" name="from_date">
            </div>
            <div>
                <label for="filter_to_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="filter_to_date" name="to_date">
            </div>
            <div>
                <label for="filter_recruiter_id" class="form-label">Recruiter</label>
                <select class="form-select" id="filter_recruiter_id" name="recruiter_id">
                    <option value="">All recruiters</option>
                    @foreach ($recruiters as $recruiter)
                        <option value="{{ $recruiter->id }}">{{ $recruiter->recruiter_name }}</option>
                    @endforeach
                </select>
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
                <label for="filter_level_of_interview_id" class="form-label">Interview Level</label>
                <select class="form-select" id="filter_level_of_interview_id" name="level_of_interview_id[]" multiple>
                    @foreach ($interviewLevels as $level)
                        <option value="{{ $level->id }}">{{ $level->level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-auto d-flex gap-2 border-top pt-3">
                <button type="button" class="btn btn-light w-50" id="candidate-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary w-50">Apply</button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            var exportBaseUrl = @json(route('admin.candidates.export'));
            var indexUrl = @json(route('admin.candidates.index'));
            var currentFilters = {};
            var interviewLevelFilter = new Choices('#filter_level_of_interview_id', {
                removeItemButton: true,
                shouldSort: false,
                placeholder: true,
                placeholderValue: 'Select interview levels'
            });

            function collectFilters() {
                var filters = {};
                $('#candidate-filter-form').serializeArray().forEach(function(item) {
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
                Object.keys(currentFilters).forEach(function(key) {
                    var values = Array.isArray(currentFilters[key]) ? currentFilters[key] : [currentFilters[key]];
                    values.forEach(function(value) {
                        url.searchParams.append(key + (Array.isArray(currentFilters[key]) ? '[]' : ''), value);
                    });
                });
                $('a[href^="' + exportBaseUrl + '"]').attr('href', url.toString());
            }

            var table = $('#candidates-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: indexUrl,
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
                        name: 'candidate_name'
                    },
                    {
                        data: 'cv_preview',
                        name: 'cv_preview',
                        orderable: false,
                        searchable: false
                    },
                    {
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
                    },
                    {
                        data: 'mobile_no',
                        name: 'mobile_no'
                    }, {
                        data: 'email',
                        name: 'email'
                    }, {
                        data: 'qualification',
                        name: 'qualification'
                    },
                    {
                        data: 'total_experience',
                        name: 'total_experience'
                    }, {
                        data: 'relevant_experience',
                        name: 'relevant_experience'
                    }, {
                        data: 'take_home',
                        name: 'take_home'
                    }, {
                        data: 'variable',
                        name: 'variable'
                    },
                    {
                        data: 'current_ctc',
                        name: 'current_ctc'
                    }, {
                        data: 'expected_ctc',
                        name: 'expected_ctc'
                    }, {
                        data: 'notice_period',
                        name: 'notice_period'
                    }, {
                        data: 'current_company',
                        name: 'current_company'
                    },
                    {
                        data: 'current_location',
                        name: 'current_location'
                    }, {
                        data: 'preferred_location',
                        name: 'preferred_location'
                    }, {
                        data: 'reason_for_change',
                        name: 'reason_for_change'
                    },
                    {
                        data: 'interview_level',
                        name: 'interview_level',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'created_at',
                        name: 'created_at'
                    }, {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
            updateExportUrl();

            $('#candidate-filter-form').on('submit', function(e) {
                e.preventDefault();
                currentFilters = collectFilters();
                updateExportUrl();
                table.ajax.reload();

                var offcanvasEl = document.getElementById('candidateFilterOffcanvas');
                var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                if (offcanvas) {
                    offcanvas.hide();
                }
            });

            $('#candidate-filter-reset').on('click', function() {
                $('#candidate-filter-form')[0].reset();
                interviewLevelFilter.removeActiveItems();
                currentFilters = {};
                updateExportUrl();
                table.ajax.reload();
            });

            $(document).on('click', '.delete-record', function() {
                if (!confirm('Are you sure you want to delete this candidate?')) return;
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
