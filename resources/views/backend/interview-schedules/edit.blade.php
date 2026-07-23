@extends('backend.layouts.master')
@section('title', 'Edit Interview Schedule')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Edit Interview Schedule</h4>
                                <a href="{{ route('admin.interview-schedules.index') }}" class="btn btn-sm btn-light">Back</a>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.interview-schedules.update', $interviewSchedule->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
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
    $(document).ready(function () {

        function toggleOnboardingDate() {

            var selectedLevel = $('#level_of_interview_id option:selected').text().trim();
            var onboardingDate = $('#onboarding_date').val();

            if (selectedLevel === 'Offer Released' || onboardingDate !== '') {
                $('#onboardingDateDiv').show();
            } else {
                $('#onboardingDateDiv').hide();
            }
        }

        $('#level_of_interview_id').on('change', function () {
            toggleOnboardingDate();
        });

        toggleOnboardingDate();
    });
</script>
@endsection
