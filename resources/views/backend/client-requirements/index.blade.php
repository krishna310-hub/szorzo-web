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
                            <a href="{{ route('admin.client-requirements.create') }}" class="btn btn-sm btn-primary">Add Client Requirement</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Client</th>
                                            <th>Billing %</th>
                                            <th>Job Description ID</th>
                                            <th>Mode</th>
                                            <th>Open Date</th>
                                            <th>Job Role</th>
                                            <th>CTC</th>
                                            <th>Location</th>
                                            <th>No.Of.Position</th>
                                            <th>Closure Target Date</th>
                                            <th>CV's Required</th>
                                            <th>CV's Uploaded</th>
                                            <th>Project Owner</th>
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
@endsection

@section('script')
<script>

</script>
@endsection
