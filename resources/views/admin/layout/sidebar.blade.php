<div class="leftside-menu overflow-hidden">

    <!-- Brand Logo Light -->
    <a href="{{ rtrim(config('app.main_site_url', 'https://livinglegacyqr.com'), '/') }}/" target="_blank" class="logo logo-dark pt-2 text-center">

        <img class="d-sm-none d-block" style="max-width: 100px; width: 100%;" src="{{ asset('images/logo/cropped.png') }}">
        <img class="d-none d-sm-block mx-auto" style="max-width: 80px; width: 100%;"
            src="{{ asset('images/logo/cropped.png') }}">
    </a>


    {{-- Living Legacy --}}
    <div class="pb-3"></div>
    <div class="h-100 overflow-y-auto pb-5">
        <!-- Sidebar Hover Menu Toggle Button -->
        <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
            <i class="ri-checkbox-blank-circle-line align-middle"></i>
        </div>

        <!-- Full Sidebar Menu Close Button -->
        <div class="button-close-fullsidebar">
            <i class="ri-close-fill align-middle"></i>
        </div>

        <!-- Sidebar -left -->
        <div class="h-100" id="leftside-menu-container" data-simplebar>
            <!-- Leftbar User -->
            <div class="leftbar-user">
                <a href="pages-profile.html">
                    <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="user-image" height="42"
                        class="rounded-circle shadow-sm">
                    <span class="leftbar-user-name mt-2">Dominic Keller</span>
                </a>
            </div>

            <!--- Sidemenu -->
            @role('admin')
                <ul class="side-nav">
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Dashboard">
                        <a href="{{ route('admin.dashboard') }}" class="side-nav-link">
                            <i class="uil uil-dashboard"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Create QR Codes">
                        <a href="{{ route('admin.batches') }}" class="side-nav-link">
                            <i class="uil uil-focus-add"></i>
                            <span>Create QR Codes</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Available QR Codes">
                        <a href="{{ route('admin.batch.available') }}" class="side-nav-link">
                            <i class="uil uil-check-square"></i>
                            <span>Available QR Codes</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Linked QR Codes">
                        <a href="{{ route('admin.links.linked') }}" class="side-nav-link">
                            <i class="uil uil-link"></i>
                            <span>Linked QR Codes</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Transfer QR Codes">
                        <a href="{{ route('admin.transfer.page') }}" class="side-nav-link">
                            <i class="uil uil-exchange-alt"></i>
                            <span>Transfer QR Codes</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Products">
                        <a href="{{ route('admin.products') }}" class="side-nav-link">
                            <i class="uil uil-bag-alt"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Orders">
                        <a href="{{ route('admin.orders') }}" class="side-nav-link">
                            <i class="uil uil-shopping-cart-alt"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="All Re-Sellers">
                        <a href="{{ route('users.list') }}" class="side-nav-link">
                            <i class="uil-users-alt"></i>
                            <span>All Re-Sellers</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Users">
                        <a href="{{ route('users.list.local') }}" class="side-nav-link">
                            <i class="mdi mdi-account-group-outline"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Reseller Applications">
                        <a href="{{ route('admin.reseller.applications') }}" class="side-nav-link">
                            <i class="ri-user-received-line"></i>
                            <span>Reseller Applications</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="Reviews">
                        <a href="{{ route('admin.reviews') }}" class="side-nav-link">
                            <i class=" ri-user-heart-line"></i>
                            <span>Reviews</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Settings">
                        <a href="{{ route('admin.settings') }}" class="side-nav-link">
                            <i class="uil-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>

                </ul>
            @endrole
            @role('re-sellers')
                <ul class="side-nav">
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Dashboard">
                        <a href="{{ route('sellar.dashboard') }}" class="side-nav-link">
                            <i class="uil uil-dashboard"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Products">
                        <a href="{{ route('reseller.products') }}" class="side-nav-link">
                            <i class="uil uil-shopping-cart-alt"></i>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="My Orders">
                        <a href="{{ route('myOrders') }}" class="side-nav-link">
                            <i class="uil uil-bag-alt"></i>
                            <span>My Orders</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Invoices">
                        <a href="{{ route('reseller.invoices') }}" class="side-nav-link">
                            <i class="uil uil-receipt"></i>
                            <span>Invoices</span>
                        </a>
                    </li>
                    <li class="side-nav-item" data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-title="Settings">
                        <a href="{{ route('settings') }}" class="side-nav-link">
                            <i class="uil-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            @endrole
            <!--- End Sidemenu -->
            <div class="clearfix"></div>
        </div>
    </div>

</div>
