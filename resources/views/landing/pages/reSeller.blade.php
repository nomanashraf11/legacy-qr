@extends('landing.layout.master')
@section('content')
    <section class="realtive py-36 pb-24">
        <div class="container relative">
            <div class="grid grid-cols-1 pb-6 max-w-3xl mx-auto text-center">
                <h3 class="font-semibold text-2xl leading-normal mb-4">
                    Become a Reseller Partner
                </h3>
                <p class="text-slate-400 max-w-lg mx-auto">
                    Join our network of trusted resellers and gain access to wholesale pricing, dedicated support, and a comprehensive product catalog.
                </p>
                <div class="flex flex-wrap justify-center gap-6 mt-4 text-sm text-slate-400">
                    <span><strong class="text-slate-200">Wholesale Pricing</strong> — Competitive rates on all products</span>
                    <span><strong class="text-slate-200">Dedicated Support</strong> — Personal account manager</span>
                    <span><strong class="text-slate-200">Fast Shipping</strong> — Priority fulfillment</span>
                </div>
                <span class="text-gray-400 mt-3 block">
                    Already a reseller? <a href="{{ route('reseller.login') }}" class="hover:underline text-white">Login Here</a>
                </span>
            </div>

            <div class="grid md:grid-cols-1 grid-cols-1 items-center gap-6 mx-auto max-w-4xl">
                <div class="mx-auto w-full">
                    <div class="bg-white dark:bg-slate-900 rounded-md shadow dark:shadow-gray-700 p-6">
                        <form method="post" name="resellerApplicationForm" id="reseller_application_form">
                            @csrf
                            <p class="mb-0" id="error-msg1"></p>
                            <div id="simple-msg1"></div>

                            <h5 class="font-semibold mb-3 text-slate-700 dark:text-slate-200">Business Information</h5>
                            <div class="grid lg:grid-cols-2 grid-cols-1 gap-3 mb-4">
                                <div>
                                    <label class="font-semibold">Business Name <span class="text-red-500">*</span></label>
                                    <input name="business_name" type="text" required
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                        placeholder="Your business name">
                                </div>
                                <div>
                                    <label class="font-semibold">Business Category <span class="text-red-500">*</span></label>
                                    <select name="business_category" required
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                        <option value="">Select category</option>
                                        <option value="Retail">Retail</option>
                                        <option value="E-commerce">E-commerce</option>
                                        <option value="Funeral Home">Funeral Home</option>
                                        <option value="Gift Shop">Gift Shop</option>
                                        <option value="Hospitality">Hospitality</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="font-semibold">Years in Business</label>
                                    <input name="years_in_business" type="number" min="0" placeholder="e.g., 5"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                                <div class="lg:col-span-2">
                                    <label class="font-semibold">Street Address</label>
                                    <input name="street_address" type="text" placeholder="123 Main Street"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                                <div>
                                    <label class="font-semibold">City <span class="text-red-500">*</span></label>
                                    <input name="city" type="text" required placeholder="City"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                                <div>
                                    <label class="font-semibold">State <span class="text-red-500">*</span></label>
                                    <input name="state" type="text" required placeholder="e.g., NY"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                                <div>
                                    <label class="font-semibold">ZIP Code</label>
                                    <input name="zip_code" type="text" placeholder="10001"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                                <div>
                                    <label class="font-semibold">Business Phone</label>
                                    <input name="business_phone" type="tel" placeholder="(555) 123-4567"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                                <div>
                                    <label class="font-semibold">Website</label>
                                    <input name="website" type="url" placeholder="https://www.example.com"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                            </div>

                            <h5 class="font-semibold mb-3 mt-6 text-slate-700 dark:text-slate-200">Owner / Contact Information</h5>
                            <div class="grid lg:grid-cols-2 grid-cols-1 gap-3 mb-4">
                                <div>
                                    <label class="font-semibold">Full Name <span class="text-red-500">*</span></label>
                                    <input name="full_name" type="text" required placeholder="John Smith"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                                <div>
                                    <label class="font-semibold">Email Address <span class="text-red-500">*</span></label>
                                    <input name="email" type="email" required placeholder="john@example.com"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                                <div>
                                    <label class="font-semibold">Phone Number</label>
                                    <input name="phone" type="tel" placeholder="(555) 123-4567"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                            </div>

                            <h5 class="font-semibold mb-3 mt-6 text-slate-700 dark:text-slate-200">Additional Information</h5>
                            <div class="grid lg:grid-cols-1 grid-cols-1 gap-3 mb-4">
                                <div>
                                    <label class="font-semibold">Estimated Monthly Order Volume</label>
                                    <select name="estimated_monthly_volume"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                        <option value="">Select estimated volume</option>
                                        <option value="1-10">1-10</option>
                                        <option value="11-50">11-50</option>
                                        <option value="51-100">51-100</option>
                                        <option value="100+">100+</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="font-semibold">How did you hear about us?</label>
                                    <input name="hear_about_us" type="text" placeholder="e.g., Trade show, Email, Referral"
                                        class="mt-2 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0">
                                </div>
                                <div>
                                    <label class="font-semibold">Additional Notes</label>
                                    <textarea name="additional_notes" rows="4"
                                        class="mt-2 w-full py-2 px-3 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-100 dark:border-gray-800 focus:ring-0"
                                        placeholder="Tell us more about your business or any questions you have..."></textarea>
                                </div>
                            </div>

                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-4 mb-3">
                                By submitting this application, you agree to our <a href="{{ route('terms') }}" class="underline">terms of service</a> and <a href="{{ route('privacy') }}" class="underline">privacy policy</a>.
                            </p>

                            <button type="submit" id="submit1" name="send"
                                class="h-10 px-6 tracking-wide inline-flex items-center justify-center font-medium rounded-md bg-teal-500 text-white mt-2 relative">
                                <span class="spinner hidden absolute inset-0 flex justify-center items-center">
                                    <div class="w-6 h-6 border-t-2 border-b-2 border-teal-500 rounded-full animate-spin"></div>
                                </span>
                                <span class="button-text">Submit Application</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        $("#reseller_application_form").on("submit", function(event) {
            event.preventDefault();
            var $form = $(this);
            var $submitButton = $form.find('button[type="submit"]');
            var $spinner = $submitButton.find('.spinner');
            var $buttonText = $submitButton.find('.button-text');

            $submitButton.prop('disabled', true);
            $spinner.removeClass('hidden');
            $buttonText.addClass('hidden');

            var formData = new FormData(this);
            $.ajax({
                url: "/reseller-application",
                data: formData,
                type: "POST",
                processData: false,
                contentType: false,
                success: function(response) {
                    $spinner.addClass('hidden');
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
                        $submitButton.prop('disabled', false);
                        toastr.error(response.message, "Error");
                    }
                },
                error: function(errors) {
                    $spinner.addClass('hidden');
                    $buttonText.removeClass('hidden');
                    $submitButton.prop('disabled', false);
                    const errData = errors?.responseJSON?.errors;
                    if (errData) {
                        const errorMessages = Object.values(errData).flat();
                        toastr.options = { progressBar: true, closeButton: true };
                        errorMessages.forEach(function(msg) { toastr.error(msg, "Error"); });
                    } else {
                        toastr.error(errors?.responseJSON?.message || "Something went wrong.", "Error");
                    }
                },
                complete: function() {
                    $submitButton.prop('disabled', false);
                }
            });
        });
    </script>
@endpush
