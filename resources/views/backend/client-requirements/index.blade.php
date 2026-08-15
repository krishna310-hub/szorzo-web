@extends('backend.layouts.master')
@section('title', 'Client Requirements')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <h5 class="card-title mb-0 flex-grow-1">Client Requirements</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    @include('backend.partials.master-import-export', [
                                        'routePrefix' => 'client-requirements',
                                        'moduleName' => 'Client Requirements',
                                        'model' => \App\Models\ClientRequirement::class,
                                        'fields' => ['Record ID', 'Client', 'Billing', 'Revenue Amount', 'Job Description', 'Mode', 'Requirement Open Date', 'Job Role', 'Number Of Position', 'Closure Target Date', 'CV Required', 'CV Uploaded', 'Project Owner', 'Priority', 'CTC', 'Location', 'Status'],
                                    ])
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas" data-bs-target="#requirementFilters"><i class="ri-filter-3-line me-1"></i>Filter</button>
                                    @can('create', \App\Models\ClientRequirement::class)
                                        <a href="{{ route('admin.client-requirements.create') }}" class="btn btn-sm btn-primary">Add Client Requirement</a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                @include('backend.partials.import-feedback')
                                <div class="table-responsive">
                                    <table id="client-requirements-table" class="table table-bordered nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Client</th>
                                                <th>Billing</th>
                                                @if (auth()->user()->isSuperAdmin())
                                                    <th>Revenue</th>
                                                @endif
                                                <th>Job Role</th>
                                                <th>JD</th>
                                                <th>Position Level</th>
                                                <th>Mode</th>
                                                <th>Requirement Open Date</th>
                                                <th>Number Of Position</th>
                                                <th>Closure Target Date</th>
                                                <th>CV's Required</th>
                                                <th>CV's Uploaded</th>
                                                <th>Project Owner</th>
                                                <th>Priority</th>
                                                <th>CTC</th>
                                                <th>Location</th>
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

    <div class="offcanvas offcanvas-end" tabindex="-1" id="requirementFilters">
        <div class="offcanvas-header border-bottom"><h5 class="offcanvas-title">Client Requirement Filters</h5><button class="btn-close" data-bs-dismiss="offcanvas"></button></div>
        <form id="requirement-filter-form" class="offcanvas-body d-flex flex-column gap-3">
            <div><label class="form-label">Client</label><select name="client_id" class="form-select"><option value="">All clients</option>@foreach($clients as $item)<option value="{{ $item->id }}">{{ $item->client }}</option>@endforeach</select></div>
            <div><label class="form-label">Project Owner</label><select name="project_owner_id" class="form-select"><option value="">All project owners</option>@foreach($recruiters as $item)<option value="{{ $item->id }}">{{ $item->recruiter_name }}</option>@endforeach</select></div>
            <div><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="">All priorities</option><option value="1">Priority</option><option value="0">Non priority</option></select></div>
            <div><label class="form-label">Mode</label><select name="mode_id" class="form-select"><option value="">All modes</option>@foreach($modes as $item)<option value="{{ $item->id }}">{{ $item->mode }}</option>@endforeach</select></div>
            <div><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All statuses</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
            <div class="mt-auto d-flex gap-2 border-top pt-3"><button type="button" id="requirement-filter-reset" class="btn btn-light w-50">Reset</button><button class="btn btn-primary w-50">Apply</button></div>
        </form>
    </div>

    <div class="modal fade" id="job-description-modal" tabindex="-1" aria-labelledby="job-description-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="job-description-modal-title">Job Description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="job-description-modal-content"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            var currentFilters = {};
            var exportBaseUrl = @json(route('admin.client-requirements.export'));
            var table = $('#client-requirements-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false,
                fixedColumns: {
                    leftColumns: 3
                },
                ajax: {
                    url: '{{ route('admin.client-requirements.index') }}',
                    type: 'GET',
                    data: function(data) { Object.assign(data, currentFilters); }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'client_name',
                    name: 'client_name',
                }, {
                    data: 'billing_value',
                    name: 'billing_value'
                }
                @if (auth()->user()->isSuperAdmin())
                , {
                    data: 'revenue_amount',
                    name: 'revenue_amount'
                }
                @endif
                , {
                    data: 'job_role_name',
                    name: 'job_role_name',
                }, {
                    data: 'job_description_action',
                    name: 'job_description_action',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'psoition_level',
                    name: 'psoition_level',
                    orderable: false,
                    searchable: false
                },{
                    data: 'mode_name',
                    name: 'mode_name',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'requirement_open_date',
                    name: 'requirement_open_date'
                }, {
                    data: 'number_of_position',
                    name: 'number_of_position'
                }, {
                    data: 'closure_target_date',
                    name: 'closure_target_date'
                }, {
                    data: 'cv_required',
                    name: 'cv_required'
                }, {
                    data: 'cv_uploaded',
                    name: 'cv_uploaded'
                }, {
                    data: 'project_owner_name',
                    name: 'project_owner_name',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'priority',
                    name: 'priority',
                }, {
                    data: 'ctc',
                    name: 'ctc'
                }, {
                    data: 'location_name',
                    name: 'location_name',
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
                }]
            });

            function updateExportUrl() {
                var url = new URL(exportBaseUrl, window.location.origin);
                Object.keys(currentFilters).forEach(function(key) { url.searchParams.set(key, currentFilters[key]); });
                $('a[href^="' + exportBaseUrl + '"]').attr('href', url.toString());
            }
            $('#requirement-filter-form').on('submit', function(event) {
                event.preventDefault(); currentFilters = {};
                $(this).serializeArray().forEach(function(item) { if (item.value !== '') currentFilters[item.name] = item.value; });
                updateExportUrl(); table.ajax.reload(); bootstrap.Offcanvas.getInstance(document.getElementById('requirementFilters'))?.hide();
            });
            $('#requirement-filter-reset').on('click', function() { $('#requirement-filter-form')[0].reset(); currentFilters = {}; updateExportUrl(); table.ajax.reload(); });

            function sanitizeJobDescription(html) {
                const parsed = new DOMParser().parseFromString(html || '', 'text/html');
                parsed.querySelectorAll('script, iframe, object, embed').forEach(function(element) {
                    element.remove();
                });
                parsed.body.querySelectorAll('*').forEach(function(element) {
                    Array.from(element.attributes).forEach(function(attribute) {
                        const name = attribute.name.toLowerCase();
                        if (name.startsWith('on') ||
                            (['href', 'src'].includes(name) && /^\s*javascript:/i.test(attribute.value))) {
                            element.removeAttribute(attribute.name);
                        }
                    });
                });

                return parsed.body.innerHTML;
            }

            $(document).on('click', '.view-job-description', function() {
                const rowData = table.row($(this).closest('tr')).data();
                const content = rowData && rowData.job_description_content
                    ? sanitizeJobDescription(rowData.job_description_content)
                    : '<p class="text-muted mb-0">No job description is available.</p>';

                $('#job-description-modal-content').html(content);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('job-description-modal')).show();
            });

            $(document).on('click', '.delete-record', function() {
                if (!confirm('Are you sure you want to delete this client requirement?')) return;
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
