@extends('landing.layout.master')
@section('content')
    <!-- Hero -->
    <div class="text-secondary hero-text px-4 px-md-0 py-5">
        <div class="pt-5 pb-4 w-100">
            <h1 class="display-1 fw-bold text-white">Wholesale Pricing </h1>
            <div class="hairline mx-auto"></div>
            <div class="col-lg-7 mx-auto mt-4 pt-2">
                <p class="fs-5 text-white-50">
                    Uncover exclusive wholesale pricing options designed for funeral
                    homes seeking modern Funeral Technology solutions. Benefit from
                    special rates on bulk Digital Memorial plaques, crafted to provide
                    an affordable, yet meaningful tribute experience. Expect a
                    personalized response within two days.
                </p>
            </div>
        </div>

        <div class="border-cta">
            <div class="normal-text">Ordering more than 50?</div>
            <a href="{{ route('contact') }}" class="sans-link">Contact Us</a>
        </div>
    </div>
    </section>

    <!-- Pricing Cards -->

    <div class="pricing pb-5 pt-5">
        <div class="container mb-5">
            <h1 class="text-center mb-5">Choose a Plan</h1>

            <div class="row pt-0 pt-md-4">
                <div class="col-md-4 p-md-0">
                    <div class="card p-4">
                        <div class="card-body">
                            <h2 class="text-center m-0 text-white">5-19</h2>
                            <p class="py-3 m-0 fw-light text-white-50 text-center">
                                Memorial Webpages and Plaques
                            </p>
                            <div class="d-flex align-items-start justify-content-center">
                                <h2 class="text-white-50">
                                    <iconify-icon icon="bx:dollar"></iconify-icon>
                                </h2>
                                <div class="d-flex align-items-baseline gap-1">
                                    <h1 class="text-white">19.99</h1>
                                    <span class="text-white-50 fw-light">each</span>
                                </div>
                            </div>
                            <div class="mt-2 mb-4 rounded overflow-hidden">
                                <img src="{{ asset('assets/landing/images/price.jpg') }}" class="w-100 h-auto" />
                            </div>
                            <ul class="p-0 m-0 list-unstyled">
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>5-19 memorial pages</span>
                                </li>
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>5-19 Our Tributes Plaques</span>
                                </li>
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>5-19 Garden Stakes</span>
                                </li>
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>Save 12%</span>
                                </li>
                            </ul>
                            <button class="btn btn-light blue-btn w-100 mt-5 rounded-0">
                                BUY NOW
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Plan 2 -->
                <div class="col-md-4 p-md-0 card-2">
                    <div class="card-header text-center p-1 rounded-0">
                        Most Popular
                    </div>
                    <div class="card p-4">
                        <div class="card-body">
                            <h2 class="text-center m-0 text-white">20-50</h2>
                            <p class="py-3 m-0 fw-light text-white-50 text-center">
                                Memorial Webpages and Plaques
                            </p>
                            <div class="d-flex align-items-start justify-content-center">
                                <h2 class="text-white-50">
                                    <iconify-icon icon="bx:dollar"></iconify-icon>
                                </h2>
                                <div class="d-flex align-items-baseline gap-1">
                                    <h1 class="text-white">19.99</h1>
                                    <span class="text-white-50 fw-light">each</span>
                                </div>
                            </div>
                            <div class="mt-2 mb-4 rounded overflow-hidden">
                                <img src="{{ asset('assets/landing/images/price.jpg') }}" class="w-100 h-auto" />
                            </div>
                            <ul class="p-0 m-0 list-unstyled">
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>20-50 memorial pages</span>
                                </li>
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>20-50 Our Tributes Plaques</span>
                                </li>
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>20-50 Garden Stakes</span>
                                </li>
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>Save 22%</span>
                                </li>
                            </ul>
                            <button class="btn btn-primary buy-btn w-100 mt-5 rounded-0">
                                BUY NOW
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Plan 3 -->
                <div class="col-md-4 p-md-0">
                    <div class="card p-4">
                        <div class="card-body">
                            <h2 class="text-center m-0 text-white">50+</h2>
                            <p class="py-3 m-0 fw-light text-white-50 text-center">
                                Memorial Webpages and Plaques
                            </p>

                            <h1 class="text-center text-white">Custom</h1>
                            <div class="mt-3 mb-4 rounded overflow-hidden">
                                <img src="{{ asset('assets/landing/images/price.jpg') }}" class="w-100 h-auto" />
                            </div>
                            <ul class="p-0 m-0 list-unstyled">
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>50+ memorial pages</span>
                                </li>
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>50+ Our Tributes Plaques</span>
                                </li>
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>50+ Garden Stakes</span>
                                </li>
                                <li>
                                    <iconify-icon class="done-icon" icon="ic:round-done"></iconify-icon>
                                    <span>Save 32%</span>
                                </li>
                            </ul>
                            <button class="btn btn-light blue-btn w-100 mt-5 rounded-0">
                                CONTACT US
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
