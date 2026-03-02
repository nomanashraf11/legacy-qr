@extends('landing.layout.master')
@section('content')
    <section class="p-5 md:pt-24 pt-24 bg-black min-h-screen">
        <div class="container">
            <div class="flex justify-center">
                <div class="w-full max-w-xl sm:w-10/12 md:w-1/2 my-1">
                    <h2 class="text-2xl font-medium text-center text-white mb-10">
                        FAQ - Order, Shipping, Etc.
                    </h2>
                    <ul class="flex flex-col relative">
                        <li class="bg-white/20 text-white my-2 shadow" x-data="accordion(1)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer">
                                <span> What happens to my Living Legacy page if my QR Medallion gets
                                    damaged?</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-white min-h-6 min-w-6 max-h-6 min-w-6 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div x-ref="tab" :style="handleToggle()"
                                class="border-l-2 border-white overflow-hidden max-h-0 duration-500 transition-all">
                                <p class="p-3 text-gray-300">
                                    Your Living Legacy page is safely stored on our servers. You can order a
                                    replacement QR Medallion and we will transfer your loved one’s Living
                                    Legacy page over, hassle free.
                                </p>
                            </div>
                        </li>
                        <li class="bg-white/20 text-white my-2 shadow-lg" x-data="accordion(2)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer">
                                <span>How do I add a music playlist to a Living Legacy page?</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-white min-h-6 min-w-6 max-h-6 min-w-6 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div class="border-l-2 border-white overflow-hidden max-h-0 duration-500 transition-all"
                                x-ref="tab" :style="handleToggle()">
                                <p class="p-3 text-gray-300">
                                    First, make sure you have a Spotify account. On Spotify, create a playlist
                                    for your loved one with his or her favorite songs. Once you have a Spotify
                                    playlist dedicated to your loved one, share and copy the link/url to the
                                    Spotify playlist. Paste that Spotify link into the Spotify URL section during
                                    initial setup or afterwards be logging in to edit your Living Legacy page.
                                </p>
                            </div>
                        </li>
                        <li class="bg-white/20 text-white my-2 shadow-lg" x-data="accordion(3)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer">
                                <span>Can I remove a Tribute if a rude remark or photo is made on my loved
                                    one’s Living Legacy page?</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-white min-h-6 min-w-6 max-h-6 min-w-6 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div class="border-l-2 border-white overflow-hidden max-h-0 duration-500 transition-all"
                                x-ref="tab" :style="handleToggle()">
                                <p class="p-3 text-gray-300">
                                    You as an account owner have the full ability to allow or remove tributes
                                    from your loved one’s page. Simply log in as an account owner and delete
                                    any comments that you do not want visible to others.
                                </p>
                            </div>
                        </li>
                        <li class="bg-white/20 text-white my-2 shadow-lg" x-data="accordion(4)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer">
                                <span> Do you advertise my loved one’s page to the public?</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-white min-h-6 min-w-6 max-h-6 min-w-6 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div class="border-l-2 border-white overflow-hidden max-h-0 duration-500 transition-all"
                                x-ref="tab" :style="handleToggle()">
                                <p class="p-3 text-gray-300">
                                    No, we will not share or advertise your loved one’s page to the public. You
                                    as an account owner do have the ability to share your loved ones Living
                                    Legacy page to social media for others to visit. This is an optional step
                                    available to you, not a requirement.
                                </p>
                            </div>
                        </li>
                        <li class="bg-white/20 text-white my-2 shadow-lg" x-data="accordion(5)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer">
                                <span> Is there any ongoing maintenance or web hosting fees to maintain the
                                    Living Legacy page?</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-white min-h-6 min-w-6 max-h-6 min-w-6 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div class="border-l-2 border-white overflow-hidden max-h-0 duration-500 transition-all"
                                x-ref="tab" :style="handleToggle()">
                                <p class="p-3 text-gray-300">
                                    No, there is no ongoing fees for your Living Legacy page. All storage and
                                    maintenance is covered in your initial purchase. Storage covers up to 25
                                    photos and up to 1 gb of videos. If your video is of a larger size and longer
                                    length, it can also be uploaded to your Living Legacy page via embedded
                                    YouTube capability.
                                </p>
                            </div>
                        </li>
                        <li class="bg-white/20 text-white my-2 shadow-lg" x-data="accordion(6)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer">
                                <span>How do I get help and support?</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-white min-h-6 min-w-6 max-h-6 min-w-6 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div class="border-l-2 border-white overflow-hidden max-h-0 duration-500 transition-all"
                                x-ref="tab" :style="handleToggle()">
                                <p class="p-3 text-gray-300">
                                    Ensuring that your experience with Living Legacy is a hassle-free
                                    experience is our main priority. We provide many different avenues to
                                    contact us for help, support and suggestions. Please visit our Help Center on
                                    the website for a variety of different ways to contact us. We are also via
                                    Living Legacy on Facebook.
                                </p>
                            </div>
                        </li>
                        <li class="bg-white/20 text-white my-2 shadow-lg" x-data="accordion(7)">
                            <h2 @click="handleClick()"
                                class="flex flex-row justify-between items-center font-semibold p-3 cursor-pointer">
                                <span>Where can I purchase my Living Legacy Medallion?</span>
                                <svg :class="handleRotate()"
                                    class="fill-current text-white min-h-6 min-w-6 max-h-6 max-w-6 transform transition-transform duration-500"
                                    viewBox="0 0 20 20">
                                    <path
                                        d="M13.962,8.885l-3.736,3.739c-0.086,0.086-0.201,0.13-0.314,0.13S9.686,12.71,9.6,12.624l-3.562-3.56C5.863,8.892,5.863,8.611,6.036,8.438c0.175-0.173,0.454-0.173,0.626,0l3.25,3.247l3.426-3.424c0.173-0.172,0.451-0.172,0.624,0C14.137,8.434,14.137,8.712,13.962,8.885 M18.406,10c0,4.644-3.763,8.406-8.406,8.406S1.594,14.644,1.594,10S5.356,1.594,10,1.594S18.406,5.356,18.406,10 M17.521,10c0-4.148-3.373-7.521-7.521-7.521c-4.148,0-7.521,3.374-7.521,7.521c0,4.147,3.374,7.521,7.521,7.521C14.148,17.521,17.521,14.147,17.521,10">
                                    </path>
                                </svg>
                            </h2>
                            <div class="border-l-2 border-white overflow-hidden max-h-0 duration-500 transition-all"
                                x-ref="tab" :style="handleToggle()">
                                <p class="p-3 text-gray-300">
                                    We are constantly expanding our reach to make it as seamless as possible
                                    for our customers to use the shopping experience of their choice. Currently,
                                    our Living Legacy Medallions are available on Amazon, Shopify and in select
                                    Monument stores. If you have a local Monument store that does not already
                                    carry Living Legacy Medallions, please feel free to suggest us to them. We
                                    would love to serve your community
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>


    <section class="relative lg:py-24 py-16 bg-slate-50 dark:bg-slate-800" id="contact">
        <div class="container">
            <div class="grid grid-cols-1 pb-6 text-center">
                <h3 class="font-semibold text-2xl leading-normal mb-4">Get in touch </h3>

                <p class="text-slate-400 max-w-xl mx-auto">Feel free to reach out if you require any further assistance or
                    guidance.</p>
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
        </div>
        <!--end container-->
    </section>
    <!--end section-->
@endsection
@push('scripts')
    <script>
        $("#contactForm").on("submit", function(event) {
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
                success: function(response) {
                    toastr.options = {
                        progressBar: true,
                        closeButton: true,
                        timeOut: 2000,
                    };
                    if (response.status === true) {
                        toastr.success(response.message, "Success");
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        toastr.error(response.message, "Error");
                    }
                },
                error: function(errors) {
                    const errorMessages = Object.values(errors?.responseJSON?.errors).flat();
                    toastr.options = {
                        progressBar: true,
                        closeButton: true,
                    };
                    for (let i = 0; i < errorMessages.length; i++) {
                        toastr.error(errorMessages[i], "Error");
                    }
                },
                complete: function() {
                    $submitButton.prop('disabled', false); // Re-enable the submit button
                }
            });
        });
    </script>
@endpush
