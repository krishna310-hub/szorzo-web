@extends('backend.layouts.master')
@section('title', 'Candidates')
@section('content')
<div class="main-content"><div class="page-content"><div class="container-fluid"><div class="row"><div class="col-lg-12"><div class="card">
<div class="card-header d-flex align-items-center"><h5 class="card-title mb-0 flex-grow-1">Candidates</h5>@can('create', \App\Models\Candidate::class)<a href="{{ route('admin.candidates.create') }}" class="btn btn-sm btn-primary">Add New Candidate</a>@endcan</div>
<div class="card-body"><div class="table-responsive"><table id="candidates-table" class="table table-bordered nowrap w-100"><thead><tr>
<th>S.No</th><th>Recruiter</th><th>Client</th><th>Job Role</th><th>Candidate Name</th><th>Mobile No</th><th>Email</th><th>Qualification</th><th>Total Experience</th><th>Relevant Experience</th><th>Take Home</th><th>Variable</th><th>Current CTC</th><th>Expected CTC</th><th>Notice Period</th><th>Current Company</th><th>Current Location</th><th>Preferred Location</th><th>Reason For Change</th><th>Level Of Interview</th><th>Status</th><th>Created At</th><th>Action</th>
</tr></thead><tbody></tbody></table></div></div>
</div></div></div></div></div></div>
@endsection
@section('script')
<script>
$(document).ready(function () {
var table = $('#candidates-table').DataTable({ processing: true, serverSide: true, scrollX: true, ajax: { url: '{{ route('admin.candidates.index') }}', type: 'GET' }, columns: [
{ data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
{ data: 'recruiter_name', name: 'recruiter_name', orderable: false, searchable: false }, { data: 'client_name', name: 'client_name', orderable: false, searchable: false }, { data: 'job_role_name', name: 'job_role_name', orderable: false, searchable: false },
{ data: 'candidate_name', name: 'candidate_name' }, { data: 'mobile_no', name: 'mobile_no' }, { data: 'email', name: 'email' }, { data: 'qualification', name: 'qualification' },
{ data: 'total_experience', name: 'total_experience' }, { data: 'relevant_experience', name: 'relevant_experience' }, { data: 'take_home', name: 'take_home' }, { data: 'variable', name: 'variable' },
{ data: 'current_ctc', name: 'current_ctc' }, { data: 'expected_ctc', name: 'expected_ctc' }, { data: 'notice_period', name: 'notice_period' }, { data: 'current_company', name: 'current_company' },
{ data: 'current_location', name: 'current_location' }, { data: 'preferred_location', name: 'preferred_location' }, { data: 'reason_for_change', name: 'reason_for_change' },
{ data: 'interview_level', name: 'interview_level', orderable: false, searchable: false }, { data: 'status', name: 'status', orderable: false, searchable: false }, { data: 'created_at', name: 'created_at' }, { data: 'action', name: 'action', orderable: false, searchable: false }
]});
$(document).on('click', '.delete-record', function () { if (!confirm('Are you sure you want to delete this candidate?')) return; $.ajax({ url: $(this).data('route'), type: 'DELETE', success: function (res) { res.status ? toastr.success(res.message) : toastr.error(res.message); table.ajax.reload(null, false); }}); });
});
</script>
@endsection
