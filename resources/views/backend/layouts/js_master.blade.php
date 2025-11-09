<!-- Custom Script -->
<script src="{{ asset('admin/js/jquery.min.js')}}"></script>

<script src="{{asset('admin/js/custom-script.js')}}"></script>
<!-- JAVASCRIPT -->
<script src="{{asset('admin/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('admin/libs/simplebar/simplebar.min.js')}}"></script>
<script src="{{asset('admin/libs/node-waves/waves.min.js')}}"></script>
<script src="{{asset('admin/libs/feather-icons/feather.min.js')}}"></script>
<script src="{{asset('admin/js/pages/plugins/lord-icon-2.1.0.js')}}"></script>
<script src="{{asset('admin/js/plugins.js')}}"></script>

<!-- apexcharts -->
<script src="{{asset('admin/libs/apexcharts/apexcharts.min.js')}}"></script>

<!-- Vector map-->
<script src="{{asset('admin/libs/jsvectormap/js/jsvectormap.min.js')}}"></script>
<script src="{{asset('admin/libs/jsvectormap/maps/world-merc.js')}}"></script>

 <!-- dropzone js -->
 {{-- <script src="{{asset('admin/libs/dropzone/dropzone-min.js')}}"></script> --}}
 {{-- <script src="{{ asset('admin/js/pages/ecommerce-product-create.init.js')}}"></script> --}}

<!--Swiper slider js-->
<script src="{{asset('admin/libs/swiper/swiper-bundle.min.js')}}"></script>

<!-- Dashboard init -->
<script src="{{asset('admin/js/pages/dashboard-ecommerce.init.js')}}"></script>

<!-- ckeditor -->
{{-- <script src="{{ asset('admin/libs/%40ckeditor/ckeditor5-build-classic/build/ckeditor.js')}}"></script> --}}

<!-- App js -->
<script src="{{asset('admin/js/app.js')}}"></script>

{{-- Profile --}}
<script src="{{ asset('admin/js/pages/profile-setting.init.js')}}"></script>
<!--datatable js-->
<script src="{{ asset('admin/js/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ asset('admin/js/datatables/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{ asset('admin/js/datatables/dataTables.responsive.min.js')}}"></script>

<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Choices('#choices-multiple-remove-button', {
            removeItemButton: true,
        });
    });
</script>