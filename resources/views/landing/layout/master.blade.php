<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth" dir="ltr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Living Legacy</title>
    <meta name="description" content="Responsive Tailwind CSS Template">
    <meta name="keywords" content="Onepage, creative, modern, Tailwind CSS, multipurpose, clean">
    <meta name="author" content="Shreethemes">
    <meta name="website" content="https://shreethemes.in">
    <meta name="email" content="support@shreethemes.in">
    <meta name="version" content="1.0.0">

    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet" />
    <script defer src="https://unpkg.com/alpinejs@3.2.3/dist/cdn.min.js"></script>

    <link href="https://db.onlinewebfonts.com/c/8b9a6615e5548f889ddd9de161c44b24?family=Brush" rel="stylesheet"
        type="text/css" />

    <!-- favicon -->

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


    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <!-- Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Main Css -->
    <link href="{{ asset('assets/landing/assets/libs/tobii/css/tobii.min.css') }} " rel="stylesheet">
    <link href="{{ asset('assets/landing/assets/libs/tiny-slider/tiny-slider.css') }} " rel="stylesheet">
    <link href="{{ asset('assets/landing/assets/libs/@mdi/font/css/materialdesignicons.min.css') }} " rel="stylesheet"
        type="text/css">
    <script src=https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('assets/landing/assets/css/tailwind.min.css') }} ">
    <link rel="stylesheet" href="{{ asset('assets/landing/assets/css/custom.css') }} ">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"
        integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @if (App\Models\User::role('admin')->first()->admin->tawk == 1)
        <!-- <script type="text/javascript">
            var Tawk_API = Tawk_API || {},
                Tawk_LoadStart = new Date();
            (function() {
                var s1 = document.createElement("script"),
                    s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/65d2fee29131ed19d96e7af2/1hn0379n7';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script> -->
        <script type="text/javascript">
            var Tawk_API = Tawk_API || {},
                Tawk_LoadStart = new Date();
            (function() {
                var s1 = document.createElement("script"),
                    s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/6622927ba0c6737bd12e2fba/default';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();
        </script>
    @endif


    {!! $admin->analytics !!}

    <script>
        // Set Tailwind CSS configuration
        tailwindConfig = {
            theme: {
                fontFamily: {
                    'brush': ["'Brush'", 'sans-serif'],
                },
                extend: {
                    colors: {
                        clifford: '#da373d',
                    }
                }
            }
        }
    </script>

    <style>
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
        }
    </style>

    <style type="text/tailwindcss">
        .home-title {
            font-family: 'Brush', sans-serif !important;
        }

        @media only screen and (min-width: 600px) {
            .footer-text {
                justify-content: space-between !important;
            }
        }

        @media only screen and (min-width: 768px) {
            .home-title {
                font-size: 40px !important;
            }
        }

        .tns-controls button {
            width: 30px;
            height: 30px;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1px solid lightgray;
        }

        .tns-controls button:nth-child(1) {
            position: absolute;
            left: 0;
            top: calc(50% - 30px);
            z-index: 9999;
            margin-left: -30px;
        }

        .tns-controls button:nth-child(2) {
            position: absolute;
            right: 0;
            top: calc(50% - 30px);
            z-index: 9999;
            margin-right: -30px;
        }
    </style>






</head>

<body class="font-libre_franklin text-base overflow-x-hidden text-black dark:text-white bg-white dark:bg-slate-900 @if(request()->routeIs('reseller.view')) !bg-black @endif">
    @if(!request()->routeIs('reseller.view'))
        @include('landing.layout.navbar')
    @endif
    @yield('content')
    @include('landing.layout.footer')


    <!-- JAVASCRIPTS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/landing/assets/libs/feather-icons/feather.min.js') }} "></script>
    <script src="{{ asset('assets/landing/assets/libs/gumshoejs/gumshoe.polyfills.min.js') }} "></script>
    <script src="{{ asset('assets/landing/assets/libs/tobii/js/tobii.min.js') }} "></script>
    <script src="{{ asset('assets/landing/assets/libs/tiny-slider/min/tiny-slider.js') }} "></script>
    <script src="{{ asset('assets/landing/assets/js/plugins.init.js') }} "></script>
    <script src="{{ asset('assets/landing/assets/js/app.js') }} "></script>
    <!-- JAVASCRIPTS -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
        integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('accordion', {
                tab: 0,
            });

            Alpine.data('accordion', (idx) => ({
                init() {
                    this.idx = idx;
                },
                idx: -1,
                handleClick() {
                    this.$store.accordion.tab =
                        this.$store.accordion.tab === this.idx ? 0 : this.idx;
                },
                handleRotate() {
                    return this.$store.accordion.tab === this.idx ? 'rotate-180' : '';
                },
                handleToggle() {
                    return this.$store.accordion.tab === this.idx ?
                        `max-height: ${this.$refs.tab.scrollHeight}px` :
                        '';
                },
            }));
        });
    </script>

</body>

</html>
