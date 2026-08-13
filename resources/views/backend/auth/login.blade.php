<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Login | SZORZO')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('frontend/images/favicon.png') }}">
    @include('backend.layouts.css_master')
    <link rel="stylesheet" href="{{ asset('vendor/flasher/flasher.min.css') }}">

    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background-color: #f8fafc;
        }

        .animated-gradient-bg {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            /* Changed overflow to allow vertical scrolling on small screens */
            overflow-x: hidden;
            overflow-y: auto;
            /* Added padding to prevent the card from touching the screen edges */
            padding: 2rem 1rem;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(75px);
            z-index: 1;
            animation: floatShape 20s ease-in-out infinite;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            background: rgba(220, 38, 38, 0.12);
            top: -10%;
            left: -10%;
        }

        .shape-2 {
            width: 450px;
            height: 450px;
            background: rgba(254, 205, 211, 0.5);
            bottom: -5%;
            right: -5%;
            animation-delay: -5s;
            animation-direction: reverse;
        }

        .shape-3 {
            width: 600px;
            height: 600px;
            background: rgba(252, 165, 165, 0.2);
            top: 25%;
            left: 35%;
            animation-delay: -10s;
        }

        @keyframes floatShape {
            0% {
                transform: translate(0, 0) scale(1);
            }
            33% {
                transform: translate(40px, -50px) scale(1.1);
            }
            66% {
                transform: translate(-30px, 30px) scale(0.9);
            }
            100% {
                transform: translate(0, 0) scale(1);
            }
        }

        .glass-login-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 1050px;
            /* Using auto margins for better centering in flexbox */
            margin: auto;
            display: flex;
            flex-wrap: wrap;
            z-index: 10;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .image-container {
            position: relative;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .image-container::before {
            content: '';
            position: absolute;
            top: -15%;
            right: -15%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            z-index: 0;
        }

        .image-container::after {
            content: '';
            position: absolute;
            bottom: -5%;
            left: -10%;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            z-index: 0;
        }

        .brand-logo-wrapper {
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
            align-self: flex-start;
        }

        .brand-logo-wrapper:hover {
            transform: scale(1.05);
        }

        .illustration-wrapper {
            position: relative;
            background: #ffffff;
            width: min(380px, 90vw);
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2rem auto;
            overflow: hidden;
        }

        .side-illustration {
            width: 100%;
            max-width: 360px;
            height: auto;
            object-fit: contain;
            animation: floatImg 5s ease-in-out infinite;
            /* filter: drop-shadow(0px 25px 35px rgba(130, 0, 0, 0.4)); */
        }

        @keyframes floatImg {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        .text-content {
            position: relative;
            z-index: 2;
        }

        .form-container {
            padding: 4.5rem 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.7);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.85rem 1.2rem;
            font-size: 1rem;
            color: #334155;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15);
            background: #fff;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid #e2e8f0;
            border-left: none;
            border-radius: 0 12px 12px 0;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .form-control:focus+.input-group-text {
            border-color: #dc2626;
            background: #fff;
        }

        .btn-animated {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
            background-size: 200% auto;
            border: none;
            color: #ffffff;
            padding: 0.9rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.5px;
            transition: 0.4s;
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.25);
        }

        .btn-animated:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(220, 38, 38, 0.4);
            color: #ffffff;
        }

        /* FIXED FOOTER CREDITS */
        .footer-credits {
            /* Changed from position: absolute to relative document flow */
            position: relative;
            margin-top: 1.5rem;
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
            z-index: 10;
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .image-container {
                display: none;
            }

            .form-container {
                padding: 3rem 2rem;
                border-radius: 24px;
            }
        }

        @media (max-width: 575.98px) {
            .form-container {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="animated-gradient-bg">

        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>
        <div class="bg-shape shape-3"></div>

        <div class="glass-login-card row g-0">

            <div class="col-lg-6 image-container">

                <div class="brand-logo-wrapper">
                    <a href="{{ route('admin.dashboard') }}">
                        <img src="{{ isset($settings['app_logo']) ? asset($settings['app_logo']) : asset('frontend/images/logo-bg.webp') }}"
                            alt="SZORZO Logo" height="45" style="filter: brightness(0) invert(1);">
                    </a>
                </div>

                <div class="illustration-wrapper">
                    <img src="{{ asset('frontend/images/loader-about.webp') }}" class="side-illustration"
                        alt="Mobile Login Illustration">
                </div>

                <div class="text-content mt-auto">
                    <h2 class="fw-bold text-white mb-2" style="text-shadow: 0 2px 4px rgba(0,0,0,0.15);">Welcome to
                        {{ $settings['app_name'] ?? 'SZORZO' }}</h2>
                    <p class="fs-15 text-white mb-0"
                        style="opacity: 0.9; text-shadow: 0 1px 2px rgba(0,0,0,0.15); line-height: 1.6;">
                        Experience seamless management with our secure, beautifully designed dashboard. Your workflow,
                        optimized.
                    </p>
                </div>

            </div>

            <!-- Right Column: Login Form -->
            <div class="col-lg-6 form-container">
                <div class="mb-5">
                    <h2 class="fw-bold text-dark mb-2">Sign In 👋</h2>
                    <p class="text-muted fs-15">Please enter your credentials to access your account.</p>
                </div>

                <div>
                    <form method="POST" action="{{ route('login.check') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="username" class="form-label fw-semibold text-dark">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                name="email" id="username" placeholder="name@example.com" value="{{ old('email') }}"
                                required autofocus>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold text-dark mb-0" for="password">Password</label>
                            </div>
                            <div class="input-group">
                                <input type="password" id="password"
                                    class="form-control border-end-0 @error('password') is-invalid @enderror"
                                    name="password" placeholder="Enter your password" required>
                                <span class="input-group-text toggle-password" id="password-addon"
                                    style="cursor: pointer;">
                                    <i toggle="#password" class="ri-eye-fill align-middle fs-18 toggle-password"></i>
                                </span>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-4 mt-2">
                            <input class="form-check-input" type="checkbox" name="remember" id="auth-remember-check"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-muted fs-14" for="auth-remember-check">
                                Remember me on this device
                            </label>
                        </div>

                        <div class="mt-4 pt-2">
                            <button class="btn btn-animated w-100" type="submit">Sign In to Dashboard</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <div class="footer-credits">
            <p class="mb-0">©
                <script>
                    document.write(new Date().getFullYear())
                </script> <strong>Szorzo</strong>. All rights reserved.
            </p>
        </div>
    </div>

    @include('backend.layouts.js_master')
    <script src="{{ asset('vendor/flasher/flasher.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('.toggle-password');
            const passwordInput = document.getElementById('password');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    this.classList.toggle('ri-eye-fill');
                    this.classList.toggle('ri-eye-off-fill');
                });
            }
        });
    </script>
</body>

</html>
