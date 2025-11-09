<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Login | Glow Unlock')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{asset('frontend/images/glow-unlock-favicon.png')}}">

    @include('backend.layouts.css_master')
    <link rel="stylesheet" href="{{ asset('vendor/flasher/flasher.min.css')}}">
    {{-- @include('') --}}
</head>

<body>
    <div id="layout-wrapper">
        <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
            <div class="bg-overlay"></div>
            <!-- auth-page content -->
            <div class="auth-page-content overflow-hidden pt-lg-5">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card overflow-hidden card-bg-fill galaxy-border-none">
                                <div class="row g-0">
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
                                                <h5 class="text-primary">Welcome Back !</h5>
                                                <p class="text-muted">Sign in to continue to 
                                                    {{ $settings['app_name'] ?? 'Dubda Butchers' }}.</p>
                                            </div>

                                            <div class="mt-4">
                                                <form method="POST" action="{{ route('login.check') }}">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label for="username" class="form-label">Username</label>
                                                        <input type="email" class="form-control" name="email" id="username" placeholder="Enter username" value="{{ old('email') }}" required>
                                                    </div>
                                                    @error('email')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                    <div class="mb-3">
                                                    <div class="input-group">
                                                        <label class="form-label"
                                                            for="password-input">Password</label>
                                                        <div class="input-group">
                                                            <input type="password" id="password"
                                                                class="form-control" name="password"
                                                                placeholder="Enter password" value="">
                                                            <span class="input-group-text" id="password-addon"
                                                                style="cursor: pointer;">
                                                                <i toggle="#password"
                                                                    class="ri-eye-fill align-middle toggle-password"></i>
                                                            </span>
                                                        </div>
                                                        @error('password')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                    </div>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="auth-remember-check" {{ old('remember') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="auth-remember-check">Remember me</label>
                                                    </div>

                                                    <div class="mt-4">
                                                        <button class="btn btn-success w-100" type="submit">Sign In</button>
                                                    </div>

                                                    {{-- <div class="mt-4 text-center">
                                                        <div class="signin-other-title">
                                                            <h5 class="fs-13 mb-4 title">Sign In with</h5>
                                                        </div>

                                                        <div>
                                                            <button type="button" class="btn btn-primary btn-icon waves-effect waves-light"><i class="ri-facebook-fill fs-16"></i></button>
                                                            <button type="button" class="btn btn-danger btn-icon waves-effect waves-light"><i class="ri-google-fill fs-16"></i></button>
                                                            <button type="button" class="btn btn-dark btn-icon waves-effect waves-light"><i class="ri-github-fill fs-16"></i></button>
                                                            <button type="button" class="btn btn-info btn-icon waves-effect waves-light"><i class="ri-twitter-fill fs-16"></i></button>
                                                        </div>
                                                    </div> --}}

                                                </form>
                                            </div>

                                            <div class="mt-5 text-center">
                                                {{-- <p class="mb-0">Don't have an account ? <a href="auth-signup-cover.html" class="fw-semibold text-primary text-decoration-underline"> Signup</a> </p> --}}
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
            <!-- footer -->
            <footer class="footer galaxy-border-none">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                <p class="mb-0">©
                                    <script>
                                        document.write(new Date().getFullYear())
                                    </script> All rights reserved. by <i
                                        class="mdi mdi-heart text-danger"></i> {{ config('app.name') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
@include('backend.layouts.js_master')
<script src="{{ asset('vendor/flasher/flasher.min.js')}}"></script>
