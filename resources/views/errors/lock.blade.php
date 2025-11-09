<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="gradient-4" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>
    <meta charset="utf-8" />
    <title>Maintenance Mode | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('frontend/images/glow-unlock-favicon.png') }}">

    @include('backend.layouts.css_master')
</head>

<body>
<div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <!-- auth-page content -->
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card overflow-hidden galaxy-border-none card-bg-fill">
                            <div class="row justify-content-center g-0">
                                <div class="col-lg-6">
                                    <div id="qoutescarouselIndicators" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="3000">
                                        
                                        <!-- Indicators -->
                                        <div class="carousel-indicators">
                                            @foreach ($sliders as $index => $slider)
                                                <button type="button" data-bs-target="#qoutescarouselIndicators"
                                                    data-bs-slide-to="{{ $index }}"
                                                    @if ($index == 0) class="active" aria-current="true" @endif
                                                    aria-label="Slide {{ $index + 1 }}">
                                                </button>
                                            @endforeach
                                        </div>

                                        <!-- Slides -->
                                        <div class="carousel-inner h-100">
                                            @foreach ($sliders as $index => $slider)
                                                <div class="carousel-item @if ($index == 0) active @endif">
                                                    <img src="{{ asset($slider->image) }}"
                                                        class="d-block w-100 h-100 slide-bg-img"
                                                        style="object-fit: cover;" alt="Slide {{ $index + 1 }}">
                                                    <div class="bg-overlay"></div>
                                                    <div class="carousel-caption d-flex flex-column justify-content-center h-100">
                                                        <div class="mb-4">
                                                            <a href="{{ route('admin.dashboard') }}" class="d-block">
                                                                <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/glow-unlock-favicon.png') }}"
                                                                    alt="Logo" height="100">
                                                            </a>
                                                        </div>
                                                        <div class="mb-3">
                                                            {{-- <i class="ri-double-quotes-l display-4 text-success"></i>
                                                             --}}
                                                             <span class="">{{$slider->content_head}}</span>
                                                        </div>
                                                        <p class="fs-15 fst-italic text-white">
                                                            {{ $slider->content_body ?? '' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Controls -->
                                        <button class="carousel-control-prev" type="button" data-bs-target="#qoutescarouselIndicators" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#qoutescarouselIndicators" data-bs-slide="next">
                                            <span class="carousel-control-next-icon"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    </div>
                                </div>
                                <!-- end col -->

                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4">
                                        <div>
                                            <h5 class="text-primary">Lock Screen</h5>
                                            <p class="text-muted">Enter your password to unlock the screen!</p>
                                        </div>
                                        <div class="user-thumb text-center">
                                            <img src="{{ Auth::user()?->profile_picture ? asset(Auth::user()->profile_picture) : asset('admin/images/users/user-dummy-img.jpg') }}" class="rounded-circle img-thumbnail avatar-lg material-shadow" alt="thumbnail">
                                            <h5 class="mt-3">{{ auth()->user()->name }}</h5>
                                        </div>

                                        <div class="mt-4">
                                            <form method="POST" action="{{ route('admin.lock.screen.unlock') }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="password" class="form-label">
                                                        Change New Password <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="password" id="password" class="form-control" name="password"
                                                            placeholder="Enter password" value="">
                                                        <span class="input-group-text" id="password-addon" style="cursor: pointer;">
                                                            <i toggle="#password" class="ri-eye-fill align-middle toggle-password"></i>
                                                        </span>
                                                    </div>
                                                    @error('password')
                                                        <div class="error">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-2 mt-4">
                                                    <button class="btn btn-success w-100" type="submit">Unlock</button>
                                                </div>
                                            </form><!-- end form -->
                                        </div>

                                        <div class="mt-5 text-center">
                                            <p class="mb-0">Not you ? return <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline"> Login</a></p>
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->
                            </div>
                            <!-- end row -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->
        @include('backend.layouts.js_master')
        <!-- footer -->
        <footer class="footer galaxy-border-none">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0">&copy;
                                <script>document.write(new Date().getFullYear())</script> Velzon. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>

</body>
</html>
