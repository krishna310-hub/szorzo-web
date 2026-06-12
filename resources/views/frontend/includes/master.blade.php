<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Accessibility + Mobile -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <!-- SEO -->
     <meta
        name="description"
        content="SZORZO is India's leading GCC launchpad and global business transformation partner specializing in GCC setup, AI solutions, engineering services, market expansion, talent consolidation, and technology-driven business growth.">

    <meta
        name="keywords"
        content="SZORZO, GCC launchpad, Global Capability Center, AI partner, business transformation, engineering services, market expansion, talent mapping, India GCC, digital transformation, enterprise solutions">

    <meta name="author" content="Awaiken">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta
        name="google-site-verification"
        content="lqB0UndLURfLDXdqhyq41AEiBl2RZhNUbOP8ppt6QSE">

    <!-- Page Title -->
    <title>
        SZORZO India's #1 GCC Launchpad | Global AI Partner
    </title>

    <!-- Favicon -->
    <link
        rel="shortcut icon"
        type="image/x-icon"
        href="{{ asset('frontend/images/favicon.png') }}">

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('frontend/images/favicon.png') }}">

    <!-- Google Fonts -->
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <!-- Preload Font CSS -->
    <link
        rel="preload"
        as="style"
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap">

    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
        rel="stylesheet">

    <!-- Critical CSS -->
    <link
        href="{{ asset('frontend/css/bootstrap.min.css') }}"
        rel="stylesheet">

    <link
        href="{{ asset('frontend/css/custom.css') }}"
        rel="stylesheet">

    <!-- Non-Critical CSS -->
    <link
        rel="stylesheet"
        href="{{ asset('frontend/css/animate.css') }}"
        media="print"
        onload="this.media='all'">

    <link
        rel="stylesheet"
        href="{{ asset('frontend/css/slicknav.min.css') }}"
        media="print"
        onload="this.media='all'">

    <link
        rel="stylesheet"
        href="{{ asset('frontend/css/swiper-bundle.min.css') }}"
        media="print"
        onload="this.media='all'">

    <link
        rel="stylesheet"
        href="{{ asset('frontend/css/all.min.css') }}"
        media="print"
        onload="this.media='all'">

    <link
        rel="stylesheet"
        href="{{ asset('frontend/css/magnific-popup.css') }}"
        media="print"
        onload="this.media='all'">

    <link
        rel="stylesheet"
        href="{{ asset('frontend/css/remixicon.css') }}"
        media="print"
        onload="this.media='all'">

    <link
        rel="stylesheet"
        href="{{ asset('frontend/css/mousecursor.css') }}"
        media="print"
        onload="this.media='all'">

    <!-- NoScript Fallback -->
    <noscript>
        <link rel="stylesheet" href="{{ asset('frontend/css/animate.css') }}">
        <link rel="stylesheet" href="{{ asset('frontend/css/slicknav.min.css') }}">
        <link rel="stylesheet" href="{{ asset('frontend/css/swiper-bundle.min.css') }}">
        <link rel="stylesheet" href="{{ asset('frontend/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('frontend/css/magnific-popup.css') }}">
        <link rel="stylesheet" href="{{ asset('frontend/css/remixicon.css') }}">
        <link rel="stylesheet" href="{{ asset('frontend/css/mousecursor.css') }}">
    </noscript>
</head>

<body>

    <!-- Preloader -->
    <div class="preloader">
        <div class="loading-container">
            <div class="loading"></div>

            <div id="loading-icon">
                <img
                    src="{{ asset('frontend/images/loader.webp') }}"
                    alt="Loading"
                    width="80"
                    height="80">
            </div>
        </div>
    </div>

    <!-- Header -->
    @include('frontend.includes.header')

    <!-- Main Landmark -->
    <main id="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('frontend.includes.footer')

    <!-- Preloader Script -->
    <script defer>
        window.addEventListener('load', function () {
            const preloader = document.querySelector('.preloader');

            if (preloader) {
                preloader.style.transition = 'opacity .3s ease';
                preloader.style.opacity = '0';

                setTimeout(() => {
                    preloader.remove();
                }, 300);
            }
        });
    </script>

    <!-- Core Scripts -->
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        defer>
    </script>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        defer>
    </script>

    <!-- Ajax Setup -->
    <script defer>
        window.addEventListener('load', function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                }
            });
        });
    </script>

    <!-- Page Scripts -->
    @yield('scripts')

</body>

</html>
