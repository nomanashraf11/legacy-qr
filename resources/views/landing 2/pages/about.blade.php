@extends('landing.layout.master')
@section('content')
    <div class="py-5 my-5">
        <div class="container">
            <div class="row text-start justify-content-between hero-text">
                <div class="col-lg-6 mb">
                    <h1 class="display-1 fw-bold text-white">About Us</h1>
                    <div class="hairline"></div>
                    <div class="mt-4 pt-2">
                        <p class="fs-5 w-100 text-white-50">
                            Lorem ipsum dolor sit amet consectetur adipisicing elit.
                            Deleniti asperiores, tempore minima expedita, voluptatem vero
                            adipisci dolorem facere aliquam architecto itaque obcaecati enim
                            reiciendis consequuntur, temporibus a maxime soluta delectus!
                        </p>
                    </div>
                </div>

                <div class="col-lg-5">
                    <img src="{{ asset('assets/landing/images/old-people.jpg') }} " width="100%" height="400"
                        class="object-fit-cover rounded-5" />
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-md-0 mission px-md-0">
        <div class="row px-md-0">
            <div class="col-md-6 image-container mx-auto mx-md-0 order-1 order-md-0">
                <img src="{{ asset('assets/landing/images/mission.jpg') }} " class="object-fit-cover" />
            </div>
            <div class="col-md-6 order-0 order-md-1">
                <div class="pb-4 p-md-5">
                    <h2>Our Mission</h2>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit.
                        Repudiandae eveniet quam vero necessitatibus sit quisquam, magnam
                        perferendis voluptatibus quidem veritatis exercitationem error
                        deserunt vel voluptas consectetur porro quos qui illum!</p>
                </div>
            </div>
        </div>
    </div>

    <div class="px-3 px-sm-5 mx-sm-3 mx-md-0 px-md-0 mission">
        <div class="row px-md-0">
            <div class="col-md-6 image-container mx-auto mx-md-0 order-1">
                <img src="{{ asset('assets/landing/images/vision.jpg') }}" class="object-fit-cover" />
            </div>
            <div class="col-md-6 order-0">
                <div class="pb-4 p-md-5">
                    <h2>Our Vision</h2>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Modi,
                        non nam? Nisi rem a deserunt est aperiam tempore assumenda atque
                        voluptatum error suscipit fuga, cum, laboriosam eum natus
                        voluptatibus aut.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
