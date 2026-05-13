<!DOCTYPE html>
<html lang="zxx">

    <head>
        <!-- Meta -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
        <meta name="description" content="">
        <meta name="keywords" content="">
        <meta name="author" content="Awaiken">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="google-site-verification" content="lqB0UndLURfLDXdqhyq41AEiBl2RZhNUbOP8ppt6QSE" />
        
        <!-- Page Title -->
        <title>SZORZO India's #1 GCC Launchpad | Global AI Partner</title>
        
        <!-- Favicon Icon -->
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('frontend/images/favicon.png') }}">
        <link rel="icon" href="{{ asset('frontend/images/favicon.png') }}" type="image/png">
        
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
        
        <!-- CRITICAL CSS (Loads Immediately) -->
        <link href="{{ asset('frontend/css/bootstrap.min.css') }}" rel="stylesheet" media="screen">
        <link href="{{ asset('frontend/css/custom.css') }}" rel="stylesheet" media="screen">
        <link href="{{ asset('frontend/css/animate.css') }}" rel="stylesheet"> 

        <!-- NON-CRITICAL CSS (Deferred loading) -->
        <link rel="stylesheet" href="{{ asset('frontend/css/slicknav.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('frontend/css/swiper-bundle.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('frontend/css/all.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('frontend/css/magnific-popup.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('frontend/css/remixicon.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('frontend/css/mousecursor.css') }}" media="print" onload="this.media='all'">
        
        <!-- Fallback for users with disabled JavaScript -->
        <noscript>
            <link rel="stylesheet" href="{{ asset('frontend/css/slicknav.min.css') }}">
            <link rel="stylesheet" href="{{ asset('frontend/css/swiper-bundle.min.css') }}">
            <link rel="stylesheet" href="{{ asset('frontend/css/all.min.css') }}">
            <link rel="stylesheet" href="{{ asset('frontend/css/magnific-popup.css') }}">
            <link rel="stylesheet" href="{{ asset('frontend/css/remixicon.css') }}">
            <link rel="stylesheet" href="{{ asset('frontend/css/mousecursor.css') }}">
        </noscript>
    </head>

    <body>
        <!-- Preloader Start -->
        <div class="preloader">
            <div class="loading-container">
                <div class="loading"></div>
                <div id="loading-icon"><img src="{{ asset('frontend/images/loader.png') }}" alt="Loader"></div>
            </div>
        </div>
        <!-- Preloader End -->

        
        <script>
            window.addEventListener('load', function() {
                var preloader = document.querySelector('.preloader');
                if (preloader) {
                    preloader.style.transition = 'opacity 0.5s ease';
                    preloader.style.opacity = '0';
                    setTimeout(function() {
                        preloader.style.display = 'none';
                    }, 500);
                }
            });
        </script>

        @include('frontend.includes.header')

        @yield('content')
        
        <!-- CORE SCRIPTS (Moved to the bottom for performance AND stability) -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Ajax Setup restored to original format since jQuery is now loaded right above it -->
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        </script>

        <!-- Your custom scripts and animation libraries will load perfectly here -->
        @yield('scripts')
        
        @include('frontend.includes.footer')
    </body>
</html>