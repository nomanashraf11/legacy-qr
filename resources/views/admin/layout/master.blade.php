<!DOCTYPE html>
<html lang="en" data-menu-color="light">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Living Legacy')</title>
    @yield('meta')

    <!-- App favicon -->
    <link rel="icon" type="image/png" sizes="192x192"
        href="{{ asset('assets/landing/assets/favicon/android-chrome-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180"
        href="{{ asset('assets/landing/assets/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('assets/landing/assets/favicon/favicon-32x32.png') }} ">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('assets/landing/assets/favicon/favicon-16x16.png') }} ">
    <link rel="manifest" href="{{ asset('assets/landing/assets/favicon/site.webmanifest') }} ">
    <link rel="manifest" href="{{ asset('assets/landing/assets/favicon/mstile-150x150.png') }} ">
    <link rel="mask-icon" href="{{ asset('assets/landing/assets/favicon/safari-pinned-tab.svg') }} " color="#5bbad5">

    <!-- Daterangepicker css -->
    {{--
    <link rel="stylesheet" href="{{ asset('assets/vendor/daterangepicker/daterangepicker.css') }}"> --}}

    <!-- Datatables css -->

    <link href="{{ asset('assets/vendor/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/vendor/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/vendor/datatables.net-fixedcolumns-bs5/css/fixedColumns.bootstrap5.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/vendor/datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/vendor/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/vendor/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}" rel="stylesheet"
        type="text/css" />
    <!-- Theme Config Js -->
    <script src="{{ asset('assets/js/hyper-config.js') }}"></script>
    <!-- App css -->
    <link href="{{ asset('assets/css/app-saas.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    {{-- Custom css --}}
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <!-- Icons css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"
        integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!--Start of Tawk.to Script-->
    @if (App\Models\User::role('admin')->first()->admin->tawk == 1)
        <script type="text/javascript">
            var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
            (function () {
                var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/6622927ba0c6737bd12e2fba/default';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
    @endif

    {!! $admin->analytics !!}

</head>

<body>
    <div class="wrapper">
        @include('admin.layout.navbar')
        @include('admin.layout.sidebar')
        <div class="content-page">
            <!-- content -->
            @yield('content')
            <!-- Footer Start -->
            @include('admin.layout.footer')
            <!-- end Footer -->
        </div>
    </div>



    <!-- Vendor js -->
    {{--
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script> --}}

    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>

    <!-- Code Highlight js -->
    {{--
    <script src="{{ asset('assets/vendor/highlightjs/highlight.pack.min.js') }}"></script> --}}
    {{--
    <script src="{{ asset('assets/vendor/clipboard/clipboard.min.js') }}"></script> --}}
    {{--
    <script src="{{ asset('assets/js/hyper-syntax.js') }}"></script> --}}

    {{--
    <script src="{{ asset('js/jquery-3.6.0.js') }}"></script> --}}

    <!-- Datatables js -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>


    <script src="{{ asset('assets/vendor/datatables.net/js/jquery.dataTables.min.js ') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>




    <!-- Daterangepicker js -->
    <script src="{{ asset('assets/vendor/daterangepicker/moment.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/daterangepicker/daterangepicker.js') }}"></script>

    <!-- Vector Map js -->
    <script src="{{ asset('assets/vendor/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js') }}">
    </script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
        integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>


    @stack('scripts')
    @if (Session::has('message'))
        <script>
            toastr.options = {
                "progressBar": true,
                "closeButton": true,
                "timeOut": 3000 // Moved timeOut option to the common options
            };
            var status = {{ json_encode(Session::get('status')) }}; // Convert PHP boolean to JavaScript boolean

            if (status == 'true') {
                toastr.success("{{ Session::get('message') }}");
            } else {
                toastr.error("{{ Session::get('message') }}"); // Changed toastr.success to toastr.error for status === false
            }
        </script>
    @endif

</body>

</html>