<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employee Onboarding | {{ config('app.name') }}</title>
    <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/app.min.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 1200px">
        <div class="card-header bg-white py-3"><h3 class="mb-1">Employee Onboarding Form</h3><p class="text-muted mb-0">Complete and submit your information. This link can be used once.</p></div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('employee-onboarding.store', $onboardingLink->token) }}" enctype="multipart/form-data">
                @csrf
                @include('backend.employees.form')
            </form>
        </div>
    </div>
</main>
<script src="{{ asset('admin/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@stack('script')
</body>
</html>
