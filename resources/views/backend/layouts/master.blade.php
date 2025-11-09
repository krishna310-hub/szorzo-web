<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="gradient-4" data-sidebar-size="lg" data-sidebar-image="img-3" data-preloader="disable" data-theme="default" data-theme-colors="blue">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" /> --}}
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{asset('frontend/images/glow-unlock-favicon.png')}}">

    @include('backend.layouts.css_master')

</head>

<body>
    @if(session('status') === 'success')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                flasher
                    .use('toastr') // ✅ Toastr style
                    .success(@json(session('message', 'Action completed successfully!')), 'Success');
            });
        </script>
    @endif

    <!-- Begin page -->
    <div id="layout-wrapper">
        <!-- Custom Modal -->
        <div class="modal fade" id="common_model" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="common_model_content"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Show Image -->
        <div class="modal fade" id="show_image" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="show_image_content"></div>
                    </div>
                </div>
            </div>
        </div>

        @include('backend.layouts.header')

        <!-- removeNotificationModal -->
        <div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="NotificationModalbtn-close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mt-2 text-center">
                            <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                            <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                                <h4>Are you sure ?</h4>
                                <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                            <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!</button>
                        </div>
                    </div>

                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        @include('backend.layouts.sidebar')

        @yield('content')
    </div>
    <!-- END layout-wrapper -->



    <!--start back-to-top-->
    <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
        <i class="ri-arrow-up-line"></i>
    </button>
    <!--end back-to-top-->
    <div class="show-loder d-none" id="loading-image1"><img src="{{ asset('frontend/plugins/lightgallery/img/loading.gif') }}" /></div>
    <!--preloader-->
    <div id="preloader">
        <div id="status">
            <div class="spinner-border text-primary avatar-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    @include('backend.layouts.theme_setting')

    @include('backend.layouts.js_master')
    @yield('script')
    @stack('script')
</body>

</html>