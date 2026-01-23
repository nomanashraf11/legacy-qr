@extends('admin.layout.master')
@section('title', 'Living Leagacy | Dashboard')

@section('content')
    <div class="content">
        <!-- Start Content-->
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Buy QR Codes</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <form action="{{ route('stripe.checkout') }}" method="post">
                                        @csrf
                                        <div>
                                            <label for="">Enter number of QrCodes to buy</label>
                                            <input type="number" name="qr_codes" class="form-control">
                                        </div>
                                        <div class="text-end mt-1">
                                            <button type="submit" class="mt-2 btn btn-primary">Buy Now</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item border-0 pb-0">
                                            <h5 class="d-flex m-0 p-0 justify-content-between align-items-center">
                                                Minimum
                                                <span>
                                                    {{ App\Models\User::role('admin')->first()->admin->min_quantity }}
                                                </span>
                                            </h5>
                                        </li>
                                        <li class="list-group-item border-0 pb-0">
                                            <h5 class="d-flex m-0 p-0 justify-content-between align-items-center">
                                                Maximum
                                                <span>
                                                    {{ App\Models\User::role('admin')->first()->admin->max_quantity }}
                                                </span>
                                            </h5>
                                        </li>
                                        <li class="list-group-item border-0 pb-0">
                                            <h5 class="d-flex m-0 p-0 justify-content-between align-items-center">
                                                Price per unit
                                                <span>
                                                    ${{ number_format(App\Models\User::role('admin')->first()->admin->qr_price, 2) }}
                                                </span>
                                            </h5>
                                        </li>
                                    </ul>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- container -->
    </div>
@endsection
