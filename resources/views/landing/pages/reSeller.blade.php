@extends('landing.layout.master')
@section('content')
    <section class="realtive py-36 pb-24">
        <div class="container relative">
            <div class="grid grid-cols-1 pb-6 max-w-xl mx-auto text-center">
                <h3 class="font-semibold text-2xl leading-normal mb-4">
                    We look forward to partnering with you in order to bring Living Legacies to your community
                </h3>

                <p class="text-slate-400 max-w-lg mx-auto">
                    Please submit the reseller form below to gain access to our reseller portal. We will be in touch
                    with you shortly!
                </p>
                <span class="text-gray-400 mt-3">
                    Already a reseller? <a href="{{ route('login') }}" class="hover:underline text-white">Login Here</a>
                </span>
            </div><!--end grid-->


            <div class="grid md:grid-cols-1 grid-cols-1 items-center gap-6 mx-auto">
                <div class="lg:col-span-8 md:col-span-6 mx-auto">
                    <div class="lg:ms-5">
                        <div class="bg-white dark:bg-slate-900 rounded-md shadow dark:shadow-gray-700 p-6">
                            <form method="post" name="myForm" id="reSeller_form">
                                @csrf
                                <p class="mb-0" id="error-msg1"></p>
                                <div id="simple-msg1"></div>
                                <div class="grid lg:grid-cols-12 grid-cols-1 gap-3 mb-4">
                                    <div class="lg:col-span-6">
                                        <label class="font-semibold">First Name</label>
                                        <input name="first_name" id="fname" type="text"
                                            class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                            placeholder="First Name">
                                    </div>
                                    <div class="lg:col-span-6">
                                        <label class="font-semibold">Last Name</label>
                                        <input name="last_name" id="lname" type="text"
                                            class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                            placeholder="Last Name">
                                    </div>
                                    <div class="lg:col-span-6">
                                        <label class="font-semibold">Website</label>
                                        <input name="website" id="website" type="text"
                                            class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                            placeholder="Website">
                                    </div>
                                    <div class="lg:col-span-6">
                                        <label class="font-semibold">Your Email</label>
                                        <input name="email" id="email1" type="email"
                                            class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                            placeholder="Email">
                                    </div>

                                    <div class="lg:col-span-12">
                                        <label class="font-semibold">Phone Number</label>
                                        <input name="phone" id="phone" type="text"
                                            class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                            placeholder="Phone Number">
                                    </div>
                                    <div class="lg:col-span-12">
                                        <label class="font-semibold">Address</label>
                                        <input name="address" id="address" type="text"
                                            class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                            placeholder="Enter your street address ">
                                    </div>
                                    <div class="lg:col-span-12">
                                        <label class="font-semibold">Business Details</label>
                                        <textarea name="business"
                                            class="mt-2 w-full py-2 px-3 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                            rows="5" placeholder="Write some information about your business"></textarea>
                                    </div>
                                </div>
                                <button type="submit" id="submit1" name="send"
                                    class="h-10 px-6 tracking-wide inline-flex items-center justify-center font-medium rounded-md bg-teal-500 text-white mt-2 relative">
                                    <span class="spinner hidden absolute inset-0 flex justify-center items-center">
                                        <!-- Spinner HTML goes here -->
                                        <div
                                            class="w-6 h-6 border-t-2 border-b-2 border-teal-500 rounded-full animate-spin">
                                        </div>
                                    </span>
                                    <span class="button-text">Submit</span>
                                </button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div><!--end container-->
    </section><!--end section-->
@endsection
@push('scripts')
    <script>
        $("#reSeller_form").on("submit", function(event) {
            event.preventDefault();
            var $submitButton = $(this).find('button[type="submit"]');
            var $spinner = $submitButton.find('.spinner');
            var $buttonText = $submitButton.find('.button-text');

            $submitButton.prop('disabled', true); // Disable the submit button
            $spinner.removeClass('hidden'); // Show the spinner
            $buttonText.addClass('hidden'); // Hide the button text
            var $submitButton = $(this).find('button[type="submit"]');
            $submitButton.prop('disabled', true); // Disable the submit button
            var formData = new FormData(this);
            $.ajax({
                url: "/create-re-seller-request",
                data: formData,
                type: "POST",
                processData: false, // Important: Don't process the data
                contentType: false,
                success: function(response) {
                    $spinner.addClass('hidden'); // Hide the spinner
                    $buttonText.removeClass('hidden');
                    toastr.options = {
                        progressBar: true,
                        closeButton: true,
                        timeOut: 4500,
                    };
                    if (response.status === true) {
                        toastr.success(response.message, "Success");
                        setTimeout(function() {
                            location.reload();
                        }, 4600);
                    } else {
                        $submitButton.prop('disabled', false); // Re-enable the submit button
                        toastr.error(response.message, "Error");
                    }
                },
                error: function(errors) {
                    $spinner.addClass('hidden'); // Hide the spinner
                    $buttonText.removeClass('hidden');
                    $submitButton.prop('disabled', false); // Re-enable the submit button

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
