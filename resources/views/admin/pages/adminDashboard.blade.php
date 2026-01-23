@extends('admin.layout.master')
@section('title', 'Living Leagacy | Dashboard')

@section('content')
    <div class="content">
        <div class="container-fluid pe-lg-4">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <h4 class="page-title">Dashboard</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between ">
                                    <div class="align-self-center">
                                        <i class="mdi mdi-cart-outline primary fs-1 text-info float-left"></i>
                                    </div>
                                    <div class="text-end">
                                        <h3>{{ $reSellers }}</h3>
                                        <span>Total Re-Sellers</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between ">
                                    <div class="align-self-center">
                                        <i class="mdi mdi-cart-arrow-down primary fs-1 text-info float-left"></i>
                                    </div>
                                    <div class="text-end">
                                        <h3>{{ $orders }}</h3>
                                        <span>Orders</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between ">
                                    <div class="align-self-center">
                                        <i class="mdi mdi-cart-check primary fs-1 text-info float-left"></i>
                                    </div>
                                    <div class="text-end">
                                        <h3>{{ $pendingOrders }}</h3>
                                        <span>Pending Orders</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between ">
                                    <div class="align-self-center">
                                        <i class="ri-qr-code-line primary fs-1 text-info float-left"></i>
                                    </div>
                                    <div class="text-end">
                                        <h3>{{ $qrCodes }}</h3>
                                        <span>Total QR Codes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between ">
                                    <div class="align-self-center">
                                        <i class="ri-qr-scan-2-line primary fs-1 text-info float-left"></i>
                                    </div>
                                    <div class="text-end">
                                        <h3>{{ $availableCodes }}</h3>
                                        <span>Available QR Codes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="d-flex justify-content-between ">
                                    <div class="align-self-center">
                                        <i class=" ri-links-line primary fs-1 text-info float-left"></i>
                                    </div>
                                    <div class="text-end">
                                        <h3>{{ $linkedCodes }}</h3>
                                        <span>Linked QR Codes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
