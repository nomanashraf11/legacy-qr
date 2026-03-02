@extends('landing.layout.master')
@section('content')
    <section>
        <!-- Hero -->
        <div class="text-secondary position-relative hero-text px-4 px-md-0 py-5">
            <div class="pt-5 pb-4 w-100">
                <h1 class="display-1 fw-bold text-white">Send us a Message</h1>
                <div class="hairline mx-auto"></div>
                <div class="col-lg-7 mx-auto mt-4 pt-2">
                    <p class="fs-5 text-white-50">
                        Reach out with your questions, ideas, or stories – we're here to
                        listen and assist you in creating a fitting tribute for those you
                        cherish.
                    </p>
                </div>
            </div>
        </div>
        <img src="{{ asset('assets/landing/svg/divider.svg') }}" width="100%" class="curve-divider" />
    </section>

    <!-- Contact fORM -->

    <div class="contact">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card rounded-4">
                        <h2 class="text-center my-4">Contact Us</h2>
                        <form id="contactForm" method="POST" class="d-flex flex-column gap-4">
                            @csrf
                            <div class="d-flex align-items-center gap-4 w-100">
                                <div class="form-group w-100">
                                    <label class="form-label" for="name">Name</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter your name" required />
                                </div>
                                <div class="form-group w-100">
                                    <label class="form-label" for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter your email address" required />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="subject">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject"
                                    placeholder="Enter the subject" />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="message">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Enter your message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark text-uppercase rounded-0 py-2">
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/settings.js') }}"></script>
@endpush
