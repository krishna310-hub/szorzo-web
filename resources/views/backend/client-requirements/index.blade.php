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
                                                <th>Onboarded</th>
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

    <div class="modal fade" id="job-description-modal" tabindex="-1" aria-labelledby="job-description-modal-title" aria-hidden="true" style="z-index: 1065 !important;">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="z-index: 1070 !important;">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light py-3 border-bottom">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="job-description-modal-title">
                        <i class="ri-file-text-line text-primary fs-4"></i>
                        <span id="job-description-modal-title-text">Job Description</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="job-description-modal-content"></div>
                <div class="modal-footer bg-light py-2 border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
<style>
    #job-description-modal {
        z-index: 1065 !important;
    }
    #job-description-modal .modal-dialog {
        z-index: 1070 !important;
    }
    #job-description-modal-content {
        max-height: 70vh;
        overflow-y: auto !important;
        word-break: break-word;
    }
    #job-description-modal-content img {
        max-width: 100%;
        height: auto;
    }
    #job-description-modal-content table {
        width: 100% !important;
        margin-bottom: 1rem;
        border-collapse: collapse;
    }
    #job-description-modal-content table th,
    #job-description-modal-content table td {
        padding: 0.5rem;
        border: 1px solid #dee2e6;
    }
</style>
@endpush
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
                    data: 'onboarded_count',
                    name: 'onboarded_count',
                    orderable: false,
                    searchable: false
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
                if (!html || typeof html !== 'string') {
                    return '<p class="text-muted mb-0">No job description is available.</p>';
                }
                try {
                    const parsed = new DOMParser().parseFromString(html, 'text/html');
                    // Strip dangerous tags, stylesheets, and external links that could affect host page layout
                    parsed.querySelectorAll('script, style, link, iframe, object, embed').forEach(function(element) {
                        element.remove();
                    });
                    // Strip inline javascript handlers and javascript: URLs
                    parsed.body.querySelectorAll('*').forEach(function(element) {
                        Array.from(element.attributes).forEach(function(attribute) {
                            const name = attribute.name.toLowerCase();
                            if (name.startsWith('on') ||
                                (['href', 'src'].includes(name) && /^\s*javascript:/i.test(attribute.value))) {
                                element.removeAttribute(attribute.name);
                            }
                        });
                    });

                    const clean = parsed.body.innerHTML.trim();
                    return clean || '<p class="text-muted mb-0">No job description is available.</p>';
                } catch (e) {
                    console.error('Error sanitizing job description:', e);
                    return $('<div>').text(html).html();
                }
            }

            function ensureModalInBody() {
                const modal = document.getElementById('job-description-modal');
                if (modal && modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
            }

            // Move modal to body immediately so it is not trapped behind backdrops
            ensureModalInBody();

            // When modal is hidden, clean up any remaining backdrops and reset body lock
            $('#job-description-modal').on('hidden.bs.modal', function() {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
            });

            // Fallback close handler to guarantee modal dismisses reliably
            $(document).on('click', '#job-description-modal [data-bs-dismiss="modal"]', function(e) {
                e.preventDefault();
                const modalEl = document.getElementById('job-description-modal');
                if (window.bootstrap && bootstrap.Modal) {
                    const inst = bootstrap.Modal.getInstance(modalEl);
                    if (inst) {
                        inst.hide();
                    } else {
                        $(modalEl).modal('hide');
                    }
                } else {
                    $(modalEl).modal('hide');
                }
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
            });

            $(document).on('click', '.view-job-description', function(e) {
                e.preventDefault();
                e.stopPropagation();

                ensureModalInBody();

                // Clean up any stale or stacked backdrops
                if (!$('#job-description-modal').hasClass('show')) {
                    $('.modal-backdrop').remove();
                }

                const button = $(this);
                const reqId = button.data('id');
                const customTitle = button.data('title');

                if (customTitle) {
                    $('#job-description-modal-title-text').text(customTitle);
                } else {
                    $('#job-description-modal-title-text').text('Job Description');
                }

                // Check DataTables cache first
                let rowData = null;
                const tr = button.closest('tr');
                if (tr.length && typeof table !== 'undefined' && table.row) {
                    rowData = table.row(tr).data();
                }
                if (!rowData && button.closest('td').length && typeof table !== 'undefined' && table.row) {
                    rowData = table.row(button.closest('td')).data();
                }

                const modalEl = document.getElementById('job-description-modal');
                const showModal = function() {
                    if (window.bootstrap && bootstrap.Modal) {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    } else {
                        $(modalEl).modal('show');
                    }
                };

                if (rowData && rowData.job_description_content !== undefined && rowData.job_description_content !== null) {
                    const cleanContent = sanitizeJobDescription(rowData.job_description_content);
                    $('#job-description-modal-content').html(cleanContent);
                    showModal();
                } else if (reqId) {
                    // Show spinner while fetching via dedicated endpoint
                    $('#job-description-modal-content').html(
                        '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted mb-0">Loading job description...</p></div>'
                    );
                    showModal();

                    $.ajax({
                        url: '{{ url('admin/client-requirements') }}/' + reqId + '/job-description',
                        type: 'GET',
                        success: function(res) {
                            if (res && res.title) {
                                $('#job-description-modal-title-text').text(res.title);
                            }
                            const content = res && res.content
                                ? sanitizeJobDescription(res.content)
                                : '<p class="text-muted mb-0">No job description is available.</p>';
                            $('#job-description-modal-content').html(content);
                        },
                        error: function() {
                            $('#job-description-modal-content').html('<div class="alert alert-danger mb-0">Failed to load job description.</div>');
                        }
                    });
                } else {
                    $('#job-description-modal-content').html('<p class="text-muted mb-0">No job description is available.</p>');
                    showModal();
                }
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
