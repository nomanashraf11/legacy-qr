@extends('admin.layout.master')
@section('title', 'Living Legacy | Settings')

@section('content')
    <div class="content pe-lg-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="d-flex align-items-center  justify-content-between">
                            <h4 class="page-title">My Account</h4>
                        </div>
                    </div>
                </div>
            </div>
            {{-- @if (session('status'))
                @if (session('status') == 'two-factor-authentication-disabled')
                    <div class="alert alert-danger" role="alert">
                        Two factor authentication has been disabled.
                    </div>
                @elseif (session('status') == 'two-factor-authentication-enabled')
                    <div class="alert alert-success" role="alert">
                        Two Factor Authentication has been enabled.
                    </div>
                @endif
            @endif --}}
            @php
                $user = Auth::user();
            @endphp
            @role('admin')
                <form id="adminUpdateAccount" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card mt-1">
                        <div class="card-body">
                            <h4>Personal Information</h4>
                            <div class="row my-3">
                                <div class="col">
                                    <label for="name">Name</label>
                                    <input class="form-control" type="text" id="nameInput" name="name"
                                        value="{{ $user->name }}">
                                </div>
                                <div class="col">
                                    <label for="name">Email</label>
                                    <input class="form-control" type="email" id="addressInput" name="email"
                                        value="{{ $user->email }}">
                                </div>
                            </div>
                            <div class="row my-3">
                                <div class="col">
                                    <label for="name">Amazon Link</label>
                                    <input class="mt-4 form-control" type="text" id="addressInput" name="amazon"
                                        value="{{ $user->admin->amazon }}">
                                </div>
                                <div class="col">
                                    <label for="name">Our Store Link</label>
                                    <p class="small text-muted">If filled then then this will be preferred for selling </p>
                                    <input class="form-control" type="text" id="addressInput" name="reviews_link"
                                        value="{{ $user->admin->reviews_link }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="name">Facebook Link</label>
                                    <input class="form-control" type="text" id="addressInput" name="facebook"
                                        value="{{ $user->admin->facebook }}">
                                </div>
                                <div class="col">
                                    <label for="name">Instagram Link</label>
                                    <input class="form-control" type="text" id="addressInput" name="instagram"
                                        value="{{ $user->admin->instagram }}">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <label for="name">Google Analytics</label>
                                    <textarea class="form-control" name="analytics" id="addressInput" rows="10">{{ $user->admin->analytics }}</textarea>

                                </div>
                            </div>
                            @role('re-sellers')
                                <div class="row my-3">
                                    <div class="col">
                                        <label for="name">Phone</label>
                                        <input class="form-control" type="text" id="nameInput" name="phone"
                                            value="{{ $user->reSeller?->phone ?? '' }}">
                                    </div>
                                    <div class="col">
                                        <label for="name">Address</label>
                                        <input class="form-control" type="email" id="addressInput" name="address"
                                            value="{{ $user->reSeller?->shipping_address ?? '' }}">
                                    </div>
                                </div>
                            @endrole
                            <div class="text-end">
                                <button type="submit" class="mt-3 btn btn-primary " id="updateSubmit">Update Details
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="page-title-box">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                                            <h4>Set prices for resellers and define the minimum and maximum
                                                quantities they can purchase</h4>

                                        </div>
                                        <div class="row my-3">
                                            <div class="col">
                                                <form id="qrDataUpdate" method="post">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="">Min Quantity</label>
                                                            <input type="text" name="min_quantity" class="form-control"
                                                                value="{{ $user->admin->min_quantity ?? '' }}">
                                                        </div>
                                                        <div class="col">
                                                            <label for="">Max Quantity</label>
                                                            <input type="text" name="max_quantity" class="form-control"
                                                                value="{{ $user->admin->max_quantity ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            <label for="">Price</label>
                                                            <input type="text" name="qr_price" class="form-control"
                                                                value="{{ isset(Auth::user()->admin->qr_price) ? number_format(Auth::user()->admin->qr_price, 2) : '' }}"">
                                                        </div>
                                                    </div>
                                                    <div class="text-end">

                                                        <button type="submit" class="btn btn-primary">Update
                                                            Price</button>
                                                    </div>
                                                </form>
                                                <form action="{{ route('togglePurchase') }}" method="post">
                                                    @csrf
                                                    @if (Auth::user()->admin->purchase == 1)
                                                        <button class="btn btn-primary" type="submit">Turn on
                                                            purchase</button>
                                                    @else
                                                        <button class="btn btn-primary" type="submit">Turn off
                                                            purchase</button>
                                                    @endif
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="page-title-box">
                                        <div class="row align-items-center justify-content-between">
                                            <div class="col-8 col-sm-3">
                                                <h4>Twak to Script</h4>
                                            </div>
                                            <div class="col-3 col-sm-1 ">
                                                @if ($user->admin->tawk == 1)
                                                    <span class="badge w-100 bg-success ">
                                                        ON
                                                    </span>
                                                @else
                                                    <span class="badge w-100 bg-danger ">
                                                        OFF
                                                    </span>
                                                @endif

                                            </div>
                                        </div>
                                        <div class="row my-3">


                                            <div class="col">
                                                <form action="{{ route('change.tawkto') }}" method="post">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col">
                                                            @if ($user->admin->tawk == 0)
                                                                <button type="submit" class="btn btn-primary">Turn on
                                                                    TawkTo</button>
                                                            @else
                                                                <button type="submit" class="btn btn-primary">Turn off
                                                                    TawkTo</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="page-title-box">
                                        <h4 class="page-title">Two factor authentication </h4>
                                    </div>
                                </div>
                            </div>
                            <!-- Check if two-factor authentication is already enabled -->
                            @if ($user->two_factor_secret)
                                <!-- Form to Disable 2FA -->
                                <form method="POST" action="{{ route('two-factor.disable') }}">
                                    @csrf
                                    @method('DELETE')

                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="list item mb-2">
                                                {!! $user->twoFactorQrCodeSvg() !!}
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <!-- Div that contains codes, initially hidden -->
                                            <div class="list item mb-2" id="recovery-codes" {{-- style="display: none;" --}}>
                                                @php
                                                    $codes = json_decode(decrypt($user->two_factor_recovery_codes));
                                                @endphp
                                                <ul>
                                                    @foreach ($codes as $code)
                                                        <li>{{ $code }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>

                                            <!-- Toggle button -->
                                            {{-- <button type="button" class="btn btn-info mb-2" onclick="toggleCodes()">Show
                                                Recovery Codes</button> --}}

                                        </div>
                                    </div>

                                    <div class="mb-4 mt-3">
                                        <button class="btn btn-danger" type="submit">
                                            Disable Two Factor Authentication
                                        </button>
                                    </div>
                                </form>
                            @else
                                <!-- Form to Enable 2FA -->
                                <form method="POST" action="{{ route('two-factor.enable') }}">
                                    @csrf
                                    <div class="mb-4 mt-3">
                                        <button class="btn btn-primary" type="submit">
                                            Enable Two Factor Authentication
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endrole
            @role('re-sellers')
                @php $missingFields = session('missing_profile_fields', []); @endphp
                @if (!empty($missingFields))
                    <div class="alert alert-warning d-flex align-items-start" role="alert">
                        <div class="flex-grow-1">
                            <strong>{{ session('message', 'Complete your profile to continue.') }}</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($missingFields as $field => $label)
                                    <li>{{ $label }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <form id="sellarUpdateAccount" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <h4>Personal Information</h4>

                            <div class="row my-3">
                                <div class="col">
                                    <label for="name">Name</label>
                                    <input class="form-control" type="text" id="nameInput" name="name"
                                        value="{{ $user->name }}">
                                </div>
                                <div class="col">
                                    <label for="name">Email</label>
                                    <input class="form-control" type="email" id="addressInput" name="email"
                                        value="{{ $user->email }}">
                                </div>
                            </div>
                            <div class="row my-3">
                                <div class="col">
                                    <label for="phoneInput">Phone <span class="text-danger">*</span></label>
                                    <input class="form-control {{ isset($missingFields['phone']) ? 'border-warning' : '' }}" type="text" id="phoneInput" name="phone"
                                        value="{{ $user->reSeller?->phone ?? '' }}" placeholder="Required for orders">
                                </div>
                                <div class="col">
                                    <label for="addressInput">Shipping Address <span class="text-danger">*</span></label>
                                    <input class="form-control {{ isset($missingFields['address']) ? 'border-warning' : '' }}" type="text" id="addressInput" name="address"
                                        value="{{ $user->reSeller?->shipping_address ?? '' }}" placeholder="Required for orders">
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="mt-2 btn btn-primary " id="updateSubmit">Update Details
                                </button>
                            </div>

                        </div>
                    </div>
                </form>
            @endrole
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    <h4 class="page-title">Change Password</h4>
                                </div>
                            </div>
                        </div>

                        <form id="adminChangePassword" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="list item mb-2">
                                        <label class="text-muted smaller form-label">Current Password</label>
                                        <input type="password" class="form-control" name="old_password"
                                            placeholder="Enter your password">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="list item mb-2">
                                        <label class="text-muted smaller form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password"
                                            placeholder="Enter your new password">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="list item mb-2">
                                        <label class="text-muted smaller form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="new_password_confirmation"
                                            placeholder="Enter your new password again">
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mb-4 mt-3">
                                <button class="btn btn-primary" type="submit" id="submit-button">
                                    Update my password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/settings.js') }}"></script>

    <script>
        function toggleCodes() {
            var codesDiv = document.getElementById('recovery-codes');
            var button = document.querySelector('button[onclick="toggleCodes()"]');
            if (codesDiv.style.display === 'none') {
                codesDiv.style.display = 'block';
                button.textContent = 'Hide Recovery Codes';
                codesDiv.style.opacity = '0';
                setTimeout(function() {
                    codesDiv.style.transition = 'opacity 0.5s ease-in-out';
                    codesDiv.style.opacity = '1';
                }, 10); // Start the transition slightly after displaying the block
            } else {
                codesDiv.style.opacity = '0';
                codesDiv.style.transition = 'opacity 0.5s ease-in-out';
                setTimeout(function() {
                    codesDiv.style.display = 'none';
                    button.textContent = 'Show Recovery Codes';
                }, 500); // Match the delay to the transition duration
            }
        }
    </script>
@endpush
