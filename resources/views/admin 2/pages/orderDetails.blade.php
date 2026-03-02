@extends('admin.layout.master')
@section('title', 'Living Leagacy | Dashboard')

@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Order Details</h4>
                    </div>
                    <div class="mt-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tracking ID:</strong> {{ $order->tracking_id }}</p>
                                        <p><strong>Quantity:</strong> {{ $order->qr_codes }}</p>
                                        <p><strong>Amount:</strong> ${{ number_format($order->amount, 2) }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong> {{ $order->tracking_details }}
                                            @role('admin')
                                                <a class="changeTrackingDetails ms-3 pointer" id={{ $order->uuid }}>
                                                    <i class="mdi mdi-pencil-outline fs-5"></i>
                                                </a>
                                            @endrole
                                        </p>
                                        @php
                                            // dd($order->re_seller_id);
                                            $resel = App\Models\ReSeller::where('id', $order->re_seller_id)->first();
                                        @endphp
                                        <p><strong>Name:</strong> {{ $resel->user->name }}</p>
                                        <p><strong>Phone:</strong> {{ $resel->phone }}</p>
                                        <p><strong>Shipping Address:</strong> Meer Market Jinnah Road</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="col-md-6 ">
                        <div class="card">
                            <div class="card-body">
                                <p>Tracking ID: {{ $order->tracking_id }}</p>
                                <p>Status : {{ $order->tracking_details }}
                                    @role('admin')
                                        <a class="changeTrackingDetails" id={{ $order->uuid }}>edit</a>
                                    @endrole
                                </p>
                                <p>Quantity : {{ $order->qr_codes }}</p>
                                <p>Amount : ${{ number_format($order->amount, 2) }}</p>
                                @php
                                    // dd($order->re_seller_id);
                                    $resel = App\Models\ReSeller::where('id', $order->re_seller_id)->first();
                                @endphp
                                <p>Name : {{ $resel->user->name }}</p>
                                <p>Phone : {{ $resel->phone }}</p>
                                <p>Shipping Address : {{ $resel->shipping_address }}</p>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
    @include('admin.pages.modals.editTrackingDetails')
@endsection
@push('scripts')
    <script src="{{ asset('js/order.js') }}"></script>
@endpush
