@extends('landing.layout.master')
@section('content')
    <!-- Navbar and hero -->
    <section>
        <!-- Hero -->
        <div class="text-secondary position-relative hero-text px-4 px-md-0 py-5">
            <div class="pt-5 pb-4 w-100">
                <h1 class="display-1 fw-bold text-white">How it Works</h1>
                <div class="hairline mx-auto"></div>
                <div class="col-lg-7 mx-auto mt-4 pt-2">
                    <p class="fs-5">
                        Begin Your Tribute Journey: Simply scan, upload, share, and place.
                        Open your QR plaque, upload cherished memories, invite
                        contributions, and securely affix it to your chosen spot. It's an
                        easy, meaningful way to create a lasting memorial.
                    </p>
                </div>
            </div>
        </div>
        <img src="{{ asset('assets/landing/svg/divider.svg') }} " width="100%" class="curve-divider" />
    </section>

    <!-- How it works -->

    <div class="how-works py-5">
        <div class="container pt-5">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('assets/landing/svg/qr-circle.svg') }} " />
                                <h3 class="text-white">Scan</h3>
                            </div>
                            <p class="ps-3 text-white-50 ms-5">
                                Scan your QR after unboxing it.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('assets/landing/svg/circle-pics.svg') }} " />
                                <h3 class="text-white"> Create Page </h3>
                            </div>
                            <p class="ps-3 text-white-50 ms-5">
                                Add photos, videos and more!
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('assets/landing/svg/share.svg') }} " />
                                <h3 class="text-white">Share & Collect </h3>
                            </div>
                            <p class="ps-3 text-white-50 ms-5">
                                Share with family or friends and collect tributes of your
                                loved one.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('assets/landing/svg/circle-place.svg') }} " />
                                <h3 class="text-white">Place & Enjoy </h3>
                            </div>
                            <p class="ps-3 text-white-50 ms-5">
                                Place your QR anywhere anywhere you'd like!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
