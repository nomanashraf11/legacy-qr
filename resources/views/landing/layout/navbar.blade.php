<nav class="navbar" id="navbar">
    <div class="container relative flex flex-wrap items-center justify-between">
        <a class="navbar-brand md:me-8" href="{{ rtrim(config('app.main_site_url'), '/') }}/">
            <img src="{{ asset('assets/landing/assets/images/logo-dark.png') }} "
                class="inline-block dark:hidden max-w-28" alt="">
            <img src="{{ asset('assets/landing/assets/images/logo-light.png') }} "
                class="hidden dark:inline-block max-w-40" alt="">
        </a>

        <div class="nav-icons flex items-center lg_992:order-2 ms-auto md:ms-8">
            <!-- Navbar Button -->
            <ul class="list-none menu-social mb-0">
                <li class="inline">
                    <a href="{{ config('app.client_url') }}"
                        class="h-8 px-4 text-[12px] tracking-wider inline-flex items-center justify-center font-medium rounded-md bg-teal-500 text-white uppercase">Login</a>
                </li>
            </ul>
            <!-- Navbar Collapse Manu Button -->
            <button data-collapse="menu-collapse" type="button"
                class="collapse-btn inline-flex items-center ms-2 text-dark dark:text-white lg_992:hidden"
                aria-controls="menu-collapse" aria-expanded="false">
                <span class="sr-only">Navigation Menu</span>
                <i class="mdi mdi-menu text-[24px]"></i>
            </button>
        </div>
        <style>
            @media (max-width: 991px) {
                .navbar .navigation .navbar-nav {
                    display: block;
                    height: 100%;
                    max-height: 47rem;
                    overflow-y: auto !important;
                }
            }
        </style>

        <!-- Navbar Manu -->
        <div class="navigation lg_992:order-1 lg_992:flex hidden ms-auto" id="menu-collapse">
            <ul data-collapse="menu-collapse" class="navbar-nav" id="navbar-navlist">
                <li class="nav-item">
                    <a class="nav-link active text-white sm:text-gray-600" href="/#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white sm:text-gray-600" href="/#review">Reviews</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white sm:text-gray-600" href="/#how-it-works">How it works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white sm:text-gray-600" href="/#demo">See a demo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white sm:text-gray-600" href="#contact">Contact us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white sm:text-gray-600" href="{{ route('help.center') }}">Help center</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white sm:text-gray-600" href="{{ route('reseller.view') }}">
                        Resellers
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
