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
        <!-- Page Title -->
        <title>SZORZO India's #1 GCC Launchpad | Global AI Partner</title>
        <!-- Favicon Icon -->
        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('frontend/images/favicon.png') }}">
        <link rel="icon" href="{{ asset('frontend/images/favicon.png') }}" type="image/png">
        <!-- Google Fonts Css-->
        <link rel="preconnect" href="https://fonts.googleapis.com/">
        <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&amp;display=swap" rel="stylesheet">
        <!-- Bootstrap Css -->
        <link href="{{ asset('frontend/css/bootstrap.min.css') }}" rel="stylesheet" media="screen">
        <!-- SlickNav Css -->
        <link href="{{ asset('frontend/css/slicknav.min.css') }}" rel="stylesheet">
        <!-- Swiper Css -->
        <link rel="stylesheet" href="{{ asset('frontend/css/swiper-bundle.min.css') }}">
        <!-- Font Awesome Icon Css-->
        <link href="{{ asset('frontend/css/all.min.css') }}" rel="stylesheet" media="screen">
        <!-- Animated Css -->
        <link href="{{ asset('frontend/css/animate.css') }}" rel="stylesheet">
        <!-- Magnific Popup Core Css File -->
        <link rel="stylesheet" href="{{ asset('frontend/css/magnific-popup.css') }}">
        <link rel="stylesheet" href="{{ asset('frontend/css/remixicon.css') }}">
        <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

        <!-- Mouse Cursor Css File -->
        <link rel="stylesheet" href="{{ asset('frontend/css/mousecursor.css') }}">
        <!-- Main Custom Css -->
        <link href="{{ asset('frontend/css/custom.css') }}" rel="stylesheet" media="screen">
        <meta name="google-site-verification" content="lqB0UndLURfLDXdqhyq41AEiBl2RZhNUbOP8ppt6QSE" />
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

        @include('frontend.includes.header')

        @yield('content')
        @yield('scripts')
        @include('frontend.includes.footer')
    </body>

</html>
