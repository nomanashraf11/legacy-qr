@extends('landing.layout.master')
@section('content')
    <section class="relative flex items-center py-36" id="home">
        <div class="container relative">
            <div class="grid lg:grid-cols-12 md:grid-cols-2 grid-cols-1 items-center mt-6 gap-6 relative">
                <div class="lg:col-span-7 md:me-6">
                    <h1 class="font-semibold !font-brush home-title lg:leading-normal leading-normal tracking-wide text-2xl mb-5">
                        Building Legacies in Living Moments
                    </h1>
                    <p class="text-slate-400 text-lg max-w-xl">
                        Create and preserve your loved one’s legacy hassle free. Share photos,
                        videos, memories, music, family information and more with each scan of our Living Legacy QR
                        medallion or each share of our Living Legacy memorial page
                    </p>
                    <a target="_blank" href="{{ $admin->reviews_link ?? $admin->amazon }}"
                       class="h-10 mt-4 border border-white/20 px-6 tracking-wide inline-flex items-center justify-center gap-2 font-medium rounded-md hover:bg-white/10 bg-white/20 transition-all duration-150 active:bg-white/5 select-none text-white">
                        Buy Now On
                        <img src="{{ asset('assets/landing/assets/images/amazon.svg') }} "
                             class="object-cover mt-2 h-5">
                    </a>
                </div>

                <div class="lg:col-span-5 mt-20">
                    <div class="relative">
                        <img src="{{ asset('assets/landing/assets/images/person.jpg') }} "
                             class="mx-auto rounded-[150px] rounded-br-2xl shadow dark:shadow-gray-700 w-[90%]">
                    </div>
                </div>
            </div><!--end grid-->
        </div><!--end container-->
    </section><!--end section-->
    <!-- End Hero -->

    <!-- Start Features -->
    <section class="relative md:py-24 py-10 bg-slate-50 dark:bg-slate-800">
        <div class="container w-full">
            <div class="flex flex-col md:flex-row !gap-5">
                <div class="flex items-center justify-center">
                    <img class="max-h-[400px]" src=" {{ 'assets/landing/assets/images/check.png' }} ">
                </div>
                <div class="grid grid-cols-2 w-full md:grid-cols-3 justify-between gap-10 flex-wrap mt-10 pt-5 md:mt-0 md:pt-0">
                    <div class="flex items-center justify-center flex-col gap-3">
                        <img width="30" height="30" src=" {{ 'assets/landing/assets/images/book.png' }} ">
                        <caption>Bio</caption>
                    </div>
                    <div class="flex items-center justify-center flex-col gap-3">
                        <img width="30" height="30" src=" {{ 'assets/landing/assets/images/family-tree.png' }} ">
                        <caption>Family Tree</caption>
                    </div>
                    <div class="flex items-center justify-center flex-col gap-3">
                        <img width="30" height="30" src=" {{ 'assets/landing/assets/images/map.png' }} ">
                        <caption>Cemetry Location</caption>
                    </div>
                    <div class="flex items-center justify-center flex-col gap-3">
                        <img width="30" height="30" src=" {{ 'assets/landing/assets/images/Music.png' }} ">
                        <caption>Spotify</caption>
                    </div>
                    <div class="flex items-center justify-center flex-col gap-3">
                        <img width="30" height="30" src=" {{ 'assets/landing/assets/images/photo.png' }} ">
                        <caption>Photos</caption>
                    </div>
                    <div class="flex items-center justify-center flex-col gap-3">
                        <img width="30" height="30" src=" {{ 'assets/landing/assets/images/Video.png' }} ">
                        <caption>Videos</caption>
                    </div>
                    <div class="flex items-center justify-center flex-col gap-3">
                        <img width="30" height="30" src=" {{ 'assets/landing/assets/images/share.png' }} ">
                        <caption>Share</caption>
                    </div>
                    <div class="flex items-center justify-center flex-col gap-3">
                        <img width="30" height="30" src=" {{ 'assets/landing/assets/images/timeline.png' }} ">
                        <caption>Life Events</caption>
                    </div>
                    <div class="flex items-center justify-center flex-col gap-3">
                        <img width="30" height="30" src=" {{ 'assets/landing/assets/images/youtube.png' }} ">
                        <caption>YouTube</caption>
                    </div>
                </div><!--end grid-->
            </div>
        </div><!--end container-->
    </section>
    <!--end section-->
    <!-- End Features -->


    <!-- Start Process -->
    <section id="how-it-works" class="realtive md:py-24 py-16 bg-slate-50 dark:bg-slate-800">
        <div class="container relative">
            <div class="grid grid-cols-1 pb-6 text-center">
                <h3 class="font-semibold text-2xl leading-normal mb-4">How it Works</h3>

            </div><!--end grid-->

            <div class="grid md:grid-cols-12 grid-cols-1 mt-6 gap-6">
                <div class="lg:col-span-4 md:col-span-5 max-h-max">
                    <div class="sticky top-20">
                        <ul class="flex-column p-6 bg-white dark:bg-slate-900 shadow dark:shadow-gray-700 rounded-md"
                            id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                            <li role="presentation">
                                <button
                                    class="px-4 py-2 text-start text-base font-medium rounded-md w-full hover:text-teal-500 duration-500"
                                    id="profile-tab" data-tabs-target="#profile" type="button" role="tab"
                                    aria-controls="profile" aria-selected="true">
                                    <span class="block">Step 1</span>
                                    <span class="text-lg mt-2 block">Scan and Setup</span>
                                    <span class="block mt-2">
                                        Scan and activate the Medallion with your smart phone; Create and
                                        customize your loved one’s legacy page via your choice of smart phone or
                                        PC
                                    </span>
                                </button>
                            </li>
                            <li role="presentation">
                                <button
                                    class="px-4 py-2 text-start text-base font-medium rounded-md w-full mt-6 duration-500"
                                    id="dashboard-tab" data-tabs-target="#dashboard" type="button" role="tab"
                                    aria-controls="dashboard" aria-selected="false">
                                    <span class="block">Step 2</span>
                                    <span class="text-lg mt-2 block">Install Medallion</span>
                                    <span class="block mt-2">
                                        Place Medallion on headstone, urn, memorial bench and more
                                    </span>
                                </button>
                            </li>
                            <li role="presentation">
                                <button
                                    class="px-4 py-2 text-start text-base font-medium rounded-md w-full mt-6 duration-500"
                                    id="settings-tab" data-tabs-target="#settings" type="button" role="tab"
                                    aria-controls="settings" aria-selected="false">
                                    <span class="block">Step 3</span>
                                    <span class="text-lg mt-2 block">Share your loved one’s Legacy </span>
                                    <span class="block mt-2">
                                        Share your loved one’s Living Legacy both in person and through
                                        integrated social media platforms
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-8 md:col-span-7">
                    <div id="myTabContent"
                         class="p-6 bg-white h-full overflow-hidden dark:bg-slate-900 shadow dark:shadow-gray-700 rounded-md">
                        <div id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <img src="{{ asset('images/website_pictures/How_it_works_Step_1.jpg') }} "
                                 class="shadow w-full max-h-[600px] dark:shadow-gray-700 rounded-md h-full object-cover">
                        </div>
                        <div class="hidden h-full" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                            <img src="{{ asset('images/website_pictures/HowitworksStep2.jpg') }} "
                                 class="shadow w-full max-h-[600px] dark:shadow-gray-700 rounded-md h-full object-cover">
                        </div>
                        <div class="hidden h-full" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                            <img src="{{ asset('images/website_pictures/HowitWorksStep3.png') }} "
                                 class="shadow w-full max-h-[600px] dark:shadow-gray-700 rounded-md h-full object-cover">
                        </div>
                    </div>
                </div>
            </div><!--end grid-->
        </div><!--end container-->
    </section>
    <!--end section-->
    <!-- End Process -->

    <section id='demo' class="relative md:pt-24 pt-16 md:pt-24 pt-24 bg-gradient-to-b dark:bg-slate-800">
        <div class="container relative">
            <div class="grid grid-cols-1 pb-6 text-center">
                <h3 class="font-semibold text-2xl leading-normal">Here's a demo</h3>
                <p class="text-slate-400 max-w-xl mx-auto">Scan the QR Code below to see it in action.</p>
            </div><!--end grid-->
            <div class="grid grid-cols-1 justify-center">
                <div class="relative z-1">
                    <div class="fold mb-0 pb-0">
                        <div class="content-container px-4">
                            <div class="w-row">
                                <div class="column-3 w-col w-col-6">
                                    <div class="qr-feature block">
                                        <div class="text-stack-with-border mb-5">
                                            <div class="flex-heading d-flex align-items-center">
                                                <img class="mb-3"
                                                     src="{{ asset('assets/landing/assets/images/white-qr.svg') }} ">
                                                <div class="h5">Scan</div>
                                            </div>
                                            <p class="paragraph-small grid-col margin-bottom-24px">
                                                Scan the QR plaque with your phone to view the memorial page.
                                            </p>
                                            <div class="arrow-link">
                                                <a target="_blank" href="https://qr.livinglegacyqr.com/OcGuqO0a1l1l/legacy" class="white-link underline underline-offset-2">
                                                    View a Real Tribute
                                                </a>
                                            </div>
                                        </div>
                                        <img src="{{ asset('assets/landing/assets/images/qrcode.png') }}"
                                             class="qr-with-arrows h-auto">
                                    </div>
                                    <!-- width="458" height="304" -->
                                </div>
                                <div class="phone-column phone-container w-col w-col-6 overflow-hidden relative pt-0">
                                    <div class="relative phone-sm">
                                        <img src="{{ asset('assets/landing/assets/images/mobile.png') }}"
                                             class="mx-auto dark:shadow-gray-700 w-[90%]">
                                        <div
                                            class="overflow-hidden after:content-[''] after:absolute after:h-10 after:w-10 after:bg-teal-500/20 after:top-0 after:start-0 after:-z-1 after:rounded-lg after:animate-[spin_10s_linear_infinite]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content md:mt-8">
                        <div class="reviews pb-0 pt-9">
                            <div class="content-container">
                                <a href=" {{ $admin->reviews_link ?? $admin->amazon }} ">
                                    <div class="cta-box border border-2">
                                        <div class="inner-cta-box long">
                                            <div
                                                class="amazon-heading flex flex-wrap justify-center gap-3 align-middle py-3">
                                                <h3 class="h3-white my-0 pb-1">Buy it now on </h3>
                                                <img src="{{ asset('assets/landing/assets/images/amazon.svg') }} "
                                                     class="amazong-small xs-sm alt">
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--end row -->
        </div><!--end container-->

        <div class="absolute bottom-0 start-0 end-0 sm:h-2/3 h-4/5 bg-gradient-to-b dark:bg-slate-800"></div>
    </section>
    <!--end section-->
    <!-- End -->

    @if ($reviews->count() > 0)
        <!-- Start Review -->
        <section class="relative md:py-24 py-16 bg-slate-50 dark:bg-slate-800" id="review">
            <div class="container relative">
                <div class="grid grid-cols-1 pb-6 text-center">
                    <h3 class="font-semibold text-2xl leading-normal mb-4">Reviews</h3>

                    <p class="text-slate-400 max-w-xl mx-auto">
                        Check out what our customers have to say
                    </p>
                </div><!--end grid-->

                <div class="mt-6 relative ">
                    <div class="tiny-three-item">
                        @foreach ($reviews as $review)
                            <div style="user-select:none;" class="tiny-slide text-center">
                                <div class="cursor-e-resize">
                                    <div
                                        class="content relative rounded shadow dark:shadow-gray-700 m-2 p-6 bg-white dark:bg-slate-900">
                                        <ul class="list-none text-amber-400 flex justify-start gap-1">
                                            <li class="inline">
                                                <img src="{{ asset('assets/landing/assets/images/rating-star.svg') }} ">
                                            </li>
                                            <li class="inline">
                                                <img src="{{ asset('assets/landing/assets/images/rating-star.svg') }} ">
                                            </li>
                                            <li class="inline">
                                                <img src="{{ asset('assets/landing/assets/images/rating-star.svg') }} ">
                                            </li>
                                            <li class="inline">
                                                <img src="{{ asset('assets/landing/assets/images/rating-star.svg') }} ">
                                            </li>
                                            <li class="inline">
                                                <img src="{{ asset('assets/landing/assets/images/rating-star.svg') }} ">
                                            </li>
                                        </ul>
                                        <p class="text-slate-400 text-start mt-4 mb-5"> {{ $review->description }} </p>
                                        <div class="flex items-center gap-3">
                                            <img width="50" height="50"
                                                 src="{{ asset('images/reviews/' . $review->image) }}"
                                                 class="object-cover rounded-full min-h-[50px] min-w-[50px] max-h-[50px] max-w-[50px]"/>
                                            <div class="text-start">
                                                <p class="font-semibold text-sm">{{ $review->name }}</p>
                                                <span class="text-slate-400 text-sm">{{ $review->title }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End Review-->
    @endif





    <!-- Start About -->
    <section class="relative md:py-24 py-16">
        <div class="container relative">
            <div class="grid md:grid-cols-12 grid-cols-1 items-center gap-6">
                <div class="md:col-span-6">
                    <div class="lg:me-8">
                        <div class="relative shadow w-full overflow-hidden rounded collage flex flex-wrap">
                            <img src="{{ asset('assets/landing/assets/images/About-us.jpg') }} "
                                 class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>

                <div class="md:col-span-6">
                    <div class="lg:ms-8">
                        <h3 class="font-semibold text-4xl leading-normal mb-4">
                            About Us
                        </h3>

                        <p class="text-slate-400 max-w-xl mb-3">
                            At Living Legacy, we are an ordinary family who have weathered extraordinary times. Over
                            four years, like countless others, we endured the loss of three cherished family members.
                            Amidst
                            frequent visits to cemeteries and gravesides, a poignant realization struck us: there was no
                            one left but us to preserve and share their stories. Who would carry forth their narratives
                            once we
                            were gone? What memories would future generations hold of them?
                        </p>
                        <p class="text-slate-400 max-w-xl mb-6">
                            From this deeply personal journey, Living Legacy emerged. Our mission is simple yet
                            profound: to offer an accessible and affordable technologically advanced solution for
                            ordinary people to
                            safeguard the legacies of their loved ones, ensuring that their stories endure for
                            generations to
                            come.
                        </p>

                    </div>
                </div>
            </div>
        </div>
        <!--end container-->
    </section>
    <!--end section-->
    <!-- End About -->

    <section class="relative lg:py-24 py-16 bg-slate-50 dark:bg-slate-800" id="contact">
        <div class="container relative">
            <div class="grid grid-cols-1 pb-6 text-center">
                <h3 class="font-semibold text-2xl leading-normal mb-4">Get in touch </h3>
                <p class="text-slate-400 max-w-xl mx-auto">
                    Need to get in touch with use? Fill out the form with your inquiry information.
                </p>
            </div><!--end grid-->
            <div class="grid md:grid-cols-1 grid-cols-1 items-center gap-6 mx-auto">

                <div class="lg:col-span-8 md:col-span-6 mx-auto">
                    <div class="lg:ms-5">
                        <div class="bg-white dark:bg-slate-900 rounded-md shadow dark:shadow-gray-700 p-6">
                            <form id="contactForm" method="POST">
                                @csrf
                                <p class="mb-0" id="error-msg"></p>
                                <div id="simple-msg"></div>
                                <div class="grid lg:grid-cols-12 grid-cols-1 gap-3">
                                    <div class="lg:col-span-6">
                                        <label for="name" class="font-semibold">Your Name:</label>
                                        <input name="name" id="name" type="text"
                                               class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                               placeholder="Enter your name">
                                    </div>

                                    <div class="lg:col-span-6">
                                        <label for="email" class="font-semibold">Your Email:</label>
                                        <input name="email" id="email" type="email"
                                               class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                               placeholder="Enter your email address">
                                    </div>

                                    <div class="lg:col-span-12">
                                        <label for="subject" class="font-semibold">Your Question:</label>
                                        <input name="subject" id="subject"
                                               class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                               placeholder="Enter your question">
                                    </div>

                                    <div class="lg:col-span-12">
                                        <label for="comments" class="font-semibold">Your Comment:</label>
                                        <textarea name="message" id="message"
                                                  class="mt-2 w-full py-2 px-3 h-28 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                                  placeholder="Add your comments here..."></textarea>
                                    </div>
                                </div>
                                <button type="submit"
                                        class="h-10 px-6 tracking-wide inline-flex items-center justify-center font-medium rounded-md bg-teal-500 text-white mt-2">
                                    Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div><!--end container-->
    </section>
    <!--end section-->
@endsection
@push('scripts')
    <script>
        $("#contactForm").on("submit", function (event) {
            event.preventDefault();
            var $submitButton = $(this).find('button[type="submit"]');
            $submitButton.prop('disabled', true); // Disable the submit button
            var formData = new FormData(this);
            $.ajax({
                url: "/send-contact",
                data: formData,
                type: "POST",
                processData: false, // Important: Don't process the data
                contentType: false,
                success: function (response) {
                    toastr.options = {
                        progressBar: true,
                        closeButton: true,
                        timeOut: 2000,
                    };
                    if (response.status === true) {
                        toastr.success(response.message, "Success");
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error(response.message, "Error");
                    }
                },
                error: function (errors) {
                    const errorMessages = Object.values(errors?.responseJSON?.errors).flat();
                    toastr.options = {
                        progressBar: true,
                        closeButton: true,
                    };
                    for (let i = 0; i < errorMessages.length; i++) {
                        toastr.error(errorMessages[i], "Error");
                    }
                },
                complete: function () {
                    $submitButton.prop('disabled', false); // Re-enable the submit button
                }
            });
        });
    </script>
@endpush
