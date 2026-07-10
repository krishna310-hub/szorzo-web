@extends('backend.layouts.master')
@section('title', 'Add Interview Schedule')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Add Interview Schedule</h4>
                                <a href="{{ route('admin.interview-schedules.index') }}" class="btn btn-sm btn-light">Back</a>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.interview-schedules.store') }}" method="POST">
                                    @csrf
                                    @include('backend.interview-schedules.form')
                                </form>
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
        $(document).ready(function() {
            $('#candidate_id').on('change', function() {
                var option = $(this).find(':selected');
                if (option.val()) {
                    $('#client_id').val(option.data('client-id') || '');
                    $('#job_role_id').val(option.data('job-role-id') || '');
                    $('#level_of_interview_id').val(option.data('level-id') || '');
                }
            });
        });
    </script>
@endsection
